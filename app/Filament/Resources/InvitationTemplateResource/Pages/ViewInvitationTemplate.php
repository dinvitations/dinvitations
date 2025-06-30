<?php

namespace App\Filament\Resources\InvitationTemplateResource\Pages;

use App\Filament\Resources\InvitationTemplateResource;
use App\Livewire\ShowTemplates;
use Doctrine\DBAL\Schema\View;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInvitationTemplate extends ViewRecord
{
    protected static string $resource = InvitationTemplateResource::class;

    protected static ?string $breadcrumb = 'Template Details';
    protected static ?string $title = 'Templates';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Template')
                ->url(fn($record) => InvitationTemplateResource::getUrl('edit', [
                    'record' => $record,
                    'template_id' => request()->query('template_id'),
                ])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->suffixAction(
                                        Action::make('preview')
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->url(fn ($record) => route('invitation.show', ['slug' => $record->slug]))
                                            ->openUrlInNewTab()
                                    ),
                                TextEntry::make('published_at')
                                    ->label('Published at')
                                    ->dateTime('M d, Y')
                                    ->badge()
                                    ->color('success'),
                            ]),
                    ]),
                Section::make()
                    ->schema([
                        ViewEntry::make('template')
                            ->label('Template Builder')
                            ->view('livewire.embed.show-invitation')
                            ->viewData(function ($record) {
                                return ['slug' => $record->slug];
                            }),
                    ])
            ]);
    }
}
