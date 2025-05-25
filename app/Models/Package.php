<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
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

    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class)
            ->using(new class extends Pivot {
            use HasUuids;
            })
            ->withTimestamps();
    }
}
