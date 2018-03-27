<?php

namespace App\Models;

trait FormatsCurrencyTrait
{
    protected function formatCurrency($number, $code)
    {
        return number_format($number, 2, ',', '.') . ' ' . $code;
    }
}