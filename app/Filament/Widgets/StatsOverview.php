<?php

namespace App\Filament\Widgets;

use App\Models\InvitationGuest;
use App\Models\Order;
use App\Models\Template;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user->hasRole('client')) {
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
            $stats = [
                Stat::make('Total Customers', User::role('client')->count()),
                Stat::make('Total Templates', Template::count()),
                Stat::make('Total Orders', Order::count()),
            ];
        }
        return $stats;
    }
}
