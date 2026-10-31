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
        // 1. Purchases (Supplier Bills / Stock Inward)
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->onDelete('cascade');
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('created_by_user_id');
                $table->string('purchase_number'); // e.g. BILL-001
                $table->integer('sequence_number'); // e.g. 1
                $table->date('date');
                $table->date('due_date')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax', 15, 2)->default(0);
                $table->decimal('discount', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled'])->default('unpaid');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Purchase Items Table
        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->string('description');
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('total_price', 15, 2)->default(0);
                $table->timestamps();
            });
        }

        // 3. Link purchases to transactions
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'purchase_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('purchase_id')->nullable()->after('expense_id')->constrained('purchases')->onDelete('set null');
            });
        }

        // 4. Add SaaS Plan to Companies
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (!Schema::hasColumn('companies', 'plan')) {
                    $table->enum('plan', ['trial', 'basic', 'advance'])->default('trial')->after('currency');
                }
                if (!Schema::hasColumn('companies', 'plan_expires_at')) {
                    $table->timestamp('plan_expires_at')->nullable()->after('plan');
                }
            });
        }

        // 5. Add Avatar to Users
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (Schema::hasColumn('companies', 'plan_expires_at')) {
                    $table->dropColumn('plan_expires_at');
                }
                if (Schema::hasColumn('companies', 'plan')) {
                    $table->dropColumn('plan');
                }
            });
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'purchase_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('purchase_id');
            });
        }

        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
