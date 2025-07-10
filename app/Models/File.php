<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
   
    protected $appends = ['link','mime_type'];
 
    
    protected $fillable = [
        'id', 
        'name', 
        'file_extension',
        'file_base64',
        'file_size',
        'compressed_file_size',
        
    ];

      protected $hidden = [
        'file_base64',
    ];

    public function getLinkAttribute()
    {
        return env('APP_URL_MEDIA') . $this->id;
    }

    public function assignable()
    {
        return $this->morphTo('assignable', 'assignable_type', 'assignable_uuid');
    }
    

    public function getMimeTypeAttribute()
    {
        return match ($this->file_extension) {
            'jpeg', 'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }


}
