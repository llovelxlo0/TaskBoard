<?php

namespace App\Support;

use App\Models\TaskActivity;
use Carbon\CarbonInterface;

class TaskActivityPresenter
{
    public function __construct(private TaskActivity $activity) {}
    public function title(): string
    {
        return match ($this->activity->type) {
            'created' => 'Task Created',
            'updated' => 'Task Updated',
            'status_changed' => 'Status Changed',
            'comment_added' => 'Comment Added',
            default => 'Task Activity',
        };
    }
    public function actorName(): string
    {
        return $this->activity->actor?->name ?? 'System';
    }
    public function badgeVariant(): string
    {
        return match ($this->activity->type) {
            'created' => 'success',
            'updated' => 'info',
            'status_changed' => 'warning',
            'comment_added' => 'muted',
            default => 'default',
        };
    }
    public function badgeLabel(): string
    {
        return match ($this->activity->type) {
            'created' => 'Created',
            'updated' => 'Updated',
            'status_changed' => 'Status',
            'comment_added' => 'Comment',
            default => strtoupper((string)$this->activity->type),
        };
    }
    public function message(): string
    {
        $m = $this->activity->meta ?? [];

        return match ($this->activity->type) {
            'created' => 'Task was created.',
            'status_changed' => sprintf('Status changed from %s to %s', $m['from'] ?? '?', $m['to'] ?? '?'),
            'comment_added' => $m['comment'] ?? 'A comment was created.',
            'updated' => 'Line was updated:',
            default => 'Activity occurred.',
        };
    }
   public function diff(): array
   {
       if ($this->activity->type !== 'updated') {
           return [];
       }
       $meta = $this->activity->meta;
       return is_array($meta) ? $meta : [];
   }
   public function isStatusChanged(): bool
   {
       return $this->activity->type === 'status_changed';
   }
   public function statusFrom(): string
   {
       return (string)(($this->activity->meta['from'] ?? '?'));
   }
   public function statusTo(): string
   {
       return (string)(($this->activity->meta['to'] ?? '?'));
   }
   public function statusVariant(string $value): string
   {
       return match ($value) {
           'todo' => 'muted',
           'in_progress' => 'warning',
           'completed' => 'success',
           default => 'default',
       };
   }
   public function commentText(): ?string
   {
       return $this->activity->comment?->comment;
   }
   public function data(): string
   {
       return $this->activity->created_at->diffForHumans();
   }
   public function dateExact(): string
   {
       return $this->activity->created_at->format('Y-m-d H:i:s');
   }
}
