<?php

namespace App\Models;

use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'title',
        'priority',
        'status',
        'short_description',
        'full_description',
    ];
    protected $casts = [
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'completed_at' => 'datetime',
    ];
    public function changeStatus(TaskStatus $status): void
    {
        $this->status = $status;

        if ($status === TaskStatus::COMPLETED) {
            $this->completed_at = Carbon::now();
        } else {
            $this->completed_at = null;
        }
        $this->save();
    }
    public function activities()
    {
        return $this->hasMany(TaskActivity::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
