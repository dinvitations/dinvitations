<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GreetingWallResource\Pages;
use App\Models\Feature;
use App\Models\InvitationGuest;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GreetingWallResource extends Resource
{
    protected static ?string $model = InvitationGuest::class;
    protected static ?string $modelLabel = 'Greeting Wall';
    protected static ?string $pluralModelLabel = 'Greeting Wall';

    protected static ?string $navigationIcon = 'heroicon-o-tv';
    protected static ?string $navigationGroup = 'Pro Plan';
    protected static ?int $navigationSort = 2;

    protected static ?string $breadcrumb = 'Greeting Wall';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if (!$user->hasFeature(Feature::FEATURES['greeting'])) {
            return 'Unlock Now!';
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = auth()->user();

        return !$user->hasFeature(Feature::FEATURES['greeting']) ? 'success' : null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isClient();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Attended')
            ->emptyStateHeading(function () {
                $user = auth()->user();
                if (!$user->hasFeature(Feature::FEATURES['greeting'])) {
                    return 'Oops, Locked!';
                }
                return 'No attendance yet';
            })
            ->emptyStateDescription(function () {
                $user = auth()->user();
                if (!$user->hasFeature(Feature::FEATURES['greeting'])) {
                    return 'This page isn’t available for you yet. Upgrade to get full access.';
                }
                return 'Attendance data is not available yet.';
            })
            ->emptyStateIcon(function () {
                $user = auth()->user();
                if (!$user->hasFeature(Feature::FEATURES['greeting'])) {
                    return 'heroicon-o-lock-closed';
                }
                return 'heroicon-o-x-mark';
            })
            ->columns([
                Tables\Columns\ImageColumn::make('greeting_wall_image_url')
                    ->label('Image')
                    ->disk('minio')
                    ->visibility('private'),

                Tables\Columns\TextColumn::make('attended_at')
                    ->label('Time')
                    ->dateTime('h:i A')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('guest.name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Category')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'reg' => 'General',
                        'vip' => 'VIP',
                        'vvip' => 'VVIP',
                        default => strtoupper($state),
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Open')
                    ->modalHeading(fn($record) => $record->guest->name)
                    ->modalContent(fn($record) => view('filament.widgets.partials.greeting-wall-modal', ['record' => $record])),

                Tables\Actions\Action::make('rewrite')
                    ->icon('heroicon-s-pencil-square')
                    ->label('Rewrite'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('invitation', function ($query) {
                $query->whereNotNull('published_at')
                    ->whereHas('order', function ($subQuery) {
                        $subQuery->where('status', 'active')
                            ->where('user_id', auth()->user()->id)
                            ->whereHas('package.features', function ($featureQuery) {
                                $featureQuery->where('name', Feature::FEATURES['greeting']);
                            });
                    }, '=', 1);
            })
            ->whereNotNull(['attended_at', 'greeting_wall_image_url'])
            ->orderByRaw('left_at IS NOT NULL');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGreetingWalls::route('/'),
        ];
    }
}
