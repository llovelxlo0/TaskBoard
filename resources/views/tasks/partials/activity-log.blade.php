<h3>История</h3>

@php($collection = new \App\Support\TaskActivityCollectionPresenter($task->activities))

@forelse($collection->groupedByDay() as $date => $activities)
    <h4 style="margin:16px 0 8px; opacity:.8;">
        {{ $collection->dayLabel($date) }}
    </h4>

    @foreach($activities as $activity)
        @php($p = new \App\Support\TaskActivityPresenter($activity))

        <div style="padding:10px 0; border-bottom:1px solid #eee;">
            <div style="display:flex; justify-content:space-between; gap:12px;">
                <div>
                    <div><strong>{{ $p->title() }}</strong></div>

                    <div style="opacity:.9;">
                        <x-badge :variant="$p->badgeVariant()" style="margin-right:8px;">
                            {{ $p->badgeLabel() }}
                        </x-badge>

                        <strong>{{ $p->actorName() }}</strong>
                        — {{ $p->message() }}

                        @if($activity->type === 'comment_added')
                            : <em>{{ $p->commentText() ?? 'Комментарий удалён' }}</em>
                        @endif
                    </div>
                </div>

                <div style="white-space:nowrap; opacity:.7;" title="{{ $p->dateExact() }}">
                    {{ $p->data() }}
                </div>
            </div>

            @if($p->diff() !== [])
                <ul style="margin:8px 0 0 18px;">
                    @foreach($p->diff() as $field => $change)
                        <li>
                            {{ $field }}:
                            <code>{{ $change['from'] ?? '—' }}</code>
                            →
                            <code>{{ $change['to'] ?? '—' }}</code>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($p->isStatusChanged())
                <div style="margin-top:6px;">
                    <x-badge :variant="$p->statusVariant($p->statusFrom())">
                        {{ $p->statusFrom() }}
                    </x-badge>
                    <span style="opacity:.7; margin:0 6px;">→</span>
                    <x-badge :variant="$p->statusVariant($p->statusTo())">
                        {{ $p->statusTo() }}
                    </x-badge>
                </div>
            @endif
        </div>
    @endforeach
@empty
    <p>История пока пуста.</p>
@endforelse
