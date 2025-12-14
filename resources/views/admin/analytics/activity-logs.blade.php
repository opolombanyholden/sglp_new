@extends('layouts.admin')

@section('title', $title ?? 'Logs d\'activité')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.analytics') }}">Analytics</a></li>
    <li class="breadcrumb-item active">Logs d'activité</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-history"></i> {{ $title ?? 'Logs d\'activité' }}</h2>
                    <div>
                        <a href="{{ route('admin.analytics') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour Analytics
                        </a>
                    </div>
                </div>

                @if($logs->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Liste des activités</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Utilisateur</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($logs as $log)
                                            <tr>
                                                <td>{{ $log->id ?? '#' }}</td>
                                                <td>
                                                    @if(isset($log->user))
                                                        {{ $log->user->name ?? 'N/A' }}
                                                    @else
                                                        Système
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        {{ $log->action ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>{{ $log->description ?? 'Aucune description' }}</td>
                                                <td>{{ $log->type ?? 'N/A' }}</td>
                                                <td>
                                                    @if(isset($log->created_at))
                                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $logs->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Aucun log d'activité</h5>
                        <p class="mb-0">
                            Le système de logs d'activité n'est pas encore configuré ou aucune activité n'a été enregistrée.
                        </p>
                        <hr>
                        <p class="mb-0">
                            <strong>Note :</strong> Pour activer les logs d'activité, vous devez créer le modèle
                            <code>App\Models\ActivityLog</code> et la migration correspondante.
                        </p>
                    </div>

                    <!-- Informations sur l'implémentation -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-code"></i> Implémentation du système de logs</h5>
                        </div>
                        <div class="card-body">
                            <p>Pour implémenter le système de logs d'activité, suivez ces étapes :</p>
                            <ol>
                                <li>Créer le modèle : <code>php artisan make:model ActivityLog -m</code></li>
                                <li>Définir la structure de la table dans la migration</li>
                                <li>Ajouter les relations nécessaires (user, etc.)</li>
                                <li>Implémenter le logging dans les contrôleurs</li>
                            </ol>

                            <h6 class="mt-3">Structure suggérée :</h6>
                            <ul>
                                <li><strong>user_id</strong> - ID de l'utilisateur</li>
                                <li><strong>action</strong> - Type d'action (create, update, delete, etc.)</li>
                                <li><strong>description</strong> - Description de l'action</li>
                                <li><strong>type</strong> - Type d'entité (Organisation, User, etc.)</li>
                                <li><strong>ip_address</strong> - Adresse IP</li>
                                <li><strong>user_agent</strong> - User agent du navigateur</li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection