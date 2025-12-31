<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\DocumentGeneration;
use App\Models\Organisation;
use App\Models\Dossier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * SERVICE DE GÉNÉRATION DE DOCUMENTS
 * 
 * ⭐ VERSION CORRIGÉE - NOVEMBRE 2025
 * Service principal pour générer les documents PDF à la volée
 * à partir des templates Blade configurés
 * 
 * CORRECTIONS APPLIQUÉES :
 * - ✅ Changement de generateSVG() vers getQrCodeBase64ForPdf()
 * - ✅ Enrichissement de la section dossier avec date_soumission
 * - ✅ Gestion de la relation personnes() manquante
 * - ✅ Fallbacks robustes pour toutes les variables
 * 
 * Améliorations Étape 1.1 :
 * - Variables dynamiques avancées (dirigeants, fondateurs, adhérents)
 * - Constantes ministérielles et républicaines
 * - Statistiques enrichies
 * - Formatage automatique des adresses
 * 
 * Améliorations Étape 1.2 :
 * - Watermark texte CSS automatique
 * - Détection automatique du texte watermark
 * - Configuration personnalisable
 * 
 * Améliorations Étape 1.3 :
 * - Intégration ImageHelperService
 * - Logos officiels en base64 (Gabon, Ministère, Drapeau)
 * - Backgrounds avec armoiries
 * - Watermark image
 * - Placeholders SVG automatiques
 * 
 * Projet : SGLP
 * Date : 06 Novembre 2025
 */
class DocumentGenerationService
{
    protected QRCodeService $qrCodeService;
    protected DocumentNumberingService $numberingService;
    protected ImageHelperService $imageHelper;

