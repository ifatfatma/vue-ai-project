<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    use HasFactory;
    protected $table = 'seo_settings';
    protected $fillable =  [
        'raw_meta_tags', 
        'meta_description',
        'meta_keywords',
        'tools',
        'og_image',
    ];

    protected $casts = [
        'tools' => 'array',
    ];
}