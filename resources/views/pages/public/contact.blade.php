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
    <section class="bg-gradient-to-br from-sky-600 via-sky-700 to-sky-900 py-16">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Hubungi Kami</h1>
            <p class="mt-3 text-sky-100">Ada pertanyaan atau saran? Jangan ragu untuk menghubungi kami.</p>
        </div>
    </section>

    <!-- Content -->
    <section class="bg-white py-16 dark:bg-zinc-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-2">
                <!-- Contact Info -->
                <div>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Informasi Kontak</h2>
                    <p class="mt-3 text-zinc-600 dark:text-zinc-400">
                        Kami siap membantu Anda. Hubungi kami melalui salah satu kanal di bawah atau kirim pesan
                        melalui formulir.
                    </p>

                    <div class="mt-8 space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/30">
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

                        <div class="flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30">
                                <flux:icon name="phone" class="size-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">Telepon</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    +62 812-3456-7890<br>
                                    Senin - Minggu, 08:00 - 18:00 WITA
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/30">
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

                        <div class="flex items-start gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30">
                                <flux:icon name="clock" class="size-6 text-violet-600 dark:text-violet-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">Jam Operasional</h3>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                    Senin - Jumat: 08:00 - 18:00 WITA<br>
                                    Sabtu - Minggu: 07:00 - 19:00 WITA
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
                        <div class="rounded-2xl border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-900">
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
</div>
