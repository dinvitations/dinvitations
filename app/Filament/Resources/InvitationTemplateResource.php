<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvitationTemplateResource\Pages;
use App\Models\Invitation;
use App\Models\Template;
use App\Models\TemplateView;
use App\Support\Constants;
use Dotswan\FilamentGrapesjs\Fields\GrapesJs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\TextUI\Configuration\Constant;

class InvitationTemplateResource extends Resource
{
    protected static ?string $model = Invitation::class;
    protected static ?string $modelLabel = 'Template';
    protected static ?string $pluralModelLabel = 'Templates';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 1;

    protected static ?string $breadcrumb = 'Template';

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
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Hidden::make('template_id')
                                    ->formatStateUsing(fn() => request()->query('template_id')),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->formatStateUsing(fn($record) => $record->slug ?? Str::slug($record->event_name))
                                    ->required()
                                    ->notIn(Constants::get('MENU', 'slug'))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Published at')
                                    ->required(),
                            ])
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        GrapesJs::make('template_builder')
                            ->label('Template Builder')
                            ->dehydrated(false)
                            ->afterStateHydrated(function (GrapesJs $component, ?Invitation $record) {
                                $templateId = request()->query('template_id');
                                if (!$templateId) {
                                    return;
                                }

                                $template = Template::find($templateId);
                                if (!$template) {
                                    return;
                                }

                                $useRecordViews = $record && $record->template_id === $template->id && $record->views()->exists();

                                $cacheKey = $useRecordViews
                                    ? "invitation_view_data_{$record->id}"
                                    : "template_view_data_{$template->id}";

                                $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($useRecordViews, $record, $template) {
                                    $types = array_keys(TemplateView::getTypes());

                                    $views = ($useRecordViews ? $record->views() : $template->views())
                                        ->with('file')
                                        ->whereIn('type', $types)
                                        ->get()
                                        ->keyBy('type');

                                    $getContent = function ($view): string {
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
                                'grapesjs-component-countdown',
                                'grapesjs-dinvitations',
                                'grapesjs-navbar',
                                'grapesjs-parser-postcss',
                                'grapesjs-plugin-forms',
                                'grapesjs-rte-extensions',
                                'grapesjs-tabs',
                                'grapesjs-tooltip',
                                'grapesjs-typed',
                                'grapesjs-touch',
                            ])
                            ->settings([
                                'disableDrag' => true,
                                'assetManager' => [
                                    'upload' => route('grapesjs.upload'),
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
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $query = Template::query();
        if (auth()->user()->organizer->isWO()) {
            $query->whereHas('event', function (Builder $query) {
                $query->where('name', 'ILIKE', '%wedding%')
                    ->orWhere('name', 'ILIKE', '%nikah%');
            });
        }

        return $table
            ->query($query)
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
                Tables\Actions\ViewAction::make('view')
                    ->label('Visit link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($record) => route('templates.show', ['slug' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('choose')
                    ->icon('heroicon-s-pencil-square')
                    ->label(function ($record) {
                        $invitation = Invitation::whereHas('order', function ($query) {
                                $query->where('status', 'active')
                                    ->where('user_id', auth()->user()->id);
                            })
                            ->first();

                        if ($invitation && $invitation?->template_id === $record->id) {
                            return 'Choosed';
                        }

                        return 'Choose';
                    })
                    ->visible(function () {
                        return Invitation::whereHas('order', function ($query) {
                            $query->where('status', 'active')
                                ->where('user_id', auth()->user()->id);
                        })->exists();
                    })
                    ->url(function ($record) {
                        $invitation = Invitation::whereHas('order', function ($query) {
                            $query->where('status', 'active')
                                ->where('user_id', auth()->user()->id);
                        })->first();

                        if (!$invitation) {
                            abort(Response::HTTP_NOT_FOUND, 'No active invitation found for this user.');
                        }

                        if ($invitation->published_at && $invitation->template_id === $record->id) {
                            return InvitationTemplateResource::getUrl('view', [
                                'record' => $invitation,
                                'template_id' => $record->id,
                            ]);
                        }

                        return InvitationTemplateResource::getUrl('edit', [
                            'record' => $invitation,
                            'template_id' => $record->id,
                        ]);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvitationTemplates::route('/'),
            'edit' => Pages\EditInvitationTemplate::route('/{record}/edit'),
            'view' => Pages\ViewInvitationTemplate::route('/{record}/view'),
        ];
    }
}
