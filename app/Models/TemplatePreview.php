<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TemplatePreview extends Model
{
    /** @use HasFactory<\Database\Factories\TemplatePreviewFactory> */
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'file_id',
        'type',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function scopeWeb($query)
    {
        return $query->where('type', 'web');
    }

    public function scopeMobile($query)
    {
        return $query->where('type', 'mobile');
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
