<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');

            // return_status: null (no return), requested, approved, rejected, refunded
            $table->string('return_status')->nullable()->after('cancellation_reason');
            $table->text('return_reason')->nullable()->after('return_status');
            $table->timestamp('return_requested_at')->nullable()->after('return_reason');
            $table->timestamp('return_decided_at')->nullable()->after('return_requested_at');

            $table->decimal('refund_amount', 10, 2)->nullable()->after('return_decided_at');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->string('stripe_refund_id')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivered_at', 'cancelled_at', 'cancellation_reason',
                'return_status', 'return_reason', 'return_requested_at', 'return_decided_at',
                'refund_amount', 'refunded_at', 'stripe_refund_id',
            ]);
        });
    }
};
