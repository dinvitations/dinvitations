<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplatesResource\Pages;
use App\Models\Event;
use App\Models\Template;
use Dotswan\FilamentGrapesjs\Fields\GrapesJs;
use Filament\Forms;
use Filament\Actions\StaticAction;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TemplatesResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?int $navigationSort = 1;

    public static ?string $breadcrumb = 'Templates';

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
                                if ($record) {
                                    $cacheKey = "template_html_{$record->id}";
                                    $html = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($record) {
                                        $view = $record->viewHtml;

                                        if (
                                            !$view ||
                                            !$view->file ||
                                            !Storage::disk($view->file->disk)->exists($view->file->path)
                                        ) {
                                            return '';
                                        }

                                        $html = Storage::disk($view->file->disk)->get($view->file->path);
                                        return $html;
                                    });
                                    $component->state($html);
                                }
                            })
                            ->plugins([
                                'grapesjs-tailwind',
                                'gjs-blocks-basic',
                                'grapesjs-dinvitations'
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
                        ->disk('minio')
                        ->visibility('private')
                        ->width('100%')
                        ->height('auto')
                        ->defaultImageUrl('https://placehold.co/640x480'),
                    Tables\Columns\TextColumn::make('name')
                        ->label('Name')
                        ->weight('bold')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('slug')
                        ->label('Slug')
                        ->formatStateUsing(function ($record) {
                            $domain = request()->getHost();
                            return Str::limit($domain . '/' . $record->slug, 35, '...');
                        })
                        ->tooltip(function ($record) {
                            $domain = request()->getHost();
                            return "{$domain}/{$record->slug}";
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
                    ->url(fn($record) => URL::route('templates.show', ['slug' => $record->slug]))
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplates::route('/create'),
            'edit' => Pages\EditTemplates::route('/{record}/edit'),
        ];
    }
}
