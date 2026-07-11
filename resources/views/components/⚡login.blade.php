<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        $this->addError('email', 'These credentials do not match our records.');
        $this->password = '';
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
            <h2 class="mt-6 text-3xl font-extrabold text-white font-display tracking-tight">Welcome Back</h2>
            <p class="mt-2 text-sm text-stone-400">
                Sign in to manage your invoices
            </p>
        </div>

        <form wire:submit="login" class="mt-8 space-y-6">
            <div class="rounded-md space-y-4">
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-stone-400">Email Address</label>
                    <input wire:model="email" type="email" id="email" required autocomplete="email" 
                           class="mt-1 block w-full rounded-lg bg-stone-950 border border-stone-800 text-stone-100 placeholder-stone-600 focus:border-amber-500 focus:ring focus:ring-amber-500/20 text-sm py-2.5 px-3.5 transition"
                           placeholder="you@example.com">
                    @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-stone-400">Password</label>
                    <input wire:model="password" type="password" id="password" required autocomplete="current-password" 
                           class="mt-1 block w-full rounded-lg bg-stone-950 border border-stone-800 text-stone-100 placeholder-stone-600 focus:border-amber-500 focus:ring focus:ring-amber-500/20 text-sm py-2.5 px-3.5 transition"
                           placeholder="••••••••">
                    @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Remember me & Forgot Password -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input wire:model="remember" id="remember-me" type="checkbox" 
                           class="h-4 w-4 rounded bg-stone-950 border-stone-800 text-amber-500 focus:ring-amber-500/20 focus:ring-offset-stone-900">
                    <label for="remember-me" class="ml-2 block text-sm text-stone-400 select-none">
                        Remember me
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-stone-950 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-stone-900 focus:ring-amber-500 shadow-lg shadow-amber-600/10 hover:shadow-amber-500/20 transition-all duration-150">
                    <span wire:loading.remove wire:target="login">Sign In</span>
                    <span wire:loading wire:target="login" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-stone-950" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Authenticating...
                    </span>
                </button>
            </div>
        </form>

        <!-- Help Info -->
        <div class="mt-6 text-center text-xs text-stone-500 border-t border-stone-800/60 pt-4">
            Default credentials: <span class="text-amber-500/70">test@example.com</span> / <span class="text-amber-500/70">password</span>
        </div>
    </div>
</div>
