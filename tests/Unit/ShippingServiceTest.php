<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ShippingService;
use DomainException;
use PHPUnit\Framework\TestCase;

final class ShippingServiceTest extends TestCase
{
    private static function method(array $changes=[]): array
    {
        return array_merge([
            'code'=>'standard','title'=>'ارسال استاندارد','is_active'=>1,
            'type'=>'flat','base_fee_toman'=>25000,
            'free_over_toman'=>null,'province_filter'=>null,
        ],$changes);
    }

    public function testFixedFee(): void
    {
        self::assertSame(25000,ShippingService::calculate(self::method(),120000,'تهران'));
    }

    public function testFreeShippingThreshold(): void
    {
        $method=self::method(['free_over_toman'=>300000]);
        self::assertSame(25000,ShippingService::calculate($method,299999,'تهران'));
        self::assertSame(0,ShippingService::calculate($method,300000,'تهران'));
    }

    public function testLocalPickupHasNoFee(): void
    {
        self::assertSame(0,ShippingService::calculate(self::method([
            'type'=>'pickup','base_fee_toman'=>50000,
        ]),5000,'تهران'));
    }

    public function testDisabledMethodIsNotQuoted(): void
    {
        $this->expectException(DomainException::class);
        ShippingService::calculate(self::method(['is_active'=>0]),5000,'تهران');
    }

    public function testProvinceRestriction(): void
    {
        $this->expectException(DomainException::class);
        ShippingService::calculate(self::method([
            'province_filter'=>'تهران,البرز',
        ]),5000,'اصفهان');
    }
}
