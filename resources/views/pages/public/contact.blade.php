<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;

new #[Title('Hubungi Kami - Kolam Po\'opo')] #[Layout('layouts.guest')] class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:50')]
    public string $subject = '';

    #[Validate('required|string|max:2000')]
    public string $message = '';

    public bool $sent = false;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->name = auth()->user()->name;
            $this->email = auth()->user()->email;
        }
    }

    public function send(): void
    {
        $this->validate();

        Mail::raw(
            "Dari: {$this->name} ({$this->email})\n\nSubjek: {$this->subject}\n\n{$this->message}",
            function ($mail) {
                $mail->to(config('mail.from.address', 'info@kolampoopo.id'))
                    ->subject("Pesan Kontak: {$this->subject}")
                    ->replyTo($this->email, $this->name);
            }
        );

        $this->sent = true;
        $this->reset(['name', 'email', 'subject', 'message']);

        if (auth()->check()) {
            $this->name = auth()->user()->name;
            $this->email = auth()->user()->email;
        }
    }
}; ?>

<div>
    <!-- Hero -->
    <section class="relative overflow-hidden bg-linear-to-br from-sky-600 via-sky-700 to-sky-900 py-16">
        <!-- Animated floating orbs -->
        <div class="animate-float-gentle absolute -top-10 left-1/4 size-40 rounded-full bg-white/10 blur-2xl"></div>
        <div class="animate-float-slow absolute -bottom-10 right-1/3 size-52 rounded-full bg-sky-400/10 blur-2xl"></div>
        <div class="animate-float-reverse absolute left-10 top-16 size-24 rounded-full bg-sky-300/10 blur-xl"></div>

        <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-sky-100 backdrop-blur-sm">
                <flux:icon name="envelope" variant="mini" class="size-4" />
                Hubungi Kami
            </span>
            <h1 class="mt-4 text-3xl font-bold text-white sm:text-4xl">Hubungi Kami</h1>
            <p class="mt-3 text-sky-100">Ada pertanyaan atau saran? Jangan ragu untuk menghubungi kami.</p>
        </div>

        <!-- Wave divider -->
        <div class="absolute -bottom-1 left-0 w-full">
            <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0,40 C360,100 1080,0 1440,60 L1440,100 L0,100Z" class="fill-white dark:fill-zinc-800" />
            </svg>
        </div>
    </section>

    <!-- Content -->
    <section class="relative bg-white py-16 dark:bg-zinc-800">
        <div class="bg-dot-pattern pointer-events-none absolute inset-0 opacity-40"></div>
        <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2">
                <!-- Contact Info -->
                <div>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Informasi Kontak</h2>
                    <p class="mt-3 text-zinc-600 dark:text-zinc-400">
                        Kami siap membantu Anda. Hubungi kami melalui salah satu kanal di bawah atau kirim pesan
                        melalui formulir.
                    </p>

                    <div class="mt-8 space-y-6">
                        <div class="flex items-start gap-4 transition-all duration-300 hover:translate-x-1">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-100 shadow-sm dark:bg-sky-900/30">
                                <flux:icon name="map-pin" class="size-6 text-sky-600 dark:text-sky-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">Alamat</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    Kolam Po'opo<br>
                                    Sulawesi Utara, Indonesia
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 transition-all duration-300 hover:translate-x-1">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-green-100 shadow-sm dark:bg-green-900/30">
                                <flux:icon name="phone" class="size-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">Telepon</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    +62 812-3456-7890<br>
                                    Setiap Hari, 08:00 - 22:00 WITA
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 transition-all duration-300 hover:translate-x-1">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 shadow-sm dark:bg-amber-900/30">
                                <flux:icon name="envelope" class="size-6 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">Email</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    info@kolampoopo.id<br>
                                    reservasi@kolampoopo.id
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 transition-all duration-300 hover:translate-x-1">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 shadow-sm dark:bg-violet-900/30">
                                <flux:icon name="clock" class="size-6 text-violet-600 dark:text-violet-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">Jam Operasional</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    Setiap Hari: 08:00 - 22:00 WITA
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div>
                    @if($sent)
                        <div class="rounded-2xl border border-green-200 bg-green-50 p-8 text-center dark:border-green-800 dark:bg-green-900/20">
                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                <flux:icon name="check-circle" class="size-8 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-green-800 dark:text-green-400">Pesan Terkirim!</h3>
                            <p class="mt-2 text-sm text-green-700 dark:text-green-400/80">
                                Terima kasih telah menghubungi kami. Kami akan membalas pesan Anda dalam 1×24 jam.
                            </p>
                            <flux:button wire:click="$set('sent', false)" variant="ghost" class="mt-4">
                                Kirim Pesan Lain
                            </flux:button>
                        </div>
                    @else
                        <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                            <h2 class="mb-6 text-xl font-bold text-zinc-900 dark:text-white">Kirim Pesan</h2>

                            <form wire:submit="send" class="space-y-5">
                                <flux:input wire:model="name" label="Nama Lengkap" placeholder="Masukkan nama Anda" required />
                                <flux:input wire:model="email" label="Email" type="email" placeholder="email@contoh.com" required />
                                <flux:input wire:model="subject" label="Subjek" placeholder="Tentang apa pesan Anda?" required />
                                <flux:textarea wire:model="message" label="Pesan" placeholder="Tulis pesan Anda di sini..." rows="5" required />

                                <flux:button type="submit" variant="primary" class="w-full" icon="paper-airplane">
                                    Kirim Pesan
                                </flux:button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Google Maps -->
    <section class="bg-zinc-50 dark:bg-zinc-900">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white sm:text-3xl">Temukan Kami</h2>
                <p class="mt-3 text-zinc-600 dark:text-zinc-400">Lihat lokasi kami di peta dan dapatkan petunjuk arah</p>
            </div>
            <div class="mt-8 overflow-hidden rounded-2xl border border-zinc-200 shadow-sm dark:border-zinc-700">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.2!2d124.8!3d1.3!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2sKolam+Po'opo!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid"
                    width="100%"
                    height="400"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="w-full"
                ></iframe>
            </div>
            <div class="mt-4 text-center">
                <a href="https://www.google.com/maps/dir/?api=1&destination=Kolam+Po'opo+Sulawesi+Utara" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 hover:text-sky-700 dark:text-sky-400">
                    <flux:icon name="map-pin" variant="mini" class="size-4" />
                    Buka di Google Maps & Dapatkan Petunjuk Arah
                </a>
            </div>
        </div>
    </section>
</div>
