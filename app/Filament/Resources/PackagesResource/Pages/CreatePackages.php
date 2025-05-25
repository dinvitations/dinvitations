<?php

namespace App\Filament\Resources\PackagesResource\Pages;

use App\Filament\Resources\PackagesResource;
use App\Models\Feature;
use App\Models\Package;
use DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;

class CreatePackages extends CreateRecord
{
    protected static string $resource = PackagesResource::class;

    protected static bool $canCreateAnother = false;

    public static string|Alignment $formActionsAlignment = Alignment::Between;

    protected function handleRecordCreation(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            // Create package
            $package = static::getModel()::create($data);

            // Sync features
            $featureIds = [];
            foreach ($data['features'] ?? [] as $feature) {
                $feature = Feature::firstOrCreate(['name' => $feature]);
                $featureIds[] = $feature->id;
            }
            $package->features()->sync($featureIds);

            return $package->refresh();
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Successfully')
            ->body('Package placed successfully');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction(),
        ];
    }
}
