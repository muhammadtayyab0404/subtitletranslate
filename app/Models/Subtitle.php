<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    protected $fillable = [
        'user_id','original_name','stored_name','path','status','total_lines','error','target_language',
    ];

    public function lines()
    {
        return $this->hasMany(SubtitleLine::class);
    }
}
