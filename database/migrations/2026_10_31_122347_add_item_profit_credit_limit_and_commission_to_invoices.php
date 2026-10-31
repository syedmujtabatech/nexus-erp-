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
        // 1. Add credit_limit to customers table
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0)->after('company_name');
            }
        });

        // 2. Add sales_person_id, commission_rate, commission_amount to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'sales_person_id')) {
                $table->foreignId('sales_person_id')->nullable()->after('created_by_user_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('invoices', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(0)->after('notes');
            }
            if (!Schema::hasColumn('invoices', 'commission_amount')) {
                $table->decimal('commission_amount', 15, 2)->default(0)->after('commission_rate');
            }
        });

        // 3. Add cost_price to invoice_items table
        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'cost_price')) {
                $table->decimal('cost_price', 15, 2)->default(0)->after('unit_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'cost_price')) {
                $table->dropColumn('cost_price');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'commission_amount')) {
                $table->dropColumn('commission_amount');
            }
            if (Schema::hasColumn('invoices', 'commission_rate')) {
                $table->dropColumn('commission_rate');
            }
            if (Schema::hasColumn('invoices', 'sales_person_id')) {
                $table->dropConstrainedForeignId('sales_person_id');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'credit_limit')) {
                $table->dropColumn('credit_limit');
            }
        });
    }
};
