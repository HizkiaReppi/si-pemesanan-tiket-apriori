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

    /**
     * Create a new message instance.
     */
    public function __construct(public Order $order)
    {
        $this->qrSvg = $order->qr_token
            ? app(QrCodeService::class)->generateSvg($order->qr_token, 200)
            : '';
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
            markdown: 'mail.e-ticket',
            with: [
                'qrSvg' => $this->qrSvg,
            ],
        );
    }
}
