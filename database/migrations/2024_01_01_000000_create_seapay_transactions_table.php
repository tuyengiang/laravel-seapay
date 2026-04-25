<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seapay_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('account')->index()->comment('Tên tài khoản SeaPay đã dùng');
            $table->string('order_id')->index()->comment('Mã đơn hàng từ hệ thống của bạn');
            $table->string('transaction_id')->nullable()->index()->comment('Mã giao dịch từ SeaPay');
            $table->decimal('amount', 15, 2)->comment('Số tiền');
            $table->string('currency', 10)->default('VND');
            $table->string('status', 50)->default('pending')->comment('pending|success|failed|cancelled|expired|refunded');
            $table->string('payment_url')->nullable();
            $table->text('description')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->json('metadata')->nullable();
            $table->json('raw_response')->nullable()->comment('Response thô từ SeaPay');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['account', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seapay_transactions');
    }
};
