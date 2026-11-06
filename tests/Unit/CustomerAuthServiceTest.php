<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CustomerAuthService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CustomerAuthServiceTest extends TestCase
{
    public static function mobileNumbers(): iterable
    {
        yield 'valid Iranian mobile' => ['09123456789', true];
        yield 'another valid prefix' => ['09901234567', true];
        yield 'missing digits' => ['0912345678', false];
        yield 'too many digits' => ['091234567890', false];
        yield 'country code unsupported' => ['+989123456789', false];
        yield 'non-digit characters' => ['0912345678x', false];
        yield 'SQL injection payload' => ["09123456789' OR 1=1", false];
        yield 'leading spaces' => [' 09123456789', false];
        yield 'empty' => ['', false];
    }

    #[DataProvider('mobileNumbers')]
    public function testMobileFormat(string $mobile, bool $expected): void
    {
        self::assertSame($expected, CustomerAuthService::validMobile($mobile));
    }
}
