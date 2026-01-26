<?php

namespace App\Models;

use App\Enums\TaskPriorityEnum;
use Illuminate\Database\Eloquent\Model;
use App\Enums\TaskStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'status' => TaskStatusEnum::class,
        'priority' => TaskPriorityEnum::class,
        'completed_at' => 'datetime',
    ];
    public function changeStatus(TaskStatusEnum $status): void
    {
        $this->status = $status;

        if ($status === TaskStatusEnum::COMPLETED) {
            $this->completed_at = Carbon::now();
        } else {
            $this->completed_at = null;
        }
        $this->save();
    }
    public function moveTo(TaskStatusEnum $status): void
    {
        if ($status === TaskStatusEnum::COMPLETED) {
            $this->setCompleted();
            return;
        }
        $this->changeStatus($status);
    }
    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany|Task
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }
    public function setCompleted(): void
    {
        $this->changeStatus(TaskStatusEnum::COMPLETED);
    }
    public function reopen(): void
    {
        $this->changeStatus(TaskStatusEnum::IN_PROGRESS);
    }
    public function isCompleted(): bool
    {
        return $this->status === TaskStatusEnum::COMPLETED;
    }
}
