<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeactivateExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate orders whose invitation has ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $updated = Order::whereHas('invitation', function ($query) use ($now) {
            $query->where('date_end', '<', $now);
        })
        ->where('status', 'active')
        ->update([
            'status' => 'inactive'
        ]);

        $this->info("Updated $updated orders to inactive.");
    }
}
