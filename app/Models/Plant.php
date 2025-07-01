<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    
    protected $fillable = ['name', 'display_name', 'description', 'active', 'area_id'];


    protected $casts = [
         'active' => 'boolean',
    ];
    /**
     * Get the area that owns the plant.
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
