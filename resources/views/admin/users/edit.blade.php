{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Modifier Utilisateur')

@section('content')
<div class="container-fluid">
    <!-- Header avec style gabonais moderne -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-user-edit me-2"></i>
                                Modifier l'utilisateur
                            </h2>
                            <p class="mb-0 opacity-90">Modification des informations de {{ $user->name }}</p>
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

    <!-- Informations actuelles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(0, 158, 63, 0.1), rgba(0, 158, 63, 0.05));">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            @php
                                $initials = '';
                                if ($user->nom && $user->prenom) {
                                    $initials = strtoupper(substr($user->nom, 0, 1) . substr($user->prenom, 0, 1));
                                } elseif ($user->name) {
                                    $nameParts = explode(' ', trim($user->name));
                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                } else {
                                    $initials = strtoupper(substr($user->email, 0, 2));
                                }
                            @endphp
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 80px; height: 80px; font-weight: bold; font-size: 1.5rem;">
                                {{ $initials }}
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6 class="text-success mb-2">
                                        <i class="fas fa-calendar-plus me-2"></i>Compte créé
                                    </h6>
                                    <p class="mb-0">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-primary mb-2">
                                        <i class="fas fa-sign-in-alt me-2"></i>Dernière connexion
                                    </h6>
                                    <p class="mb-0">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-info mb-2">
                                        <i class="fas fa-envelope-check me-2"></i>Email vérifié
                                    </h6>
                                    <p class="mb-0">
                                        {!! $user->email_verified_at ? '<i class="fas fa-check-circle text-success"></i> Oui' : '<i class="fas fa-times-circle text-danger"></i> Non' !!}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-warning mb-2">
                                        <i class="fas fa-user-shield me-2"></i>Rôle actuel
                                    </h6>
                                    <p class="mb-0">
                                        <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                                    </p>
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
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}" id="editUserForm">
                @csrf
                @method('PUT')
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2" style="color: #009e3f;"></i>
                            Informations de l'utilisateur
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        
                        <!-- Section Informations personnelles -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Informations personnelles</h5>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label fw-bold">
                                        <i class="fas fa-id-badge text-success me-2"></i>
                                        Nom de famille <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="nom" name="nom" class="form-control form-control-lg" 
                                           value="{{ old('nom', $user->nom) }}" required>
                                    <div class="form-text">Nom de famille officiel</div>
                                    @error('nom')
                                    <div class="text-danger mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="prenom" class="form-label fw-bold">
                                        <i class="fas fa-user text-success me-2"></i>
                                        Prénom(s) <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="prenom" name="prenom" class="form-control form-control-lg" 
                                           value="{{ old('prenom', $user->prenom) }}" required>
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
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        Adresse email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" class="form-control form-control-lg" 
                                           value="{{ old('email', $user->email) }}" required>
                                    <div class="form-text">Adresse email de connexion</div>
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
                                           value="{{ old('phone', $user->phone) }}" 
                                           placeholder="+241 01 23 45 67">
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
                                           value="{{ old('nip', $user->nip) }}" 
                                           placeholder="01-1234-19900101"
                                           pattern="[0-9]{2}-[0-9]{4}-[0-9]{8}">
                                    <div class="form-text">Format gabonais : XX-QQQQ-YYYYMMDD</div>
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
                                           value="{{ old('city', $user->city) }}" 
                                           placeholder="Libreville">
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
                                <textarea id="address" name="address" class="form-control" rows="3" 
                                          placeholder="Adresse complète de résidence">{{ old('address', $user->address) }}</textarea>
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
                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Rôle et permissions</h5>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-tags text-primary me-2"></i>
                                    Rôle système <span class="text-danger">*</span>
                                </label>
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="card h-100 role-option {{ old('role', $user->role) === 'operator' ? 'border-success bg-light' : '' }}" 
                                             style="cursor: pointer;" onclick="selectRole('operator')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="role" value="operator" 
                                                       {{ old('role', $user->role) === 'operator' ? 'checked' : '' }} 
                                                       id="role_operator" class="d-none">
                                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                                     style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                                <h6 class="fw-bold">Opérateur</h6>
                                                <p class="small text-muted mb-0">Saisie et gestion des organisations</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card h-100 role-option {{ old('role', $user->role) === 'agent' ? 'border-warning bg-light' : '' }}" 
                                             style="cursor: pointer;" onclick="selectRole('agent')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="role" value="agent" 
                                                       {{ old('role', $user->role) === 'agent' ? 'checked' : '' }} 
                                                       id="role_agent" class="d-none">
                                                <div class="bg-warning rounded-circle text-dark d-flex align-items-center justify-content-center mx-auto mb-3" 
                                                     style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                    <i class="fas fa-user-tie"></i>
                                                </div>
                                                <h6 class="fw-bold">Agent</h6>
                                                <p class="small text-muted mb-0">Validation et traitement des dossiers</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card h-100 role-option {{ old('role', $user->role) === 'admin' ? 'border-danger bg-light' : '' }}" 
                                             style="cursor: pointer;" onclick="selectRole('admin')">
                                            <div class="card-body text-center">
                                                <input type="radio" name="role" value="admin" 
                                                       {{ old('role', $user->role) === 'admin' ? 'checked' : '' }} 
                                                       id="role_admin" class="d-none">
                                                <div class="bg-danger rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                                     style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                    <i class="fas fa-crown"></i>
                                                </div>
                                                <h6 class="fw-bold">Administrateur</h6>
                                                <p class="small text-muted mb-0">Accès complet au système</p>
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
                                    <option value="{{ $roleId }}" {{ old('role_id', $user->role_id) == $roleId ? 'selected' : '' }}>
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

                        <!-- Section Statut du compte -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="bg-info rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-toggle-on"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Statut du compte</h5>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-chart-line text-info me-2"></i>
                                    Statut <span class="text-danger">*</span>
                                </label>
                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <div class="card text-center status-option {{ old('status', $user->status ?? 'active') === 'active' ? 'border-success bg-light' : '' }}" 
                                             style="cursor: pointer;" onclick="selectStatus('active')">
                                            <div class="card-body py-3">
                                                <input type="radio" name="status" value="active" 
                                                       {{ old('status', $user->status ?? 'active') === 'active' ? 'checked' : '' }} 
                                                       id="status_active" class="d-none">
                                                <div class="badge bg-success mb-2">
                                                    <i class="fas fa-check-circle me-1"></i>Actif
                                                </div>
                                                <small class="d-block">Opérationnel</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="card text-center status-option {{ old('status', $user->status ?? 'active') === 'inactive' ? 'border-secondary bg-light' : '' }}" 
                                             style="cursor: pointer;" onclick="selectStatus('inactive')">
                                            <div class="card-body py-3">
                                                <input type="radio" name="status" value="inactive" 
                                                       {{ old('status', $user->status ?? 'active') === 'inactive' ? 'checked' : '' }} 
                                                       id="status_inactive" class="d-none">
                                                <div class="badge bg-secondary mb-2">
                                                    <i class="fas fa-pause-circle me-1"></i>Inactif
                                                </div>
                                                <small class="d-block">Désactivé</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="card text-center status-option {{ old('status', $user->status ?? 'active') === 'suspended' ? 'border-danger bg-light' : '' }}" 
                                             style="cursor: pointer;" onclick="selectStatus('suspended')">
                                            <div class="card-body py-3">
                                                <input type="radio" name="status" value="suspended" 
                                                       {{ old('status', $user->status ?? 'active') === 'suspended' ? 'checked' : '' }} 
                                                       id="status_suspended" class="d-none">
                                                <div class="badge bg-danger mb-2">
                                                    <i class="fas fa-ban me-1"></i>Suspendu
                                                </div>
                                                <small class="d-block">Compte bloqué</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="card text-center status-option {{ old('status', $user->status ?? 'active') === 'pending' ? 'border-warning bg-light' : '' }}" 
                                             style="cursor: pointer;" onclick="selectStatus('pending')">
                                            <div class="card-body py-3">
                                                <input type="radio" name="status" value="pending" 
                                                       {{ old('status', $user->status ?? 'active') === 'pending' ? 'checked' : '' }} 
                                                       id="status_pending" class="d-none">
                                                <div class="badge bg-warning text-dark mb-2">
                                                    <i class="fas fa-hourglass-half me-1"></i>En attente
                                                </div>
                                                <small class="d-block">Validation</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('status')
                                <div class="text-danger mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                            
                            <div class="form-check form-switch">
                                <!-- Hidden input pour s'assurer qu'une valeur est toujours envoyée -->
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_active">
                                    <i class="fas fa-power-off text-success me-2"></i>
                                    Compte actif
                                </label>
                                <div class="form-text">Autoriser la connexion à ce compte</div>
                                @error('is_active')
                                <div class="text-danger mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Section Mot de passe -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <div class="bg-warning rounded-circle text-dark d-flex align-items-center justify-content-center me-3" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Sécurité</h5>
                            </div>
                            
                            <div class="card" style="background: linear-gradient(135deg, rgba(255, 205, 0, 0.1), rgba(255, 205, 0, 0.05));">
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="changePassword">
                                        <label class="form-check-label fw-bold" for="changePassword">
                                            <i class="fas fa-key text-warning me-2"></i>
                                            Modifier le mot de passe
                                        </label>
                                        <div class="form-text">Cochez pour définir un nouveau mot de passe</div>
                                    </div>
                                    
                                    <div id="passwordFields" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="password" class="form-label fw-bold">
                                                    <i class="fas fa-lock text-warning me-2"></i>
                                                    Nouveau mot de passe
                                                </label>
                                                <input type="password" id="password" name="password" class="form-control form-control-lg" 
                                                       minlength="8">
                                                <div class="form-text">Minimum 8 caractères</div>
                                                @error('password')
                                                <div class="text-danger mt-1">
                                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="password_confirmation" class="form-label fw-bold">
                                                    <i class="fas fa-lock text-warning me-2"></i>
                                                    Confirmer le mot de passe
                                                </label>
                                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg">
                                                <div class="form-text">Ressaisir le nouveau mot de passe</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                            
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>
                                Sauvegarder les modifications
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
.stats-card:hover {
    transform: translateY(-2px);
    transition: transform 0.3s ease;
}

