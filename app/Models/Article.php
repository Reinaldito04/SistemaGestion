<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'active', 'article_type_id'];
    protected $appends = ['article_type_name','article_type_display_name'];

    public function articleType()
    {
        return $this->belongsTo(ArticleType::class);
    }

    public function getArticleTypeNameAttribute()
    {
        return $this->articleType ? $this->articleType->name : null;
    }

        public function getArticleTypeDisplayNameAttribute()
    {
        return $this->articleType ? $this->articleType->display_name : null;
    }
}
