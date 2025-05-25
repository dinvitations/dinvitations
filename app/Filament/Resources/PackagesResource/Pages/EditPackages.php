<?php

namespace App\Filament\Resources\PackagesResource\Pages;

use App\Filament\Resources\PackagesResource;
use App\Models\Feature;
use App\Models\Package;
use Arr;
use DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;

class EditPackages extends EditRecord
{
    protected static string $resource = PackagesResource::class;

    public static string|Alignment $formActionsAlignment = Alignment::Between;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['features'] = $this->record->features->pluck('name')->toArray();
        return $data;
    }

    protected function handleRecordUpdate(Model $package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            // Update package
            $package->update(Arr::except($data, ['features']));

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

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-s-check-circle')
            ->title('Successfully')
            ->body('Package updated successfully');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
            $this->getSubmitFormAction()
                ->label('Update'),
        ];
    }
}
