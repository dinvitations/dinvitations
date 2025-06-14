<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'price',
        'description',
    ];

    public const NAMES = [
        'basic' => 'Basic',
        'medium' => 'Medium',
        'premium' => 'Premium',
        'luxury' => 'Luxury',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class)
            ->using(FeaturePackage::class)
            ->withTimestamps();
    }
}
