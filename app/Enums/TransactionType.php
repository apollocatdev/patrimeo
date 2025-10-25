<?php

namespace App\Enums;

enum TransactionType: string
{
    case Expense = 'expense';
    case Transfer = 'transfer';
    case Income = 'income';

    public function label(): string
    {
        return match ($this) {
            self::Expense => __('Expense'),
            self::Transfer => __('Transfer'),
            self::Income => __('Income'),
        };
    }
}
