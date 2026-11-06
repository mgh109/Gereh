<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Payments\ZarinpalGateway;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ZarinpalGatewayTest extends TestCase
{
    public function testTomanToRialUsesExactIntegerArithmetic(): void
    {
        self::assertSame(10, ZarinpalGateway::tomanToRial(1));
        self::assertSame(1250000, ZarinpalGateway::tomanToRial(125000));
    }

    public function testRejectsZeroAndNegativePaymentAmount(): void
    {
        foreach ([0, -1] as $amount) {
            try {
                ZarinpalGateway::tomanToRial($amount);
                self::fail('Expected amount validation exception');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsOverflowAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ZarinpalGateway::tomanToRial(PHP_INT_MAX);
    }

    public function testRejectsUntrustedAuthority(): void
    {
        foreach (['', 'short', '../other', 'https://attacker.example', str_repeat('x', 121)] as $bad) {
            try {
                ZarinpalGateway::validateAuthority($bad);
                self::fail('Invalid authority should have been rejected');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testGeneratesOnlyProviderPaymentUrl(): void
    {
        $authority = str_repeat('A', 36);
        $gateway = new ZarinpalGateway(str_repeat('a', 36), true, 'https://shop.example.test/callback');
        self::assertSame(
            'https://sandbox.zarinpal.com/pg/StartPay/' . $authority,
            $gateway->startUrl($authority)
        );
    }

    public function testMissingCredentialsNeverContactGateway(): void
    {
        $this->expectException(RuntimeException::class);
        (new ZarinpalGateway('', true, 'https://shop.example.test/callback'))->request(5000, 'test');
    }
}
