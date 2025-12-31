<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\OperationType;
use App\Models\DocumentType;
use App\Models\Adherent;
use App\Models\Fondateur;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperationController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Afficher la liste des organisations pour sélectionner
     */
    public function selectOrganisation(Request $request)
    {
        $query = Organisation::query()
            ->with(['organisationType'])
            ->where('statut', 'approuve') // Seules les organisations validées/approuvées
            ->orderBy('nom');

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('sigle', 'like', "%{$search}%")
                    ->orWhere('numero_recepisse', 'like', "%{$search}%");
            });
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('organisation_type_id', $request->type);
        }

        $organisations = $query->paginate(15)->withQueryString();
        $organisationTypes = \App\Models\OrganisationType::actif()->ordered()->get();

        // Paramètre d'opération directe (depuis le menu)
        $selectedOperation = $request->input('operation');
        $operationType = null;
        if ($selectedOperation) {
            $operationType = OperationType::where('code', $selectedOperation)->where('is_active', true)->first();
        }

        return view('admin.operations.select-organisation', compact('organisations', 'organisationTypes', 'selectedOperation', 'operationType'));
    }

    /**
     * Afficher les opérations disponibles pour une organisation
     */
    public function selectOperation(Organisation $organisation)
    {
        // Récupérer les types d'opérations actifs (sauf création et changement_statutaire qui est inclus dans modification)
        $operationTypes = OperationType::where('is_active', true)
            ->where('code', '!=', 'creation')
            ->where('code', '!=', 'changement_statutaire')
            ->orderBy('ordre')
            ->get();

        // Vérifier les opérations en cours pour cette organisation
        $dossiersEnCours = Dossier::where('organisation_id', $organisation->id)
            ->whereIn('statut', [Dossier::STATUT_BROUILLON, Dossier::STATUT_SOUMIS, Dossier::STATUT_EN_COURS])
            ->get()
            ->keyBy('type_operation');

        return view('admin.operations.select-operation', compact('organisation', 'operationTypes', 'dossiersEnCours'));
    }

    /**
     * Formulaire générique de création d'opération
     */
    public function create(Organisation $organisation, $operationType)
    {
        // Valider le type d'opération
        $opType = OperationType::where('code', $operationType)
            ->where('is_active', true)
            ->firstOrFail();

        // Note: La redirection vers la sélection des champs est désactivée
        // Le formulaire de modification inclut maintenant un sélecteur de type
        // (informations générales, statuts/R.I., bureau, mixte)

        // Récupérer le dossier actuel de l'organisation (pour afficher les valeurs existantes)
        $dossierActuel = $organisation->dossierActif;

        // Récupérer les documents requis pour cette opération
        $documentTypes = $opType->documentTypes()
            ->where('is_active', true)
            ->orderBy('document_type_operation_type.ordre')
            ->get();

        // Champs sélectionnés pour modification (passés via requête)
        $champsModifies = request()->input('champs_modifies', []);

        // Données spécifiques selon le type
        $viewData = [
            'organisation' => $organisation->load(['organisationType', 'fondateurs', 'membresBureau', 'adherents']),
            'operationType' => $opType,
            'documentTypes' => $documentTypes,
            'dossierActuel' => $dossierActuel,
            'champsModifies' => $champsModifies,
        ];

        // Vue spécifique selon le type d'opération
        $viewName = $this->getViewName($operationType);

        return view($viewName, $viewData);
    }

    /**
     * Formulaire de sélection des champs à modifier (étape préalable pour les modifications)
     */
    public function selectModificationFields(Organisation $organisation)
    {
        // Récupérer le dossier actuel de l'organisation
        $dossierActuel = $organisation->dossierActif;

        // Liste des champs modifiables groupés par catégorie
        $champsModifiables = [
            'informations_generales' => [
                'label' => 'Informations générales',
                'icon' => 'fas fa-info-circle',
                'champs' => [
                    'nom' => 'Nom de l\'organisation',
                    'sigle' => 'Sigle / Acronyme',
                    'objet' => 'Objet / Mission',
                    'devise' => 'Devise',
                ]
            ],
            'adresse' => [
                'label' => 'Adresse et localisation',
                'icon' => 'fas fa-map-marker-alt',
                'champs' => [
                    'siege_social' => 'Siège social',
                    'province' => 'Province',
                    'commune' => 'Commune',
                    'quartier' => 'Quartier / Zone',
                ]
            ],
            'contacts' => [
                'label' => 'Contacts',
                'icon' => 'fas fa-phone',
                'champs' => [
                    'telephone' => 'Téléphone',
                    'email' => 'Adresse email',
                    'site_web' => 'Site web',
                ]
            ],
            'bureau' => [
                'label' => 'Bureau exécutif',
                'icon' => 'fas fa-users',
                'champs' => [
                    'membres_bureau' => 'Composition du bureau',
                    'president' => 'Président / Représentant légal',
                ]
            ],
            'adherents' => [
                'label' => 'Adhérents',
                'icon' => 'fas fa-user-friends',
                'champs' => [
                    'liste_adherents' => 'Liste des adhérents',
                ]
            ],
            'documents_constitutifs' => [
                'label' => 'Documents constitutifs',
                'icon' => 'fas fa-file-alt',
                'champs' => [
                    'statuts' => 'Statuts',
                    'reglement_interieur' => 'Règlement intérieur',
                ]
            ],
        ];

        return view('admin.operations.select-modification-fields', compact(
            'organisation',
            'dossierActuel',
            'champsModifiables'
        ));
    }

    /**
     * Enregistrer une nouvelle opération
     */
    public function store(Request $request, Organisation $organisation, $operationType)
    {
        // DEBUG: Log de l'arrivée de la requête
        Log::info("=== DEBUT store() ===", [
            'organisation_id' => $organisation->id,
            'operationType' => $operationType,
            'action' => $request->input('action'),
            'user_id' => auth()->id(),
            'has_csrf' => $request->has('_token')
        ]);

        // Valider le type d'opération
        $opType = OperationType::where('code', $operationType)
            ->where('is_active', true)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Action (brouillon ou soumettre)
            $action = $request->input('action', 'brouillon');
            $statut = ($action === 'soumettre') ? Dossier::STATUT_SOUMIS : Dossier::STATUT_BROUILLON;

            // Récupérer le dossier actuel de l'organisation
            $dossierActuel = $organisation->dossierActif;

            // Vérifier si l'opération nécessite le versioning
            if ($dossierActuel && $this->isVersionableOperation($operationType)) {
                // Récupérer les champs modifiés (pour les modifications)
                $champsModifies = ($operationType === 'modification')
                    ? $request->input('champs_modifies', [])
                    : null;

                // Dupliquer le dossier existant pour créer une nouvelle version
                $dossier = $dossierActuel->duplicate($operationType, $champsModifies);

                // Mettre à jour le statut selon l'action
                $dossier->update([
                    'statut' => $statut,
                    'date_soumission' => ($action === 'soumettre') ? now() : null,
                    'donnees_supplementaires' => $this->prepareDonneesSupplementaires($request, $operationType, $organisation),
                ]);

                Log::info("Nouvelle version de dossier créée", [
                    'parent_dossier_id' => $dossierActuel->id,
                    'new_dossier_id' => $dossier->id,
                    'version' => $dossier->version,
                    'type_operation' => $operationType,
                ]);
            } else {
                // Créer un nouveau dossier (cas de création ou pas de dossier existant)
                $dossier = Dossier::create([
                    'organisation_id' => $organisation->id,
                    'numero_dossier' => Dossier::generateNumeroDossier($operationType),
                    'type_operation' => $operationType,
                    'statut' => $statut,
                    'date_soumission' => ($action === 'soumettre') ? now() : null,
                    'donnees_supplementaires' => $this->prepareDonneesSupplementaires($request, $operationType, $organisation),
                    'version' => 1,
                    'is_current_version' => true,
                ]);
            }

            // Traiter les données spécifiques selon le type d'opération
            $this->processOperationData($request, $dossier, $organisation, $operationType);

            // Traiter les documents uploadés
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $documentTypeId => $file) {
                    $path = $file->store('dossiers/' . $dossier->id, 'public');
                    $hash = hash_file('sha256', $file->getRealPath());

                    $dossier->documents()->create([
                        'document_type_id' => $documentTypeId,
                        'chemin_fichier' => $path,
                        'nom_fichier' => $file->getClientOriginalName(),
                        'nom_original' => $file->getClientOriginalName(),
                        'type_mime' => $file->getClientMimeType(),
                        'taille' => $file->getSize(),
                        'hash_fichier' => $hash,
                        'uploaded_by' => auth()->id(),
                        'is_system_generated' => false,
                    ]);
                }
            }

            // Générer le QR Code
            try {
                $this->qrCodeService->generateForDossier($dossier);
            } catch (\Exception $e) {
                Log::warning('Erreur génération QR Code: ' . $e->getMessage());
            }

            // Enregistrer l'opération dans l'historique
            if (method_exists($dossier, 'operations')) {
                $dossier->operations()->create([
                    'type_operation' => 'creation',
                    'user_id' => auth()->id(),
                    'description' => "Dossier de {$opType->libelle} créé",
                    'ancien_statut' => null,
                    'nouveau_statut' => $statut,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            DB::commit();

            Log::info("Dossier d'opération créé", [
                'dossier_id' => $dossier->id,
                'dossier_numero' => $dossier->numero_dossier,
                'type_operation' => $operationType,
                'organisation_id' => $organisation->id,
                'action' => $action,
                'admin_id' => auth()->id(),
            ]);

            $successMessage = ($action === 'soumettre')
                ? "Dossier de {$opType->libelle} soumis avec succès. Numéro: {$dossier->numero_dossier}"
                : "Dossier de {$opType->libelle} enregistré comme brouillon. Numéro: {$dossier->numero_dossier}";

            return redirect()->route('admin.dossiers.show', $dossier->id)
                ->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur création dossier opération: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur lors de la création du dossier: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Obtenir le nom de la vue selon le type d'opération
     */
    private function getViewName($operationType)
    {
        $views = [
            'modification' => 'admin.operations.modification',
            'cessation' => 'admin.operations.cessation',
            'ajout_adherent' => 'admin.operations.ajout-adherent',
            'retrait_adherent' => 'admin.operations.retrait-adherent',
            'declaration_activite' => 'admin.operations.declaration-activite',
            'changement_statutaire' => 'admin.operations.changement-statutaire',
        ];

        return $views[$operationType] ?? 'admin.operations.generic';
    }

    /**
     * Préparer les données supplémentaires selon le type d'opération
     */
    private function prepareDonneesSupplementaires(Request $request, $operationType, Organisation $organisation)
    {
        $data = [
            'organisation_avant' => [
                'nom' => $organisation->nom,
                'sigle' => $organisation->sigle,
                'siege_social' => $organisation->siege_social,
            ],
            'demandeur' => [
                'user_id' => auth()->id(),
                'nom' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'date_demande' => now()->toDateTimeString(),
        ];

        // Données spécifiques selon l'opération
        switch ($operationType) {
            case 'modification':
                // Type de modification sélectionné
                $data['type_modification'] = $request->input('type_modification', 'informations');

                // Modifications des informations générales
                $data['modifications'] = $request->input('modifications', []);

                // Justification des modifications
                $data['justification'] = $request->input('justification');

                // Pour les changements statutaires
                if (in_array($data['type_modification'], ['changement_statutaire', 'mixte'])) {
                    $data['date_ag'] = $request->input('date_ag');
                    $data['quorum'] = $request->input('quorum');
                    $data['vote'] = $request->input('vote');
                    $data['documents_concernes'] = $request->input('documents_concernes', []);

                    // Articles modifiés avec détails complets
                    $articles = $request->input('articles', []);
                    $data['articles_modifies'] = [];
                    foreach ($articles as $article) {
                        if (!empty($article['numero']) || !empty($article['ancien_contenu']) || !empty($article['nouveau_contenu'])) {
                            $data['articles_modifies'][] = [
                                'document' => $article['document'] ?? 'statuts',
                                'numero' => $article['numero'] ?? null,
                                'titre' => $article['titre'] ?? null,
                                'ancien_contenu' => $article['ancien_contenu'] ?? null,
                                'nouveau_contenu' => $article['nouveau_contenu'] ?? null,
                                'motif' => $article['motif'] ?? null,
                            ];
                        }
                    }
                    $data['nombre_articles_modifies'] = count($data['articles_modifies']);
                }

                // Pour les modifications du bureau
                if (in_array($data['type_modification'], ['bureau', 'mixte'])) {
                    $bureauMembres = $request->input('bureau_membres', []);
                    $data['bureau_modifications'] = [];
                    foreach ($bureauMembres as $membre) {
                        if (!empty($membre['nom']) || !empty($membre['fonction'])) {
                            $data['bureau_modifications'][] = [
                                'type_changement' => $membre['type_changement'] ?? 'ajout',
                                'fonction' => $membre['fonction'] ?? null,
                                'civilite' => $membre['civilite'] ?? null,
                                'nom' => $membre['nom'] ?? null,
                                'prenom' => $membre['prenom'] ?? null,
                                'date_naissance' => $membre['date_naissance'] ?? null,
                                'telephone' => $membre['telephone'] ?? null,
                                'email' => $membre['email'] ?? null,
                            ];
                        }
                    }
                    $data['nombre_modifications_bureau'] = count($data['bureau_modifications']);
                }
                break;

            case 'cessation':
                $data['motif_cessation'] = $request->input('motif_cessation');
                $data['date_effet'] = $request->input('date_effet');
                break;

            case 'ajout_adherent':
                $data['nouveaux_adherents'] = $request->input('adherents', []);
                break;

            case 'retrait_adherent':
                $data['adherents_retires'] = $request->input('adherents_ids', []);
                $data['motif_retrait'] = $request->input('motif_retrait');
                break;

            case 'declaration_activite':
                $data['periode'] = $request->input('periode');
                $data['activites'] = $request->input('activites');
                $data['bilan'] = $request->input('bilan');
                break;

            case 'changement_statutaire':
                $data['type_changement'] = $request->input('type_changement');
                $data['date_ag'] = $request->input('date_ag');
                $data['description_changements'] = $request->input('description_changements');
                $data['documents_concernes'] = $request->input('documents_concernes', []);
                $data['justification'] = $request->input('justification');
                $data['quorum'] = $request->input('quorum');
                $data['vote'] = $request->input('vote');
                $data['ancien_texte'] = $request->input('ancien_texte');
                $data['nouveau_texte'] = $request->input('nouveau_texte');

                // Articles modifiés avec détails complets
                $articles = $request->input('articles', []);
                $data['articles_modifies'] = [];
                foreach ($articles as $article) {
                    if (!empty($article['numero'])) {
                        $data['articles_modifies'][] = [
                            'document' => $article['document'] ?? 'statuts',
                            'numero' => $article['numero'],
                            'titre' => $article['titre'] ?? null,
                            'ancien_contenu' => $article['ancien_contenu'] ?? null,
                            'nouveau_contenu' => $article['nouveau_contenu'] ?? null,
                            'motif' => $article['motif'] ?? null,
                        ];
                    }
                }
                $data['nombre_articles_modifies'] = count($data['articles_modifies']);
                break;
        }

        return json_encode($data);
    }

    /**
     * Traiter les données spécifiques selon l'opération
     */
    private function processOperationData(Request $request, Dossier $dossier, Organisation $organisation, $operationType)
    {
        switch ($operationType) {
            case 'ajout_adherent':
                // Les adhérents seront ajoutés après validation du dossier
                break;

            case 'retrait_adherent':
                // Les adhérents seront retirés après validation du dossier
                break;

            // Les autres opérations ne nécessitent pas de traitement immédiat
            // Les modifications seront appliquées après validation
        }
    }

    /**
     * Vérifier si le type d'opération nécessite le versioning
     * 
     * Ces opérations créent une nouvelle version du dossier
     * pour préserver l'historique des modifications
     */
    private function isVersionableOperation(string $operationType): bool
    {
        return in_array($operationType, [
            'modification',
            'cessation',
            'ajout_adherent',
            'retrait_adherent',
            'declaration_activite',
            'changement_statutaire',
        ]);
    }
}
