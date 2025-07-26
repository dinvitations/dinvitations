<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SelfieStationResource\Pages;
use App\Models\Feature;
use App\Models\InvitationGuest;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class SelfieStationResource extends Resource
{
    protected static ?string $model = InvitationGuest::class;
    protected static ?string $modelLabel = 'Selfie Station';
    protected static ?string $pluralModelLabel = 'Selfie Station';

    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationGroup = 'Pro Plan';
    protected static ?int $navigationSort = 1;

    protected static ?string $breadcrumb = 'Selfie Station';

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if (!$user->hasFeature(Feature::FEATURES['selfie'])) {
            return 'Unlock Now!';
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = auth()->user();

        return !$user->hasFeature(Feature::FEATURES['selfie']) ? 'success' : null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isClient();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Attended Guests')
            ->emptyStateHeading(function () {
                $user = auth()->user();
                if (!$user->hasFeature(Feature::FEATURES['selfie'])) {
                    return 'Oops, Locked!';
                }
                return 'No attendance yet';
            })
            ->emptyStateDescription(function () {
                $user = auth()->user();
                if (!$user->hasFeature(Feature::FEATURES['selfie'])) {
                    return 'This page isn’t available for you yet. Upgrade to get full access.';
                }
                return 'Attendance data is not available yet.';
            })
            ->emptyStateIcon(function () {
                $user = auth()->user();
                if (!$user->hasFeature(Feature::FEATURES['selfie'])) {
                    return 'heroicon-o-lock-closed';
                }
                return 'heroicon-o-x-mark';
            })
            ->columns([
                Tables\Columns\ImageColumn::make('selfie_photo_url')
                    ->label('Image')
                    ->disk('minio')
                    ->visibility('private'),

                Tables\Columns\TextColumn::make('selfie_at')
                    ->label('Time')
                    ->dateTime('h:i A')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('guest.name')
                    ->label('Guest')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Category')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'reg' => 'General',
                        'vip' => 'VIP',
                        'vvip' => 'VVIP',
                        default => strtoupper($state),
                    })
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Open')
                    ->modalHeading(fn ($record) => $record->guest->name)
                    ->modalContent(fn ($record) => view('filament.widgets.partials.selfie-station-modal', ['record' => $record]))
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('removeSelfie')
                    ->label('Remove Selfie')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if ($record->selfie_photo_url) {
                                Storage::disk('minio')->delete($record->selfie_photo_url);
                            }

                            $record->update([
                                'selfie_photo_url' => null,
                                'selfie_at' => null,
                            ]);
                        }
                    })
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('invitation', function ($query) {
                $query->whereNotNull('published_at')
                    ->whereHas('order', function ($subQuery) {
                        $subQuery->where('status', 'active')
                            ->where('user_id', auth()->id())
                            ->whereHas('package.features', function ($featureQuery) {
                                $featureQuery->where('name', Feature::FEATURES['selfie']);
                            });
                    }, '=', 1);
            })
            ->whereNotNull(['attended_at', 'selfie_at', 'selfie_photo_url'])
            ->orderByRaw('left_at IS NOT NULL');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSelfieStations::route('/'),
        ];
    }
}
