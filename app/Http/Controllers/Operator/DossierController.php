<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\Organisation;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\QrCode;
use App\Services\DossierService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\OrganisationValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Adherent;
use Exception;

class DossierController extends Controller
{
    protected $dossierService;
    protected $fileUploadService;
    protected $notificationService;
    protected $validationService;
    
    public function __construct(
        DossierService $dossierService,
        FileUploadService $fileUploadService,
        NotificationService $notificationService,
        OrganisationValidationService $validationService
    ) {
        $this->dossierService = $dossierService;
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
        $this->validationService = $validationService;
    }
    
    /**
     * Afficher la liste des dossiers
     */
    public function index(Request $request)
    {
        $query = Dossier::whereHas('organisation', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['organisation', 'currentStep']);
        
        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        if ($request->has('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }
        
        if ($request->has('type_operation')) {
            $query->where('type_operation', $request->type_operation);
        }
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_dossier', 'like', "%{$search}%")
                    ->orWhereHas('organisation', function ($q2) use ($search) {
                        $q2->where('nom', 'like', "%{$search}%");
                    });
            });
        }
        
        $dossiers = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Organisations pour le filtre
        $organisations = Organisation::where('user_id', Auth::id())
            ->orderBy('nom')
            ->get();
        
        return view('operator.dossiers.index', compact('dossiers', 'organisations'));
    }

    /**
     * Afficher le formulaire de création selon le type
     */
    public function create($type)
    {
        // Mapper les types courts vers les types complets
        $typeMapping = [
            'association' => Organisation::TYPE_ASSOCIATION,
            'ong' => Organisation::TYPE_ONG,
            'parti' => Organisation::TYPE_PARTI,
            'confession' => Organisation::TYPE_CONFESSION
        ];
        
        if (!isset($typeMapping[$type])) {
            abort(404, 'Type d\'organisation non reconnu');
        }
        
        $fullType = $typeMapping[$type];
        
        // Vérifier les limites de création
        $limits = $this->checkOrganisationLimits(Auth::user(), $fullType);
        
        if (!$limits['can_create']) {
            return redirect()->route('operator.dashboard')
                ->with('error', $limits['message']);
        }
        
        // Directement afficher le formulaire de création au lieu de rediriger vers un guide
        $provinces = $this->getProvinces();
        $guides = $this->getGuideContent($fullType);
        $documentTypes = $this->getRequiredDocuments($fullType);
        
        return view('operator.dossiers.create', compact('type', 'fullType', 'provinces', 'guides', 'documentTypes'));
    }

    /**
     * ✅ MÉTHODE TEMPLATE EXCEL ADHÉRENTS
     * Résout: Route [operator.templates.adherents-excel] not defined
     */
    public function downloadTemplate()
    {
        try {
            Log::info("=== TÉLÉCHARGEMENT TEMPLATE ADHÉRENTS ===", [
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);

            // Créer le contenu CSV du template
            $csvContent = "NIP,Nom,Prénom,Téléphone,Profession,Adresse\n";
            $csvContent .= "A1-0001-19801225,MOUNDOUNGA,Jean,+24101234567,Ingénieur,Libreville\n";
            $csvContent .= "B2-0002-19751110,OBAME,Marie,+24101234568,Professeur,Port-Gentil\n";
            $csvContent .= "C3-0003-19900315,NGUEMA,Paul,+24101234569,Médecin,Franceville\n";

            $fileName = 'template_adherents_' . date('Y-m-d') . '.csv';

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error("=== ERREUR TÉLÉCHARGEMENT TEMPLATE ===", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors du téléchargement du template : ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer un nouveau dossier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'type_operation' => 'required|in:creation,modification,cessation,declaration'
        ]);
        
        // Vérifier que l'organisation appartient à l'utilisateur
        $organisation = Organisation::where('id', $validated['organisation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        try {
            // Créer le dossier via le service
            $dossier = $this->dossierService->createDossier([
                'organisation_id' => $organisation->id,
                'type_operation' => $validated['type_operation']
            ]);
            
            return redirect()->route('operator.dossiers.edit', $dossier->id)
                ->with('success', 'Dossier créé avec succès. Complétez les informations requises.');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un dossier spécifique
     */
    public function show($dossier)
    {
        $dossier = Dossier::with([
            'organisation',
            'documents.documentType',
            'currentStep.validationEntity',
            'validations.validatedBy',
            'comments.user'
        ])->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Obtenir le statut détaillé
        $status = $this->dossierService->getDossierStatus($dossier);
        
        return view('operator.dossiers.show', compact('dossier', 'status'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($dossier)
    {
        $dossier = Dossier::with(['organisation', 'documents'])->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$dossier->canBeModified()) {
            return redirect()->route('operator.dossiers.show', $dossier->id)
                ->with('error', 'Ce dossier ne peut plus être modifié');
        }
        
        // Documents requis
        $requiredDocuments = DocumentType::where('type_organisation', $dossier->organisation->type)
            ->where(function ($query) use ($dossier) {
                $query->where('type_operation', $dossier->type_operation)
                    ->orWhereNull('type_operation');
            })
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();
        
        return view('operator.dossiers.edit', compact('dossier', 'requiredDocuments'));
    }

    /**
     * Mettre à jour un dossier
     */
    public function update(Request $request, $dossier)
    {
        $dossier = Dossier::findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Sauvegarder les métadonnées
        $dossier->update([
            'metadata' => array_merge($dossier->metadata ?? [], [
                'last_updated' => now()->toDateTimeString(),
                'form_data' => $request->except(['_token', '_method'])
            ])
        ]);
        
        return redirect()->route('operator.dossiers.edit', $dossier->id)
            ->with('success', 'Modifications enregistrées');
    }

    /**
     * Soumettre un dossier
     */
    public function soumettre($dossier)
    {
        $dossier = Dossier::with('organisation')->findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        try {
            // Valider l'organisation
            $validation = $this->validationService->validateBeforeSubmission($dossier->organisation);
            
            if (!$validation['is_valid']) {
                return redirect()->back()
                    ->with('error', 'Validation échouée')
                    ->with('validation_errors', $validation['errors']);
            }
            
            // Soumettre le dossier
            $this->dossierService->submitDossier($dossier);
            
            return redirect()->route('operator.dossiers.show', $dossier->id)
                ->with('success', 'Dossier soumis avec succès');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la soumission : ' . $e->getMessage());
        }
    }

    /**
     * Uploader un document
     */
    public function uploadDocument(Request $request, $dossier)
    {
        $dossier = Dossier::findOrFail($dossier);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'document' => 'required|file|max:10240', // 10MB max
            'document_type_id' => 'required|exists:document_types,id'
        ]);
        
        try {
            // Ajouter le document via le service
            $document = $this->dossierService->addDocument(
                $dossier,
                $request->document_type_id,
                $request->file('document')
            );
            
            return redirect()->back()
                ->with('success', 'Document téléchargé avec succès');
            
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du téléchargement : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument($dossier, $document)
    {
        $dossier = Dossier::findOrFail($dossier);
        $document = Document::findOrFail($document);
        
        // Vérifier l'accès
        if ($dossier->organisation->user_id !== Auth::id() || $document->dossier_id !== $dossier->id) {
            abort(403);
        }
        
        // Vérifier que le dossier peut être modifié
        if (!$dossier->canBeModified()) {
            return redirect()->back()
                ->with('error', 'Ce dossier ne peut plus être modifié');
        }
        
        // Supprimer le fichier physique
        if ($document->chemin_fichier && Storage::exists($document->chemin_fichier)) {
            Storage::delete($document->chemin_fichier);
        }
        
        // Supprimer l'enregistrement
        $document->delete();
        
        return redirect()->back()
            ->with('success', 'Document supprimé');
    }

    /**
     * Télécharger un document
     */
    public function downloadDocument($document)
    {
        $document = Document::with(['dossier.organisation'])->findOrFail($document);
        
        // Vérifier l'accès
        if ($document->dossier->organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que le fichier existe
        if (!$document->fileExists()) {
            return redirect()->back()
                ->with('error', 'Fichier introuvable');
        }
        
        return Storage::download($document->chemin_fichier, $document->nom_original);
    }

    /**
     * ✅ MÉTHODE CONFIRMATION OPTIMISÉE POUR 50K ADHÉRENTS
     * Correction complète avec gestion optimisée des gros volumes
     */
    public function confirmation(Request $request, $dossier)
    {
        try {
            Log::info("=== DÉBUT CONFIRMATION OPTIMISÉE 50K ===", [
                'dossier_param' => $dossier,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            // ✅ CORRECTION : Gestion ID ou objet
            if (is_object($dossier) && is_a($dossier, 'App\Models\Dossier')) {
                $dossierObj = $dossier;
            } else {
                $dossierId = is_numeric($dossier) ? (int)$dossier : $dossier;
                
                // ✅ REQUÊTE OPTIMISÉE AVEC whereHas
                $dossierObj = Dossier::with([
                    'organisation',
                    'documents'
                ])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

                if (!$dossierObj) {
                    Log::error("=== DOSSIER NON TROUVÉ ===", [
                        'dossier_id' => $dossierId,
                        'user_id' => auth()->id()
                    ]);
                    
                    return redirect()->route('operator.dashboard')
                        ->with('error', 'Dossier non trouvé ou accès non autorisé.');
                }
            }

            // ✅ CALCUL OPTIMISÉ DES STATISTIQUES POUR GROS VOLUMES
            $adherents_stats = $this->calculateAdherentsStatsOptimized($dossierObj->organisation);
            
            // ✅ QR CODE AVEC GESTION D'ERREUR
            $qrCode = $this->getQrCodeForDossier($dossierObj);
            
            // ✅ ACCUSÉ DE RÉCEPTION OPTIMISÉ
            $accuseReceptionUrl = $this->getAccuseReceptionDownloadUrl($dossierObj);
            
            // ✅ DONNÉES DE CONFIRMATION OPTIMISÉES
            $confirmationData = [
                'organisation' => $dossierObj->organisation,
                'dossier' => $dossierObj,
                'numero_recepisse' => $dossierObj->organisation->numero_recepisse ?? 'Non attribué',
                'numero_dossier' => $dossierObj->numero_dossier ?? 'Non attribué',
                'qr_code' => $qrCode,
                'adherents_stats' => $adherents_stats,
                'anomalies' => $this->getAnomaliesFromDossier($dossierObj),
                'accuse_reception_url' => $accuseReceptionUrl,
                'delai_traitement' => '72 heures ouvrées',
                'message_legal' => $this->getMessageLegal(),
                'prochaines_etapes' => $this->getProchainesEtapes(),
                'contact_support' => $this->getContactSupport(),
                'submitted_at' => $dossierObj->submitted_at ?? $dossierObj->created_at ?? now(),
                'estimated_completion' => $this->calculateEstimatedCompletion($dossierObj)
            ];

            // Nettoyer la session
            session()->forget('success_data');

            Log::info('✅ PAGE CONFIRMATION CHARGÉE AVEC SUCCÈS', [
                'dossier_id' => $dossierObj->id,
                'adherents_total' => $adherents_stats['total'],
                'performance_optimized' => true
            ]);

            return view('operator.dossiers.confirmation', compact('confirmationData'));

        } catch (\Exception $e) {
            Log::error('❌ ERREUR CONFIRMATION OPTIMISÉE', [
                'dossier_param' => $dossier,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return redirect()->route('operator.dashboard')
                ->with('error', 'Erreur lors de l\'affichage de la confirmation: ' . $e->getMessage());
        }
    }

       /**
     * ✅ CALCUL STATISTIQUES INVERSÉ - VALIDES D'ABORD, PUIS ANOMALIES
     * Logique : adherents_valides = SANS ANOMALIES, avec_anomalies = total - valides
     */
    private function calculateAdherentsStatsOptimized($organisation)
    {
        try {
            Log::info('📊 CALCUL STATS INVERSÉ - VALIDES D\'ABORD', [
                'organisation_id' => $organisation->id
            ]);

            $stats = [
                'total' => 0,
                'valides' => 0,
                'avec_anomalies' => 0,
                'anomalies_critiques' => 0,
                'anomalies_majeures' => 0,
                'anomalies_mineures' => 0,
                'taux_validite' => 0
            ];

            // ✅ ÉTAPE 1 : COMPTER TOTAL ADHÉRENTS
            $stats['total'] = Adherent::where('organisation_id', $organisation->id)->count();

            if ($stats['total'] == 0) {
                return $stats;
            }

            // ✅ ÉTAPE 2 : CALCULER D'ABORD LES ADHÉRENTS VALIDES (SANS ANOMALIES)
            // Compter les adhérents qui N'ONT PAS d'entrée dans adherent_anomalies
            $stats['valides'] = Adherent::where('organisation_id', $organisation->id)
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                          ->from('adherent_anomalies')
                          ->whereRaw('adherent_anomalies.adherent_id = adherents.id');
                })
                ->count();

            // ✅ ÉTAPE 3 : CALCULER LES ADHÉRENTS AVEC ANOMALIES (INVERSE)
            $stats['avec_anomalies'] = $stats['total'] - $stats['valides'];

            // ✅ ÉTAPE 4 : DÉTAIL DES ANOMALIES PAR TYPE
            $anomaliesStats = \DB::table('adherent_anomalies as aa')
                ->join('adherents as a', 'aa.adherent_id', '=', 'a.id')
                ->where('a.organisation_id', $organisation->id)
                ->select([
                    \DB::raw('SUM(CASE WHEN aa.type_anomalie = "critique" THEN 1 ELSE 0 END) as total_critiques'),
                    \DB::raw('SUM(CASE WHEN aa.type_anomalie = "majeure" THEN 1 ELSE 0 END) as total_majeures'),
                    \DB::raw('SUM(CASE WHEN aa.type_anomalie = "mineure" THEN 1 ELSE 0 END) as total_mineures')
                ])
                ->first();

            // ✅ REMPLIR LES STATISTIQUES DES ANOMALIES
            if ($anomaliesStats) {
                $stats['anomalies_critiques'] = (int) $anomaliesStats->total_critiques;
                $stats['anomalies_majeures'] = (int) $anomaliesStats->total_majeures;
                $stats['anomalies_mineures'] = (int) $anomaliesStats->total_mineures;
            }

            // ✅ CALCULER TAUX DE VALIDITÉ (% d'adhérents sans anomalies)
            $stats['taux_validite'] = $stats['total'] > 0 
                ? round(($stats['valides'] / $stats['total']) * 100, 2) 
                : 0;

            // ✅ VÉRIFICATION DE COHÉRENCE
            $calculatedTotal = $stats['valides'] + $stats['avec_anomalies'];
            if ($calculatedTotal !== $stats['total']) {
                Log::warning('⚠️ INCOHÉRENCE CALCUL INVERSÉ DÉTECTÉE', [
                    'total_reel' => $stats['total'],
                    'valides' => $stats['valides'],
                    'avec_anomalies' => $stats['avec_anomalies'],
                    'total_calcule' => $calculatedTotal,
                    'difference' => $stats['total'] - $calculatedTotal
                ]);
                
                // Correction automatique : forcer la cohérence
                $stats['avec_anomalies'] = $stats['total'] - $stats['valides'];
            }

            Log::info('✅ STATS CALCULÉES MÉTHODE INVERSÉE', [
                'organisation_id' => $organisation->id,
                'total' => $stats['total'],
                'valides_sans_anomalies' => $stats['valides'],
                'avec_anomalies_calcule' => $stats['avec_anomalies'],
                'taux_validite' => $stats['taux_validite'] . '%',
                'methode' => 'INVERSE_VALIDES_FIRST'
            ]);

            return $stats;

        } catch (\Exception $e) {
            Log::error('❌ ERREUR CALCUL STATS INVERSÉ', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);
            
            // ✅ FALLBACK : Retour aux anciennes statistiques
            return $this->calculateAdherentsStatsLegacyInverse($organisation);
        }
    }

    /**
     * ✅ MÉTHODE FALLBACK INVERSÉE - CALCUL LEGACY DEPUIS JSON
     */
    private function calculateAdherentsStatsLegacyInverse($organisation)
    {
        $stats = [
            'total' => 0,
            'valides' => 0,
            'avec_anomalies' => 0,
            'anomalies_critiques' => 0,
            'anomalies_majeures' => 0,
            'anomalies_mineures' => 0,
            'taux_validite' => 0
        ];

        // ✅ ÉTAPE 1 : Total adhérents
        $stats['total'] = Adherent::where('organisation_id', $organisation->id)->count();

        if ($stats['total'] == 0) {
            return $stats;
        }

        // ✅ ÉTAPE 2 : Compter D'ABORD les adhérents VALIDES (sans anomalies)
        $adherents = Adherent::where('organisation_id', $organisation->id)
            ->select('id', 'anomalies_data', 'has_anomalies')
            ->get();

        $adherentsAvecAnomaliesCount = 0;

        foreach ($adherents as $adherent) {
            // ✅ LOGIQUE INVERSÉE : Identifier ceux AVEC anomalies
            if ($adherent->has_anomalies && !empty($adherent->anomalies_data)) {
                $adherentsAvecAnomaliesCount++;

                // Analyser les anomalies JSON pour le détail
                $anomalies = json_decode($adherent->anomalies_data ?: '[]', true) ?: [];
                
                foreach ($anomalies as $anomalie) {
                    $type = $anomalie['type'] ?? 'mineure';
                    
                    switch ($type) {
                        case 'critique':
                            $stats['anomalies_critiques']++;
                            break;
                        case 'majeure':
                            $stats['anomalies_majeures']++;
                            break;
                        case 'mineure':
                            $stats['anomalies_mineures']++;
                            break;
                    }
                }
            }
        }

        // ✅ CALCUL INVERSÉ : Valides = Total - Avec anomalies
        $stats['valides'] = $stats['total'] - $adherentsAvecAnomaliesCount;
        $stats['avec_anomalies'] = $adherentsAvecAnomaliesCount;

        // ✅ SÉCURITÉ : Éviter les valeurs négatives
        if ($stats['valides'] < 0) {
            Log::warning('⚠️ VALEURS NÉGATIVES DÉTECTÉES - CORRECTION', [
                'valides_calcule' => $stats['valides'],
                'total' => $stats['total'],
                'avec_anomalies' => $stats['avec_anomalies']
            ]);
            
            $stats['valides'] = 0;
            $stats['avec_anomalies'] = $stats['total'];
        }

        $stats['taux_validite'] = $stats['total'] > 0 
            ? round(($stats['valides'] / $stats['total']) * 100, 2) 
            : 0;

        Log::info('✅ STATS LEGACY INVERSÉES CALCULÉES', [
            'organisation_id' => $organisation->id,
            'total' => $stats['total'],
            'valides_sans_anomalies' => $stats['valides'],
            'avec_anomalies' => $stats['avec_anomalies'],
            'taux_validite' => $stats['taux_validite'] . '%',
            'methode' => 'LEGACY_INVERSE'
        ]);

        return $stats;
    }


    /**
     * ✅ MÉTHODE FALLBACK CORRIGÉE - CALCUL LEGACY DEPUIS JSON
     */
    private function calculateAdherentsStatsLegacy($organisation)
    {
        $stats = [
            'total' => 0,
            'valides' => 0,
            'avec_anomalies' => 0,
            'anomalies_critiques' => 0,
            'anomalies_majeures' => 0,
            'anomalies_mineures' => 0,
            'taux_validite' => 0
        ];

        $stats['total'] = Adherent::where('organisation_id', $organisation->id)->count();

        if ($stats['total'] == 0) {
            return $stats;
        }

        // ✅ CALCUL DEPUIS JSON ANOMALIES_DATA (ANCIEN SYSTÈME)
        $adherents = Adherent::where('organisation_id', $organisation->id)
            ->select('id', 'anomalies_data', 'has_anomalies')
            ->get();

        foreach ($adherents as $adherent) {
            // ✅ CORRECTION : Un adhérent valide = SANS anomalies (has_anomalies = false)
            if (!$adherent->has_anomalies || empty($adherent->anomalies_data)) {
                $stats['valides']++;
                continue;
            }

            $stats['avec_anomalies']++;

            // Analyser les anomalies JSON
            $anomalies = json_decode($adherent->anomalies_data ?: '[]', true) ?: [];
            
            foreach ($anomalies as $anomalie) {
                $type = $anomalie['type'] ?? 'mineure';
                
                switch ($type) {
                    case 'critique':
                        $stats['anomalies_critiques']++;
                        break;
                    case 'majeure':
                        $stats['anomalies_majeures']++;
                        break;
                    case 'mineure':
                        $stats['anomalies_mineures']++;
                        break;
                }
            }
        }

        // ✅ VÉRIFICATION : Total doit être cohérent
        if (($stats['valides'] + $stats['avec_anomalies']) !== $stats['total']) {
            Log::warning('⚠️ INCOHÉRENCE DÉTECTÉE DANS CALCUL STATS', [
                'total' => $stats['total'],
                'valides' => $stats['valides'],
                'avec_anomalies' => $stats['avec_anomalies'],
                'somme' => $stats['valides'] + $stats['avec_anomalies']
            ]);
            
            // Correction automatique
            $stats['valides'] = $stats['total'] - $stats['avec_anomalies'];
        }

        $stats['taux_validite'] = $stats['total'] > 0 
            ? round(($stats['valides'] / $stats['total']) * 100, 2) 
            : 0;

        Log::info('✅ STATS LEGACY CALCULÉES', [
            'organisation_id' => $organisation->id,
            'total' => $stats['total'],
            'valides_sans_anomalies' => $stats['valides'],
            'avec_anomalies' => $stats['avec_anomalies'],
            'taux_validite' => $stats['taux_validite'] . '%'
        ]);

        return $stats;
    }

    // ========================================
// À AJOUTER DANS DossierController.php
// ========================================

    /**
     * ✅ GÉNÉRATION PDF PAR CHUNKS - SOLUTION ROBUSTE POUR GROS VOLUMES
     * REMPLACE la méthode rapportAnomalies() existante
     */
    public function rapportAnomalies($dossierId)
    {
        try {
            Log::info('📋 GÉNÉRATION RAPPORT ANOMALIES PDF - MODE CHUNKS', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);

            // ✅ RÉCUPÉRER LE DOSSIER
            $dossier = Dossier::with(['organisation'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                abort(404, 'Dossier non trouvé');
            }

            // ✅ COMPTER LES ANOMALIES POUR STRATÉGIE
            $totalAnomalies = \DB::table('adherent_anomalies as aa')
                ->join('adherents as a', 'aa.adherent_id', '=', 'a.id')
                ->where('a.organisation_id', $dossier->organisation->id)
                ->count();

            Log::info('📊 ANALYSE VOLUME POUR STRATÉGIE PDF', [
                'total_anomalies' => $totalAnomalies,
                'memory_limit_current' => ini_get('memory_limit')
            ]);

            // ✅ STRATÉGIE SELON LE VOLUME
            if ($totalAnomalies > 501) {
                return $this->generateLightweightPdfReport($dossier, $totalAnomalies);
            } else {
                return $this->generateStandardPdfReport($dossier, $totalAnomalies);
            }

        } catch (\Exception $e) {
            Log::error('❌ ERREUR GÉNÉRATION RAPPORT PDF', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage(),
                'memory_peak' => memory_get_peak_usage(true) / 1024 / 1024 . 'MB'
            ]);

            // ✅ FALLBACK ULTIME : HTML simple
            return $this->generateHtmlFallbackReport($dossierId);
        }
    }


    /**
     * ✅ GÉNÉRATION PDF STANDARD (MÉTHODE MANQUANTE)
     */
    private function generateStandardPdfReport($dossier, $totalAnomalies)
    {
        try {
            Log::info('📋 GÉNÉRATION PDF STANDARD', [
                'dossier_id' => $dossier->id,
                'total_anomalies' => $totalAnomalies
            ]);

            // ✅ CALCULER STATISTIQUES
            $stats = $this->calculateAdherentsStatsOptimized($dossier->organisation);

            // ✅ RÉCUPÉRER LES ANOMALIES (LIMITÉES POUR ÉVITER SURCHARGE)
            $anomalies = \DB::table('adherent_anomalies as aa')
                ->join('adherents as a', 'aa.adherent_id', '=', 'a.id')
                ->where('a.organisation_id', $dossier->organisation->id)
                ->select([
                    'aa.*',
                    'a.nip',
                    'a.nom',
                    'a.prenom',
                    'a.civilite'
                ])
                ->orderBy('aa.type_anomalie')
                ->orderBy('aa.priorite')
                ->limit(100) // ✅ LIMITE POUR ÉVITER SURCHARGE MÉMOIRE
                ->get();

            // ✅ GROUPER LES ANOMALIES
            $anomaliesGroupees = [
                'critiques' => $anomalies->where('type_anomalie', 'critique'),
                'majeures' => $anomalies->where('type_anomalie', 'majeure'),
                'mineures' => $anomalies->where('type_anomalie', 'mineure')
            ];

            // ✅ DONNÉES POUR LE RAPPORT
            $rapportData = [
                'dossier' => $dossier,
                'organisation' => $dossier->organisation,
                'anomalies' => $anomalies,
                'anomaliesGroupees' => $anomaliesGroupees,
                'stats' => $stats,
                'metadata' => [
                    'genere_le' => now()->format('d/m/Y à H:i'),
                    'genere_par' => auth()->user()->name ?? 'Système',
                    'total_anomalies' => $totalAnomalies,
                    'anomalies_affichees' => $anomalies->count(),
                    'numero_dossier' => $dossier->numero_dossier ?? 'N/A',
                    'mode_optimise' => $anomalies->count() < $totalAnomalies,
                    'note_limitation' => $anomalies->count() < $totalAnomalies ? 
                        'Seules les ' . $anomalies->count() . ' premières anomalies sont affichées sur ' . $totalAnomalies . ' total.' : null
                ]
            ];

            $filename = 'rapport_anomalies_' . 
                       ($dossier->numero_dossier ?? $dossier->id) . '_' . 
                       now()->format('Ymd_His') . '.pdf';

            // ✅ GÉNÉRER LE PDF
            $pdf = \PDF::loadView('operator.dossiers.rapport-anomalies-pdf', $rapportData);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
                'dpi' => 96
            ]);

            Log::info('✅ PDF STANDARD GÉNÉRÉ AVEC SUCCÈS', [
                'dossier_id' => $dossier->id,
                'anomalies_affichees' => $anomalies->count(),
                'total_anomalies' => $totalAnomalies
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR PDF STANDARD', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            
            // ✅ FALLBACK VERS VERSION STATISTIQUES
            return $this->generateQuickStatsPdf($dossier, $totalAnomalies);
        }
    }

    /**
     * ✅ GÉNÉRATION PDF STATISTIQUES RAPIDE (MÉTHODE MANQUANTE)
     */
    private function generateQuickStatsPdf($dossier, $totalAnomalies)
    {
        try {
            Log::info('📋 GÉNÉRATION PDF STATISTIQUES RAPIDE', [
                'dossier_id' => $dossier->id,
                'total_anomalies' => $totalAnomalies
            ]);

            $stats = $this->calculateAdherentsStatsOptimized($dossier->organisation);
            
            // ✅ HTML INLINE ULTRA-SIMPLE
            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Rapport Statistiques</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
                    .header { text-align: center; border-bottom: 2px solid #009e3f; padding-bottom: 10px; margin-bottom: 20px; }
                    .header h1 { color: #009e3f; font-size: 16px; margin: 0; }
                    .stat { margin: 8px 0; padding: 8px; background: #f8f9fa; border-left: 3px solid #009e3f; }
                    .note { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 15px 0; font-size: 10px; }
                </style>
            </head>
            <body>
                <table width="100%" style="margin-bottom: 15px;">
                    <tr>
                        <td width="200" align="center" style="color: #333333; font-weight: bold; font-size:14px;">
                            <div style="font-size:17px;">MINISTÈRE DE L’INTÉRIEUR,
                            DE LA SÉCURITÉ <br> ET DE LA DÉCENTRALISATION</div>
                            <div>
                        <div>_____________________</div>
                        <div>SECRETARIAT GENERAL</div>
                        <div>_____________________</div>
                        <div>DIRECTION GENERALE DES ELECTIONS 			
                    <br>ET DES LIBERTES PUBLIQUES</div>
                        <div>_____________________</div>
                        <div>DIRECTION DES PARTIS POLITIQUES<br>
                        ASSOCIATIONS ET LIBERTE DE CULTE</div>
                        <div>_____________________</div><br/>
                        <div style="font-size:14px;">N° '.($dossier->numero_dossier ?? 'N/A').'</div>

                            </div>
                        </td>
                        <td  width="70"></td>
                        <td align="right" style="color: #003f7f; font-weight: bold; font-size:19px;">
                            <div style="text-align:center" align="top">
                            RÉPUBLIQUE GABONAISE<br>
                            <div style="font-size:12px">UNION • TRAVAIL • JUSTICE</div>
                            </div><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>    
                        </td>
                    </tr>
                </table>

                <div class="header">
                    <h1>RAPPORT D\'ANOMALIES - STATISTIQUES</h1>
                    <p>Organisation: ' . ($dossier->organisation->nom ?? 'N/A') . '</p>
                </div>
                
                <div class="stat"><strong>Total Adhérents:</strong> ' . number_format($stats['total']) . '</div>
                <div class="stat"><strong>Adhérents Valides (sans anomalies):</strong> ' . number_format($stats['valides']) . '</div>
                <div class="stat"><strong>Adhérents avec Anomalies:</strong> ' . number_format($stats['avec_anomalies']) . '</div>
                <div class="stat"><strong>Taux de Validité:</strong> ' . $stats['taux_validite'] . '%</div>
                
                <div style="margin: 20px 0;">
                    <h3 style="color: #009e3f; font-size: 14px;">Répartition des Anomalies</h3>
                    <div class="stat" style="background: #fee; border-left-color: #dc3545;"><strong>Anomalies Critiques:</strong> ' . number_format($stats['anomalies_critiques']) . '</div>
                    <div class="stat" style="background: #fff3cd; border-left-color: #ffc107;"><strong>Anomalies Majeures:</strong> ' . number_format($stats['anomalies_majeures']) . '</div>
                    <div class="stat" style="background: #e1f5fe; border-left-color: #17a2b8;"><strong>Anomalies Mineures:</strong> ' . number_format($stats['anomalies_mineures']) . '</div>
                </div>
                
                <p>
            <table style="float: left; width:100%;">
                <tr>
                    <td style="width:60%;"></td>
                
                    <td style="width:40%; font-weight: bold; text-align: center; font-size:14px;">
                        Le Directeur Général des Elections<br/>
                        et des Libertés Publiques<br/><br/><br/><br/><br/><br/>

                        Dieudonné YAYA
                    </td>
                </tr>
            </table>
        </p>
        <br/><br/><br/>

                <div class="footer-ministeriel" style="text-align:center;">
                    <strong>Information:</strong> Rapport statistiques généré pour un volume de <strong>' . number_format($totalAnomalies) . ' anomalies</strong>.
                    <br>Pour consulter le détail complet de chaque anomalie, utilisez la consultation en ligne.
                </div>
                <br/>
                
                <div class="footer-ministeriel" style="text-align:center;">
                    <strong>MINISTÈRE DE L\'INTÉRIEUR, DE LA SÉCURITÉ ET DE LA DÉCENTRALISATION</strong><br>
                    119, RUE Jean Baptiste NDENDE, (Avenue de Cointet BP 2110 Libreville, Gabon)<br>
                    <em>Ce document contient des informations confidentielles. Sa diffusion est strictement réservée aux personnes autorisées.</em>
                </div>
            </body>
            </html>';

            $pdf = \PDF::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'rapport_stats_' . 
                       ($dossier->numero_dossier ?? $dossier->id) . '_' . 
                       now()->format('Ymd_His') . '.pdf';

            Log::info('✅ PDF STATISTIQUES GÉNÉRÉ AVEC SUCCÈS', [
                'dossier_id' => $dossier->id,
                'total_anomalies' => $totalAnomalies,
                'mode' => 'STATS_ONLY'
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('❌ ÉCHEC TOTAL PDF', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Impossible de générer le PDF. Utilisez la consultation en ligne.');
        }
    }

    /**
     * ✅ GÉNÉRATION PDF LÉGER (MÉTHODE MANQUANTE)
     */
    private function generateLightweightPdfReport($dossier, $totalAnomalies)
    {
        try {
            Log::info('📋 GÉNÉRATION PDF LÉGER', [
                'dossier_id' => $dossier->id,
                'total_anomalies' => $totalAnomalies
            ]);

            // ✅ CALCULER STATISTIQUES
            $stats = $this->calculateAdherentsStatsOptimized($dossier->organisation);

            // ✅ RÉCUPÉRER SEULEMENT LES ANOMALIES CRITIQUES (TOP 50)
            $anomaliesCritiques = \DB::table('adherent_anomalies as aa')
                ->join('adherents as a', 'aa.adherent_id', '=', 'a.id')
                ->where('a.organisation_id', $dossier->organisation->id)
                ->where('aa.type_anomalie', 'critique')
                ->select([
                    'aa.type_anomalie',
                    'aa.message_anomalie',
                    'aa.champ_concerne',
                    'a.nip',
                    'a.nom',
                    'a.prenom',
                    'a.civilite'
                ])
                ->orderBy('aa.priorite')
                ->limit(50)
                ->get();

            // ✅ DONNÉES MINIMALES
            $rapportData = [
                'dossier' => $dossier,
                'organisation' => $dossier->organisation,
                'stats' => $stats,
                'anomaliesCritiques' => $anomaliesCritiques,
                'metadata' => [
                    'genere_le' => now()->format('d/m/Y à H:i'),
                    'genere_par' => auth()->user()->name ?? 'Système',
                    'total_anomalies' => $totalAnomalies,
                    'critiques_affichees' => $anomaliesCritiques->count(),
                    'numero_dossier' => $dossier->numero_dossier ?? 'N/A',
                    'mode' => 'LEGER',
                    'note' => 'Rapport léger - Top ' . $anomaliesCritiques->count() . ' anomalies critiques sur ' . $totalAnomalies . ' total.'
                ]
            ];

            $filename = 'rapport_anomalies_leger_' . 
                       ($dossier->numero_dossier ?? $dossier->id) . '_' . 
                       now()->format('Ymd_His') . '.pdf';

            // ✅ UTILISER LA VUE MINIMALE
            $pdf = \PDF::loadView('operator.dossiers.rapport-minimal-pdf', $rapportData);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => false,
                'dpi' => 72
            ]);

            Log::info('✅ PDF LÉGER GÉNÉRÉ AVEC SUCCÈS', [
                'dossier_id' => $dossier->id,
                'critiques_affichees' => $anomaliesCritiques->count()
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR PDF LÉGER', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->generateQuickStatsPdf($dossier, $totalAnomalies);
        }
    }

    /**
     * ✅ GÉNÉRATION PDF STATISTIQUES UNIQUEMENT (FALLBACK ULTIME)
     */
    private function generateStatisticsOnlyPdf($dossier, $totalAnomalies)
    {
        try {
            $stats = $this->calculateAdherentsStatsOptimized($dossier->organisation);

            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Rapport Statistiques - ' . ($dossier->organisation->nom ?? 'Organisation') . '</title>
                <style>
                    body { font-family: Arial; font-size: 12px; margin: 20px; }
                    .header { text-align: center; border-bottom: 2px solid #009e3f; padding-bottom: 10px; }
                    .stats { margin: 20px 0; }
                    .stat-item { margin: 10px 0; padding: 10px; background: #f8f9fa; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>🇬🇦 RAPPORT D\'ANOMALIES - STATISTIQUES</h1>
                    <p>Organisation: ' . ($dossier->organisation->nom ?? 'N/A') . '</p>
                    <p>Généré le: ' . now()->format('d/m/Y à H:i') . '</p>
                </div>
                <div class="stats">
                    <div class="stat-item"><strong>Total Adhérents:</strong> ' . $stats['total'] . '</div>
                    <div class="stat-item"><strong>Adhérents Valides:</strong> ' . $stats['valides'] . '</div>
                    <div class="stat-item"><strong>Avec Anomalies:</strong> ' . $stats['avec_anomalies'] . '</div>
                    <div class="stat-item"><strong>Anomalies Critiques:</strong> ' . $stats['anomalies_critiques'] . '</div>
                    <div class="stat-item"><strong>Anomalies Majeures:</strong> ' . $stats['anomalies_majeures'] . '</div>
                    <div class="stat-item"><strong>Anomalies Mineures:</strong> ' . $stats['anomalies_mineures'] . '</div>
                    <div class="stat-item"><strong>Taux de Validité:</strong> ' . $stats['taux_validite'] . '%</div>
                </div>
                <div style="margin-top: 30px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7;">
                    <strong>Note:</strong> Rapport statistiques généré en raison du volume élevé d\'anomalies (' . $totalAnomalies . ').
                    Pour consulter le détail complet, utilisez le rapport en ligne.
                </div>
            </body>
            </html>';

            $pdf = \PDF::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'rapport_stats_' . $dossier->id . '_' . now()->format('Ymd_His') . '.pdf';

            Log::info('✅ PDF STATISTIQUES SEULES GÉNÉRÉ', [
                'total_anomalies' => $totalAnomalies,
                'mode' => 'STATS_ONLY'
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('❌ ÉCHEC TOTAL PDF', ['error' => $e->getMessage()]);
            return back()->with('error', 'Impossible de générer le PDF. Volume d\'anomalies trop important. Utilisez la consultation en ligne.');
        }
    }

    /**
     * ✅ FALLBACK HTML (si PDF impossible)
     */
    private function generateHtmlFallbackReport($dossierId)
    {
        return redirect()->route('operator.dossiers.consulter-anomalies', $dossierId)
                        ->with('warning', 'PDF indisponible pour ce volume. Consultation en ligne recommandée.');
    }

    /**
     * ✅ CONSULTATION EN LIGNE DES ANOMALIES - CORRIGÉE
     */
    public function consulterAnomalies($dossierId)
    {
        try {
            Log::info('👁️ CONSULTATION ANOMALIES EN LIGNE', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);

            // ✅ RÉCUPÉRER LE DOSSIER
            $dossier = Dossier::with(['organisation'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                abort(404, 'Dossier non trouvé');
            }

            // ✅ RÉCUPÉRER LES ANOMALIES AVEC PAGINATION POUR LA CONSULTATION EN LIGNE
            $anomaliesQuery = \DB::table('adherent_anomalies as aa')
                ->join('adherents as a', 'aa.adherent_id', '=', 'a.id')
                ->where('a.organisation_id', $dossier->organisation->id)
                ->select([
                    'aa.*',
                    'a.nip',
                    'a.nom',
                    'a.prenom',
                    'a.civilite'
                ])
                ->orderBy('aa.priorite')
                ->orderBy('aa.created_at', 'desc');

            // ✅ CORRECTION : Utiliser DB::table()->paginate() correctement
            $anomalies = $anomaliesQuery->paginate(20);

            // ✅ CALCULER STATISTIQUES
            $stats = $this->calculateAdherentsStatsOptimized($dossier->organisation);

            return view('operator.dossiers.consulter-anomalies', [
                'dossier' => $dossier,
                'organisation' => $dossier->organisation,
                'anomalies' => $anomalies,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR CONSULTATION ANOMALIES', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors de la consultation des anomalies : ' . $e->getMessage());
        }
    }

    /**
     * ✅ REFRESH DES STATISTIQUES VIA AJAX
     */
    public function refreshStats($dossierId)
    {
        try {
            Log::info('🔄 REFRESH STATS AJAX', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);

            // ✅ RÉCUPÉRER LE DOSSIER
            $dossier = Dossier::with(['organisation'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé'
                ], 404);
            }

            // ✅ RECALCULER LES STATISTIQUES
            $stats = $this->calculateAdherentsStatsOptimized($dossier->organisation);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR REFRESH STATS', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du refresh des statistiques'
            ], 500);
        }
    }

    /**
     * ✅ ANALYSE ÉCHANTILLON POUR ESTIMATION GROS VOLUMES
     */
    private function analyzeAnomaliesSample($sample)
    {
        $stats = ['critiques' => 0, 'majeures' => 0, 'mineures' => 0];
        
        foreach ($sample as $adherent) {
            if (!$adherent->is_active) continue;
            
            $anomalies = json_decode($adherent->anomalies ?? '[]', true) ?: [];
            
            if (empty($anomalies)) continue;
            
            // Classification simple pour l'estimation
            if (count($anomalies) > 5) {
                $stats['critiques']++;
            } elseif (count($anomalies) > 2) {
                $stats['majeures']++;
            } else {
                $stats['mineures']++;
            }
        }
        
        return $stats;
    }

    /**
     * ✅ TÉLÉCHARGEMENT ACCUSÉ DE RÉCEPTION
     */
    public function downloadAccuse($dossierId)
    {
        try {
            Log::info('📄 TÉLÉCHARGEMENT ACCUSÉ DE RÉCEPTION', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);

            // ✅ RÉCUPÉRER LE DOSSIER
            $dossier = Dossier::with(['organisation'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                abort(404, 'Dossier non trouvé');
            }

            // ✅ CHERCHER LE DOCUMENT ACCUSÉ DE RÉCEPTION
            $accuseDocument = \DB::table('documents')
                ->where('dossier_id', $dossier->id)
                ->where('is_system_generated', true)
                ->where('nom_fichier', 'like', 'accuse_reception_%')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$accuseDocument) {
                return back()->with('error', 'Accusé de réception non trouvé');
            }

            // ✅ VÉRIFIER L'EXISTENCE DU FICHIER
            $filePath = storage_path('app/public/' . $accuseDocument->chemin_fichier);
            
            if (!file_exists($filePath)) {
                Log::error('❌ FICHIER ACCUSÉ INTROUVABLE', [
                    'dossier_id' => $dossierId,
                    'file_path' => $filePath
                ]);
                
                return back()->with('error', 'Fichier accusé de réception introuvable');
            }

            // ✅ TÉLÉCHARGER LE FICHIER
            return response()->download($filePath, $accuseDocument->nom_fichier);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR TÉLÉCHARGEMENT ACCUSÉ', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors du téléchargement de l\'accusé de réception');
        }
    }



    /**
     * ✅ FINALISATION "PLUS TARD" - OPTIMISÉE
     */
    public function finalizeLater(Request $request, $dossierId)
    {
        try {
            Log::info('💾 DÉBUT finalizeLater OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            $cleanDossierId = (int) $dossierId;
            if ($cleanDossierId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiant de dossier invalide'
                ], 400);
            }

            // ✅ REQUÊTE OPTIMISÉE
            $dossier = Dossier::with('organisation')
                             ->where('id', $cleanDossierId)
                             ->whereHas('organisation', function($query) {
                                 $query->where('user_id', auth()->id());
                             })
                             ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé ou accès non autorisé'
                ], 404);
            }

            // ✅ MISE À JOUR OPTIMISÉE
            $updateData = [
                'statut' => 'brouillon_phase2_complete',
                'updated_at' => now()
            ];

            // ✅ MÉTADONNÉES DANS JSON EXISTANT
            $donneesSupplementaires = $this->getExistingDonneesSupplementaires($dossier);
            $donneesSupplementaires['finalisation_later'] = [
                'finalized_at' => now()->toISOString(),
                'finalized_by' => auth()->id(),
                'status' => 'saved_for_later_submission',
                'adherents_count' => $dossier->organisation->adherents()->count(),
                'correction_applied' => 'optimized_for_50k'
            ];

            $updateData['donnees_supplementaires'] = json_encode($donneesSupplementaires);

            $updated = $dossier->update($updateData);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la sauvegarde'
                ], 500);
            }

            Log::info('✅ Finalisation LATER réussie', [
                'dossier_id' => $cleanDossierId,
                'statut' => $updateData['statut'],
                'adherents_count' => $dossier->organisation->adherents()->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dossier sauvegardé avec succès. Vous pourrez le soumettre plus tard.',
                'redirect_url' => route('operator.dossiers.index'),
                'dossier' => [
                    'id' => $dossier->id,
                    'numero_dossier' => $dossier->numero_dossier,
                    'statut' => $updateData['statut'],
                    'adherents_count' => $dossier->organisation->adherents()->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR finalizeLater OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur système lors de la sauvegarde. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * ✅ FINALISATION "MAINTENANT" - OPTIMISÉE
     */
    public function finalizeNow(Request $request, $dossierId)
    {
        try {
            Log::info('🚀 DÉBUT finalizeNow OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString()
            ]);

            $cleanDossierId = (int) $dossierId;
            if ($cleanDossierId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiant de dossier invalide'
                ], 400);
            }

            // ✅ REQUÊTE OPTIMISÉE
            $dossier = Dossier::with('organisation')
                             ->where('id', $cleanDossierId)
                             ->whereHas('organisation', function($query) {
                                 $query->where('user_id', auth()->id());
                             })
                             ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé ou accès non autorisé'
                ], 404);
            }

            // ✅ MISE À JOUR OPTIMISÉE
            $updateData = [
                'statut' => 'soumis',
                'submitted_at' => now(),
                'updated_at' => now()
            ];

            // ✅ MÉTADONNÉES OPTIMISÉES
            $donneesSupplementaires = $this->getExistingDonneesSupplementaires($dossier);
            $donneesSupplementaires['finalisation_now'] = [
                'submitted_at' => now()->toISOString(),
                'submitted_by' => auth()->id(),
                'status' => 'submitted_immediately',
                'adherents_count' => $dossier->organisation->adherents()->count(),
                'qr_code_required' => true,
                'correction_applied' => 'optimized_for_50k'
            ];

            $updateData['donnees_supplementaires'] = json_encode($donneesSupplementaires);

            $updated = $dossier->update($updateData);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la soumission'
                ], 500);
            }

            Log::info('✅ Finalisation NOW réussie', [
                'dossier_id' => $cleanDossierId,
                'statut' => $updateData['statut'],
                'adherents_count' => $dossier->organisation->adherents()->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dossier finalisé et soumis avec succès. Un accusé de réception sera généré.',
                'redirect_url' => route('operator.dossiers.confirmation', $cleanDossierId),
                'dossier' => [
                    'id' => $dossier->id,
                    'numero_dossier' => $dossier->numero_dossier,
                    'statut' => $updateData['statut'],
                    'submitted_at' => $updateData['submitted_at']->format('d/m/Y H:i'),
                    'adherents_count' => $dossier->organisation->adherents()->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR finalizeNow OPTIMISÉE', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur système lors de la soumission. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * ✅ STORE ADHÉRENTS PHASE 2 - OPTIMISÉ 50K
     */
    public function storeAdherentsPhase2(Request $request, $dossierId)
    {
        try {
            Log::info('🚀 DÉBUT storeAdherentsPhase2 OPTIMISÉ 50K', [
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);

            // ✅ CONFIGURATION OPTIMISÉE POUR 50K
            @set_time_limit(900); // 15 minutes
            @ini_set('memory_limit', '2048M'); // 2GB

            $dossier = Dossier::with('organisation')
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé'
                ], 404);
            }

            // ✅ VÉRIFICATION VOLUME EXISTANT
            $adherentsExistants = Adherent::where('organisation_id', $dossier->organisation->id)->count();

            if ($adherentsExistants > 0) {
                Log::info('⚠️ ADHÉRENTS DÉJÀ EXISTANTS - FINALISATION DIRECTE', [
                    'organisation_id' => $dossier->organisation->id,
                    'count' => $adherentsExistants
                ]);
                
                $dossier->update([
                    'statut' => 'soumis',
                    'donnees_supplementaires' => json_encode([
                        'solution' => 'EXISTING_DATA_FINALIZED',
                        'total_existing' => $adherentsExistants,
                        'processed_at' => now()->toISOString()
                    ])
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Dossier finalisé avec succès',
                    'data' => [
                        'total_existing' => $adherentsExistants,
                        'solution' => 'EXISTING_DATA'
                    ],
                    'redirect_url' => route('operator.dossiers.confirmation', $dossier->id)
                ]);
            }

            // ✅ TRAITEMENT NOUVEAU VOLUME
            $adherentsData = $request->input('adherents');
            
            if (is_string($adherentsData)) {
                $adherentsArray = json_decode($adherentsData, true) ?: [];
            } else {
                $adherentsArray = is_array($adherentsData) ? $adherentsData : [];
            }

            if (empty($adherentsArray)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée d\'adhérents fournie'
                ], 422);
            }

            $totalAdherents = count($adherentsArray);
            
            // ✅ DÉCISION INTELLIGENTE SELON VOLUME
            if ($totalAdherents >= 1000) {
                Log::info('🔄 ACTIVATION TRAITEMENT CHUNKING OPTIMISÉ', [
                    'total_adherents' => $totalAdherents,
                    'chunks_estimated' => ceil($totalAdherents / 500)
                ]);
                
                return $this->processWithOptimizedChunking($adherentsArray, $dossier->organisation, $dossier, $request);
            } else {
                return $this->processStandardOptimized($adherentsArray, $dossier->organisation, $dossier, $request);
            }

        } catch (\Exception $e) {
            Log::error('❌ ERREUR storeAdherentsPhase2 OPTIMISÉ', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ TRAITEMENT STANDARD OPTIMISÉ
     */
    private function processStandardOptimized(array $adherentsArray, $organisation, $dossier, Request $request)
    {
        DB::beginTransaction();

        try {
            $inserted = 0;
            $errors = [];

            // ✅ TRAITEMENT PAR LOTS MÊME EN STANDARD
            $chunks = array_chunk($adherentsArray, 100);
            
            foreach ($chunks as $chunk) {
                foreach ($chunk as $adherentData) {
                    try {
                        if (!is_array($adherentData)) continue;
                        
                        $cleanData = $this->validateAdherentData($adherentData);

                        Adherent::create([
                            'organisation_id' => $organisation->id,
                            'nip' => $cleanData['nip'],
                            'nom' => strtoupper($cleanData['nom']),
                            'prenom' => $cleanData['prenom'],
                            'profession' => $cleanData['profession'] ?? null,
                            'fonction' => $cleanData['fonction'] ?? 'Membre',
                            'telephone' => $cleanData['telephone'] ?? null,
                            'email' => $cleanData['email'] ?? null,
                            'date_adhesion' => now(),
                            'is_active' => true
                        ]);

                        $inserted++;

                    } catch (\Exception $e) {
                        $errors[] = "Erreur adhérent: " . $e->getMessage();
                    }
                }
                
                // ✅ NETTOYAGE MÉMOIRE ENTRE CHUNKS
                if (memory_get_usage() > 1000000000) { // 1GB
                    gc_collect_cycles();
                }
            }

            $dossier->update([
                'statut' => 'soumis',
                'donnees_supplementaires' => json_encode([
                    'solution' => 'STANDARD_OPTIMIZED',
                    'total_inserted' => $inserted,
                    'errors_count' => count($errors),
                    'processed_at' => now()->toISOString()
                ])
            ]);

            DB::commit();

            Log::info('✅ TRAITEMENT STANDARD OPTIMISÉ TERMINÉ', [
                'inserted' => $inserted,
                'errors_count' => count($errors)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Adhérents traités avec succès',
                'data' => [
                    'total_inserted' => $inserted,
                    'errors' => $errors
                ],
                'redirect_url' => route('operator.dossiers.confirmation', $dossier->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * ✅ TRAITEMENT CHUNKING ULTRA-OPTIMISÉ POUR 50K
     */
    private function processWithOptimizedChunking(array $adherentsArray, $organisation, $dossier, Request $request)
    {
        try {
            $chunkSize = 250; // ✅ CHUNKS PLUS PETITS POUR 50K
            $chunks = array_chunk($adherentsArray, $chunkSize);
            $totalChunks = count($chunks);
            
            $totalInserted = 0;
            $allErrors = [];

            Log::info('🔄 DÉBUT CHUNKING ULTRA-OPTIMISÉ', [
                'total_adherents' => count($adherentsArray),
                'total_chunks' => $totalChunks,
                'chunk_size' => $chunkSize,
                'estimated_time' => ($totalChunks * 2) . ' seconds'
            ]);

            DB::beginTransaction();

            foreach ($chunks as $index => $chunk) {
                $chunkStartTime = microtime(true);
                
                $chunkInserted = 0;
                foreach ($chunk as $adherentData) {
                    try {
                        if (!is_array($adherentData)) continue;
                        
                        $cleanData = $this->validateAdherentData($adherentData);

                        Adherent::create([
                            'organisation_id' => $organisation->id,
                            'nip' => $cleanData['nip'],
                            'nom' => strtoupper($cleanData['nom']),
                            'prenom' => $cleanData['prenom'],
                            'profession' => $cleanData['profession'] ?? null,
                            'telephone' => $cleanData['telephone'] ?? null,
                            'date_adhesion' => now(),
                            'is_active' => true
                        ]);

                        $chunkInserted++;

                    } catch (\Exception $e) {
                        $allErrors[] = "Chunk $index: " . $e->getMessage();
                    }
                }

                $totalInserted += $chunkInserted;
                
                $chunkTime = round((microtime(true) - $chunkStartTime) * 1000, 2);
                
                Log::info("✅ CHUNK ULTRA-OPTIMISÉ $index/$totalChunks", [
                    'inserted' => $chunkInserted,
                    'total_so_far' => $totalInserted,
                    'chunk_time_ms' => $chunkTime,
                    'memory_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2)
                ]);

                // ✅ NETTOYAGE MÉMOIRE CRUCIAL POUR 50K
                if ($index % 10 === 0) {
                    gc_collect_cycles();
                }

                // ✅ PAUSE MICRO POUR ÉVITER SURCHARGE SERVEUR
                if ($totalChunks > 100) {
                    usleep(250000); // 0.25 seconde
                }
            }

            DB::commit();

            $dossier->update([
                'statut' => 'soumis',
                'donnees_supplementaires' => json_encode([
                    'solution' => 'ULTRA_OPTIMIZED_CHUNKING',
                    'chunks_processed' => $totalChunks,
                    'total_inserted' => $totalInserted,
                    'errors_count' => count($allErrors),
                    'processed_at' => now()->toISOString(),
                    'performance_optimized' => true
                ])
            ]);

            Log::info('🎉 CHUNKING ULTRA-OPTIMISÉ TERMINÉ', [
                'total_inserted' => $totalInserted,
                'chunks_processed' => $totalChunks,
                'final_memory_mb' => round(memory_get_usage() / 1024 / 1024, 2)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Adhérents traités avec succès par chunking ultra-optimisé",
                'data' => [
                    'total_inserted' => $totalInserted,
                    'chunks_processed' => $totalChunks,
                    'errors' => $allErrors,
                    'solution' => 'ULTRA_OPTIMIZED_CHUNKING'
                ],
                'redirect_url' => route('operator.dossiers.confirmation', $dossier->id)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('❌ ERREUR CHUNKING ULTRA-OPTIMISÉ', [
                'error' => $e->getMessage(),
                'dossier_id' => $dossier->id
            ]);

            throw $e;
        }
    }

    /**
     * Page d'import des adhérents - Phase 2
     */
    public function adherentsImportPage($dossierId)
    {
        try {
            $dossier = Dossier::with(['organisation', 'adherents'])
                ->where('id', $dossierId)
                ->whereHas('organisation', function($query) {
                    $query->where('user_id', auth()->id());
                })
                ->firstOrFail();

            $organisation = $dossier->organisation;
            $organisationType = $organisation->type ?? 'association';

            $adherents_stats = [
                'existants' => $dossier->adherents()->count(),
                'minimum_requis' => $this->getMinimumAdherentsRequired($organisationType),
                'manquants' => 0,
                'peut_soumettre' => false
            ];
            
            $adherents_stats['manquants'] = max(0, $adherents_stats['minimum_requis'] - $adherents_stats['existants']);
            $adherents_stats['peut_soumettre'] = $adherents_stats['manquants'] <= 0;

            $upload_config = [
                'max_file_size' => '50MB',
                'chunk_size' => 250,
                'max_adherents' => 100000,
                'chunking_threshold' => 1000
            ];

            $urls = [
                'store_adherents' => route('operator.dossiers.store-adherents', $dossier->id),
                'template_download' => route('operator.templates.adherents-excel'),
                'confirmation' => route('operator.dossiers.confirmation', $dossier->id)
            ];

            return view('operator.dossiers.adherents-import', compact(
                'dossier', 'organisation', 'adherents_stats', 'upload_config', 'urls'
            ));

        } catch (\Exception $e) {
            Log::error('❌ ERREUR adherentsImportPage', [
                'dossier_id' => $dossierId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('operator.dashboard')
                ->with('error', 'Erreur lors du chargement de la page d\'import');
        }
    }

    // ========================================================================
    // MÉTHODES AUXILIAIRES OPTIMISÉES
    // ========================================================================

    /**
     * ✅ RÉCUPÉRATION SÉCURISÉE DES DONNÉES JSON
     */
    private function getExistingDonneesSupplementaires($dossier)
    {
        $donneesSupplementaires = [];
        
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $decoded = json_decode($dossier->donnees_supplementaires, true);
                $donneesSupplementaires = $decoded && is_array($decoded) ? $decoded : [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }
        
        return $donneesSupplementaires;
    }

    /**
     * ✅ QR CODE OPTIMISÉ
     */
    private function getQrCodeForDossier(Dossier $dossier)
    {
        try {
            return QrCode::where('verifiable_type', 'App\\Models\\Dossier')
                ->where('verifiable_id', $dossier->id)
                ->where('is_active', 1)
                ->orderBy('created_at', 'desc')
                ->first();
        } catch (\Exception $e) {
            Log::error('❌ ERREUR QR CODE', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ ACCUSÉ DE RÉCEPTION OPTIMISÉ
     */
    private function getAccuseReceptionDownloadUrl(Dossier $dossier)
    {
        try {
            $accuseDocument = $dossier->documents()
                ->where(function($query) {
                    $query->where('nom_fichier', 'LIKE', 'accuse_reception_%')
                          ->orWhere('nom_fichier', 'LIKE', 'accuse_phase1_%');
                })
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($accuseDocument && $accuseDocument->chemin_fichier) {
                return route('operator.dossiers.download-accuse', ['path' => basename($accuseDocument->chemin_fichier)]);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('❌ ERREUR URL ACCUSÉ', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ VALIDATION ADHÉRENT OPTIMISÉE
     */
    private function validateAdherentData(array $data)
    {
        return [
            'nip' => strtoupper(trim($data['nip'] ?? '')),
            'nom' => trim($data['nom'] ?? ''),
            'prenom' => trim($data['prenom'] ?? ''),
            'profession' => trim($data['profession'] ?? ''),
            'fonction' => trim($data['fonction'] ?? 'Membre'),
            'telephone' => preg_replace('/[^0-9+]/', '', $data['telephone'] ?? ''),
            'email' => isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? $data['email'] : null
        ];
    }

    /**
     * ✅ ANOMALIES DEPUIS DOSSIER
     */
    private function getAnomaliesFromDossier(Dossier $dossier)
    {
        $donneesSupplementaires = $this->getExistingDonneesSupplementaires($dossier);
        return $donneesSupplementaires['adherents_anomalies'] ?? [];
    }

    /**
     * ✅ MESSAGE LÉGAL
     */
    private function getMessageLegal()
    {
        return 'Votre dossier numérique a été soumis avec succès. Conformément aux dispositions légales en vigueur, vous devez déposer votre dossier physique en 3 exemplaires auprès de la Direction Générale des Élections et des Libertés Publiques.';
    }

    /**
     * ✅ PROCHAINES ÉTAPES
     */
    private function getProchainesEtapes()
    {
        return [
            [
                'numero' => 1,
                'titre' => 'Assignation d\'un agent',
                'description' => 'Un agent sera assigné à votre dossier sous 48h ouvrées',
                'delai' => '48h ouvrées'
            ],
            [
                'numero' => 2,
                'titre' => 'Examen du dossier',
                'description' => 'Votre dossier sera examiné selon l\'ordre d\'arrivée',
                'delai' => '72h ouvrées'
            ],
            [
                'numero' => 3,
                'titre' => 'Notification du résultat',
                'description' => 'Vous recevrez une notification par email',
                'delai' => 'Variable'
            ],
            [
                'numero' => 4,
                'titre' => 'Dépôt physique requis',
                'description' => 'Déposer le dossier physique en 3 exemplaires à la DGELP',
                'delai' => 'Dans les 7 jours'
            ]
        ];
    }

    /**
     * ✅ CONTACT SUPPORT
     */
    private function getContactSupport()
    {
        return [
            'email' => 'support@pngdi.ga',
            'telephone' => '+241 01 23 45 67',
            'horaires' => 'Lundi - Vendredi: 08h00 - 17h00'
        ];
    }

    /**
     * ✅ CALCUL ESTIMATION COMPLETION
     */
    private function calculateEstimatedCompletion(Dossier $dossier)
    {
        $baseHours = 72;
        
        switch ($dossier->organisation->type) {
            case 'parti_politique':
                $baseHours += 24;
                break;
            case 'confession_religieuse':
                $baseHours += 12;
                break;
        }
        
        $nombreAdherents = $dossier->organisation->adherents()->count();
        if ($nombreAdherents > 10000) {
            $baseHours += 24;
        } elseif ($nombreAdherents > 1000) {
            $baseHours += 12;
        }
        
        return now()->addHours($baseHours);
    }

    /**
     * Obtenir le minimum d'adhérents requis selon le type d'organisation
     */
    private function getMinimumAdherentsRequired($organisationType)
    {
        $minimums = [
            'association' => 10,
            'ong' => 15,
            'parti_politique' => 500,
            'confession_religieuse' => 20
        ];
        
        return $minimums[$organisationType] ?? 10;
    }
    
    /**
     * Vérifier les limites de création d'organisation
     */
    protected function checkOrganisationLimits($user, $type)
    {
        if ($type === Organisation::TYPE_PARTI) {
            $hasActiveParti = Organisation::where('user_id', $user->id)
                ->where('type', Organisation::TYPE_PARTI)
                ->where('is_active', true)
                ->exists();
            
            if ($hasActiveParti) {
                return [
                    'can_create' => false,
                    'message' => 'Vous avez déjà un parti politique actif.'
                ];
            }
        }
        
        return ['can_create' => true, 'message' => ''];
    }

    /**
     * Obtenir le contenu du guide pour un type d'organisation
     */
    private function getGuideContent($type)
    {
        $guides = [
            Organisation::TYPE_ASSOCIATION => [
                'title' => 'Création d\'une association',
                'description' => 'Formalités pour créer une association au Gabon',
                'requirements' => [
                    'Minimum 7 membres fondateurs',
                    'Objet social déterminé',
                    'Siège social au Gabon'
                ]
            ]
        ];

        return $guides[$type] ?? $guides[Organisation::TYPE_ASSOCIATION];
    }

    /**
     * Obtenir les documents requis selon le type d'organisation
     */
    private function getRequiredDocuments($type)
    {
        return [
            'statuts' => ['name' => 'Statuts', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true]
        ];
    }

    /**
     * Obtenir la liste des provinces du Gabon
     */
    private function getProvinces(): array
    {
        return [
            'Estuaire' => 'Estuaire',
            'Haut-Ogooué' => 'Haut-Ogooué',
            'Moyen-Ogooué' => 'Moyen-Ogooué',
            'Ngounié' => 'Ngounié',
            'Nyanga' => 'Nyanga',
            'Ogooué-Ivindo' => 'Ogooué-Ivindo',
            'Ogooué-Lolo' => 'Ogooué-Lolo',
            'Ogooué-Maritime' => 'Ogooué-Maritime',
            'Woleu-Ntem' => 'Woleu-Ntem'
        ];
    }

    // ========================================================================
    // MÉTHODES PLACEHOLDER POUR COMPATIBILITÉ
    // ========================================================================


    public function anomalies(Request $request) { return redirect()->back()->with('info', 'En développement'); }
    public function resolveAnomalie(Request $request, $adherentId) { return redirect()->back()->with('info', 'En développement'); }
    public function subventionsIndex() { return redirect()->back()->with('info', 'En développement'); }
    public function subventionCreate($organisation) { return redirect()->back()->with('info', 'En développement'); }
    public function subventionStore(Request $request) { return redirect()->back()->with('info', 'En développement'); }
    public function subventionShow($subvention) { return redirect()->back()->with('info', 'En développement'); }
    public function brouillons() { return redirect()->route('operator.dossiers.index', ['statut' => 'brouillon']); }
    public function saveDraft(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function restoreDraft(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function historique($dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function timeline($dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function extendLock(Request $request, $dossier) { return response()->json(['message' => 'En développement']); }
    public function releaseLock(Request $request, $dossier) { return response()->json(['message' => 'En développement']); }
    public function duplicate(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function saveAsTemplate(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function templates() { return redirect()->back()->with('info', 'En développement'); }
    public function createFromTemplate(Request $request, $template) { return redirect()->back()->with('info', 'En développement'); }
    public function addComment(Request $request, $dossier) { return redirect()->back()->with('info', 'En développement'); }
    public function updateComment(Request $request, $comment) { return redirect()->back()->with('info', 'En développement'); }
    public function deleteComment($comment) { return redirect()->back()->with('info', 'En développement'); }
    public function replaceDocument(Request $request, $dossier, $document) { return redirect()->back()->with('info', 'En développement'); }
    public function previewDocument($dossier, $document) { return redirect()->back()->with('info', 'En développement'); }
    public function getStats() { return response()->json(['total_dossiers' => 0, 'en_cours' => 0, 'approuves' => 0, 'rejetes' => 0]); }
}