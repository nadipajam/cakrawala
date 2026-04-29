<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_details', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable()->after('boarding_status');
            $table->timestamp('boarded_at')->nullable()->after('checked_in_at');
            $table->string('checkin_reference', 80)->nullable()->after('boarded_at');
            $table->string('boarding_group', 10)->nullable()->after('checkin_reference');
            $table->string('gate_number', 20)->nullable()->after('boarding_group');
            $table->string('boarding_pass_pdf_path')->nullable()->after('gate_number');
            $table->string('boarding_pass_qr_path')->nullable()->after('boarding_pass_pdf_path');
        });

        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_detail_id')->nullable()->constrained('booking_details')->nullOnDelete();
            $table->string('addon_code', 80);
            $table->enum('addon_type', ['baggage', 'priority', 'service', 'insurance']);
            $table->string('addon_name', 160);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['selected', 'paid', 'cancelled'])->default('selected');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });

        Schema::create('booking_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('request_type', ['refund', 'reschedule', 'name_correction', 'cancel_request'])->default('refund');
            $table->text('reason');
            $table->foreignId('preferred_flight_id')->nullable()->constrained('flights')->nullOnDelete();
            $table->enum('status', ['submitted', 'in_review', 'approved', 'rejected', 'completed'])->default('submitted');
            $table->text('admin_notes')->nullable();
            $table->decimal('resolution_amount', 12, 2)->nullable();
            $table->text('resolution_details')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'request_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_change_requests');
        Schema::dropIfExists('booking_addons');

        Schema::table('booking_details', function (Blueprint $table) {
            $table->dropColumn([
                'checked_in_at',
                'boarded_at',
                'checkin_reference',
                'boarding_group',
                'gate_number',
                'boarding_pass_pdf_path',
                'boarding_pass_qr_path',
            ]);
        });
    }
};
