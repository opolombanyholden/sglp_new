<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Dossier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Models\QrCode;
use App\Services\QrCodeService;

class PDFService
{
    /**
     * Générer l'accusé de réception PDF avec mPDF et header/footer fixes
     */
    public function generateAccuseReception(Dossier $dossier)
    {
        try {
            // Récupérer le template et personnalisations
            $template = \App\Models\DocumentTemplate::where('type_document', 'accuse_reception')
                ->where('is_active', true)
                ->first();

            $customization = null;
            if ($template) {
                $customization = \App\Models\DocumentGenerationCustomization::where('dossier_id', $dossier->id)
                    ->where('document_template_id', $template->id)
                    ->first();
            }

            // Récupérer en-tête et signature
            $headerText = $customization?->header_text ?? $template?->header_text ?? '';
            $signatureText = $customization?->signature_text ?? $template?->signature_text ?? '';

            // DEBUG: Log pour vérifier les données
            Log::info('PDF Generation Debug', [
                'template_found' => $template ? true : false,
                'template_id' => $template?->id,
                'customization_found' => $customization ? true : false,
                'header_text_length' => strlen($headerText),
                'signature_text_length' => strlen($signatureText),
                'header_preview' => substr(strip_tags($headerText), 0, 50),
                'signature_preview' => substr(strip_tags($signatureText), 0, 50),
            ]);

            // Générer QR Code en PNG base64 si disponible
            $qrCodeBase64 = '';
            if ($dossier->qr_code) {
                try {
                    $qrCodeBase64 = app(\App\Services\QrCodeService::class)->generateQrBase64FromUrl(
                        route('public.documents.verify', ['code' => $dossier->qr_code->code])
                    );
                    Log::info('QR Code generated', ['length' => strlen($qrCodeBase64)]);
                } catch (\Exception $e) {
                    Log::warning('QR Code generation failed: ' . $e->getMessage());
                }
            }

            // Préparer les données pour le template (SANS header_text/signature_text car gérés par mPDF)
            $data = $this->prepareAccuseData($dossier);

            // Générer le contenu HTML depuis la vue Blade
            $contentHtml = view('admin.pdf.accuse-reception-content', $data)->render();

            // Options pour header/footer fixes (répétés sur chaque page)
            $pdfOptions = [
                'header_text' => $headerText,
                'signature_text' => $signatureText,
                'qr_code_base64' => $qrCodeBase64,
            ];

            // Générer le PDF avec mPDF (header/footer automatiques via SetHTMLHeader/Footer)
            $mpdf = \App\Helpers\PdfTemplateHelper::generatePdf($contentHtml, 'P', 'A4', $pdfOptions);

            return $mpdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération accusé PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération de l\'accusé de réception: ' . $e->getMessage());
        }
    }

    /**
     * Générer le récépissé provisoire PDF - VERSION HARMONISÉE
     */
    public function generateRecepisseProvisoire(Dossier $dossier)
    {
        try {
            // Valider les données requises
            if (!$dossier->organisation) {
                throw new \Exception('Organisation manquante pour le dossier');
            }

            // ✅ HARMONISATION : Utiliser la même méthode que l'accusé
            $data = $this->prepareRecepisseProvisoireDataHarmonise($dossier);

            // Générer le PDF avec le template
            $pdf = Pdf::loadView('admin.pdf.recepisse-provisoire', $data);

            // Configuration PDF (identique à l'accusé)
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération récépissé provisoire: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id ?? null,
                'organisation_id' => $dossier->organisation->id ?? null
            ]);
            throw new \Exception('Erreur lors de la génération du récépissé provisoire: ' . $e->getMessage());
        }
    }

    /**
     * Générer le récépissé définitif PDF avec backgrounds
     */
    public function generateRecepisseDefinitif(Dossier $dossier)
    {
        try {
            if (!$dossier->organisation) {
                throw new \Exception('Organisation manquante pour le dossier');
            }

            $data = $this->prepareRecepisseDefinitifDataHarmonise($dossier);

            // Générer le contenu HTML depuis la vue Blade
            // Note: Pour l'instant on utilise la vue complète, à modifier plus tard
            $contentHtml = view('admin.pdf.recepisse-definitif', $data)->render();

            // Pour le récépissé définitif, on garde la vue complète pour l'instant
            // car elle a une structure plus complexe
            // TODO: Créer recepisse-definitif-content.blade.php
            $pdf = Pdf::loadHTML($contentHtml);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);

            return $pdf;

        } catch (\Exception $e) {
            Log::error('Erreur génération récépissé définitif: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id ?? null
            ]);
            throw new \Exception('Erreur lors de la génération du récépissé définitif: ' . $e->getMessage());
        }
    }

    /**
     * ===================================================================
     * MÉTHODES DE PRÉPARATION HARMONISÉES - TOUTES IDENTIQUES
     * ===================================================================
     */

    /**
     * ✅ MÉTHODE UNIFIÉE CORRIGÉE : Récupérer les données du mandataire
     * CORRECTION : Cherche D'ABORD dans $dossier->donnees_supplementaires
     * puis en fallback dans DossierOperation
     */
    private function getMandataireDataUnified(Dossier $dossier)
    {
        try {
            Log::info('🔍 Récupération données mandataire unifiées - VERSION CORRIGÉE', [
                'dossier_id' => $dossier->id
            ]);

            // ✅ PRIORITÉ 1 : Chercher directement dans $dossier->donnees_supplementaires
            $donneesSupplementaires = null;

            if (!empty($dossier->donnees_supplementaires)) {
                Log::info('📦 Données supplémentaires trouvées dans le dossier');

                // Gérer le cas où c'est une string JSON ou un array
                if (is_string($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning('⚠️ Erreur décodage JSON donnees_supplementaires du dossier: ' . json_last_error_msg());
                        $donneesSupplementaires = null;
                    }
                } elseif (is_array($dossier->donnees_supplementaires)) {
                    $donneesSupplementaires = $dossier->donnees_supplementaires;
                }

                // Extraire les données du demandeur avec recherche flexible
                if ($donneesSupplementaires) {
                    $mandataireKeys = ['demandeur', 'declarant', 'mandataire', 'responsable', 'dirigeant', 'representant'];

                    foreach ($mandataireKeys as $key) {
                        if (isset($donneesSupplementaires[$key]) && is_array($donneesSupplementaires[$key])) {
                            Log::info("✅ Données mandataire trouvées dans dossier.donnees_supplementaires sous clé: {$key}", [
                                'data' => $donneesSupplementaires[$key]
                            ]);
                            return $donneesSupplementaires[$key];
                        }
                    }
                }
            }

            // ✅ PRIORITÉ 2 (FALLBACK) : Chercher dans DossierOperation
            Log::info('🔄 Recherche fallback dans DossierOperation...');

            if (class_exists('\App\Models\DossierOperation')) {
                $operationCreation = \App\Models\DossierOperation::where('dossier_id', $dossier->id)
                    ->where('type_operation', \App\Models\DossierOperation::TYPE_CREATION ?? 'creation')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($operationCreation) {
                    $donneesApres = $operationCreation->donnees_apres;

                    if (is_array($donneesApres) && isset($donneesApres['donnees_supplementaires'])) {
                        $donneesSupp = $donneesApres['donnees_supplementaires'];

                        if (is_string($donneesSupp)) {
                            $donneesSupp = json_decode($donneesSupp, true);
                        }

                        if (is_array($donneesSupp)) {
                            $mandataireKeys = ['demandeur', 'declarant', 'mandataire', 'responsable', 'dirigeant', 'representant'];

                            foreach ($mandataireKeys as $key) {
                                if (isset($donneesSupp[$key]) && is_array($donneesSupp[$key])) {
                                    Log::info("✅ Données mandataire trouvées dans DossierOperation sous clé: {$key}");
                                    return $donneesSupp[$key];
                                }
                            }
                        }
                    }
                }
            }

            // ✅ PRIORITÉ 3 (FALLBACK) : Utiliser le premier fondateur
            Log::info('🔄 Recherche fallback dans les fondateurs...');

            if ($dossier->organisation && $dossier->organisation->fondateurs) {
                $premierFondateur = $dossier->organisation->fondateurs->first();

                if ($premierFondateur) {
                    Log::info('✅ Utilisation du premier fondateur comme mandataire');
                    return [
                        'nom' => $premierFondateur->nom ?? 'Non disponible',
                        'prenom' => $premierFondateur->prenom ?? '',
                        'telephone' => $premierFondateur->telephone ?? 'Non renseigné',
                        'email' => $premierFondateur->email ?? 'Non renseigné',
                        'nip' => $premierFondateur->nip ?? 'Non renseigné',
                        'civilite' => $premierFondateur->civilite ?? 'M',
                        'adresse' => $premierFondateur->adresse ?? ($dossier->organisation->siege_social ?? 'Libreville'),
                        'nationalite' => $premierFondateur->nationalite ?? 'gabonaise',
                        'fonction' => $premierFondateur->fonction ?? 'Fondateur'
                    ];
                }
            }

            Log::warning('❌ Aucune donnée de mandataire trouvée - utilisation des valeurs par défaut');
            return $this->getDefaultMandataireData();

        } catch (\Exception $e) {
            Log::error('❌ Erreur getMandataireDataUnified', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);

            return $this->getDefaultMandataireData();
        }
    }

    /**
     * ✅ DONNÉES PAR DÉFAUT UNIFIÉES
     */
    private function getDefaultMandataireData()
    {
        return [
            'nom' => 'Non disponible',
            'prenom' => '',
            'email' => 'Non renseigné',
            'telephone' => 'Non renseigné',
            'nip' => 'Non renseigné',
            'adresse' => 'Libreville',
            'nationalite' => 'gabonaise',
            'profession' => 'Non renseignée',
            'civilite' => 'M',
            'role' => 'Représentant'
        ];
    }

    /**
     * ✅ QR CODE UNIFIÉ
     */
    private function getOrGenerateQrCodeUnified(Dossier $dossier)
    {
        try {
            // Chercher un QR Code existant
            $qrCode = QrCode::where('verifiable_type', 'App\\Models\\Dossier')
                ->where('verifiable_id', $dossier->id)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->first();

            // Vérifier la validité du QR Code existant
            if ($qrCode) {
                // Régénérer si SVG manquant
                if (empty($qrCode->svg_content) && empty($qrCode->png_base64)) {
                    Log::info('QR Code existant mais incomplet, régénération...', [
                        'qr_code_id' => $qrCode->id
                    ]);

                    $qrCodeService = app(QrCodeService::class);
                    $qrCodeService->regenerateForPdf($qrCode);
                    $qrCode->refresh();
                }

                return $qrCode;
            }

            // Si pas de QR Code du tout, en générer un nouveau
            if (!$qrCode) {
                Log::info('Aucun QR Code trouvé, génération...', [
                    'dossier_id' => $dossier->id
                ]);

                $qrCodeService = app(QrCodeService::class);
                $qrCode = $qrCodeService->generateForDossier($dossier);
            }

            return $qrCode;

        } catch (\Exception $e) {
            Log::error('Erreur gestion QR Code unifié', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * ✅ NUMÉROTATION UNIFIÉE
     */
    private function generateNumeroAdministratifUnified(Dossier $dossier)
    {
        $sequence = $dossier->numero_dossier;
        return "{$sequence}/MISD/SG/DGELP/DPPALC";
    }

    /**
     * ===================================================================
     * MÉTHODES DE PRÉPARATION DES DONNÉES - VERSION HARMONISÉE
     * ===================================================================
     */

    /**
     * ✅ ACCUSÉ DE RÉCEPTION - VERSION HARMONISÉE
     */
    private function prepareAccuseData(Dossier $dossier)
    {
        try {
            $organisation = $dossier->organisation;

            Log::info('🚀 Préparation données accusé - version harmonisée', [
                'dossier_id' => $dossier->id,
                'organisation_nom' => $organisation->nom
            ]);

            // ✅ RÉCUPÉRER LES DONNÉES DU MANDATAIRE (méthode unifiée corrigée)
            $mandataireData = $this->getMandataireDataUnified($dossier);

            // ✅ QR CODE (méthode unifiée)
            $qrCode = $this->getOrGenerateQrCodeUnified($dossier);

            // ✅ FORMATAGE UNIFIÉ DES DONNÉES
            $nomCompletMandataire = $this->formatNomCompletUnified($mandataireData);
            $telephoneMandataire = $this->formatTelephoneUnified($mandataireData);
            $civilite = $this->getCiviliteUnified($mandataireData);
            $domicileMandataire = $this->formatAdresseUnified($mandataireData, $organisation);
            $nationaliteMandataire = $mandataireData['nationalite'] ?? 'gabonaise';

            // Téléphone de l'organisation (fallback)
            $telephoneOrganisation = $this->formatTelephoneOrganisation($organisation);

            // ✅ NUMÉROTATION UNIFIÉE
            $numeroAdministratif = $this->generateNumeroAdministratifUnified($dossier);

            // ✅ STRUCTURE DE DONNÉES UNIFIÉE
            $data = [
                // Informations organisation
                'nom_organisation' => $organisation->nom,
                'sigle_organisation' => $organisation->sigle,
                'type_organisation' => $organisation->type,

                // ✅ INFORMATIONS MANDATAIRE UNIFIÉES
                'civilite' => $civilite,
                'nom_prenom' => $nomCompletMandataire,
                'nationalite' => $nationaliteMandataire,
                'domicile' => $domicileMandataire,
                'telephone' => $telephoneMandataire,

                // Informations organisation complètes
                'org_telephone' => $telephoneOrganisation,
                'org_email' => $organisation->email ?? 'Non renseigné',
                'org_adresse' => $this->formatAdresseOrganisation($organisation),

                // ✅ NUMÉROTATION UNIFIÉE
                'numero_administratif' => $numeroAdministratif,
                'date_generation' => now()->format('d/m/Y'),

                // ✅ QR CODE UNIFIÉ
                'qr_code' => $qrCode,

                // Métadonnées
                'dossier' => $dossier,
                'generated_at' => now()
            ];

            Log::info('✅ Données accusé préparées (version harmonisée)', [
                'dossier_id' => $dossier->id,
                'nom_prenom' => $data['nom_prenom'],
                'telephone' => $data['telephone'],
                'qr_code_present' => $qrCode ? 'Oui' : 'Non'
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error('❌ Erreur préparation données accusé harmonisé', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);

            // Retourner données minimales en cas d'erreur
            return $this->getMinimalDataFallback($dossier);
        }
    }

    /**
     * ✅ RÉCÉPISSÉ PROVISOIRE - VERSION HARMONISÉE (IDENTIQUE À L'ACCUSÉ)
     */
    private function prepareRecepisseProvisoireDataHarmonise(Dossier $dossier)
    {
        try {
            $organisation = $dossier->organisation;

            Log::info('🚀 Préparation données récépissé provisoire - version harmonisée', [
                'dossier_id' => $dossier->id,
                'organisation_nom' => $organisation->nom
            ]);

            // ✅ UTILISER LA MÊME LOGIQUE QUE L'ACCUSÉ
            $mandataireData = $this->getMandataireDataUnified($dossier);
            $qrCode = $this->getOrGenerateQrCodeUnified($dossier);

            // ✅ FORMATAGE IDENTIQUE À L'ACCUSÉ
            $nomCompletMandataire = $this->formatNomCompletUnified($mandataireData);
            $telephoneMandataire = $this->formatTelephoneUnified($mandataireData);
            $civilite = $this->getCiviliteUnified($mandataireData);
            $domicileMandataire = $this->formatAdresseUnified($mandataireData, $organisation);
            $nationaliteMandataire = $mandataireData['nationalite'] ?? 'gabonaise';

            $telephoneOrganisation = $this->formatTelephoneOrganisation($organisation);
            $numeroAdministratif = $this->generateNumeroAdministratifUnified($dossier);

            // ✅ STRUCTURE DE DONNÉES IDENTIQUE À L'ACCUSÉ
            $data = [
                // Informations organisation (identique à l'accusé)
                'nom_organisation' => $organisation->nom,
                'sigle_organisation' => $organisation->sigle,
                'type_organisation' => $organisation->type,

                // ✅ VARIABLES IDENTIQUES À L'ACCUSÉ
                'civilite' => $civilite,
                'nom_prenom' => $nomCompletMandataire,
                'nationalite' => $nationaliteMandataire,
                'domicile' => $domicileMandataire,
                'telephone' => $telephoneMandataire,

                // Informations organisation (identique à l'accusé)
                'org_telephone' => $telephoneOrganisation,
                'org_email' => $organisation->email ?? 'Non renseigné',
                'org_adresse' => $this->formatAdresseOrganisation($organisation),

                // ✅ NUMÉROTATION IDENTIQUE
                'numero_administratif' => $numeroAdministratif,
                'numero_reference' => $numeroAdministratif, // Alias pour compatibilité
                'date_generation' => now()->format('d/m/Y'),

                // ✅ QR CODE IDENTIQUE
                'qr_code' => $qrCode,

                // Variables spécifiques au récépissé (en plus)
                'organisation' => $organisation,
                'numero_accuse_reception' => str_pad($dossier->id, 3, '0', STR_PAD_LEFT),
                'date_accuse_reception' => ($dossier->created_at ?? now())->format('d F Y'),
                'date_emission' => now()->format('d F Y'),
                'ministre_nom' => 'Hermann IMMONGAULT',
                'adresse_siege' => $this->formatAdresseOrganisation($organisation),
                'boite_postale' => $organisation->boite_postale ?? '',
                'fonction_dirigeant' => $this->getFonctionDirigeantProvisoire($organisation->type, $mandataireData['civilite'] ?? 'M'),

                // Métadonnées
                'dossier' => $dossier,
                'generated_at' => now()
            ];

            Log::info('✅ Données récépissé provisoire préparées (version harmonisée)', [
                'dossier_id' => $dossier->id,
                'nom_prenom' => $data['nom_prenom'],
                'telephone' => $data['telephone'],
                'qr_code_present' => $qrCode ? 'Oui' : 'Non'
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error('❌ Erreur préparation récépissé provisoire harmonisé', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);

            return $this->getMinimalDataFallback($dossier);
        }
    }

    /**
     * ✅ RÉCÉPISSÉ DÉFINITIF - VERSION HARMONISÉE
     */
    private function prepareRecepisseDefinitifDataHarmonise(Dossier $dossier)
    {
        try {
            $organisation = $dossier->organisation;

            // ✅ UTILISER LA MÊME BASE QUE L'ACCUSÉ
            $mandataireData = $this->getMandataireDataUnified($dossier);
            $qrCode = $this->getOrGenerateQrCodeUnified($dossier);

            // ✅ FORMATAGE IDENTIQUE
            $nomCompletMandataire = $this->formatNomCompletUnified($mandataireData);
            $telephoneMandataire = $this->formatTelephoneUnified($mandataireData);
            $civilite = $this->getCiviliteUnified($mandataireData);
            $domicileMandataire = $this->formatAdresseUnified($mandataireData, $organisation);
            $nationaliteMandataire = $mandataireData['nationalite'] ?? 'gabonaise';

            $telephoneOrganisation = $this->formatTelephoneOrganisation($organisation);
            $numeroAdministratif = $this->generateNumeroAdministratifUnified($dossier);

            // ✅ STRUCTURE DE BASE IDENTIQUE + SPÉCIFICITÉS RÉCÉPISSÉ DÉFINITIF
            $data = [
                // Base identique à l'accusé
                'nom_organisation' => $organisation->nom,
                'sigle_organisation' => $organisation->sigle,
                'type_organisation' => $organisation->type,
                'civilite' => $civilite,
                'nom_prenom' => $nomCompletMandataire,
                'nationalite' => $nationaliteMandataire,
                'domicile' => $domicileMandataire,
                'telephone' => $telephoneMandataire,
                'org_telephone' => $telephoneOrganisation,
                'org_email' => $organisation->email ?? 'Non renseigné',
                'numero_administratif' => $numeroAdministratif,
                'date_generation' => now()->format('d/m/Y'),
                'qr_code' => $qrCode,

                // Spécificités récépissé définitif
                'numero_dossier' => $dossier->numero_dossier,
                'numero_recepisse' => $dossier->numero_dossier,
                'date_approbation' => $dossier->validated_at ?
                    $dossier->validated_at->locale('fr_FR')->isoFormat('DD MMMM YYYY') :
                    Carbon::now()->locale('fr_FR')->isoFormat('DD MMMM YYYY'),
                'objet_organisation' => $organisation->objet ?? 'Non spécifié',
                'adresse_siege' => $this->formatAdresseOrganisation($organisation),
                'telephone_organisation' => $telephoneOrganisation,
                'type_organisation_label' => $this->getTypeOrganisationLabel($organisation->type),
                'dirigeants' => $this->prepareDirigeants($organisation),
                'loi_reference' => $this->getLoiReference($organisation->type),
                'ministre_nom' => 'Hermann IMMONGAULT',
                'pieces_annexees' => $this->getPiecesAnnexees($organisation->type),
                'prescriptions' => $this->getPrescriptionsLegales($organisation->type),

                // Métadonnées
                'dossier' => $dossier,
                'generated_at' => now()
            ];

            return $data;

        } catch (\Exception $e) {
            Log::error('❌ Erreur préparation récépissé définitif harmonisé', [
                'dossier_id' => $dossier->id,
                'error' => $e->getMessage()
            ]);

            return $this->getMinimalDataFallback($dossier);
        }
    }

    /**
     * ✅ DONNÉES MINIMALES EN CAS D'ERREUR
     */
    private function getMinimalDataFallback(Dossier $dossier)
    {
        return [
            'nom_organisation' => $dossier->organisation->nom ?? 'Organisation',
            'sigle_organisation' => $dossier->organisation->sigle ?? '',
            'type_organisation' => $dossier->organisation->type ?? 'association',
            'civilite' => 'Monsieur/Madame',
            'nom_prenom' => '❌ ERREUR - Voir logs système',
            'nationalite' => 'gabonaise',
            'domicile' => 'LIBREVILLE, GABON',
            'telephone' => '+241 XX XX XX XX',
            'org_telephone' => '+241 XX XX XX XX',
            'org_email' => 'contact@organisation.ga',
            'numero_administratif' => 'XXXX/MISD/SG/DGELP/DPPALC',
            'date_generation' => now()->format('d/m/Y'),
            'qr_code' => null,
            'dossier' => $dossier,
            'generated_at' => now()
        ];
    }

    /**
     * ===================================================================
     * MÉTHODES DE FORMATAGE UNIFIÉES
     * ===================================================================
     */

    /**
     * ✅ FORMATAGE UNIFIÉ DU NOM COMPLET
     */
    private function formatNomCompletUnified($mandataireData)
    {
        $nom = trim($mandataireData['nom'] ?? '');
        $prenom = trim($mandataireData['prenom'] ?? '');

        if ($nom !== '' && $prenom !== '') {
            return $prenom . ' ' . $nom;
        } elseif ($nom !== '') {
            return $nom;
        } elseif ($prenom !== '') {
            return $prenom;
        }

        return 'Non disponible';
    }

    /**
     * ✅ FORMATAGE UNIFIÉ DE LA CIVILITÉ
     */
    private function getCiviliteUnified($mandataireData)
    {
        // Vérifier s'il y a une civilité explicite
        $civiliteExplicite = $mandataireData['civilite'] ?? $mandataireData['sexe'] ?? $mandataireData['genre'] ?? null;

        if ($civiliteExplicite) {
            switch (strtoupper($civiliteExplicite)) {
                case 'F':
                case 'FEMME':
                case 'MME':
                case 'MADAME':
                    return 'Madame';
                case 'MLLE':
                case 'MADEMOISELLE':
                    return 'Mademoiselle';
                case 'M':
                case 'HOMME':
                case 'MONSIEUR':
                default:
                    return 'Monsieur';
            }
        }

        // Déduire du prénom si pas de civilité explicite
        $prenom = strtolower($mandataireData['prenom'] ?? '');
        $prenomsFemin = ['marie', 'jeanne', 'louise', 'claire', 'sophie', 'florence', 'catherine', 'nicole', 'pascale'];

        foreach ($prenomsFemin as $prenomFem) {
            if (strpos($prenom, $prenomFem) !== false) {
                return 'Madame';
            }
        }

        return 'Monsieur';
    }

    /**
     * ✅ FORMATAGE UNIFIÉ DU TÉLÉPHONE
     */
    private function formatTelephoneUnified($mandataireData)
    {
        $telephone = $mandataireData['telephone'] ?? null;

        if (empty($telephone) || $telephone === 'Non renseigné') {
            return 'Non renseigné';
        }

        // Nettoyer le numéro
        $clean = preg_replace('/[^0-9]/', '', $telephone);

        // Vérifier si c'est un numéro gabonais valide
        if (strlen($clean ?? '') >= 8 && strlen($clean ?? '') <= 9) {
            // Formater avec indicatif +241
            if (strlen($clean ?? '') === 8) {
                return '+241 ' . substr($clean, 0, 2) . ' ' . substr($clean, 2, 3) . ' ' . substr($clean, 5, 3);
            } elseif (strlen($clean ?? '') === 9) {
                return '+241 ' . substr($clean, 0, 1) . ' ' . substr($clean, 1, 2) . ' ' . substr($clean, 3, 3) . ' ' . substr($clean, 6, 3);
            }
        }

        return $telephone; // Retourner tel quel si format non reconnu
    }

    /**
     * ✅ FORMATAGE UNIFIÉ DE L'ADRESSE
     */
    private function formatAdresseUnified($mandataireData, $organisation)
    {
        // Priorité 1 : Adresse personnelle du mandataire
        if (!empty($mandataireData['adresse']) && $mandataireData['adresse'] !== 'Non renseigné') {
            return $mandataireData['adresse'];
        }

        // Priorité 2 : Domicile du mandataire
        if (!empty($mandataireData['domicile']) && $mandataireData['domicile'] !== 'Non renseigné') {
            return $mandataireData['domicile'];
        }

        // Priorité 3 : Siège social de l'organisation
        if ($organisation && !empty($organisation->siege_social)) {
            return $organisation->siege_social;
        }

        // Priorité 4 : Construire depuis les données géo de l'organisation
        if ($organisation) {
            $parts = [];
            if ($organisation->quartier)
                $parts[] = $organisation->quartier;
            if ($organisation->ville_commune)
                $parts[] = $organisation->ville_commune;
            if ($organisation->province)
                $parts[] = $organisation->province;

            if (!empty($parts)) {
                return implode(', ', $parts);
            }
        }

        return 'Libreville, Gabon';
    }

    /**
     * ===================================================================
     * MÉTHODES UTILITAIRES CONSERVÉES
     * ===================================================================
     */

    /**
     * Formater l'adresse de l'organisation
     */
    private function formatAdresseOrganisation($organisation)
    {
        $adresse = [];

        if ($organisation->siege_social) {
            $adresse[] = $organisation->siege_social;
        }

        if ($organisation->quartier) {
            $adresse[] = 'Quartier ' . $organisation->quartier;
        } elseif ($organisation->village) {
            $adresse[] = 'Village ' . $organisation->village;
        }

        if ($organisation->lieu_dit) {
            $adresse[] = $organisation->lieu_dit;
        }

        if ($organisation->ville_commune) {
            $adresse[] = $organisation->ville_commune;
        }

        if ($organisation->arrondissement) {
            $adresse[] = $organisation->arrondissement . ' arrondissement';
        }

        if ($organisation->prefecture) {
            $adresse[] = $organisation->prefecture;
        }

        if ($organisation->province) {
            $adresse[] = 'Province ' . $organisation->province;
        }

        return !empty($adresse) ? implode(', ', $adresse) : 'Libreville, Gabon';
    }

    /**
     * Formatage du téléphone de l'organisation
     */
    private function formatTelephoneOrganisation($organisation)
    {
        $telephones = [];

        if ($organisation->telephone && $organisation->telephone !== 'Non renseigné') {
            $telephones[] = $this->formatTelephoneUnified(['telephone' => $organisation->telephone]);
        }

        if (
            $organisation->telephone_secondaire &&
            $organisation->telephone_secondaire !== $organisation->telephone &&
            $organisation->telephone_secondaire !== 'Non renseigné'
        ) {
            $telephones[] = $this->formatTelephoneUnified(['telephone' => $organisation->telephone_secondaire]);
        }

        return !empty($telephones) ? implode(' / ', $telephones) : 'Non renseigné';
    }

    /**
     * Fonction dirigeant provisoire
     */
    private function getFonctionDirigeantProvisoire($type, $civilite)
    {
        $fonctions = [
            'parti_politique' => 'Secrétaire Général',
            'association' => 'Président',
            'ong' => 'Directeur Exécutif',
            'confession_religieuse' => 'Responsable'
        ];

        $fonction = $fonctions[$type] ?? 'Représentant';

        // Féminiser si nécessaire
        if (in_array(strtoupper($civilite), ['F', 'MME', 'MADAME', 'MLLE'])) {
            $fonction = str_replace('Président', 'Présidente', $fonction);
            $fonction = str_replace('Directeur', 'Directrice', $fonction);
            $fonction = str_replace('Secrétaire Général', 'Secrétaire Générale', $fonction);
        }

        return $fonction;
    }

    /**
     * Label du type d'organisation
     */
    private function getTypeOrganisationLabel($type)
    {
        $labels = [
            'association' => 'Association',
            'ong' => 'Organisation Non Gouvernementale (ONG)',
            'parti_politique' => 'Parti Politique',
            'confession_religieuse' => 'Confession Religieuse'
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Référence légale
     */
    private function getLoiReference($type)
    {
        $lois = [
            'association' => 'Loi n°35/62 du 10 décembre 1962 relative aux associations',
            'ong' => 'Loi n°001/2005 du 4 février 2005 relative aux ONG',
            'parti_politique' => 'Loi n°016/2025 du 27 juin 2025 relative aux partis politiques',
            'confession_religieuse' => 'Loi n°XX/XXXX relative aux confessions religieuses'
        ];

        return $lois[$type] ?? 'Législation en vigueur';
    }

    /**
     * Préparer la liste des dirigeants
     */
    private function prepareDirigeants($organisation)
    {
        $dirigeants = [];

        if ($organisation->fondateurs) {
            foreach ($organisation->fondateurs->take(5) as $fondateur) {
                $dirigeants[] = [
                    'nom_complet' => trim(($fondateur->prenom ?? '') . ' ' . ($fondateur->nom ?? '')),
                    'fonction' => $fondateur->fonction ?? 'Membre fondateur',
                    'nationalite' => $fondateur->nationalite ?? 'gabonaise'
                ];
            }
        }

        return $dirigeants;
    }

    /**
     * Pièces annexées
     */
    private function getPiecesAnnexees($type)
    {
        $pieces = [
            'parti_politique' => [
                'Statuts du parti',
                'Procès-verbal de l\'assemblée constitutive',
                'Liste des membres fondateurs',
                'Programme politique'
            ],
            'association' => [
                'Statuts de l\'association',
                'Procès-verbal de l\'assemblée générale constitutive',
                'Liste des membres du bureau'
            ],
            'ong' => [
                'Statuts de l\'ONG',
                'Procès-verbal de l\'assemblée constitutive',
                'Liste des membres fondateurs',
                'Plan d\'action'
            ],
            'confession_religieuse' => [
                'Statuts',
                'Procès-verbal de constitution',
                'Liste des responsables'
            ]
        ];

        return $pieces[$type] ?? ['Documents constitutifs'];
    }

    /**
     * Prescriptions légales
     */
    private function getPrescriptionsLegales($type)
    {
        $prescriptions = [
            'parti_politique' => 'Le parti politique est tenu de se conformer aux dispositions de la loi n°016/2025 du 27 juin 2025 relative aux partis politiques en République Gabonaise.',
            'association' => 'L\'association est tenue de se conformer aux dispositions de la loi n°35/62 du 10 décembre 1962 relative aux associations.',
            'ong' => 'L\'ONG est tenue de se conformer aux dispositions de la loi n°001/2005 du 4 février 2005.',
            'confession_religieuse' => 'La confession religieuse est tenue de se conformer à la législation en vigueur.'
        ];

        return $prescriptions[$type] ?? 'Se conformer à la législation en vigueur.';
    }
}