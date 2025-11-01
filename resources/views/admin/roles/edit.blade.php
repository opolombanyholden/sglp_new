{{-- resources/views/admin/roles/edit.blade.php - VERSION CORRIGÉE --}}
@extends('layouts.admin')
@section('title', 'Modifier le Rôle')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise bleue pour "Édition" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-edit me-2"></i>
                                Modifier le Rôle
                            </h2>
                            <p class="mb-0 opacity-90">
                                Modifiez les paramètres et permissions du rôle "{{ $role->display_name ?? $role->name }}"
                                @if($role->isSystemRole())
                                    <span style="background: rgba(255, 255, 255, 0.2); padding: 6px 12px; border-radius: 15px; font-size: 0.85rem; margin-left: 1rem;">
                                        <i class="fas fa-shield-alt"></i>
                                        Rôle Système
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-outline-light btn-lg me-2">
                                <i class="fas fa-eye me-2"></i>
                                Voir le rôle
                            </a>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ AJOUT - Messages d'erreur et succès --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Erreurs de validation</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" id="editRoleForm">
        @csrf
        @method('PUT')
        
        <!-- Informations générales -->
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2" style="color: #003f7f;"></i>
                                Informations Générales
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($role->isSystemRole())
                        <div class="alert" style="background: linear-gradient(45deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05)); border: 1px solid rgba(255, 193, 7, 0.2); border-radius: 12px;">
                            <i class="fas fa-exclamation-triangle me-2" style="color: #ffc107;"></i>
                            <strong>Attention :</strong> Il s'agit d'un rôle système. Certaines modifications peuvent être limitées pour préserver l'intégrité du système.
                        </div>
                        @else
                        <div class="alert" style="background: linear-gradient(45deg, rgba(0, 158, 63, 0.1), rgba(0, 158, 63, 0.05)); border: 1px solid rgba(0, 158, 63, 0.2); border-radius: 12px;">
                            <i class="fas fa-lightbulb me-2" style="color: #009e3f;"></i>
                            <strong>Aide :</strong> Vous pouvez modifier librement ce rôle personnalisé. Assurez-vous que les changements n'affectent pas les utilisateurs existants.
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold" style="color: #003f7f;">
                                    <i class="fas fa-tag me-1"></i>
                                    Nom du rôle <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @if($role->isSystemRole()) @endif @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $role->name) }}"
                                       placeholder="ex: moderateur_contenu"
                                       @if($role->isSystemRole()) readonly style="background: #e9ecef; cursor: not-allowed;" @endif
                                       required
                                       style="border-radius: 12px; border: 2px solid #e3e6f0; padding: 12px 16px;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($role->isSystemRole())
                                <small class="text-muted">Le nom des rôles système ne peut pas être modifié</small>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="display_name" class="form-label fw-bold" style="color: #003f7f;">
                                    <i class="fas fa-eye me-1"></i>
                                    Nom d'affichage <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('display_name') is-invalid @enderror" 
                                       id="display_name" 
                                       name="display_name" 
                                       value="{{ old('display_name', $role->display_name) }}"
                                       placeholder="ex: Modérateur de Contenu"
                                       required
                                       style="border-radius: 12px; border: 2px solid #e3e6f0; padding: 12px 16px;">
                                @error('display_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold" style="color: #003f7f;">
                                <i class="fas fa-align-left me-1"></i>
                                Description
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Décrivez les responsabilités et le périmètre de ce rôle..."
                                      style="border-radius: 12px; border: 2px solid #e3e6f0; padding: 12px 16px;">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Niveau hiérarchique -->
        <div class="row mb-4">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-layer-group me-2" style="color: #009e3f;"></i>
                        Niveau Hiérarchique <span class="text-danger">*</span>
                    </h5>
                    <small class="text-muted" id="levelIndicator">Aucun niveau sélectionné</small>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4" style="border-radius: 12px;">
                    <i class="fas fa-info-circle me-2" style="color: #009e3f;"></i>
                    <strong>Important :</strong> Le niveau détermine les permissions maximales du rôle. 
                    <strong>Cliquez sur une option</strong> pour la sélectionner.
                </div>

                <!-- Sélection de niveau avec design amélioré -->
                <div class="level-selection-container">
                    
                    <!-- Niveau 10 - Super Admin -->
                    <div class="level-card" data-level="10">
                        <div class="level-header" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                            <div class="level-info">
                                <div class="level-title">
                                    <i class="fas fa-crown me-2"></i>
                                    <strong>Niveau 10</strong>
                                    <span class="level-badge">MAX</span>
                                </div>
                                <div class="level-subtitle">Super Administrateur</div>
                            </div>
                            <div class="level-checkbox">
                                <i class="far fa-circle unchecked-icon"></i>
                                <i class="fas fa-check-circle checked-icon"></i>
                            </div>
                        </div>
                        <div class="level-description">
                            Accès complet au système - Tous pouvoirs administratifs
                        </div>
                    </div>

                    <!-- Niveau 9 - Admin Général -->
                    <div class="level-card" data-level="9">
                        <div class="level-header" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                            <div class="level-info">
                                <div class="level-title">
                                    <i class="fas fa-user-cog me-2"></i>
                                    <strong>Niveau 9</strong>
                                </div>
                                <div class="level-subtitle">Administrateur Général</div>
                            </div>
                            <div class="level-checkbox">
                                <i class="far fa-circle unchecked-icon"></i>
                                <i class="fas fa-check-circle checked-icon"></i>
                            </div>
                        </div>
                        <div class="level-description">
                            Gestion complète des dossiers et utilisateurs
                        </div>
                    </div>

                    <!-- Niveau 8 - Admin Spécialisé -->
                    <div class="level-card" data-level="8">
                        <div class="level-header" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                            <div class="level-info">
                                <div class="level-title">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <strong>Niveau 8</strong>
                                </div>
                                <div class="level-subtitle">Administrateur Spécialisé</div>
                            </div>
                            <div class="level-checkbox">
                                <i class="far fa-circle unchecked-icon"></i>
                                <i class="fas fa-check-circle checked-icon"></i>
                            </div>
                        </div>
                        <div class="level-description">
                            Administration d'un domaine spécifique
                        </div>
                    </div>

                    <!-- Niveau 6 - Modérateur -->
                    <div class="level-card" data-level="6">
                        <div class="level-header" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                            <div class="level-info">
                                <div class="level-title">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Niveau 6</strong>
                                </div>
                                <div class="level-subtitle">Modérateur</div>
                            </div>
                            <div class="level-checkbox">
                                <i class="far fa-circle unchecked-icon"></i>
                                <i class="fas fa-check-circle checked-icon"></i>
                            </div>
                        </div>
                        <div class="level-description">
                            Validation et modération de contenu
                        </div>
                    </div>

                    <!-- Niveau 4 - Opérateur -->
                    <div class="level-card" data-level="4">
                        <div class="level-header" style="background: linear-gradient(135deg, #ffcd00 0%, #fd7e14 100%); color: #212529;">
                            <div class="level-info">
                                <div class="level-title">
                                    <i class="fas fa-user me-2"></i>
                                    <strong>Niveau 4</strong>
                                </div>
                                <div class="level-subtitle">Opérateur</div>
                            </div>
                            <div class="level-checkbox">
                                <i class="far fa-circle unchecked-icon"></i>
                                <i class="fas fa-check-circle checked-icon"></i>
                            </div>
                        </div>
                        <div class="level-description">
                            Traitement des dossiers courants
                        </div>
                    </div>

                    <!-- Niveau 2 - Auditeur -->
                    <div class="level-card" data-level="2">
                        <div class="level-header" style="background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);">
                            <div class="level-info">
                                <div class="level-title">
                                    <i class="fas fa-search me-2"></i>
                                    <strong>Niveau 2</strong>
                                </div>
                                <div class="level-subtitle">Auditeur</div>
                            </div>
                            <div class="level-checkbox">
                                <i class="far fa-circle unchecked-icon"></i>
                                <i class="fas fa-check-circle checked-icon"></i>
                            </div>
                        </div>
                        <div class="level-description">
                            Consultation et rapports uniquement
                        </div>
                    </div>

                    <!-- Niveau 1 - Visiteur -->
                    <div class="level-card" data-level="1">
                        <div class="level-header" style="background: linear-gradient(135deg, #e9ecef 0%, #f8f9fa 100%); color: #495057;">
                            <div class="level-info">
                                <div class="level-title">
                                    <i class="fas fa-eye me-2"></i>
                                    <strong>Niveau 1</strong>
                                    <span class="level-badge level-badge-low">MIN</span>
                                </div>
                                <div class="level-subtitle">Visiteur</div>
                            </div>
                            <div class="level-checkbox">
                                <i class="far fa-circle unchecked-icon"></i>
                                <i class="fas fa-check-circle checked-icon"></i>
                            </div>
                        </div>
                        <div class="level-description">
                            Accès en lecture seule très limité
                        </div>
                    </div>
                </div>

                <input type="hidden" name="level" id="level" value="{{ old('level') }}" required>
                @error('level')
                    <div class="text-danger mt-3">{{ $message }}</div>
                @enderror
                
                <div id="levelError" class="text-danger mt-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Veuillez sélectionner un niveau hiérarchique
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Paramètres -->
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs me-2" style="color: #ffcd00;"></i>
                                Paramètres
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="color: #003f7f;">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Statut du rôle
                                </label>
                                <div class="form-check form-switch">
                                    {{-- ✅ CORRECTION CRITIQUE - Gestion correcte du checkbox --}}
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           value="1"
                                           {{ old('is_active', $role->is_active) ? 'checked' : '' }}
                                           @if($role->isSystemRole() && $role->is_active) disabled @endif
                                           style="transform: scale(1.5);">
                                    <label class="form-check-label ms-2" for="is_active" id="statusLabel">
                                        {{ $role->is_active ? 'Rôle actif' : 'Rôle inactif' }}
                                    </label>
                                </div>
                                @if($role->isSystemRole() && $role->is_active)
                                <small class="text-muted">Les rôles système actifs ne peuvent pas être désactivés</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($role->isSystemRole())
                                <div class="alert alert-warning" style="border-radius: 12px;">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Rôle système</strong> - Protégé contre la suppression
                                </div>
                                @else
                                <div class="alert alert-info" style="border-radius: 12px;">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <strong>Rôle personnalisé</strong> - Peut être modifié et supprimé
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        @if(($role->users_count ?? 0) > 0)
                        <div class="alert" style="background: linear-gradient(45deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05)); border: 1px solid rgba(255, 193, 7, 0.2); border-radius: 12px;">
                            <i class="fas fa-users me-2" style="color: #ffc107;"></i>
                            <strong>Utilisateurs affectés :</strong> Ce rôle est actuellement assigné à {{ $role->users_count }} utilisateur(s). 
                            Les modifications s'appliqueront immédiatement à tous ces utilisateurs.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-secondary btn-lg me-2" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                                
                                @if(!$role->isSystemRole() && ($role->users_count ?? 0) === 0)
                                <button type="button" class="btn btn-lg" onclick="confirmDelete({{ $role->id }}, '{{ $role->display_name }}')" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-trash me-2"></i>
                                    Supprimer
                                </button>
                                @endif
                            </div>
                            
                            <div>
                                <button type="button" class="btn btn-outline-info btn-lg me-3" onclick="previewChanges()" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-eye me-2"></i>
                                    Aperçu
                                </button>
                                <button type="submit" class="btn btn-lg" id="submitBtn" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-save me-2"></i>
                                    <span>Enregistrer les modifications</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- FAB (Floating Action Button) tricolore -->
<div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 1000;">
    <div id="fabMenu">
        <div onclick="toggleFAB()" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.3s ease;">
            <i class="fas fa-tools" style="color: white; font-size: 1.5rem;"></i>
        </div>
    </div>
</div>

<style>
/* Animation d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

.level-option:hover:not(.disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.level-option.selected {
    border: 3px solid #009e3f !important;
    box-shadow: 0 0 0 3px rgba(0, 158, 63, 0.2);
}

.form-control:focus {
    border-color: #009e3f;
    box-shadow: 0 0 0 3px rgba(0, 158, 63, 0.1);
}

/* ===== STYLES AMÉLIORÉS POUR LA SÉLECTION DE NIVEAU ===== */

.level-selection-container {
    display: grid;
    gap: 12px;
    margin-top: 20px;
}

.level-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid transparent;
    position: relative;
}