    public function __construct(
        QRCodeService $qrCodeService,
        DocumentNumberingService $numberingService,
        ImageHelperService $imageHelper
    ) {
        $this->qrCodeService = $qrCodeService;
        $this->numberingService = $numberingService;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Générer un document à la volée
     * 
     * @param DocumentTemplate $template Template à utiliser
     * @param array $data Données du document
     * @return array ['pdf' => stream, 'metadata' => DocumentGeneration, 'filename' => string]
     * @throws \Exception
     */
    public function generate(DocumentTemplate $template, array $data): array
    {
        try {
            // 1. Vérifier que le template existe
            if (!$template->templateExists()) {
                throw new \Exception("Le template Blade '{$template->template_path}' n'existe pas.");
            }

            // 2. Générer numéro unique
            $numeroDocument = $this->numberingService->generate(
                $template->type_document,
                $data['organisation_id']
            );

            // 3. ✅ CORRIGÉ : Générer QR code avec URL basée sur numeroDocument
            $qrCode = $this->qrCodeService->generateForDocument($numeroDocument, [
                'organisation_id' => $data['organisation_id'],
                'dossier_id' => $data['dossier_id'] ?? null,
                'template_id' => $template->id,
                'type_document' => $template->type_document,
            ]);

            // 4. Préparer les variables (VERSION AMÉLIORÉE)
            $variables = $this->prepareVariables($data, $numeroDocument, $qrCode);

            // 5. Valider les variables requises
            $this->validateRequiredVariables($template, $variables);

            // 6. Générer hash de vérification
            $hash = $this->generateHash($numeroDocument, $variables);

            // 7. Enregistrer les métadonnées (LOG uniquement)
            $generation = DocumentGeneration::create([
                'document_template_id' => $template->id,
                'dossier_id' => $data['dossier_id'] ?? null,
                'dossier_validation_id' => $data['dossier_validation_id'] ?? null,
                'organisation_id' => $data['organisation_id'],
                'numero_document' => $numeroDocument,
                'type_document' => $template->type_document,
                'qr_code_token' => $qrCode->code,
                'qr_code_url' => $qrCode->verification_url,
                'hash_verification' => $hash,
                'variables_data' => $variables,
                'generated_by' => Auth::id() ?? 1,
                'generated_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // 8. Générer QR Code Base64 pour le PDF (Footer)
            $qrCodeBase64 = $qrCode ? $this->qrCodeService->getQrCodeBase64ForPdf($qrCode) : '';

            // 9. Générer le HTML avec variables
            $html = $this->renderTemplate($template, $variables, $qrCode);

            // 10. Générer le PDF en mémoire
            $pdf = $this->generatePDF($html, $template, $qrCodeBase64);

            Log::info('Document généré avec succès', [
                'template_id' => $template->id,
                'numero_document' => $numeroDocument,
                'organisation_id' => $data['organisation_id'],
            ]);

            return [
                'pdf' => $pdf,
                'metadata' => $generation,
                'filename' => $this->generateFilename($template, $numeroDocument),
            ];

        } catch (\Exception $e) {
            Log::error('Erreur génération document', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Régénérer un document existant
     * 
     * @param DocumentGeneration $generation Document à régénérer
     * @return array
     * @throws \Exception
     */
    public function regenerate(DocumentGeneration $generation): array
    {
        if (!$generation->is_valid) {
            throw new \Exception('Ce document a été invalidé et ne peut être régénéré.');
        }

        // Incrémenter le compteur de téléchargement
        $generation->incrementDownloadCount();

        $template = $generation->template;

        // Vérifier que le template existe toujours
        if (!$template->templateExists()) {
            throw new \Exception("Le template Blade '{$template->template_path}' n'existe plus.");
        }

        // Récupérer le QR code
        $qrCode = \App\Models\QrCode::where('code', $generation->qr_code_token)->first();

        // Générer le HTML avec les variables sauvegardées
        $html = $this->renderTemplate($template, $generation->variables_data, $qrCode);

        // Générer QR Code Base64 pour le PDF
        $qrCodeBase64 = $qrCode ? $this->qrCodeService->getQrCodeBase64ForPdf($qrCode) : '';

        // Générer le PDF
        $pdf = $this->generatePDF($html, $template, $qrCodeBase64);

        Log::info('Document régénéré', [
            'generation_id' => $generation->id,
            'numero_document' => $generation->numero_document,
        ]);

        return [
            'pdf' => $pdf,
            'metadata' => $generation,
            'filename' => $this->generateFilename($template, $generation->numero_document),
        ];
    }

    /**
     * Préparer les variables pour le template
     * 
     * ⭐ VERSION AMÉLIORÉE avec variables dynamiques avancées
     * ✅ CORRIGÉ : Gestion robuste de la relation personnes()
     * 
     * @param array $data Données brutes
     * @param string $numeroDocument Numéro du document
     * @param \App\Models\QrCode $qrCode QR code généré
     * @return array Variables préparées
     */
    protected function prepareVariables(array $data, string $numeroDocument, $qrCode): array
    {
        // Charger l'organisation avec toutes ses relations
        $organisation = Organisation::with([
            'organisationType',
            'domaineActivite',
            'fondateurs' => function ($query) {
                $query->orderBy('ordre')->limit(10);
            },
            'adherentsActifs',
            'etablissements',
            'dossiers'
        ])->findOrFail($data['organisation_id']);

        $dossier = isset($data['dossier_id']) ? Dossier::find($data['dossier_id']) : null;

        // ========================================
        // VARIABLES DE BASE
        // ========================================

        // ✅ CORRIGÉ : Récupérer le déclarant depuis les données du dossier (étape 2 du formulaire)
        $presidentNom = null;
        $presidentCivilite = null;
        $presidentFonction = null;

        // D'abord, essayer de récupérer depuis donnees_supplementaires du dossier
        if ($dossier && !empty($dossier->donnees_supplementaires)) {
            $donneesSupp = is_array($dossier->donnees_supplementaires)
                ? $dossier->donnees_supplementaires
                : json_decode($dossier->donnees_supplementaires, true);

            $demandeur = $donneesSupp['demandeur'] ?? null;

            if ($demandeur) {
                $presidentNom = trim(($demandeur['prenom'] ?? '') . ' ' . ($demandeur['nom'] ?? ''));
                $presidentCivilite = $demandeur['civilite'] ?? 'M.';
                $presidentFonction = $demandeur['role'] ?? $demandeur['fonction'] ?? 'Président(e)';
            }
        }

        // Fallback : si pas de demandeur dans le dossier, chercher dans les fondateurs
        if (empty($presidentNom)) {
            $president = $organisation->fondateurs()
                ->where('fonction', 'LIKE', '%président%')
                ->orWhere('fonction', 'LIKE', '%president%')
                ->first();

            if ($president) {
                $presidentNom = trim(($president->prenom ?? '') . ' ' . ($president->nom ?? ''));
                $presidentFonction = $president->fonction ?? 'Président(e)';

                // Chercher la civilité depuis l'adherent correspondant
                $adherentPresident = \App\Models\Adherent::where('organisation_id', $organisation->id)
                    ->where('nip', $president->nip)
                    ->first();

                if ($adherentPresident && !empty($adherentPresident->civilite)) {
                    $presidentCivilite = $adherentPresident->civilite;
                } else {
                    $presidentCivilite = ($president->sexe === 'F') ? 'Mme' : 'M.';
                }
            }
        }

        $variables = [
            // Organisation (enrichie)
            'organisation' => [
                'id' => $organisation->id,
                'nom' => $organisation->nom,
                'sigle' => $organisation->sigle ?? '',
                'type' => $organisation->organisationType->nom ?? 'Organisation',
                'type_code' => $organisation->organisationType->code ?? 'organisation',
                'numero_recepisse' => $organisation->numero_recepisse ?? 'En cours',
                'date_creation' => $this->formatDateSafe($organisation->date_creation) ?? 'N/A',
                'objet' => $organisation->objet ?? 'Non spécifié',

                // Nouveaux champs pour récépissé provisoire
                'president_nom' => $presidentNom,
                'president_civilite' => $presidentCivilite,
                'president_fonction' => $presidentFonction,
                'domaine' => $organisation->domaineActivite->nom ?? $organisation->objet ?? 'Social',

                // Adresse complète
                'siege_social' => $organisation->siege_social ?? '',
                'adresse_complete' => $this->formatAdresseComplete($organisation),
                'province' => $organisation->province ?? '',
                'departement' => $organisation->departement ?? '',
                'commune' => $organisation->commune ?? '',
                'ville_commune' => $organisation->ville_commune ?? $organisation->commune ?? '',
                'quartier' => $organisation->quartier ?? '',
                'boite_postale' => $organisation->boite_postale ?? '',

                // Contacts
                'telephone' => $organisation->telephone ?? 'Non renseigné',
                'email' => $organisation->email ?? '',
                'site_web' => $organisation->site_web ?? '',

                // Statuts
                'statut' => $organisation->statut ?? '',
                'is_active' => $organisation->is_active ?? false,
            ],

            // ========================================
            // ✅ DIRIGEANTS (Bureau Exécutif) - AVEC GESTION RELATION MANQUANTE
            // ========================================
            'dirigeants' => $this->getDirigeantsSecure($organisation),

            // ========================================
            // ✅ MEMBRES DU BUREAU (Pour Récépissé Définitif)
            // ========================================
            'organisation_membres' => $this->getMembresBureauPourRecepisse($organisation),

            // ========================================
            // FONDATEURS
            // ========================================
            'fondateurs' => [
                'nombre' => $organisation->fondateurs->count(),
                'liste' => $organisation->fondateurs->map(function ($fondateur) {
                    return [
                        'nom' => $fondateur->nom,
                        'prenom' => $fondateur->prenom,
                        'nip' => $fondateur->nip ?? '',
                        'nom_complet' => trim($fondateur->prenom . ' ' . $fondateur->nom),
                    ];
                })->toArray(),
                'premier' => $organisation->fondateurs->first() ? [
                    'nom_complet' => trim($organisation->fondateurs->first()->prenom . ' ' . $organisation->fondateurs->first()->nom),
                    'nip' => $organisation->fondateurs->first()->nip ?? '',
                ] : null,
            ],

            // ========================================
            // ADHÉRENTS & STATISTIQUES
            // ========================================
            'adherents' => [
                'total' => $organisation->adherentsActifs->count(),
                'hommes' => $organisation->adherentsActifs->where('sexe', 'M')->count(),
                'femmes' => $organisation->adherentsActifs->where('sexe', 'F')->count(),
                'pourcentage_femmes' => $organisation->adherentsActifs->count() > 0
                    ? round(($organisation->adherentsActifs->where('sexe', 'F')->count() / $organisation->adherentsActifs->count()) * 100, 1)
                    : 0,
            ],

            // ========================================
            // ÉTABLISSEMENTS (Antennes)
            // ========================================
            'etablissements' => [
                'nombre' => $organisation->etablissements->count(),
                'provinces' => $organisation->etablissements->pluck('province')->unique()->values()->toArray(),
            ],

            // ========================================
            // ✅ MANDATAIRE (Représentant légal) - AVEC GESTION RELATION MANQUANTE
            // ========================================
            'mandataire' => $this->getMandataireSecure($organisation),

            // ========================================
            // DOCUMENT (Métadonnées)
            // ========================================
            'document' => [
                'numero_document' => $numeroDocument,
                'date_generation' => now()->format('d/m/Y'),
                'date_generation_longue' => $this->formatDateLongue(now()),
                'heure_generation' => now()->format('H:i'),
                'annee' => now()->year,
                'qr_code_url' => $qrCode->verification_url,
                'qr_code_token' => $qrCode->code,
            ],

            // ========================================
            // ✅ DOSSIER (ENRICHI avec date_soumission)
            // ========================================
            'dossier' => $dossier ? [
                'id' => $dossier->id,
                'numero_dossier' => $dossier->numero_dossier,
                'date_depot' => $this->formatDateSafe($dossier->date_depot),
                'date_soumission' => $this->formatDateSafe($dossier->submitted_at) ?? now()->format('d/m/Y'),
                'date_soumission_longue' => $dossier->submitted_at ? $this->formatDateLongue($dossier->submitted_at) : $this->formatDateLongue(now()),
                'statut' => $dossier->statut_label ?? 'En cours',
                'statut_code' => $dossier->statut ?? 'en_cours',
                'type_operation' => $dossier->type_operation ?? 'creation',
                'phase' => $dossier->phase ?? 1,
            ] : [
                // Fallback si pas de dossier
                'numero_dossier' => 'DRAFT-' . time(),
                'date_soumission' => now()->format('d/m/Y'),
                'date_soumission_longue' => $this->formatDateLongue(now()),
                'statut' => 'Brouillon',
                'statut_code' => 'brouillon',
                'type_operation' => 'creation',
                'phase' => 1,
            ],

            // ========================================
            // CONSTANTES MINISTÉRIELLES
            // ========================================
            'ministere' => [
                'nom_complet' => 'MINISTÈRE DE L\'INTÉRIEUR ET DE LA SÉCURITÉ',
                'nom_court' => 'Ministère de l\'Intérieur',
                'sigle' => 'MISD',
                'direction' => 'Direction Générale des Affaires Politiques et Associatives',
                'direction_sigle' => 'DGAPA',
            ],

            // ========================================
            // CONSTANTES RÉPUBLICAINES
            // ========================================
            'republique' => [
                'nom' => 'RÉPUBLIQUE GABONAISE',
                'devise' => 'Union - Travail - Justice',
                'capitale' => 'Libreville',
            ],

            // ========================================
            // INFORMATIONS GÉOGRAPHIQUES
            // ========================================
            'geographie' => [
                'lieu_edition' => 'Libreville',
                'pays' => 'Gabon',
            ],

            // ========================================
            // COULEURS NATIONALES (pour styling)
            // ========================================
            'couleurs' => [
                'vert' => '#009e3f',
                'jaune' => '#ffcd00',
                'bleu' => '#003f7f',
                'rouge' => '#8b1538',
            ],
        ];

        // ========================================
        // ⭐ NOUVEAU : LOGOS OFFICIELS EN BASE64
        // ========================================
        $variables['logos'] = [
            'gabon' => $this->imageHelper->getLogoGabonBase64(),
            'ministere' => $this->imageHelper->getLogoMinistereBase64(),
            'drapeau' => $this->imageHelper->getDrapeauGabonBase64(),
        ];

        // ========================================
        // ⭐ NOUVEAU : BACKGROUNDS DISPONIBLES
        // ========================================
        $variables['backgrounds'] = [
            'armoiries' => $this->imageHelper->getBackgroundArmoiriesGabon(),
        ];

        // ========================================
        // IMAGE DE FOND (pour mPDF)
        // ========================================
        $bgImagePath = public_path('storage/images/bg-pied-page.png');
        $variables['bg_pied_page_base64'] = '';

        if (file_exists($bgImagePath)) {
            $imageData = file_get_contents($bgImagePath);
            $variables['bg_pied_page_base64'] = 'data:image/png;base64,' . base64_encode($imageData);
        } else {
            Log::warning('Image de fond bg-pied-page.png introuvable', ['path' => $bgImagePath]);
        }

        // ========================================
        // ⭐ NOUVEAU : DONNÉES DE MODIFICATION
        // Pour les dossiers de type 'modification'
        // ========================================
        if ($dossier && $dossier->type_operation === 'modification') {
            // ✅ FIX: Forcer le décodage JSON avec gestion d'erreur
            $rawData = $dossier->getAttributes()['donnees_supplementaires'] ?? null;

            Log::debug('Données brutes donnees_supplementaires', [
                'type' => gettype($rawData),
                'length' => is_string($rawData) ? strlen($rawData) : 'N/A'
            ]);

            $donneesSupp = [];
            if (is_string($rawData) && !empty($rawData)) {
                $decoded = json_decode($rawData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // ✅ FIX: Gérer le cas de double encodage JSON
                    // Si le premier décodage retourne une string, décoder à nouveau
                    if (is_string($decoded)) {
                        $decoded = json_decode($decoded, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            Log::error('Erreur 2ème décodage JSON donnees_supplementaires', [
                                'error' => json_last_error_msg()
                            ]);
                            $decoded = [];
                        }
                    }
                    $donneesSupp = $decoded;
                } else {
                    Log::error('Erreur décodage JSON donnees_supplementaires', [
                        'error' => json_last_error_msg(),
                        'raw_preview' => substr($rawData, 0, 100)
                    ]);
                }
            } elseif (is_array($rawData)) {
                $donneesSupp = $rawData;
            }

            Log::info('Données supplementaires décodées', [
                'dossier_id' => $dossier->id,
                'type_modification' => $donneesSupp['type_modification'] ?? 'non défini',
                'has_modifications' => !empty($donneesSupp['modifications']),
                'has_bureau_modifications' => !empty($donneesSupp['bureau_modifications']),
            ]);

            // Récupérer les nouvelles valeurs des modifications
            $nouvellesValeurs = $donneesSupp['modifications'] ?? [];
            $bureauMembres = $donneesSupp['bureau_modifications'] ?? [];

            // ✅ APPLIQUER LES NOUVELLES VALEURS À L'ORGANISATION
            // Créer une copie modifiée des données organisation avec les nouvelles valeurs
            if (!empty($nouvellesValeurs)) {
                // Appliquer chaque modification aux données d'organisation
                if (!empty($nouvellesValeurs['nom'])) {
                    $variables['organisation']['nom'] = $nouvellesValeurs['nom'];
                }
                if (!empty($nouvellesValeurs['sigle'])) {
                    $variables['organisation']['sigle'] = $nouvellesValeurs['sigle'];
                }
                if (!empty($nouvellesValeurs['objet'])) {
                    $variables['organisation']['objet'] = $nouvellesValeurs['objet'];
                }
                if (!empty($nouvellesValeurs['siege_social'])) {
                    $variables['organisation']['siege_social'] = $nouvellesValeurs['siege_social'];
                }
                if (!empty($nouvellesValeurs['province'])) {
                    $variables['organisation']['province'] = $nouvellesValeurs['province'];
                }
                if (!empty($nouvellesValeurs['departement'])) {
                    $variables['organisation']['departement'] = $nouvellesValeurs['departement'];
                }
                if (!empty($nouvellesValeurs['ville_commune'])) {
                    $variables['organisation']['ville_commune'] = $nouvellesValeurs['ville_commune'];
                }
                if (!empty($nouvellesValeurs['commune'])) {
                    $variables['organisation']['commune'] = $nouvellesValeurs['commune'];
                }
                if (!empty($nouvellesValeurs['quartier'])) {
                    $variables['organisation']['quartier'] = $nouvellesValeurs['quartier'];
                }
                if (!empty($nouvellesValeurs['telephone'])) {
                    $variables['organisation']['telephone'] = $nouvellesValeurs['telephone'];
                }
                if (!empty($nouvellesValeurs['email'])) {
                    $variables['organisation']['email'] = $nouvellesValeurs['email'];
                }
                if (!empty($nouvellesValeurs['site_web'])) {
                    $variables['organisation']['site_web'] = $nouvellesValeurs['site_web'];
                }
                if (!empty($nouvellesValeurs['boite_postale'])) {
                    $variables['organisation']['boite_postale'] = $nouvellesValeurs['boite_postale'];
                }

                Log::info('Nouvelles valeurs appliquées à l\'organisation pour le PDF', [
                    'dossier_id' => $dossier->id,
                    'champs_modifies' => array_keys($nouvellesValeurs),
                ]);
            }

            // ✅ APPLIQUER LES NOUVEAUX MEMBRES DU BUREAU
            if (!empty($bureauMembres)) {
                $variables['organisation_membres'] = collect($bureauMembres)->map(function ($membre) {
                    return [
                        'nom' => $membre['nom'] ?? '',
                        'prenom' => $membre['prenom'] ?? '',
                        'nom_complet' => trim(($membre['prenom'] ?? '') . ' ' . ($membre['nom'] ?? '')),
                        'fonction' => $membre['fonction'] ?? '',
                        'contact' => $membre['telephone'] ?? '',
                    ];
                });

                Log::info('Nouveaux membres du bureau appliqués pour le PDF', [
                    'dossier_id' => $dossier->id,
                    'nombre_membres' => count($bureauMembres),
                ]);
            }

            $variables['modifications'] = [
                'type_modification' => $donneesSupp['type_modification'] ?? null,
                'justification' => $donneesSupp['justification'] ?? null,
                'modifications' => $nouvellesValeurs,
                'articles_modifies' => $donneesSupp['articles'] ?? [],
                'bureau_modifications' => $bureauMembres,
            ];

            Log::info('Données de modification ajoutées aux variables PDF', [
                'dossier_id' => $dossier->id,
                'type_modification' => $variables['modifications']['type_modification'] ?? 'non défini',
            ]);
        } else {
            $variables['modifications'] = null;
        }

        return $variables;
    }

    /**
     * ✅ NOUVEAU : Obtenir les dirigeants de manière sécurisée
     * Gère le cas où la relation personnes() n'existe pas
     */
    protected function getDirigeantsSecure(Organisation $organisation): array
    {
        try {
            // Vérifier si la méthode personnes existe
            if (!method_exists($organisation, 'personnes')) {
                Log::warning('Relation personnes() manquante sur Organisation', [
                    'organisation_id' => $organisation->id
                ]);

                return [
                    'president' => null,
                    'vice_president' => null,
                    'secretaire_general' => null,
                    'tresorier' => null,
                    'liste_complete' => [],
                ];
            }

            return [
                'president' => $this->getPersonneByRole($organisation, 'president'),
                'vice_president' => $this->getPersonneByRole($organisation, 'vice_president'),
                'secretaire_general' => $this->getPersonneByRole($organisation, 'secretaire_general'),
                'tresorier' => $this->getPersonneByRole($organisation, 'tresorier'),
                'liste_complete' => $this->getDirigeantsComplets($organisation),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur récupération dirigeants', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);

            return [
                'president' => null,
                'vice_president' => null,
                'secretaire_general' => null,
                'tresorier' => null,
                'liste_complete' => [],
            ];
        }
    }

    /**
     * ✅ NOUVEAU : Obtenir le mandataire de manière sécurisée
     * Gère le cas où la relation personnes() n'existe pas
     */
    protected function getMandataireSecure(Organisation $organisation): ?array
    {
        try {
            if (!method_exists($organisation, 'personnes')) {
                Log::warning('Relation personnes() manquante pour mandataire', [
                    'organisation_id' => $organisation->id
                ]);

                // Fallback : utiliser le premier fondateur
                if ($organisation->fondateurs->isNotEmpty()) {
                    $fondateur = $organisation->fondateurs->first();
                    return [
                        'nom' => $fondateur->nom,
                        'prenom' => $fondateur->prenom,
                        'nom_complet' => trim($fondateur->prenom . ' ' . $fondateur->nom),
                        'nip' => $fondateur->nip ?? '',
                        'email' => '',
                        'telephone' => '',
                        'role' => 'Représentant légal',
                    ];
                }

                return null;
            }

            return $this->getMandataire($organisation);
        } catch (\Exception $e) {
            Log::error('Erreur récupération mandataire', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * ✅ HELPER : Récupérer les personnes de manière sécurisée
     * Gère le cas où personnes() retourne Query Builder OU Collection
     * 
     * @param Organisation $organisation
     * @return \Illuminate\Support\Collection Collection de personnes
     */
    protected function getPersonnesCollection(Organisation $organisation): \Illuminate\Support\Collection
    {
        try {
            if (!method_exists($organisation, 'personnes')) {
                return collect([]);
            }

            $result = $organisation->personnes();

            // Si c'est un Query Builder, récupérer les résultats
            if (
                $result instanceof \Illuminate\Database\Eloquent\Builder ||
                $result instanceof \Illuminate\Database\Eloquent\Relations\Relation
            ) {
                return $result->get();
            }

            // Si c'est déjà une Collection, la retourner
            if ($result instanceof \Illuminate\Support\Collection) {
                return $result;
            }

            // Sinon, convertir en collection
            return collect($result);

        } catch (\Exception $e) {
            Log::error('Erreur getPersonnesCollection', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);
            return collect([]);
        }
    }

    /**
     * Obtenir une personne par son rôle dans l'organisation
     */
    protected function getPersonneByRole(Organisation $organisation, string $role): ?array
    {
        try {
            // Utiliser le helper pour récupérer les personnes
            $personnes = $this->getPersonnesCollection($organisation);

            // Filtrer et récupérer la première personne correspondante
            $personne = $personnes
                ->where('role', $role)
                ->where('is_active', true)
                ->first();

            if (!$personne) {
                return null;
            }

            return [
                'nom' => $personne->nom,
                'prenom' => $personne->prenom,
                'nom_complet' => trim($personne->prenom . ' ' . $personne->nom),
                'nip' => $personne->nip ?? '',
                'email' => $personne->email ?? '',
                'telephone' => $personne->telephone ?? '',
                'role' => $personne->role_label ?? ucfirst(str_replace('_', ' ', $role)),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtenir la liste complète des dirigeants
     */
    protected function getDirigeantsComplets(Organisation $organisation): array
    {
        try {
            // Utiliser le helper pour récupérer les personnes
            $personnes = $this->getPersonnesCollection($organisation);

            // Filtrer les dirigeants
            return $personnes
                ->where('is_active', true)
                ->whereIn('role', ['president', 'vice_president', 'secretaire_general', 'tresorier'])
                ->map(function ($personne) {
                    return [
                        'nom' => $personne->nom,
                        'prenom' => $personne->prenom,
                        'nom_complet' => trim($personne->prenom . ' ' . $personne->nom),
                        'role' => $personne->role_label ?? ucfirst(str_replace('_', ' ', $personne->role)),
                        'nip' => $personne->nip ?? '',
                    ];
                })
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Erreur getDirigeantsComplets', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Obtenir les membres du bureau pour le récépissé définitif
     * 
     * @param Organisation $organisation
     * @return \Illuminate\Support\Collection
     */
    protected function getMembresBureauPourRecepisse(Organisation $organisation)
    {
        try {
            // Charger les membres du bureau marqués pour le récépissé, ordonnés
            return $organisation->membresBureauPourRecepisse()
                ->get()
                ->map(function ($membre) {
                    return [
                        'nom' => $membre->nom,
                        'prenom' => $membre->prenom,
                        'nom_complet' => trim($membre->prenom . ' ' . $membre->nom),
                        'fonction' => $membre->fonction,
                        'contact' => $membre->contact,
                        'domicile' => $membre->domicile,
                        'nip' => $membre->nip,
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Erreur getMembresBureauPourRecepisse', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);
            return collect([]);
        }
    }

    /**
     * Obtenir le mandataire (représentant légal)
     */
    protected function getMandataire(Organisation $organisation): ?array
    {
        try {
            // Utiliser le helper pour récupérer les personnes
            $personnes = $this->getPersonnesCollection($organisation);

            // Chercher un mandataire désigné
            $mandataire = $personnes
                ->where('is_mandataire', true)
                ->where('is_active', true)
                ->first();

            // Sinon, prendre le président
            if (!$mandataire) {
                $mandataire = $personnes
                    ->where('role', 'president')
                    ->where('is_active', true)
                    ->first();
            }

            if (!$mandataire) {
                return null;
            }

            return [
                'nom' => $mandataire->nom,
                'prenom' => $mandataire->prenom,
                'nom_complet' => trim($mandataire->prenom . ' ' . $mandataire->nom),
                'nip' => $mandataire->nip ?? '',
                'email' => $mandataire->email ?? '',
                'telephone' => $mandataire->telephone ?? '',
                'role' => $mandataire->is_mandataire ? 'Représentant légal' : 'Représentant légal',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur getMandataire', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Formater l'adresse complète de l'organisation
     */
    protected function formatAdresseComplete(Organisation $organisation): string
    {
        $parts = array_filter([
            $organisation->siege_social,
            $organisation->quartier,
            $organisation->commune,
            $organisation->province,
        ]);

        $adresse = implode(', ', $parts);

        if ($organisation->boite_postale) {
            $adresse .= ' - BP ' . $organisation->boite_postale;
        }

        return $adresse ?: 'Adresse non renseignée';
    }

    /**
     * ✅ HELPER : Formater une date de manière sécurisée (format court)
     * Gère string, Carbon, DateTime
     * 
     * @param mixed $date Date à formater
     * @param string $format Format de sortie
     * @return string|null Date formatée ou null
     */
    protected function formatDateSafe($date, string $format = 'd/m/Y'): ?string
    {
        try {
            if (empty($date)) {
                return null;
            }

            // Si c'est déjà une string formatée, vérifier si c'est une date valide
            if (is_string($date)) {
                // Tenter de parser la date
                $dateObj = \Carbon\Carbon::parse($date);
                return $dateObj->format($format);
            }

            // Si c'est un objet Carbon ou DateTime
            if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
                return $date->format($format);
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('Erreur formatDateSafe', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Formater une date en format long français
     * ✅ MODIFIÉ : Accepte string, Carbon, DateTime
     */
    protected function formatDateLongue($date): string
    {
        try {
            // Convertir en objet Carbon si nécessaire
            if (is_string($date)) {
                $date = \Carbon\Carbon::parse($date);
            } elseif (!($date instanceof \Carbon\Carbon || $date instanceof \DateTime)) {
                $date = now();
            }

            $mois = [
                1 => 'janvier',
                2 => 'février',
                3 => 'mars',
                4 => 'avril',
                5 => 'mai',
                6 => 'juin',
                7 => 'juillet',
                8 => 'août',
                9 => 'septembre',
                10 => 'octobre',
                11 => 'novembre',
                12 => 'décembre'
            ];

            $jour = $date->format('j');
            $moisNum = (int) $date->format('n');
            $annee = $date->format('Y');

            return "le {$jour} {$mois[$moisNum]} {$annee}";
        } catch (\Exception $e) {
            return "le " . now()->format('j') . " " . now()->format('F') . " " . now()->format('Y');
        }
    }

    /**
     * Valider que toutes les variables requises sont présentes
     * 
     * @param DocumentTemplate $template
     * @param array $variables
     * @throws \Exception
     */
    protected function validateRequiredVariables(DocumentTemplate $template, array $variables): void
    {
        if (empty($template->required_variables)) {
            return;
        }

        $flatVariables = $this->flattenArray($variables);
        $missingVars = [];

        foreach ($template->required_variables as $requiredVar) {
            if (!isset($flatVariables[$requiredVar])) {
                $missingVars[] = $requiredVar;
            }
        }

        if (!empty($missingVars)) {
            throw new \Exception('Variables requises manquantes : ' .
                implode(', ', $missingVars));
        }
    }

    /**
     * ✅ CORRIGÉ : Rendre le template HTML avec variables
     * Utilise getQrCodeBase64ForPdf() au lieu de generateSVG()
     * 
     * @param DocumentTemplate $template Template
     * @param array $variables Variables
     * @param \App\Models\QrCode|null $qrCode QR code généré
     * @return string HTML généré
     */
    protected function renderTemplate(DocumentTemplate $template, array $variables, $qrCode = null): string
    {
        // ✅ CORRECTION : Utiliser getQrCodeBase64ForPdf() au lieu de generateSVG()
        $qrCodeSvg = '';
        if ($template->has_qr_code) {
            if ($qrCode) {
                $qrCodeSvg = $this->qrCodeService->getQrCodeBase64ForPdf($qrCode);
            } elseif (!empty($variables['document']['qr_code_url'])) {
                $qrCodeSvg = $this->qrCodeService->getQrCodeBase64ForPdf(null, $variables['document']['qr_code_url']);
            }
        }

        // ========================================
        // ÉTAPE 1.2 : Générer le CSS du watermark
        // ========================================
        $watermarkCss = '';
        if ($template->has_watermark) {
            $watermarkCss = $this->generateWatermarkCss($template, $variables);
        }

        // ========================================
        // ⭐ ÉTAPE 1.3 : Générer background CSS si activé
        // ========================================
        $backgroundCss = '';
        if (!empty($template->metadata['background']['enabled'])) {
            $backgroundType = $template->metadata['background']['type'] ?? 'armoiries';

            if ($backgroundType === 'armoiries') {
                $opacity = $template->metadata['background']['opacity'] ?? 0.05;
                $backgroundCss = $this->imageHelper->getBackgroundArmoiriesGabon($opacity);
            } elseif ($backgroundType === 'custom' && !empty($template->metadata['background']['image_path'])) {
                $backgroundCss = $this->imageHelper->generateImageWatermark(
                    $template->metadata['background']['image_path'],
                    $template->metadata['background']['options'] ?? []
                );
            }
        }

        // ✅ Encoder l'image de pied de page pour mPDF
        $bgPiedPageBase64 = $this->getBackgroundImageBase64();

        // Rendre le template Blade
        $html = View::make($template->template_path, [
            ...$variables,
            'qr_code_svg' => $qrCodeSvg,
            // ⚠️ FIX: Désactiver le QR code dans le layout Blade car il est géré par mPDF dans le footer
            'has_qr_code' => false,
            'has_signature' => $template->has_signature,
            'signature_text' => $template->signature_text ?? '',
            'has_watermark' => $template->has_watermark,
            'watermark_css' => $watermarkCss,
            'background_css' => $backgroundCss,
            'has_background' => !empty($backgroundCss),
            'bg_pied_page_base64' => $bgPiedPageBase64,  // ✅ NOUVEAU
            'signature_path' => $template->getSignatureFullPath(),
        ])->render();

        // ========================================
        // ÉTAPE 1.2 : Injecter le CSS watermark dans le HTML
        // ========================================
        if (!empty($watermarkCss)) {
            $html = $this->injectWatermarkCss($html, $watermarkCss);
        }

        // ========================================
        // ⭐ ÉTAPE 1.3 : Injecter le CSS background dans le HTML
        // ========================================
        if (!empty($backgroundCss)) {
            $html = $this->injectBackgroundCss($html, $backgroundCss);
        }

        return $html;
    }

    /**
     * Générer le PDF à partir du HTML
     * 
     * @param string $html HTML à convertir
     * @param DocumentTemplate $template Template
     * @param string|null $qrCodeBase64 Image base64 du QR Code pour le footer
     * @return \Mpdf\Mpdf
     */
    protected function generatePDF(string $html, DocumentTemplate $template, ?string $qrCodeBase64 = null)
    {
        Log::info('DocumentGenerationService::generatePDF', [
            'has_qr_code_base64' => !empty($qrCodeBase64),
            'qr_code_length' => strlen($qrCodeBase64 ?? ''),
            'template_id' => $template->id
        ]);

        // Options pour PdfTemplateHelper (si le template a des headers/footers personnalisés)
        $pdfOptions = [
            'header_text' => $template->header_text ?? '',
            'signature_text' => $template->signature_text ?? '',
            'qr_code_base64' => $qrCodeBase64 ?? '', // Passer le QR Code au helper mPDF
        ];

        // Pour le récépissé définitif (document multi-pages), header uniquement sur première page
        if (
            stripos($template->template_path, 'recepisse-definitif') !== false ||
            stripos($template->nom, 'récépissé définitif') !== false
        ) {
            $pdfOptions['header_first_page_only'] = true;
            $pdfOptions['bg_in_footer'] = true; // Utiliser le footer pour répéter l'image sur toutes les pages
        }

        // Pour le récépissé provisoire, activer aussi l'image de fond
        if (
            stripos($template->template_path, 'recepisse-provisoire') !== false ||
            stripos($template->nom, 'récépissé provisoire') !== false
        ) {
            $pdfOptions['bg_in_footer'] = true; // Image de fond pour le récépissé provisoire aussi
        }

        // Utiliser PdfTemplateHelper pour générer le PDF avec mPDF
        // Note: Les marges seront celles par défaut de mPDF (15mm)
        $mpdf = \App\Helpers\PdfTemplateHelper::generatePdf(
            $html,
            $template->getPdfOrientation(),
            $template->getPdfFormat(),
            $pdfOptions
        );

        return $mpdf;
    }

    /**
     * Générer le nom du fichier PDF
     * 
     * @param DocumentTemplate $template Template
     * @param string $numeroDocument Numéro du document
     * @return string Nom du fichier
     */
    protected function generateFilename(DocumentTemplate $template, string $numeroDocument): string
    {
        $slug = \Str::slug($template->nom);
        $cleanNumber = str_replace(['/', '\\', '-'], '_', $numeroDocument);

        return "{$slug}_{$cleanNumber}.pdf";
    }

    /**
     * Générer hash de vérification
     * 
     * @param string $numeroDocument Numéro du document
     * @param array $variables Variables utilisées
     * @return string Hash SHA-256
     */
    protected function generateHash(string $numeroDocument, array $variables): string
    {
        return hash('sha256', $numeroDocument . json_encode($variables) . config('app.key'));
    }

    /**
     * Aplatir un tableau multidimensionnel avec notation point
     * 
     * @param array $array Tableau à aplatir
     * @param string $prefix Préfixe pour les clés
     * @return array Tableau aplati
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value) && !empty($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * ÉTAPE 1.2 : Générer le CSS pour le watermark
     * 
     * @param DocumentTemplate $template
     * @param array $variables
     * @return string CSS du watermark
     */
    protected function generateWatermarkCss(DocumentTemplate $template, array $variables): string
    {
        // Configuration par défaut du watermark
        $config = [
            'text' => $this->getWatermarkText($template, $variables),
            'opacity' => 0.1,
            'rotation' => -45,
            'font_size' => '120px',
            'color' => '#009e3f', // Vert Gabon par défaut
            'font_weight' => 'bold',
            'z_index' => -1,
        ];

        // Permettre la personnalisation via metadata du template
        if (!empty($template->metadata['watermark'])) {
            $config = array_merge($config, $template->metadata['watermark']);
        }

        // Générer le CSS
        return "
            <style>
                body::before {
                    content: '{$config['text']}';
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate({$config['rotation']}deg);
                    font-size: {$config['font_size']};
                    font-weight: {$config['font_weight']};
                    color: {$config['color']};
                    opacity: {$config['opacity']};
                    z-index: {$config['z_index']};
                    white-space: nowrap;
                    pointer-events: none;
                    user-select: none;
                }
            </style>
        ";
    }

    /**
     * ÉTAPE 1.2 : Déterminer le texte du watermark
     * 
     * @param DocumentTemplate $template
     * @param array $variables
     * @return string Texte du watermark
     */
    protected function getWatermarkText(DocumentTemplate $template, array $variables): string
    {
        // Si le texte est défini dans les metadata du template
        if (!empty($template->metadata['watermark']['text'])) {
            return $template->metadata['watermark']['text'];
        }

        // Texte par défaut selon le type de document
        $defaults = [
            'recepisse_provisoire' => 'PROVISOIRE',
            'recepisse_definitif' => 'DOCUMENT OFFICIEL',
            'accuse_reception' => 'ACCUSÉ DE RÉCEPTION',
            'certificat' => 'CONFIDENTIEL',
            'attestation' => 'ORIGINAL',
        ];

        $typeDocument = $template->type_document;

        // Chercher une correspondance
        foreach ($defaults as $key => $text) {
            if (stripos($typeDocument, $key) !== false) {
                return $text;
            }
        }

        // Par défaut
        return 'DOCUMENT OFFICIEL';
    }

    /**
     * ÉTAPE 1.2 : Injecter le CSS watermark dans le HTML
     * 
     * @param string $html HTML original
     * @param string $watermarkCss CSS à injecter
     * @return string HTML modifié
     */
    protected function injectWatermarkCss(string $html, string $watermarkCss): string
    {
        // Chercher la balise </head> pour injecter le CSS avant
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $watermarkCss . "\n</head>", $html);
        }
        // Si pas de </head>, chercher <style>
        elseif (stripos($html, '<style>') !== false) {
            $html = str_ireplace('<style>', '<style>' . "\n" . $watermarkCss, $html);
        }
        // Sinon, ajouter au début du body
        else {
            $html = str_ireplace('<body>', '<body>' . "\n" . $watermarkCss, $html);
        }

        return $html;
    }

    /**
     * ⭐ ÉTAPE 1.3 : Générer un watermark image (utilise ImageHelperService)
     * 
     * Utilise une image de logo en filigrane au lieu de texte CSS
     * 
     * @param DocumentTemplate $template
     * @param string $imagePath Chemin vers l'image du watermark
     * @return string CSS du watermark image
     */
    protected function generateImageWatermarkCss(DocumentTemplate $template, string $imagePath): string
    {
        // Utiliser ImageHelperService pour générer le watermark
        $options = [
            'opacity' => $template->metadata['watermark']['opacity'] ?? 0.1,
            'size' => $template->metadata['watermark']['size'] ?? '400px',
            'rotation' => $template->metadata['watermark']['rotation'] ?? -45,
        ];

        return $this->imageHelper->generateImageWatermark($imagePath, $options);
    }

    /**
     * ⭐ ÉTAPE 1.3 : Injecter le CSS background dans le HTML
     * 
     * @param string $html HTML original
     * @param string $backgroundCss CSS à injecter
     * @return string HTML modifié
     */
    protected function injectBackgroundCss(string $html, string $backgroundCss): string
    {
        // Chercher la balise </head> pour injecter le CSS avant
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $backgroundCss . "\n</head>", $html);
        }
        // Si pas de </head>, chercher <style>
        elseif (stripos($html, '<style>') !== false) {
            $html = str_ireplace('<style>', '<style>' . "\n" . $backgroundCss, $html);
        }
        // Sinon, ajouter après le watermark ou au début du body
        elseif (stripos($html, '<body>') !== false) {
            $html = str_ireplace('<body>', '<body>' . "\n" . $backgroundCss, $html);
        }

        return $html;
    }

    /**
     * ✅ NOUVEAU : Encoder l'image de pied de page en base64
     * Pour utilisation avec mPDF dans les templates
     */
    protected function getBackgroundImageBase64(): ?string
    {
        try {
            $imagePath = public_path('storage/images/bg-pied-page.png');

            if (!file_exists($imagePath)) {
                Log::warning('Image bg-pied-page.png introuvable', ['path' => $imagePath]);
                return null;
            }

            $imageData = file_get_contents($imagePath);
            $base64 = base64_encode($imageData);

            return 'data:image/png;base64,' . $base64;

        } catch (\Exception $e) {
            Log::error('Erreur encodage image bg-pied-page.png', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}