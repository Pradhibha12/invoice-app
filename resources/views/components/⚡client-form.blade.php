<?php

use Livewire\Component;
use App\Models\Client;

new class extends Component
{
    public $client = null;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';

    public function mount(?Client $client = null)
    {
        if ($client && $client->exists) {
            $this->client = $client;
            $this->name = $client->name;
            $this->email = $client->email;
            $this->phone = $client->phone ?? '';
            $this->address = $client->address ?? '';
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->client && $this->client->exists) {
            $this->client->update($validated);
            session()->flash('message', 'Client updated successfully.');
        } else {
            Client::create($validated);
            session()->flash('message', 'Client created successfully.');
        }

        return redirect()->route('clients.index');
    }
};
?>

<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl border border-stone-200 shadow-sm">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-stone-900 font-display">{{ $client ? 'Edit Client' : 'Create Client' }}</h1>
        <a href="{{ route('clients.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Back to List</a>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-semibold text-stone-700">Name</label>
            <input wire:model="name" type="text" id="name" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-stone-700">Email Address</label>
            <input wire:model="email" type="email" id="email" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-semibold text-stone-700">Phone Number</label>
            <input wire:model="phone" type="text" id="phone" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="address" class="block text-sm font-semibold text-stone-700">Address</label>
            <textarea wire:model="address" id="address" rows="3" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm" placeholder="Full street address..."></textarea>
            @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end pt-4 border-t border-stone-100 space-x-2">
            <a href="{{ route('clients.index') }}" class="px-4 py-2 border border-stone-300 rounded-lg shadow-sm text-sm font-medium text-stone-700 bg-white hover:bg-stone-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition">
                Save Client
            </button>
        </div>
    </form>
</div>