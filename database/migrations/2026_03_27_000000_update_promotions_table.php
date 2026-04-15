<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Add new promotion types
            $table->enum('promotion_type', [
                'percentage',      // Percentage discount (e.g., 10% off)
                'fixed',          // Fixed amount discount
                'buy_one_get_one', // Buy 1 Get 1
                'free_shipping',  // Free shipping
                'bundle',         // Bundle deals
                'flash_sale'      // Flash sale (time-limited)
            ])->default('percentage')->after('type');

            // Rename old type column or keep it for backward compatibility
            // We'll use promotion_type for the new system

            // Rule-based application
            $table->enum('apply_to', [
                'all',            // Apply to all products
                'category',       // Apply to specific category
                'products',       // Apply to specific products
                'customer_group'  // Apply to specific customer groups
            ])->default('all')->after('promotion_type');

            // Category for category-based promotions
            $table->foreignId('category_id')->nullable()->after('apply_to')->constrained('categories')->nullOnDelete();

            // Product IDs for product-specific promotions (JSON array)
            $table->json('product_ids')->nullable()->after('category_id');

            // Customer group for group-based promotions
            $table->foreignId('customer_group_id')->nullable()->after('product_ids')->constrained('customer_groups')->nullOnDelete();

            // Usage limits
            $table->integer('per_user_limit')->nullable()->after('usage_limit');
            $table->integer('max_uses_per_user')->default(0)->after('per_user_limit');

            // Priority for sorting when multiple promotions apply
            $table->integer('priority')->default(0)->after('max_uses_per_user');

            // Make code nullable for automatic promotions
            $table->string('code')->nullable()->change();

            // Additional fields for specific promotion types
            $table->string('buy_quantity')->nullable()->after('priority'); // For Buy 1 Get 1
            $table->string('get_quantity')->nullable()->after('buy_quantity'); // For Buy 1 Get 1
            $table->decimal('minimum_quantity', 10, 0)->nullable()->after('get_quantity'); // Minimum quantity for BOGO

            // Flash sale specific
            $table->boolean('is_flash_sale')->default(false)->after('minimum_quantity');
            $table->integer('flash_sale_duration_minutes')->nullable()->after('is_flash_sale');

            // Bundle specific
            $table->json('bundle_product_ids')->nullable()->after('flash_sale_duration_minutes');
            $table->decimal('bundle_discount_percentage', 5, 2)->nullable()->after('bundle_product_ids');

            // Description
            $table->text('description')->nullable()->after('bundle_discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn([
                'promotion_type',
                'apply_to',
                'category_id',
                'product_ids',
                'customer_group_id',
                'per_user_limit',
                'max_uses_per_user',
                'priority',
                'buy_quantity',
                'get_quantity',
                'minimum_quantity',
                'is_flash_sale',
                'flash_sale_duration_minutes',
                'bundle_product_ids',
                'bundle_discount_percentage',
                'description',
            ]);

            // Restore code as nullable if needed
            $table->string('code')->nullable()->change();
        });
    }
};