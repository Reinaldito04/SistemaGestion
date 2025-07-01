<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'active'];
    protected $casts = [
        'active' => 'boolean',
    ];
    /**
     * Get the plants associated with the area.
     */
    public function plants()
    {
        return $this->hasMany(Plant::class);
    }

}
