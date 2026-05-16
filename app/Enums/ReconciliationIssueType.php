<?php

declare(strict_types=1);

namespace App\Enums;

enum ReconciliationIssueType: string
{
    case MissingTransaction = 'missing_transaction';
    case AmountMismatch = 'amount_mismatch';
    case Duplicate = 'duplicate';
    case UnmatchedEvent = 'unmatched_event';

    public function label(): string
    {
        return match ($this) {
            self::MissingTransaction => 'Missing Transaction',
            self::AmountMismatch => 'Amount Mismatch',
            self::Duplicate => 'Duplicate',
            self::UnmatchedEvent => 'Unmatched Event',
        };
    }
}
