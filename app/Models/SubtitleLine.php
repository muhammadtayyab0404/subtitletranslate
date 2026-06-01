<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubtitleLine extends Model
{
    protected $fillable = [
        'subtitle_id','seq','start_time','end_time','text_original','ai_description'
    ];

    public function subtitle()
    {
        return $this->belongsTo(Subtitle::class);
    }
}
