<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify plan to varchar so it supports trial, basic, advance, premium
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE companies MODIFY COLUMN plan VARCHAR(50) DEFAULT 'trial'");
        }

        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'max_users')) {
                $table->integer('max_users')->default(5)->after('plan');
            }
            if (!Schema::hasColumn('companies', 'max_invoices')) {
                $table->integer('max_invoices')->default(50)->after('max_users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'max_invoices')) {
                $table->dropColumn('max_invoices');
            }
            if (Schema::hasColumn('companies', 'max_users')) {
                $table->dropColumn('max_users');
            }
        });
    }
};
