<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'name',
        'original_name',
        'filename',
        'path',
        'disk',
        'extension',
        'type',
        'size',
        'mime_type',
        'status',
        'visibility',
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): ?string
    {
        if ($this->visibility === 'public' && Storage::disk($this->disk)->exists($this->path)) {
            return Storage::disk($this->disk)->url($this->path);
        }

        return null;
    }

    public function getSizeForHumansAttribute(): string
    {
        if (!$this->size) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($this->size, 1024));
        return round($this->size / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    public function getFullNameAttribute(): string
    {
        return $this->filename . ($this->extension ? '.' . $this->extension : '');
    }
}
