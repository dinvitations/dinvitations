<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    const FEATURES = [
        'scan' => 'Scan & Redeem Station',
        'greeting' => 'Digital Greeting Wall',
        'selfie' => 'Digital Selfie Station',
    ];

    protected static function booted()
    {
        static::creating(function ($feature) {
            $feature->slug = str($feature->name)->slug();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class)
            ->using(FeaturePackage::class)
            ->withTimestamps();
    }
}
