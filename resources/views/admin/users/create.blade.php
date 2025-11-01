{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Créer un Utilisateur')

@section('content')
<div class="container-fluid">
    <!-- Header avec style gabonais moderne -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-user-plus me-2"></i>
                                Créer un nouvel utilisateur
                            </h2>
                            <p class="mb-0 opacity-90">Création d'un compte opérateur ou agent pour le système SGLP</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Retour
                                </a>
                                <button type="button" class="btn btn-outline-light btn-lg" onclick="location.reload()">
                                    <i class="fas fa-sync me-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conseils de création -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(255, 205, 0, 0.1), rgba(255, 205, 0, 0.05));">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning rounded-circle text-dark d-flex align-items-center justify-content-center me-3" 
                             style="width: 45px; height: 45px;">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h5 class="mb-0 text-primary">Conseils pour la création d'un compte</h5>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Vérifiez que l'email est valide et accessible par l'utilisateur</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Le NIP doit respecter le format gabonais officiel (optionnel)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <small class="text-muted">Le mot de passe temporaire sera envoyé à l'utilisateur par email</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start mb-3">
                                <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <small class="text-muted">L'utilisateur devra changer son mot de passe à la première connexion</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire principal -->
    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('admin.users.store') }}" id="createUserForm">
                @csrf
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2" style="color: #003f7f;"></i>
                            Informations du nouvel utilisateur
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Section Informations personnelles -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Informations personnelles</h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label fw-bold">
                                        <i class="fas fa-id-badge text-primary me-2"></i>
                                        Nom de famille <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="nom" name="nom" class="form-control form-control-lg" 
                                           value="{{ old('nom') }}" required placeholder="Dupont">
                                    <div class="form-text">Nom de famille officiel</div>
                                    @error('nom')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="prenom" class="form-label fw-bold">
                                        <i class="fas fa-user text-primary me-2"></i>
                                        Prénom(s) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="prenom" name="prenom" class="form-control form-control-lg" 
                                           value="{{ old('prenom') }}" required placeholder="Jean">
                                    <div class="form-text">Prénom(s) officiels</div>
                                    @error('prenom')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-bold">
                                        <i class="fas fa-envelope text-success me-2"></i>
                                        Adresse email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" class="form-control form-control-lg" 
                                           value="{{ old('email') }}" required placeholder="jean.dupont@example.com">
                                    <div class="form-text">Adresse email de connexion (doit être unique)</div>
                                    @error('email')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-bold">
                                        <i class="fas fa-phone text-info me-2"></i>
                                        Téléphone
                                    </label>
                                    <input type="tel" id="phone" name="phone" class="form-control form-control-lg" 
                                           value="{{ old('phone') }}" placeholder="+241 01 23 45 67">
                                    <div class="form-text">Numéro de téléphone (optionnel)</div>
                                    @error('phone')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nip" class="form-label fw-bold">
                                        <i class="fas fa-id-card text-warning me-2"></i>
                                        Numéro NIP
                                    </label>
                                    <input type="text" id="nip" name="nip" class="form-control form-control-lg" 
                                           value="{{ old('nip') }}" placeholder="01-1234-19900101"
                                           pattern="[0-9]{2}-[0-9]{4}-[0-9]{8}">
                                    <div class="form-text">Format gabonais : XX-QQQQ-YYYYMMDD (optionnel)</div>
                                    @error('nip')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label fw-bold">
                                        <i class="fas fa-city text-secondary me-2"></i>
                                        Ville
                                    </label>
                                    <input type="text" id="city" name="city" class="form-control form-control-lg" 
                                           value="{{ old('city') }}" placeholder="Libreville">
                                    <div class="form-text">Ville de résidence</div>
                                    @error('city')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label fw-bold">
                                    <i class="fas fa-home text-secondary me-2"></i>
                                    Adresse complète
                                </label>
                                <textarea id="address" name="address" class="form-control" rows="2" 
                                          placeholder="123 Avenue de la Liberté, Quartier Glass">{{ old('address') }}</textarea>
                                <div class="form-text">Adresse de résidence (optionnel)</div>
                                @error('address')
                                <div class="text-danger mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section Rôle et permissions -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Rôle et permissions</h5>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-tags text-success me-2"></i>
                                    Rôle système <span class="text-danger">*</span>
                                </label>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="card h-100 role-option border-primary bg-light" 
                                             style="cursor: pointer;" onclick="selectRole('operator')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="role" value="operator" checked 
                                                       id="role_operator" class="d-none">
                                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                                     style="width: 70px; height: 70px; font-size: 1.8rem;">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <h6 class="fw-bold">Opérateur</h6>
                                                <p class="small text-muted mb-0">Peut créer et gérer les organisations.<br>Soumet les dossiers pour validation.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card h-100 role-option" 
                                             style="cursor: pointer;" onclick="selectRole('agent')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="role" value="agent" 
                                                       id="role_agent" class="d-none">
                                                <div class="bg-warning rounded-circle text-dark d-flex align-items-center justify-content-center mx-auto mb-3" 
                                                     style="width: 70px; height: 70px; font-size: 1.8rem;">
                                                    <i class="fas fa-user-tie"></i>
                                                </div>
                                                <h6 class="fw-bold">Agent</h6>
                                                <p class="small text-muted mb-0">Valide ou rejette les dossiers.<br>Peut demander des compléments.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card h-100 role-option" 
                                             style="cursor: pointer;" onclick="selectRole('admin')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="role" value="admin" 
                                                       id="role_admin" class="d-none">
                                                <div class="bg-danger rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                                     style="width: 70px; height: 70px; font-size: 1.8rem;">
                                                    <i class="fas fa-crown"></i>
                                                </div>
                                                <h6 class="fw-bold">Administrateur</h6>
                                                <p class="small text-muted mb-0">Accès complet au système.<br>Gestion des utilisateurs et paramètres.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('role')
                                <div class="text-danger mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                            
                            @if(isset($roles['advanced']) && !empty($roles['advanced']))
                            <div class="mb-3">
                                <label for="role_id" class="form-label fw-bold">
                                    <i class="fas fa-cogs text-secondary me-2"></i>
                                    Rôle avancé (optionnel)
                                </label>
                                <select id="role_id" name="role_id" class="form-select form-select-lg">
                                    <option value="">Aucun rôle avancé</option>
                                    @foreach($roles['advanced'] as $roleId => $roleLabel)
                                    <option value="{{ $roleId }}" {{ old('role_id') == $roleId ? 'selected' : '' }}>
                                        {{ $roleLabel }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Système de rôles avancé avec permissions granulaires</div>
                                @error('role_id')
                                <div class="text-danger mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                            @endif
                        </div>

                        <!-- Section Sécurité -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="bg-warning rounded-circle text-dark d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Sécurité et accès</h5>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-bold">
                                        <i class="fas fa-lock text-warning me-2"></i>
                                        Mot de passe temporaire <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" id="password" name="password" class="form-control form-control-lg" 
                                               required minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password')">
                                            <i class="fas fa-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 8 caractères (sera modifié à la première connexion)</div>
                                    <div id="password-strength" class="mt-2" style="display: none;">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="strength-bar" style="width: 0%;"></div>
                                        </div>
                                        <small class="text-muted" id="strength-text"></small>
                                    </div>
                                    @error('password')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label fw-bold">
                                        <i class="fas fa-lock text-warning me-2"></i>
                                        Confirmer le mot de passe <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation')">
                                            <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Ressaisir le mot de passe</div>
                                </div>
                            </div>
                            
                            <!-- Exigences de mot de passe -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-shield-alt text-success me-2"></i>
                                        Exigences de sécurité
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="requirement d-flex align-items-center mb-2" id="req-length">
                                                <div class="bg-secondary rounded-circle text-white me-2" style="width: 20px; height: 20px; font-size: 0.7rem;">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                                <small>Au moins 8 caractères</small>
                                            </div>
                                            <div class="requirement d-flex align-items-center mb-2" id="req-uppercase">
                                                <div class="bg-secondary rounded-circle text-white me-2" style="width: 20px; height: 20px; font-size: 0.7rem;">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                                <small>Au moins une majuscule</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="requirement d-flex align-items-center mb-2" id="req-lowercase">
                                                <div class="bg-secondary rounded-circle text-white me-2" style="width: 20px; height: 20px; font-size: 0.7rem;">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                                <small>Au moins une minuscule</small>
                                            </div>
                                            <div class="requirement d-flex align-items-center mb-2" id="req-number">
                                                <div class="bg-secondary rounded-circle text-white me-2" style="width: 20px; height: 20px; font-size: 0.7rem;">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                                <small>Au moins un chiffre</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check form-switch">
                                <!-- Hidden input pour s'assurer qu'une valeur est toujours envoyée -->
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    <i class="fas fa-power-off text-success me-2"></i>
                                    Activer le compte immédiatement
                                </label>
                                <div class="form-text">Le compte sera directement utilisable après création</div>
                                @error('is_active')
                                <div class="text-danger mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions du formulaire -->
                    <div class="card-footer bg-light border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour à la liste
                            </a>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>
                                Créer l'utilisateur
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Styles gabonais complémentaires pour Bootstrap 5 */
.role-option:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.form-control:focus,
.form-select:focus {
    border-color: #003f7f;
    box-shadow: 0 0 0 0.2rem rgba(0, 63, 127, 0.25);
}

/* Animation d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

/* États des exigences de mot de passe */
.requirement.valid .bg-secondary {
    background-color: #28a745 !important;
}

.requirement.valid i {
    content: "\f00c";
}

/* Notification moderne */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    min-width: 300px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
</style>

<script>
// JavaScript Bootstrap 5 compatible - sans jQuery
document.addEventListener('DOMContentLoaded', function() {
    console.log('Formulaire de création utilisateur chargé');
    
    // Sélection du rôle par défaut
    selectRole('operator');
    
    // Validation du mot de passe en temps réel
    const passwordInput = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            validatePassword(password);
            document.getElementById('password-strength').style.display = password ? 'block' : 'none';
        });
    }
    
    // Validation confirmation mot de passe
    if (passwordConfirmation) {
        passwordConfirmation.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmation = this.value;
            
            if (confirmation && password !== confirmation) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (confirmation && password === confirmation) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-invalid', 'is-valid');
            }
        });
    }
    
    // Validation NIP gabonais
    const nipInput = document.getElementById('nip');
    if (nipInput) {
        nipInput.addEventListener('input', function() {
            const value = this.value;
            const nipPattern = /^[0-9]{2}-[0-9]{4}-[0-9]{8}$/;
            
            if (value && !nipPattern.test(value)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (value) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.remove('is-invalid', 'is-valid');
            }
        });
    }
    
    // Validation email
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value;
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && emailPattern.test(email)) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else if (email) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }
    
    // Soumission du formulaire avec validation
    const form = document.getElementById('createUserForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmation = passwordConfirmation.value;
            
            if (!password) {
                e.preventDefault();
                showNotification('error', 'Veuillez saisir un mot de passe');
                passwordInput.focus();
                return false;
            }
            
            if (password !== confirmation) {
                e.preventDefault();
                showNotification('error', 'Les mots de passe ne correspondent pas');
                passwordConfirmation.focus();
                return false;
            }
            
            if (!validatePasswordStrength(password)) {
                e.preventDefault();
                showNotification('error', 'Le mot de passe ne respecte pas les exigences de sécurité');
                passwordInput.focus();
                return false;
            }
            
            // Animation du bouton de soumission
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalContent = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Création en cours...';
                submitBtn.disabled = true;
            }
            
            showNotification('info', 'Création de l\'utilisateur en cours...');
        });
    }
    
    // Gestion des erreurs de validation côté serveur
    @if($errors->any())
        @foreach($errors->all() as $error)
            showNotification('error', '{{ addslashes($error) }}');
        @endforeach
    @endif
    
    // Message de succès
    @if(session('success'))
        showNotification('success', '{{ addslashes(session('success')) }}');
    @endif
});

