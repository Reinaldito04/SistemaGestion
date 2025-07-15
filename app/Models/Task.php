<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'created_by',
        'article_id',
        'sector_id',
    ];

    protected $appends = [
        'sector_name',  'sector_display_name','plant_name','plant_display_name','area_display_name','area_display_name',
        'ier_display_name', 'ier_name','status', 'creator_name'
    ];

    protected static function booted()
{
    static::addGlobalScope('withParticipants', function ($query) {
        $query->with('participants');
    });
}

    /**
     * Relación: un task pertenece a un sector.
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Accesor: nombre del sector con validación.
     */
    public function getSectorNameAttribute()
    {
        return $this->sector ? $this->sector->name : null;
    }

       public function getSectorDisplayNameAttribute()
    {
        return $this->sector ? $this->sector->display_name : null;
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

      public function getStatusAttribute()
    {
        $executed = $this->executed_at;
        $approved = $this->approved_at;
        $canceled = $this->canceled_at;

        if (is_null($executed) && is_null($approved) && is_null($canceled)) {
            return 'En proceso';
        }

        if (!is_null($executed) && is_null($approved) && is_null($canceled)) {
            return 'Ejecutado';
        }

        if (!is_null($executed) && !is_null($approved) && is_null($canceled)) {
            return 'Aprobado';
        }

        return 'Indeterminado'; // 🔒 fallback por si no encaja en ningún estado
    }

        public function scopeEnProceso($query)
    {
        return $query->whereNull('executed_at')
                    ->whereNull('approved_at')
                    ->whereNull('canceled_at');
    }

        public function scopeEjecutado($query)
    {
        return $query->whereNotNull('executed_at')
                    ->whereNull('approved_at')
                    ->whereNull('canceled_at');
    }

        public function scopeAprobado($query)
    {
        return $query->whereNotNull('executed_at')
                    ->whereNotNull('approved_at')
                    ->whereNull('canceled_at');
    }

    public function scopeIndeterminado($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('canceled_at')
            ->orWhere(function ($subQ) {
                $subQ->whereNotNull('executed_at')
                    ->whereNotNull('approved_at')
                    ->whereNotNull('canceled_at');
            });
        });
    }



        public function participants()
    {
        return $this->belongsToMany(User::class);
    }

    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

    public function getCreatorNameAttribute()
{
    return $this->creator?->name ?? null;
}

}
