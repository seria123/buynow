<?php

namespace Tests\Unit;

use App\Models\Sales\Promotion;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionTest extends TestCase
{
    use RefreshDatabase;

    protected PromotionService $promotionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->promotionService = new PromotionService();
    }

    /** @test */
    public function it_can_create_a_promotion()
    {
        $promotion = Promotion::create([
            'name' => 'Summer Sale',
            'code' => 'SUMMER20',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('promotions', [
            'code' => 'SUMMER20',
            'name' => 'Summer Sale',
        ]);
    }

    /** @test */
    public function it_validates_promo_code_successfully()
    {
        $promotion = Promotion::create([
            'name' => 'Test Promotion',
            'code' => 'TESTCODE',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $result = $this->promotionService->validatePromoCode('TESTCODE', 100);

        $this->assertTrue($result['valid']);
        $this->assertEquals($promotion->id, $result['promotion']->id);
    }

    /** @test */
    public function it_rejects_invalid_promo_code()
    {
        $result = $this->promotionService->validatePromoCode('INVALID', 100);

        $this->assertFalse($result['valid']);
        $this->assertEquals('Invalid promo code', $result['message']);
    }

    /** @test */
    public function it_rejects_inactive_promotion()
    {
        $promotion = Promotion::create([
            'name' => 'Inactive Promo',
            'code' => 'INACTIVE',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => false,
        ]);

        $result = $this->promotionService->validatePromoCode('INACTIVE', 100);

        $this->assertFalse($result['valid']);
        $this->assertEquals('This promo code is not active', $result['message']);
    }

    /** @test */
    public function it_rejects_expired_promotion()
    {
        $promotion = Promotion::create([
            'name' => 'Expired Promo',
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $result = $this->promotionService->validatePromoCode('EXPIRED', 100);

        $this->assertFalse($result['valid']);
        $this->assertEquals('This promo code has expired', $result['message']);
    }

    /** @test */
    public function it_rejects_upcoming_promotion()
    {
        $promotion = Promotion::create([
            'name' => 'Upcoming Promo',
            'code' => 'UPCOMING',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);

        $result = $this->promotionService->validatePromoCode('UPCOMING', 100);

        $this->assertFalse($result['valid']);
        $this->assertEquals('This promo code is not yet active', $result['message']);
    }

    /** @test */
    public function it_enforces_minimum_order_amount()
    {
        $promotion = Promotion::create([
            'name' => 'Min Order Promo',
            'code' => 'MINORDER',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'minimum_order_amount' => 100,
        ]);

        // Order below minimum
        $result = $this->promotionService->validatePromoCode('MINORDER', 50);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Minimum order amount', $result['message']);

        // Order meets minimum
        $result = $this->promotionService->validatePromoCode('MINORDER', 100);
        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function it_enforces_usage_limit()
    {
        $promotion = Promotion::create([
            'name' => 'Limited Promo',
            'code' => 'LIMITED',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'usage_limit' => 5,
            'used_count' => 5,
        ]);

        $result = $this->promotionService->validatePromoCode('LIMITED', 100);

        $this->assertFalse($result['valid']);
        $this->assertEquals('This promo code has reached its usage limit', $result['message']);
    }

    /** @test */
    public function it_calculates_percentage_discount_correctly()
    {
        $promotion = Promotion::create([
            'name' => 'Percentage Promo',
            'code' => 'PERCENT',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        $discount = $this->promotionService->calculateDiscount($promotion, 100);

        $this->assertEquals(20, $discount);
    }

    /** @test */
    public function it_calculates_fixed_discount_correctly()
    {
        $promotion = Promotion::create([
            'name' => 'Fixed Promo',
            'code' => 'FIXED',
            'type' => 'fixed',
            'value' => 15,
            'is_active' => true,
        ]);

        $discount = $this->promotionService->calculateDiscount($promotion, 100);

        $this->assertEquals(15, $discount);
    }

    /** @test */
    public function it_does_not_exceed_order_total_for_fixed_discount()
    {
        $promotion = Promotion::create([
            'name' => 'Fixed Promo',
            'code' => 'FIXEDHIGH',
            'type' => 'fixed',
            'value' => 150,
            'is_active' => true,
        ]);

        $discount = $this->promotionService->calculateDiscount($promotion, 100);

        $this->assertEquals(100, $discount);
    }

    /** @test */
    public function it_returns_zero_discount_when_below_minimum_order()
    {
        $promotion = Promotion::create([
            'name' => 'Min Order Promo',
            'code' => 'MINORDER2',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'minimum_order_amount' => 100,
        ]);

        $discount = $this->promotionService->calculateDiscount($promotion, 50);

        $this->assertEquals(0, $discount);
    }

    /** @test */
    public function it_can_apply_promo_code_successfully()
    {
        $promotion = Promotion::create([
            'name' => 'Apply Promo',
            'code' => 'APPLY',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $result = $this->promotionService->applyPromoCode('APPLY', 100);

        $this->assertTrue($result['valid']);
        $this->assertEquals(10, $result['discount_amount']);
        $this->assertEquals('APPLY', $result['promotion_code']);
    }

    /** @test */
    public function it_can_increment_usage_count()
    {
        $promotion = Promotion::create([
            'name' => 'Usage Promo',
            'code' => 'USAGE',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'used_count' => 0,
        ]);

        $this->assertEquals(0, $promotion->used_count);

        $this->promotionService->recordUsage($promotion);

        $this->assertEquals(1, $promotion->fresh()->used_count);
    }

    /** @test */
    public function it_auto_uppercases_promo_codes()
    {
        $promotion = Promotion::create([
            'name' => 'Lowercase Promo',
            'code' => 'lowercase',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->assertEquals('LOWERCASE', $promotion->fresh()->code);
    }
}
