<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestsResource\Pages;
use App\Models\Guest;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class GuestsResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Manage';
    protected static ?int $navigationSort = 3;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuests::route('/')
        ];
    }
}
