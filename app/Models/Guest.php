<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;
    use SoftDeletes;
    use SoftCascadeTrait;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'guest_group_id',
        'name',
        'phone_number',
        'type_default',
    ];

    protected $softCascade = ['invitationGuests'];

    public function guest_group()
    {
        return $this->belongsTo(GuestGroup::class);
    }

    public function invitationGuests()
    {
        return $this->hasMany(InvitationGuest::class);
    }
}
