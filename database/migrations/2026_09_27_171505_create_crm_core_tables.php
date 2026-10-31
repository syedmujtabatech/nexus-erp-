<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Admin (1), Company Owner (2), Employee/Subuser (3)
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Update Users Table with core CRM columns
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->default(2)->after('id'); // Default Role 2 (Company Owner)
            $table->string('phone')->nullable()->after('email');
            $table->string('otp')->nullable();
            $table->boolean('is_otp_verified')->default(false);
            $table->foreignId('current_company_id')->nullable();
        });

        // 2. Companies Table
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); // Primary Owner
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('currency')->default('USD'); // Default Currency (USD, PKR, EUR, etc.)
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        // 3. User Company pivot with permissions
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->default(3);
            $table->json('permissions')->nullable(); // Granular permissions array e.g., ['create_invoice', 'manage_leads']
            $table->timestamps();
        });

        // 4. Customers Table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by_user_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('company_name')->nullable();
            $table->timestamps();
        });

        // 5. Leads Table
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by_user_id');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('contact_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal_sent', 'converted', 'lost'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Products & Services Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 7. Vendors Table
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // 8. Accounts (Bank / Cash)
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g. Petty Cash, HBL Bank, Stripe
            $table->string('account_number')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->timestamps();
        });

        // 9. Quotations Table
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by_user_id');
            $table->string('quotation_number'); // e.g., QT-001
            $table->integer('sequence_number'); // Numerical 1, 2, 3
            $table->date('date');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'accepted', 'declined', 'converted'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. Invoices Table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('quotation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by_user_id');
            $table->string('invoice_number'); // e.g. INV-001
            $table->integer('sequence_number'); // Strict reuse numeric index e.g., 1
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 11. Invoice & Quotation Line Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('itemable'); // Can belong to Invoice or Quotation
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamps();
        });

        // 12. Recycled Sequence Numbers Tracker (For Strict 001 reuse on delete)
        Schema::create('recycled_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'invoice' or 'quotation'
            $table->integer('sequence_number'); // e.g., 1 for 001
            $table->timestamps();
        });

        // 13. Expenses Table
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by_user_id');
            $table->string('category');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 14. Transactions & Payments Table (Includes Invoice Payments, Vendor Payments, Internal Transfers)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->onDelete('cascade'); // For internal transfer
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('expense_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by_user_id');
            $table->enum('type', ['income', 'expense', 'internal_transfer'])->default('income');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('recycled_sequences');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('products');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('roles');
    }
};
