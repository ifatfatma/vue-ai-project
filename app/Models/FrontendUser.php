<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class FrontendUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'frontend_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
        'otp',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];

    /**
     * Frontend user's saved prompts.
     */
    public function savedPrompts(): BelongsToMany
    {
        return $this->belongsToMany(
            Prompt::class,
            'saved_prompts',
            'frontend_user_id',
            'prompt_id'
        )->withTimestamps();
    }

    /**
     * Prompts created by this frontend user.
     */
    public function prompts()
    {
        return $this->hasMany(Prompt::class, 'frontend_user_id');
    }
}