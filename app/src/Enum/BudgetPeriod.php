<?php

namespace App\Enum;

enum BudgetPeriod: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
}
