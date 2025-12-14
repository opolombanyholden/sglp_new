<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier le ENUM type_document pour ajouter 'accuse_reception'
        DB::statement("ALTER TABLE `document_templates` MODIFY COLUMN `type_document` ENUM(
            'accuse_reception',
            'recepisse_provisoire',
            'recepisse_definitif',
            'recepisse_enregistrement',
            'certificat_enregistrement',
            'certificat_conformite',
            'attestation',
            'autorisation',
            'agrement',
            'notification_rejet',
            'demande_complement',
            'convocation',
            'pv_commission',
            'decision',
            'courrier_officiel',
            'autre'
        ) NOT NULL COMMENT 'Type de document généré'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retirer 'accuse_reception' du ENUM
        DB::statement("ALTER TABLE `document_templates` MODIFY COLUMN `type_document` ENUM(
            'recepisse_provisoire',
            'recepisse_definitif',
            'recepisse_enregistrement',
            'certificat_enregistrement',
            'certificat_conformite',
            'attestation',
            'autorisation',
            'agrement',
            'notification_rejet',
            'demande_complement',
            'convocation',
            'pv_commission',
            'decision',
            'courrier_officiel',
            'autre'
        ) NOT NULL COMMENT 'Type de document généré'");
    }
};
