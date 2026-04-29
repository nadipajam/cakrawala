<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payer_name', 120)->nullable()->after('payment_method');
            $table->string('payer_phone', 30)->nullable()->after('payer_name');
            $table->string('payer_bank_name', 120)->nullable()->after('payer_phone');
            $table->string('payer_bank_account_number', 60)->nullable()->after('payer_bank_name');
            $table->text('payment_notes')->nullable()->after('payer_bank_account_number');
            $table->timestamp('submitted_at')->nullable()->after('proof_file');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payer_name',
                'payer_phone',
                'payer_bank_name',
                'payer_bank_account_number',
                'payment_notes',
                'submitted_at',
            ]);
        });
    }
};
