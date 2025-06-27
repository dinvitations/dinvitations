<?php

namespace App\Filament\Widgets;

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
            $totalInvitedGuests = InvitationGuest::query()
                ->whereHas('invitation', function ($query) use ($user) {
                    $query->whereNotNull('published_at')
                        ->whereHas('order', function ($subQuery) use ($user) {
                            $subQuery->where('status', 'active');
                            $subQuery->where('user_id', $user->id);
                        }, '=', 1);
                })
                ->count();

            $totalAttendingGuests = InvitationGuest::query()
                ->whereHas('invitation', function ($query) use ($user) {
                    $query->whereNotNull('published_at')
                        ->whereHas('order', function ($subQuery) use ($user) {
                            $subQuery->where('status', 'active');
                            $subQuery->where('user_id', $user->id);
                        }, '=', 1);
                })
                ->whereNotNull('attended_at')
                ->count();

            $attendanceRate = percent($totalAttendingGuests, $totalInvitedGuests);

            $stats = [
                Stat::make('Total Invited Guests', $totalInvitedGuests),
                Stat::make('Total Attending Guests', $totalAttendingGuests),
                Stat::make('Attendance Rate', $attendanceRate),
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
