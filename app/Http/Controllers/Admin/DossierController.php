<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\User;
use App\Models\DossierValidation;
use App\Models\Province;
use App\Models\Departement;
use App\Models\CommuneVille;
use App\Models\Arrondissement;
use App\Models\Canton;
use App\Models\Regroupement;
use App\Models\Localite;
use App\Models\OrganisationType;
use App\Models\Fondateur;
use App\Models\Adherent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\PDFService;
use App\Services\FifoPriorityService;
use App\Services\WorkflowService;
use App\Services\QRCodeService;


class DossierController extends Controller
{
    protected $pdfService;
    protected $workflowService;
    protected $qrCodeService;
    protected $fifoPriorityService;

    public function __construct(
        PDFService $pdfService,
        WorkflowService $workflowService,
        QRCodeService $qrCodeService,
        FifoPriorityService $fifoPriorityService
    ) {
        $this->middleware(['auth', 'verified', 'admin']);
        $this->pdfService = $pdfService;
        $this->workflowService = $workflowService;
        $this->qrCodeService = $qrCodeService;
        $this->fifoPriorityService = $fifoPriorityService;
    }

    /**
     * Liste de toutes les organisations
     * Route: /admin/dossiers
     */
    public function index(Request $request)
    {
        try {
            // Query de base avec les organisations et leurs dossiers
            $query = Organisation::with([
                'user',
                'dossiers' => function ($q) {
                    $q->latest()->take(1); // Dernier dossier seulement
                }
            ])->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('sigle', 'like', "%{$search}%")
                        ->orWhere('numero_recepisse', 'like', "%{$search}%");
                });
            }

            // Filtre par type d'organisation
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filtre par statut
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            // Filtre par province
            if ($request->filled('province')) {
                $query->where('province', $request->province);
            }

            // Filtre par date de création
            if ($request->filled('date_debut')) {
                $query->where('created_at', '>=', $request->date_debut);
            }

            if ($request->filled('date_fin')) {
                $query->where('created_at', '<=', $request->date_fin);
            }

            // Pagination
            $organisations = $query->paginate(20);

            // Enrichir chaque organisation avec des données calculées
            $organisations->getCollection()->transform(function ($organisation) {
                return $this->enrichOrganisationData($organisation);
            });

            // Statistiques pour la vue
            $stats = [
                'total_organisations' => Organisation::count(),
                'par_type' => [
                    'association' => Organisation::where('type', 'association')->count(),
                    'ong' => Organisation::where('type', 'ong')->count(),
                    'parti_politique' => Organisation::where('type', 'parti_politique')->count(),
                    'confession_religieuse' => Organisation::where('type', 'confession_religieuse')->count(),
                ],
                'par_statut' => [
                    'brouillon' => Organisation::where('statut', 'brouillon')->count(),
                    'soumis' => Organisation::where('statut', 'soumis')->count(),
                    'en_validation' => Organisation::where('statut', 'en_validation')->count(),
                    'approuve' => Organisation::where('statut', 'approuve')->count(),
                    'rejete' => Organisation::where('statut', 'rejete')->count(),
                ],
                'nouvelles_semaine' => Organisation::where('created_at', '>=', now()->subWeek())->count(),
                'approuvees_mois' => Organisation::where('statut', 'approuve')
                    ->where('updated_at', '>=', now()->subMonth())->count(),
            ];

            // Listes pour les filtres
            $types = [
                'association' => 'Association',
                'ong' => 'ONG',
                'parti_politique' => 'Parti Politique',
                'confession_religieuse' => 'Confession Religieuse'
            ];

            $statuts = [
                'brouillon' => 'Brouillon',
                'soumis' => 'Soumis',
                'en_validation' => 'En validation',
                'approuve' => 'Approuvé',
                'rejete' => 'Rejeté'
            ];

            $provinces = [
                'Estuaire',
                'Haut-Ogooué',
                'Moyen-Ogooué',
                'Ngounié',
                'Nyanga',
                'Ogooué-Ivindo',
                'Ogooué-Lolo',
                'Ogooué-Maritime',
                'Woleu-Ntem'
            ];

            return view('admin.dossiers.index', compact(
                'organisations',
                'stats',
                'types',
                'statuts',
                'provinces'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur DossierController@index: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors du chargement des organisations.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 📝 CRÉATION DE DOSSIER (ADMIN)
    |--------------------------------------------------------------------------
    */

    /**
     * ========================================
     * CREATE - Formulaire de création de dossier (Admin)
     * ========================================
     * Route: GET /admin/dossiers/create
     */
    public function create()
    {
        try {
            \Log::info('Admin accède au formulaire de création d\'organisation', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Unknown'
            ]);

            // Types d'organisations avec leurs configurations
            $typesOrganisation = OrganisationType::where('is_active', true)
                ->orderBy('ordre')
                ->get();

            // Provinces pour géolocalisation
            $provinces = Province::where('is_active', true)
                ->orderBy('ordre_affichage')
                ->get();

            return view('admin.dossiers.create', compact(
                'typesOrganisation',
                'provinces'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur DossierController@create: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * ========================================
     * STORE - Enregistrer un nouveau dossier (Admin)
     * ========================================
     * Route: POST /admin/dossiers
     */
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validated = $request->validate([
                // Type d'organisation
                'organisation_type_id' => 'required|exists:organisation_types,id',

                // Déclarant
                'demandeur_nip' => 'required|string|max:20',
                'demandeur_nom' => 'required|string|max:100',
                'demandeur_prenom' => 'required|string|max:100',
                'demandeur_email' => 'nullable|email|max:255',
                'demandeur_telephone' => 'required|string|max:20',
                'demandeur_role' => 'nullable|string|max:100',

                // Organisation
                'org_nom' => 'required|string|max:255',
                'org_sigle' => 'nullable|string|max:20',
                'org_objet' => 'required|string',
                'org_date_creation' => 'required|date',
                'org_telephone' => 'required|string|max:20',
                'org_email' => 'nullable|email|max:255',
                'org_site_web' => 'nullable|url|max:255',

                // Géolocalisation
                'org_province_id' => 'required|exists:provinces,id',
                'org_departement_id' => 'required|exists:departements,id',
                'org_commune_id' => 'nullable|exists:communes_villes,id',
                'org_arrondissement_id' => 'nullable|exists:arrondissements,id',
                'org_quartier' => 'nullable|string|max:100',
                'org_adresse' => 'required|string|max:500',
                'org_latitude' => 'nullable|numeric|between:-90,90',
                'org_longitude' => 'nullable|numeric|between:-180,180',

                // Fondateurs
                'fondateurs' => 'required|array|min:1',
                'fondateurs.*.nip' => 'required|string|max:20',
                'fondateurs.*.civilite' => 'required|in:M,Mme,Mlle',
                'fondateurs.*.nom' => 'required|string|max:100',
                'fondateurs.*.prenom' => 'required|string|max:100',
                'fondateurs.*.fonction' => 'required|string|max:100',
                'fondateurs.*.telephone' => 'nullable|string|max:20',
                'fondateurs.*.email' => 'nullable|email|max:255',

                // Adhérents (optionnels selon config)
                'adherents' => 'nullable|array',
                'adherents.*.nip' => 'required_with:adherents|string|max:20',
                'adherents.*.nom' => 'required_with:adherents|string|max:100',
                'adherents.*.prenom' => 'required_with:adherents|string|max:100',
                'adherents.*.telephone' => 'nullable|string|max:20',
                'adherents.*.profession' => 'nullable|string|max:100',

                // Documents
                'documents' => 'nullable|array',
                'documents.*' => 'file|max:10240',
            ]);

            DB::beginTransaction();

            // Récupérer le type d'organisation
            $orgType = OrganisationType::findOrFail($validated['organisation_type_id']);

            // Récupérer les noms des entités géographiques
            $province = Province::find($validated['org_province_id']);
            $departement = Departement::find($validated['org_departement_id']);
            $commune = isset($validated['org_commune_id']) ? CommuneVille::find($validated['org_commune_id']) : null;
            $arrondissement = isset($validated['org_arrondissement_id']) ? Arrondissement::find($validated['org_arrondissement_id']) : null;

            // Créer l'organisation
            $organisation = Organisation::create([
                'user_id' => auth()->id(),
                'organisation_type_id' => $orgType->id,
                'type' => $orgType->code,
                'nom' => $validated['org_nom'],
                'sigle' => $validated['org_sigle'] ?? null,
                'objet' => $validated['org_objet'],
                'siege_social' => $validated['org_adresse'], // Mapping correct vers siege_social
                'province' => $province->nom ?? null,
                'departement' => $departement->nom ?? null,
                'prefecture' => $departement->nom ?? 'Non défini', // Champ obligatoire
                'ville_commune' => $commune->nom ?? null,
                'arrondissement' => $arrondissement->nom ?? null,
                'quartier' => $validated['org_quartier'] ?? null,
                'latitude' => $validated['org_latitude'] ?? null,
                'longitude' => $validated['org_longitude'] ?? null,
                // Références ID pour les jointures
                'province_ref_id' => $validated['org_province_id'],
                'departement_ref_id' => $validated['org_departement_id'],
                'commune_ville_ref_id' => $validated['org_commune_id'] ?? null,
                'arrondissement_ref_id' => $validated['org_arrondissement_id'] ?? null,
                'telephone' => $validated['org_telephone'],
                'email' => $validated['org_email'] ?? null,
                'site_web' => $validated['org_site_web'] ?? null,
                'date_creation' => $validated['org_date_creation'],
                'statut' => 'soumis',
                'is_active' => true,
            ]);

            // Générer numéro de récépissé provisoire
            $numeroRecepisse = $this->generateRecepisseNumberAdmin($orgType->code);
            $organisation->update(['numero_recepisse' => $numeroRecepisse]);

            // Créer le dossier
            $dossier = Dossier::create([
                'organisation_id' => $organisation->id,
                'numero_dossier' => $this->generateNumeroDossierAdmin(),
                'numero_recepisse' => $numeroRecepisse,
                'type_operation' => 'creation',
                'statut' => 'soumis',
                'donnees_supplementaires' => json_encode([
                    'demandeur' => [
                        'nip' => $validated['demandeur_nip'],
                        'nom' => $validated['demandeur_nom'],
                        'prenom' => $validated['demandeur_prenom'],
                        'email' => $validated['demandeur_email'] ?? null,
                        'telephone' => $validated['demandeur_telephone'],
                        'role' => $validated['demandeur_role'] ?? 'Déclarant',
                    ],
                    'geolocalisation' => [
                        'province_id' => $validated['org_province_id'],
                        'departement_id' => $validated['org_departement_id'],
                        'commune_id' => $validated['org_commune_id'] ?? null,
                        'arrondissement_id' => $validated['org_arrondissement_id'] ?? null,
                        'latitude' => $validated['org_latitude'] ?? null,
                        'longitude' => $validated['org_longitude'] ?? null,
                    ],
                    'created_by_admin' => true,
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->name,
                ]),
                'is_active' => true,
            ]);

            // Créer les fondateurs
            foreach ($validated['fondateurs'] as $fondateurData) {
                Fondateur::create([
                    'organisation_id' => $organisation->id,
                    'nip' => $fondateurData['nip'],
                    'civilite' => $fondateurData['civilite'],
                    'nom' => $fondateurData['nom'],
                    'prenom' => $fondateurData['prenom'],
                    'fonction' => $fondateurData['fonction'],
                    'telephone' => $fondateurData['telephone'] ?? null,
                    'email' => $fondateurData['email'] ?? null,
                ]);
            }

            // Créer les membres du bureau
            if (!empty($request->membresBureau)) {
                foreach ($request->membresBureau as $index => $membreData) {
                    \App\Models\MembreBureau::create([
                        'organisation_id' => $organisation->id,
                        'nip' => $membreData['nip'],
                        'nom' => strtoupper($membreData['nom']),
                        'prenom' => $membreData['prenom'],
                        'fonction' => $membreData['fonction'],
                        'contact' => $membreData['contact'] ?? null,
                        'domicile' => $membreData['domicile'] ?? null,
                        'afficher_recepisse' => ($membreData['afficher_recepisse'] ?? '0') === '1',
                        'ordre' => $index + 1,
                    ]);
                }
            }

            // Créer les adhérents si fournis
            if (!empty($validated['adherents'])) {
                foreach ($validated['adherents'] as $adherentData) {
                    Adherent::create([
                        'organisation_id' => $organisation->id,
                        'nip' => $adherentData['nip'],
                        'nom' => $adherentData['nom'],
                        'prenom' => $adherentData['prenom'],
                        'profession' => $adherentData['profession'] ?? null,
                        'telephone' => $adherentData['telephone'] ?? null,
                        'date_adhesion' => now(),
                        'is_active' => true,
                    ]);
                }
            }

            // Traiter les documents uploadés
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $documentTypeId => $file) {
                    $path = $file->store('dossiers/' . $dossier->id, 'public');

                    // Générer le hash du fichier
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
                \Log::warning('Erreur génération QR Code: ' . $e->getMessage());
            }

            DB::commit();

            \Log::info('Dossier créé par admin', [
                'dossier_id' => $dossier->id,
                'dossier_numero' => $dossier->numero_dossier,
                'organisation_id' => $organisation->id,
                'organisation_nom' => $organisation->nom,
                'type' => $orgType->code,
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
            ]);

            return redirect()->route('admin.dossiers.show', $dossier->id)
                ->with('success', 'Dossier créé avec succès. Numéro: ' . $dossier->numero_dossier);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création dossier admin: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur lors de la création: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * API : Récupérer la configuration d'un type d'organisation
     * Route: GET /admin/dossiers/type-config/{id}
     */
    public function getTypeConfig($id)
    {
        try {
            $orgType = OrganisationType::with([
                'documentTypes' => function ($q) {
                    $q->where('is_active', true)->orderBy('ordre');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $orgType->id,
                    'code' => $orgType->code,
                    'nom' => $orgType->nom,
                    'nb_min_fondateurs' => $orgType->nb_min_fondateurs_majeurs ?? 3,
                    'nb_min_adherents' => $orgType->nb_min_adherents_creation ?? 0,
                    'is_lucratif' => $orgType->is_lucratif ?? false,
                    'guide_creation' => $orgType->guide_creation,
                    'loi_reference' => $orgType->loi_reference,
                    'documents_requis' => $orgType->documentTypes->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'nom' => $doc->nom,
                            'code' => $doc->code,
                            'description' => $doc->description,
                            'is_required' => $doc->pivot->is_required ?? true,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Type d\'organisation non trouvé',
            ], 404);
        }
    }

    /**
     * Générer un numéro de dossier unique (Admin)
     */
    private function generateNumeroDossierAdmin(): string
    {
        $prefix = 'DOS';
        $year = date('Y');
        $count = Dossier::whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%s-%05d', $prefix, $year, $count);
    }

    /**
     * Générer un numéro de récépissé provisoire (Admin)
     */
    private function generateRecepisseNumberAdmin(string $type): string
    {
        $prefixes = [
            'association' => 'ASS',
            'ong' => 'ONG',
            'parti_politique' => 'PP',
            'confession_religieuse' => 'CR',
        ];

        $prefix = $prefixes[$type] ?? 'ORG';
        $year = date('Y');
        $count = Organisation::whereYear('created_at', $year)->where('type', $type)->count() + 1;

        return sprintf('%s/%s/%05d', $prefix, $year, $count);
    }

    /*
    |--------------------------------------------------------------------------
    | 📋 DOSSIERS EN ATTENTE
    |--------------------------------------------------------------------------
    */

    /**
     * Page des dossiers en attente - Compatible avec en-attente.blade.php
     */
    public function enAttente(Request $request)
    {
        try {
            // Query de base avec SEULEMENT les relations confirmées
            $query = Dossier::with(['organisation']) // Organisation existe ✅
                ->whereIn('statut', ['soumis', 'en_cours'])
                ->where(function ($q) {
                    $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
                })
                ->orderBy('created_at', 'desc');

            // Application des filtres de recherche
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('numero_dossier', 'like', "%{$search}%")
                        ->orWhereHas('organisation', function ($org) use ($search) {
                            $org->where('nom', 'like', "%{$search}%")
                                ->orWhere('sigle', 'like', "%{$search}%");
                        });
                });
            }

            // Filtre par type d'organisation
            if ($request->filled('type') && $request->type !== '') {
                $query->whereHas('organisation', function ($q) use ($request) {
                    $q->where('type', $request->type);
                });
            }

            // Filtre par priorité calculée
            if ($request->filled('priorite') && $request->priorite !== '') {
                if ($request->priorite === 'haute') {
                    $query->where(function ($q) {
                        $q->where('created_at', '<=', now()->subDays(7))
                            ->orWhereHas('organisation', function ($org) {
                                $org->where('type', 'parti_politique');
                            });
                    });
                } elseif ($request->priorite === 'normale') {
                    $query->where('created_at', '>', now()->subDays(7))
                        ->whereHas('organisation', function ($org) {
                            $org->where('type', '!=', 'parti_politique');
                        });
                }
            }

            // Filtre par période
            if ($request->filled('periode') && $request->periode !== '') {
                switch ($request->periode) {
                    case 'today':
                        $query->whereDate('created_at', today());
                        break;
                    case 'week':
                        $query->where('created_at', '>=', now()->startOfWeek());
                        break;
                    case 'month':
                        $query->where('created_at', '>=', now()->startOfMonth());
                        break;
                }
            }

            // Pagination avec 15 éléments par page
            $dossiersEnAttente = $query->paginate(15);

            // Enrichir chaque dossier avec données métier
            $dossiersEnAttente->getCollection()->transform(function ($dossier) {
                return $this->enrichDossierDataArchitecture($dossier);
            });

            // Calcul des statistiques pour les cards
            $totalEnAttente = Dossier::whereIn('statut', ['soumis', 'en_cours'])
                ->where(function ($q) {
                    $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
                })->count();

            $prioriteHaute = $this->calculateHighPriorityCountArchitecture();
            $delaiMoyen = $this->calculateAverageWaitingTimeArchitecture();

            // Agents disponibles - Utiliser le modèle User correct
            $agents = User::where('role', 'agent')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            // Retour de la vue avec toutes les données
            return view('admin.dossiers.en-attente', compact(
                'dossiersEnAttente',
                'totalEnAttente',
                'prioriteHaute',
                'delaiMoyen',
                'agents'
            ));

        } catch (\Exception $e) {
            // Log détaillé de l'erreur
            \Log::error('Erreur DossierController@enAttente: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_params' => $request->all()
            ]);

            // Retour avec message d'erreur utilisateur
            return back()->with('error', 'Erreur lors du chargement des dossiers en attente. Veuillez réessayer.')
                ->withInput();
        }
    }


    /**
     * Afficher les détails d'un dossier
     */
    public function show(Request $request, $id)
    {
        try {
            // ========== CORRECTION : Relations alignées sur la structure DB ==========
            $dossier = Dossier::with([
                'organisation.fondateurs',
                'organisation.adherents' => function ($query) {
                    $query->take(10);
                },
                'documents.documentType',
                'assignedAgent',
                'validations.validatedBy',      // ✅ Correction : validations au lieu de dossierValidations
                'operations.user'               // ✅ Correction : operations au lieu de dossierComments
            ])->findOrFail($id);

            // Enrichir avec données métier
            $dossier = $this->enrichDossierData($dossier);

            // Historique des actions sur le dossier
            $historique = $this->getDossierHistory($dossier);

            // ========== STATISTIQUES COMME L'ANCIEN CODE ==========
            $stats = [
                'documents_count' => $dossier->documents ? $dossier->documents->count() : 0,
                'comments_count' => $dossier->operations ? $dossier->operations->where('type_operation', 'commentaire')->count() : 0,
                'validations_count' => $dossier->validations ? $dossier->validations->count() : 0,
                'delai_attente' => \Carbon\Carbon::parse($dossier->created_at)->diffInDays(now())
            ];

            // ========== CALCUL DE PRIORITÉ COMME L'ANCIEN CODE ==========
            $isPriority = false;
            $reason = 'Normale';
            if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
                $isPriority = true;
                $reason = 'Parti politique';
            } elseif ($stats['delai_attente'] > 7) {
                $isPriority = true;
                $reason = 'Délai > 7 jours';
            }

            $dossier->priorite_calculee = $isPriority ? 'haute' : 'normale';
            $dossier->raison_priorite = $reason;

            // ========== AGENTS POUR ASSIGNATION ==========
            $agents = User::where('role', 'agent')
                ->orWhere('role', 'admin')
                ->orderBy('name')
                ->get();

            // ========== INFORMATIONS DÉCLARANT DEPUIS JSON ==========
            $declarant = null;
            if (!empty($dossier->donnees_supplementaires)) {
                // donnees_supplementaires est déjà un array grâce au cast du modèle
                $donneesSupplementaires = is_array($dossier->donnees_supplementaires)
                    ? $dossier->donnees_supplementaires
                    : json_decode($dossier->donnees_supplementaires, true);
                $declarant = $donneesSupplementaires['demandeur'] ?? null;
            }

            // ========== ACTIONS DISPONIBLES POUR LES PDF ==========
            $documentsDisponibles = $this->getAvailableActionsUpdated($dossier);

            // ========== LOG AVEC BACKSLASH (comme ancien code) ==========
            \Log::info("Consultation dossier #{$dossier->id}", [
                'user_id' => auth()->id(),
                'dossier_numero' => $dossier->numero_dossier,
                'ip' => request()->ip()
            ]);

            // ========== RETOUR VUE AVEC VARIABLES COMPATIBLES ==========
            return view('admin.dossiers.show', compact(
                'dossier',
                'agents',
                'stats',
                'historique',
                'declarant',
                'documentsDisponibles'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning("Tentative d'accès à un dossier inexistant: {$id}", [
                'user_id' => auth()->id(),
                'ip' => request()->ip()
            ]);

            return redirect()->route('admin.dossiers.en-attente')
                ->with('error', 'Dossier non trouvé.');

        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'affichage du dossier {$id}: " . $e->getMessage(), [
                'user_id' => auth()->id(),
                'exception' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.dossiers.en-attente')
                ->with('error', 'Erreur technique lors de l\'affichage du dossier.');
        }
    }


    /**
     * Télécharger l'accusé de réception PDF
     */
    public function downloadAccuse($id)
    {
        try {
            // CORRECTION : Charger avec organisation.fondateurs
            $dossier = Dossier::with(['organisation.fondateurs'])->findOrFail($id);

            // Vérifier que le dossier a des données supplémentaires JSON
            if (empty($dossier->donnees_supplementaires)) {
                return back()->with('error', 'Impossible de générer l\'accusé : informations du déclarant manquantes.');
            }

            // Générer le PDF d'accusé de réception
            $mpdf = $this->pdfService->generateAccuseReception($dossier);

            // Nom de fichier sécurisé
            $filename = $this->sanitizeFilename("accuse_reception_{$dossier->numero_dossier}") . "_" . now()->format('Ymd') . ".pdf";

            // CORRECTION : donnees_supplementaires est déjà un array
            $donneesSupp = is_array($dossier->donnees_supplementaires)
                ? $dossier->donnees_supplementaires
                : json_decode($dossier->donnees_supplementaires, true);
            $declarant = $donneesSupp['demandeur'] ?? [];
            \Log::info("Génération accusé PDF pour dossier {$dossier->id}", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'declarant_nom' => ($declarant['prenom'] ?? '') . ' ' . ($declarant['nom'] ?? ''),
                'declarant_nip' => $declarant['nip'] ?? 'Non renseigné',
                'user' => auth()->user()->name
            ]);

            // mPDF: Utiliser Output pour le téléchargement
            return response($mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Erreur génération accusé PDF: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de la génération de l\'accusé de réception: ' . $e->getMessage());
        }
    }

    /**
     * Alias pour télécharger l'accusé de réception PDF
     * Route: GET /admin/dossiers/{id}/accuse-reception
     */
    public function downloadAccuseReception($id)
    {
        return $this->downloadAccuse($id);
    }




    /**
     * Télécharger le récépissé final PDF
     */
    public function downloadRecepisse($id)
    {
        try {
            // CORRECTION : Charger avec organisation.fondateurs
            $dossier = Dossier::with(['organisation.fondateurs'])->findOrFail($id);

            // Vérifier que le dossier est approuvé
            if ($dossier->statut !== 'approuve') {
                return back()->with('error', 'Le récépissé définitif n\'est disponible que pour les dossiers approuvés.');
            }

            // Générer le PDF de récépissé
            $pdf = $this->pdfService->generateRecepisseDefinitif($dossier);

            // Nom de fichier sécurisé
            $filename = $this->sanitizeFilename("recepisse_definitif_{$dossier->organisation->nom}_{$dossier->numero_dossier}") . "_" . now()->format('Ymd') . ".pdf";

            // CORRECTION : Log avec backslash
            \Log::info("Génération récépissé définitif PDF pour dossier {$dossier->id}", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'numero_recepisse' => $dossier->numero_recepisse,
                'user' => auth()->user()->name
            ]);

            return \App\Helpers\PdfTemplateHelper::downloadPdf($pdf, $filename);

        } catch (\Exception $e) {
            \Log::error('Erreur génération récépissé définitif PDF: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de la génération du récépissé définitif: ' . $e->getMessage());
        }
    }

    /**
     * Télécharger le récépissé définitif PDF
     * Route: GET /admin/dossiers/{dossier}/recepisse-definitif
     * 
     * @param int|string $id ID du dossier
     * @return \Illuminate\Http\Response
     */
    public function downloadRecepisseDefinitif($id)
    {
        try {
            // Charger le dossier avec ses relations
            $dossier = Dossier::with(['organisation.fondateurs'])->findOrFail($id);

            // Vérifier que le dossier est dans un statut permettant le récépissé définitif
            if (!in_array($dossier->statut, [Dossier::STATUT_ACCEPTE, 'approuve'])) {
                return back()->with('error', 'Le récépissé définitif n\'est disponible que pour les dossiers acceptés/approuvés.');
            }

            // Générer le PDF du récépissé définitif
            $pdf = $this->pdfService->generateRecepisseDefinitif($dossier);

            // Nom de fichier sécurisé
            $filename = $this->sanitizeFilename("recepisse_definitif_{$dossier->organisation->nom}_{$dossier->numero_dossier}") . "_" . now()->format('Ymd') . ".pdf";

            // Log de l'activité
            \Log::info("Téléchargement récépissé définitif pour dossier {$dossier->id}", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'numero_recepisse' => $dossier->numero_recepisse,
                'user' => auth()->user()->name
            ]);

            return \App\Helpers\PdfTemplateHelper::downloadPdf($pdf, $filename);

        } catch (\Exception $e) {
            \Log::error('Erreur téléchargement récépissé définitif: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de la génération du récépissé définitif: ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * MÉTHODE PRINCIPALE : validate() 
     * ========================================
     * Route: POST /admin/dossiers/{id}/validate
     * Cette méthode sera appelée par la modal d'approbation
     */
    public function validateDossier(Request $request, $id)
    {
        try {
            // Validation des données d'entrée
            $request->validate([
                'numero_recepisse_final' => 'required|string|max:100',
                'date_approbation' => 'required|date',
                'validite_mois' => 'nullable|integer|min:1|max:120',
                'commentaire_approbation' => 'nullable|string|max:2000',
                'generer_recepisse' => 'nullable',
                'envoyer_email_approbation' => 'nullable',
                'publier_annuaire' => 'nullable'
            ]);

            DB::beginTransaction();

            $dossier = Dossier::with('organisation', 'assignedAgent')->findOrFail($id);

            // Vérifier que le dossier peut être approuvé
            if (!in_array($dossier->statut, ['en_cours', 'soumis'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce dossier ne peut pas être approuvé dans son état actuel'
                ], 400);
            }

            // Mettre à jour le dossier
            $dossier->update([
                'statut' => 'approuve',
                'approved_at' => $request->date_approbation,
                'approved_by' => auth()->id(),
                'numero_recepisse' => $request->numero_recepisse_final
            ]);

            // Mettre à jour l'organisation
            if ($dossier->organisation) {
                $updateData = [
                    'numero_recepisse' => $request->numero_recepisse_final,
                    'date_approbation' => $request->date_approbation,
                    'statut' => 'approuve',
                    'is_approved' => true
                ];

                if ($request->validite_mois) {
                    $updateData['date_expiration'] = Carbon::parse($request->date_approbation)
                        ->addMonths($request->validite_mois);
                }

                if ($request->publier_annuaire) {
                    $updateData['visible_annuaire'] = true;
                }

                $dossier->organisation->update($updateData);
            }

            // Enregistrer l'opération de validation
            if (method_exists($dossier, 'operations')) {
                $dossier->operations()->create([
                    'type_operation' => 'validation',
                    'user_id' => auth()->id(),
                    'description' => 'Dossier approuvé - Récépissé: ' . $request->numero_recepisse_final,
                    'ancien_statut' => $dossier->getOriginal('statut') ?? 'en_cours',
                    'nouveau_statut' => 'approuve',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            // Ajouter un commentaire d'approbation (optionnel)
            if ($request->filled('commentaire_approbation')) {
                // Vérifier que la relation operations existe
                if (method_exists($dossier, 'operations')) {
                    $dossier->operations()->create([
                        'type_operation' => 'commentaire',
                        'user_id' => auth()->id(),
                        'description' => $request->commentaire_approbation,
                        'ancien_statut' => $dossier->getOriginal('statut'),
                        'nouveau_statut' => 'approuve',
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent()
                    ]);
                }
            }

            // Créer une validation selon le modèle existant
            if (method_exists($dossier, 'validations')) {
                $dossier->validations()->create([
                    'workflow_step_id' => $dossier->current_step_id ?? 1,
                    'validation_entity_id' => 1,
                    'validated_by' => auth()->id(),
                    'decision' => 'approuve',
                    'commentaire' => $request->commentaire_approbation,
                    'numero_enregistrement' => $request->numero_recepisse_final,
                    'decided_at' => now()
                ]);
            }

            // Générer le récépissé PDF si demandé
            if ($request->generer_recepisse && $this->pdfService) {
                try {
                    // UTILISER LA MÉTHODE EXISTANTE generateRecepisseDefinitif
                    $pdf = $this->pdfService->generateRecepisseDefinitif($dossier);

                    // Sauvegarder le document récépissé si la relation documents existe
                    if (method_exists($dossier, 'documents')) {
                        $dossier->documents()->create([
                            'nom_fichier' => 'recepisse_definitif_' . $dossier->numero_dossier . '.pdf',
                            'nom_original' => 'Récépissé Définitif.pdf',
                            'type_document' => 'recepisse_definitif',
                            'taille_fichier' => 0,
                            'is_generated' => true,
                            'uploaded_by' => auth()->id()
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Erreur génération récépissé lors de l\'approbation: ' . $e->getMessage());
                }
            }

            // Envoyer email de confirmation si demandé
            $userEmail = $dossier->organisation->email ?? ($dossier->assignedAgent->email ?? null);
            if ($request->envoyer_email_approbation && $userEmail) {
                try {
                    // TODO: Implémenter l'envoi d'email
                    \Log::info('Email d\'approbation à envoyer à: ' . $userEmail);
                } catch (\Exception $e) {
                    \Log::warning('Erreur envoi email d\'approbation: ' . $e->getMessage());
                }
            }

            // Log de l'approbation avec le style existant
            \Log::info("Dossier {$dossier->id} approuvé", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'numero_recepisse' => $request->numero_recepisse_final,
                'approved_by' => auth()->user()->name,
                'date_approbation' => $request->date_approbation
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dossier approuvé avec succès',
                'data' => [
                    'dossier_id' => $dossier->id,
                    'nouveau_statut' => 'approuve',
                    'numero_recepisse' => $request->numero_recepisse_final,
                    'date_approbation' => $request->date_approbation,
                    'recepisse_genere' => $request->generer_recepisse
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de l\'approbation du dossier: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation: ' . $e->getMessage()
            ], 500);
        }
    }




    /**
     * ========================================
     * CORRECTION DE LA MÉTHODE EXISTANTE : valider()
     * ========================================
     * Garder cette méthode mais corriger les erreurs
     */
    public function valider(Request $request, $id)
    {
        try {
            // CORRECTION : Utiliser validate() au lieu de approuver()
            $request->validate([
                'numero_enregistrement' => 'nullable|string|max:100',
                'commentaire' => 'nullable|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);

            DB::transaction(function () use ($dossier, $request) {
                // Mettre à jour le dossier
                $dossier->update([
                    'statut' => 'approuve',
                    'validated_at' => now(),
                    'numero_recepisse' => $request->numero_enregistrement ?: $this->generateRecepisseNumber($dossier)
                ]);

                // Mettre à jour l'organisation
                if ($dossier->organisation) {
                    $dossier->organisation->update([
                        'statut' => 'approuve',
                        'numero_recepisse' => $dossier->numero_recepisse
                    ]);
                }

                // CORRECTION : Gérer les relations optionnelles
                if (class_exists('App\Models\DossierValidation')) {
                    DossierValidation::updateOrCreate([
                        'dossier_id' => $dossier->id,
                    ], [
                        'workflow_step_id' => 1,
                        'validation_entity_id' => 1,
                        'validated_by' => auth()->id(),
                        'decision' => 'approuve',
                        'commentaire' => $request->commentaire,
                        'numero_enregistrement' => $request->numero_enregistrement,
                        'decided_at' => now(),
                        'duree_traitement' => $dossier->created_at->diffInMinutes(now())
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Dossier validé avec succès',
                'recepisse_number' => $dossier->fresh()->numero_recepisse
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur validation dossier: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ========================================
     * CORRECTION DE LA MÉTHODE : rejeter()
     * ========================================
     */
    public function rejeter(Request $request, $id)
    {
        try {
            // CORRECTION : Utiliser validate() au lieu de approuver()
            $request->validate([
                'motif' => 'required|string|max:1000',
                'commentaire' => 'required|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);

            DB::transaction(function () use ($dossier, $request) {
                $dossier->update([
                    'statut' => 'rejete',
                    'motif_rejet' => $request->commentaire,
                    'validated_at' => now()
                ]);

                // CORRECTION : Gérer les relations optionnelles
                if (class_exists('App\Models\DossierValidation')) {
                    DossierValidation::updateOrCreate([
                        'dossier_id' => $dossier->id,
                    ], [
                        'workflow_step_id' => 1,
                        'validation_entity_id' => 1,
                        'validated_by' => auth()->id(),
                        'decision' => 'rejete',
                        'motif_rejet' => $request->motif,
                        'commentaire' => $request->commentaire,
                        'decided_at' => now(),
                        'duree_traitement' => $dossier->created_at->diffInMinutes(now())
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Dossier rejeté avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur rejet dossier: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ========================================
     * CORRECTION DE LA MÉTHODE : attribuer()
     * ========================================
     */
    public function attribuer(Request $request, $id)
    {
        try {
            // CORRECTION : Utiliser validate() au lieu de approuver()
            $request->validate([
                'agent_id' => 'required|exists:users,id',
                'priorite' => 'nullable|in:normale,moyenne,haute',
                'commentaire' => 'nullable|string|max:500'
            ]);

            $dossier = Dossier::findOrFail($id);
            $agent = User::findOrFail($request->agent_id);

            $dossier->update([
                'assigned_to' => $agent->id,
                'statut' => 'en_cours'
            ]);

            // Enregistrer l'opération d'assignation (pour tracer la date)
            if (method_exists($dossier, 'operations')) {
                $dossier->operations()->create([
                    'type_operation' => 'assignation',
                    'user_id' => auth()->id(),
                    'description' => "Dossier assigné à {$agent->name}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Dossier assigné à {$agent->name} avec succès"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTHODES PRIVÉES ==========

    /**
     * Enrichir un dossier avec des données métier calculées
     */
    private function enrichDossierData($dossier)
    {
        // Jours d'attente
        $dossier->jours_attente = now()->diffInDays($dossier->created_at);

        // Calcul de priorité
        $priorite = $this->calculatePriorite($dossier);
        $dossier->priorite = $priorite['niveau'];
        $dossier->priorite_color = $priorite['color'];

        // Progression du workflow
        $dossier->progression = $this->calculateProgression($dossier);

        // Actions disponibles
        $dossier->actions_disponibles = $this->getAvailableActions($dossier);

        return $dossier;
    }

    /**
     * Calculer la priorité d'un dossier
     */
    private function calculatePriorite($dossier)
    {
        $joursAttente = now()->diffInDays($dossier->created_at);

        // Parti politique = priorité haute automatique
        if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
            return ['niveau' => 'haute', 'color' => 'danger'];
        }

        // Basé sur ancienneté
        if ($joursAttente > 10) {
            return ['niveau' => 'haute', 'color' => 'danger'];
        } elseif ($joursAttente > 5) {
            return ['niveau' => 'moyenne', 'color' => 'warning'];
        } else {
            return ['niveau' => 'normale', 'color' => 'success'];
        }
    }

    /**
     * Calculer le pourcentage de progression
     */
    private function calculateProgression($dossier)
    {
        switch ($dossier->statut) {
            case 'brouillon':
                return 10;
            case 'soumis':
                return 30;
            case 'en_cours':
                return 60;
            case 'approuve':
                return 100;
            case 'rejete':
                return 100;
            default:
                return 0;
        }
    }

    /**
     * Actions disponibles selon statut
     */
    private function getAvailableActions($dossier)
    {
        switch ($dossier->statut) {
            case 'soumis':
                return ['assigner', 'valider', 'rejeter'];
            case 'en_cours':
                return ['valider', 'rejeter', 'reassigner'];
            case 'approuve':
                return ['consulter', 'download_recepisse'];
            case 'rejete':
                return ['consulter'];
            default:
                return [];
        }
    }

    /**
     * Calculer nombre de dossiers haute priorité
     */
    private function calculateHighPriorityCount()
    {
        return Dossier::whereIn('statut', ['soumis', 'en_cours'])
            ->where(function ($q) {
                $q->where('created_at', '<=', now()->subDays(7))
                    ->orWhereHas('organisation', function ($org) {
                        $org->where('type', 'parti_politique');
                    });
            })->count();
    }

    /**
     * Calculer temps d'attente moyen
     */
    private function calculateAverageWaitingTime()
    {
        $dossiers = Dossier::whereIn('statut', ['soumis', 'en_cours'])->get();

        if ($dossiers->isEmpty()) {
            return 0;
        }

        $totalJours = $dossiers->sum(function ($dossier) {
            return now()->diffInDays($dossier->created_at);
        });

        return round($totalJours / $dossiers->count(), 1);
    }

    /**
     * Obtenir l'historique d'un dossier
     */
    private function getDossierHistory($dossier)
    {
        // Pour l'instant, retourner un historique simulé
        // À terme, utiliser une table d'audit ou dossier_operations
        return collect([
            [
                'date' => $dossier->created_at,
                'action' => 'Création du dossier',
                'utilisateur' => $dossier->organisation->user->name ?? 'Système',
                'details' => 'Dossier soumis pour validation'
            ]
        ]);
    }

    /**
     * Documents disponibles pour téléchargement
     */
    private function getAvailableDocuments($dossier)
    {
        $documents = [];

        // Accusé de réception toujours disponible
        $documents[] = [
            'type' => 'accuse',
            'nom' => 'Accusé de réception',
            'url' => route('admin.dossiers.download-accuse', $dossier->id),
            'icon' => 'fas fa-file-alt'
        ];

        // Récépissé seulement si approuvé
        if ($dossier->statut === 'approuve') {
            $documents[] = [
                'type' => 'recepisse',
                'nom' => 'Récépissé de création',
                'url' => route('admin.dossiers.download-recepisse', $dossier->id),
                'icon' => 'fas fa-certificate'
            ];
        }

        return $documents;
    }

    /**
     * Statistiques du dossier
     */
    private function getDossierStats($dossier)
    {
        return [
            'jours_ecoules' => now()->diffInDays($dossier->created_at),
            'nb_documents' => $dossier->documents ? $dossier->documents->count() : 0,
            'nb_adherents' => $dossier->organisation && $dossier->organisation->adherents ?
                $dossier->organisation->adherents->count() : 0,
            'progression' => $this->calculateProgression($dossier)
        ];
    }

    /**
     * Générer numéro de récépissé unique
     */
    private function generateRecepisseNumber($dossier)
    {
        $type = $dossier->organisation ? substr($dossier->organisation->type, 0, 3) : 'ORG';
        $year = now()->year;
        $sequence = str_pad(Dossier::where('statut', 'approuve')->count() + 1, 4, '0', STR_PAD_LEFT);

        return strtoupper($type) . '-' . $year . '-' . $sequence;
    }

    /**
     * Générer PDF accusé de réception (placeholder)
     */

    private function generateAccusePDF($dossier)
    {
        try {
            return $this->pdfService->generateAccuseReception($dossier);
        } catch (\Exception $e) {
            \Log::error('Erreur génération accusé PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération de l\'accusé de réception: ' . $e->getMessage());
        }
    }

    /**
     * Générer PDF récépissé
     */
    private function generateRecepissePDF($dossier)
    {
        try {
            return $this->pdfService->generateRecepisseDefinitif($dossier);
        } catch (\Exception $e) {
            \Log::error('Erreur génération récépissé PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération du récépissé: ' . $e->getMessage());
        }
    }

    /**
     * ======================================
     * MÉTHODES UTILITAIRES AJOUTÉES
     * ======================================
     */

    /**
     * Enrichit un dossier avec des données métier calculées
     */


    /**
     * Calcule la priorité d'un dossier
     */
    private function calculatePriority($dossier)
    {
        // Priorité haute si :
        // - Parti politique (toujours prioritaire)
        // - Dossier en attente depuis plus de 7 jours
        if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
            return 'haute';
        }

        $delai = Carbon::parse($dossier->created_at)->diffInDays(now());
        return $delai > 7 ? 'haute' : 'normale';
    }

    /**
     * Compte le nombre de dossiers à priorité haute
     */


    /**
     * Calcule le délai moyen d'attente
     */


    /**
     * ======================================
     * AUTRES MÉTHODES NÉCESSAIRES
     * ======================================
     */

    /**
     * ========================================
     * CORRECTION DE LA MÉTHODE : assign()
     * ========================================
     */
    /**
     * Assigner un dossier - Version simplifiée compatible
     */
    public function assign(Request $request, $id)
    {
        try {
            $request->validate([
                'agent_id' => 'required|exists:users,id',
                'commentaire' => 'nullable|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);
            $agent = User::findOrFail($request->agent_id);

            // Vérifier que l'agent est actif
            if (!($agent->is_active ?? true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'agent sélectionné n\'est pas actif'
                ], 400);
            }

            // Assignation simple
            $dossier->update([
                'assigned_to' => $agent->id,
                'statut' => 'en_cours'
            ]);

            // Enregistrer l'opération d'assignation (pour tracer la date)
            if (method_exists($dossier, 'operations')) {
                $dossier->operations()->create([
                    'type_operation' => 'assignation',
                    'user_id' => auth()->id(),
                    'description' => "Dossier assigné à {$agent->name}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            // Ajouter un commentaire si fourni ET si la relation existe
            if ($request->filled('commentaire') && method_exists($dossier, 'operations')) {
                $dossier->operations()->create([
                    'type_operation' => 'commentaire',
                    'user_id' => auth()->id(),
                    'description' => $request->commentaire,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            // Log simple
            \Log::info("Dossier {$dossier->id} assigné à {$agent->name}", [
                'dossier_numero' => $dossier->numero_dossier,
                'agent_id' => $agent->id,
                'assigned_by' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => "Dossier assigné à {$agent->name} avec succès",
                'data' => [
                    'agent_name' => $agent->name,
                    'assigned_at' => now()->format('d/m/Y à H:i'),
                    'statut' => 'en_cours'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur assignation dossier: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'agent_id' => $request->agent_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Calculer la position d'un dossier dans la queue FIFO
     */
    public function calculatePosition(Request $request)
    {
        try {
            $request->validate([
                'priority' => 'required|in:normale,moyenne,haute,urgente',
                'dossier_id' => 'required|exists:dossiers,id'
            ]);

            $dossier = Dossier::findOrFail($request->dossier_id);

            // Calcul simple de position basé sur la priorité
            $basePosition = Dossier::whereIn('statut', ['soumis', 'en_cours'])
                ->where('id', '<', $dossier->id)
                ->count();

            // Ajustement selon priorité
            // ✅ COMPATIBLE PHP 7.3
            switch ($request->priority) {
                case 'urgente':
                    $priorityAdjustment = -10;
                    break;
                case 'haute':
                    $priorityAdjustment = -5;
                    break;
                case 'moyenne':
                    $priorityAdjustment = 0;
                    break;
                case 'normale':
                    $priorityAdjustment = 2;
                    break;
                default:
                    $priorityAdjustment = 0;
                    break;
            }

            $estimatedPosition = max(1, $basePosition + $priorityAdjustment);

            return response()->json([
                'success' => true,
                'position' => $estimatedPosition,
                'priority' => $request->priority
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur calculatePosition: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul de position'
            ], 500);
        }
    }

    /**
     * ✅ OBTENIR L'APERÇU DE LA QUEUE FIFO
     */
    public function queuePreview(Request $request)
    {
        try {
            $statut = $request->get('statut', 'soumis');
            $limit = $request->get('limit', 10);

            $queue = $this->fifoPriorityService->getOrderedQueue($statut, $limit);
            $stats = $this->fifoPriorityService->getQueueStatistics($statut);

            return response()->json([
                'success' => true,
                'queue' => $queue,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur chargement queue preview', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la queue'
            ], 500);
        }
    }

    /**
     * ✅ RÉORGANISER MANUELLEMENT LA QUEUE
     */
    public function reorganizeQueue(Request $request, string $statut)
    {
        try {
            // Vérifier les permissions
            if (!in_array(auth()->user()->role, ['admin', 'superviseur'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permissions insuffisantes'
                ], 403);
            }

            $this->fifoPriorityService->reorganizeQueue($statut);

            return response()->json([
                'success' => true,
                'message' => "Queue du statut '{$statut}' réorganisée avec succès"
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur réorganisation queue', [
                'statut' => $statut,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation'
            ], 500);
        }
    }

    /**
     * ✅ HISTORIQUE DES CHANGEMENTS DE PRIORITÉ
     */
    public function priorityHistory(Dossier $dossier)
    {
        try {
            $history = $this->fifoPriorityService->getPriorityHistory($dossier);

            return response()->json([
                'success' => true,
                'history' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de l\'historique'
            ], 500);
        }
    }

    /**
     * ✅ STATISTIQUES GLOBALES FIFO
     */
    public function fifoStatistics()
    {
        try {
            $allStats = [];

            foreach (['soumis', 'en_cours', 'en_attente'] as $statut) {
                $allStats[$statut] = $this->fifoPriorityService->getQueueStatistics($statut);
            }

            return response()->json([
                'success' => true,
                'statistics' => $allStats,
                'global' => [
                    'total_in_queue' => array_sum(array_column($allStats, 'total')),
                    'total_urgent' => array_sum(array_column($allStats, 'urgents')),
                    'average_delay' => round(array_sum(array_column($allStats, 'delai_moyen_jours')) / count($allStats), 1)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques'
            ], 500);
        }
    }

    /**
     * ========================================
     * CORRECTION DE LA MÉTHODE : addComment()
     * ========================================
     */
    public function addComment(Request $request, $id)
    {
        try {
            // CORRECTION : Utiliser validate() au lieu de approuver()
            $request->validate([
                'comment_text' => 'required|string|max:1000'
            ]);

            $dossier = Dossier::findOrFail($id);

            // CORRECTION : Vérifier que la relation operations existe
            if (method_exists($dossier, 'operations')) {
                $comment = $dossier->operations()->create([
                    'type_operation' => 'commentaire',
                    'user_id' => auth()->id(),
                    'description' => $request->comment_text,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Commentaire ajouté avec succès'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Fonctionnalité de commentaires non disponible'
                ], 501);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur DossierController@addComment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du commentaire'
            ], 500);
        }
    }


    // ===============================================
// MÉTHODES À AJOUTER AU DossierController
// ===============================================

    /**
     * Valide et approuve un dossier
     * Route: POST /admin/dossiers/{id}/validate
     */
    public function approuver(Request $request, $id)
    {
        try {
            $request->validate([
                'numero_recepisse_final' => 'required|string|max:100|unique:organisations,numero_recepisse,' . $id,
                'date_approbation' => 'required|date',
                'validite_mois' => 'nullable|integer|min:1|max:120',
                'commentaire_approbation' => 'nullable|string|max:2000',
                'generer_recepisse' => 'boolean',
                'envoyer_email_approbation' => 'boolean',
                'publier_annuaire' => 'boolean'
            ]);

            DB::beginTransaction();

            $dossier = Dossier::with('organisation')->findOrFail($id);

            // Vérifier que le dossier peut être approuvé
            if (!in_array($dossier->statut, ['en_cours', 'soumis'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce dossier ne peut pas être approuvé dans son état actuel'
                ], 400);
            }

            // Mettre à jour le dossier
            $dossier->update([
                'statut' => 'approuve',
                'approved_at' => $request->date_approbation,
                'approved_by' => auth()->id()
            ]);

            // Mettre à jour l'organisation
            if ($dossier->organisation) {
                $updateData = [
                    'numero_recepisse' => $request->numero_recepisse_final,
                    'date_approbation' => $request->date_approbation,
                    'is_approved' => true
                ];

                if ($request->validite_mois) {
                    $updateData['date_expiration'] = Carbon::parse($request->date_approbation)
                        ->addMonths($request->validite_mois);
                }

                if ($request->publier_annuaire) {
                    $updateData['visible_annuaire'] = true;
                }

                $dossier->organisation->update($updateData);
            }

            // Ajouter un commentaire d'approbation
            if ($request->filled('commentaire_approbation')) {
                $dossier->operations()->create([
                    'type_operation' => 'commentaire',
                    'user_id' => auth()->id(),
                    'description' => $request->commentaire_approbation,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }

            // Créer une validation officielle
            $dossier->validations()->create([
                'user_id' => auth()->id(),
                'type_validation' => 'approbation',
                'statut' => 'approuve',
                'commentaire' => $request->commentaire_approbation,
                'date_validation' => $request->date_approbation,
                'numero_recepisse' => $request->numero_recepisse_final
            ]);

            // Générer le récépissé PDF si demandé
            if ($request->generer_recepisse && $this->pdfService) {
                try {
                    $recepisseUrl = $this->pdfService->generateRecepisse($dossier);

                    // Sauvegarder le document récépissé
                    $dossier->documents()->create([
                        'nom_fichier' => 'recepisse_' . $dossier->numero_dossier . '.pdf',
                        'nom_original' => 'Récépissé Officiel.pdf',
                        'type_document' => 'recepisse',
                        'chemin_fichier' => $recepisseUrl,
                        'taille_fichier' => 0, // À calculer si nécessaire
                        'is_generated' => true
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Erreur génération récépissé: ' . $e->getMessage());
                }
            }

            // Envoyer notification email si demandé
            $emailUser = $dossier->organisation->email ?? ($dossier->assignedAgent->email ?? null);
            if ($request->envoyer_email_approbation && $emailUser) {
                try {
                    // TODO: Implémenter l'envoi d'email avec Mailable
                    \Log::info('Email d\'approbation à envoyer à: ' . $emailUser);
                } catch (\Exception $e) {
                    \Log::warning('Erreur envoi email: ' . $e->getMessage());
                }
            }

            // Log de l'activité
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties([
                    'numero_recepisse' => $request->numero_recepisse_final,
                    'date_approbation' => $request->date_approbation,
                    'validite_mois' => $request->validite_mois
                ])
                ->log('Dossier approuvé');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dossier approuvé avec succès',
                'numero_recepisse' => $request->numero_recepisse_final
            ]);

        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erreur DossierController@validate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation'
            ], 500);
        }
    }

    /**
     * Rejette un dossier
     * Route: POST /admin/dossiers/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        try {
            $request->validate([
                'motif_rejet' => 'required|string|max:100',
                'justification_rejet' => 'required|string|max:2000',
                'recommandations' => 'nullable|string|max:1000',
                'possibilite_recours' => 'required|in:oui,oui_avec_delai,non',
                'delai_recours' => 'nullable|integer|min:0|max:365',
                'envoyer_email_rejet' => 'boolean',
                'generer_lettre_rejet' => 'boolean',
                'archiver_dossier' => 'boolean'
            ]);

            DB::beginTransaction();

            $dossier = Dossier::with('organisation', 'assignedAgent')->findOrFail($id);

            // Vérifier que le dossier peut être rejeté
            if (in_array($dossier->statut, ['approuve', 'rejete'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce dossier ne peut pas être rejeté dans son état actuel'
                ], 400);
            }

            // Mettre à jour le dossier
            $dossier->update([
                'statut' => 'rejete',
                'rejected_at' => now(),
                'rejected_by' => auth()->id()
            ]);

            // Créer une validation de rejet
            $dossier->validations()->create([
                'user_id' => auth()->id(),
                'type_validation' => 'rejet',
                'statut' => 'rejete',
                'motif' => $request->motif_rejet,
                'commentaire' => $request->justification_rejet,
                'recommandations' => $request->recommandations,
                'possibilite_recours' => $request->possibilite_recours,
                'delai_recours_jours' => $request->delai_recours,
                'date_validation' => now()
            ]);

            // Ajouter un commentaire de rejet
            $commentaireRejet = "**Dossier rejeté**\n\n";
            $commentaireRejet .= "**Motif:** " . $request->motif_rejet . "\n\n";
            $commentaireRejet .= "**Justification:** " . $request->justification_rejet;

            if ($request->filled('recommandations')) {
                $commentaireRejet .= "\n\n**Recommandations:** " . $request->recommandations;
            }

            $dossier->operations()->create([
                'type_operation' => 'rejet',
                'user_id' => auth()->id(),
                'description' => $commentaireRejet,
                'ancien_statut' => $dossier->getOriginal('statut') ?? 'en_cours',
                'nouveau_statut' => 'rejete',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Générer la lettre de rejet si demandé
            if ($request->generer_lettre_rejet && $this->pdfService) {
                try {
                    $lettreUrl = $this->pdfService->generateLettreRejet($dossier, [
                        'motif' => $request->motif_rejet,
                        'justification' => $request->justification_rejet,
                        'recommandations' => $request->recommandations,
                        'possibilite_recours' => $request->possibilite_recours,
                        'delai_recours' => $request->delai_recours
                    ]);

                    $dossier->documents()->create([
                        'nom_fichier' => 'lettre_rejet_' . $dossier->numero_dossier . '.pdf',
                        'nom_original' => 'Lettre de Rejet Officielle.pdf',
                        'type_document' => 'lettre_rejet',
                        'chemin_fichier' => $lettreUrl,
                        'is_generated' => true
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Erreur génération lettre rejet: ' . $e->getMessage());
                }
            }

            // Envoyer notification email si demandé
            $emailRejet = $dossier->organisation->email ?? ($dossier->assignedAgent->email ?? null);
            if ($request->envoyer_email_rejet && $emailRejet) {
                try {
                    // TODO: Implémenter l'envoi d'email de rejet
                    \Log::info('Email de rejet à envoyer à: ' . $emailRejet);
                } catch (\Exception $e) {
                    \Log::warning('Erreur envoi email rejet: ' . $e->getMessage());
                }
            }

            // Archiver si demandé
            if ($request->archiver_dossier) {
                $dossier->archives()->create([
                    'archived_by' => auth()->id(),
                    'archived_at' => now(),
                    'motif_archivage' => 'Archivage automatique après rejet',
                    'type_archive' => 'rejet'
                ]);
            }

            // Log de l'activité
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties([
                    'motif' => $request->motif_rejet,
                    'possibilite_recours' => $request->possibilite_recours
                ])
                ->log('Dossier rejeté');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dossier rejeté avec succès'
            ]);

        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erreur DossierController@reject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet'
            ], 500);
        }
    }

    /**
     * Demande des modifications à un dossier
     * Route: POST /admin/dossiers/{id}/request-modification
     */
    public function requestModification(Request $request, $id)
    {
        try {
            $request->validate([
                'modifications' => 'required|array|min:1',
                'modifications.*' => 'string|max:100',
                'details_modifications' => 'required|string|max:2000',
                'delai_modification' => 'required|integer|min:1|max:365',
                'priorite_modification' => 'required|in:normale,haute,basse',
                'envoyer_email_modification' => 'boolean',
                'suspendre_traitement' => 'boolean',
                'rappel_automatique' => 'boolean'
            ]);

            DB::beginTransaction();

            $dossier = Dossier::with('organisation', 'assignedAgent')->findOrFail($id);

            // Vérifier que le dossier peut être modifié
            if (in_array($dossier->statut, ['approuve', 'rejete'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce dossier ne peut plus être modifié'
                ], 400);
            }

            // Mettre à jour le statut si suspension demandée
            if ($request->suspendre_traitement) {
                $dossier->update([
                    'statut' => 'en_attente_modification',
                    'modification_requested_at' => now(),
                    'modification_deadline' => now()->addDays($request->delai_modification)
                ]);
            }

            // Créer l'enregistrement de demande de modification
            $dossier->modifications()->create([
                'user_id' => auth()->id(),
                'type_modifications' => $request->modifications,
                'details' => $request->details_modifications,
                'delai_jours' => $request->delai_modification,
                'priorite' => $request->priorite_modification,
                'date_limite' => now()->addDays($request->delai_modification),
                'statut' => 'en_attente',
                'email_envoye' => $request->envoyer_email_modification,
                'rappels_actives' => $request->rappel_automatique
            ]);

            // Ajouter un commentaire détaillé
            $commentaireModification = "**Modifications demandées**\n\n";
            $commentaireModification .= "**Types de modifications:**\n";
            foreach ($request->modifications as $modification) {
                $commentaireModification .= "- " . ucfirst(str_replace('_', ' ', $modification)) . "\n";
            }
            $commentaireModification .= "\n**Détails:** " . $request->details_modifications;
            $commentaireModification .= "\n\n**Délai accordé:** " . $request->delai_modification . " jour(s)";
            $commentaireModification .= "\n**Date limite:** " . now()->addDays($request->delai_modification)->format('d/m/Y');

            $dossier->operations()->create([
                'type_operation' => 'retour_pour_correction',
                'user_id' => auth()->id(),
                'description' => $commentaireModification,
                'ancien_statut' => $dossier->getOriginal('statut') ?? 'en_cours',
                'nouveau_statut' => 'retour_modification',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Envoyer notification email si demandé
            $userEmail = $dossier->organisation->email ?? ($dossier->assignedAgent->email ?? null);
            if ($request->envoyer_email_modification && $userEmail) {
                try {
                    // TODO: Implémenter l'envoi d'email de demande de modification
                    \Log::info('Email de demande modification à envoyer à: ' . $userEmail);
                } catch (\Exception $e) {
                    \Log::warning('Erreur envoi email modification: ' . $e->getMessage());
                }
            }

            // Programmer les rappels automatiques si activés
            if ($request->rappel_automatique) {
                // TODO: Programmer les tâches de rappel
                \Log::info('Rappels automatiques programmés pour le dossier: ' . $dossier->numero_dossier);
            }

            // Log de l'activité
            activity()
                ->performedOn($dossier)
                ->causedBy(auth()->user())
                ->withProperties([
                    'modifications' => $request->modifications,
                    'delai_jours' => $request->delai_modification,
                    'priorite' => $request->priorite_modification
                ])
                ->log('Modifications demandées');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande de modification envoyée avec succès',
                'date_limite' => now()->addDays($request->delai_modification)->format('d/m/Y')
            ]);

        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erreur DossierController@requestModification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la demande de modification'
            ], 500);
        }
    }

    /**
     * Télécharge un document du dossier
     * Route: GET /admin/dossiers/{id}/documents/{documentId}/download
     */
    public function downloadDocument($dossierId, $documentId)
    {
        try {
            $dossier = Dossier::findOrFail($dossierId);
            $document = $dossier->documents()->findOrFail($documentId);

            $cheminComplet = storage_path('app/' . $document->chemin_fichier);

            if (!file_exists($cheminComplet)) {
                return response()->json(['error' => 'Fichier introuvable'], 404);
            }

            // Log de l'activité de téléchargement
            activity()
                ->performedOn($document)
                ->causedBy(auth()->user())
                ->log('Document téléchargé');

            return response()->download($cheminComplet, $document->nom_original);

        } catch (\Exception $e) {
            \Log::error('Erreur téléchargement document: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du téléchargement'], 500);
        }
    }

    /**
     * Prévisualise un document du dossier
     * Route: GET /admin/dossiers/{id}/documents/{documentId}/preview
     */
    public function previewDocument($dossierId, $documentId)
    {
        try {
            $dossier = Dossier::findOrFail($dossierId);
            $document = $dossier->documents()->findOrFail($documentId);

            $cheminComplet = storage_path('app/' . $document->chemin_fichier);

            if (!file_exists($cheminComplet)) {
                abort(404, 'Fichier introuvable');
            }

            // Log de l'activité de prévisualisation
            activity()
                ->performedOn($document)
                ->causedBy(auth()->user())
                ->log('Document prévisualisé');

            return response()->file($cheminComplet, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->nom_original . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur prévisualisation document: ' . $e->getMessage());
            abort(500, 'Erreur lors de la prévisualisation');
        }
    }

    /**
     * Génère un PDF du dossier complet
     * Route: GET /admin/dossiers/{id}/pdf
     */
    public function generatePDF($id)
    {
        try {
            $dossier = Dossier::with([
                'organisation',
                'user',
                'documents',
                'operations.user',
                'validations.user'
            ])->findOrFail($id);

            if ($this->pdfService) {
                $pdfPath = $this->pdfService->generateDossierComplet($dossier);
                return response()->download($pdfPath, 'dossier_' . $dossier->numero_dossier . '.pdf');
            }

            return response()->json(['error' => 'Service PDF non disponible'], 503);

        } catch (\Exception $e) {
            \Log::error('Erreur génération PDF dossier: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la génération PDF'], 500);
        }
    }

    /**
     * Obtient l'historique complet d'un dossier
     * Route: GET /admin/dossiers/{id}/history
     */
    public function history($id)
    {
        try {
            $dossier = Dossier::with([
                'operations.user',
                'validations.user',
                'modifications.user'
            ])->findOrFail($id);

            // Combiner tous les événements avec timestamps
            $events = collect();

            // Ajouter les commentaires
            foreach ($dossier->operations->where('type_operation', 'commentaire') as $comment) {
                $events->push([
                    'type' => 'comment',
                    'date' => $comment->created_at,
                    'user' => $comment->user->name ?? 'Système',
                    'action' => ucfirst($comment->type),
                    'details' => $comment->contenu,
                    'icon' => 'comment',
                    'color' => 'info'
                ]);
            }

            // Ajouter les validations
            foreach ($dossier->validations as $validation) {
                $events->push([
                    'type' => 'validation',
                    'date' => $validation->created_at,
                    'user' => $validation->user->name ?? 'Système',
                    'action' => ucfirst($validation->type_validation),
                    'details' => $validation->commentaire,
                    'icon' => $validation->statut === 'approuve' ? 'check' : 'times',
                    'color' => $validation->statut === 'approuve' ? 'success' : 'danger'
                ]);
            }

            // Trier par date décroissante
            $events = $events->sortByDesc('date')->values();

            return response()->json([
                'success' => true,
                'events' => $events
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur historique dossier: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du chargement de l\'historique'], 500);
        }
    }

    /**
     * Enrichit un dossier selon l'architecture SGLP réelle
     */
    private function enrichDossierDataArchitecture($dossier)
    {
        // Calcul de la priorité
        $dossier->priorite_calculee = $this->calculatePriorityArchitecture($dossier);

        // Calcul du délai d'attente
        $dossier->delai_attente = Carbon::parse($dossier->created_at)->diffInDays(now());

        // Indicateur de retard
        $dossier->en_retard = $dossier->delai_attente > 7;

        // Nombre de documents - Compter directement depuis la DB
        $dossier->nb_documents = DB::table('documents')
            ->where('dossier_id', $dossier->id)
            ->count();

        // Accès à l'utilisateur via l'organisation (architecture SGLP)
        if ($dossier->organisation && $dossier->organisation->user_id) {
            $dossier->user_organisation = User::find($dossier->organisation->user_id);
        }

        return $dossier;
    }

    /**
     * Calcule la priorité selon l'architecture SGLP
     */
    private function calculatePriorityArchitecture($dossier)
    {
        // Priorité haute si :
        // - Parti politique (toujours prioritaire)
        // - Dossier en attente depuis plus de 7 jours
        if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
            return 'haute';
        }

        $delai = Carbon::parse($dossier->created_at)->diffInDays(now());
        return $delai > 7 ? 'haute' : 'normale';
    }

    /**
     * Compte le nombre de dossiers à priorité haute
     */
    private function calculateHighPriorityCountArchitecture()
    {
        return Dossier::whereIn('statut', ['soumis', 'en_cours'])
            ->where(function ($q) {
                $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
            })
            ->where(function ($q) {
                $q->where('created_at', '<=', now()->subDays(7))
                    ->orWhereHas('organisation', function ($org) {
                        $org->where('type', 'parti_politique');
                    });
            })
            ->count();
    }

    /**
     * Calcule le délai moyen d'attente
     */
    /**
     * Calcule le délai moyen d'attente
     */
    private function calculateAverageWaitingTimeArchitecture()
    {
        $dossiers = Dossier::whereIn('statut', ['soumis', 'en_cours'])
            ->where(function ($q) {
                $q->whereNull('assigned_to')->orWhere('statut', 'soumis');
            })
            ->select('id', 'created_at')
            ->get();

        if ($dossiers->isEmpty()) {
            return 0;
        }

        $totalDelai = $dossiers->sum(function ($dossier) {
            return Carbon::parse($dossier->created_at)->diffInDays(now());
        });

        return round($totalDelai / $dossiers->count(), 1);
    }

    /**
     * ==========================================
     * NOUVELLE MÉTHODE : RÉCÉPISSÉ PROVISOIRE
     * ==========================================
     */

    /**
     * Télécharger le récépissé provisoire PDF
     * 
     * Route: GET /admin/dossiers/{id}/download-recepisse-provisoire
     * 
     * @param int $id ID du dossier
     * @return \Illuminate\Http\Response
     */
    public function downloadRecepisseProvisoire($id)
    {
        try {
            // CORRECTION : Charger avec organisation.fondateurs
            $dossier = Dossier::with(['organisation.fondateurs'])->findOrFail($id);

            // Vérifier que le dossier peut générer un récépissé provisoire
            if (!$this->canGenerateRecepisseProvisoire($dossier)) {
                return back()->with('error', 'Le récépissé provisoire n\'est pas disponible pour ce dossier.');
            }

            // Vérifier que le dossier a des données supplémentaires JSON
            if (empty($dossier->donnees_supplementaires)) {
                return back()->with('error', 'Impossible de générer le récépissé : informations du déclarant manquantes.');
            }

            // CORRECTION : Le récépissé provisoire utilise les données JSON du déclarant, pas les fondateurs
            // Générer le PDF de récépissé provisoire sans vérifier les fondateurs
            $pdf = $this->pdfService->generateRecepisseProvisoire($dossier);

            // Nom de fichier sécurisé
            $filename = $this->sanitizeFilename("recepisse_provisoire_{$dossier->organisation->nom}_{$dossier->numero_dossier}") . "_" . now()->format('Ymd') . ".pdf";

            // CORRECTION : donnees_supplementaires est déjà un array
            $donneesSupp = is_array($dossier->donnees_supplementaires)
                ? $dossier->donnees_supplementaires
                : json_decode($dossier->donnees_supplementaires, true);
            $declarant = $donneesSupp['demandeur'] ?? [];
            \Log::info("Génération récépissé provisoire PDF pour dossier {$dossier->id}", [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation' => $dossier->organisation->nom ?? 'Inconnue',
                'declarant_nom' => ($declarant['prenom'] ?? '') . ' ' . ($declarant['nom'] ?? ''),
                'declarant_nip' => $declarant['nip'] ?? 'Non renseigné',
                'user' => auth()->user()->name
            ]);

            // OPTIONNEL : Enregistrer l'activité si le système ActivityLog est disponible
            if (class_exists('\Spatie\Activitylog\Models\Activity')) {
                activity()
                    ->performedOn($dossier)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'action' => 'download_recepisse_provisoire',
                        'organisation' => $dossier->organisation->nom,
                        'declarant' => ($declarant['prenom'] ?? '') . ' ' . ($declarant['nom'] ?? '')
                    ])
                    ->log('Téléchargement récépissé provisoire');
            }

            return \App\Helpers\PdfTemplateHelper::downloadPdf($pdf, $filename);

        } catch (\Exception $e) {
            \Log::error('Erreur génération récépissé provisoire PDF: ' . $e->getMessage(), [
                'dossier_id' => $id,
                'user' => auth()->user()->name ?? 'Système',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erreur lors de la génération du récépissé provisoire: ' . $e->getMessage());
        }
    }

    /**
     * =======================================
     * MÉTHODES UTILITAIRES AJOUTÉES
     * =======================================
     */

    /**
     * Nettoyer le nom de fichier pour éviter les problèmes
     * 
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename($filename)
    {
        // Remplacer les caractères spéciaux par des underscores
        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $filename);

        // Supprimer les underscores multiples
        $filename = preg_replace('/_+/', '_', $filename);

        // Supprimer les underscores en début et fin
        return trim($filename, '_');
    }

    /**
     * Vérifier si un dossier peut avoir un récépissé provisoire
     * 
     * @param Dossier $dossier
     * @return bool
     */
    private function canGenerateRecepisseProvisoire($dossier)
    {
        // Statuts autorisés pour le récépissé provisoire
        $statutsAutorises = ['soumis', 'en_cours', 'en_attente', 'approuve'];

        return in_array($dossier->statut, $statutsAutorises) &&
            $dossier->organisation &&
            !empty($dossier->donnees_supplementaires);
    }





    /**
     * Obtenir les actions disponibles pour un dossier
     * (Méthode mise à jour pour inclure le récépissé provisoire)
     * 
     * @param Dossier $dossier
     * @return array
     */
    private function getAvailableActionsUpdated($dossier)
    {
        $actions = [];

        // Accusé de réception - Toujours disponible
        $actions['accuse'] = [
            'disponible' => true,
            'libelle' => 'Accusé de réception',
            'description' => 'Document confirmant la réception du dossier',
            'couleur' => 'primary',
            'icone' => 'fas fa-file-alt'
        ];

        // Récépissé provisoire - Selon statut
        $actions['recepisse_provisoire'] = [
            'disponible' => $this->canGenerateRecepisseProvisoire($dossier),
            'libelle' => 'Récépissé provisoire',
            'description' => 'Document provisoire de déclaration',
            'couleur' => 'warning',
            'icone' => 'fas fa-file-signature'
        ];

        // Récépissé définitif - Seulement si approuvé
        $actions['recepisse_definitif'] = [
            'disponible' => $dossier->statut === 'approuve',
            'libelle' => 'Récépissé définitif',
            'description' => 'Document officiel d\'enregistrement',
            'couleur' => 'success',
            'icone' => 'fas fa-certificate'
        ];

        return $actions;
    }

    /**
     * Méthode utilitaire : Vérifier si un récépissé provisoire peut être généré
     */

    /**
     * Enrichir les données d'une organisation avec des informations calculées
     * 
     * @param Organisation $organisation
     * @return Organisation
     */
    private function enrichOrganisationData($organisation)
    {
        // Récupérer le dernier dossier de l'organisation
        $dernierDossier = $organisation->dossiers->first();

        // Ajouter les informations du dernier dossier
        $organisation->dernier_dossier = $dernierDossier;
        $organisation->dernier_dossier_numero = $dernierDossier->numero_dossier ?? null;
        $organisation->dernier_dossier_statut = $dernierDossier->statut ?? null;
        $organisation->dernier_dossier_date = $dernierDossier->created_at ?? null;

        // Calculer la priorité si le dossier est en attente
        if ($dernierDossier && in_array($dernierDossier->statut, ['soumis', 'en_cours'])) {
            $organisation->priorite = $this->calculatePriorityArchitecture($dernierDossier);

            // Calculer le délai d'attente en jours
            $organisation->delai_attente = Carbon::parse($dernierDossier->created_at)->diffInDays(now());
        } else {
            $organisation->priorite = null;
            $organisation->delai_attente = null;
        }

        // Compter le nombre total de dossiers
        $organisation->nombre_dossiers = Dossier::where('organisation_id', $organisation->id)->count();

        // Badge de statut avec couleur
        $organisation->statut_badge = $this->getStatutBadge($organisation->statut);

        // Badge de type avec couleur
        $organisation->type_badge = $this->getTypeBadge($organisation->type);

        // Formater les dates pour l'affichage
        $organisation->created_at_formatted = Carbon::parse($organisation->created_at)->format('d/m/Y H:i');
        $organisation->updated_at_formatted = Carbon::parse($organisation->updated_at)->format('d/m/Y H:i');

        return $organisation;
    }

    /**
     * Obtenir le badge HTML pour le statut d'une organisation
     * 
     * @param string $statut
     * @return string
     */
    private function getStatutBadge($statut)
    {
        $badges = [
            'brouillon' => ['class' => 'secondary', 'icon' => 'fas fa-edit', 'text' => 'Brouillon'],
            'soumis' => ['class' => 'info', 'icon' => 'fas fa-paper-plane', 'text' => 'Soumis'],
            'en_validation' => ['class' => 'warning', 'icon' => 'fas fa-clock', 'text' => 'En validation'],
            'approuve' => ['class' => 'success', 'icon' => 'fas fa-check-circle', 'text' => 'Approuvé'],
            'rejete' => ['class' => 'danger', 'icon' => 'fas fa-times-circle', 'text' => 'Rejeté']
        ];

        $badge = $badges[$statut] ?? ['class' => 'secondary', 'icon' => 'fas fa-question', 'text' => ucfirst($statut)];

        return [
            'class' => $badge['class'],
            'icon' => $badge['icon'],
            'text' => $badge['text']
        ];
    }

    /**
     * Obtenir le badge HTML pour le type d'organisation
     * 
     * @param string $type
     * @return string
     */
    private function getTypeBadge($type)
    {
        $badges = [
            'association' => ['class' => 'primary', 'icon' => 'fas fa-users', 'text' => 'Association'],
            'ong' => ['class' => 'success', 'icon' => 'fas fa-globe', 'text' => 'ONG'],
            'parti_politique' => ['class' => 'danger', 'icon' => 'fas fa-flag', 'text' => 'Parti Politique'],
            'confession_religieuse' => ['class' => 'info', 'icon' => 'fas fa-church', 'text' => 'Confession Religieuse']
        ];

        $badge = $badges[$type] ?? ['class' => 'secondary', 'icon' => 'fas fa-building', 'text' => ucfirst($type)];

        return [
            'class' => $badge['class'],
            'icon' => $badge['icon'],
            'text' => $badge['text']
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 🆕 CRÉATION D'ORGANISATION PAR ADMIN
    |--------------------------------------------------------------------------
    */

    /**
     * Afficher le formulaire de création d'une organisation (Admin)
     * Route: GET /admin/organisations/create
     */
    public function createOrganisation()
    {
        try {
            // Charger les types d'organisations
            $typesOrganisation = OrganisationType::where('is_active', true)
                ->orderBy('ordre')
                ->get();

            // Charger les provinces pour le formulaire
            $provinces = Province::where('is_active', true)
                ->orderBy('ordre_affichage')
                ->get();

            // Statistiques pour contexte
            $stats = [
                'total_organisations' => Organisation::count(),
                'ce_mois' => Organisation::whereMonth('created_at', now()->month)->count(),
            ];

            Log::info('Admin accède au formulaire de création d\'organisation', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name
            ]);

            return view('admin.dossiers.create', compact('typesOrganisation', 'provinces'));

        } catch (\Exception $e) {
            Log::error('Erreur affichage formulaire création organisation (Admin)', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.organisations.index')
                ->with('error', 'Erreur lors du chargement du formulaire');
        }
    }

    /**
     * Enregistrer une nouvelle organisation (Admin)
     * Route: POST /admin/organisations
     */
    public function storeOrganisation(Request $request)
    {
        try {
            Log::info('🚀 Début création organisation par Admin', [
                'user_id' => Auth::id(),
                'type_organisation' => $request->input('organisation_type_id')
            ]);

            // ✅ ÉTAPE 1 : Charger les règles métier depuis organisation_types
            $organisationType = OrganisationType::findOrFail($request->organisation_type_id);

            Log::info('📋 Règles métier chargées', [
                'type' => $organisationType->code,
                'min_fondateurs' => $organisationType->nb_min_fondateurs_majeurs,
                'min_adherents' => $organisationType->nb_min_adherents_creation
            ]);

            // ✅ ÉTAPE 2 : Validation avec règles dynamiques
            $validator = Validator::make($request->all(), [
                // Informations de base
                'organisation_type_id' => 'required|exists:organisation_types,id',
                'nom' => 'required|string|max:255',
                'sigle' => 'nullable|string|max:50',
                'objet' => 'required|string',
                'siege_social' => 'required|string',
                'date_creation' => 'nullable|date',

                // ✅ Géolocalisation (Foreign Keys CORRECTS)
                'province_ref_id' => 'required|exists:provinces,id',
                'departement_ref_id' => 'required|exists:departements,id',
                'commune_ville_ref_id' => 'nullable|exists:communes_villes,id',
                'arrondissement_ref_id' => 'nullable|exists:arrondissements,id',
                'canton_ref_id' => 'nullable|exists:cantons,id',
                'regroupement_ref_id' => 'nullable|exists:regroupements,id',
                'localite_ref_id' => 'nullable|exists:localites,id',

                // ✅ Zone type et champs conditionnels
                'zone_type' => 'required|in:urbaine,rurale',
                'quartier' => 'nullable|string|max:255',
                'village' => 'nullable|string|max:255',
                'lieu_dit' => 'nullable|string|max:255',

                // Contact
                'telephone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',

                // ✅ Fondateurs (validation dynamique)
                'fondateurs' => 'required|array|min:' . $organisationType->nb_min_fondateurs_majeurs,
                'fondateurs.*.nip' => 'required|string|size:14',
                'fondateurs.*.nom' => 'required|string|max:255',
                'fondateurs.*.prenom' => 'required|string|max:255',
                'fondateurs.*.fonction' => 'required|string|max:255',
                'fondateurs.*.telephone' => 'nullable|string|max:20',
                'fondateurs.*.email' => 'nullable|email|max:255',
            ], [
                'province_ref_id.required' => 'La province est obligatoire',
                'departement_ref_id.required' => 'Le département est obligatoire',
                'zone_type.required' => 'Le type de zone (urbaine/rurale) est obligatoire',
                'fondateurs.min' => "Minimum {$organisationType->nb_min_fondateurs_majeurs} fondateur(s) requis pour ce type d'organisation",
            ]);

            if ($validator->fails()) {
                Log::warning('❌ Validation échouée création organisation Admin', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            // ✅ ÉTAPE 3 : Récupérer les noms géographiques depuis les tables
            $geoData = $this->buildGeographicData($request);

            Log::info('🗺️ Données géographiques construites', $geoData);

            // ✅ ÉTAPE 4 : Créer l'organisation avec TOUTES les colonnes
            $organisation = Organisation::create([
                // Informations de base
                'organisation_type_id' => $request->organisation_type_id,
                'type' => $organisationType->code, // ✅ ENUM type
                'nom' => $request->nom,
                'sigle' => $request->sigle,
                'objet' => $request->objet,
                'siege_social' => $request->siege_social,
                'date_creation' => $request->date_creation ?? now(),

                // ✅ Contact
                'telephone' => $request->telephone,
                'email' => $request->email,

                // ✅ Anciens champs texte (obligatoires pour compatibilité)
                'province' => $geoData['province_nom'],
                'prefecture' => $geoData['prefecture_nom'],
                'departement' => $geoData['departement_nom'],
                'zone_type' => $request->zone_type,

                // ✅ Champs conditionnels selon zone_type
                'ville_commune' => $geoData['ville_commune'],
                'arrondissement' => $geoData['arrondissement'],
                'quartier' => $request->quartier,
                'canton' => $geoData['canton'],
                'regroupement' => $geoData['regroupement'],
                'village' => $request->village,
                'lieu_dit' => $request->lieu_dit,

                // ✅ Foreign Keys (noms corrects)
                'province_ref_id' => $request->province_ref_id,
                'departement_ref_id' => $request->departement_ref_id,
                'commune_ville_ref_id' => $request->commune_ville_ref_id,
                'arrondissement_ref_id' => $request->arrondissement_ref_id,
                'canton_ref_id' => $request->canton_ref_id,
                'regroupement_ref_id' => $request->regroupement_ref_id,
                'localite_ref_id' => $request->localite_ref_id,

                // Métadonnées
                'user_id' => Auth::id(),
                'statut' => 'soumis',
                'is_active' => true,
                'nombre_adherents_min' => $organisationType->nb_min_adherents_creation,
            ]);

            Log::info('✅ Organisation créée par Admin', [
                'organisation_id' => $organisation->id,
                'nom' => $organisation->nom
            ]);

            // ✅ ÉTAPE 5 : Créer les fondateurs
            $fondateursCount = 0;
            foreach ($request->fondateurs as $index => $fondateurData) {
                Fondateur::create([
                    'organisation_id' => $organisation->id,
                    'nip' => $fondateurData['nip'],
                    'nom' => $fondateurData['nom'],
                    'prenom' => $fondateurData['prenom'],
                    'fonction' => $fondateurData['fonction'],
                    'telephone' => $fondateurData['telephone'] ?? null,
                    'email' => $fondateurData['email'] ?? null,
                    'ordre' => $index + 1,
                ]);
                $fondateursCount++;
            }

            Log::info('✅ Fondateurs créés', ['count' => $fondateursCount]);

            // ✅ ÉTAPE 6 : Créer le dossier
            $dossier = Dossier::create([
                'organisation_id' => $organisation->id,
                'type_operation' => 'creation',
                'numero_dossier' => $this->generateDossierNumber($organisation),
                'statut' => 'soumis',
                'submitted_at' => now(),
                'donnees_supplementaires' => json_encode([
                    'demandeur' => [
                        'nom' => Auth::user()->name,
                        'email' => Auth::user()->email,
                        'role' => 'Admin',
                        'nip' => Auth::user()->nip ?? 'ADMIN',
                    ],
                    'created_by_admin' => true,
                    'phase_creation' => 'admin_direct',
                    'regles_appliquees' => [
                        'min_fondateurs' => $organisationType->nb_min_fondateurs_majeurs,
                        'min_adherents' => $organisationType->nb_min_adherents_creation,
                    ]
                ], JSON_UNESCAPED_UNICODE)
            ]);

            Log::info('✅ Dossier créé', [
                'dossier_id' => $dossier->id,
                'numero' => $dossier->numero_dossier
            ]);

            // Initialiser le workflow
            $this->workflowService->initializeWorkflow($dossier);

            // Générer QR Code
            try {
                $this->qrCodeService->generateForDossier($dossier);
            } catch (\Exception $e) {
                Log::warning('⚠️ Erreur génération QR Code non bloquante', [
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            Log::info('🎉 Création organisation Admin terminée avec succès', [
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id
            ]);

            return redirect()->route('admin.dossiers.show', $dossier->id)
                ->with('success', "Organisation \"{$organisation->nom}\" créée avec succès ! Dossier N° {$dossier->numero_dossier}");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erreur création organisation Admin', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'organisation : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Construire les données géographiques
     * Récupère les noms depuis les tables de référence
     */
    private function buildGeographicData(Request $request): array
    {
        $data = [
            'province_nom' => null,
            'prefecture_nom' => null,
            'departement_nom' => null,
            'ville_commune' => null,
            'arrondissement' => null,
            'canton' => null,
            'regroupement' => null,
        ];

        // Province (obligatoire)
        if ($request->province_ref_id) {
            $province = Province::find($request->province_ref_id);
            if ($province) {
                $data['province_nom'] = $province->nom;
                $data['prefecture_nom'] = $province->chef_lieu ?? $province->nom;
            }
        }

        // Département (obligatoire)
        if ($request->departement_ref_id) {
            $departement = Departement::find($request->departement_ref_id);
            if ($departement) {
                $data['departement_nom'] = $departement->nom;
                // Prefecture peut aussi venir du département
                if (!$data['prefecture_nom']) {
                    $data['prefecture_nom'] = $departement->chef_lieu ?? $departement->nom;
                }
            }
        }

        // Zone urbaine
        if ($request->zone_type === 'urbaine') {
            if ($request->commune_ville_ref_id) {
                $commune = CommuneVille::find($request->commune_ville_ref_id);
                if ($commune) {
                    $data['ville_commune'] = $commune->nom;
                }
            }

            if ($request->arrondissement_ref_id) {
                $arrondissement = Arrondissement::find($request->arrondissement_ref_id);
                if ($arrondissement) {
                    $data['arrondissement'] = $arrondissement->nom;
                }
            }
        }

        // Zone rurale
        if ($request->zone_type === 'rurale') {
            if ($request->canton_ref_id) {
                $canton = Canton::find($request->canton_ref_id);
                if ($canton) {
                    $data['canton'] = $canton->nom;
                }
            }

            if ($request->regroupement_ref_id) {
                $regroupement = Regroupement::find($request->regroupement_ref_id);
                if ($regroupement) {
                    $data['regroupement'] = $regroupement->nom;
                }
            }
        }

        return $data;
    }

    /**
     * Générer un numéro de dossier unique
     */
    private function generateDossierNumber($organisation)
    {
        $typeCode = strtoupper(substr($organisation->organisationType->code ?? 'ORG', 0, 3));
        $year = date('Y');
        $lastDossier = Dossier::whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%s-%05d', $typeCode, $year, $lastDossier);
    }

    /*
    |--------------------------------------------------------------------------
    | 🗺️ API GÉOLOCALISATION (AJAX)
    |--------------------------------------------------------------------------
    */

    /**
     * Récupérer toutes les provinces
     */
    public function getProvinces()
    {
        try {
            $provinces = Province::where('is_active', true)
                ->orderBy('ordre_affichage')
                ->get(['id', 'nom', 'code', 'chef_lieu']);

            return response()->json([
                'success' => true,
                'data' => $provinces
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur chargement provinces'
            ], 500);
        }
    }

    /**
     * Récupérer les départements d'une province
     */
    public function getDepartements($province_id)
    {
        try {
            $departements = Departement::where('province_id', $province_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->get(['id', 'nom', 'code', 'chef_lieu']);

            return response()->json([
                'success' => true,
                'data' => $departements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur chargement départements'
            ], 500);
        }
    }

    /**
     * Récupérer les communes d'un département
     */
    public function getCommunes($departement_id)
    {
        try {
            $communes = CommuneVille::where('departement_id', $departement_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->get(['id', 'nom', 'type', 'statut']);

            return response()->json([
                'success' => true,
                'data' => $communes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur chargement communes'
            ], 500);
        }
    }

    /**
     * Récupérer les arrondissements d'une commune
     */
    public function getArrondissements($commune_id)
    {
        try {
            $arrondissements = Arrondissement::where('commune_ville_id', $commune_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->get(['id', 'nom', 'code']);

            return response()->json([
                'success' => true,
                'data' => $arrondissements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur chargement arrondissements'
            ], 500);
        }
    }


    /**
     * Récupérer les localités d'un regroupement
     */
    public function getLocalites($regroupement_id)
    {
        try {
            $localites = Localite::where('regroupement_id', $regroupement_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->get(['id', 'nom', 'type']);

            return response()->json([
                'success' => true,
                'data' => $localites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur chargement localités'
            ], 500);
        }
    }

    /**
     * ✅ NOUVELLE : Récupérer les localités d'un regroupement OU d'un arrondissement
     */
    public function getLocalitesNew(Request $request)
    {
        try {
            $query = Localite::where('is_active', true);

            if ($request->regroupement_id) {
                $query->where('regroupement_id', $request->regroupement_id)
                    ->where('type', 'village');
            } elseif ($request->arrondissement_id) {
                $query->where('arrondissement_id', $request->arrondissement_id)
                    ->where('type', 'quartier');
            }

            $localites = $query->orderBy('nom')->get(['id', 'nom', 'type']);

            return response()->json([
                'success' => true,
                'data' => $localites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur chargement localités'
            ], 500);
        }
    }


    /**
     * ============================================
     * MÉTHODE À AJOUTER/REMPLACER DANS :
     * app/Http/Controllers/Admin/DossierController.php
     * ============================================
     * 
     * Cette méthode retourne toutes les configurations d'un type d'organisation
     * incluant : fondateurs min, adhérents min, documents requis, guide, loi
     */

    /**
     * API : Récupérer les règles métier complètes d'un type d'organisation
     * Route: GET /admin/api/geo/organisation-types/{organisation_type_id}/rules
     * 
     * @param int $organisation_type_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganisationTypeRules($organisation_type_id)
    {
        try {
            // Charger le type avec ses documents requis
            $orgType = \App\Models\OrganisationType::with([
                'documentTypes' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('document_type_organisation_type.ordre', 'asc');
                }
            ])->findOrFail($organisation_type_id);

            // Formater les documents requis
            $documentsRequis = $orgType->documentTypes->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'code' => $doc->code,
                    'nom' => $doc->libelle ?? $doc->nom,
                    'description' => $doc->description,
                    'format_accepte' => $doc->format_accepte ?? 'pdf,jpg,jpeg,png',
                    'taille_max_mo' => $doc->taille_max ?? 5,
                    'is_obligatoire' => (bool) ($doc->pivot->is_obligatoire ?? true),
                    'ordre' => $doc->pivot->ordre ?? 0,
                    'aide_texte' => $doc->pivot->aide_texte ?? null,
                ];
            });

            // Séparer documents obligatoires et facultatifs
            $docsObligatoires = $documentsRequis->where('is_obligatoire', true)->values();
            $docsFacultatifs = $documentsRequis->where('is_obligatoire', false)->values();

            return response()->json([
                'success' => true,
                'data' => [
                    // Informations de base
                    'id' => $orgType->id,
                    'code' => $orgType->code,
                    'nom' => $orgType->nom,
                    'description' => $orgType->description,
                    'couleur' => $orgType->couleur,
                    'icone' => $orgType->icone,

                    // Règles métier
                    'nb_min_fondateurs' => $orgType->nb_min_fondateurs_majeurs ?? 2,
                    'nb_min_adherents' => $orgType->nb_min_adherents_creation ?? 0,
                    'is_lucratif' => (bool) ($orgType->is_lucratif ?? false),

                    // Textes légaux et guide
                    'guide_creation' => $orgType->guide_creation,
                    'texte_legislatif' => $orgType->texte_legislatif,
                    'loi_reference' => $orgType->loi_reference,

                    // Documents requis
                    'documents_requis' => $documentsRequis,
                    'documents_obligatoires' => $docsObligatoires,
                    'documents_facultatifs' => $docsFacultatifs,
                    'nb_documents_obligatoires' => $docsObligatoires->count(),
                    'nb_documents_facultatifs' => $docsFacultatifs->count(),

                    // Flags pour l'interface
                    'require_fondateurs' => ($orgType->nb_min_fondateurs_majeurs ?? 0) > 0,
                    'require_adherents' => ($orgType->nb_min_adherents_creation ?? 0) > 0,
                    'require_documents' => $docsObligatoires->count() > 0,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Type d\'organisation non trouvé',
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Erreur getOrganisationTypeRules: ' . $e->getMessage(), [
                'organisation_type_id' => $organisation_type_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des règles',
            ], 500);
        }
    }


    /**
     * ============================================
     * MÉTHODES API GÉOLOCALISATION - À AJOUTER
     * Dans app/Http/Controllers/Admin/DossierController.php
     * ============================================
     * 
     * Ajouter ces méthodes dans la section API du DossierController
     * Après les méthodes existantes : getCantons(), getRegroupements()
     */

    /**
     * ========================================
     * API : Obtenir les quartiers d'un arrondissement (Zone Urbaine)
     * ========================================
     * Route: GET /admin/api/geo/quartiers/{arrondissement_id}
     * 
     * @param int $arrondissement_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuartiers($arrondissement_id)
    {
        try {
            $quartiers = \App\Models\Localite::where('arrondissement_id', $arrondissement_id)
                ->where('type', 'quartier')
                ->where('is_active', true)
                ->orderBy('nom')
                ->select('id', 'nom', 'code')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $quartiers,
                'count' => $quartiers->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getQuartiers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des quartiers',
                'data' => []
            ], 500);
        }
    }

    /**
     * ========================================
     * API : Obtenir les villages d'un regroupement (Zone Rurale)
     * ========================================
     * Route: GET /admin/api/geo/villages/{regroupement_id}
     * 
     * @param int $regroupement_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVillages($regroupement_id)
    {
        try {
            $villages = \App\Models\Localite::where('regroupement_id', $regroupement_id)
                ->where('type', 'village')
                ->where('is_active', true)
                ->orderBy('nom')
                ->select('id', 'nom', 'code')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $villages,
                'count' => $villages->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getVillages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des villages',
                'data' => []
            ], 500);
        }
    }


    /**
     * ============================================
     * VÉRIFICATION DES MÉTHODES EXISTANTES
     * ============================================
     * 
     * Assurez-vous que ces méthodes existent également :
     * 
     * - getProvinces()
     * - getDepartements($province_id)
     * - getCommunes($departement_id)
     * - getArrondissements($commune_id)
     * - getCantons($departement_id)
     * - getRegroupements($canton_id)
     * 
     * Si elles n'existent pas, voici leurs implémentations :
     */

    /**
     * API : Obtenir les cantons d'un département (Zone Rurale)
     * Route: GET /admin/api/geo/cantons/{departement_id}
     */
    public function getCantons($departement_id)
    {
        try {
            $cantons = \App\Models\Canton::where('departement_id', $departement_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->select('id', 'nom', 'code')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cantons,
                'count' => $cantons->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getCantons: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des cantons',
                'data' => []
            ], 500);
        }
    }

    /**
     * API : Obtenir les regroupements d'un canton (Zone Rurale)
     * Route: GET /admin/api/geo/regroupements/{canton_id}
     */
    public function getRegroupements($canton_id)
    {
        try {
            $regroupements = \App\Models\Regroupement::where('canton_id', $canton_id)
                ->where('is_active', true)
                ->orderBy('nom')
                ->select('id', 'nom', 'code')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $regroupements,
                'count' => $regroupements->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getRegroupements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des regroupements',
                'data' => []
            ], 500);
        }
    }


}