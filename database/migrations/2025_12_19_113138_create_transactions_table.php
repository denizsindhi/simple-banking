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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // deposit, withdrawal, transfer
            $table->bigInteger('amount'); // stored in cents
            $table->timestamp('timestamp');
            $table->foreignId('source_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('target_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->string('status'); // success, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->index('source_account_id');
            $table->index('target_account_id');
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
