<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'badge_number',
        'phone',
        'vehicle_number',
        'active',
        'latitude',
        'longitude',
        'location_sharing_enabled',
        'location_updated_at',
        'profile_photo_path',
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
            'otp_verified_at' => 'datetime',
            'location_sharing_enabled' => 'boolean',
            'location_updated_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the URL to the user's profile photo.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(
            fn (): ?string => $this->profile_photo_path
                ? asset('storage/' . $this->profile_photo_path)
                : null,
        );
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'driver_id');
    }

    public function driverFaces(): HasMany
    {
        return $this->hasMany(DriverFace::class, 'driver_id');
    }

    /**
     * Next sequential numeric badge for drivers (max existing all-digit badge + 1), or 1 if none.
     */
    public static function nextDriverBadgeNumber(): string
    {
        return (string) DB::transaction(function () {
            $maxNumeric = static::query()
                ->where('role', 'driver')
                ->lockForUpdate()
                ->pluck('badge_number')
                ->filter(fn ($b) => $b !== null && $b !== '' && ctype_digit(trim((string) $b)))
                ->map(fn ($b) => (int) trim((string) $b))
                ->max();

            return ($maxNumeric ?? 0) + 1;
        });
    }
}
