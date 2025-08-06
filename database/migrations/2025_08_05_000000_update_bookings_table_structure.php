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
        Schema::table('bookings', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('bookings', 'bookable_type')) {
                $table->dropMorphs('bookable');
            }
            if (Schema::hasColumn('bookings', 'start_date')) {
                $table->dropColumn(['start_date', 'end_date']);
            }
            if (Schema::hasColumn('bookings', 'total_price')) {
                $table->dropColumn('total_price');
            }
            if (Schema::hasColumn('bookings', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            
            // Add new columns
            if (!Schema::hasColumn('bookings', 'guest_id')) {
                $table->foreignId('guest_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('bookings', 'property_id')) {
                $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            }
            if (!Schema::hasColumn('bookings', 'check_in_date')) {
                $table->date('check_in_date');
            }
            if (!Schema::hasColumn('bookings', 'check_out_date')) {
                $table->date('check_out_date');
            }
            if (!Schema::hasColumn('bookings', 'number_of_nights')) {
                $table->integer('number_of_nights');
            }
            if (!Schema::hasColumn('bookings', 'price_per_night')) {
                $table->decimal('price_per_night', 10, 2);
            }
            if (!Schema::hasColumn('bookings', 'total_amount')) {
                $table->decimal('total_amount', 10, 2);
            }
            if (!Schema::hasColumn('bookings', 'special_requests')) {
                $table->text('special_requests')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Reverse the changes
            $table->dropForeign(['guest_id']);
            $table->dropForeign(['property_id']);
            $table->dropColumn([
                'guest_id',
                'property_id',
                'check_in_date',
                'check_out_date',
                'number_of_nights',
                'price_per_night',
                'total_amount',
                'special_requests'
            ]);
            
            // Add back old columns
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('bookable');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('total_price', 10, 2);
        });
    }
};
