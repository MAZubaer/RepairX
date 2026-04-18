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
        if (! Schema::hasTable('service_records')) {
            return;
        }

        Schema::table('service_records', function (Blueprint $table) {
            if (! Schema::hasColumn('service_records', 'in_progress_emailed_at')) {
                $table->timestamp('in_progress_emailed_at')->nullable()->after('rating');
            }

            if (! Schema::hasColumn('service_records', 'completed_emailed_at')) {
                $table->timestamp('completed_emailed_at')->nullable()->after('in_progress_emailed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('service_records')) {
            return;
        }

        Schema::table('service_records', function (Blueprint $table) {
            if (Schema::hasColumn('service_records', 'completed_emailed_at')) {
                $table->dropColumn('completed_emailed_at');
            }

            if (Schema::hasColumn('service_records', 'in_progress_emailed_at')) {
                $table->dropColumn('in_progress_emailed_at');
            }
        });
    }
};
