{{-- resources/views/admin/provinces/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Créer une Province')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nouvelle Province
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-2">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.geolocalisation.provinces.index') }}">Provinces</a>
                            </li>
                            <li class="breadcrumb-item active">Créer</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.geolocalisation.provinces.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            {{-- Messages d'erreur généraux --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Erreur :</strong> Veuillez corriger les champs indiqués ci-dessous.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Formulaire --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations de la Province
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.geolocalisation.provinces.store') }}" novalidate>
                        @csrf
                        @include('admin.geolocalisation.provinces.form', ['province' => $province])
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.geolocalisation.provinces.index') }}" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Créer la Province
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection