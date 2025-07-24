<?php

namespace App\Support;

use Illuminate\Support\Collection;

class Constants
{
    public const MENU = [
        ['slug' => 'templates', 'name' => 'Templates', 'type' => 'admin'],
        ['slug' => 'orders', 'name' => 'Orders', 'type' => 'admin'],
        ['slug' => 'customers', 'name' => 'Customers', 'type' => 'admin'],
        ['slug' => 'admins', 'name' => 'Admins', 'type' => 'admin'],
        ['slug' => 'events', 'name' => 'Event Categories', 'type' => 'admin'],
        ['slug' => 'packages', 'name' => 'Package Categories', 'type' => 'admin'],

        ['slug' => 'invitations', 'name' => 'Event Details', 'type' => 'client'],
        ['slug' => 'invitation-templates', 'name' => 'Templates', 'type' => 'client'],
        ['slug' => 'guests', 'name' => 'Guests', 'type' => 'client'],
        ['slug' => 'history-orders', 'name' => 'History Orders', 'type' => 'client'],
        ['slug' => 'selfie', 'name' => 'Selfie', 'type' => 'client'],

        ['slug' => 'dashboard', 'name' => 'Dashboard', 'type' => 'both'],

        ['slug' => 'template-views', 'name' => 'Template Views', 'type' => 'global'],
        ['slug' => 'api', 'name' => 'API', 'type' => 'global'],
        ['slug' => 'telescope', 'name' => 'Telescope', 'type' => 'global'],
    ];

    public static function get(string $name, ?string $pluck = null): Collection | array
    {
        $const = collect(constant("self::{$name}"));
        return $pluck ? $const->pluck($pluck)->unique()->all() : $const;
    }
}
