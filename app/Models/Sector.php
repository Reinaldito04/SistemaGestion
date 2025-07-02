<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
     
    protected $fillable = ['name', 'display_name', 'description', 'active', 'plant_id'];


    protected $casts = [
         'active' => 'boolean',
    ];

    protected $appends = ['plant_name','plant_display_name','area_name','area_display_name'];
    /**
     * Get the plant that owns the sector.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

   public function getPlantNameAttribute()
    {
        return $this->plant ? $this->plant->name : null;
    }
    public function getPlantDisplayNameAttribute()
     {
          return $this->plant ? $this->plant->display_name : null;
     }

    public function getAreaNameAttribute()
     {
          return $this->plant ? $this->plant->area_name : null;
     }

   public function getAreaDisplayNameAttribute()
    {
        return $this->plant ? $this->plant->area_display_name : null;
    }

}
