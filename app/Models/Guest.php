<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'type_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitationGuests()
    {
        return $this->hasMany(InvitationGuest::class);
    }
}
