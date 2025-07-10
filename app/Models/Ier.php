<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Ier extends Model
{
    
 protected $appends = ['plant_name','plant_display_name'];
    
    protected $fillable = ['name', 'display_name', 'description', 'active'];

    
    

    public function plant()
    {
        return $this->morphedByMany(Plant::class, 'assignable', 'iers_assignables', 'ier_id', 'assignable_id')
                    ->withTimestamps();
    }
   public function files(): MorphToMany
    {
        return $this->morphToMany(File::class, 'assignable', 'files_assignables', 'assignable_id', 'file_id')
                    ->withTimestamps();
    }

    public function getPlantNameAttribute()
    {
        return $this->plant()->limit(1)->first()?->name;
    }

     public function getPlantDisplayNameAttribute()
    {
        return $this->plant()->limit(1)->first()?->display_name;
    }

    
}
