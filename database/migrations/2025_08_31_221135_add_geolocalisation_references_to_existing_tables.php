<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Vérifier si une colonne existe
     */
    private function columnExists($table, $column)
    {
        return Schema::hasColumn($table, $column);
    }

    /**
     * Vérifier si un index existe
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // TABLE ORGANISATIONS : Vérifier et ajouter seulement les index manquants
        if (!$this->indexExists('organisations', 'org_prov_dept_idx')) {
            DB::statement('ALTER TABLE organisations ADD INDEX org_prov_dept_idx (province_ref_id, departement_ref_id)');
        }
        if (!$this->indexExists('organisations', 'org_zone_prov_idx')) {
            DB::statement('ALTER TABLE organisations ADD INDEX org_zone_prov_idx (zone_type, province_ref_id)');
        }
        if (!$this->indexExists('organisations', 'org_zone_comm_idx')) {
            DB::statement('ALTER TABLE organisations ADD INDEX org_zone_comm_idx (zone_type, commune_ville_ref_id)');
        }
        if (!$this->indexExists('organisations', 'org_zone_cant_idx')) {
            DB::statement('ALTER TABLE organisations ADD INDEX org_zone_cant_idx (zone_type, canton_ref_id)');
        }

        // ADHERENTS : Ajouter les colonnes si elles n'existent pas
        Schema::table('adherents', function (Blueprint $table) {
            if (!$this->columnExists('adherents', 'province_ref_id')) {
                $table->foreignId('province_ref_id')->nullable()->after('lieu_dit')->constrained('provinces')->onDelete('set null')->comment('Référence vers province');
            }
            if (!$this->columnExists('adherents', 'departement_ref_id')) {
                $table->foreignId('departement_ref_id')->nullable()->after('province_ref_id')->constrained('departements')->onDelete('set null')->comment('Référence vers département');
            }
            if (!$this->columnExists('adherents', 'commune_ville_ref_id')) {
                $table->foreignId('commune_ville_ref_id')->nullable()->after('departement_ref_id')->constrained('communes_villes')->onDelete('set null')->comment('Référence vers commune/ville (urbain)');
            }
            if (!$this->columnExists('adherents', 'arrondissement_ref_id')) {
                $table->foreignId('arrondissement_ref_id')->nullable()->after('commune_ville_ref_id')->constrained('arrondissements')->onDelete('set null')->comment('Référence vers arrondissement (urbain)');
            }
            if (!$this->columnExists('adherents', 'canton_ref_id')) {
                $table->foreignId('canton_ref_id')->nullable()->after('arrondissement_ref_id')->constrained('cantons')->onDelete('set null')->comment('Référence vers canton (rural)');
            }
            if (!$this->columnExists('adherents', 'regroupement_ref_id')) {
                $table->foreignId('regroupement_ref_id')->nullable()->after('canton_ref_id')->constrained('regroupements')->onDelete('set null')->comment('Référence vers regroupement (rural)');
            }
            if (!$this->columnExists('adherents', 'localite_ref_id')) {
                $table->foreignId('localite_ref_id')->nullable()->after('regroupement_ref_id')->constrained('localites')->onDelete('set null')->comment('Référence vers localité finale');
            }
        });

        // Ajouter les index pour adherents
        if (!$this->indexExists('adherents', 'adh_prov_dept_idx')) {
            DB::statement('ALTER TABLE adherents ADD INDEX adh_prov_dept_idx (province_ref_id, departement_ref_id)');
        }
        if (!$this->indexExists('adherents', 'adh_zone_prov_idx')) {
            DB::statement('ALTER TABLE adherents ADD INDEX adh_zone_prov_idx (zone_type, province_ref_id)');
        }
        if (!$this->indexExists('adherents', 'adh_org_prov_idx')) {
            DB::statement('ALTER TABLE adherents ADD INDEX adh_org_prov_idx (organisation_id, province_ref_id)');
        }

        // ETABLISSEMENTS : Ajouter les colonnes si elles n'existent pas
        Schema::table('etablissements', function (Blueprint $table) {
            if (!$this->columnExists('etablissements', 'province_ref_id')) {
                $table->foreignId('province_ref_id')->nullable()->after('longitude')->constrained('provinces')->onDelete('set null')->comment('Référence vers province');
            }
            if (!$this->columnExists('etablissements', 'departement_ref_id')) {
                $table->foreignId('departement_ref_id')->nullable()->after('province_ref_id')->constrained('departements')->onDelete('set null')->comment('Référence vers département');
            }
            if (!$this->columnExists('etablissements', 'commune_ville_ref_id')) {
                $table->foreignId('commune_ville_ref_id')->nullable()->after('departement_ref_id')->constrained('communes_villes')->onDelete('set null')->comment('Référence vers commune/ville (urbain)');
            }
            if (!$this->columnExists('etablissements', 'arrondissement_ref_id')) {
                $table->foreignId('arrondissement_ref_id')->nullable()->after('commune_ville_ref_id')->constrained('arrondissements')->onDelete('set null')->comment('Référence vers arrondissement (urbain)');
            }
            if (!$this->columnExists('etablissements', 'canton_ref_id')) {
                $table->foreignId('canton_ref_id')->nullable()->after('arrondissement_ref_id')->constrained('cantons')->onDelete('set null')->comment('Référence vers canton (rural)');
            }
            if (!$this->columnExists('etablissements', 'regroupement_ref_id')) {
                $table->foreignId('regroupement_ref_id')->nullable()->after('canton_ref_id')->constrained('regroupements')->onDelete('set null')->comment('Référence vers regroupement (rural)');
            }
            if (!$this->columnExists('etablissements', 'localite_ref_id')) {
                $table->foreignId('localite_ref_id')->nullable()->after('regroupement_ref_id')->constrained('localites')->onDelete('set null')->comment('Référence vers localité finale');
            }
        });

        // Ajouter les index pour etablissements
        if (!$this->indexExists('etablissements', 'etab_org_prov_idx')) {
            DB::statement('ALTER TABLE etablissements ADD INDEX etab_org_prov_idx (organisation_id, province_ref_id)');
        }
        if (!$this->indexExists('etablissements', 'etab_zone_prov_idx')) {
            DB::statement('ALTER TABLE etablissements ADD INDEX etab_zone_prov_idx (zone_type, province_ref_id)');
        }

        // FONDATEURS : Ajouter les colonnes si elles n'existent pas
        Schema::table('fondateurs', function (Blueprint $table) {
            if (!$this->columnExists('fondateurs', 'province_ref_id')) {
                $table->foreignId('province_ref_id')->nullable()->after('lieu_dit')->constrained('provinces')->onDelete('set null')->comment('Référence vers province');
            }
            if (!$this->columnExists('fondateurs', 'departement_ref_id')) {
                $table->foreignId('departement_ref_id')->nullable()->after('province_ref_id')->constrained('departements')->onDelete('set null')->comment('Référence vers département');
            }
            if (!$this->columnExists('fondateurs', 'commune_ville_ref_id')) {
                $table->foreignId('commune_ville_ref_id')->nullable()->after('departement_ref_id')->constrained('communes_villes')->onDelete('set null')->comment('Référence vers commune/ville (urbain)');
            }
            if (!$this->columnExists('fondateurs', 'arrondissement_ref_id')) {
                $table->foreignId('arrondissement_ref_id')->nullable()->after('commune_ville_ref_id')->constrained('arrondissements')->onDelete('set null')->comment('Référence vers arrondissement (urbain)');
            }
            if (!$this->columnExists('fondateurs', 'canton_ref_id')) {
                $table->foreignId('canton_ref_id')->nullable()->after('arrondissement_ref_id')->constrained('cantons')->onDelete('set null')->comment('Référence vers canton (rural)');
            }
            if (!$this->columnExists('fondateurs', 'regroupement_ref_id')) {
                $table->foreignId('regroupement_ref_id')->nullable()->after('canton_ref_id')->constrained('regroupements')->onDelete('set null')->comment('Référence vers regroupement (rural)');
            }
            if (!$this->columnExists('fondateurs', 'localite_ref_id')) {
                $table->foreignId('localite_ref_id')->nullable()->after('regroupement_ref_id')->constrained('localites')->onDelete('set null')->comment('Référence vers localité finale');
            }
        });

        // Ajouter les index pour fondateurs
        if (!$this->indexExists('fondateurs', 'fond_org_prov_idx')) {
            DB::statement('ALTER TABLE fondateurs ADD INDEX fond_org_prov_idx (organisation_id, province_ref_id)');
        }
        if (!$this->indexExists('fondateurs', 'fond_zone_prov_idx')) {
            DB::statement('ALTER TABLE fondateurs ADD INDEX fond_zone_prov_idx (zone_type, province_ref_id)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer les index
        DB::statement('DROP INDEX IF EXISTS fond_zone_prov_idx ON fondateurs');
        DB::statement('DROP INDEX IF EXISTS fond_org_prov_idx ON fondateurs');
        DB::statement('DROP INDEX IF EXISTS etab_zone_prov_idx ON etablissements');
        DB::statement('DROP INDEX IF EXISTS etab_org_prov_idx ON etablissements');
        DB::statement('DROP INDEX IF EXISTS adh_org_prov_idx ON adherents');
        DB::statement('DROP INDEX IF EXISTS adh_zone_prov_idx ON adherents');
        DB::statement('DROP INDEX IF EXISTS adh_prov_dept_idx ON adherents');
        DB::statement('DROP INDEX IF EXISTS org_zone_cant_idx ON organisations');
        DB::statement('DROP INDEX IF EXISTS org_zone_comm_idx ON organisations');
        DB::statement('DROP INDEX IF EXISTS org_zone_prov_idx ON organisations');
        DB::statement('DROP INDEX IF EXISTS org_prov_dept_idx ON organisations');

        // Supprimer les colonnes de référence
        Schema::table('fondateurs', function (Blueprint $table) {
            if ($this->columnExists('fondateurs', 'localite_ref_id')) {
                $table->dropForeign(['localite_ref_id']);
                $table->dropColumn('localite_ref_id');
            }
            if ($this->columnExists('fondateurs', 'regroupement_ref_id')) {
                $table->dropForeign(['regroupement_ref_id']);
                $table->dropColumn('regroupement_ref_id');
            }
            if ($this->columnExists('fondateurs', 'canton_ref_id')) {
                $table->dropForeign(['canton_ref_id']);
                $table->dropColumn('canton_ref_id');
            }
            if ($this->columnExists('fondateurs', 'arrondissement_ref_id')) {
                $table->dropForeign(['arrondissement_ref_id']);
                $table->dropColumn('arrondissement_ref_id');
            }
            if ($this->columnExists('fondateurs', 'commune_ville_ref_id')) {
                $table->dropForeign(['commune_ville_ref_id']);
                $table->dropColumn('commune_ville_ref_id');
            }
            if ($this->columnExists('fondateurs', 'departement_ref_id')) {
                $table->dropForeign(['departement_ref_id']);
                $table->dropColumn('departement_ref_id');
            }
            if ($this->columnExists('fondateurs', 'province_ref_id')) {
                $table->dropForeign(['province_ref_id']);
                $table->dropColumn('province_ref_id');
            }
        });

        Schema::table('etablissements', function (Blueprint $table) {
            if ($this->columnExists('etablissements', 'localite_ref_id')) {
                $table->dropForeign(['localite_ref_id']);
                $table->dropColumn('localite_ref_id');
            }
            if ($this->columnExists('etablissements', 'regroupement_ref_id')) {
                $table->dropForeign(['regroupement_ref_id']);
                $table->dropColumn('regroupement_ref_id');
            }
            if ($this->columnExists('etablissements', 'canton_ref_id')) {
                $table->dropForeign(['canton_ref_id']);
                $table->dropColumn('canton_ref_id');
            }
            if ($this->columnExists('etablissements', 'arrondissement_ref_id')) {
                $table->dropForeign(['arrondissement_ref_id']);
                $table->dropColumn('arrondissement_ref_id');
            }
            if ($this->columnExists('etablissements', 'commune_ville_ref_id')) {
                $table->dropForeign(['commune_ville_ref_id']);
                $table->dropColumn('commune_ville_ref_id');
            }
            if ($this->columnExists('etablissements', 'departement_ref_id')) {
                $table->dropForeign(['departement_ref_id']);
                $table->dropColumn('departement_ref_id');
            }
            if ($this->columnExists('etablissements', 'province_ref_id')) {
                $table->dropForeign(['province_ref_id']);
                $table->dropColumn('province_ref_id');
            }
        });

        Schema::table('adherents', function (Blueprint $table) {
            if ($this->columnExists('adherents', 'localite_ref_id')) {
                $table->dropForeign(['localite_ref_id']);
                $table->dropColumn('localite_ref_id');
            }
            if ($this->columnExists('adherents', 'regroupement_ref_id')) {
                $table->dropForeign(['regroupement_ref_id']);
                $table->dropColumn('regroupement_ref_id');
            }
            if ($this->columnExists('adherents', 'canton_ref_id')) {
                $table->dropForeign(['canton_ref_id']);
                $table->dropColumn('canton_ref_id');
            }
            if ($this->columnExists('adherents', 'arrondissement_ref_id')) {
                $table->dropForeign(['arrondissement_ref_id']);
                $table->dropColumn('arrondissement_ref_id');
            }
            if ($this->columnExists('adherents', 'commune_ville_ref_id')) {
                $table->dropForeign(['commune_ville_ref_id']);
                $table->dropColumn('commune_ville_ref_id');
            }
            if ($this->columnExists('adherents', 'departement_ref_id')) {
                $table->dropForeign(['departement_ref_id']);
                $table->dropColumn('departement_ref_id');
            }
            if ($this->columnExists('adherents', 'province_ref_id')) {
                $table->dropForeign(['province_ref_id']);
                $table->dropColumn('province_ref_id');
            }
        });
    }
};