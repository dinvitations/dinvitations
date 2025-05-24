<?php

namespace App\Filament\Resources\AdminsResource\Pages;

use App\Filament\Resources\AdminsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminsResource::class;

    protected static ?string $title = 'Admin';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Admin'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function paginateTableQuery(Builder $query): Paginator|CursorPaginator
    {
        return $query->simplePaginate(
            perPage: ($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage(),
            pageName: $this->getTablePaginationPageName(),
        );
    }
}
