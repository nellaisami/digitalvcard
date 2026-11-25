<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tax;

class PlanTaxCalculator
{
    /**
     * Active taxes are additive and each is calculated against the base price.
     *
     * @return array{base: float, taxes: array<int, array{name: string, rate: float, amount: float}>, total: float}
     */
    public function calculate(Plan $plan): array
    {
        $base = (float) $plan->price;
        $baseCents = (int) round($base * 100);
        $taxes = Tax::query()->where('status', 'active')->orderBy('tax_name')->get()
            ->map(function (Tax $tax) use ($baseCents): array {
                $rateUnits = (int) round((float) $tax->tax_value * 10000);
                $taxCents = intdiv($baseCents * $rateUnits + 500000, 1000000);

                return [
                    'name' => $tax->tax_name,
                    'rate' => (float) $tax->tax_value,
                    'amount' => $taxCents / 100,
                ];
            })
            ->all();

        return [
            'base' => $base,
            'taxes' => $taxes,
            'total' => ($baseCents + (int) round(array_sum(array_column($taxes, 'amount')) * 100)) / 100,
        ];
    }
}
