<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\Tests\Unit\Models\Enums\StatusEnum;

class ArticleWithEnum extends Model
{
    use CrudTrait;

    protected $table = 'articles';
    protected $fillable = ['user_id', 'content', 'metas', 'tags', 'extras', 'cast_metas', 'cast_tags', 'cast_extras', 'status'];
    protected $casts = [
        'cast_metas'  => 'object',
        'cast_tags'   => 'object',
        'cast_extras' => 'object',
        'status' => StatusEnum::class
    ];

    /**
     * Get the author for the article.
     */
    public function user()
    {
        return $this->belongsTo('Backpack\CRUD\Tests\Unit\Models\User');
    }

    public function getContentComposedAttribute()
    {
        return $this->content.'++';
    }
}
