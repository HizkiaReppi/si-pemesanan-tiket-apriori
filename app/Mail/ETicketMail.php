<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ETicketMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $qrSvg;

    public ?string $qrPngPath = null;

    /**
     * Create a new message instance.
     */
    public function __construct(public Order $order)
    {
        $qrService = app(QrCodeService::class);

        $this->qrSvg = $order->qr_token
            ? $qrService->generateSvg($order->qr_token, 200)
            : '';

        if ($order->qr_token) {
            $this->qrPngPath = $qrService->generatePng($order->qr_token, 300);

            $this->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message): void {
                if ($this->qrPngPath && file_exists($this->qrPngPath)) {
                    $message->embed(
                        fopen($this->qrPngPath, 'r'),
                        'qr-code',
                        'image/png',
                    );
                }
            });
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "E-Tiket Anda - {$this->order->code}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.e-ticket',
            with: [
                'qrSvg' => $this->qrSvg,
                'qrImageCid' => $this->qrPngPath ? 'cid:qr-code' : null,
            ],
        );
    }
}
