<?php

namespace App\Services;

class CarExpenseBreakdownService
{
    /**
     * @param  mixed  $items
     * @return array<int, array{description: string, purchase: int, sales: int|null}>
     */
    public static function normalizeItems($items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $result = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $description = trim((string) ($item['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            $purchase = max(0, (int) ($item['purchase'] ?? 0));
            $salesRaw = $item['sales'] ?? null;
            $sales = ($salesRaw === null || $salesRaw === '')
                ? null
                : max(0, (int) $salesRaw);

            $result[] = [
                'description' => $description,
                'purchase' => $purchase,
                'sales' => $sales,
            ];
        }

        return $result;
    }

    public static function hasBreakdown(?array $breakdown): bool
    {
        return is_array($breakdown) && count($breakdown) > 0;
    }

    public static function sumPurchase(array $items): int
    {
        return (int) array_sum(array_column($items, 'purchase'));
    }

    public static function sumSales(array $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $sales = $item['sales'] ?? null;
            $total += ($sales !== null)
                ? (int) $sales
                : (int) ($item['purchase'] ?? 0);
        }

        return $total;
    }

    /**
     * @return array<int, array{description: string, purchase: int, sales: int|null}>
     */
    public static function initSalesFromPurchase(array $items): array
    {
        return array_map(function (array $item): array {
            if (! isset($item['sales']) || $item['sales'] === null) {
                $item['sales'] = (int) ($item['purchase'] ?? 0);
            } else {
                $item['sales'] = max(0, (int) $item['sales']);
            }

            return $item;
        }, $items);
    }

    /**
     * @return array<int, string>
     */
    public static function linesForPrint(array $items, string $side = 'sales'): array
    {
        $lines = [];

        foreach ($items as $item) {
            $description = trim((string) ($item['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            $amount = $side === 'purchase'
                ? (int) ($item['purchase'] ?? 0)
                : (($item['sales'] ?? null) !== null
                    ? (int) $item['sales']
                    : (int) ($item['purchase'] ?? 0));

            $lines[] = $amount.'$ '.$description;
        }

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    public static function expenseLinesFromNote(?string $note): array
    {
        if (! $note) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $note))
        ));
    }
}
