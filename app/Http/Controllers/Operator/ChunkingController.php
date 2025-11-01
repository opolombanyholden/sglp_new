<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Adherent;
use App\Models\NipDatabase;
use App\Models\AdherentAnomalie;

/**
 * ========================================================================
 * CHUNKING CONTROLLER CORRIGÉ - VERSION 4.0 
 * Solution définitive pour l'insertion des données via chunking
 * Intégration des vérifications nip_database et suppression des vérifications téléphone
 * ========================================================================
 * 
 * CORRECTIONS APPLIQUÉES VERSION 4.0 :
 * ✅ Suppression complète des vérifications téléphone
 * ✅ Ajout vérification NIP avec table nip_database (anomalie critique)
 * ✅ Ajout vérification cohérence données nip_database (anomalie majeure)
 * ✅ Validation et parsing robuste des adhérents  
 * ✅ Gestion d'erreur améliorée avec logs détaillés
 * ✅ Insertion en base garantie avec fallback
 * ✅ Traçabilité complète du processus
 * ✅ Compatibilité Phase 2 et chunking adaptatif
 */
class ChunkingController extends Controller
{
    /**
     * ✅ MÉTHODE PRINCIPALE CORRIGÉE : Traitement chunk avec insertion garantie
     */
    public function processChunk(Request $request)
    {
        $debugTrace = [
            'etapes' => [],
            'timestamp_debut' => now()->toISOString(),
            'chunk_id' => uniqid('chunk_'),
            'user_id' => auth()->id(),
            'version' => '4.0-NIP_DATABASE'
        ];
        
        try {
            // ============================================
            // ÉTAPE 1 : RÉCUPÉRATION ET VALIDATION DES DONNÉES CORRIGÉE
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => '1_RECUPERATION_DONNEES_V4',
                'timestamp' => now()->toISOString(),
                'status' => 'START'
            ];
            
            // ✅ CORRECTION 1: Récupération flexible des données
            $dossierId = $this->getDossierId($request);
            $adherentsData = $this->getAdherentsData($request);
            $chunkIndex = $request->input('chunk_index', 0);
            $totalChunks = $request->input('total_chunks', 1);
            $isFinalChunk = $request->input('is_final_chunk', false);
            
            $debugTrace['etapes'][] = [
                'etape' => '1_RECUPERATION_DONNEES_V4',
                'timestamp' => now()->toISOString(),
                'status' => 'SUCCESS',
                'donnees' => [
                    'dossier_id' => $dossierId,
                    'adherents_count' => count($adherentsData),
                    'chunk_index' => $chunkIndex,
                    'total_chunks' => $totalChunks,
                    'is_final_chunk' => $isFinalChunk,
                    'first_adherent_preview' => !empty($adherentsData) ? array_slice($adherentsData[0], 0, 3) : null
                ]
            ];
            
            // ✅ Validation des données essentielles
            if (!$dossierId) {
                throw new \Exception('ID du dossier manquant ou invalide');
            }
            
            if (empty($adherentsData)) {
                throw new \Exception('Aucun adhérent à traiter dans ce chunk');
            }
            
            // ============================================
            // ÉTAPE 2 : VALIDATION DOSSIER ET ORGANISATION
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => '2_VALIDATION_DOSSIER_V4',
                'timestamp' => now()->toISOString(),
                'status' => 'START'
            ];
            
            $dossier = $this->validateDossier($dossierId);
            $organisation = $dossier->organisation;
            
            $debugTrace['etapes'][] = [
                'etape' => '2_VALIDATION_DOSSIER_V4',
                'timestamp' => now()->toISOString(),
                'status' => 'SUCCESS',
                'donnees' => [
                    'dossier_id' => $dossier->id,
                    'organisation_id' => $organisation->id,
                    'organisation_nom' => $organisation->nom,
                    'organisation_type' => $organisation->type
                ]
            ];
            
