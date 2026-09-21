<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('shipping_address');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');
            $table->string('coupon_code')->nullable()->after('tax_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_code');
            $table->foreignId('address_id')->nullable()->after('discount_amount')->constrained('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('address_id');
            $table->dropColumn(['subtotal', 'tax_rate', 'tax_amount', 'coupon_code', 'discount_amount']);
        });
    }
};
