<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CompanySetting;

new class extends Component
{
    use WithFileUploads;

    public $company_name = '';
    public $logo = null;
    public $logo_path = null;
    public $address = '';
    public $email = '';
    public $phone = '';
    public $tax_id = '';

    public function mount()
    {
        $settings = CompanySetting::getOrNew();
        $this->company_name = $settings->company_name;
        $this->logo_path = $settings->logo_path;
        $this->address = $settings->address ?? '';
        $this->email = $settings->email ?? '';
        $this->phone = $settings->phone ?? '';
        $this->tax_id = $settings->tax_id ?? '';
    }

    protected function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048', // max 2MB
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
        ];
    }

    public function save()
    {
        $this->validate();

        $settings = CompanySetting::first() ?? new CompanySetting();
        $settings->company_name = $this->company_name;
        $settings->address = $this->address;
        $settings->email = $this->email;
        $settings->phone = $this->phone;
        $settings->tax_id = $this->tax_id;

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            $settings->logo_path = $path;
            $this->logo_path = $path;
        }

        $settings->save();

        \App\Models\ActivityLog::log('updated', $settings, "Updated Company Settings for {$this->company_name}");

        session()->flash('message', 'Company settings saved successfully.');
    }
};
?>

<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl border border-stone-200 shadow-sm">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-stone-900 font-display">Company Settings</h1>
        <a href="{{ route('invoices.index') }}" class="text-sm text-stone-600 hover:text-stone-950">&larr; Back to Invoices</a>
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="company_name" class="block text-sm font-semibold text-stone-700">Company Name</label>
                <input wire:model="company_name" type="text" id="company_name" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                @error('company_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="tax_id" class="block text-sm font-semibold text-stone-700">Tax ID / Business Reg #</label>
                <input wire:model="tax_id" type="text" id="tax_id" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm" placeholder="e.g. TAX-12345">
                @error('tax_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
        </div>

        <div>
            <label for="address" class="block text-sm font-semibold text-stone-700">Address</label>
            <textarea wire:model="address" id="address" rows="3" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm" placeholder="123 Corporate Blvd..."></textarea>
            @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-stone-700 mb-2">Company Logo</label>
            <div class="flex items-center space-x-6">
                @if ($logo)
                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 flex items-center justify-center">
                        <img src="{{ $logo->temporaryUrl() }}" class="object-contain w-full h-full">
                    </div>
                @elseif ($logo_path)
                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 flex items-center justify-center">
                        <img src="{{ asset('storage/' . $logo_path) }}" class="object-contain w-full h-full">
                    </div>
                @else
                    <div class="w-20 h-20 rounded-lg border-2 border-dashed border-stone-300 flex items-center justify-center text-stone-400">
                        No Logo
                    </div>
                @endif

                <div class="flex-grow">
                    <input type="file" wire:model="logo" id="logo" class="sr-only">
                    <label for="logo" class="cursor-pointer inline-flex items-center px-4 py-2 border border-stone-300 rounded-lg shadow-sm text-sm font-medium text-stone-700 bg-white hover:bg-stone-50 transition">
                        Choose File
                    </label>
                    <p class="text-xs text-stone-500 mt-1">PNG, JPG up to 2MB</p>
                    @error('logo') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-stone-100">
            <button type="submit" class="px-6 py-2 rounded-lg text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition duration-150">
                Save Settings
            </button>
        </div>
    </form>
</div>