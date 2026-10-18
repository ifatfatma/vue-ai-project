<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Prompt extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'id', 'category_id', 'ai_tool', 'title', 'label', 'prompt_text', 'status', 'copies_count', 'image', 'created_at', 'updated_at', 'user_id', 'deleted_at', 'frontend_user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getRatingAttribute()
    {
        $copies = $this->copies_count ?? 0;

        if($copies >= 50) return 5;
        if($copies >= 20) return 4;
        if($copies >= 10) return 3;
        if($copies >= 5) return 2;
        if($copies > 0) return 1;

        return 0;

    }

    protected $casts = [
    'ai_tool' => 'array',
];

    
}