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
     * Générer l'accusé de réception PDF
     */
    public function generateAccuseReception(Dossier $dossier)
    {
        try {
            // Préparer les données pour le template
            $data = $this->prepareAccuseData($dossier);
            
            // Générer le PDF avec DomPDF
            $pdf = Pdf::loadView('admin.pdf.accuse-reception', $data);
            
            // Configuration du PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);
            
            return $pdf;
            
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
            throw $e;
        }
    }
    
    /**
     * Générer le récépissé définitif PDF - VERSION HARMONISÉE
     */
    public function generateRecepisseDefinitif(Dossier $dossier)
    {
        try {
            // Vérifier que le dossier est approuvé
            if ($dossier->statut !== 'approuve') {
                throw new \Exception('Le récépissé ne peut être généré que pour les dossiers approuvés');
            }
            
            // ✅ HARMONISATION : Utiliser la même base que l'accusé
            $data = $this->prepareRecepisseDefinitifDataHarmonise($dossier);
            
            // Générer le PDF avec DomPDF
            $pdf = Pdf::loadView('admin.pdf.recepisse-definitif', $data);
            
            // Configuration du PDF (identique)
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);
            
            return $pdf;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération récépissé définitif PDF: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération du récépissé définitif: ' . $e->getMessage());
        }
    }

    /**
     * ===================================================================
     * MÉTHODES DE PRÉPARATION HARMONISÉES - TOUTES IDENTIQUES
     * ===================================================================
     */

    /**
     * ✅ MÉTHODE UNIFIÉE : Récupérer les données du mandataire
     * Utilisée par TOUS les documents PDF
     */
    private function getMandataireDataUnified(Dossier $dossier)
    {
        try {
            Log::info('🔍 Récupération données mandataire unifiées', [
                'dossier_id' => $dossier->id
            ]);
            
            // Récupérer l'opération de création du dossier
            $operationCreation = \App\Models\DossierOperation::where('dossier_id', $dossier->id)
                ->where('type_operation', \App\Models\DossierOperation::TYPE_CREATION)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$operationCreation) {
                Log::warning('⚠️ Aucune opération de création trouvée');
                return $this->getDefaultMandataireData();
            }
            
            $donneesApres = $operationCreation->donnees_apres;
            
            if (!is_array($donneesApres) || !isset($donneesApres['donnees_supplementaires'])) {
                Log::warning('⚠️ donnees_supplementaires manquant dans donnees_apres');
                return $this->getDefaultMandataireData();
            }
            
            // Décoder donnees_supplementaires si c'est une string JSON
            $donneesSupplementaires = $donneesApres['donnees_supplementaires'];
            
            if (is_string($donneesSupplementaires)) {
                $donneesSupplementaires = json_decode($donneesSupplementaires, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('❌ Erreur décodage JSON donnees_supplementaires: ' . json_last_error_msg());
                    return $this->getDefaultMandataireData();
                }
            }
            
            // Extraire les données du demandeur avec recherche flexible
            $mandataireKeys = ['demandeur', 'declarant', 'mandataire', 'responsable', 'dirigeant', 'representant'];
            $mandataireData = null;
            
            foreach ($mandataireKeys as $key) {
                if (isset($donneesSupplementaires[$key]) && is_array($donneesSupplementaires[$key])) {
                    $mandataireData = $donneesSupplementaires[$key];
                    Log::info("✅ Données mandataire trouvées sous clé: {$key}");
                    break;
                }
            }
            
            if (!$mandataireData) {
                Log::warning('❌ Aucune donnée de mandataire trouvée');
                return $this->getDefaultMandataireData();
            }
            
            return $mandataireData;
            
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
            'prenom' => 'Non disponible',
            'email' => 'Non disponible',
            'telephone' => 'Non disponible',
            'nip' => 'Non disponible',
            'adresse' => 'Libreville',
            'nationalite' => 'gabonaise',
            'profession' => 'Non renseignée',
            'civilite' => 'M',
            'role' => 'Représentant'
        ];
    }

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
            return $mandataireData['adresse'] . ', GABON';
        }
        
        // Priorité 2 : Adresse de l'organisation
        return $this->formatAdresseOrganisation($organisation);
    }

    /**
     * ✅ QR CODE UNIFIÉ POUR TOUS LES DOCUMENTS
     */
    private function getOrGenerateQrCodeUnified(Dossier $dossier)
    {
        try {
            // Vérifier s'il existe déjà un QR Code pour ce dossier
            $qrCode = QrCode::where('verifiable_type', Dossier::class)
                ->where('verifiable_id', $dossier->id)
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Si QR Code existe mais n'a pas de SVG, le regénérer
            if ($qrCode && empty($qrCode->svg_content)) {
                Log::info('QR Code sans SVG trouvé, regénération...', [
                    'qr_code_id' => $qrCode->id,
                    'dossier_id' => $dossier->id
                ]);
                
                // Utiliser le service QR Code pour regénérer le SVG
                $qrCodeService = app(QrCodeService::class);
                $updatedQrCode = $qrCodeService->regenerateQrCodeSvg($qrCode);
                
                // Utiliser le QR Code mis à jour ou l'original en cas d'échec
                $qrCode = $updatedQrCode ?: $qrCode;
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
            
            // ✅ RÉCUPÉRER LES DONNÉES DU MANDATAIRE (méthode unifiée)
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
            $adresse[] = $organisation->arrondissement . 'arrondissement';
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
        
        if ($organisation->telephone_secondaire && 
            $organisation->telephone_secondaire !== $organisation->telephone &&
            $organisation->telephone_secondaire !== 'Non renseigné') {
            $telephones[] = $this->formatTelephoneUnified(['telephone' => $organisation->telephone_secondaire]);
        }

        return !empty($telephones) ? implode(' / ', $telephones) : 'Non renseigné';
    }

    /**
     * Obtenir le libellé du type d'organisation
     */
    private function getTypeOrganisationLabel($type)
    {
        $types = [
            'association' => 'Association',
            'ong' => 'Organisation Non Gouvernementale (ONG)',
            'parti_politique' => 'Parti Politique',
            'confession_religieuse' => 'Organisation Religieuse',
        ];
        
        return $types[$type] ?? 'Organisation';
    }

    /**
     * Obtenir la référence légale selon le type
     */
    private function getLoiReference($type)
    {
        $references = [
            'association' => 'loi n°35/62 du 10 décembre 1962',
            'ong' => 'loi n°35/62 du 10 décembre 1962',
            'parti_politique' => 'loi n°016/2025 du 27 juin 2025 relative aux partis politiques en République Gabonaise',
            'confession_religieuse' => 'loi n°35/62 du 10 décembre 1962',
        ];
        
        return $references[$type] ?? 'législation en vigueur';
    }

    /**
     * Déterminer la fonction dirigeant selon le type et le genre
     */
    private function getFonctionDirigeantProvisoire($type, $civilite = 'M')
    {
        $estFeminin = in_array(strtoupper($civilite), ['F', 'FEMME', 'MME', 'MADAME']);
        
        $fonctions = [
            'association' => $estFeminin ? 'Présidente' : 'Président',
            'ong' => $estFeminin ? 'Présidente' : 'Président',
            'parti_politique' => $estFeminin ? 'Présidente' : 'Président',
            'confession_religieuse' => $estFeminin ? 'Responsable Spirituelle' : 'Responsable Spirituel'
        ];

        return $fonctions[$type] ?? ($estFeminin ? 'Présidente' : 'Président');
    }

    /**
     * Préparer les dirigeants pour le récépissé définitif
     */
    private function prepareDirigeants($organisation)
    {
        $dirigeants = [];
        
        // Récupérer les fondateurs/dirigeants principaux
        foreach ($organisation->fondateurs->take(7) as $fondateur) {
            $poste = $this->determinerPoste($fondateur, $organisation->type);
            $dirigeants[] = [
                'poste' => $poste,
                'nom_prenom' => "{$fondateur->nom} {$fondateur->prenom}",
            ];
        }
        
        // Compléter avec des postes par défaut si nécessaire
        $postesDefaut = $this->getPostesDefaut($organisation->type);
        while (count($dirigeants) < 7 && count($dirigeants) < count($postesDefaut)) {
            $dirigeants[] = [
                'poste' => $postesDefaut[count($dirigeants)],
                'nom_prenom' => 'Non désigné',
            ];
        }
        
        return $dirigeants;
    }

    /**
     * Déterminer le poste d'un dirigeant
     */
    private function determinerPoste($fondateur, $typeOrganisation)
    {
        static $index = 0;
        $postes = $this->getPostesDefaut($typeOrganisation);
        
        return $postes[$index++] ?? 'Membre du Bureau';
    }

    /**
     * Obtenir les postes par défaut selon le type
     */
    private function getPostesDefaut($type)
    {
        $postes = [
            'association' => [
                'Président(e)',
                'Vice-Président(e)',
                'Secrétaire Général(e)',
                'Secrétaire Général(e) Adjoint(e)',
                'Trésorier Général',
                'Trésorier Général Adjoint',
                'Commissaire aux Comptes',
            ],
            'parti_politique' => [
                'Président du Parti',
                'Secrétaire Général',
                'Trésorier Général',
                'Commissaire aux Comptes',
                'Responsable Communication',
                'Responsable Organisation',
                'Responsable Jeunesse',
            ],
        ];
        
        return $postes[$type] ?? $postes['association'];
    }

    /**
     * Obtenir les pièces annexées selon le type
     */
    private function getPiecesAnnexees($type)
    {
        return [
            'Statuts',
            'Procès-verbal de l\'assemblée constitutive',
            'Liste des membres du comité directeur',
            'Demande adressée au Ministre de l\'Intérieur',
            'Reçu de 10.000 frs CFA délivré par la Direction du Journal Officiel',
        ];
    }

    /**
     * Obtenir les prescriptions légales
     */
    private function getPrescriptionsLegales($type)
    {
        return [
            'Toutes modifications apportées aux statuts de l\'organisation et tous les changements survenus dans son administration ou sa direction devront être déclarés dans un délai d\'un mois.',
            'Un registre spécial doit être tenu au siège de l\'organisation et présenté sur demande aux autorités compétentes.',
            'L\'organisation doit respecter strictement les dispositions légales en vigueur sous peine de dissolution.',
        ];
    }
}