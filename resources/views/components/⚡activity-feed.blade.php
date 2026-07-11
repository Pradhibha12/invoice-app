<?php

use Livewire\Component;
use App\Models\ActivityLog;

new class extends Component
{
    public function logs()
    {
        return ActivityLog::with('user')->latest('id')->get();
    }

    public function getActionColor(string $action): string
    {
        return match ($action) {
            'created' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
            'updated' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'deleted' => 'bg-red-500/10 text-red-400 border-red-500/20',
            'paid' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'partially_paid' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
            default => 'bg-stone-500/10 text-stone-400 border-stone-500/20',
        };
    }
};
?>

<div class="space-y-8 max-w-4xl mx-auto">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-extrabold text-stone-950 font-display tracking-tight">Activity Log</h1>
        <span class="text-sm text-stone-500 font-medium">Chronological System Feed</span>
    </div>

    <!-- Timeline Wrapper -->
    <div class="bg-white rounded-2xl border border-stone-200 p-6 sm:p-8 shadow-sm">
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @forelse($this->logs() as $index => $log)
                    <li>
                        <div class="relative pb-8">
                            <!-- Connector Line -->
                            @if ($index < count($this->logs()) - 1)
                                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-stone-200" aria-hidden="true"></span>
                            @endif

                            <div class="relative flex items-start space-x-4">
                                <!-- Status Dot / Badge -->
                                <div class="relative">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border font-bold text-sm uppercase {{ $this->getActionColor($log->action) }}">
                                        {{ substr($log->action, 0, 2) }}
                                    </span>
                                </div>

                                <!-- Log Content -->
                                <div class="min-w-0 flex-grow pt-1.5">
                                    <div class="flex justify-between items-baseline space-x-4">
                                        <div>
                                            <p class="text-sm font-semibold text-stone-900 leading-snug">
                                                {{ $log->description }}
                                            </p>
                                            <p class="mt-1 text-xs text-stone-500">
                                                By {{ $log->user ? $log->user->name : 'System' }}
                                                @if($log->subject_type)
                                                    • <span class="text-stone-400 font-mono">{{ $log->subject_type }} #{{ $log->subject_id }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="whitespace-nowrap text-right text-xs text-stone-500 font-medium">
                                            {{ $log->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <div class="py-12 text-center text-stone-500 text-sm">
                        No system activity logged yet.
                    </div>
                @endforelse
            </ul>
        </div>
    </div>
</div>
