<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    use BelongsToTenant;

    protected $table = 'kb_articles';

    protected $fillable = ['title', 'slug', 'body', 'category_id', 'published'];

    protected $casts = ['published' => 'boolean'];
}
