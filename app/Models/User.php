<?php

namespace App\Models;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Exception;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;
    use SoftCascadeTrait;
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function booted()
    {
        static::deleting(function ($user) {
            if (!$user->isForceDeleting()) {
                $user->email = null;
                $user->email_verified_at = null;
                $user->saveQuietly();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organizer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $softCascade = ['orders'];

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function sendEmailVerificationNotification(): void
    {
        if ($this->hasVerifiedEmail()) {
            return;
        }

        if (!method_exists($this, 'notify')) {
            $userClass = $this::class;

            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = app(VerifyEmail::class);
        $notification->url = Filament::getVerifyEmailUrl($this);

        $this->notify($notification);
    }

    /**
     * Allow all users to access the Filament panel.
     * See https://filamentphp.com/docs/3.x/panels/installation#allowing-users-to-access-a-panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function organizer()
    {
        return $this->belongsTo(__CLASS__, 'organizer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check whether the user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole(Role::ROLES['manager']);
    }

    /**
     * Check whether the user is an organizer
     */
    public function isOrganizer(?string $role = null): bool
    {
        if (!empty($role))
            return $this->hasRole($role);

        return $this->hasAnyRole([
            Role::ROLES['event_organizer'],
            Role::ROLES['wedding_organizer'],
        ]);
    }

    /**
     * Check whether the user is Wedding Organizer
     */
    public function isWO(): bool
    {
        return $this->hasRole(Role::ROLES['wedding_organizer']);
    }

    /**
     * Check whether the user is a client
     */
    public function isClient(): bool
    {
        return $this->hasRole(Role::ROLES['client']);
    }
}
