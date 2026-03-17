<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Pages;
use App\Http\Controllers;
use App\Models\Sales\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends \App\Http\Controllers\Controller
{
    protected PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    // Show all active promotions (for frontend)
    public function index()
    {
        $promotions = $this->promotionService->getActivePromotions();
        return response()->json($promotions);
    }

    // Show a single promotion
    public function show($id)
    {
        $promotion = Promotion::findOrFail($id);
        return response()->json($promotion);
    }

    // Store a new promotion
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:promotions',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promotion = Promotion::create($request->all());
        return response()->json($promotion, 201);
    }

    // Update a promotion
    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:promotions,code,' . $id,
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $promotion->update($request->all());
        return response()->json($promotion);
    }

    // Delete a promotion
    public function destroy($id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();
        return response()->json(null, 204);
    }

    // Validate a promo code (for checkout)
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'order_total' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $orderTotal = $request->input('order_total', 0);
        $result = $this->promotionService->validatePromoCode($request->input('code'), $orderTotal);

        return response()->json($result);
    }

    // Apply a promo code (for checkout)
    public function apply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'order_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $this->promotionService->applyPromoCode(
            $request->input('code'),
            $request->input('order_total')
        );

        return response()->json($result);
    }

    // Get best available discount
    public function bestDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_total' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $this->promotionService->getBestAvailableDiscount(
            $request->input('order_total')
        );

        if (!$result) {
            return response()->json([
                'available' => false,
                'message' => 'No promotions available for this order',
            ]);
        }

        return response()->json([
            'available' => true,
            'promotion' => [
                'id' => $result['promotion']->id,
                'name' => $result['promotion']->name,
                'code' => $result['promotion']->code,
                'type' => $result['promotion']->type,
                'value' => $result['promotion']->value,
            ],
            'discount_amount' => $result['discount_amount'],
        ]);
    }
}
