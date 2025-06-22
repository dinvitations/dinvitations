<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestGroup extends Model
{
    /** @use HasFactory<\Database\Factories\GuestGroupFactory> */
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use SoftCascadeTrait;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $softCascade = ['guests'];

    protected $fillable = [
        'id',
        'customer_id',
        'name',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }
}