// Sélection des rôles
function selectRole(role) {
    // Retirer la sélection de tous les rôles
    document.querySelectorAll('.role-option').forEach(option => {
        option.classList.remove('border-primary', 'border-warning', 'border-danger', 'bg-light');
    });
    
    // Sélectionner le rôle choisi
    const selectedOption = document.querySelector(`#role_${role}`).closest('.role-option');
    document.getElementById(`role_${role}`).checked = true;
    
    // Appliquer le style correspondant
    if (role === 'operator') {
        selectedOption.classList.add('border-primary', 'bg-light');
    } else if (role === 'agent') {
        selectedOption.classList.add('border-warning', 'bg-light');
    } else if (role === 'admin') {
        selectedOption.classList.add('border-danger', 'bg-light');
    }
}

// Toggle visibilité mot de passe
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validation de la force du mot de passe
function validatePassword(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password)
    };
    
    // Mise à jour des indicateurs visuels
    updateRequirement('req-length', requirements.length);
    updateRequirement('req-uppercase', requirements.uppercase);
    updateRequirement('req-lowercase', requirements.lowercase);
    updateRequirement('req-number', requirements.number);
    
    // Calcul de la force
    const validCount = Object.values(requirements).filter(Boolean).length;
    let strength = 'weak';
    let percentage = 25;
    let barColor = 'bg-danger';
    
    if (validCount >= 3) {
        strength = 'medium';
        percentage = 60;
        barColor = 'bg-warning';
    }
    if (validCount === 4) {
        strength = 'strong';
        percentage = 100;
        barColor = 'bg-success';
    }
    
    // Mise à jour de la barre de force
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    if (strengthBar && strengthText) {
        strengthBar.className = `progress-bar ${barColor}`;
        strengthBar.style.width = percentage + '%';
        
        const labels = {
            weak: 'Mot de passe faible',
            medium: 'Mot de passe moyen',
            strong: 'Mot de passe fort'
        };
        strengthText.textContent = labels[strength];
    }
    
    return validCount === 4;
}

