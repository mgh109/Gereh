<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\MelipayamakClient;
use PHPUnit\Framework\TestCase;

final class MelipayamakClientTest extends TestCase
{
    public function testAcceptsOnlyPositiveReceiptWithSuccessfulStatus(): void
    {
        self::assertTrue(MelipayamakClient::isAccepted(['RetStatus'=>1,'Value'=>'123456']));
        self::assertTrue(MelipayamakClient::isAccepted(['retStatus'=>1,'value'=>12]));
        self::assertFalse(MelipayamakClient::isAccepted(['RetStatus'=>0,'Value'=>'123456']));
        self::assertFalse(MelipayamakClient::isAccepted(['RetStatus'=>1,'Value'=>'0']));
        self::assertFalse(MelipayamakClient::isAccepted(['RetStatus'=>1,'Value'=>'-8']));
        self::assertFalse(MelipayamakClient::isAccepted(['RetStatus'=>1,'Value'=>'error']));
        self::assertFalse(MelipayamakClient::isAccepted([]));
        self::assertFalse(MelipayamakClient::isAccepted('success'));
    }
}
