<?php

namespace App\Services;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Get the current cart from session.
     *
     * @return array{visit_date: string|null, items: array<int, array{type: string, id: int, name: string, price: float, quantity: int}>}
     */
    public function getCart(): array
    {
        return session(self::SESSION_KEY, [
            'visit_date' => null,
            'items' => [],
        ]);
    }

    /**
     * Set the visit date for the cart.
     */
    public function setVisitDate(string $date): void
    {
        $cart = $this->getCart();
        $cart['visit_date'] = $date;
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Add or update an item in the cart.
     */
    public function addItem(string $type, int $id, string $name, float $price, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $key = "{$type}_{$id}";

        $existingIndex = null;
        foreach ($cart['items'] as $index => $item) {
            if ($item['type'] === $type && $item['id'] === $id) {
                $existingIndex = $index;
                break;
            }
        }

        if ($existingIndex !== null) {
            $cart['items'][$existingIndex]['quantity'] = $quantity;
        } else {
            $cart['items'][] = [
                'type' => $type,
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'quantity' => $quantity,
            ];
        }

        // Remove items with zero quantity
        $cart['items'] = array_values(array_filter(
            $cart['items'],
            fn ($item) => $item['quantity'] > 0
        ));

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(string $type, int $id): void
    {
        $cart = $this->getCart();

        $cart['items'] = array_values(array_filter(
            $cart['items'],
            fn ($item) => ! ($item['type'] === $type && $item['id'] === $id)
        ));

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * Get the total number of items.
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();

        return array_sum(array_column($cart['items'], 'quantity'));
    }

    /**
     * Get the subtotal of the cart.
     */
    public function getSubtotal(): float
    {
        $cart = $this->getCart();

        return array_reduce(
            $cart['items'],
            fn ($carry, $item) => $carry + ($item['price'] * $item['quantity']),
            0
        );
    }

    /**
     * Check if the cart has any items.
     */
    public function hasItems(): bool
    {
        return ! empty($this->getCart()['items']);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