// Mettre à jour un indicateur d'exigence
function updateRequirement(id, isValid) {
    const element = document.getElementById(id);
    if (element) {
        const icon = element.querySelector('i');
        const circle = element.querySelector('.bg-secondary, .bg-success');
        
        if (isValid) {
            element.classList.add('valid');
            if (circle) circle.className = circle.className.replace('bg-secondary', 'bg-success');
            if (icon) {
                icon.className = 'fas fa-check';
            }
        } else {
            element.classList.remove('valid');
            if (circle) circle.className = circle.className.replace('bg-success', 'bg-secondary');
            if (icon) {
                icon.className = 'fas fa-times';
            }
        }
    }
}

// Validation finale de la force du mot de passe
function validatePasswordStrength(password) {
    return password.length >= 8 &&
           /[A-Z]/.test(password) &&
           /[a-z]/.test(password) &&
           /[0-9]/.test(password);
}

// Système de notifications Bootstrap 5
function showNotification(type, message, duration = 5000) {
    const colors = {
        success: 'alert-success',
        error: 'alert-danger',
        warning: 'alert-warning',
        info: 'alert-info'
    };
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    const notification = document.createElement('div');
    notification.className = `alert ${colors[type]} alert-dismissible fade show notification`;
    notification.innerHTML = `
        <i class="${icons[type]} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-suppression
    setTimeout(() => {
        if (notification && notification.parentNode) {
            const alert = new bootstrap.Alert(notification);
            alert.close();
        }
    }, duration);
}
</script>
@endsection