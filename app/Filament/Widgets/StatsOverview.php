<?php

namespace App\Filament\Widgets;

use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Models\Order;
use App\Models\Role;
use App\Models\Template;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->hasRole(Role::ROLES['client'])) {
            $totalInvitedGuests = $totalAttendingGuests = $attendanceRate = $availableSeats = $souvenirStock = 0;
    
            $invitation = Invitation::query()
                ->whereHas('order', function (Builder $query) use ($user) {
                    $query->where('status', 'active');
                    $query->where('user_id', $user->id);
                })
                ->whereNotNull('published_at')
                ->first();

            if ($invitation) {
                $totalInvitedGuests = InvitationGuest::query()
                    ->where('invitation_id', $invitation->id)
                    ->count();

                $totalAttendingGuests = InvitationGuest::query()
                    ->where('invitation_id', $invitation->id)
                    ->whereNotNull('attended_at')
                    ->count();

                $attendanceRate = percent($totalAttendingGuests, $totalInvitedGuests);

                $availableSeats = $invitation->availableSeats();
                $availableSeats = "{$availableSeats}/{$invitation->total_seats}";

                $availableSouvenirStock = $invitation->availableSouvenirStock();
                $souvenirStock = "{$availableSouvenirStock}/{$invitation->souvenir_stock}";
            }

            $stats = [
                Stat::make('Total Invited Guests', $totalInvitedGuests),
                Stat::make('Total Attending Guests', $totalAttendingGuests),
                // Stat::make('Attendance Rate', $attendanceRate),
                Stat::make('Available Seats', $availableSeats),
                Stat::make('Souvenir Stock', $souvenirStock),
            ];
        } else {
            $totalCustomers = User::role(Role::ROLES['client'])
                ->when(auth()->user()->isOrganizer(), function (Builder $query) {
                    $query->whereRelation('organizer', 'id', auth()->user()->id);
                })
                ->count();

            $totalTemplates = Template::when(auth()->user()->isWO(), function (Builder $query) {
                    $query->whereHas('event', function (Builder $query) {
                        $query->where('name', 'ILIKE', '%wedding%')
                            ->orWhere('name', 'ILIKE', '%nikah%');
                    });
                })
                ->count();

            $totalOrders = Order::when(auth()->user()->isOrganizer(), function (Builder $query) {
                    $query->whereRelation('customer.organizer', 'id', auth()->user()->id);
                })
                ->count();

            $stats = [
                Stat::make('Total Customers', $totalCustomers),
                Stat::make('Total Templates', $totalTemplates),
                Stat::make('Total Orders', $totalOrders),
            ];
        }
        return $stats;
    }
}
