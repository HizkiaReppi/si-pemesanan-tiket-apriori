<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate a signed QR token for an order.
     */
    public function generateToken(string $orderCode, string $visitDate): string
    {
        $payload = "{$orderCode}|{$visitDate}";
        $signature = hash_hmac('sha256', $payload, config('app.key'));

        return "{$payload}|{$signature}";
    }

    /**
     * Verify a QR token and extract the order code and visit date.
     *
     * @return array{order_code: string, visit_date: string}|null
     */
    public function verifyToken(string $token): ?array
    {
        $parts = explode('|', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$orderCode, $visitDate, $signature] = $parts;

        $expectedSignature = hash_hmac('sha256', "{$orderCode}|{$visitDate}", config('app.key'));

        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        return [
            'order_code' => $orderCode,
            'visit_date' => $visitDate,
        ];
    }

    /**
     * Generate QR code SVG string from a token.
     */
    public function generateSvg(string $token, int $size = 300): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd,
        );

        $writer = new Writer($renderer);

        return $writer->writeString($token);
    }
}
