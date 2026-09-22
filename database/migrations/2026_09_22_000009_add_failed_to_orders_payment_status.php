<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Postgres enforces the enum('unpaid', 'paid') as a CHECK constraint rather
 * than a native enum type, so widening it means replacing that constraint
 * rather than altering the column.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_status_check');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_status_check CHECK (payment_status IN ('unpaid', 'paid', 'failed'))");
    }

    public function down(): void
    {
        // Any existing 'failed' rows would violate the old constraint, so fold them back to 'unpaid' first.
        DB::statement("UPDATE orders SET payment_status = 'unpaid' WHERE payment_status = 'failed'");
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_status_check');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_status_check CHECK (payment_status IN ('unpaid', 'paid'))");
    }
};
