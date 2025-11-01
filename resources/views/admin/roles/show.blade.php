{{-- resources/views/admin/roles/show.blade.php --}}
@extends('layouts.admin')
@section('title', 'Détail du Rôle')

@section('content')
<div class="container-fluid">
    <!-- Header principal avec informations du rôle -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem; backdrop-filter: blur(10px);">
                                    <i class="fas fa-{{ $role->level >= 8 ? 'crown' : ($role->level >= 6 ? 'user-tie' : ($role->level >= 4 ? 'user' : 'eye')) }} fa-3x"></i>
                                </div>
                                <div>
                                    <h2 class="mb-1">{{ $role->display_name ?? $role->name }}</h2>
                                    <code class="text-white-50 fs-6">{{ $role->name }}</code>
                                    @if(isset($roleStats) && $roleStats['is_system'])
                                        <div class="mt-2">
                                            <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; backdrop-filter: blur(10px); display: inline-flex; align-items: center; gap: 0.5rem;">
                                                <i class="fas fa-shield-alt"></i>
                                                Rôle Système
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <span style="font-size: 1.25rem; padding: 8px 20px; border-radius: 25px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; @if($role->level == 10) background: linear-gradient(135deg, #8b1538, #c41e3a); @elseif($role->level == 9) background: linear-gradient(135deg, #003f7f, #0056b3); @elseif($role->level == 8) background: linear-gradient(135deg, #009e3f, #00b347); @elseif($role->level == 6) background: linear-gradient(135deg, #17a2b8, #20c997); @elseif($role->level == 4) background: linear-gradient(135deg, #ffcd00, #fd7e14); color: #212529; @elseif($role->level == 2) background: linear-gradient(135deg, #6c757d, #adb5bd); @else background: linear-gradient(135deg, #e9ecef, #f8f9fa); color: #495057; @endif color: white;">
                                    <i class="fas fa-layer-group"></i>
                                    Niveau {{ $role->level }}/10
                                </span>
                                
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 6px 12px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; @if($role->is_active) background: rgba(0, 158, 63, 0.2); color: #009e3f; @else background: rgba(108, 117, 125, 0.2); color: #6c757d; @endif">
                                    <i class="fas fa-circle"></i>
                                    {{ $role->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-light btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-edit me-2"></i>
                                    Modifier
                                </a>
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-light btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Retour
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques du rôle -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $roleStats['users_count'] ?? $role->users_count ?? 0 }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Utilisateurs</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffcd00 0%, #ffd700 100%);">
                <div class="card-body text-dark text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(33, 37, 41, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $roleStats['permissions_count'] ?? $role->permissions_count ?? 0 }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Permissions</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                <div class="card-body text-white text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $roleStats['days_since_creation'] ?? ($role->created_at ? $role->created_at->diffInDays() : 0) }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Jours d'existence</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $role->level }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Niveau hiérarchique</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations générales -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2" style="color: #009e3f;"></i>
                            Informations Générales
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Nom du rôle :</label>
                        <div>
                            <code style="background: #f8f9fc; padding: 4px 8px; border-radius: 6px;">{{ $role->name }}</code>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Nom d'affichage :</label>
                        <div class="fw-bold" style="color: #003f7f;">{{ $role->display_name ?? $role->name }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Description :</label>
                        <div>{{ $role->description ?: 'Aucune description fournie' }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Type :</label>
                        <div>
                            @if(isset($roleStats) && $roleStats['is_system'])
                                <span class="badge" style="background: #003f7f; color: white; padding: 6px 12px; border-radius: 15px;">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Rôle Système
                                </span>
                            @else
                                <span class="badge" style="background: #009e3f; color: white; padding: 6px 12px; border-radius: 15px;">
                                    <i class="fas fa-user-cog me-1"></i>
                                    Rôle Personnalisé
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Créé le :</label>
                        <div>{{ $role->created_at ? $role->created_at->format('d/m/Y à H:i') : 'Non défini' }}</div>
                    </div>
                    
                    <div>
                        <label class="fw-bold text-muted small">Dernière modification :</label>
                        <div>{{ $role->updated_at ? $role->updated_at->format('d/m/Y à H:i') : 'Non défini' }}</div>
                    </div>
                </div>
            </div>

            <!-- Utilisateurs ayant ce rôle -->
            @if($role->users && $role->users->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2" style="color: #17a2b8;"></i>
                            Utilisateurs ({{ $role->users->count() }})
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position: relative; padding-left: 2rem;">
                        <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);"></div>
                        
                        @foreach($role->users->take(5) as $user)
                        <div style="position: relative; padding-bottom: 1.5rem;">
                            <div style="position: absolute; left: -23px; top: 5px; width: 12px; height: 12px; background: white; border: 3px solid #009e3f; border-radius: 50%;"></div>
                            <div style="background: white; padding: 1rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: #003f7f;">
                                            {{ $user->name ?? 'Utilisateur' }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $user->email ?? 'Email non défini' }}
                                        </div>
                                        <small style="color: #17a2b8;">
                                            Membre depuis {{ $user->created_at ? $user->created_at->diffForHumans() : 'Date inconnue' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($role->users->count() > 5)
                        <div class="text-center mt-3">
                            <button class="btn btn-outline-primary btn-sm" style="border-radius: 20px;">
                                Voir tous les utilisateurs ({{ $role->users->count() }})
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Permissions du rôle -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2" style="color: #ffcd00;"></i>
                            Permissions du Rôle ({{ $roleStats['permissions_count'] ?? $role->permissions_count ?? 0 }})
                        </h5>
                        <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-outline-warning btn-sm" style="border-radius: 20px;">
                            <i class="fas fa-edit me-1"></i>
                            Gérer
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($permissionsByCategory) && $permissionsByCategory->count() > 0)
                        <div class="row">
                            @foreach($permissionsByCategory as $category => $permissions)
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header text-white" style="background: {{ $categoryColors[$category] ?? '#6c757d' }}; border-radius: 15px 15px 0 0;">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-{{ $categoryIcons[$category] ?? 'key' }} me-2"></i>
                                            {{ $categoryLabels[$category] ?? ucfirst($category) }}
                                            <span class="badge bg-light text-dark ms-auto">{{ $permissions->count() }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($permissions as $permission)
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <div style="width: 32px; height: 32px; background: rgba(0, 158, 63, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #009e3f;">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold mb-1" style="color: #003f7f;">
                                                    {{ $permission->display_name ?? $permission->name }}
                                                </div>
                                                @if($permission->description)
                                                <div class="text-muted small">
                                                    {{ $permission->description }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-key fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
                            <h5 class="text-muted">Aucune permission assignée</h5>
                            <p class="text-muted">Ce rôle n'a actuellement aucune permission définie.</p>
                            <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-primary" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); border: none; border-radius: 25px; padding: 12px 30px;">
                                <i class="fas fa-plus me-2"></i>
                                Gérer les permissions
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions disponibles -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-tools me-2" style="color: #17a2b8;"></i>
                            Actions Disponibles
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                            <i class="fas fa-edit me-2"></i>
                            Modifier le rôle
                        </a>
                        
                        <a href="{{ route('admin.roles.permissions', $role->id) }}" class="btn btn-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                            <i class="fas fa-key me-2"></i>
                            Gérer les permissions
                        </a>
                        
                        <button onclick="duplicateRole({{ $role->id }})" class="btn btn-lg" style="background: linear-gradient(135deg, #ffcd00 0%, #fd7e14 100%); color: #212529; border: none; border-radius: 25px; padding: 12px 30px;">
                            <i class="fas fa-copy me-2"></i>
                            Dupliquer le rôle
                        </button>
                        
                        <button onclick="exportRole({{ $role->id }})" class="btn btn-outline-primary btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                            <i class="fas fa-download me-2"></i>
                            Exporter
                        </button>
                        
                        @if(isset($roleStats) && $roleStats['can_be_deleted'])
                        <button onclick="confirmDelete({{ $role->id }}, '{{ $role->display_name }}')" class="btn btn-lg" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                            <i class="fas fa-trash me-2"></i>
                            Supprimer
                        </button>
                        @endif
                    </div>
                    
                    @if(isset($roleStats) && $roleStats['is_system'])
                    <div class="alert alert-info mt-3" style="border-radius: 12px;">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Rôle système :</strong> Ce rôle fait partie du système et ne peut pas être supprimé. Certaines modifications peuvent être limitées.
                    </div>
                    @endif
                    
                    @if(($roleStats['users_count'] ?? $role->users_count ?? 0) > 0 && !(isset($roleStats) && $roleStats['is_system']))
                    <div class="alert alert-warning mt-3" style="border-radius: 12px;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Ce rôle est assigné à {{ $roleStats['users_count'] ?? $role->users_count }} utilisateur(s). 
                        Pour le supprimer, vous devez d'abord réassigner ces utilisateurs à d'autres rôles.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore spécialisé détail -->
<div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 1000;">
    <div id="fabMenu">
        <div onclick="toggleFAB()" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.3s ease;">
            <i class="fas fa-user-shield" style="color: white; font-size: 1.5rem;"></i>
        </div>
        <div class="fab-options" style="position: absolute; bottom: 70px; right: 0; display: flex; flex-direction: column; gap: 10px; opacity: 0; visibility: hidden; transition: all 0.3s ease;">
            <button onclick="window.location.href='{{ route('admin.roles.edit', $role->id) }}'" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #009e3f; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button onclick="window.location.href='{{ route('admin.roles.permissions', $role->id) }}'" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #ffcd00; color: #000; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Permissions">
                <i class="fas fa-key"></i>
            </button>
            <button onclick="duplicateRole({{ $role->id }})" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #003f7f; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Dupliquer">
                <i class="fas fa-copy"></i>
            </button>
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

#fabMenu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-options button:hover {
    transform: scale(1.1);
}
</style>

<script>
console.log('Module Détail Rôle SGLP chargé');

/**
 * Confirmer suppression du rôle
 */
function confirmDelete(roleId, roleName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le rôle "${roleName}" ?\n\nCette action est irréversible.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/roles/${roleId}`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Dupliquer le rôle
 */
function duplicateRole(roleId) {
    if (confirm('Voulez-vous créer une copie de ce rôle ?')) {
        // Créer un formulaire POST pour la duplication
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/roles/${roleId}/duplicate`;
        form.style.display = 'none';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Exporter le rôle
 */
function exportRole(roleId) {
    window.open(`/admin/roles/export?role_ids[]=${roleId}`, '_blank');
}

/**
 * FAB toggle
 */
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

// Fermer FAB en cliquant ailleurs
document.addEventListener('click', function(event) {
    const fabMenu = document.getElementById('fabMenu');
    if (!fabMenu.contains(event.target)) {
        fabMenu.classList.remove('active');
    }
});

/**
 * Basculer le statut du rôle
 */
function toggleRoleStatus(roleId) {
    fetch(`/admin/roles/${roleId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors du changement de statut: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors du changement de statut');
    });
}
</script>
@endsection