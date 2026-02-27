<?php

namespace App\Actions;

use App\Enums\CheckInStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\QrCodeService;

class ValidateTicket
{
    public function __construct(private QrCodeService $qrCodeService) {}

    /**
     * Validate a QR code token and return validation result.
     *
     * @return array{status: CheckInStatus, order: Order|null, message: string}
     */
    public function execute(string $token): array
    {
        $decoded = $this->qrCodeService->verifyToken($token);

        if (! $decoded) {
            return [
                'status' => CheckInStatus::Invalid,
                'order' => null,
                'message' => 'Kode QR tidak valid.',
            ];
        }

        $order = Order::where('code', $decoded['order_code'])->first();

        if (! $order) {
            return [
                'status' => CheckInStatus::Invalid,
                'order' => null,
                'message' => 'Pesanan tidak ditemukan.',
            ];
        }

        if (! $order->isPaid()) {
            return [
                'status' => CheckInStatus::Invalid,
                'order' => $order,
                'message' => 'Pesanan belum dibayar.',
            ];
        }

        if ($order->status === OrderStatus::Completed) {
            return [
                'status' => CheckInStatus::AlreadyUsed,
                'order' => $order,
                'message' => 'Tiket sudah digunakan.',
            ];
        }

        if (! $order->visit_date->isToday()) {
            $formattedDate = $order->visit_date->translatedFormat('d F Y');

            return [
                'status' => CheckInStatus::WrongDate,
                'order' => $order,
                'message' => "Tiket ini berlaku untuk tanggal {$formattedDate}.",
            ];
        }

        if ($order->checkIns()->where('status', CheckInStatus::Valid)->exists()) {
            return [
                'status' => CheckInStatus::AlreadyUsed,
                'order' => $order,
                'message' => 'Tiket sudah di-scan sebelumnya.',
            ];
        }

        return [
            'status' => CheckInStatus::Valid,
            'order' => $order,
            'message' => 'Tiket valid. Selamat datang!',
        ];
    }
}
