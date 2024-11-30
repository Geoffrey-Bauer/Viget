<?php

namespace App\Enum;

enum TransactionType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
}
