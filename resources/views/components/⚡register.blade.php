<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);

        session()->regenerate();

        return redirect()->route('dashboard');
    }
};
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-stone-950 via-stone-900 to-amber-950/20 py-12 px-4 sm:px-6 lg:px-8 w-full">
    <div class="max-w-md w-full space-y-8 bg-stone-900/60 backdrop-blur-md p-8 sm:p-10 rounded-2xl border border-stone-800 shadow-2xl">
        <!-- Brand/Header -->
        <div class="text-center">
            <div class="mx-auto h-12 w-12 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-600 flex items-center justify-center font-black text-stone-950 font-display text-2xl shadow-lg">
                I
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-white font-display tracking-tight">Create Account</h2>
            <p class="mt-2 text-sm text-stone-400">
                Get started with your custom InvoiceApp
            </p>
        </div>

        <form wire:submit="register" class="mt-8 space-y-5">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-stone-400">Full Name</label>
                    <input wire:model="name" type="text" id="name" required autocomplete="name" 
                           class="mt-1 block w-full rounded-lg bg-stone-950 border border-stone-800 text-stone-100 placeholder-stone-600 focus:border-amber-500 focus:ring focus:ring-amber-500/20 text-sm py-2.5 px-3.5 transition"
                           placeholder="John Doe">
                    @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-400">Email Address</label>
                    <input wire:model="email" type="email" id="email" required autocomplete="email" 
                           class="mt-1 block w-full rounded-lg bg-stone-950 border border-stone-800 text-stone-100 placeholder-stone-600 focus:border-amber-500 focus:ring focus:ring-amber-500/20 text-sm py-2.5 px-3.5 transition"
                           placeholder="john@example.com">
                    @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-stone-400">Password</label>
                    <input wire:model="password" type="password" id="password" required autocomplete="new-password" 
                           class="mt-1 block w-full rounded-lg bg-stone-950 border border-stone-800 text-stone-100 placeholder-stone-600 focus:border-amber-500 focus:ring focus:ring-amber-500/20 text-sm py-2.5 px-3.5 transition"
                           placeholder="Min. 8 characters">
                    @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-stone-400">Confirm Password</label>
                    <input wire:model="password_confirmation" type="password" id="password_confirmation" required autocomplete="new-password" 
                           class="mt-1 block w-full rounded-lg bg-stone-950 border border-stone-800 text-stone-100 placeholder-stone-600 focus:border-amber-500 focus:ring focus:ring-amber-500/20 text-sm py-2.5 px-3.5 transition"
                           placeholder="Repeat your password">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-stone-950 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-stone-900 focus:ring-amber-500 shadow-lg shadow-amber-600/10 hover:shadow-amber-500/20 transition-all duration-150">
                    <span wire:loading.remove wire:target="register">Create Account</span>
                    <span wire:loading wire:target="register" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-stone-950" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating account...
                    </span>
                </button>
            </div>
        </form>

        <!-- Redirect to Login -->
        <div class="mt-6 text-center text-sm text-stone-400 border-t border-stone-800/60 pt-4">
            Already have an account? 
            <a href="{{ route('login') }}" class="font-semibold text-amber-500 hover:text-amber-400 transition ml-1">
                Sign In
            </a>
        </div>
    </div>
</div>
