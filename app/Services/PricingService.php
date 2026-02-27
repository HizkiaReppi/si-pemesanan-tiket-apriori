<?php

namespace App\Services;

use App\Enums\DayType;
use App\Models\Addon;
use App\Models\TicketPrice;
use App\Models\TicketType;
use Carbon\Carbon;

class PricingService
{
    /**
     * Determine the day type for a given date.
     */
    public function getDayType(Carbon|string $date): DayType
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->isWeekend() ? DayType::Weekend : DayType::Weekday;
    }

    /**
     * Get the price for a ticket type on a given date.
     */
    public function getTicketPrice(TicketType $ticketType, Carbon|string $date): float
    {
        $dayType = $this->getDayType($date);

        $ticketPrice = TicketPrice::where('ticket_type_id', $ticketType->id)
            ->where('day_type', $dayType)
            ->first();

        return $ticketPrice ? (float) $ticketPrice->price : 0;
    }

    /**
     * Get the price for an addon.
     */
    public function getAddonPrice(Addon $addon): float
    {
        return (float) $addon->price;
    }

    /**
     * Calculate the subtotal for a list of cart items.
     *
     * @param  array<int, array{type: string, id: int, quantity: int}>  $items
     */
    public function calculateSubtotal(array $items, Carbon|string $visitDate): float
    {
        $subtotal = 0;
        $dayType = $this->getDayType($visitDate);

        foreach ($items as $item) {
            if ($item['type'] === 'ticket') {
                $ticketType = TicketType::find($item['id']);
                if ($ticketType) {
                    $price = $this->getTicketPrice($ticketType, $visitDate);
                    $subtotal += $price * $item['quantity'];
                }
            } elseif ($item['type'] === 'addon') {
                $addon = Addon::find($item['id']);
                if ($addon) {
                    $subtotal += $this->getAddonPrice($addon) * $item['quantity'];
                }
            }
        }

        return $subtotal;
    }
}
