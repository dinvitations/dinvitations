<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TemplateView extends Model
{
    /** @use HasFactory<\Database\Factories\TemplateViewFactory> */
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'file_id',
        'type',
        'content',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function scopeHtml($query)
    {
        return $query->where('type', 'html');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->file ?
            Storage::disk($this->file->disk)
                ->temporaryUrl(
                    $this->file->path,
                    now()->addMinutes(5)
                )
            : null;
    }

}
