<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    
    protected $fillable = ['name', 'display_name', 'description', 'active', 'area_id'];


    protected $casts = [
         'active' => 'boolean',
    ];

    protected $appends = ['area_name', 'area_display_name'];
    /**
     * Get the area that owns the plant.
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

   public function getAreaNameAttribute()
    {
        return $this->area ? $this->area->name : null;
    }
    public function getAreaDisplayNameAttribute()
     {
          return $this->area ? $this->area->display_name : null;
     }
    
}
