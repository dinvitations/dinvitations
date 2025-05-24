<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TemplateBuilder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $navigationLabel = 'Template';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.templates.template-builder';

}
