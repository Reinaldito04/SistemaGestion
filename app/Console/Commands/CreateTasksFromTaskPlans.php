<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TaskPlan;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateTasksFromTaskPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-tasks-from-task-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea tareas desde TaskPlans activos según su frecuencia y vigencia';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now('America/Caracas');

        $plans = TaskPlan::where('is_active', true)
            ->where('start_at', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->get();



        foreach ($plans as $plan) {
            if (!$this->shouldCreateTask($plan, $now)) {
                continue;
            }

            // Verifica que no exista ya un Task creado hoy para este plan y deadline
            $exists = Task::where('task_plan_id', $plan->id)
                ->whereDate('deadline_at', $this->getDeadlineAt($plan, $now)->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            // Crea el task y asigna participantes
            DB::transaction(function () use ($plan, $now) {
                $task = Task::create([
                    'title' => $plan->activity_title,
                    'description' => $plan->activity_description,
                    'created_by' => $plan->created_by,
                    'audited_by' => $plan->audited_by,
                    'article_id' => $plan->article_id,
                    'sector_id' => $plan->sector_id,
                    'deadline_at' => $this->getDeadlineAt($plan, $now),
                    'task_plan_id' => $plan->id,
                ]);

                // Copia los participantes del plan a la tarea
                $task->participants()->sync($plan->participants->pluck('id')->toArray());

                $this->info("Task creado para el plan #{$plan->id} con deadline {$task->deadline_at}");
            });
        }
    }

    /**
     * Decide si corresponde crear un Task según la frecuencia y el día.
     */
    protected function shouldCreateTask(TaskPlan $plan, Carbon $now)
    {
        $frequency = $plan->frequency;
        $days = $plan->days ?: [];

        if ($frequency === 'daily') {
            return true;
        }

        if ($frequency === 'weekly') {
            // Carbon: 0 = Sunday, 1 = Monday, ...
            return in_array($now->dayOfWeek, $days);
        }

        if ($frequency === 'monthly') {
            $day = $now->day;
            $lastDay = $now->copy()->endOfMonth()->day;
            $validDays = [];

            foreach ($days as $d) {
                if (is_numeric($d)) {
                    $validDays[] = intval($d);
                } elseif (is_string($d) && preg_match('/^last(-(\d+))?$/', $d, $m)) {
                    // Ej: last, last-1, last-2...
                    $offset = isset($m[2]) ? intval($m[2]) : 0;
                    $validDays[] = $lastDay - $offset;
                }
            }
            return in_array($day, $validDays);
        }

        // yearly (no implementado)
        return false;
    }

    /**
     * Calcula el deadline_at para el Task según el plan y el día.
     */
    protected function getDeadlineAt($plan, Carbon $now)
    {
        // Usa la fecha de hoy y la hora de deadline_time del plan
        return Carbon::parse($now->toDateString() . ' ' . $plan->deadline_time);
    }
}