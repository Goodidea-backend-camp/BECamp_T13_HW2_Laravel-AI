<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function audioFile()
    {
        return $this->hasOne(AudioFile::class);
    }
}
