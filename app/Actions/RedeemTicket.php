<?php

namespace App\Actions;

use App\Enums\CheckInStatus;
use App\Enums\OrderStatus;
use App\Events\TicketRedeemed;
use App\Models\CheckIn;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class RedeemTicket
{
    public function __construct(private ValidateTicket $validateTicket) {}

    /**
     * Redeem a ticket by scanning the QR code.
     *
     * @return array{success: bool, check_in: CheckIn|null, message: string}
     */
    public function execute(string $token, int $scannedBy, ?string $deviceInfo = null): array
    {
        $validation = $this->validateTicket->execute($token);

        if ($validation['status'] !== CheckInStatus::Valid) {
            // Record failed attempt
            if ($validation['order']) {
                CheckIn::create([
                    'order_id' => $validation['order']->id,
                    'scanned_by' => $scannedBy,
                    'status' => $validation['status'],
                    'scanned_at' => now(),
                    'device_info' => $deviceInfo,
                    'notes' => $validation['message'],
                ]);
            }

            return [
                'success' => false,
                'check_in' => null,
                'message' => $validation['message'],
            ];
        }

        return DB::transaction(function () use ($validation, $scannedBy, $deviceInfo) {
            /** @var Order $order */
            $order = $validation['order'];

            $checkIn = CheckIn::create([
                'order_id' => $order->id,
                'scanned_by' => $scannedBy,
                'status' => CheckInStatus::Valid,
                'scanned_at' => now(),
                'device_info' => $deviceInfo,
            ]);

            $order->update(['status' => OrderStatus::Completed]);

            TicketRedeemed::dispatch($checkIn);

            return [
                'success' => true,
                'check_in' => $checkIn,
                'message' => $validation['message'],
            ];
        });
    }
}
