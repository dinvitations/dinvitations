<?php

namespace App\Filament\Clusters\Templates\Pages;

use App\Filament\Clusters\Templates;
use Filament\Pages\Page;

class TemplateBuilder extends Page
{
    protected static ?string $cluster = Templates::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Template Builder';

    protected static ?string $slug = 'builder';

    protected static string $view = 'filament.pages.templates.template-builder';

}
