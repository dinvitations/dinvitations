<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'package_id',
        'description',
    ];

    public const NAMES = [
        'wedding' => 'Wedding',
        'open_house' => 'Open House',
        'seminar' => 'Seminar',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