            // ============================================
            // ÉTAPE 3 : INSERTION IMMEDIATE CORRIGÉE V4
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => '3_INSERTION_IMMEDIATE_V4',
                'timestamp' => now()->toISOString(),
                'status' => 'START'
            ];
            
            $result = $this->insertAdherentsImmediatelyV4($adherentsData, $organisation, $dossier, $debugTrace);
            
            $debugTrace['etapes'][] = [
                'etape' => '3_INSERTION_IMMEDIATE_V4',
                'timestamp' => now()->toISOString(),
                'status' => 'SUCCESS',
                'donnees' => [
                    'inserted' => $result['inserted'],
                    'errors_count' => count($result['errors']),
                    'anomalies_count' => $result['anomalies_count'],
                    'nip_database_errors' => $result['nip_database_errors'] ?? 0
                ]
            ];
            
            // ============================================
            // ÉTAPE 4 : FORMATAGE RÉPONSE FINALE
            // ============================================
            $response = [
                'success' => true,
                'chunk_index' => $chunkIndex,
                'processed' => $result['inserted'],
                'inserted' => $result['inserted'],
                'errors' => $result['errors'],
                'valid_adherents' => $result['valid_adherents'],
                'adherents_with_anomalies' => $result['anomalies_count'],
                'nip_database_verification' => [
                    'checked' => true,
                    'errors' => $result['nip_database_errors'] ?? 0
                ],
                'is_final_chunk' => $isFinalChunk,
                'message' => "Chunk {$chunkIndex} : {$result['inserted']} adhérents insérés en base (v4.0 avec vérification nip_database)",
                'solution' => 'INSERTION_DURING_CHUNKING_V4_NIP_DATABASE',
                'debug_trace' => $debugTrace
            ];
            
            // ✅ LOG FINAL DE SUCCÈS
            Log::info('🎉 CHUNK TRAITÉ AVEC SUCCÈS V4.0', [
                'chunk_id' => $debugTrace['chunk_id'],
                'chunk_index' => $chunkIndex,
                'inserted' => $result['inserted'],
                'nip_database_checks' => $result['nip_database_checks'] ?? 0,
                'processing_time' => now()->diffInMilliseconds($debugTrace['timestamp_debut']) . 'ms',
                'version' => '4.0-NIP_DATABASE'
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            // ============================================
            // GESTION ERREUR ROBUSTE V4.0
            // ============================================
            $debugTrace['etapes'][] = [
                'etape' => 'ERREUR_V4',
                'timestamp' => now()->toISOString(),
                'status' => 'ERROR',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ];
            
            Log::error('🚨 ERREUR CHUNK V4.0', [
                'chunk_id' => $debugTrace['chunk_id'],
                'error' => $e->getMessage(),
                'trace_complete' => $debugTrace,
                'version' => '4.0-NIP_DATABASE'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur insertion chunk v4.0: ' . $e->getMessage(),
                'debug_trace' => $debugTrace,
                'chunk_index' => $chunkIndex ?? null,
                'version' => '4.0-NIP_DATABASE'
            ], 500);
        }
    }
    
    /**
     * ✅ CORRECTION 2: Récupération flexible de l'ID du dossier
     */
    private function getDossierId(Request $request)
    {
        // Priorité 1: Paramètre direct
        $dossierId = $request->input('dossier_id');
        
        // Priorité 2: Session
        if (!$dossierId) {
            $dossierId = session('current_dossier_id');
            Log::info('📂 Dossier ID récupéré depuis session', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);
        }
        
        // Priorité 3: Configuration Phase 2
        if (!$dossierId && isset(request()->route()->parameters['dossier'])) {
            $dossierId = request()->route()->parameters['dossier'];
        }
        
        return $dossierId;
    }
    
    /**
     * ✅ CORRECTION 3: Récupération flexible des données d'adhérents
     */
    private function getAdherentsData(Request $request)
    {
        $adherentsData = [];
        
        // ✅ Méthode 1: Array direct d'adhérents (format Phase 2)
        if ($request->has('adherents') && is_array($request->input('adherents'))) {
            $adherentsData = $request->input('adherents');
            Log::info('📊 Adhérents récupérés comme array direct', [
                'count' => count($adherentsData)
            ]);
        }
        // ✅ Méthode 2: JSON string chunk_data (format chunking-import.js)
        else if ($request->has('chunk_data')) {
            $chunkDataJson = $request->input('chunk_data');
            if (is_string($chunkDataJson)) {
                $decoded = json_decode($chunkDataJson, true);
                $adherentsData = $decoded ?? [];
                Log::info('📊 Adhérents récupérés depuis chunk_data JSON', [
                    'count' => count($adherentsData)
                ]);
            }
        }
        // ✅ Méthode 3: JSON string adherents (fallback)
        else if ($request->has('adherents') && is_string($request->input('adherents'))) {
            $adherentsJson = $request->input('adherents');
            $decoded = json_decode($adherentsJson, true);
            $adherentsData = $decoded ?? [];
            Log::info('📊 Adhérents récupérés depuis adherents JSON', [
                'count' => count($adherentsData)
            ]);
        }
        
        return $adherentsData;
    }
    
    /**
     * ✅ CORRECTION 4: Validation robuste du dossier
     */
    private function validateDossier($dossierId)
    {
        $dossier = Dossier::with('organisation')
            ->where('id', $dossierId)
            ->whereHas('organisation', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->first();
            
        if (!$dossier) {
            throw new \Exception("Dossier {$dossierId} non trouvé ou accès non autorisé");
        }
        
        if (!$dossier->organisation) {
            throw new \Exception("Organisation manquante pour le dossier {$dossierId}");
        }
        
        return $dossier;
    }
    
    /**
     * ✅ CORRECTION 5: Insertion immédiate V4.0 - Avec vérifications nip_database
     */
    private function insertAdherentsImmediatelyV4(array $adherentsData, Organisation $organisation, Dossier $dossier, &$debugTrace)
    {
        $inserted = 0;
        $errors = [];
        $validAdherents = 0;
        $anomaliesCount = 0;
        $nipDatabaseChecks = 0;
        $nipDatabaseErrors = 0;
        
        Log::info('🚀 DÉBUT INSERTION V4.0 AVEC NIP_DATABASE', [
            'organisation_id' => $organisation->id,
            'adherents_count' => count($adherentsData),
            'version' => '4.0-NIP_DATABASE'
        ]);
        
        // ✅ TRANSACTION COURTE pour éviter les timeouts
        DB::beginTransaction();
        
        try {
            $adherentsToInsert = [];
            $anomaliesData = [];
            
            // ============================================
            // PRÉPARATION DES DONNÉES V4.0 AVEC NIP_DATABASE
            // ============================================
            foreach ($adherentsData as $index => $adherentData) {
                try {
                    // ✅ CORRECTION: Validation et nettoyage robuste
                    $cleanData = $this->prepareAdherentDataV4($adherentData, $organisation, $index);
                    
                    // ✅ Détection des anomalies AVANT insertion (V4.0 avec nip_database)
                    $anomalies = $this->detectAnomaliesV4($cleanData, $organisation->type);
                    $nipDatabaseChecks++;
                    
                    // Compter les erreurs nip_database
                    if (isset($anomalies['nip_database_error']) && $anomalies['nip_database_error']) {
                        $nipDatabaseErrors++;
                    }
                    
                    // ✅ RÈGLE MÉTIER SGLP: Enregistrer MÊME avec anomalies (sauf critiques bloquantes)
                    $hasCriticalAnomalies = !empty($anomalies['critiques']);
                    
                    $adherentToInsert = [
                        'organisation_id' => $organisation->id,
                        'nip' => $cleanData['nip'],
                        'nom' => strtoupper($cleanData['nom']),
                        'prenom' => $cleanData['prenom'],
                        'profession' => $cleanData['profession'],
                        'fonction' => $cleanData['fonction'],
                        'email' => $cleanData['email'],
                        'date_adhesion' => now(),
                        'is_active' => !$hasCriticalAnomalies, // Inactif si anomalies critiques
                        'has_anomalies' => !empty($anomalies['all']),
                        'anomalies_data' => !empty($anomalies['all']) ? json_encode($anomalies['all']) : null,
                        'anomalies_severity' => $this->determineMaxSeverity($anomalies),
                        'source' => 'chunking_v4_nip_database',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    $adherentsToInsert[] = $adherentToInsert;
                    
                    // Comptabiliser les anomalies
                    if (!empty($anomalies['all'])) {
                        $anomaliesCount++;
                        $anomaliesData[] = [
                            'nip' => $cleanData['nip'],
                            'nom_complet' => $cleanData['nom'] . ' ' . $cleanData['prenom'],
                            'anomalies' => $anomalies['all'],
                            'severity' => $this->determineMaxSeverity($anomalies),
                            'nip_database_status' => $anomalies['nip_database_status'] ?? 'non_verifie',
                            'line_index' => $index
                        ];
                    } else {
                        $validAdherents++;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'nip' => $adherentData['nip'] ?? 'N/A',
                        'nom' => ($adherentData['nom'] ?? 'Inconnu') . ' ' . ($adherentData['prenom'] ?? ''),
                        'error' => $e->getMessage()
                    ];
                    
                    Log::warning('Erreur préparation adhérent V4', [
                        'index' => $index,
                        'adherent' => $adherentData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // ============================================
            // INSERTION EN BASE V4.0 - GARANTIE
            // ============================================
            if (!empty($adherentsToInsert)) {
                try {
                    // ✅ MÉTHODE 1: Insertion directe en lot (plus rapide)
                    DB::table('adherents')->insert($adherentsToInsert);
                    $inserted = count($adherentsToInsert);
                    
                    // ✅ NOUVELLE LOGIQUE : Créer les anomalies dans adherent_anomalies
                    $this->createAnomaliesInTable($adherentsToInsert, $organisation);
                    
                    Log::info('✅ INSERTION EN LOT RÉUSSIE V4.0', [
                        'inserted' => $inserted,
                        'nip_database_checks' => $nipDatabaseChecks,
                        'nip_database_errors' => $nipDatabaseErrors,
                        'method' => 'bulk_insert'
                    ]);
                    
                } catch (\Illuminate\Database\QueryException $e) {
                    // ✅ MÉTHODE 2: Fallback avec insertion individuelle
                    Log::warning('⚠️ Insertion lot échouée, fallback individuel V4.0', [
                        'error' => $e->getMessage()
                    ]);
                    
                    $inserted = 0;
                    foreach ($adherentsToInsert as $adherent) {
                        try {
                            // ✅ Vérifier doublon par NIP
                            $existingAdherent = DB::table('adherents')
                                ->where('organisation_id', $adherent['organisation_id'])
                                ->where('nip', $adherent['nip'])
                                ->first();
                                
                            if (!$existingAdherent) {
                                DB::table('adherents')->insert($adherent);
                                $inserted++;
                                
                                // ✅ Créer les anomalies pour cet adhérent individuel
                                $this->createAnomaliesForSingleAdherent($adherent, $organisation);
                            } else {
                                Log::info('Doublon NIP ignoré V4.0', [
                                    'nip' => $adherent['nip'],
                                    'nom' => $adherent['nom']
                                ]);
                            }
                            
                        } catch (\Exception $individualError) {
                            $errors[] = [
                                'nip' => $adherent['nip'],
                                'nom' => $adherent['nom'],
                                'error' => $individualError->getMessage()
                            ];
                            
                            Log::warning('Erreur insertion individuelle V4.0', [
                                'nip' => $adherent['nip'],
                                'error' => $individualError->getMessage()
                            ]);
                        }
                    }
                    
                    Log::info('✅ INSERTION INDIVIDUELLE TERMINÉE V4.0', [
                        'inserted' => $inserted,
                        'errors' => count($errors),
                        'method' => 'individual_insert_fallback'
                    ]);
                }
            }
            
            DB::commit();
            
            // ✅ LOG FINAL DE L'INSERTION
            Log::info('🎉 INSERTION CHUNK TERMINÉE V4.0', [
                'organisation_id' => $organisation->id,
                'total_to_insert' => count($adherentsToInsert),
                'inserted' => $inserted,
                'valid_adherents' => $validAdherents,
                'anomalies_count' => $anomaliesCount,
                'nip_database_checks' => $nipDatabaseChecks,
                'nip_database_errors' => $nipDatabaseErrors,
                'errors_count' => count($errors),
                'success_rate' => count($adherentsToInsert) > 0 ? round(($inserted / count($adherentsToInsert)) * 100, 2) . '%' : '0%'
            ]);
            
            return [
                'inserted' => $inserted,
                'errors' => $errors,
                'valid_adherents' => $validAdherents,
                'anomalies_count' => $anomaliesCount,
                'anomalies_data' => $anomaliesData,
                'nip_database_checks' => $nipDatabaseChecks,
                'nip_database_errors' => $nipDatabaseErrors,
                'total_processed' => count($adherentsToInsert)
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('❌ ERREUR INSERTION CHUNK V4.0', [
                'organisation_id' => $organisation->id,
                'adherents_count' => count($adherentsData),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * ✅ CORRECTION 6: Préparation robuste des données adhérent V4.0 (sans téléphone)
     */
    private function prepareAdherentDataV4($adherentData, Organisation $organisation, $index = 0)
    {
        // ✅ Gestion defensive des types de données
        if (!is_array($adherentData)) {
            if (is_string($adherentData)) {
                $decoded = json_decode($adherentData, true);
                $adherentData = $decoded ?? [];
            } else {
                throw new \Exception("Format de données adhérent invalide à l'index {$index}");
            }
        }
        
        // ✅ Mapping flexible des champs (SANS téléphone)
        $nip = $adherentData['nip'] ?? $adherentData['NIP'] ?? '';
        $nom = $adherentData['nom'] ?? $adherentData['Nom'] ?? '';
        $prenom = $adherentData['prenom'] ?? $adherentData['Prenom'] ?? $adherentData['Prénom'] ?? '';
        $profession = $adherentData['profession'] ?? $adherentData['Profession'] ?? '';
        $email = $adherentData['email'] ?? $adherentData['Email'] ?? '';
        $fonction = $adherentData['fonction'] ?? $adherentData['Fonction'] ?? 'Membre';
        $dateNaissance = $adherentData['date_naissance'] ?? $adherentData['Date_naissance'] ?? '';
        $lieuNaissance = $adherentData['lieu_naissance'] ?? $adherentData['Lieu_naissance'] ?? '';
        
        // ✅ Nettoyage et validation (SANS téléphone)
        return [
            'nip' => $this->cleanNipV4($nip),
            'nom' => $this->cleanString($nom),
            'prenom' => $this->cleanString($prenom),
            'profession' => $this->cleanString($profession),
            'fonction' => $this->cleanString($fonction) ?: 'Membre',
            'email' => $this->cleanEmailV4($email),
            'date_naissance' => $this->cleanDateV4($dateNaissance),
            'lieu_naissance' => $this->cleanString($lieuNaissance),
            'source' => 'chunking_v4_nip_database',
            'line_index' => $index
        ];
    }
    
    /**
     * ✅ CORRECTION 7: Détection d'anomalies V4.0 avec nip_database (SANS téléphone)
     */
    private function detectAnomaliesV4($cleanData, $organisationType)
    {
        $anomalies = [
            'all' => [], 
            'critiques' => [], 
            'majeures' => [], 
            'mineures' => [],
            'nip_database_error' => false,
            'nip_database_status' => 'non_verifie'
        ];
        
        // ✅ Validation NIP (critique)
        if (empty($cleanData['nip']) || strlen($cleanData['nip']) < 5) {
            $anomalies['critiques'][] = 'NIP invalide ou trop court';
            $anomalies['all'][] = 'NIP invalide ou trop court';
        }
        
        // ✅ Validation nom/prénom (critique)
        if (empty($cleanData['nom']) || empty($cleanData['prenom'])) {
            $anomalies['critiques'][] = 'Nom ou prénom manquant';
            $anomalies['all'][] = 'Nom ou prénom manquant';
        }
        
        // ✅ NOUVELLE VÉRIFICATION 1: NIP dans nip_database (CRITIQUE)
        if (!empty($cleanData['nip'])) {
            try {
                $nipRecord = NipDatabase::where('nip', $cleanData['nip'])->first();
                
                if (!$nipRecord) {
                    // ✅ ANOMALIE CRITIQUE: NIP non trouvé dans nip_database
                    $anomalies['critiques'][] = "NIP '{$cleanData['nip']}' non trouvé dans la base de données officielle";
                    $anomalies['all'][] = "NIP '{$cleanData['nip']}' non trouvé dans la base de données officielle";
                    $anomalies['nip_database_error'] = true;
                    $anomalies['nip_database_status'] = 'non_trouve';
                    
                    Log::warning('🔍 NIP NON TROUVÉ DANS NIP_DATABASE', [
                        'nip' => $cleanData['nip'],
                        'nom' => $cleanData['nom'],
                        'prenom' => $cleanData['prenom']
                    ]);
                } else {
                    // ✅ NOUVELLE VÉRIFICATION 2: Cohérence des données (MAJEURE)
                    $incoherences = $this->checkDataCoherenceV4($cleanData, $nipRecord);
                    
                    if (!empty($incoherences)) {
                        $anomalies['majeures'][] = 'Données incohérentes avec la base officielle: ' . implode(', ', array_keys($incoherences));
                        $anomalies['all'][] = 'Données incohérentes avec la base officielle: ' . implode(', ', array_keys($incoherences));
                        $anomalies['nip_database_status'] = 'donnees_incoherentes';
                        
                        Log::warning('📊 DONNÉES INCOHÉRENTES AVEC NIP_DATABASE', [
                            'nip' => $cleanData['nip'],
                            'incoherences' => $incoherences,
                            'adherent_data' => [
                                'nom' => $cleanData['nom'],
                                'prenom' => $cleanData['prenom'],
                                'date_naissance' => $cleanData['date_naissance'],
                                'lieu_naissance' => $cleanData['lieu_naissance']
                            ],
                            'database_data' => [
                                'nom' => $nipRecord->nom,
                                'prenom' => $nipRecord->prenom,
                                'date_naissance' => $nipRecord->date_naissance ? $nipRecord->date_naissance->format('d/m/Y') : null,
                                'lieu_naissance' => $nipRecord->lieu_naissance
                            ]
                        ]);
                    } else {
                        $anomalies['nip_database_status'] = 'coherent';
                    }
                }
            } catch (\Exception $e) {
                Log::error('❌ ERREUR VÉRIFICATION NIP_DATABASE', [
                    'nip' => $cleanData['nip'],
                    'error' => $e->getMessage()
                ]);
                
                $anomalies['majeures'][] = 'Erreur lors de la vérification avec la base officielle';
                $anomalies['all'][] = 'Erreur lors de la vérification avec la base officielle';
                $anomalies['nip_database_error'] = true;
                $anomalies['nip_database_status'] = 'erreur_verification';
            }
        }
        
        // ✅ Validation email (mineure)
        if (!empty($cleanData['email']) && !filter_var($cleanData['email'], FILTER_VALIDATE_EMAIL)) {
            $anomalies['mineures'][] = 'Format email invalide';
            $anomalies['all'][] = 'Format email invalide';
        }
        
        // ✅ Validation profession manquante (mineure)
        if (empty($cleanData['profession'])) {
            $anomalies['mineures'][] = 'Profession non renseignée';
            $anomalies['all'][] = 'Profession non renseignée';
        }
        
        return $anomalies;
    }
    
    /**
     * ✅ NOUVELLE MÉTHODE: Vérifier cohérence données avec nip_database
     */
    private function checkDataCoherenceV4($cleanData, $nipRecord)
    {
        $incoherences = [];
        
        // Comparer nom (insensible à la casse et aux accents)
        if (!$this->compareNamesV4($cleanData['nom'], $nipRecord->nom)) {
            $incoherences['nom'] = [
                'adherent' => $cleanData['nom'],
                'database' => $nipRecord->nom
            ];
        }
        
        // Comparer prénom (insensible à la casse et aux accents)
        if (!$this->compareNamesV4($cleanData['prenom'], $nipRecord->prenom)) {
            $incoherences['prenom'] = [
                'adherent' => $cleanData['prenom'],
                'database' => $nipRecord->prenom
            ];
        }
        
        // Comparer date de naissance si disponible
        if (!empty($cleanData['date_naissance']) && $nipRecord->date_naissance) {
            $adherentDate = $this->parseDate($cleanData['date_naissance']);
            if ($adherentDate && !$adherentDate->isSameDay($nipRecord->date_naissance)) {
                $incoherences['date_naissance'] = [
                    'adherent' => $adherentDate->format('d/m/Y'),
                    'database' => $nipRecord->date_naissance->format('d/m/Y')
                ];
            }
        }
        
        // Comparer lieu de naissance si disponible
        if (!empty($cleanData['lieu_naissance']) && !empty($nipRecord->lieu_naissance)) {
            if (!$this->compareNamesV4($cleanData['lieu_naissance'], $nipRecord->lieu_naissance)) {
                $incoherences['lieu_naissance'] = [
                    'adherent' => $cleanData['lieu_naissance'],
                    'database' => $nipRecord->lieu_naissance
                ];
            }
        }
        
        return $incoherences;
    }
    
    /**
     * ✅ NOUVELLE MÉTHODE: Comparer noms (insensible casse/accents)
     */
    private function compareNamesV4($name1, $name2)
    {
        if (empty($name1) || empty($name2)) {
            return empty($name1) && empty($name2);
        }
        
        // Normaliser les chaînes
        $normalized1 = $this->normalizeStringV4($name1);
        $normalized2 = $this->normalizeStringV4($name2);
        
        return $normalized1 === $normalized2;
    }
    
    /**
     * ✅ NOUVELLE MÉTHODE: Normaliser chaîne de caractères
     */
    private function normalizeStringV4($string)
    {
        // Convertir en minuscules
        $string = strtolower($string);
        
        // Supprimer les accents (compatible PHP 7.3)
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        
        // Supprimer caractères spéciaux et espaces multiples
        $string = preg_replace('/[^a-z0-9\s]/', '', $string);
        $string = preg_replace('/\s+/', ' ', $string);
        
        return trim($string);
    }
    
    /**
     * ✅ NOUVELLE MÉTHODE: Déterminer sévérité maximale
     */
    private function determineMaxSeverity($anomalies)
    {
        if (!empty($anomalies['critiques'])) {
            return 'critique';
        } elseif (!empty($anomalies['majeures'])) {
            return 'majeure';
        } elseif (!empty($anomalies['mineures'])) {
            return 'mineure';
        }
        
        return null;
    }
    
    /**
     * ✅ MÉTHODES UTILITAIRES V4.0 (SANS téléphone)
     */
    private function cleanNipV4($nip)
    {
        if (empty($nip)) {
            return $this->generateTemporaryNipV4();
        }
        return strtoupper(trim($nip));
    }
    
    private function cleanString($str)
    {
        return trim($str ?? '');
    }
    
    private function cleanEmailV4($email)
    {
        if (empty($email)) return null;
        $email = trim($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
    
    private function cleanDateV4($date)
    {
        if (empty($date)) return null;
        
        $parsedDate = $this->parseDate($date);
        return $parsedDate ? $parsedDate->format('Y-m-d') : null;
    }
    
    private function parseDate($dateString)
    {
        if (empty($dateString)) return null;
        
        try {
            // Formats supportés: d/m/Y, Y-m-d, d-m-Y
            $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
            
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date && $date->format($format) === $dateString) {
                    return \Carbon\Carbon::instance($date);
                }
            }
            
            // Fallback avec strtotime
            $timestamp = strtotime($dateString);
            if ($timestamp !== false) {
                return \Carbon\Carbon::createFromTimestamp($timestamp);
            }
            
        } catch (\Exception $e) {
            Log::warning('Erreur parsing date', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    private function generateTemporaryNipV4()
    {
        $prefix = 'GA';
        $sequence = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $date = date('Ymd');
        return "{$prefix}-{$sequence}-{$date}";
    }
    
    /**
     * ✅ MÉTHODES AUXILIAIRES EXISTANTES MAINTENUES
     */
    public function getSessionData(Request $request)
    {
        return $this->getSessionDataV4($request);
    }
    
    private function getSessionDataV4(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            $dossierId = $request->input('dossier_id');
            
            Log::info('📥 RÉCUPÉRATION SESSION V4.0', [
                'session_key' => $sessionKey,
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);
            
            if (!$sessionKey || !$dossierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres manquants'
                ], 400);
            }
            
            $sessionData = session($sessionKey);
            
            if (!$sessionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expirée ou inexistante'
                ], 404);
            }
            
            $adherentsData = is_array($sessionData) ? $sessionData : [];
            $totalCount = count($adherentsData);
            
            return response()->json([
                'success' => true,
                'data' => $adherentsData,
                'total' => $totalCount,
                'version' => '4.0-NIP_DATABASE'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ ERREUR RÉCUPÉRATION SESSION V4.0', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur v4.0: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ✅ MÉTHODES DE DIAGNOSTIC ET SUPPORT
     */
    public function healthCheck(Request $request)
    {
        try {
            // Test connexion nip_database
            $nipDatabaseStatus = 'unknown';
            try {
                $nipCount = NipDatabase::count();
                $nipDatabaseStatus = "accessible ({$nipCount} enregistrements)";
            } catch (\Exception $e) {
                $nipDatabaseStatus = "erreur: " . $e->getMessage();
            }
            
            return response()->json([
                'success' => true,
                'healthy' => true,
                'version' => '4.0-NIP_DATABASE',
                'timestamp' => now()->toISOString(),
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->id(),
                'memory_usage' => memory_get_usage(true),
                'nip_database_status' => $nipDatabaseStatus,
                'solution' => 'INSERTION_DURING_CHUNKING_V4_NIP_DATABASE'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'healthy' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function refreshCSRF()
    {
        try {
            return response()->json([
                'success' => true,
                'csrf_token' => csrf_token(),
                'version' => '4.0-NIP_DATABASE'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur refresh CSRF v4.0: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function authTest(Request $request)
    {
        try {
            $user = auth()->user();
            
            return response()->json([
                'success' => true,
                'message' => 'Test authentification réussi v4.0',
                'data' => [
                    'authenticated' => auth()->check(),
                    'user_id' => $user ? $user->id : null,
                    'user_role' => $user ? $user->role : null,
                    'timestamp' => now()->toISOString(),
                    'version' => '4.0-NIP_DATABASE'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur test auth v4.0: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Créer les anomalies dans la table adherent_anomalies
     */
    private function createAnomaliesInTable(array $adherentsData, Organisation $organisation)
    {
        try {
            foreach ($adherentsData as $index => $adherentData) {
                if (!empty($adherentData['has_anomalies']) && !empty($adherentData['anomalies_data'])) {
                    // Récupérer l'ID de l'adhérent inséré
                    $adherentRecord = DB::table('adherents')
                        ->where('organisation_id', $adherentData['organisation_id'])
                        ->where('nip', $adherentData['nip'])
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($adherentRecord) {
                        $anomaliesData = json_decode($adherentData['anomalies_data'], true);
                        
                        if (is_array($anomaliesData)) {
                            foreach ($anomaliesData as $anomalieData) {
                                $this->insertSingleAnomalie($adherentRecord->id, $adherentData['organisation_id'], $anomalieData, $index + 1);
                            }
                        }
                    }
                }
            }
            
            Log::info('✅ Anomalies créées dans adherent_anomalies (lot)', [
                'organisation_id' => $organisation->id,
                'adherents_processed' => count($adherentsData)
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur création anomalies en lot', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Créer anomalies pour un adhérent individuel
     */
    private function createAnomaliesForSingleAdherent(array $adherentData, Organisation $organisation)
    {
        try {
            if (!empty($adherentData['has_anomalies']) && !empty($adherentData['anomalies_data'])) {
                // Récupérer l'ID de l'adhérent inséré
                $adherentRecord = DB::table('adherents')
                    ->where('organisation_id', $adherentData['organisation_id'])
                    ->where('nip', $adherentData['nip'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($adherentRecord) {
                    $anomaliesData = json_decode($adherentData['anomalies_data'], true);
                    
                    if (is_array($anomaliesData)) {
                        foreach ($anomaliesData as $anomalieData) {
                            $this->insertSingleAnomalie($adherentRecord->id, $adherentData['organisation_id'], $anomalieData);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ Erreur création anomalies individuelles', [
                'nip' => $adherentData['nip'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Insérer une anomalie unique
     */
    private function insertSingleAnomalie(int $adherentId, int $organisationId, array $anomalieData, int $ligneImport = 0)
    {
        try {
            // Mapping des codes d'anomalies vers les champs concernés
            $champMapping = [
                'nip_absent' => 'nip',
                'nip_invalide' => 'nip',
                'nip_doublon_fichier' => 'nip',
                'nip_doublon_organisation' => 'nip',
                'nip_non_trouve_database' => 'nip',
                'donnees_incoherentes_database' => 'donnees_personnelles',
                'age_mineur' => 'date_naissance',
                'age_suspect' => 'date_naissance',
                'double_appartenance_parti' => 'organisation',
                'profession_exclue_parti' => 'profession',
                'profession_manquante' => 'profession'
            ];

            $champConcerne = $champMapping[$anomalieData['code']] ?? 'general';
            
            // Déterminer la priorité selon le type
            switch($anomalieData['type']) {
                case 'critique':
                    $priorite = 1;
                    break;
                case 'majeure':
                    $priorite = 2;
                    break;
                case 'mineure':
                    $priorite = 3;
                    break;
                default:
                    $priorite = 3;
            }

            // Formater la description
            $description = $anomalieData['message'];
            if (!empty($anomalieData['action_requise'])) {
                $description .= "\n\nAction requise: " . $anomalieData['action_requise'];
            }

            // Déterminer l'impact métier
            $impactMetier = $this->determineImpactMetierV4($anomalieData['code'], $anomalieData['type']);

            DB::table('adherent_anomalies')->insert([
                'adherent_id' => $adherentId,
                'organisation_id' => $organisationId,
                'ligne_import' => $ligneImport,
                'type_anomalie' => $anomalieData['type'],
                'champ_concerne' => $champConcerne,
                'message_anomalie' => $anomalieData['message'],
                'description' => $description,
                'detectee_le' => now(),
                'valeur_erronee' => json_encode($anomalieData['details'] ?? null),
                'valeur_incorrecte' => json_encode($anomalieData['details'] ?? null),
                'impact_metier' => $impactMetier,
                'priorite' => $priorite,
                'statut' => 'detectee',
                'created_at' => now(),
                'updated_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erreur insertion anomalie unique', [
                'adherent_id' => $adherentId,
                'anomalie_code' => $anomalieData['code'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Déterminer l'impact métier
     */
    private function determineImpactMetierV4(string $code, string $type): string
    {
        $impacts = [
            'nip_non_trouve_database' => 'Risque de fraude - NIP non authentifié',
            'donnees_incoherentes_database' => 'Incohérence avec base officielle',
            'double_appartenance_parti' => 'Violation règles politiques',
            'profession_exclue_parti' => 'Incompatibilité réglementaire',
            'age_mineur' => 'Non-respect âge minimum',
            'nip_doublon_organisation' => 'Doublon inter-organisations',
            'nip_invalide' => 'Format NIP non conforme',
            'nip_absent' => 'Identification impossible'
        ];

        return $impacts[$code] ?? $this->getDefaultImpactByTypeV4($type);
    }

    /**
     * ✅ HELPER : Impact par défaut selon le type (compatible PHP 7.3)
     */
    private function getDefaultImpactByTypeV4(string $type): string
    {
        switch($type) {
            case 'critique':
                return 'Impact critique sur la validité';
            case 'majeure':
                return 'Impact important sur la cohérence';
            case 'mineure':
                return 'Impact mineur sur la qualité';
            default:
                return 'Impact à évaluer';
        }
    }
}