.level-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.level-card.selected {
    border-color: #009e3f;
    box-shadow: 0 8px 25px rgba(0, 158, 63, 0.3);
    transform: translateY(-2px);
}

.level-card.selected::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 2px solid #009e3f;
    border-radius: 12px;
    z-index: 1;
    pointer-events: none;
}

.level-header {
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    position: relative;
}

.level-info {
    flex: 1;
}

.level-title {
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    margin-bottom: 4px;
}

.level-subtitle {
    font-size: 0.9rem;
    opacity: 0.9;
}

.level-badge {
    background: rgba(255,255,255,0.3);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: bold;
    margin-left: 8px;
}

.level-badge-low {
    background: rgba(0,0,0,0.2);
}

.level-checkbox {
    font-size: 1.5rem;
    position: relative;
    z-index: 2;
}

.checked-icon {
    display: none;
    color: #00ff00;
    text-shadow: 0 0 5px rgba(0,255,0,0.5);
}

.unchecked-icon {
    display: inline-block;
    opacity: 0.6;
}

.level-card.selected .checked-icon {
    display: inline-block;
    animation: checkPulse 0.6s ease-out;
}

.level-card.selected .unchecked-icon {
    display: none;
}

.level-description {
    padding: 12px 20px;
    background: white;
    color: #666;
    font-size: 0.9rem;
    border-top: 1px solid rgba(255,255,255,0.2);
}

