<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            if (! Schema::hasColumn('results', 'isp_profile_key')) {
                $table->string('isp_profile_key')->nullable()->after('service')->index();
            }

            if (! Schema::hasColumn('results', 'isp_profile_name')) {
                $table->string('isp_profile_name')->nullable()->after('isp_profile_key');
            }

            if (! Schema::hasColumn('results', 'source_ip')) {
                $table->string('source_ip', 45)->nullable()->after('isp_profile_name')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            if (Schema::hasColumn('results', 'source_ip')) {
                $table->dropIndex(['source_ip']);
                $table->dropColumn('source_ip');
            }

            if (Schema::hasColumn('results', 'isp_profile_key')) {
                $table->dropIndex(['isp_profile_key']);
                $table->dropColumn('isp_profile_key');
            }

            if (Schema::hasColumn('results', 'isp_profile_name')) {
                $table->dropColumn('isp_profile_name');
            }
        });
    }
};
