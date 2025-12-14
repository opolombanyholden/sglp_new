<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\View;

/**
 * MODÈLE DOCUMENT TEMPLATE
 * 
 * Représente un template de document officiel à générer pour :
 * - Un type d'organisation (Association, ONG, Parti, Confession)
 * - Un type d'opération (Création, Modification, Cessation)
 * - Une étape du workflow (Dépôt, Validation, Approbation)
 * 
 * @property int $id
 * @property int $organisation_type_id FK vers organisation_types
 * @property int|null $operation_type_id FK vers operation_types (null = tous types)
 * @property int|null $workflow_step_id FK vers workflow_steps (null = toutes étapes)
 * @property string $code Code unique du template
 * @property string $nom Nom du template
 * @property string $description Description
 * @property string $type_document Type (recepisse_provisoire, certificat, etc.)
 * @property string $template_path Chemin Blade du template
 * @property string|null $layout_path Layout parent Blade
 * @property array|null $variables Variables disponibles
 * @property array|null $required_variables Variables obligatoires
 * @property array|null $pdf_config Configuration PDF
 * @property bool $has_qr_code Inclure QR code
 * @property bool $has_watermark Inclure watermark
 * @property bool $has_signature Inclure signature
 * @property string|null $signature_image Chemin de la signature
 * @property bool $auto_generate Génération automatique
 * @property int|null $generation_delay_hours Délai avant génération auto
 * @property bool $is_active Template actif
 * @property array|null $metadata Données supplémentaires
 * 
 * Projet : SGLP
 * Compatible : PHP 7.3.29+, Laravel 10+
 */
class DocumentTemplate extends Model
{
    use HasFactory;

    /**
     * Table associée
     */
    protected $table = 'document_templates';

    /**
     * Colonnes assignables en masse
     */
    protected $fillable = [
        // Relations (3 dimensions)
        'organisation_type_id',
        'operation_type_id',
        'workflow_step_id',

        // Identification
        'code',
        'nom',
        'description',
        'type_document',

        // Fichiers templates
        'template_path',
        'layout_path',

        // Variables
        'variables',
        'required_variables',

        // Configuration PDF
        'pdf_config',

        // Options de génération
        'has_qr_code',
        'has_watermark',
        'has_signature',
        'signature_image',

        // Textes WYSIWYG
        'header_text',
        'signature_text',

        // Génération automatique
        'auto_generate',
        'generation_delay_hours',

        // Statut
        'is_active',
        'metadata',
    ];

