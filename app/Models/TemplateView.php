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

    public const TYPE = [
        'html' => [
            'filename' => 'template.html',
            'extension' => 'html',
            'mime' => 'text/html',
        ],
        'css' => [
            'filename' => 'style.css',
            'extension' => 'css',
            'mime' => 'text/css',
        ],
        'js' => [
            'filename' => 'script.js',
            'extension' => 'js',
            'mime' => 'application/javascript',
        ],
        'grapesjs' => [
            'projectData' => [
                'filename' => 'project-data.json',
                'extension' => 'json',
                'mime' => 'application/json',
            ],
            'components' => [
                'filename' => 'components.json',
                'extension' => 'json',
                'mime' => 'application/json',
            ],
            'style' => [
                'filename' => 'style.json',
                'extension' => 'json',
                'mime' => 'application/json',
            ],
        ],
    ];

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

    public static function getTypes(): array
    {
        $flat = [];

        foreach (self::TYPE as $key => $value) {
            if (is_array($value) && isset($value['filename'])) {
                $flat[$key] = $value;
            } else {
                foreach ($value as $subKey => $meta) {
                    $flat["{$key}.{$subKey}"] = $meta;
                }
            }
        }

        return $flat;
    }

}
