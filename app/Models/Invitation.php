<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    /** @use HasFactory<\Database\Factories\InvitationFactory> */
    use HasFactory;
    use SoftDeletes;
    use SoftCascadeTrait;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'template_id',
        'name',
        'slug',
        'date_start',
        'date_end',
        'whatsapp_message',
        'location',
        'location_latlong',
        'published_at',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $softCascade = ['guests'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function guests()
    {
        return $this->hasMany(InvitationGuest::class);
    }
}
