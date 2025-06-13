<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    use SoftDeletes;
    use SoftCascadeTrait;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'package_id',
        'order_number',
        'status',
        'price',
    ];

    protected $softCascade = ['invitation'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    protected static function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $random = Str::upper(Str::random(6));
        return "ORD-{$date}-{$random}";
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id')->role('client');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function invitation()
    {
        return $this->hasOne(Invitation::class);
    }
}
