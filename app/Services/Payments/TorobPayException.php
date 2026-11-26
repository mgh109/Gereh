<?php

namespace App\Services\Payments;

use RuntimeException;

final class TorobPayException extends RuntimeException
{
    public function __construct(public readonly string $reason, int $httpStatus = 0)
    {
        parent::__construct('TorobPay: ' . $reason, $httpStatus);
    }
}