/* Animation pour la sélection */
@keyframes checkPulse {
    0% { 
        transform: scale(0.8); 
        opacity: 0;
    }
    50% { 
        transform: scale(1.2); 
        opacity: 1;
    }
    100% { 
        transform: scale(1); 
        opacity: 1;
    }
}

/* Animation pour la désélection */
@keyframes uncheckFade {
    0% { 
        transform: scale(1); 
        opacity: 1;
    }
    100% { 
        transform: scale(0.8); 
        opacity: 0.6;
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .level-header {
        padding: 12px 15px;
    }
    
    .level-title {
        font-size: 1rem;
    }
    
    .level-checkbox {
        font-size: 1.3rem;
    }
}

/* Indicateur de niveau sélectionné */
#levelIndicator {
    background: rgba(0, 158, 63, 0.1);
    color: #009e3f;
    padding: 4px 12px;
    border-radius: 15px;
    font-weight: 500;
    transition: all 0.3s ease;
}

#levelIndicator.selected {
    background: #009e3f;
    color: white;
    box-shadow: 0 2px 8px rgba(0, 158, 63, 0.3);
}


</style>

<script>
// ===== JAVASCRIPT COMPLET MIS À JOUR POUR EDIT.BLADE.PHP =====

let selectedLevel = {{ $role->level }};
let isSystemRole = {{ $role->isSystemRole() ? 'true' : 'false' }};

