<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    
    protected $fillable = ['name', 'display_name', 'description', 'active', 'area_id'];


    protected $casts = [
         'active' => 'boolean',
    ];

    protected $appends = [
        'ier_name',
        'ier__display_name',
        'area_name',
        'area_display_name',
    ];

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

     
     public function iers()
{
    return $this->morphToMany(Ier::class, 'assignable', 'iers_assignables', 'assignable_id', 'ier_id')
                ->withTimestamps();
}


    public function getIerNameAttribute()
    {
        return $this->iers()->limit(1)->first()?->name ?? null;
    }

      public function getIerDisplayNameAttribute()
    {
        return $this->iers()->limit(1)->first()?->display_name ?? null;
    }

      public function getIerIdttribute()
    {
        return $this->iers()->limit(1)->first()?->id ?? null;
    }

    
}
