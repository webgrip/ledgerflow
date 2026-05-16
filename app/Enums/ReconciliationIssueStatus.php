<?php

declare(strict_types=1);

namespace App\Enums;

enum ReconciliationIssueStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case Ignored = 'ignored';
}
