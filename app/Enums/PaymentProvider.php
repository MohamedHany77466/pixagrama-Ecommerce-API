<?php

namespace App\Enums;

enum PaymentProvider: string
{
    //
     case COD = 'cod';
    case PAYMOB = 'paymob';

    // values
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}