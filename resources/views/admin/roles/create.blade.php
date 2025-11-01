{{-- resources/views/admin/roles/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Créer un Rôle')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise verte pour "Création" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-plus-circle me-2"></i>
                                Créer un Nouveau Rôle
                            </h2>
                            <p class="mb-0 opacity-90">Définissez un nouveau rôle avec ses permissions et son niveau hiérarchique</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" id="createRoleForm">
        @csrf
        
        <!-- Informations générales -->
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2" style="color: #009e3f;"></i>
                                Informations Générales
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert" style="background: linear-gradient(45deg, rgba(0, 158, 63, 0.1), rgba(0, 158, 63, 0.05)); border: 1px solid rgba(0, 158, 63, 0.2); border-radius: 12px;">
                            <i class="fas fa-lightbulb me-2" style="color: #009e3f;"></i>
                            <strong>Aide :</strong> Le nom du rôle doit être unique et utiliser uniquement des lettres minuscules, chiffres et tirets bas.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold" style="color: #003f7f;">
                                    <i class="fas fa-tag me-1"></i>
                                    Nom du rôle <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="ex: moderateur_contenu"
                                       required
                                       style="border-radius: 12px; border: 2px solid #e3e6f0; padding: 12px 16px;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                       value="{{ old('display_name') }}"
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
                                      style="border-radius: 12px; border: 2px solid #e3e6f0; padding: 12px 16px;">{{ old('description') }}</textarea>
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
                                Niveau Hiérarchique
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert" style="background: linear-gradient(45deg, rgba(0, 158, 63, 0.1), rgba(0, 158, 63, 0.05)); border: 1px solid rgba(0, 158, 63, 0.2); border-radius: 12px;">
                            <i class="fas fa-info-circle me-2" style="color: #009e3f;"></i>
                            <strong>Important :</strong> Le niveau détermine les permissions maximales du rôle.
                        </div>

                        <div class="level-options">
                            <div class="level-option" data-level="10" style="background: linear-gradient(45deg, #8b1538, #c41e3a); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Niveau 10 - Super Administrateur</strong>
                                        <br><small>Accès complet au système</small>
                                    </div>
                                    <i class="fas fa-crown fa-2x"></i>
                                </div>
                            </div>
                            
                            <div class="level-option" data-level="9" style="background: linear-gradient(45deg, #003f7f, #0056b3); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Niveau 9 - Administrateur Général</strong>
                                        <br><small>Gestion complète des dossiers et utilisateurs</small>
                                    </div>
                                    <i class="fas fa-user-cog fa-2x"></i>
                                </div>
                            </div>
                            
                            <div class="level-option" data-level="8" style="background: linear-gradient(45deg, #009e3f, #00b347); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Niveau 8 - Administrateur Spécialisé</strong>
                                        <br><small>Administration d'un domaine spécifique</small>
                                    </div>
                                    <i class="fas fa-user-tie fa-2x"></i>
                                </div>
                            </div>
                            
                            <div class="level-option" data-level="6" style="background: linear-gradient(45deg, #17a2b8, #20c997); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Niveau 6 - Modérateur</strong>
                                        <br><small>Validation et modération de contenu</small>
                                    </div>
                                    <i class="fas fa-shield-alt fa-2x"></i>
                                </div>
                            </div>
                            
                            <div class="level-option" data-level="4" style="background: linear-gradient(45deg, #ffcd00, #fd7e14); color: #212529; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Niveau 4 - Opérateur</strong>
                                        <br><small>Traitement des dossiers courants</small>
                                    </div>
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                            </div>
                            
                            <div class="level-option" data-level="2" style="background: linear-gradient(45deg, #6c757d, #adb5bd); color: white; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Niveau 2 - Auditeur</strong>
                                        <br><small>Consultation et rapports uniquement</small>
                                    </div>
                                    <i class="fas fa-search fa-2x"></i>
                                </div>
                            </div>
                            
                            <div class="level-option" data-level="1" style="background: linear-gradient(45deg, #e9ecef, #f8f9fa); color: #495057; padding: 12px 16px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s ease;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Niveau 1 - Visiteur</strong>
                                        <br><small>Accès en lecture seule très limité</small>
                                    </div>
                                    <i class="fas fa-eye fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="level" id="level" value="{{ old('level') }}" required>
                        @error('level')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
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
                           
                            <!-- Remplacer cette section dans le formulaire -->
<div class="col-md-6">
    <label class="form-label fw-bold" style="color: #003f7f;">
        <i class="fas fa-toggle-on me-1"></i>
        Statut du rôle
    </label>
    <div class="form-check form-switch">
        {{-- ✅ SOLUTION 1: Utiliser value="1" et gérer l'absence --}}
        <input class="form-check-input" 
               type="checkbox" 
               name="is_active" 
               id="is_active" 
               value="1"
               {{ old('is_active', true) ? 'checked' : '' }} 
               style="transform: scale(1.5);">
        <label class="form-check-label ms-2" for="is_active" id="statusLabel">
            Rôle actif
        </label>
    </div>
    
</div>

                            <div class="col-md-6">
                                <div class="alert alert-info" style="border-radius: 12px;">
                                    <i class="fas fa-user-plus me-2"></i>
                                    <strong>Rôle personnalisé</strong> - Peut être modifié et supprimé
                                </div>
                            </div>
                        </div>
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
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                                <i class="fas fa-times me-2"></i>
                                Annuler
                            </a>
                            
                            <div>
                                <button type="button" class="btn btn-outline-success btn-lg me-3" onclick="previewRole()" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-eye me-2"></i>
                                    Aperçu
                                </button>
                                <button type="submit" class="btn btn-lg" id="submitBtn" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-save me-2"></i>
                                    <span>Créer le Rôle</span>
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

.level-option:hover {
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
</style>

<script>
let selectedLevel = {{ old('level') ?: 'null' }};

// Sélection de niveau
document.querySelectorAll('.level-option').forEach(option => {
    option.addEventListener('click', function() {
        const level = this.dataset.level;
        selectLevel(level);
    });
});

function selectLevel(level) {
    selectedLevel = level;
    document.getElementById('level').value = level;
    
    document.querySelectorAll('.level-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    document.querySelector(`[data-level="${level}"]`).classList.add('selected');
}

// Auto-génération du nom d'affichage
document.getElementById('name').addEventListener('input', function() {
    if (!document.getElementById('display_name').value) {
        const displayName = this.value
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
        document.getElementById('display_name').value = displayName;
    }
});

// Toggle status
document.getElementById('is_active').addEventListener('change', function() {
    const label = document.getElementById('statusLabel');
    label.textContent = this.checked ? 'Rôle actif' : 'Rôle inactif';
});

// Aperçu du rôle
function previewRole() {
    const name = document.getElementById('name').value;
    const displayName = document.getElementById('display_name').value;
    const description = document.getElementById('description').value;
    const isActive = document.getElementById('is_active').checked;
    
    if (!name || !displayName || !selectedLevel) {
        alert('Veuillez remplir au moins le nom, le nom d\'affichage et sélectionner un niveau.');
        return;
    }
    
    const preview = `
Aperçu du rôle :
────────────────
• Nom : ${name}
• Nom d'affichage : ${displayName}
• Niveau : ${selectedLevel}/10
• Description : ${description || 'Aucune description'}
• Statut : ${isActive ? 'Actif' : 'Inactif'}
• Type : Personnalisé
    `;
    
    alert(preview);
}

// FAB toggle
function toggleFAB() {
    // Simple toggle animation
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.style.transform = fabMenu.style.transform === 'scale(1.1)' ? 'scale(1)' : 'scale(1.1)';
}

// Validation du formulaire
document.getElementById('createRoleForm').addEventListener('submit', function(e) {
    if (!selectedLevel) {
        e.preventDefault();
        alert('Veuillez sélectionner un niveau hiérarchique.');
        return;
    }
});

// Sélectionner le niveau initial si défini
if (selectedLevel) {
    selectLevel(selectedLevel);
}
</script>
@endsection