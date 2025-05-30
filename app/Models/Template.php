<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    /** @use HasFactory<\Database\Factories\TemplateFactory> */
    use HasFactory;
    use SoftDeletes;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'package_id',
        'event_id',
        'preview_url'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function views()
    {
        return $this->hasMany(TemplateView::class);
    }

    public function previews()
    {
        return $this->hasMany(TemplatePreview::class);
    }

    public function getViewHtmlAttribute()
    {
        return $this->views()->html()->first();
    }

    public function getPreviewWebAttribute()
    {
        return $this->previews()->web()->first();
    }

    public function getPreviewMobileAttribute()
    {
        return $this->previews()->mobile()->first();
    }
}