    /**
     * Conversion automatique des types
     */
    protected $casts = [
        'variables' => 'array',
        'required_variables' => 'array',
        'pdf_config' => 'array',
        'metadata' => 'array',
        'has_qr_code' => 'boolean',
        'has_watermark' => 'boolean',
        'has_signature' => 'boolean',
        'auto_generate' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * ========================================
     * RELATIONS
     * ========================================
     */

    /**
     * Relation : Type d'organisation (DIMENSION 1)
     */
    public function organisationType(): BelongsTo
    {
        return $this->belongsTo(OrganisationType::class, 'organisation_type_id');
    }

    /**
     * Relation : Type d'opération (DIMENSION 2)
     */
    public function operationType(): BelongsTo
    {
        return $this->belongsTo(OperationType::class, 'operation_type_id');
    }

    /**
     * Relation : Étape du workflow (DIMENSION 3)
     */
    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    /**
     * Relation : Documents générés avec ce template
     */
    public function generations(): HasMany
    {
        return $this->hasMany(DocumentGeneration::class);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    /**
     * Scope : Templates actifs uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : Par type de document
     */
    public function scopeByTypeDocument($query, string $typeDocument)
    {
        return $query->where('type_document', $typeDocument);
    }

    /**
     * Scope : Recherche contextuelle (3 dimensions)
     * 
     * @param int $orgTypeId Type d'organisation
     * @param int|null $opTypeId Type d'opération (null = tous)
     * @param int|null $stepId Étape workflow (null = toutes)
     */
    public function scopeForContext($query, int $orgTypeId, ?int $opTypeId = null, ?int $stepId = null)
    {
        return $query->where('organisation_type_id', $orgTypeId)
            ->when($opTypeId, fn($q) => $q->where(function ($subQuery) use ($opTypeId) {
                $subQuery->where('operation_type_id', $opTypeId)
                    ->orWhereNull('operation_type_id'); // Templates génériques
            }))
            ->when($stepId, fn($q) => $q->where(function ($subQuery) use ($stepId) {
                $subQuery->where('workflow_step_id', $stepId)
                    ->orWhereNull('workflow_step_id'); // Templates génériques
            }))
            ->where('is_active', true);
    }

    /**
     * Scope : Templates avec génération automatique pour une étape
     */
    public function scopeAutoGenerate($query, int $stepId)
    {
        return $query->where('workflow_step_id', $stepId)
            ->where('auto_generate', true)
            ->where('is_active', true);
    }

    /**
     * Scope : Par type d'organisation
     */
    public function scopeForOrganisationType($query, int $orgTypeId)
    {
        return $query->where('organisation_type_id', $orgTypeId);
    }

    /**
     * Scope : Par type d'opération
     */
    public function scopeForOperationType($query, int $opTypeId)
    {
        return $query->where(function ($q) use ($opTypeId) {
            $q->where('operation_type_id', $opTypeId)
                ->orWhereNull('operation_type_id');
        });
    }

    /**
     * Scope : Par étape workflow
     */
    public function scopeForWorkflowStep($query, int $stepId)
    {
        return $query->where(function ($q) use ($stepId) {
            $q->where('workflow_step_id', $stepId)
                ->orWhereNull('workflow_step_id');
        });
    }

    /**
     * Scope : Recherche par nom/description
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nom', 'LIKE', "%{$search}%")
                ->orWhere('code', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('type_document', 'LIKE', "%{$search}%");
        });
    }

    /**
     * ========================================
     * MÉTHODES UTILITAIRES
     * ========================================
     */

    /**
     * Obtenir le chemin complet du template Blade
     */
    public function getFullTemplatePath(): string
    {
        return $this->template_path;
    }

    /**
     * Vérifier si doit générer automatiquement
     */
    public function shouldAutoGenerate(): bool
    {
        return $this->auto_generate && $this->workflow_step_id !== null;
    }

    /**
     * Vérifier si le template Blade existe
     */
    public function templateExists(): bool
    {
        return View::exists($this->template_path);
    }

    /**
     * Obtenir l'orientation PDF
     */
    public function getPdfOrientation(): string
    {
        return $this->pdf_config['orientation'] ?? 'portrait';
    }

    /**
     * Obtenir le format PDF
     */
    public function getPdfFormat(): string
    {
        return $this->pdf_config['format'] ?? 'a4';
    }

    /**
     * Obtenir les marges PDF
     */
    public function getPdfMargins(): array
    {
        return $this->pdf_config['margins'] ?? [
            'top' => 20,
            'bottom' => 20,
            'left' => 15,
            'right' => 15,
        ];
    }

    /**
     * Vérifier si le template a un fichier
     */
    public function hasFile(): bool
    {
        return !empty($this->template_path) && View::exists($this->template_path);
    }

    /**
     * Obtenir le chemin complet du fichier signature
     */
    public function getSignatureFullPath(): ?string
    {
        return $this->signature_image
            ? storage_path('app/public/' . $this->signature_image)
            : null;
    }

    /**
     * ========================================
     * ACCESSEURS
     * ========================================
     */

    /**
     * Obtenir le statut (actif/inactif)
     */
    public function getStatutAttribute(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatutColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'secondary';
    }

    /**
     * Obtenir le badge du statut
     */
    public function getStatutBadgeAttribute(): string
    {
        $color = $this->statut_color;
        $statut = $this->statut;

        return "<span class='badge bg-{$color}'>{$statut}</span>";
    }

    /**
     * Obtenir le label du type d'organisation
     */
    public function getTypeOrganisationLabelAttribute(): string
    {
        return $this->organisationType?->nom ?? 'N/A';
    }

    /**
     * Obtenir le label du type d'opération
     */
    public function getOperationTypeLabelAttribute(): string
    {
        return $this->operationType?->libelle ?? 'Tous types';
    }

    /**
     * Obtenir le label de l'étape workflow
     */
    public function getWorkflowStepLabelAttribute(): string
    {
        return $this->workflowStep?->libelle ?? 'Toutes étapes';
    }

    /**
     * Obtenir le mode de génération
     */
    public function getGenerationModeAttribute(): string
    {
        return $this->auto_generate ? 'Automatique' : 'Manuel';
    }

    /**
     * Obtenir le badge du mode de génération
     */
    public function getGenerationModeBadgeAttribute(): string
    {
        if ($this->auto_generate) {
            return '<span class="badge bg-success"><i class="fas fa-sync"></i> Automatique</span>';
        }
        return '<span class="badge bg-info"><i class="fas fa-hand-pointer"></i> Manuel</span>';
    }

    /**
     * Obtenir le label du type de document
     */
    public function getTypeDocumentLabelAttribute(): string
    {
        $types = [
            'recepisse_provisoire' => 'Récépissé provisoire',
            'recepisse_definitif' => 'Récépissé définitif',
            'certificat_enregistrement' => 'Certificat d\'enregistrement',
            'attestation' => 'Attestation',
            'notification_rejet' => 'Notification de rejet',
            'autre' => 'Autre',
        ];

        return $types[$this->type_document] ?? $this->type_document;
    }

    /**
     * Badge du type de document
     */
    public function getTypeDocumentBadgeAttribute(): string
    {
        $colors = [
            'recepisse_provisoire' => 'warning',
            'recepisse_definitif' => 'success',
            'certificat_enregistrement' => 'primary',
            'attestation' => 'info',
            'notification_rejet' => 'danger',
            'autre' => 'secondary',
        ];

        $color = $colors[$this->type_document] ?? 'secondary';
        $label = $this->type_document_label;

        return "<span class='badge bg-{$color}'>{$label}</span>";
    }

    /**
     * Nombre de documents générés
     */
    public function getGenerationsCountAttribute(): int
    {
        return $this->generations()->count();
    }

    /**
     * Obtenir la liste des types de documents
     * 
     * @return array
     */
    public static function getTypesDocument(): array
    {
        return [
            'accuse_reception' => 'Accusé de réception',
            'recepisse_provisoire' => 'Récépissé provisoire',
            'recepisse_definitif' => 'Récépissé définitif',
            'certificat_enregistrement' => 'Certificat d\'enregistrement',
            'attestation' => 'Attestation',
            'autorisation' => 'Autorisation',
            'agrement' => 'Agrément',
            'notification_rejet' => 'Notification de rejet',
            'demande_complement' => 'Demande de complément',
            'convocation' => 'Convocation',
            'pv_commission' => 'PV de commission',
            'decision' => 'Décision',
            'courrier_officiel' => 'Courrier officiel',
            'autre' => 'Autre document',
        ];
    }
}