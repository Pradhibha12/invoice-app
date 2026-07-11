<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Invoice App' }}</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Instrument+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-gradient-to-br from-stone-100 to-amber-50/10 min-h-screen text-stone-900 font-sans antialiased flex flex-col md:flex-row">
        @auth
            <!-- Sidebar Navigation -->
            <aside class="w-full md:w-64 bg-stone-900 text-stone-100 flex flex-col md:sticky md:top-0 md:h-screen shrink-0 border-r border-stone-800 shadow-xl">
                <!-- Brand -->
                <div class="px-6 py-6 flex items-center space-x-3 border-b border-stone-800">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-amber-500 to-amber-600 flex items-center justify-center font-black text-stone-950 font-display text-lg shadow-sm">
                        I
                    </div>
                    <span class="text-xl font-extrabold tracking-tight font-display text-white">InvoiceApp</span>
                </div>

                <!-- Links -->
                <nav class="flex-grow px-4 py-6 space-y-2.5">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold tracking-wide transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-amber-600 text-stone-950 shadow-md shadow-amber-600/20' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('invoices.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold tracking-wide transition duration-150 {{ request()->routeIs('invoices.*') ? 'bg-amber-600 text-stone-950 shadow-md shadow-amber-600/20' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Invoices
                    </a>

                    <a href="{{ route('clients.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold tracking-wide transition duration-150 {{ request()->routeIs('clients.*') ? 'bg-amber-600 text-stone-950 shadow-md shadow-amber-600/20' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Clients
                    </a>

                    <a href="{{ route('activity.index') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold tracking-wide transition duration-150 {{ request()->routeIs('activity.*') ? 'bg-amber-600 text-stone-950 shadow-md shadow-amber-600/20' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Activity Log
                    </a>

                    <a href="{{ route('settings.edit') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold tracking-wide transition duration-150 {{ request()->routeIs('settings.*') ? 'bg-amber-600 text-stone-950 shadow-md shadow-amber-600/20' : 'text-stone-300 hover:text-white hover:bg-stone-800' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Settings
                    </a>
                </nav>

                <!-- Bottom Company Settings Profile quick-info -->
                <div class="p-4 border-t border-stone-800 bg-stone-950/40 text-stone-400 text-xs flex justify-between items-center">
                    <div>
                        @php
                            $company = \App\Models\CompanySetting::getOrNew();
                        @endphp
                        <p class="font-semibold text-stone-200 truncate max-w-[120px]">{{ $company->company_name }}</p>
                        @if($company->email)
                            <p class="mt-0.5 truncate max-w-[120px]">{{ $company->email }}</p>
                        @endif
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 text-stone-400 hover:text-white rounded-lg hover:bg-stone-800 transition" title="Log Out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Content Area -->
            <main class="flex-grow py-8 px-6 md:px-10 overflow-y-auto max-w-7xl mx-auto w-full">
                {{ $slot }}
            </main>
        @else
            <!-- Guest Content Wrapper -->
            <main class="flex-grow w-full">
                {{ $slot }}
            </main>
        @endauth

        @livewireScripts
    </body>
</html>
