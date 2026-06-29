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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['izin', 'cuti', 'sakit']); // izin: 5x/bln, cuti: tahunan, sakit: wajib surat dokter
            $table->date('start_date');
            $table->date('end_date');
            $table->text('alasan');
            $table->string('dokter_photo')->nullable(); // untuk sakit, upload surat dokter
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Add leave quota columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('izin_quota')->default(5)->after('role'); // 5 izin per bulan
            $table->integer('cuti_quota')->default(12)->after('izin_quota'); // 12 cuti per tahun
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['izin_quota', 'cuti_quota']);
        });
        Schema::dropIfExists('leave_requests');
    }
};