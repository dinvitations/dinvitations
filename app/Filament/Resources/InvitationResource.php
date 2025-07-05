<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvitationResource\Pages;
use App\Models\Invitation;
use Dotswan\MapPicker;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;
    protected static ?string $modelLabel = 'Event Detail';
    protected static ?string $pluralModelLabel = 'Event Details';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->isClient();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('event_name')
                                ->label('Event Name')
                                ->required(),

                            Forms\Components\TextInput::make('organizer_name')
                                ->label('Organizer\'s Name')
                                ->required(),

                            Forms\Components\TextInput::make('phone_number')
                                ->label('WhatsApp Number')
                                ->prefix('+62')
                                ->tel()
                                ->placeholder('e.g. 812-3456-7890')
                                ->helperText('Enter a valid and reachable WhatsApp number.')
                                ->required()
                                ->extraAttributes([
                                    'inputmode' => 'numeric',
                                    'pattern' => '[0-9]*',
                                    'x-on:beforeinput' => "
                                        if (event.data && /[^0-9]/.test(event.data)) {
                                            event.preventDefault();
                                        }
                                    ",
                                ])
                                ->mask(RawJs::make(<<<'JS'
                                    $input => {
                                        let numbers = $input.replace(/\D/g, '').replace(/^0+/, '').slice(0, 13);

                                        const parts = [];
                                        if (numbers.length <= 3) {
                                            parts.push(numbers);
                                        } else if (numbers.length <= 7) {
                                            parts.push(numbers.slice(0, 3), numbers.slice(3));
                                        } else if (numbers.length <= 11) {
                                            parts.push(numbers.slice(0, 3), numbers.slice(3, 7), numbers.slice(7));
                                        } else {
                                            parts.push(numbers.slice(0, 3), numbers.slice(3, 7), numbers.slice(7, 11), numbers.slice(11));
                                        }

                                        return parts.join('-');
                                    }
                                JS))
                                ->dehydrateStateUsing(fn($state) => $state
                                    ? '+62' . ltrim(preg_replace('/\D+/', '', $state), '0')
                                    : null
                                )
                                ->afterStateHydrated(function ($state, Set $set) {
                                    if (!$state) return;

                                    $digits = preg_replace('/\D+/', '', $state);
                                    $local = preg_replace('/^(62|0)/', '', $digits);
                                    $local = substr($local, 0, 13);

                                    $formatted = match (true) {
                                        strlen($local) <= 3 => $local,
                                        strlen($local) <= 7 => substr($local, 0, 3) . '-' . substr($local, 3),
                                        strlen($local) <= 11 => substr($local, 0, 3) . '-' . substr($local, 3, 4) . '-' . substr($local, 7),
                                        default => substr($local, 0, 3) . '-' . substr($local, 3, 4) . '-' . substr($local, 7, 4) . '-' . substr($local, 11),
                                    };

                                    $set('phone_number', $formatted);
                                })
                                ->rule(fn () => function ($attributes, $value, $fail) {
                                    $digits = ltrim(preg_replace('/\D+/', '', $value), '0');
                                    $length = strlen($digits);

                                    if (!ctype_digit($digits)) {
                                        $fail('The WhatsApp number must contain digits only.');
                                    }

                                    if ($length < 7 || $length > 13) {
                                        $fail('The WhatsApp number must be between 7 and 13 digits.');
                                    }
                                })->columnSpanFull(),

                            Forms\Components\Hidden::make('unlock_souvenir_stock')
                                ->default(false)
                                ->reactive(),

                            Forms\Components\TextInput::make('souvenir_stock')
                                ->label('Souvenir Stock')
                                ->numeric()
                                ->reactive()
                                ->minValue(fn ($record) => $record?->availableSouvenirStock() ?? 0)
                                ->required()
                                ->afterStateHydrated(function ($component) {
                                    $record = $component->getRecord();

                                    if (! $record) {
                                        $component->state(0);
                                        return;
                                    }

                                    $component->state($record->isSouvenirLocked() ? $record->availableSouvenirStock() : $record->souvenir_stock);
                                })
                                ->disabled(fn ($get, $record) => $record?->isSouvenirLocked() && !$get('unlock_souvenir_stock'))
                                ->dehydrated(fn ($record, $get) => $record?->isSouvenirLocked() ? $get('unlock_souvenir_stock') : true)
                                ->hint(fn ($record) => $record?->availableSouvenirStock() ? $record->availableSouvenirStock() . ' / ' . $record->souvenir_stock . ' available' : null)
                                ->suffixAction(
                                    Action::make('toggleUnlock')
                                        ->icon(fn ($get) => $get('unlock_souvenir_stock') ? 'heroicon-m-lock-open' : 'heroicon-m-lock-closed')
                                        ->hiddenLabel()
                                        ->visible(fn ($record) => $record?->isSouvenirLocked())
                                        ->action(function ($set, $get, $record) {
                                            $isUnlocked = $get('unlock_souvenir_stock');
                                            $set('unlock_souvenir_stock', ! $isUnlocked);

                                            if ($record && $isUnlocked) {
                                                $set('souvenir_stock', $record->availableSouvenirStock());
                                            }
                                        })
                                ),

                            Forms\Components\TextInput::make('total_seats')
                                ->label('Total Seats')
                                ->numeric()
                                ->minValue(0)
                                ->required(),

                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\DateTimePicker::make('date_start')
                                        ->label('Start Date')
                                        ->required(),

                                    Forms\Components\DateTimePicker::make('date_end')
                                        ->label('End Date')
                                        ->required(),
                                ])->columns(2),

                            Forms\Components\TextInput::make('location')
                                ->label('Street Address')
                                ->required()
                                ->columnSpanFull(),

                            MapPicker\Fields\Map::make('location_latlng')
                                ->label('Location')
                                ->required()
                                ->lazy()
                                ->afterStateHydrated(function ($state, Set $set) {
                                    if (is_array($state) && isset($state['lat'], $state['lng'])) {
                                        return;
                                    }

                                    if (is_string($state) && str_contains($state, ',')) {
                                        [$lat, $lng] = explode(',', $state);

                                        $lat = filter_var($lat, FILTER_VALIDATE_FLOAT);
                                        $lng = filter_var($lng, FILTER_VALIDATE_FLOAT);

                                        if ($lat !== false && $lng !== false) {
                                            $set('location_latlng', ['lat' => $lat, 'lng' => $lng]);
                                        }
                                    }
                                })
                                ->afterStateUpdated(function (array $state, Set $set, Get $get) {
                                    if (!empty($get('location')) || !isset($state['lat'], $state['lng'])) {
                                        return;
                                    }

                                    $lat = round($state['lat'], 6);
                                    $lng = round($state['lng'], 6);
                                    $cacheKey = "reverse_geocode_{$lat}_{$lng}";

                                    $address = Cache::remember($cacheKey, now()->addDay(), function () use ($lat, $lng) {
                                        $response = Http::withHeaders([
                                            'User-Agent' => 'MyApp/1.0 (me@example.com)',
                                        ])->get('https://nominatim.openstreetmap.org/reverse', [
                                            'format' => 'json',
                                            'lat' => $lat,
                                            'lon' => $lng,
                                            'zoom' => 18,
                                            'addressdetails' => 1,
                                        ]);

                                        $data = $response->json();

                                        return $response->successful() && isset($data['display_name']) ? $data['display_name'] : null;
                                    });

                                    if ($address) {
                                        $set('location', $address);
                                    }
                                })
                                ->dehydrateStateUsing(function ($state) {
                                    if (is_array($state) && isset($state['lat'], $state['lng'])) {
                                        return round($state['lat'], 6) . ',' . round($state['lng'], 6);
                                    }

                                    return null;
                                })
                                ->columnSpanFull(),

                            Actions::make([
                                Actions\Action::make('setLocation')
                                    ->label('Set Location')
                                    ->icon('heroicon-m-map-pin')
                                    ->color('primary')
                                    ->button()
                                    ->outlined()
                                    ->action(function ($livewire, Get $get, Set $set) {
                                        $address = $get('location');

                                        if (!$address) {
                                            return;
                                        }

                                        $cacheKey = 'forward_geocode_' . md5($address);
                                        $latLng = Cache::remember($cacheKey, now()->addDay(), function () use ($address) {
                                            $response = Http::withHeaders([
                                                'User-Agent' => 'MyApp/1.0 (me@example.com)',
                                            ])->get('https://nominatim.openstreetmap.org/search', [
                                                'q' => $address,
                                                'format' => 'json',
                                                'limit' => 1,
                                            ]);

                                            $data = $response->json();

                                            if ($response->successful() && isset($data[0]['lat'], $data[0]['lon'])) {
                                                return [
                                                    'lat' => (float) $data[0]['lat'],
                                                    'lng' => (float) $data[0]['lon'],
                                                ];
                                            }

                                            return null;
                                        });

                                        if ($latLng) {
                                            $set('location_latlng', $latLng);
                                            $livewire->dispatch('refreshMap');
                                        }
                                    }),
                            ])->fullWidth(),

                            Actions::make([
                                Actions\Action::make('setAddress')
                                    ->label('Set Address')
                                    ->icon('heroicon-m-home')
                                    ->color('primary')
                                    ->button()
                                    ->outlined()
                                    ->action(function (Get $get, Set $set) {
                                        if (empty($get('location_latlng'))) {
                                            return;
                                        }

                                        $location = $get('location_latlng');
                                        if (!isset($location['lat'], $location['lng'])) {
                                            return;
                                        }

                                        $lat = round($location['lat'], 6);
                                        $lng = round($location['lng'], 6);
                                        $cacheKey = "reverse_geocode_{$lat}_{$lng}";

                                        $address = Cache::remember($cacheKey, now()->addDay(), function () use ($lat, $lng) {
                                            $response = Http::withHeaders([
                                                'User-Agent' => 'MyApp/1.0 (me@example.com)',
                                            ])->get('https://nominatim.openstreetmap.org/reverse', [
                                                'format' => 'json',
                                                'lat' => $lat,
                                                'lon' => $lng,
                                                'zoom' => 18,
                                                'addressdetails' => 1,
                                            ]);

                                            $data = $response->json();

                                            return $response->successful() && isset($data['display_name']) ? $data['display_name'] : null;
                                        });

                                        if ($address) {
                                            $set('location', $address);
                                        }
                                    }),
                            ])->fullWidth(),

                            Forms\Components\RichEditor::make('message')
                                ->label('Message Content')
                                ->default(Invitation::MESSAGE)
                                ->helperText('Keep the placeholders (e.g. [Guest Name]) as is — they will be replaced with real data.')
                                ->columnSpanFull(),
                        ])
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('order', function ($subQuery) {
                $subQuery->where('status', 'active');
                $subQuery->where('user_id', auth()->user()->id);
            }, '=', 1);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvitations::route('/'),
            'create' => Pages\CreateInvitation::route('/create'),
            'edit' => Pages\EditInvitation::route('/{record}/edit'),
            'view' => Pages\ViewInvitation::route('/{record}/view')
        ];
    }
}
