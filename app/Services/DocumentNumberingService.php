<?php

namespace App\Services;

use App\Models\DocumentGeneration;
use Illuminate\Support\Facades\DB;

/**
 * SERVICE DE NUMÉROTATION DES DOCUMENTS
 * 
 * Génère des numéros uniques pour chaque type de document
 * Format : TYPE-ANNEE-SEQUENCE
 * Exemples : RECEP-2025-00001, CERT-2025-00042
 * 
 * Projet : SGLP
 */
class DocumentNumberingService
{
    /**
     * Préfixes par type de document
     */
    protected array $prefixes = [
        'recepisse_provisoire' => 'RECEP-PROV',
        'recepisse_definitif' => 'RECEP-DEF',
        'certificat_enregistrement' => 'CERT',
        'attestation' => 'ATT',
        'notification_rejet' => 'REJ',
        'autre' => 'DOC',
    ];

    /**
     * Générer un numéro unique pour un document
     * 
     * @param string $typeDocument Type de document
     * @param int|null $organisationId ID organisation (optionnel)
     * @return string Numéro unique
     */
    public function generate(string $typeDocument, ?int $organisationId = null): string
    {
        $prefix = $this->prefixes[$typeDocument] ?? 'DOC';
        $year = now()->year;
        
        // Obtenir le prochain numéro de séquence
        $sequence = $this->getNextSequence($typeDocument, $year);
        
        // Format : PREFIX-ANNEE-SEQUENCE (ex: RECEP-PROV-2025-00001)
        return sprintf('%s-%d-%05d', $prefix, $year, $sequence);
    }

    /**
     * Obtenir le prochain numéro de séquence
     * 
     * @param string $typeDocument Type de document
     * @param int $year Année
     * @return int Prochain numéro
     */
    protected function getNextSequence(string $typeDocument, int $year): int
    {
        $prefix = $this->prefixes[$typeDocument] ?? 'DOC';
        
        // Chercher le dernier numéro pour ce type et cette année
        $lastDocument = DocumentGeneration::where('type_document', $typeDocument)
            ->where('numero_document', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('numero_document', 'desc')
            ->first();
        
        if (!$lastDocument) {
            return 1; // Premier document de l'année
        }
        
        // Extraire le numéro de séquence du dernier document
        // Format : PREFIX-YEAR-XXXXX
        $parts = explode('-', $lastDocument->numero_document);
        $lastSequence = (int) end($parts);
        
        return $lastSequence + 1;
    }

    /**
     * Vérifier si un numéro existe déjà
     * 
     * @param string $numeroDocument Numéro à vérifier
     * @return bool
     */
    public function exists(string $numeroDocument): bool
    {
        return DocumentGeneration::where('numero_document', $numeroDocument)->exists();
    }

    /**
     * Valider le format d'un numéro
     * 
     * @param string $numeroDocument Numéro à valider
     * @return bool
     */
    public function isValidFormat(string $numeroDocument): bool
    {
        // Format attendu : PREFIX-YYYY-XXXXX
        return preg_match('/^[A-Z\-]+-\d{4}-\d{5}$/', $numeroDocument) === 1;
    }

    /**
     * Obtenir les statistiques de numérotation
     * 
     * @param int|null $year Année (null = année courante)
     * @return array
     */
    public function getStatistics(?int $year = null): array
    {
        $year = $year ?? now()->year;
        
        $stats = [];
        
        foreach ($this->prefixes as $type => $prefix) {
            $count = DocumentGeneration::where('type_document', $type)
                ->where('numero_document', 'LIKE', "{$prefix}-{$year}-%")
                ->count();
            
            $stats[$type] = [
                'type' => $type,
                'prefix' => $prefix,
                'count' => $count,
                'next_number' => $this->getNextSequence($type, $year),
            ];
        }
        
        return $stats;
    }
}