<?php

namespace App\Filament\Resources;

use App\Enums\PermissionsEnum;
use App\Filament\Resources\TemplatesResource\Pages;
use App\Models\Event;
use App\Models\File;
use App\Models\Role;
use App\Models\Template;
use App\Models\TemplateView;
use App\Models\User;
use Dotswan\FilamentGrapesjs\Fields\GrapesJs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplatesResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = ' ';
    protected static ?int $navigationSort = 1;

    public static ?string $breadcrumb = 'Templates';

    public static function canAccess(): bool
    {
        return auth()->user()->can(PermissionsEnum::MANAGE_TEMPLATES);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('slug', Str::slug($state));
                            })
                            ->lazy()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('event_id')
                            ->label('Event Category')
                            ->placeholder('- Select an event category -')
                            ->required()
                            ->options(Event::pluck('name', 'id'))
                            ->native(false),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\FileUpload::make('preview_url')
                            ->label('Template Preview')
                            ->disk('minio')
                            ->directory('template-previews')
                            ->visibility('private')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('force')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('1080')
                            ->imageResizeTargetHeight('1080')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        GrapesJs::make('template_builder')
                            ->label('Template Builder')
                            ->dehydrated(false)
                            ->afterStateHydrated(function (GrapesJs $component, ?Template $record) {
                                if (!$record) {
                                    return;
                                }

                                $cacheKey = "template_view_data_{$record->id}";

                                $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($record) {
                                    $types = array_keys(TemplateView::getTypes());

                                    $views = $record->views()
                                        ->with('file')
                                        ->whereIn('type', $types)
                                        ->get()
                                        ->keyBy('type');

                                    $getContent = function (?TemplateView $view): string {
                                        if (!$view || !$view->file) {
                                            return '';
                                        }

                                        $disk = $view->file->disk;
                                        $path = $view->file->path;

                                        return Storage::disk($disk)->exists($path)
                                            ? Storage::disk($disk)->get($path)
                                            : '';
                                    };

                                    return [
                                        'html' => $getContent($views->get('html')),
                                        'css' => $getContent($views->get('css')),
                                        'js' => $getContent($views->get('js')),
                                        'grapesjs' => [
                                            'projectData' => $getContent($views->get('grapesjs.projectData')),
                                            'components' => $getContent($views->get('grapesjs.components')),
                                            'style' => $getContent($views->get('grapesjs.style')),
                                        ],
                                    ];
                                });

                                $component->state([
                                    'grapesjs' => [
                                        'projectData' => $data['grapesjs']['projectData'],
                                    ]
                                ]);
                            })
                            ->plugins([
                                'gjs-blocks-basic',
                                'grapesjs-component-code-editor',
                                'grapesjs-component-countdown',
                                'grapesjs-custom-code',
                                'grapesjs-dinvitations',
                                'grapesjs-navbar',
                                'grapesjs-parser-postcss',
                                'grapesjs-plugin-export',
                                'grapesjs-plugin-forms',
                                'grapesjs-preset-webpage',
                                'grapesjs-rte-extensions',
                                'grapesjs-style-bg',
                                'grapesjs-tabs',
                                'grapesjs-tooltip',
                                'grapesjs-touch',
                                'grapesjs-tui-image-editor',
                                'grapesjs-typed',
                            ])
                            ->settings([
                                'assetManager' => [
                                    'upload' => route('grapesjs.upload'),
                                    'headers' => [
                                        'X-CSRF-TOKEN' => csrf_token(),
                                        'X-USER-ID' => auth()->user()->id,
                                    ],
                                    'uploadName' => 'files',
                                    'assets' => File::query()
                                        ->where('disk', 'uploads')
                                        ->where('fileable_type', User::class)
                                        ->where('fileable_id', auth()->user()->id)
                                        ->get()
                                        ->map(function ($file) {
                                            return [
                                                'src' => Storage::disk($file->disk)->url($file->path),
                                                'name' => $file->original_name ?? $file->filename,
                                                'type' => $file->type,
                                                'mime' => $file->mime_type,
                                            ];
                                        })
                                        ->values()
                                        ->toArray(),
                                ],
                            ])
                            ->id('template_builder')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(6)
            ->paginationPageOptions([6, 9, 12, 15])
            ->emptyStateHeading('No template yet')
            ->emptyStateDescription('Start by adding your first one!')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('preview_url')
                        ->label('Template Preview')
                        ->visibility('private')
                        ->width('100%')
                        ->height('auto')
                        ->defaultImageUrl('https://placehold.co/300x300')
                        ->getStateUsing(function ($record) {
                            $url = $record->preview_url;

                            if (filter_var($url, FILTER_VALIDATE_URL)) {
                                return $url;
                            }

                            if ($url && Storage::disk('minio')->exists($url)) {
                                return Storage::disk('minio')->temporaryUrl($url, now()->addMinutes(5));
                            }

                            return null;
                        }),
                    Tables\Columns\TextColumn::make('name')
                        ->label('Name')
                        ->weight('bold')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('slug')
                        ->label('Slug')
                        ->formatStateUsing(function ($record) {
                            $url = route('templates.show', ['slug' => $record->slug]);
                            $cleanUrl = Str::after($url, '://');
                            return Str::limit($cleanUrl, 35, '...');
                        })
                        ->tooltip(function ($record) {
                            $url = route('templates.show', ['slug' => $record->slug]);
                            return Str::after($url, '://');
                        })
                        ->searchable(),
                ]),
            ])
            ->contentGrid([
                '2xl' => 3,
                'xl' => 3,
                'lg' => 3,
                'md' => 2,
                'sm' => 2,
                'xs' => 1,
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Visit link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($record) => route('templates.show', ['slug' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->modalHeading('Delete')
                        ->modalDescription('Are you sure you want to delete?')
                        ->modalSubmitActionLabel('Delete')
                        ->successNotification(null)
                        ->after(function ($livewire) {
                            Notification::make()
                                ->success()
                                ->icon('heroicon-s-check-circle')
                                ->title('Sucessfully')
                                ->body('Templates deleted successfully')
                                ->send();

                            $livewire->resetTable();
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->isWO()) {
            $query->whereHas('event', function (Builder $query) {
                $query->where('name', 'ILIKE', '%wedding%')
                    ->orWhere('name', 'ILIKE', '%nikah%');
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplates::route('/create'),
            'edit' => Pages\EditTemplates::route('/{record}/edit'),
        ];
    }
}
