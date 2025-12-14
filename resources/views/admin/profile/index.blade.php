@extends('layouts.admin')

@section('title', $title ?? 'Mon Profil')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mon Profil</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-circle"></i> {{ $title ?? 'Mon Profil' }}</h2>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab">
                            <i class="fas fa-eye"></i> Vue d'ensemble
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="edit-tab" data-toggle="tab" href="#edit" role="tab">
                            <i class="fas fa-edit"></i> Modifier le profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav- link" id="password-tab" data-toggle="tab" href="#password" role="tab">
                            <i class="fas fa-lock"></i> Changer le mot de passe
                        </a>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="profileTabsContent">

                    <!-- TAB 1: Vue d'ensemble -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row">
                            <!-- Carte Profil -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="profile-avatar-large mb-3">
                                            @if(isset($user->avatar) && $user->avatar)
                                                <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="rounded-circle"
                                                    width="150" height="150">
                                            @else
                                                <div class="avatar-placeholder">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <h4>{{ $user->name }}</h4>
                                        <p class="text-muted">{{ $user->role ?? 'Administrateur' }}</p>
                                        <p class="text-muted"><i class="fas fa-envelope"></i> {{ $user->email }}</p>

                                        <!-- Upload Avatar Form -->
                                        <form action="{{ route('admin.profile.avatar') }}" method="POST"
                                            enctype="multipart/form-data" class="mt-3">
                                            @csrf
                                            <div class="custom-file mb-2">
                                                <input type="file" class="custom-file-input" id="avatar" name="avatar"
                                                    accept="image/*">
                                                <label class="custom-file-label" for="avatar">Changer la photo</label>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-upload"></i> Télécharger
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations du compte -->
                            <div class="col-md-8">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations du compte</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Nom complet :</strong> {{ $user->name }}</p>
                                                <p><strong>Email :</strong> {{ $user->email }}</p>
                                                <p><strong>Téléphone :</strong> {{ $user->phone ?? 'Non renseigné' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Adresse :</strong> {{ $user->address ?? 'Non renseignée' }}</p>
                                                <p><strong>Ville :</strong> {{ $user->city ?? 'Non renseignée' }}</p>
                                                <p><strong>Rôle :</strong> <span
                                                        class="badge bg-primary">{{ $user->role ?? 'Admin' }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistiques -->
                                @if(isset($stats))
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistiques</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-md-3">
                                                    <h4>{{ $stats['dossiers_traites'] }}</h4>
                                                    <p class="text-muted">Dossiers traités</p>
                                                </div>
                                                <div class="col-md-3">
                                                    <h4>{{ $stats['actions_today'] }}</h4>
                                                    <p class="text-muted">Actions aujourd'hui</p>
                                                </div>
                                                <div class="col-md-3">
                                                    <h4>{{ $stats['login_count'] }}</h4>
                                                    <p class="text-muted">Connexions</p>
                                                </div>
                                                <div class="col-md-3">
                                                    <p class="text-muted">Membre depuis</p>
                                                    <p><strong>{{ $stats['account_age'] }}</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Modifier le profil -->
                    <div class="tab-pane fade" id="edit" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-edit"></i> Modifier mes informations</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.profile.update') }}" method="POST">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Nom complet <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                    id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email <span class="text-danger">*</span></label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                                    name="email" value="{{ old('email', $user->email) }}" required>
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone">Téléphone</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                    id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city">Ville</label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                                    id="city" name="city" value="{{ old('city', $user->city) }}">
                                                @error('city')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="address">Adresse complète</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="address"
                                            name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Enregistrer les modifications
                                        </button>
                                        <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Annuler
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Changer le mot de passe -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-lock"></i> Changer mon mot de passe</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Votre mot de passe doit contenir au minimum 8
                                    caractères.
                                </div>

                                <form action="{{ route('admin.profile.password') }}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label for="current_password">Mot de passe actuel <span
                                                class="text-danger">*</span></label>
                                        <input type="password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            id="current_password" name="current_password" required>
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Nouveau mot de passe <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation">Confirmer le nouveau mot de passe <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation" required>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key"></i> Changer le mot de passe
                                        </button>
                                        <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Annuler
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            color: white;
            margin: 0 auto;
        }

        .custom-file-label::after {
            content: "Parcourir";
        }
    </style>

    <script>
        // Update file input label
        document.getElementById('avatar')?.addEventListener('change', function (e) {
            var fileName = e.target.files[0]?.name || 'Choisir un fichier';
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    </script>
@endsection