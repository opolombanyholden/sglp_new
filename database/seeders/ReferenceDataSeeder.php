<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    /**
     * Exécuter les seeds des données de référence.
     * 
     * Ce seeder orchestre l'exécution de tous les seeders nécessaires
     * pour peupler les tables de référence du workflow de validation.
     */
    public function run(): void
    {
        $this->command->info('🚀 ========================================');
        $this->command->info('   PNGDI - PEUPLEMENT DONNÉES DE RÉFÉRENCE');
        $this->command->info('========================================');
        $this->command->newLine();
        
        // Désactiver les vérifications de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // ========================================
            // ÉTAPE 1 : Entités de Validation
            // ========================================
            $this->command->info('📋 ÉTAPE 1/3 : Création des Entités de Validation...');
            $this->call(ValidationEntitySeeder::class);
            $this->command->newLine();
            
            // ========================================
            // ÉTAPE 2 : Étapes de Workflow
            // ========================================
            $this->command->info('📋 ÉTAPE 2/3 : Création des Étapes de Workflow...');
            $this->call(WorkflowStepSeeder::class);
            $this->command->newLine();
            
            // ========================================
            // ÉTAPE 3 : Liens Workflow-Entités
            // ========================================
            $this->command->info('📋 ÉTAPE 3/3 : Création des Liens Étape-Entité...');
            $this->call(WorkflowStepEntitySeeder::class);
            $this->command->newLine();
            
            // ========================================
            // VÉRIFICATION DES DONNÉES
            // ========================================
            $this->command->info('🔍 Vérification des données créées...');
            
            $entitiesCount = DB::table('validation_entities')->count();
            $stepsCount = DB::table('workflow_steps')->count();
            $linksCount = DB::table('workflow_step_entities')->count();
            
            $this->command->table(
                ['Table', 'Enregistrements'],
                [
                    ['validation_entities', $entitiesCount],
                    ['workflow_steps', $stepsCount],
                    ['workflow_step_entities', $linksCount],
                ]
            );
            
            // ========================================
            // RÉSULTAT FINAL
            // ========================================
            $this->command->newLine();
            $this->command->info('✅ ========================================');
            $this->command->info('   DONNÉES DE RÉFÉRENCE CRÉÉES AVEC SUCCÈS');
            $this->command->info('========================================');
            $this->command->info("   • {$entitiesCount} entités de validation");
            $this->command->info("   • {$stepsCount} étapes de workflow");
            $this->command->info("   • {$linksCount} liens étape-entité");
            $this->command->newLine();
            $this->command->info('🎉 JALON 2 ATTEINT : Validation de dossiers opérationnelle !');
            $this->command->newLine();
            
        } catch (\Exception $e) {
            $this->command->error('❌ Erreur lors du peuplement : ' . $e->getMessage());
            throw $e;
        } finally {
            // Réactiver les vérifications de clés étrangères
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}