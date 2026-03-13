<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    /**
     * Lists owned by this user.
     */
    public function groceryLists(): HasMany
    {
        return $this->hasMany(GroceryList::class, 'user_id');
    }

    /**
     * Lists shared with this user (read/write access).
     */
    public function sharedLists(): BelongsToMany
    {
        return $this->belongsToMany(GroceryList::class, 'list_user', 'user_id', 'list_id')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ListPayment::class);
    }

    /**
     * All lists the user can access (owned + shared).
     */
    public function accessibleLists()
    {
        $owned = $this->groceryLists();
        $shared = $this->sharedLists();
        return GroceryList::query()
            ->where('user_id', $this->id)
            ->orWhereIn('id', $shared->pluck('lists.id'));
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    /**
     * FCM tokens for this user.
     */
    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }
}
