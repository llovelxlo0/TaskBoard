<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class TaskActivityCollectionPresenter
{
    public function __construct(private Collection $activities) {}

    /**
     * @return array<string, Collection>
     */
    public function groupedByDay(): array
    {
        return $this->activities
            ->sortByDesc('created_at')
            ->groupBy(fn ($activity) => $activity->created_at->toDateString())
            ->all();
    }

    public function dayLabel(string $date): string
    {
        return match ($date) {
            now()->toDateString() => 'Сегодня',
            now()->subDay()->toDateString() => 'Вчера',
            default => Carbon::parse($date)->format('Y-m-d'),
        };
    }
}
