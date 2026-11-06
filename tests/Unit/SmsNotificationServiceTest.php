<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\SmsNotificationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SmsNotificationServiceTest extends TestCase
{
    public function testEachOrderEventHasFixedExpectedVariables(): void
    {
        self::assertSame(
            ['order_number','amount'],
            SmsNotificationService::EVENTS['payment_success']['variables']
        );
        self::assertSame(
            ['order_number','tracking_code'],
            SmsNotificationService::EVENTS['order_shipped']['variables']
        );
    }

    public function testMultiplePatternVariablesHaveTheDocumentedOrder(): void
    {
        self::assertSame(
            'GR-2026-ABCD;125000',
            SmsNotificationService::orderedText('payment_success',[
                'amount'=>'125000','order_number'=>'GR-2026-ABCD',
            ])
        );
        self::assertSame(
            'GR-001;TRACK-123',
            SmsNotificationService::orderedText('order_shipped',[
                'tracking_code'=>'TRACK-123','order_number'=>'GR-001',
            ])
        );
    }

    public function testUnknownOrUnsafeTemplateValuesFailClosed(): void
    {
        foreach ([
            ['unknown_event',[]],
            ['order_shipped',['order_number'=>'GR-1']],
            ['order_placed',['order_number'=>'GR-1;injected']],
            ['order_placed',['order_number'=>"GR-1\nInjected"]],
        ] as [$event,$values]) {
            try {
                SmsNotificationService::orderedText($event,$values);
                self::fail('Invalid pattern was unexpectedly allowed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }
}
