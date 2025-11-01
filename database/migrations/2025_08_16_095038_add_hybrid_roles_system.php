<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration finale corrigée pour le système hybride rôles/permissions
 * Évite les doublons d'index et vérifie l'existence de toutes les structures
 */
class AddHybridRolesSystem extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Ajouter la colonne role_id à la table users (si pas déjà présente)
        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable()->after('role');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
                $table->index('role_id');
            });
        }

        // 2. Ajouter colonnes étendues à users (vérification individuelle)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])
                      ->default('active')->after('role_id');
            }
            
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('last_login_at');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }

            // Colonnes profil gabonais (vérifier si elles n'existent pas déjà)
            if (!Schema::hasColumn('users', 'nom')) {
                $table->string('nom')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('users', 'prenom')) {
                $table->string('prenom')->nullable()->after('nom');
            }
            
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip')->unique()->nullable()->after('prenom');
            }
            
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('nip');
            }
            
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }

            // Colonnes sécurité 2FA
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('city');
            }
            
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('two_factor_recovery_codes');
            }
        });

        // 3. Ajouter les index seulement s'ils n'existent pas
        $this->addIndexesSafely();

        // 4. Vérifier la structure de la table roles et ajouter is_system si manquante
        if (!Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('is_active');
            });
        }

        // 5. Créer les rôles système par défaut s'ils n'existent pas
        $this->createDefaultRoles();

        // 6. Migrer les utilisateurs existants vers le nouveau système
        $this->migrateExistingUsers();

        // 7. Créer la table de mapping role -> permissions par défaut
        $this->createRolePermissionMapping();
    }

    /**
     * Ajouter les index de manière sécurisée
     */
    private function addIndexesSafely()
    {
        $indexes = [
            'users_role_status_index' => ['role', 'status'],
            'users_is_active_status_index' => ['is_active', 'status'],
            'users_last_login_at_index' => ['last_login_at']
        ];

        foreach ($indexes as $indexName => $columns) {
            if (!$this->indexExists('users', $indexName)) {
                try {
                    Schema::table('users', function (Blueprint $table) use ($columns) {
                        $table->index($columns);
                    });
                    echo "✅ Index créé: " . implode(', ', $columns) . "\n";
                } catch (\Exception $e) {
                    echo "⚠️ Index ignoré (existe déjà): " . implode(', ', $columns) . "\n";
                }
            } else {
                echo "ℹ️ Index existe déjà: " . implode(', ', $columns) . "\n";
            }
        }
    }

    /**
     * Vérifier si un index existe
     */
    private function indexExists($table, $indexName)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            foreach ($indexes as $index) {
                if ($index->Key_name === $indexName) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les foreign keys d'abord (avec vérification)
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
            
            if (Schema::hasColumn('users', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('users', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            
            // Supprimer les autres colonnes
            $columnsToRemove = [
                'status', 'last_login_at', 'nom', 'prenom', 'nip', 'phone', 
                'address', 'city', 'two_factor_secret', 'two_factor_recovery_codes', 'is_verified'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Supprimer is_system de roles si on l'a ajoutée
        if (Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('is_system');
            });
        }
    }

    /**
     * Créer les rôles système par défaut (version adaptée)
     */
    private function createDefaultRoles()
    {
        // Vérifier quelles colonnes existent dans la table roles
        $roleColumns = Schema::getColumnListing('roles');
        
        $systemRoles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrateur',
                'description' => 'Accès complet au système SGLP',
                'color' => '#8b1538',
                'level' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'admin_general',
                'display_name' => 'Administrateur Général',
                'description' => 'Administration générale du système',
                'color' => '#003f7f',
                'level' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'admin_associations',
                'display_name' => 'Admin Associations',
                'description' => 'Gestion spécialisée des associations et ONGs',
                'color' => '#009e3f',
                'level' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'admin_confessions',
                'display_name' => 'Admin Confessions',
                'description' => 'Gestion spécialisée des confessions religieuses',
                'color' => '#ffcd00',
                'level' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'admin_politiques',
                'display_name' => 'Admin Politiques',
                'description' => 'Gestion spécialisée des partis politiques',
                'color' => '#007bff',
                'level' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'moderateur',
                'display_name' => 'Modérateur',
                'description' => 'Validation et modération des contenus',
                'color' => '#17a2b8',
                'level' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'operateur',
                'display_name' => 'Opérateur',
                'description' => 'Saisie et consultation des données',
                'color' => '#28a745',
                'level' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'auditeur',
                'display_name' => 'Auditeur',
                'description' => 'Consultation uniquement - Accès lecture seule',
                'color' => '#6c757d',
                'level' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($systemRoles as $roleData) {
            // Vérifier si le rôle existe déjà
            $existingRole = DB::table('roles')->where('name', $roleData['name'])->first();
            
            if (!$existingRole) {
                // Adapter les données selon les colonnes disponibles
                $insertData = [];
                foreach ($roleData as $key => $value) {
                    if (in_array($key, $roleColumns)) {
                        $insertData[$key] = $value;
                    }
                }
                
                // Ajouter is_system si la colonne existe
                if (in_array('is_system', $roleColumns)) {
                    $insertData['is_system'] = true;
                }
                
                $insertData['created_at'] = now();
                $insertData['updated_at'] = now();
                
                DB::table('roles')->insert($insertData);
                
                echo "✅ Rôle créé: {$roleData['display_name']}\n";
            } else {
                echo "ℹ️ Rôle existe déjà: {$roleData['display_name']}\n";
                
                // Mettre à jour is_system pour les rôles existants si la colonne existe
                if (in_array('is_system', $roleColumns)) {
                    DB::table('roles')
                        ->where('name', $roleData['name'])
                        ->update(['is_system' => true]);
                }
            }
        }
    }

    /**
     * Migrer les utilisateurs existants vers le nouveau système
     */
    private function migrateExistingUsers()
    {
        // Mapping ancien système -> nouveau système
        $roleMapping = [
            'admin' => 'admin_general',
            'agent' => 'moderateur', 
            'operator' => 'operateur',
            'visitor' => 'auditeur',
        ];

        foreach ($roleMapping as $oldRole => $newRoleName) {
            $newRole = DB::table('roles')->where('name', $newRoleName)->first();
            
            if ($newRole) {
                $updatedCount = DB::table('users')
                    ->where('role', $oldRole)
                    ->whereNull('role_id')
                    ->update([
                        'role_id' => $newRole->id,
                        'status' => 'active',
                        'updated_at' => now()
                    ]);
                
                if ($updatedCount > 0) {
                    echo "✅ Migré {$updatedCount} utilisateur(s) de '{$oldRole}' vers '{$newRoleName}'\n";
                }
            }
        }

        // Mettre à jour les colonnes nom/prenom à partir de 'name' si vides
        $this->updateNomPrenomFromName();

        echo "✅ Colonnes nom/prenom mises à jour à partir du champ 'name'\n";
    }

    /**
     * Mettre à jour nom/prenom à partir du champ name
     */
    private function updateNomPrenomFromName()
    {
        if (Schema::hasColumn('users', 'nom') && Schema::hasColumn('users', 'prenom')) {
            $usersToUpdate = DB::table('users')
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->where(function($query) {
                    $query->whereNull('nom')
                          ->orWhere('nom', '')
                          ->orWhereNull('prenom')
                          ->orWhere('prenom', '');
                })
                ->get();

            foreach ($usersToUpdate as $user) {
                $nameParts = explode(' ', trim($user->name));
                $prenom = $nameParts[0] ?? '';
                $nom = count($nameParts) > 1 ? end($nameParts) : '';
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'updated_at' => now()
                    ]);
            }
        }
    }

    /**
     * Créer le mapping rôle -> permissions par défaut
     */
    private function createRolePermissionMapping()
    {
        // Vérifier si la table role_permissions existe
        if (!Schema::hasTable('role_permissions')) {
            echo "⚠️ Table role_permissions non trouvée, mapping ignoré\n";
            return;
        }

        // Obtenir les rôles
        $roles = [
            'super_admin' => DB::table('roles')->where('name', 'super_admin')->first(),
            'admin_general' => DB::table('roles')->where('name', 'admin_general')->first(),
            'moderateur' => DB::table('roles')->where('name', 'moderateur')->first(),
            'operateur' => DB::table('roles')->where('name', 'operateur')->first(),
            'auditeur' => DB::table('roles')->where('name', 'auditeur')->first(),
        ];

        $rolePermissions = [];

        // Super Admin a toutes les permissions
        if ($roles['super_admin']) {
            $allPermissions = DB::table('permissions')->pluck('id')->toArray();
            foreach ($allPermissions as $permissionId) {
                $rolePermissions[] = [
                    'role_id' => $roles['super_admin']->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Admin Général - permissions administratives
        if ($roles['admin_general']) {
            $adminPermissions = DB::table('permissions')
                ->whereIn('category', ['users', 'organizations', 'workflow', 'reports'])
                ->pluck('id')->toArray();
            
            foreach ($adminPermissions as $permissionId) {
                $rolePermissions[] = [
                    'role_id' => $roles['admin_general']->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insérer les permissions par batch pour optimiser
        if (!empty($rolePermissions)) {
            try {
                // Nettoyer les doublons existants d'abord
                foreach (array_chunk($rolePermissions, 100) as $chunk) {
                    foreach ($chunk as $rolePermission) {
                        DB::table('role_permissions')
                            ->updateOrInsert(
                                [
                                    'role_id' => $rolePermission['role_id'],
                                    'permission_id' => $rolePermission['permission_id']
                                ],
                                $rolePermission
                            );
                    }
                }
                
                echo "✅ Mapping rôle-permissions créé (" . count($rolePermissions) . " associations)\n";
            } catch (\Exception $e) {
                echo "⚠️ Erreur lors de l'insertion des permissions: " . $e->getMessage() . "\n";
            }
        }
    }
}