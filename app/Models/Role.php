<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    const ROLES = [
        'manager' => 'manager',
        'event_organizer' => 'event_organizer',
        'wedding_organizer' => 'wedding_organizer',
        'client' => 'client',
    ];
}
