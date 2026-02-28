<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{-- Top Navbar --}}
        <div class="mb-6 flex items-center justify-between" x-data>
            <div>
                @if(isset($title))
                    <h1 class="sr-only">{{ $title }}</h1>
                @endif
            </div>

            <div class="flex items-center gap-2">
                {{-- Theme Toggle --}}
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" size="sm" icon="sun" x-show="$flux.appearance === 'light'" x-cloak />
                    <flux:button variant="ghost" size="sm" icon="moon" x-show="$flux.appearance === 'dark'" x-cloak />
                    <flux:button variant="ghost" size="sm" icon="computer-desktop" x-show="$flux.appearance === 'system'" x-cloak />

                    <flux:menu>
                        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Terang</flux:menu.item>
                        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Gelap</flux:menu.item>
                        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">Sistem</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Notifications --}}
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" size="sm" icon="bell" class="relative">
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex size-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                {{ min(auth()->user()->unreadNotifications->count(), 9) }}
                            </span>
                        @endif
                    </flux:button>

                    <flux:menu class="w-80">
                        <div class="px-3 py-2">
                            <flux:heading size="sm">Notifikasi</flux:heading>
                        </div>
                        <flux:menu.separator />
                        @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                            <flux:menu.item class="whitespace-normal!">
                                <div>
                                    <p class="text-sm {{ $notification->read_at ? 'text-zinc-500' : 'font-medium text-zinc-900 dark:text-white' }}">
                                        {{ $notification->data['message'] ?? 'Notifikasi baru' }}
                                    </p>
                                    <p class="text-xs text-zinc-400">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </flux:menu.item>
                        @empty
                            <div class="px-3 py-6 text-center text-sm text-zinc-500">
                                Tidak ada notifikasi.
                            </div>
                        @endforelse
                    </flux:menu>
                </flux:dropdown>

                {{-- Profile Dropdown --}}
                <flux:dropdown position="bottom" align="end">
                    <flux:button variant="ghost" size="sm" class="flex items-center gap-2">
                        <flux:avatar size="xs" :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                        <span class="hidden text-sm font-medium text-zinc-700 dark:text-zinc-300 sm:inline">{{ auth()->user()->name }}</span>
                        <flux:icon name="chevron-down" variant="micro" class="text-zinc-400" />
                    </flux:button>

                    <flux:menu>
                        <div class="px-3 py-2">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                            <flux:badge size="sm" class="mt-1">{{ auth()->user()->role->label() }}</flux:badge>
                        </div>
                        <flux:menu.separator />
                        <flux:menu.item icon="cog-6-tooth" :href="route('profile.edit')" wire:navigate>
                            Pengaturan
                        </flux:menu.item>
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                                Keluar
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
