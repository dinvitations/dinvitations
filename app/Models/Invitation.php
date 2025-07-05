<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invitation extends Model
{
    /** @use HasFactory<\Database\Factories\InvitationFactory> */
    use HasFactory;
    use SoftDeletes;
    use SoftCascadeTrait;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'template_id',
        'event_name',
        'organizer_name',
        'slug',
        'date_start',
        'date_end',
        'phone_number',
        'souvenir_stock',
        'total_seats',
        'message',
        'location',
        'location_latlng',
        'published_at',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $softCascade = ['guests'];

    public const MESSAGE = <<<'HTML'
            <p>📩 You're Invited!<br><br>
            Hi [Guest Name], kami mengundang Anda untuk hadir di acara [Event Name] yang akan kami selenggarakan.<br><br>
            📆 Tanggal: [Start Date] s/d [End Date]<br>
            ⏰ Waktu: [Start Time] s/d [End Time]&nbsp;<br>
            📍 Lokasi: [Event Location]<br><br>
            Untuk melihat detail undangan dan konfirmasi kehadiran Anda, silakan buka tautan berikut: &nbsp;<br>
            🔗 [Link Invitation]<br><br>
            Kehadiran Anda akan menjadi kehormatan bagi kami. &nbsp;<br>
            Sampai jumpa di hari istimewa ini! 💐<br><br>
            Salam hangat, &nbsp;<br>
            [Organizer’s Name]</p>
        HTML;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function views()
    {
        return $this->hasMany(InvitationTemplateView::class);
    }

    public function guests()
    {
        return $this->hasMany(InvitationGuest::class);
    }

    public function availableSouvenirStock(): int
    {
        return max($this->souvenir_stock - $this->guests()->whereNotNull('souvenir_at')->count(), 0);
    }
    
    public function isSouvenirLocked(): bool
    {
        return $this->guests()->whereNotNull('souvenir_at')->exists();
    }

    public function availableSeats(): int
    {
        return max($this->total_seats - $this->guests()->whereNotNull('attended_at')->whereNull('left_at')->sum('guest_count'), 0);
    }
}
