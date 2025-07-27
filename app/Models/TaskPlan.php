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




    protected $casts = [
        'days' => 'array',
    ];

    public function tasks()
{
    return $this->hasMany(Task::class, 'task_plan_id');
}


}
