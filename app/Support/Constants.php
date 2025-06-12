<?php

namespace App\Support;

use Illuminate\Support\Collection;

class Constants
{
    public const MENU = [
        ['slug' => 'dashboard', 'name' => 'Dashboard', 'type' => 'admin'],
        ['slug' => 'templates', 'name' => 'Templates', 'type' => 'admin'],
        ['slug' => 'orders', 'name' => 'Orders', 'type' => 'admin'],
        ['slug' => 'customers', 'name' => 'Customers', 'type' => 'admin'],
        ['slug' => 'admins', 'name' => 'Admins', 'type' => 'admin'],

        ['slug' => 'dashboard', 'name' => 'Dashboard', 'type' => 'client'],
        ['slug' => 'invitations', 'name' => 'Invitations', 'type' => 'client'],
        ['slug' => 'guests', 'name' => 'Guests', 'type' => 'client'],
        ['slug' => 'history-orders', 'name' => 'History Orders', 'type' => 'client'],
    ];

    public static function get(string $name, ?string $pluck = null): Collection | array
    {
        $const = collect(constant("self::{$name}"));
        return $pluck ? $const->pluck($pluck)->unique()->all() : $const;
    }
}
