<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Adherent extends Model
{
    use HasFactory;

    /**
     * ✅ FILLABLE - Mis à jour avec toutes les nouvelles colonnes
     */
    protected $fillable = [
        // Identification de base
        'organisation_id',
        'nip',
        'nom',
        'prenom',
        
        // Informations personnelles
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'nationalite',
        'telephone',
        'email',
        'profession',
        'fonction',
        
        // Adresse complète
        'adresse_complete',
        'province',
        'departement',
        'canton',
        'prefecture',
        'sous_prefecture',
        'regroupement',
        'zone_type',
        'ville_commune',
        'arrondissement',
        'quartier',
        'village',
        'lieu_dit',
        
        // Documents et photos
        'photo',
        'piece_identite',
        
        // Dates importantes
        'date_adhesion',
        'date_exclusion',
        'motif_exclusion',
        
        // Statuts et relations
        'is_fondateur',
        'is_active',
        'fondateur_id',
        
        // Gestion des anomalies
        'has_anomalies',
        'anomalies_data',
        'anomalies_severity',
        
        // Historique
        'historique'
    ];

    /**
     * ✅ CASTS CORRIGÉS
     */
    protected $casts = [
        'date_naissance' => 'date',
        'date_adhesion' => 'date', 
        'date_exclusion' => 'date',
        'is_fondateur' => 'boolean',
        'is_active' => 'boolean',
        'has_anomalies' => 'boolean',
        'anomalies_data' => 'array',
        'historique' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ✅ CONSTANTES POUR ANOMALIES
     */
    const ANOMALIE_CRITIQUE = 'critique';
    const ANOMALIE_MAJEURE = 'majeure';
    const ANOMALIE_MINEURE = 'mineure';

    // Codes d'anomalies existants
    const ANOMALIE_DOUBLE_APPARTENANCE_PARTI = 'double_appartenance_parti';
    const ANOMALIE_PROFESSION_EXCLUE = 'profession_exclue_parti';
    const ANOMALIE_NIP_INVALIDE = 'nip_invalide';
    const ANOMALIE_NIP_ABSENT = 'nip_absent';
    const ANOMALIE_NIP_DOUBLON_FICHIER = 'nip_doublon_fichier';
    const ANOMALIE_NIP_DOUBLON_ORGANISATION = 'nip_doublon_organisation';
    const ANOMALIE_PROFESSION_MANQUANTE = 'profession_manquante';
    const ANOMALIE_AGE_MINEUR = 'age_mineur';
    const ANOMALIE_AGE_SUSPECT = 'age_suspect';

    // ✅ NOUVELLES ANOMALIES POUR NIP_DATABASE
    const ANOMALIE_NIP_NON_TROUVE_DATABASE = 'nip_non_trouve_database';
    const ANOMALIE_DONNEES_INCOHERENTES_DATABASE = 'donnees_incoherentes_database';

    /**
     * ✅ CONSTANTES POUR FONCTIONS ET SEXE
     */
    const SEXE_MASCULIN = 'M';
    const SEXE_FEMININ = 'F';
    
    const FONCTION_MEMBRE = 'Membre';
    const FONCTION_PRESIDENT = 'Président';
    const FONCTION_VICE_PRESIDENT = 'Vice-Président';
    const FONCTION_SECRETAIRE_GENERAL = 'Secrétaire Général';
    const FONCTION_TRESORIER = 'Trésorier';
    const FONCTION_COMMISSAIRE = 'Commissaire aux Comptes';
    
    // Professions exclues pour partis politiques
    const PROFESSIONS_EXCLUES_PARTIS = [
        'Magistrat', 'Juge', 'Procureur', 'Commissaire de police',
        'Officier de police judiciaire', 'Militaire en activité',
        'Gendarme en activité', 'Fonctionnaire de la sécurité d\'État',
        'Agent des services de renseignement', 'Diplomate en mission',
        'Gouverneur de province', 'Préfet', 'Sous-préfet', 'Maire en exercice',
        'Membre du Conseil constitutionnel', 'Membre de la Cour de cassation',
        'Membre du Conseil d\'État', 'Contrôleur général d\'État',
        'Inspecteur général d\'État', 'Agent comptable de l\'État',
        'Trésorier payeur général', 'Receveur des finances'
    ];

    /**
     * ✅ PROPRIÉTÉ STATIQUE POUR TRACKER LES DOUBLONS DANS LE BATCH
     */
    protected static $nipBatchTracker = [];

    /**
     * ✅ MÉTHODE BOOT SIMPLIFIÉE ET OPTIMISÉE
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adherent) {
            // Définir fonction par défaut
            if (empty($adherent->fonction)) {
                $adherent->fonction = self::FONCTION_MEMBRE;
            }
            
            // Définir civilité par défaut si manquante
            if (empty($adherent->civilite)) {
                $adherent->civilite = 'M';
            }
            
            // Initialiser l'historique si vide
            if (empty($adherent->historique)) {
                $adherent->historique = [
                    'creation' => now()->toISOString(),
                    'source' => 'creation_manuelle',
                    'events' => []
                ];
            }

            // Détecter et gérer toutes les anomalies
            $adherent->detectAndManageAllAnomalies();
        });

        static::created(function ($adherent) {
            try {
                // Ajouter l'événement d'adhésion dans l'historique
                $adherent->addToHistorique('adhesion', [
                    'date' => $adherent->date_adhesion ?? now(),
                    'organisation_id' => $adherent->organisation_id,
                    'profession' => $adherent->profession,
                    'fonction' => $adherent->fonction,
                    'has_anomalies' => $adherent->has_anomalies,
                    'anomalies_severity' => $adherent->anomalies_severity
                ]);

                // ✅ NOUVELLE LOGIQUE : Créer les anomalies dans la table adherent_anomalies
                if ($adherent->has_anomalies && $adherent->anomalies_data) {
                    \App\Models\AdherentAnomalie::createBulkFromAdherent($adherent);
                }

                // Logger si des anomalies ont été détectées
                if ($adherent->has_anomalies) {
                    \Log::warning('Adhérent créé avec anomalies', [
                        'adherent_id' => $adherent->id,
                        'nip' => $adherent->nip,
                        'organisation_id' => $adherent->organisation_id,
                        'anomalies' => $adherent->anomalies_data,
                        'severity' => $adherent->anomalies_severity
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'ajout à l\'historique: ' . $e->getMessage());
            }
        });
    }

    /**
     * ✅ MÉTHODE PRINCIPALE MISE À JOUR - Détecter TOUTES les anomalies
     */
    public function detectAndManageAllAnomalies()
    {
        $anomalies = [];
        $severityLevel = null;

        // 1. VÉRIFICATIONS NIP COMPLÈTES (PRIORITÉ ABSOLUE)
        $nipAnomalies = $this->checkAllNipAnomalies();
        if (!empty($nipAnomalies)) {
            $anomalies = array_merge($anomalies, $nipAnomalies);
            foreach ($nipAnomalies as $anomalie) {
                $severityLevel = $this->updateMaxSeverity($severityLevel, $anomalie['type']);
            }
        }

        // ✅ 2. NOUVELLES VÉRIFICATIONS AVEC NIP_DATABASE
        $nipDatabaseAnomalies = $this->checkNipDatabaseAnomalies();
        if (!empty($nipDatabaseAnomalies)) {
            $anomalies = array_merge($anomalies, $nipDatabaseAnomalies);
            foreach ($nipDatabaseAnomalies as $anomalie) {
                $severityLevel = $this->updateMaxSeverity($severityLevel, $anomalie['type']);
            }
        }

        // 3. VÉRIFICATION DOUBLE APPARTENANCE PARTI (ANOMALIE CRITIQUE)
        if ($this->organisation && $this->organisation->type === 'parti_politique') {
            $doubleAppartenance = $this->checkDoubleAppartenanceParti();
            if ($doubleAppartenance) {
                $anomalies[] = [
                    'code' => self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI,
                    'type' => self::ANOMALIE_CRITIQUE,
                    'message' => "Membre actif du parti politique '{$doubleAppartenance['parti_nom']}'",
                    'details' => $doubleAppartenance,
                    'date_detection' => now()->toISOString(),
                    'action_requise' => 'Exclusion formelle du parti actuel avant validation définitive'
                ];
                $severityLevel = self::ANOMALIE_CRITIQUE;
            }
        }

        // 4. VÉRIFICATION PROFESSION EXCLUE (ANOMALIE CRITIQUE)
        if ($this->organisation && $this->organisation->type === 'parti_politique' && $this->profession) {
            $professionExclue = $this->checkProfessionExclue();
            if ($professionExclue) {
                $anomalies[] = [
                    'code' => self::ANOMALIE_PROFESSION_EXCLUE,
                    'type' => self::ANOMALIE_CRITIQUE,
                    'message' => "Profession '{$this->profession}' exclue pour parti politique",
                    'details' => ['profession' => $this->profession],
                    'date_detection' => now()->toISOString(),
                    'action_requise' => 'Changement de profession ou refus d\'adhésion'
                ];
                $severityLevel = self::ANOMALIE_CRITIQUE;
            }
        }

        // 5. VÉRIFICATION PROFESSION MANQUANTE (ANOMALIE MINEURE)
        if (empty($this->profession)) {
            $anomalies[] = [
                'code' => self::ANOMALIE_PROFESSION_MANQUANTE,
                'type' => self::ANOMALIE_MINEURE,
                'message' => 'Profession non renseignée',
                'details' => [],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Saisie de la profession'
            ];
            if (!$severityLevel) {
                $severityLevel = self::ANOMALIE_MINEURE;
            }
        }

        // GESTION DE L'ÉTAT ACTIF SELON LA NOUVELLE LOGIQUE
        $this->setActiveStatusBasedOnAnomalies($anomalies, $severityLevel);

        // ENREGISTRER LES ANOMALIES
        if (!empty($anomalies)) {
            $this->has_anomalies = true;
            $this->anomalies_data = $anomalies;
            $this->anomalies_severity = $severityLevel;

            // Ajouter dans l'historique
            $this->addToHistoriqueInternal('anomalies_detected', [
                'total_anomalies' => count($anomalies),
                'severity' => $severityLevel,
                'anomalies_summary' => array_column($anomalies, 'code')
            ]);
        } else {
            $this->has_anomalies = false;
            $this->anomalies_data = null;
            $this->anomalies_severity = null;
            $this->is_active = true;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Vérifications avec nip_database
     */
    private function checkNipDatabaseAnomalies(): array
    {
        $anomalies = [];

        if (empty($this->nip)) {
            return $anomalies; // Si pas de NIP, sera géré par checkAllNipAnomalies
        }

        try {
            // Rechercher le NIP dans la base nip_database
            $nipRecord = \App\Models\NipDatabase::where('nip', $this->nip)->first();

            if (!$nipRecord) {
                // ✅ ANOMALIE CRITIQUE : NIP non trouvé dans nip_database
                $anomalies[] = [
                    'code' => self::ANOMALIE_NIP_NON_TROUVE_DATABASE,
                    'type' => self::ANOMALIE_CRITIQUE,
                    'message' => "NIP '{$this->nip}' non trouvé dans la base de données officielle",
                    'details' => [
                        'nip_recherche' => $this->nip,
                        'statut_database' => 'non_trouve',
                        'verification_date' => now()->toISOString()
                    ],
                    'date_detection' => now()->toISOString(),
                    'action_requise' => 'Vérification de l\'authenticité du NIP avec les autorités compétentes'
                ];
            } else {
                // ✅ VÉRIFICATION COHÉRENCE DES DONNÉES
                $incoherences = $this->checkDataCoherence($nipRecord);
                if (!empty($incoherences)) {
                    $anomalies[] = [
                        'code' => self::ANOMALIE_DONNEES_INCOHERENTES_DATABASE,
                        'type' => self::ANOMALIE_MAJEURE,
                        'message' => 'Données incohérentes avec la base officielle',
                        'details' => [
                            'nip' => $this->nip,
                            'incoherences_detectees' => $incoherences,
                            'donnees_adherent' => [
                                'nom' => $this->nom,
                                'prenom' => $this->prenom,
                                'date_naissance' => $this->date_naissance ? $this->date_naissance->format('d/m/Y') : null,
                                'lieu_naissance' => $this->lieu_naissance
                            ],
                            'donnees_officielles' => [
                                'nom' => $nipRecord->nom,
                                'prenom' => $nipRecord->prenom,
                                'date_naissance' => $nipRecord->date_naissance ? $nipRecord->date_naissance->format('d/m/Y') : null,
                                'lieu_naissance' => $nipRecord->lieu_naissance
                            ],
                            'statut_database' => $nipRecord->statut
                        ],
                        'date_detection' => now()->toISOString(),
                        'action_requise' => 'Correction des données selon la base officielle'
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification NIP database', [
                'nip' => $this->nip,
                'error' => $e->getMessage()
            ]);
        }

        return $anomalies;
    }

    /**
     * ✅ MÉTHODE HELPER - Vérifier cohérence des données
     */
    private function checkDataCoherence(\App\Models\NipDatabase $nipRecord): array
    {
        $incoherences = [];

        // Comparer nom (insensible à la casse et aux accents)
        if (!$this->compareNames($this->nom, $nipRecord->nom)) {
            $incoherences['nom'] = [
                'adherent' => $this->nom,
                'database' => $nipRecord->nom,
                'type' => 'nom_different'
            ];
        }

        // Comparer prénom (insensible à la casse et aux accents)
        if (!$this->compareNames($this->prenom, $nipRecord->prenom)) {
            $incoherences['prenom'] = [
                'adherent' => $this->prenom,
                'database' => $nipRecord->prenom,
                'type' => 'prenom_different'
            ];
        }

        // Comparer date de naissance
        if ($this->date_naissance && $nipRecord->date_naissance) {
            if (!$this->date_naissance->isSameDay($nipRecord->date_naissance)) {
                $incoherences['date_naissance'] = [
                    'adherent' => $this->date_naissance->format('d/m/Y'),
                    'database' => $nipRecord->date_naissance->format('d/m/Y'),
                    'type' => 'date_naissance_differente'
                ];
            }
        }

        // Comparer lieu de naissance (si disponible)
        if ($this->lieu_naissance && $nipRecord->lieu_naissance) {
            if (!$this->compareNames($this->lieu_naissance, $nipRecord->lieu_naissance)) {
                $incoherences['lieu_naissance'] = [
                    'adherent' => $this->lieu_naissance,
                    'database' => $nipRecord->lieu_naissance,
                    'type' => 'lieu_naissance_different'
                ];
            }
        }

        return $incoherences;
    }

    /**
     * ✅ MÉTHODE HELPER - Comparer noms (insensible casse/accents)
     */
    private function compareNames(?string $name1, ?string $name2): bool
    {
        if (empty($name1) || empty($name2)) {
            return empty($name1) && empty($name2);
        }

        // Normaliser les chaînes (supprimer accents, convertir en minuscules, supprimer espaces)
        $normalized1 = $this->normalizeString($name1);
        $normalized2 = $this->normalizeString($name2);

        return $normalized1 === $normalized2;
    }

    /**
     * ✅ MÉTHODE HELPER - Normaliser chaîne de caractères
     */
    private function normalizeString(string $string): string
    {
        // Convertir en minuscules
        $string = strtolower($string);
        
        // Supprimer les accents
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        
        // Supprimer caractères spéciaux et espaces multiples
        $string = preg_replace('/[^a-z0-9\s]/', '', $string);
        $string = preg_replace('/\s+/', ' ', $string);
        
        return trim($string);
    }

    /**
     * ✅ MÉTHODE HELPER - Mise à jour sévérité maximale
     */
    private function updateMaxSeverity(?string $current, string $new): string
    {
        $hierarchy = [
            self::ANOMALIE_MINEURE => 1,
            self::ANOMALIE_MAJEURE => 2,
            self::ANOMALIE_CRITIQUE => 3
        ];
        
        if (!$current) return $new;
        
        $currentLevel = $hierarchy[$current] ?? 0;
        $newLevel = $hierarchy[$new] ?? 0;
        
        return $newLevel > $currentLevel ? $new : $current;
    }

    /**
     * ✅ MÉTHODE EXISTANTE CONSERVÉE - Vérifier TOUTES les anomalies NIP
     */
    private function checkAllNipAnomalies(): array
    {
        $anomalies = [];

        // 1. NIP ABSENT OU VIDE (CRITIQUE)
        if (empty($this->nip) || trim($this->nip) === '') {
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_ABSENT,
                'type' => self::ANOMALIE_CRITIQUE,
                'message' => 'NIP absent ou vide',
                'details' => ['nip_fourni' => $this->nip],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Saisie obligatoire du NIP'
            ];
            return $anomalies; // Arrêter ici si NIP absent
        }

        // 2. FORMAT NIP INVALIDE (MAJEURE)
        if (!$this->isValidNipFormat()) {
            $nipValidation = $this->analyzeNipFormat();
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_INVALIDE,
                'type' => self::ANOMALIE_MAJEURE,
                'message' => 'Format NIP incorrect - Attendu: XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225)',
                'details' => [
                    'nip_fourni' => $this->nip,
                    'longueur' => strlen($this->nip ?? ''),
                    'format_detecte' => $nipValidation['format'],
                    'format_attendu' => 'XX-QQQQ-YYYYMMDD',
                    'exemple_valide' => 'A1-2345-19901225',
                    'details_analyse' => $nipValidation
                ],
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Correction du format NIP vers le nouveau standard'
            ];
        } else {
            // Si format valide, vérifier l'âge extrait
            $age = $this->getAgeFromNip();
            if ($age !== null) {
                if ($age < 18) {
                    $anomalies[] = [
                        'code' => self::ANOMALIE_AGE_MINEUR,
                        'type' => self::ANOMALIE_CRITIQUE,
                        'message' => "Personne mineure détectée (âge: {$age} ans)",
                        'details' => [
                            'age_calcule' => $age,
                            'nip' => $this->nip,
                            'date_naissance_extraite' => $this->extractDateFromNip()
                        ],
                        'date_detection' => now()->toISOString(),
                        'action_requise' => 'Vérification de l\'âge - Exclusion si confirmé mineur'
                    ];
                } elseif ($age > 100) {
                    $anomalies[] = [
                        'code' => self::ANOMALIE_AGE_SUSPECT,
                        'type' => self::ANOMALIE_MAJEURE,
                        'message' => "Âge suspect détecté (âge: {$age} ans)",
                        'details' => [
                            'age_calcule' => $age,
                            'nip' => $this->nip,
                            'date_naissance_extraite' => $this->extractDateFromNip()
                        ],
                        'date_detection' => now()->toISOString(),
                        'action_requise' => 'Vérification de la date de naissance dans le NIP'
                    ];
                }
            }
        }

        // 3. DOUBLON DANS LE FICHIER/BATCH (MINEURE)
        $doublonFichier = $this->checkDoublonDansFichier();
        if ($doublonFichier) {
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_DOUBLON_FICHIER,
                'type' => self::ANOMALIE_MINEURE,
                'message' => "NIP '{$this->nip}' présent plusieurs fois dans le fichier",
                'details' => $doublonFichier,
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Supprimer les doublons du fichier'
            ];
        }

        // 4. DOUBLON AVEC AUTRE ORGANISATION (CRITIQUE)
        $doublonOrganisation = $this->checkDoublonAvecAutreOrganisation();
        if ($doublonOrganisation) {
            $anomalies[] = [
                'code' => self::ANOMALIE_NIP_DOUBLON_ORGANISATION,
                'type' => self::ANOMALIE_CRITIQUE,
                'message' => "NIP '{$this->nip}' déjà enregistré dans une autre organisation",
                'details' => $doublonOrganisation,
                'date_detection' => now()->toISOString(),
                'action_requise' => 'Vérification et résolution du conflit'
            ];
        }

        return $anomalies;
    }

    /**
     * ✅ MÉTHODES HELPER CONSERVÉES POUR VÉRIFICATIONS NIP
     */
    private function isValidNipFormat(): bool
    {
        if (empty($this->nip)) {
            return false;
        }
        
        $nip = trim($this->nip);
        
        // NOUVEAU FORMAT PRINCIPAL: XX-QQQQ-YYYYMMDD
        $newFormatPattern = '/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/';
        if (preg_match($newFormatPattern, $nip)) {
            // Validation additionnelle de la date
            $parts = explode('-', $nip);
            if (count($parts) === 3) {
                $dateStr = $parts[2]; // YYYYMMDD
                $year = (int)substr($dateStr, 0, 4);
                $month = (int)substr($dateStr, 4, 2);
                $day = (int)substr($dateStr, 6, 2);
                
                if (checkdate($month, $day, $year) && $year >= 1900 && $year <= date('Y')) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function analyzeNipFormat(): array
    {
        $nip = trim($this->nip);
        $analysis = [
            'format' => 'inconnu',
            'longueur' => strlen($nip ?? ''),
            'contient_tirets' => strpos($nip, '-') !== false,
            'est_numerique' => is_numeric(str_replace('-', '', $nip)),
            'structure' => []
        ];

        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', $nip)) {
            $analysis['format'] = 'nouveau_valide';
            $parts = explode('-', $nip);
            $analysis['structure'] = [
                'prefix' => $parts[0],
                'sequence' => $parts[1],
                'date' => $parts[2]
            ];
        } elseif (preg_match('/^[0-9]{13}$/', $nip)) {
            $analysis['format'] = 'ancien_13_chiffres';
        } elseif (strpos($nip, '-') !== false) {
            $analysis['format'] = 'avec_tirets_invalide';
        }

        return $analysis;
    }

    public function getAgeFromNip(): ?int
    {
        if (empty($this->nip)) {
            return null;
        }
        
        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-([0-9]{8})$/', $this->nip, $matches)) {
            $dateStr = $matches[1]; // YYYYMMDD
            $year = (int)substr($dateStr, 0, 4);
            $month = (int)substr($dateStr, 4, 2);
            $day = (int)substr($dateStr, 6, 2);
            
            if (checkdate($month, $day, $year)) {
                $birthDate = \Carbon\Carbon::createFromDate($year, $month, $day);
                return $birthDate->diffInYears(now());
            }
        }
        
        return null;
    }

    public function extractDateFromNip(): ?string
    {
        if (empty($this->nip)) {
            return null;
        }
        
        if (preg_match('/^[A-Z0-9]{2}-[0-9]{4}-([0-9]{8})$/', $this->nip, $matches)) {
            $dateStr = $matches[1]; // YYYYMMDD
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            
            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return "{$day}/{$month}/{$year}";
            }
        }
        
        return null;
    }

    private function checkDoublonDansFichier(): ?array
    {
        if (empty($this->nip)) {
            return null;
        }

        if (!isset(self::$nipBatchTracker[$this->nip])) {
            self::$nipBatchTracker[$this->nip] = [
                'count' => 1,
                'first_occurrence' => [
                    'nom' => $this->nom,
                    'prenom' => $this->prenom,
                    'organisation_id' => $this->organisation_id
                ]
            ];
            return null;
        }

        self::$nipBatchTracker[$this->nip]['count']++;
        
        return [
            'nip' => $this->nip,
            'occurrence_numero' => self::$nipBatchTracker[$this->nip]['count'],
            'premiere_occurrence' => self::$nipBatchTracker[$this->nip]['first_occurrence'],
            'occurrence_actuelle' => [
                'nom' => $this->nom,
                'prenom' => $this->prenom,
                'organisation_id' => $this->organisation_id
            ]
        ];
    }

    private function checkDoublonAvecAutreOrganisation(): ?array
    {
        if (empty($this->nip)) {
            return null;
        }

        $existingAdherent = self::where('nip', $this->nip)
            ->where('organisation_id', '!=', $this->organisation_id)
            ->with('organisation')
            ->first();

        if ($existingAdherent) {
            return [
                'nip' => $this->nip,
                'organisation_existante_id' => $existingAdherent->organisation_id,
                'organisation_existante_nom' => $existingAdherent->organisation->nom ?? 'Organisation inconnue',
                'adherent_existant' => [
                    'id' => $existingAdherent->id,
                    'nom' => $existingAdherent->nom,
                    'prenom' => $existingAdherent->prenom,
                    'is_active' => $existingAdherent->is_active,
                    'date_adhesion' => $existingAdherent->date_adhesion
                ]
            ];
        }

        return null;
    }

    /**
     * ✅ MÉTHODES POUR VÉRIFICATIONS MÉTIER
     */
    private function checkDoubleAppartenanceParti()
    {
        if (empty($this->nip)) {
            return false;
        }

        $existingMembership = self::where('nip', $this->nip)
            ->where('is_active', true)
            ->whereHas('organisation', function ($query) {
                $query->where('type', 'parti_politique')
                      ->where('statut', '!=', 'radie');
            })
            ->where('organisation_id', '!=', $this->organisation_id)
            ->with('organisation')
            ->first();

        if ($existingMembership) {
            return [
                'parti_id' => $existingMembership->organisation_id,
                'parti_nom' => $existingMembership->organisation->nom,
                'date_adhesion_existante' => $existingMembership->date_adhesion,
                'fonction_existante' => $existingMembership->fonction
            ];
        }

        return false;
    }

    private function checkProfessionExclue()
    {
        if (empty($this->profession)) {
            return false;
        }
        
        $professionLower = strtolower($this->profession);
        $exclusLower = array_map('strtolower', self::PROFESSIONS_EXCLUES_PARTIS);
        
        return in_array($professionLower, $exclusLower);
    }

    /**
     * ✅ MÉTHODE POUR DÉFINIR LE STATUT ACTIF
     */
    private function setActiveStatusBasedOnAnomalies(array $anomalies, ?string $severityLevel)
    {
        if (empty($anomalies)) {
            $this->is_active = true;
            return;
        }

        $codesAnomaliesCritiques = array_column(
            array_filter($anomalies, function($a) { return $a['type'] === self::ANOMALIE_CRITIQUE; }),
            'code'
        );

        // Cas spéciaux où on désactive
        $casDesactivationForce = [
            self::ANOMALIE_NIP_ABSENT,
            self::ANOMALIE_PROFESSION_EXCLUE,
            self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI,
            self::ANOMALIE_NIP_DOUBLON_ORGANISATION,
            self::ANOMALIE_AGE_MINEUR,
            self::ANOMALIE_NIP_NON_TROUVE_DATABASE  // ✅ NOUVELLE ANOMALIE CRITIQUE
        ];

        $hasDesactivationForce = !empty(array_intersect($codesAnomaliesCritiques, $casDesactivationForce));

        if ($hasDesactivationForce) {
            $this->is_active = false;
        } else {
            $this->is_active = true;
        }
    }

    /**
     * ✅ MÉTHODES POUR HISTORIQUE
     */
    public function addToHistorique($type, $data = [])
    {
        try {
            $this->addToHistoriqueInternal($type, $data);
            $this->updateQuietly(['historique' => $this->historique]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans addToHistorique: ' . $e->getMessage(), [
                'adherent_id' => $this->id,
                'type' => $type,
                'data' => $data
            ]);
        }
    }

    private function addToHistoriqueInternal($type, $data = [])
    {
        $currentHistorique = $this->historique;
        
        if (is_null($currentHistorique)) {
            $historique = ['events' => []];
        } elseif (is_string($currentHistorique)) {
            $decoded = json_decode($currentHistorique, true);
            $historique = is_array($decoded) ? $decoded : ['events' => []];
        } elseif (is_array($currentHistorique)) {
            $historique = $currentHistorique;
        } else {
            $historique = ['events' => []];
        }
        
        if (!isset($historique['events']) || !is_array($historique['events'])) {
            $historique['events'] = [];
        }
        
        $historique['events'][] = [
            'type' => $type,
            'date' => now()->toISOString(),
            'data' => $data,
            'user_id' => auth()->id()
        ];
        
        $this->historique = $historique;
    }

    /**
     * ✅ RELATIONS
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AdherentHistory::class);
    }

    public function exclusion(): HasOne
    {
        return $this->hasOne(AdherentExclusion::class)->latest();
    }

    public function imports(): HasMany
    {
        return $this->hasMany(AdherentImport::class);
    }
    
    public function fondateur(): BelongsTo
    {
        return $this->belongsTo(Fondateur::class, 'fondateur_id');
    }

    /**
     * ✅ NOUVELLE RELATION - Anomalies
     */
    public function anomalies(): HasMany
    {
        return $this->hasMany(AdherentAnomalie::class);
    }

    /**
     * ✅ SCOPES
     */
    public function scopeActifs($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactifs($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeFondateurs($query)
    {
        return $query->where('is_fondateur', true);
    }

    public function scopeWithAnomalies($query)
    {
        return $query->where('has_anomalies', true);
    }

    public function scopeWithoutAnomalies($query)
    {
        return $query->where('has_anomalies', false);
    }

    public function scopeAnomaliesCritiques($query)
    {
        return $query->where('anomalies_severity', self::ANOMALIE_CRITIQUE);
    }

    public function scopeAnomaliesMajeures($query)
    {
        return $query->where('anomalies_severity', self::ANOMALIE_MAJEURE);
    }

    public function scopeAnomaliesMineures($query)
    {
        return $query->where('anomalies_severity', self::ANOMALIE_MINEURE);
    }

    // ✅ NOUVEAUX SCOPES POUR LES NOUVELLES ANOMALIES
    public function scopeNipNonTrouveDatabase($query)
    {
        return $query->whereJsonContains('anomalies_data', [['code' => self::ANOMALIE_NIP_NON_TROUVE_DATABASE]]);
    }

    public function scopeDonneesIncoherentes($query)
    {
        return $query->whereJsonContains('anomalies_data', [['code' => self::ANOMALIE_DONNEES_INCOHERENTES_DATABASE]]);
    }

    /**
     * ✅ MÉTHODES UTILITAIRES
     */
    public function isExcluded(): bool
    {
        return !$this->is_active || $this->date_exclusion !== null;
    }

    public function canBeTransferred(): bool
    {
        return !$this->is_fondateur && $this->is_active && !$this->isExcluded() 
            && (!$this->has_anomalies || $this->anomalies_severity === self::ANOMALIE_MINEURE);
    }

    public function getAge(): int
    {
        $ageFromNip = $this->getAgeFromNip();
        if ($ageFromNip !== null) {
            return $ageFromNip;
        }
        
        return $this->date_naissance ? $this->date_naissance->age : 0;
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->nom . ' ' . $this->prenom);
    }

    /**
     * ✅ MÉTHODES POUR VÉRIFIER LES NOUVELLES ANOMALIES
     */
    public function hasNipNonTrouveDatabase(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_NIP_NON_TROUVE_DATABASE);
    }

    public function hasDonneesIncoherentes(): bool
    {
        return $this->hasAnomalieCode(self::ANOMALIE_DONNEES_INCOHERENTES_DATABASE);
    }

    private function hasAnomalieCode(string $code): bool
    {
        if (!$this->has_anomalies || !$this->anomalies_data) {
            return false;
        }

        return collect($this->anomalies_data)->contains('code', $code);
    }

    /**
     * ✅ MÉTHODES STATIQUES UTILITAIRES
     */
    public static function getAnomalieTypes(): array
    {
        return [
            self::ANOMALIE_CRITIQUE => 'Critique (désactive l\'adhérent)',
            self::ANOMALIE_MAJEURE => 'Majeure (adhérent actif avec suivi)',
            self::ANOMALIE_MINEURE => 'Mineure (correction recommandée)'
        ];
    }

    public static function getAnomalieCodes(): array
    {
        return [
            self::ANOMALIE_NIP_ABSENT => 'NIP absent ou vide',
            self::ANOMALIE_NIP_INVALIDE => 'Format NIP incorrect',
            self::ANOMALIE_AGE_MINEUR => 'Personne mineure',
            self::ANOMALIE_AGE_SUSPECT => 'Âge suspect',
            self::ANOMALIE_NIP_DOUBLON_FICHIER => 'Doublon dans le fichier',
            self::ANOMALIE_NIP_DOUBLON_ORGANISATION => 'Doublon avec autre organisation',
            self::ANOMALIE_DOUBLE_APPARTENANCE_PARTI => 'Double appartenance parti',
            self::ANOMALIE_PROFESSION_EXCLUE => 'Profession exclue',
            self::ANOMALIE_PROFESSION_MANQUANTE => 'Profession manquante',
            // ✅ NOUVELLES ANOMALIES
            self::ANOMALIE_NIP_NON_TROUVE_DATABASE => 'NIP non trouvé dans la base officielle',
            self::ANOMALIE_DONNEES_INCOHERENTES_DATABASE => 'Données incohérentes avec la base officielle'
        ];
    }

    /**
     * ✅ MÉTHODES STATIQUES POUR GESTION BATCH
     */
    public static function resetBatchTracker()
    {
        self::$nipBatchTracker = [];
    }

    public static function getBatchStatistics(): array
    {
        $totalNips = count(self::$nipBatchTracker);
        $doublons = array_filter(self::$nipBatchTracker, function($data) { return $data['count'] > 1; });
        
        return [
            'total_nips_traites' => $totalNips,
            'nips_uniques' => $totalNips - count($doublons),
            'nips_doublons' => count($doublons),
            'details_doublons' => $doublons
        ];
    }

    /**
     * ✅ NOUVELLE MÉTHODE - Statistiques enrichies avec nip_database
     */
    public function getStatistiquesCompletes(): array
    {
        $nipStatus = $this->getNipStatus();
        
        return [
            'identification' => [
                'nip' => $this->nip,
                'nip_status' => $nipStatus,
                'nom_complet' => $this->nom_complet,
                'age' => $this->getAge()
            ],
            'statuts' => [
                'is_active' => $this->is_active,
                'is_fondateur' => $this->is_fondateur,
                'is_excluded' => $this->isExcluded(),
                'can_be_transferred' => $this->canBeTransferred()
            ],
            'anomalies' => [
                'has_anomalies' => $this->has_anomalies,
                'severity' => $this->anomalies_severity,
                'total_count' => $this->has_anomalies ? count($this->anomalies_data) : 0,
                'nip_database' => [
                    'non_trouve' => $this->hasNipNonTrouveDatabase(),
                    'donnees_incoherentes' => $this->hasDonneesIncoherentes()
                ]
            ],
            'organisation' => [
                'id' => $this->organisation_id,
                'nom' => $this->organisation->nom ?? 'Inconnue',
                'type' => $this->organisation->type ?? 'Inconnu',
                'date_adhesion' => $this->date_adhesion
            ]
        ];
    }

    public function getNipStatus(): array
    {
        if (empty($this->nip)) {
            return ['status' => 'absent', 'valid' => false];
        }

        $anomalies = [];
        if ($this->has_anomalies && $this->anomalies_data) {
            $nipAnomalies = array_filter($this->anomalies_data, function($anomalie) {
                return in_array($anomalie['code'], [
                    self::ANOMALIE_NIP_INVALIDE,
                    self::ANOMALIE_NIP_DOUBLON_FICHIER,
                    self::ANOMALIE_NIP_DOUBLON_ORGANISATION,
                    self::ANOMALIE_AGE_MINEUR,
                    self::ANOMALIE_AGE_SUSPECT,
                    self::ANOMALIE_NIP_NON_TROUVE_DATABASE,
                    self::ANOMALIE_DONNEES_INCOHERENTES_DATABASE
                ]);
            });
            $anomalies = array_column($nipAnomalies, 'code');
        }

        $isValid = $this->isValidNipFormat();
        
        return [
            'status' => $isValid ? (empty($anomalies) ? 'valide' : 'valide_avec_anomalies') : 'invalide',
            'valid' => $isValid,
            'nip' => $this->nip,
            'age' => $this->getAgeFromNip(),
            'date_naissance' => $this->extractDateFromNip(),
            'anomalies' => $anomalies
        ];
    }
}