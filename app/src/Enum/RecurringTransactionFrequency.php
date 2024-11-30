<?php

namespace App\Enum;

enum RecurringTransactionFrequency: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
}