document.addEventListener('DOMContentLoaded', function() {
    // ✅ MISE À JOUR - Gestion de la sélection des niveaux avec nouveau design
    document.querySelectorAll('.level-card').forEach(card => {
        // Désactiver les cartes pour les rôles système
        if (isSystemRole) {
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';
            card.style.cursor = 'not-allowed';
        } else {
            card.addEventListener('click', function() {
                const level = parseInt(this.dataset.level);
                selectLevel(level);
            });
            
            // Effet hover amélioré
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(-3px)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(0)';
                }
            });
        }
    });
    
    // ✅ INITIALISER - Sélectionner le niveau actuel au chargement
    if (selectedLevel) {
        selectLevel(selectedLevel);
        
        // Mettre à jour l'indicateur initial
        const indicator = document.getElementById('levelIndicator');
        if (isSystemRole) {
            indicator.textContent = `Niveau ${selectedLevel} (Protégé)`;
            indicator.style.background = '#ffc107';
            indicator.style.color = '#212529';
        } else {
            indicator.textContent = `Niveau ${selectedLevel} sélectionné`;
            indicator.classList.add('selected');
        }
    }
});

// ✅ FONCTION SELECTLEVEL MISE À JOUR pour le nouveau design
function selectLevel(level) {
    if (isSystemRole) return; // Empêcher la sélection pour les rôles système
    
    selectedLevel = level;
    document.getElementById('level').value = level;
    document.getElementById('levelError').style.display = 'none';
    
    // Mettre à jour l'indicateur
    const indicator = document.getElementById('levelIndicator');
    indicator.textContent = `Niveau ${level} sélectionné`;
    indicator.classList.add('selected');
    
    // Retirer toutes les sélections précédentes
    document.querySelectorAll('.level-card').forEach(card => {
        card.classList.remove('selected');
        
        // Animation de désélection
        const checkedIcon = card.querySelector('.checked-icon');
        const uncheckedIcon = card.querySelector('.unchecked-icon');
        
        if (checkedIcon && checkedIcon.style.display === 'inline-block') {
            checkedIcon.style.animation = 'uncheckFade 0.3s ease-out';
            setTimeout(() => {
                checkedIcon.style.display = 'none';
                uncheckedIcon.style.display = 'inline-block';
                checkedIcon.style.animation = '';
            }, 300);
        }
    });
    
    // Ajouter la sélection à la nouvelle option
    const selectedCard = document.querySelector(`[data-level="${level}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
        
        // Animation de sélection
        const checkedIcon = selectedCard.querySelector('.checked-icon');
        const uncheckedIcon = selectedCard.querySelector('.unchecked-icon');
        
        if (checkedIcon && uncheckedIcon) {
            uncheckedIcon.style.display = 'none';
            checkedIcon.style.display = 'inline-block';
        }
        
        // Scroll vers l'élément sélectionné
        selectedCard.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest' 
        });
    }
}

// ✅ CORRECTION - Toggle status avec gestion disabled
document.getElementById('is_active').addEventListener('change', function() {
    if (!this.disabled) {
        const label = document.getElementById('statusLabel');
        label.textContent = this.checked ? 'Rôle actif' : 'Rôle inactif';
    }
});

// ✅ FONCTION PREVIEWCHANGES MISE À JOUR
function previewChanges() {
    const name = document.getElementById('name').value;
    const displayName = document.getElementById('display_name').value;
    const description = document.getElementById('description').value;
    const isActive = document.getElementById('is_active').checked;
    
    const preview = `
═══════════════════════════════════
🔄 APERÇU DES MODIFICATIONS
═══════════════════════════════════
📝 Nom : ${name}
👁️ Nom d'affichage : ${displayName}
📊 Niveau : ${selectedLevel}/10 ${isSystemRole ? '(Protégé)' : ''}
📄 Description : ${description || 'Aucune description'}
⚡ Statut : ${isActive ? '✅ Actif' : '❌ Inactif'}
🔧 Type : ${isSystemRole ? '🛡️ Système' : '🆕 Personnalisé'}
═══════════════════════════════════
    `;
    
    alert(preview);
}

// ✅ FONCTION CONFIRMDELETE MISE À JOUR
function confirmDelete(roleId, roleName) {
    const confirmMessage = `Êtes-vous sûr de vouloir supprimer le rôle "${roleName}" ?

⚠️ Cette action est irréversible.
🗑️ Le rôle sera définitivement supprimé.
📋 Les permissions associées seront retirées.

Confirmer la suppression ?`;
    
    if (confirm(confirmMessage)) {
        // Créer un formulaire de suppression dynamique
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/roles/${roleId}`;
        form.style.display = 'none';
        
        // Token CSRF
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Méthode DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
    }
}

