<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvitationGuest extends Model
{
    /** @use HasFactory<\Database\Factories\InvitationGuestFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'guest_id',
        'invitation_id',
        'type',
        'rsvp',
        'qr_code_path',
        'souvenir_qr_path',
        'attended_at',
        'souvenir_at',
        'selfie_at',
        'left_at',
        'guest_count',
    ];

    protected $casts = [
        'rsvp' => 'boolean',
        'attended_at' => 'datetime',
        'souvenir_at' => 'datetime',
        'selfie_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}

