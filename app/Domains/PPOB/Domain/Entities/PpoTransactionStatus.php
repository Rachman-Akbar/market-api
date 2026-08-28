<?php

declare(strict_types=1);

namespace App\Domains\PPOB\Domain\Entities;

enum PpoTransactionStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Success = 'success';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';
}