// ✅ FAB TOGGLE AMÉLIORÉ
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    const currentScale = fabMenu.style.transform;
    
    if (currentScale === 'scale(1.1)') {
        fabMenu.style.transform = 'scale(1)';
        fabMenu.style.boxShadow = '0 4px 12px rgba(0,0,0,0.3)';
    } else {
        fabMenu.style.transform = 'scale(1.1)';
        fabMenu.style.boxShadow = '0 8px 20px rgba(0,0,0,0.4)';
    }
}

// ✅ VALIDATION DU FORMULAIRE MISE À JOUR
document.getElementById('editRoleForm').addEventListener('submit', function(e) {
    let hasErrors = false;
    
    // Validation du niveau pour les rôles non-système
    if (!isSystemRole && (!selectedLevel || selectedLevel === 'null')) {
        e.preventDefault();
        hasErrors = true;
        
        // Afficher l'erreur
        document.getElementById('levelError').style.display = 'block';
        
        // Mettre à jour l'indicateur d'erreur
        const indicator = document.getElementById('levelIndicator');
        indicator.textContent = 'Niveau requis !';
        indicator.style.background = '#dc3545';
        indicator.style.color = 'white';
        indicator.classList.remove('selected');
        
        // Scroll vers la section niveau
        document.querySelector('.level-selection-container').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        
        // Effet de "secousse" sur le conteneur
        const container = document.querySelector('.level-selection-container');
        container.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            container.style.animation = '';
        }, 500);
    }
    
    // Validation des champs requis
    const requiredFields = ['name', 'display_name'];
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            hasErrors = true;
            field.focus();
            field.style.borderColor = '#dc3545';
            field.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
        }
    });
    
    if (hasErrors) {
        return false;
    }
    
    // Si pas d'erreurs, afficher le loading
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement en cours...';
    
    // Permettre la soumission
    return true;
});

