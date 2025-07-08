<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleType extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'active'];
    
}
