<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->foreignId('domaine_activite_id')
                ->nullable()
                ->after('organisation_type_id')
                ->constrained('domaines_activite')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropForeign(['domaine_activite_id']);
            $table->dropColumn('domaine_activite_id');
        });
    }
};
