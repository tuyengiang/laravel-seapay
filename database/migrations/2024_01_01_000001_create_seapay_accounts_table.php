<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seapay_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Tên định danh tài khoản, dùng trong SeaPay::account(name)');
            $table->string('merchant_id')->comment('Merchant ID từ SeaPay');
            $table->string('api_key')->comment('API Key từ SeaPay');
            $table->string('secret_key')->comment('Secret Key để ký request');
            $table->string('description')->nullable()->comment('Mô tả tài khoản (ví dụ: Chi nhánh HCM)');
            $table->boolean('is_active')->default(true)->index()->comment('Tài khoản có đang hoạt động không');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seapay_accounts');
    }
};
