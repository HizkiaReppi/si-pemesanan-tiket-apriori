<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            {{-- Left Panel - Pool Themed --}}
            <div class="relative hidden h-full flex-col overflow-hidden p-10 text-white lg:flex">
                <div class="absolute inset-0 bg-gradient-to-br from-sky-500 via-sky-600 to-sky-800"></div>
                <div class="absolute inset-0 opacity-10">
                    <svg class="h-full w-full" viewBox="0 0 800 1000" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="200" r="180" fill="currentColor" class="text-white"/>
                        <circle cx="600" cy="100" r="120" fill="currentColor" class="text-white"/>
                        <circle cx="400" cy="500" r="250" fill="currentColor" class="text-white"/>
                        <circle cx="700" cy="800" r="200" fill="currentColor" class="text-white"/>
                        <circle cx="150" cy="700" r="140" fill="currentColor" class="text-white"/>
                        <path d="M0 600C200 630 400 580 600 610C700 625 750 615 800 620V1000H0Z" fill="currentColor" class="text-white" opacity="0.15"/>
                        <path d="M0 700C240 720 480 690 720 710C780 715 800 710 800 715V1000H0Z" fill="currentColor" class="text-white" opacity="0.1"/>
                    </svg>
                </div>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3 text-lg font-medium" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm">
                        <x-app-logo-icon class="h-6 w-6 fill-current text-white" />
                    </span>
                    <span class="text-xl font-bold">Kolam Po'opo</span>
                </a>

                <div class="relative z-20 mt-auto">
                    <h2 class="text-3xl font-bold leading-tight">Wisata Air Panas Alami<br>Sulawesi Utara</h2>
                    <p class="mt-4 text-lg text-sky-100/90">
                        Nikmati pengalaman berendam di kolam air panas alami yang kaya mineral, dikelilingi keindahan alam tropis yang asri.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 backdrop-blur-sm">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /></svg>
                            <span class="text-sm font-medium">Air Panas Alami</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 backdrop-blur-sm">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                            <span class="text-sm font-medium">Ramah Keluarga</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 backdrop-blur-sm">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg>
                            <span class="text-sm font-medium">E-Tiket Instan</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel - Form --}}
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-sky-600">
                            <x-app-logo-icon class="size-6 fill-current text-white" />
                        </span>
                        <span class="text-lg font-bold text-zinc-900 dark:text-white">Kolam Po'opo</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
