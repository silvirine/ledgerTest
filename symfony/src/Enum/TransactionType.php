<?php

namespace App\Enum;

enum TransactionType: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
}
