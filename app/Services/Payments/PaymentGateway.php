<?php

namespace App\Services\Payments;

interface PaymentGateway
{
    public function provider(): string;

    public static function validateAuthority(string $authority): string;

    public function startUrl(string $authority): string;

    public function request(int $amountToman, string $description, ?string $mobile = null): array;

    public function verify(int $amountToman, string $authority): array;
}