.role-option:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.status-option:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.form-control:focus {
    border-color: #009e3f;
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
}

.form-select:focus {
    border-color: #009e3f;
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 63, 0.25);
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
    console.log('Formulaire d\'édition utilisateur chargé');
    
    // Gestion du toggle mot de passe
    const changePasswordToggle = document.getElementById('changePassword');
    const passwordFields = document.getElementById('passwordFields');
    const passwordInput = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    if (changePasswordToggle) {
        changePasswordToggle.addEventListener('change', function() {
            if (this.checked) {
                passwordFields.style.display = 'block';
                passwordInput.setAttribute('required', true);
                passwordConfirmation.setAttribute('required', true);
            } else {
                passwordFields.style.display = 'none';
                passwordInput.removeAttribute('required');
                passwordConfirmation.removeAttribute('required');
                passwordInput.value = '';
                passwordConfirmation.value = '';
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
    
    // Soumission du formulaire avec validation
    const form = document.getElementById('editUserForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = passwordInput ? passwordInput.value : '';
            const confirmation = passwordConfirmation ? passwordConfirmation.value : '';
            
            if (changePasswordToggle && changePasswordToggle.checked) {
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
                
                if (password.length < 8) {
                    e.preventDefault();
                    showNotification('error', 'Le mot de passe doit contenir au moins 8 caractères');
                    passwordInput.focus();
                    return false;
                }
            }
            
            // Animation du bouton de soumission
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalContent = submitBtn.innerHTML;
                submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Enregistrement...';
                submitBtn.disabled = true;
            }
            
            showNotification('info', 'Enregistrement des modifications en cours...');
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
        option.classList.remove('border-success', 'border-warning', 'border-danger', 'bg-light');
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

// Sélection des statuts
function selectStatus(status) {
    // Retirer la sélection de tous les statuts
    document.querySelectorAll('.status-option').forEach(option => {
        option.classList.remove('border-success', 'border-secondary', 'border-danger', 'border-warning', 'bg-light');
    });
    
    // Sélectionner le statut choisi
    const selectedOption = document.querySelector(`#status_${status}`).closest('.status-option');
    document.getElementById(`status_${status}`).checked = true;
    
    // Appliquer le style correspondant et logique de l'état actif
    const isActiveCheckbox = document.getElementById('is_active');
    
    if (status === 'active') {
        selectedOption.classList.add('border-success', 'bg-light');
        isActiveCheckbox.checked = true;
        showNotification('info', 'Compte automatiquement activé');
    } else if (status === 'inactive') {
        selectedOption.classList.add('border-secondary', 'bg-light');
        isActiveCheckbox.checked = false;
        showNotification('warning', 'Compte automatiquement désactivé');
    } else if (status === 'suspended') {
        selectedOption.classList.add('border-danger', 'bg-light');
        isActiveCheckbox.checked = false;
        showNotification('warning', 'Compte automatiquement désactivé');
    } else if (status === 'pending') {
        selectedOption.classList.add('border-warning', 'bg-light');
    }
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