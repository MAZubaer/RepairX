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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('user_id');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            $table->enum('subscription_plan', ['monthly', 'yearly'])->nullable()->after('subscription_status');
            $table->dateTime('current_period_end')->nullable()->after('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'subscription_plan',
                'current_period_end',
            ]);
        });
    }
};
