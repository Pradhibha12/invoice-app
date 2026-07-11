<?php

use Livewire\Component;
use App\Models\Client;

new class extends Component
{
    public $search = '';

    public function delete(int $id)
    {
        $client = Client::find($id);
        if ($client) {
            $name = $client->name;
            $client->delete();
            \App\Models\ActivityLog::log('deleted', null, "Deleted Client {$name}");
        }
        session()->flash('message', 'Client deleted successfully.');
    }

    public function clients()
    {
        return Client::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->get();
    }

    public function getAvatarColor(string $name): string
    {
        $colors = [
            'bg-teal-600',
            'bg-amber-600',
            'bg-indigo-600',
            'bg-rose-600',
            'bg-emerald-600',
            'bg-sky-600',
            'bg-violet-600',
            'bg-fuchsia-600',
        ];
        $hash = crc32($name);
        return $colors[abs($hash) % count($colors)];
    }
};
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-extrabold text-stone-950 font-display tracking-tight">Clients</h1>
        <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2.5 bg-amber-600 text-stone-950 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 shadow-sm hover:shadow transition duration-150">
            Add Client
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="flex items-center">
        <input wire:model.live="search" type="text" placeholder="Search clients by name..." class="w-full sm:w-1/3 rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
    </div>

    <!-- Card-based List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($this->clients() as $client)
            <div class="bg-white rounded-xl border border-stone-200 p-5 relative group hover:shadow-md hover:-translate-y-0.5 transition duration-200 flex items-start space-x-4">
                
                <!-- Initials Avatar -->
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-white text-lg shrink-0 {{ $this->getAvatarColor($client->name) }}">
                    {{ strtoupper(substr($client->name, 0, 2)) }}
                </div>

                <!-- Info -->
                <div class="flex-grow space-y-1">
                    <p class="font-bold text-stone-950 text-lg leading-snug">{{ $client->name }}</p>
                    <p class="text-sm text-stone-500 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 shrink-0 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span class="truncate">{{ $client->email }}</span>
                    </p>
                    @if($client->phone)
                        <p class="text-sm text-stone-500 flex items-center">
                            <svg class="w-4 h-4 mr-1.5 shrink-0 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span>{{ $client->phone }}</span>
                        </p>
                    @endif
                </div>

                <!-- Hover actions -->
                <div class="absolute top-4 right-4 flex space-x-1.5 opacity-0 group-hover:opacity-100 transition duration-150 bg-white/95 rounded-lg p-0.5 border border-stone-200">
                    <a href="{{ route('clients.edit', $client) }}" class="p-1.5 hover:bg-stone-100 rounded text-stone-600 hover:text-teal-700" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </a>
                    <button wire:click="delete({{ $client->id }})" wire:confirm="Are you sure you want to delete this client?" class="p-1.5 hover:bg-red-50 rounded text-stone-600 hover:text-red-600 cursor-pointer" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-stone-200 py-12 text-center text-stone-500 text-sm">
                No clients found.
            </div>
        @endforelse
    </div>
</div>