<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedPrompt extends Model
{
    protected $table = 'saved_prompts';

    protected $fillable = [
        'user_id',
        'frontend_user_id',
        'prompt_id',
    ];

    /**
     * Existing Admin/legacy user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Frontend user.
     */
    public function frontendUser()
    {
        return $this->belongsTo(FrontendUser::class, 'frontend_user_id');
    }

    public function prompt()
    {
        return $this->belongsTo(Prompt::class, 'prompt_id');
    }
}