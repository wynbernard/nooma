<?php

function apply_non_pos_sale_totals(array &$payload): void
{
    $channel = strtoupper(trim((string) ($payload["saleChannel"] ?? "")));
    if ($channel !== "NON POS") {
        return;
    }

    $money = static function ($value): float {
        $value = str_replace(",", "", (string) $value);
        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    };

    $totalSale = $money($payload["kitchenSale"] ?? 0)
        + $money($payload["barSale"] ?? 0)
        + $money($payload["corkage"] ?? 0)
        + $money($payload["giftCheckSale"] ?? 0)
        + $money($payload["otherProducts"] ?? 0);
    $grandTotal = $totalSale + $money($payload["serviceCharge"] ?? 0);

    $payload["totalSale"] = number_format($totalSale, 2, ".", "");
    $payload["grandTotal"] = number_format($grandTotal, 2, ".", "");
}
