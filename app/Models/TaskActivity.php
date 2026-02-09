<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskActivity extends Model
{
    protected $fillable = [
        'task_id',
        'actor_id',
        'comment_id',
        'type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
    public function comment() : BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'comment_id');
    }

}
