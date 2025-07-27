<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskPlan extends Model
{
    protected $fillable = [
        'title',
        'description',
        'activity_title',
        'activity_description',
        'created_by',
        'audited_by',
        'article_id',
        'sector_id',
        'frequency',      // daily, weekly, monthly, yearly
        'days',           // JSON: días de la semana o del mes
        'deadline_time',  // Hora exacta de cierre
        'start_at',       // Fecha de inicio de vigencia del plan
        'end_at',         // Fecha de fin de vigencia del plan
        'is_active',      // Booleano para activar/desactivar el plan
    ];



    protected $appends = [
        'sector_name', 'sector_display_name', 'plant_name', 'plant_display_name', 'area_display_name',
        'ier_display_name', 'ier_name', 'creator_name','task_count',
    ];

    protected $casts = [
        'days' => 'array',
    ];

    public function tasks()
{
    return $this->hasMany(Task::class, 'task_plan_id');
}


public function participants()
{
    return $this->belongsToMany(User::class, 'task_plan_user');
}

  public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function getSectorNameAttribute()
    {
        return $this->sector ? $this->sector->name : null;
    }

       public function getSectorDisplayNameAttribute()
    {
        return $this->sector ? $this->sector->display_name : null;
    }

        public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

        public function getCreatorNameAttribute()
    {
        return $this->creator?->name ?? null;
    }

       public function getPlantNameAttribute()
    {
        return $this->sector && $this->sector->plant
            ? $this->sector->plant->name
            : null;
    }

      public function getPlantDisplayNameAttribute()
    {
        return $this->sector && $this->sector->plant
            ? $this->sector->plant->display_name
            : null;
    }

     public function getAreaNameAttribute()
    {
        return $this->sector && $this->sector->plant && $this->sector->plant->area
            ? $this->sector->plant->area->name
            : null;
    }

     public function getAreaDisplayNameAttribute()
    {
        return $this->sector && $this->sector->plant && $this->sector->plant->area
            ? $this->sector->plant->area->display_name
            : null;
    }

     public function getIerNameAttribute()
    {
        return $this->sector && $this->sector->plant
            ? $this->sector->plant->iers()->limit(1)->first()?->name ?? null
            : null;
    }

     public function getIerDisplayNameAttribute()
    {
        return $this->sector && $this->sector->plant
            ? $this->sector->plant->iers()->limit(1)->first()?->display_name ?? null
            : null;
    }
    
    public function getTaskCountAttribute()
    {
        // Puedes usar count() para contar las tareas relacionadas
        return $this->tasks()->count();
    }




}