// ✅ GESTION DES ERREURS DE CHAMPS EN TEMPS RÉEL
document.getElementById('name').addEventListener('input', function() {
    if (this.value.trim()) {
        this.style.borderColor = '#009e3f';
        this.style.boxShadow = '0 0 0 3px rgba(0, 158, 63, 0.1)';
    }
});

document.getElementById('display_name').addEventListener('input', function() {
    if (this.value.trim()) {
        this.style.borderColor = '#009e3f';
        this.style.boxShadow = '0 0 0 3px rgba(0, 158, 63, 0.1)';
    }
});

// ✅ AJOUT - Animation de secousse pour les erreurs
const shakeKeyframes = `
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}`;

// Ajouter les keyframes au document s'ils n'existent pas déjà
if (!document.getElementById('shakeStyles')) {
    const styleSheet = document.createElement("style");
    styleSheet.id = 'shakeStyles';
    styleSheet.type = "text/css";
    styleSheet.innerText = shakeKeyframes;
    document.head.appendChild(styleSheet);
}

// ✅ AJOUT - Gestion des raccourcis clavier
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S pour sauvegarder
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.getElementById('editRoleForm').submit();
    }
    
    // Échap pour annuler
    if (e.key === 'Escape') {
        const cancelBtn = document.querySelector('a[href*="roles.show"]');
        if (cancelBtn) {
            window.location.href = cancelBtn.href;
        }
    }
});

// ✅ AJOUT - Auto-save des modifications (optionnel)
let autoSaveTimeout;
function autoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        // Sauvegarder en localStorage pour récupération en cas de problème
        const formData = {
            name: document.getElementById('name').value,
            display_name: document.getElementById('display_name').value,
            description: document.getElementById('description').value,
            level: selectedLevel,
            is_active: document.getElementById('is_active').checked,
            timestamp: Date.now()
        };
        
        localStorage.setItem(`role_edit_${{{ $role->id }}}`, JSON.stringify(formData));
    }, 2000);
}

// Attacher l'auto-save aux champs
['name', 'display_name', 'description'].forEach(fieldId => {
    document.getElementById(fieldId).addEventListener('input', autoSave);
});

document.getElementById('is_active').addEventListener('change', autoSave);

// ✅ NETTOYAGE - Supprimer l'auto-save au submit réussi
document.getElementById('editRoleForm').addEventListener('submit', function() {
    localStorage.removeItem(`role_edit_${{{ $role->id }}}`);
});

// ✅ RÉCUPÉRATION - Restaurer les données en cas de rechargement
window.addEventListener('load', function() {
    const savedData = localStorage.getItem(`role_edit_${{{ $role->id }}}`);
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            
            // Vérifier si les données sont récentes (moins de 1 heure)
            if (Date.now() - data.timestamp < 3600000) {
                const shouldRestore = confirm('Des modifications non sauvegardées ont été détectées. Voulez-vous les restaurer ?');
                
                if (shouldRestore) {
                    document.getElementById('name').value = data.name || '';
                    document.getElementById('display_name').value = data.display_name || '';
                    document.getElementById('description').value = data.description || '';
                    document.getElementById('is_active').checked = data.is_active || false;
                    
                    if (data.level && !isSystemRole) {
                        selectLevel(data.level);
                    }
                    
                    // Mettre à jour le label du statut
                    const label = document.getElementById('statusLabel');
                    label.textContent = data.is_active ? 'Rôle actif' : 'Rôle inactif';
                }
            }
            
            // Nettoyer les anciennes données
            localStorage.removeItem(`role_edit_${{{ $role->id }}}`);
        } catch (e) {
            console.warn('Erreur lors de la restauration des données:', e);
        }
    }
});
</script>
@endsection