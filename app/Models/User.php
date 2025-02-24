<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($user) {
            $maxAttempts = 5;
            $attempts = 0;
            $randomLength = 8;
            $maxLength = 12;

            do {
                $possibleCombinations = pow(36, $randomLength); // 36 because we use 0-9 and A-Z, no lower case
                $existingCodes = self::whereRaw('LENGTH(code) = ?', [$randomLength])->count();

                if ($existingCodes >= $possibleCombinations) {
                    if ($randomLength >= $maxLength) {
                        throw new \Exception('All possible combinations are used');
                    }
                    $randomLength++;
                    $attempts = 0;
                    continue;
                }
                
                $code = strtoupper(Str::random($randomLength));

                //check if code already exists
                $exists = self::where('code', $code)->exists();

                if (!$exists) {
                    $user->code = $code;
                    break;
                }
                $attempts++;

                if ($attempts >= $maxAttempts) {
                    if ($randomLength >= $maxLength) {
                        throw new \Exception('Could not generate unique code');
                    }
                    $randomLength++;
                    $attempts = 0;
                }
            } while (true);
        });
    }

    public function sentInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'inviter_user_id');
    }

    public function receivedInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'invited_user_id');
    }

    public function householdRequests(): HasMany
    {
        return $this->hasMany(HouseholdRequest::class, 'requesting_user_id');
    }

    public function households(): BelongsToMany
    {
        return $this->belongsToMany(Household::class);
    }

    public function ownedHouseholds(): HasMany
    {
        return $this->hasMany(Household::class, 'owner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function shoppinglists(): HasMany
    {
        return $this->hasMany(Shoppinglist::class);
    }

    public function isOwner(Household $household): bool
    {
        return $this->id === $household->owner_id;
    }
}
