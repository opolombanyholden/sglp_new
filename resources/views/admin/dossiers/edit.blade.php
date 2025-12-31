@extends('layouts.admin')

@section('title', 'Modifier le Dossier ' . ($dossier->numero_dossier ?? ''))

@section('content')
    <div class="dossier-form-wrapper">
        <!-- Fond animé -->
        <div class="cosmic-bg"></div>
        <div class="floating-orbs">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
        </div>
        <div class="neon-grid"></div>
        <div class="particles-container" id="particlesContainer"></div>

        <!-- Barre de progression globale -->
        <div class="global-progress">
            <div class="global-progress-bar" id="globalProgress"></div>
        </div>

        <div class="container-fluid py-4">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><strong>Erreurs :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Hero Header -->
            <div class="hero-header">
                <div class="hero-content">
                    <div class="hero-title-group">
                        <div class="hero-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="hero-text">
                            <h1>Modifier le Dossier</h1>
                            <p>{{ $dossier->numero_dossier ?? 'Dossier' }} -
                                {{ $dossier->organisation->nom ?? 'Organisation' }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('admin.dossiers.index') }}" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Retour</span>
                    </a>
                </div>
            </div>

            <!-- Stepper horizontal -->
            <div class="stepper-container">
                <div class="stepper">
                    <div class="stepper-track">
                        <div class="stepper-progress" id="stepperProgress"></div>
                    </div>
                    <div class="step active" data-step="1" onclick="goToSection('collapseType')">
                        <div class="step-number">1</div>
                        <div class="step-label">Type</div>
                    </div>
                    <div class="step" data-step="2" onclick="goToSection('collapseDeclarant')">
                        <div class="step-number">2</div>
                        <div class="step-label">Déclarant</div>
                    </div>
                    <div class="step" data-step="3" onclick="goToSection('collapseOrganisation')">
                        <div class="step-number">3</div>
                        <div class="step-label">Organisation</div>
                    </div>
                    <div class="step" data-step="4" onclick="goToSection('collapseLocalisation')">
                        <div class="step-number">4</div>
                        <div class="step-label">Localisation</div>
                    </div>
                    <div class="step" data-step="5" onclick="goToSection('collapseFondateurs')">
                        <div class="step-number">5</div>
                        <div class="step-label">Fondateurs</div>
                    </div>
                    <div class="step" data-step="6" onclick="goToSection('collapseMembresBureau')">
                        <div class="step-number">6</div>
                        <div class="step-label">Bureau</div>
                    </div>
                    <div class="step" data-step="7" onclick="goToSection('collapseAdherents')">
                        <div class="step-number">7</div>
                        <div class="step-label">Adhérents</div>
                    </div>
                    <div class="step" data-step="8" onclick="goToSection('collapseDocuments')">
                        <div class="step-number">8</div>
                        <div class="step-label">Documents</div>
                    </div>
                </div>
            </div>

            <!-- Formulaire principal -->
            <form id="editDossierForm" method="POST" action="{{ route('admin.dossiers.update', $dossier->id) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-card">
                    <div class="p-4">
                        <div class="accordion accordion-modern" id="accordionDossier">

                            <!-- ÉTAPE 1: TYPE -->
                            <div class="accordion-item" id="itemType">
                                <div class="accordion-header">
                                    <button class="accordion-trigger" type="button" data-toggle="collapse"
                                        data-target="#collapseType" aria-expanded="true">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box green"><i class="fas fa-building"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Étape 1 sur 8</div>
                                                <div class="step-title">Type d'organisation</div>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseType" class="accordion-collapse collapse show"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">
                                        <div class="type-selection-grid">
                                            @foreach($typesOrganisation as $type)
                                                <label class="type-option" id="typeCard{{ $type->id }}">
                                                    <input type="radio" name="organisation_type_id" value="{{ $type->id }}"
                                                        data-min-fondateurs="{{ $type->nb_min_fondateurs_majeurs ?? 2 }}"
                                                        data-min-adherents="{{ $type->nb_min_adherents_creation ?? 0 }}"
                                                        data-code="{{ $type->code }}"
                                                        {{ old('organisation_type_id', $dossier->organisation->organisation_type_id ?? null) == $type->id ? 'checked' : '' }}
                                                        {{ isset($dossier) ? 'disabled' : '' }}>
                                                    <div class="type-card-inner">
                                                        <div class="type-icon-wrapper"
                                                            style="background: linear-gradient(135deg, {{ $type->couleur ?? '#009e3f' }}, {{ $type->couleur ?? '#00c44f' }}dd);">
                                                            <i class="fas {{ $type->icone ?? 'fa-building' }}"></i>
                                                        </div>
                                                        <div class="type-title">{{ $type->nom }}</div>
                                                        <div class="type-details">
                                                            <span>Min. {{ $type->nb_min_fondateurs_majeurs ?? 2 }}
                                                                fondateurs</span>
                                                            @if(($type->nb_min_adherents_creation ?? 0) > 0)
                                                                <span>Min. {{ $type->nb_min_adherents_creation }} adhérents</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        <div class="alert-box info mt-4" id="typeConfigInfo" style="display: none;">
                                            <i class="fas fa-info-circle fa-lg"></i>
                                            <div id="typeConfigText"></div>
                                        </div>

                                        <div class="section-footer">
                                            <div></div>
                                            <button type="button" class="btn-nav btn-nav-next"
                                                onclick="goToSection('collapseDeclarant')">
                                                Suivant <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÉTAPE 2: DÉCLARANT -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-trigger collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseDeclarant" aria-expanded="false">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box yellow"><i class="fas fa-user-tie"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Étape 2 sur 8</div>
                                                <div class="step-title">Informations du Déclarant</div>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseDeclarant" class="accordion-collapse collapse"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label-modern">NIP <span class="required">*</span></label>
                                                <input type="text" class="form-input-modern" name="demandeur_nip"
                                                    id="demandeur_nip"
                                                    value="{{ old('demandeur_nip', $declarant['nip'] ?? '') }}" required
                                                    placeholder="Numéro d'identification">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Civilité</label>
                                                <select class="form-select-modern" name="demandeur_civilite"
                                                    id="demandeur_civilite">
                                                    <option value="M"
                                                        {{ old('demandeur_civilite', $declarant['civilite'] ?? '') == 'M' ? 'selected' : '' }}>
                                                        Monsieur
                                                    </option>
                                                    <option value="Mme"
                                                        {{ old('demandeur_civilite', $declarant['civilite'] ?? '') == 'Mme' ? 'selected' : '' }}>
                                                        Madame
                                                    </option>
                                                    <option value="Mlle"
                                                        {{ old('demandeur_civilite', $declarant['civilite'] ?? '') == 'Mlle' ? 'selected' : '' }}>
                                                        Mademoiselle</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Nom <span class="required">*</span></label>
                                                <input type="text" class="form-input-modern" name="demandeur_nom"
                                                    id="demandeur_nom"
                                                    value="{{ old('demandeur_nom', $declarant['nom'] ?? '') }}" required
                                                    placeholder="Nom de famille">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Prénom <span
                                                        class="required">*</span></label>
                                                <input type="text" class="form-input-modern" name="demandeur_prenom"
                                                    id="demandeur_prenom"
                                                    value="{{ old('demandeur_prenom', $declarant['prenom'] ?? '') }}"
                                                    required placeholder="Prénom">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Téléphone <span
                                                        class="required">*</span></label>
                                                <input type="tel" class="form-input-modern" name="demandeur_telephone"
                                                    id="demandeur_telephone"
                                                    value="{{ old('demandeur_telephone', $declarant['telephone'] ?? '') }}"
                                                    required placeholder="+241 XX XX XX XX">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Email</label>
                                                <input type="email" class="form-input-modern" name="demandeur_email"
                                                    id="demandeur_email"
                                                    value="{{ old('demandeur_email', $declarant['email'] ?? '') }}"
                                                    placeholder="email@exemple.com">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Fonction/Rôle</label>
                                                <select class="form-select-modern" name="demandeur_role"
                                                    id="demandeur_role">
                                                    <option value="Déclarant"
                                                        {{ old('demandeur_role', $declarant['role'] ?? '') == 'Déclarant' ? 'selected' : '' }}>
                                                        Déclarant</option>
                                                    <option value="Représentant légal"
                                                        {{ old('demandeur_role', $declarant['role'] ?? '') == 'Représentant légal' ? 'selected' : '' }}>
                                                        Représentant légal</option>
                                                    <option value="Mandataire"
                                                        {{ old('demandeur_role', $declarant['role'] ?? '') == 'Mandataire' ? 'selected' : '' }}>
                                                        Mandataire</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="section-footer">
                                            <button type="button" class="btn-nav btn-nav-prev"
                                                onclick="goToSection('collapseType')">
                                                <i class="fas fa-arrow-left"></i> Précédent
                                            </button>
                                            <button type="button" class="btn-nav btn-nav-next"
                                                onclick="goToSection('collapseOrganisation')">
                                                Suivant <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÉTAPE 3: ORGANISATION -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-trigger collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseOrganisation" aria-expanded="false">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box blue"><i class="fas fa-landmark"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Étape 3 sur 8</div>
                                                <div class="step-title">Informations de l'Organisation</div>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseOrganisation" class="accordion-collapse collapse"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">
                                        <div class="form-grid">
                                            <div class="form-group span-2">
                                                <label class="form-label-modern">Nom de l'organisation <span
                                                        class="required">*</span></label>
                                                <input type="text" class="form-input-modern" name="org_nom" id="org_nom"
                                                    value="{{ old('org_nom', $dossier->organisation->nom ?? '') }}" required
                                                    placeholder="Dénomination officielle">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Sigle / Acronyme</label>
                                                <input type="text" class="form-input-modern" name="org_sigle" id="org_sigle"
                                                    value="{{ old('org_sigle', $dossier->organisation->sigle ?? '') }}"
                                                    placeholder="Ex: ASBL, ONG...">
                                            </div>
                                            <div class="form-group span-3">
                                                <label class="form-label-modern">Objet social <span
                                                        class="required">*</span></label>
                                                <textarea class="form-textarea-modern" name="org_objet" id="org_objet"
                                                    rows="3" required
                                                    placeholder="Description des activités et objectifs...">{{ old('org_objet', $dossier->organisation->objet ?? '') }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Domaine d'activité <span
                                                        class="required">*</span></label>
                                                <select class="form-select-modern" name="org_domaine_activite_id"
                                                    id="org_domaine_activite_id" required>
                                                    <option value="">Sélectionner un domaine...</option>
                                                    @foreach($domainesActivite as $domaine)
                                                        <option value="{{ $domaine->id }}"
                                                            {{ old('org_domaine_activite_id', $dossier->organisation->domaine_activite_id ?? null) == $domaine->id ? 'selected' : '' }}>
                                                            {{ $domaine->nom }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Date de création <span
                                                        class="required">*</span></label>
                                                <input type="date" class="form-input-modern" name="org_date_creation"
                                                    id="org_date_creation"
                                                    value="{{ old('org_date_creation', optional($dossier->organisation->date_creation)->format('Y-m-d') ?? '') }}"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Téléphone <span
                                                        class="required">*</span></label>
                                                <input type="tel" class="form-input-modern" name="org_telephone"
                                                    id="org_telephone"
                                                    value="{{ old('org_telephone', $dossier->organisation->telephone ?? '') }}"
                                                    required placeholder="+241 XX XX XX XX">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-modern">Email</label>
                                                <input type="email" class="form-input-modern" name="org_email"
                                                    id="org_email"
                                                    value="{{ old('org_email', $dossier->organisation->email ?? '') }}"
                                                    placeholder="contact@organisation.ga">
                                            </div>
                                        </div>

                                        <div class="section-footer">
                                            <button type="button" class="btn-nav btn-nav-prev"
                                                onclick="goToSection('collapseDeclarant')">
                                                <i class="fas fa-arrow-left"></i> Précédent
                                            </button>
                                            <button type="button" class="btn-nav btn-nav-next"
                                                onclick="goToSection('collapseLocalisation')">
                                                Suivant <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÉTAPE 4: LOCALISATION -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-trigger collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseLocalisation" aria-expanded="false">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box purple"><i class="fas fa-map-marker-alt"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Étape 4 sur 8</div>
                                                <div class="step-title">Localisation du Siège</div>
                                            </div>
                                        </div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseLocalisation" class="accordion-collapse collapse"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">

                                        <!-- Champs cachés pour les noms textuels -->
                                        <input type="hidden" name="province" id="province_nom" value="{{ old('province', $dossier->organisation->province ?? '') }}">
                                        <input type="hidden" name="departement" id="departement_nom" value="{{ old('departement', $dossier->organisation->departement ?? '') }}">
                                        <input type="hidden" name="ville_commune" id="commune_nom" value="{{ old('ville_commune', $dossier->organisation->ville_commune ?? '') }}">
                                        <input type="hidden" name="arrondissement" id="arrondissement_text" value="{{ old('arrondissement', $dossier->organisation->arrondissement ?? '') }}">
                                        <input type="hidden" name="quartier" id="quartier_nom" value="{{ old('quartier', $dossier->organisation->quartier ?? '') }}">
                                        <input type="hidden" name="canton" id="canton_nom" value="{{ old('canton', $dossier->organisation->canton ?? '') }}">
                                        <input type="hidden" name="regroupement" id="regroupement_nom" value="{{ old('regroupement', $dossier->organisation->regroupement ?? '') }}">
                                        <input type="hidden" name="village" id="village_nom" value="{{ old('village', $dossier->organisation->village ?? '') }}">

                                        <!-- Sélection du type de zone -->
                                        <div class="zone-selector mb-4">
                                            <label class="form-label-modern">Type de zone <span
                                                    class="required">*</span></label>
                                            <div class="zone-options">
                                                <label class="zone-option">
                                                    <input type="radio" name="zone_type" value="urbaine"
                                                        {{ old('zone_type', $dossier->organisation->zone_type ?? 'urbaine') == 'urbaine' ? 'checked' : '' }}>
                                                    <div class="zone-card">
                                                        <i class="fas fa-city"></i>
                                                        <span>Zone Urbaine</span>
                                                        <small>Commune → Arrondissement → Quartier</small>
                                                    </div>
                                                </label>
                                                <label class="zone-option">
                                                    <input type="radio" name="zone_type" value="rurale"
                                                        {{ old('zone_type', $dossier->organisation->zone_type ?? '') == 'rurale' ? 'checked' : '' }}>
                                                    <div class="zone-card">
                                                        <i class="fas fa-tree"></i>
                                                        <span>Zone Rurale</span>
                                                        <small>Canton → Regroupement → Village</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-grid">
                                            <!-- Province (commun) -->
                                            <div class="form-group">
                                                <label class="form-label-modern">Province <span
                                                        class="required">*</span></label>
                                                <select class="form-select-modern" name="org_province_id"
                                                    id="org_province_id" required>
                                                    <option value="">Sélectionner une province...</option>
                                                    @foreach($provinces as $province)
                                                        <option value="{{ $province->id }}" data-nom="{{ $province->nom }}"
                                                            {{ old('org_province_id', $dossier->organisation->province_ref_id ?? '') == $province->id ? 'selected' : '' }}>
                                                            {{ $province->nom }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Département (commun) -->
                                            <div class="form-group">
                                                <label class="form-label-modern">Département <span
                                                        class="required">*</span></label>
                                                <select class="form-select-modern" name="org_departement_id"
                                                    id="org_departement_id" required disabled>
                                                    <option value="">Sélectionner province d'abord...</option>
                                                </select>
                                            </div>

                                            <!-- ========== ZONE URBAINE ========== -->
                                            <div class="form-group zone-urbaine-field">
                                                <label class="form-label-modern">Commune / Ville</label>
                                                <select class="form-select-modern" name="org_commune_id" id="org_commune_id"
                                                    disabled>
                                                    <option value="">Sélectionner département...</option>
                                                </select>
                                            </div>

                                            <div class="form-group zone-urbaine-field">
                                                <label class="form-label-modern">Arrondissement</label>
                                                <select class="form-select-modern" name="org_arrondissement_id"
                                                    id="org_arrondissement_id" disabled>
                                                    <option value="">Sélectionner commune...</option>
                                                </select>
                                            </div>

                                            <div class="form-group zone-urbaine-field">
                                                <label class="form-label-modern">Quartier</label>
                                                <select class="form-select-modern" name="org_quartier_id"
                                                    id="org_quartier_id" disabled>
                                                    <option value="">Sélectionner arrondissement...</option>
                                                </select>
                                            </div>

                                            <!-- ========== ZONE RURALE ========== -->
                                            <div class="form-group zone-rurale-field" style="display: none;">
                                                <label class="form-label-modern">Canton</label>
                                                <select class="form-select-modern" name="org_canton_id" id="org_canton_id"
                                                    disabled>
                                                    <option value="">Sélectionner département...</option>
                                                </select>
                                            </div>

                                            <div class="form-group zone-rurale-field" style="display: none;">
                                                <label class="form-label-modern">Regroupement</label>
                                                <select class="form-select-modern" name="org_regroupement_id"
                                                    id="org_regroupement_id" disabled>
                                                    <option value="">Sélectionner canton...</option>
                                                </select>
                                            </div>

                                            <div class="form-group zone-rurale-field" style="display: none;">
                                                <label class="form-label-modern">Village</label>
                                                <select class="form-select-modern" name="org_village_id" id="org_village_id"
                                                    disabled>
                                                    <option value="">Sélectionner regroupement...</option>
                                                </select>
                                            </div>

                                            <!-- ========== CHAMPS COMMUNS ========== -->
                                            <div class="form-group">
                                                <label class="form-label-modern">Lieu-dit</label>
                                                <input type="text" class="form-input-modern" name="org_lieu_dit"
                                                    id="org_lieu_dit"
                                                    value="{{ old('org_lieu_dit', $dossier->organisation->lieu_dit ?? '') }}"
                                                    placeholder="Lieu-dit (optionnel)">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label-modern">Préfecture <span
                                                        class="required">*</span></label>
                                                <input type="text" class="form-input-modern" name="org_prefecture"
                                                    id="org_prefecture"
                                                    value="{{ old('org_prefecture', $dossier->organisation->prefecture ?? '') }}"
                                                    required placeholder="Préfecture">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label-modern">Sous-Préfecture</label>
                                                <input type="text" class="form-input-modern" name="org_sous_prefecture"
                                                    id="org_sous_prefecture"
                                                    value="{{ old('org_sous_prefecture', $dossier->organisation->sous_prefecture ?? '') }}"
                                                    placeholder="Sous-préfecture (optionnel)">
                                            </div>

                                            <div class="form-group span-2">
                                                <label class="form-label-modern">Adresse complète du siège social <span
                                                        class="required">*</span></label>
                                                <input type="text" class="form-input-modern" name="org_adresse"
                                                    id="org_adresse"
                                                    value="{{ old('org_adresse', $dossier->organisation->siege_social ?? '') }}"
                                                    required placeholder="Numéro, rue, bâtiment...">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label-modern">Latitude</label>
                                                <input type="text" class="form-input-modern" name="org_latitude"
                                                    id="org_latitude"
                                                    value="{{ old('org_latitude', $dossier->organisation->latitude ?? '') }}"
                                                    placeholder="Ex: 0.4162">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label-modern">Longitude</label>
                                                <input type="text" class="form-input-modern" name="org_longitude"
                                                    id="org_longitude"
                                                    value="{{ old('org_longitude', $dossier->organisation->longitude ?? '') }}"
                                                    placeholder="Ex: 9.4673">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label-modern">&nbsp;</label>
                                                <button type="button" class="btn-gps" id="btnGetLocation">
                                                    <i class="fas fa-crosshairs"></i> Ma position GPS
                                                </button>
                                            </div>
                                        </div>

                                        <div class="section-footer">
                                            <button type="button" class="btn-nav btn-nav-prev"
                                                onclick="goToSection('collapseOrganisation')">
                                                <i class="fas fa-arrow-left"></i> Précédent
                                            </button>
                                            <button type="button" class="btn-nav btn-nav-next"
                                                onclick="goToSection('collapseFondateurs')">
                                                Suivant <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÉTAPE 5: FONDATEURS -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-trigger collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseFondateurs" aria-expanded="false">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box pink"><i class="fas fa-users"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Étape 5 sur 8</div>
                                                <div class="step-title">Membres Fondateurs</div>
                                            </div>
                                        </div>
                                        <div class="accordion-badge" id="fondateursCount">0</div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseFondateurs" class="accordion-collapse collapse"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">
                                        <div class="alert-box warning">
                                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                                            <div><strong>Minimum requis :</strong> <span id="minFondateursText">2</span>
                                                fondateur(s) majeur(s)</div>
                                        </div>

                                        <div class="add-member-card">
                                            <div class="add-member-title">
                                                <i class="fas fa-user-plus"></i>
                                                <span>Ajouter un fondateur</span>
                                            </div>
                                            <div class="add-member-grid">
                                                <select class="form-select-modern" id="fondateur_civilite">
                                                    <option value="M">M.</option>
                                                    <option value="Mme">Mme</option>
                                                    <option value="Mlle">Mlle</option>
                                                </select>
                                                <input type="text" class="form-input-modern" id="fondateur_nip"
                                                    placeholder="NIP">
                                                <input type="text" class="form-input-modern" id="fondateur_nom"
                                                    placeholder="Nom">
                                                <input type="text" class="form-input-modern" id="fondateur_prenom"
                                                    placeholder="Prénom">
                                                <select class="form-select-modern" id="fondateur_fonction">
                                                    <option value="">Fonction...</option>
                                                </select>
                                                <button type="button" class="btn-add" id="btnAddFondateur">
                                                    <i class="fas fa-plus"></i> Ajouter
                                                </button>
                                            </div>
                                        </div>

                                        <div class="data-table-wrapper">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>Civ.</th>
                                                        <th>NIP</th>
                                                        <th>Nom</th>
                                                        <th>Prénom</th>
                                                        <th>Fonction</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="fondateursList">
                                                    <tr>
                                                        <td colspan="6" class="empty-state"><i
                                                                class="fas fa-users"></i>Aucun fondateur ajouté</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="hiddenFondateursInputs"></div>

                                        <div class="section-footer">
                                            <button type="button" class="btn-nav btn-nav-prev"
                                                onclick="goToSection('collapseLocalisation')">
                                                <i class="fas fa-arrow-left"></i> Précédent
                                            </button>
                                            <button type="button" class="btn-nav btn-nav-next"
                                                onclick="goToSection('collapseMembresBureau')">
                                                Suivant <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÉTAPE 5b: MEMBRES DU BUREAU -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-trigger collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseMembresBureau" aria-expanded="false">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box blue"><i class="fas fa-user-tie"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Bureau Exécutif</div>
                                                <div class="step-title">Membres du Bureau</div>
                                                <span class="step-badge">Pour récépissé (max 3)</span>
                                            </div>
                                        </div>
                                        <div class="accordion-badge" id="membresBureauCount">0</div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseMembresBureau" class="accordion-collapse collapse"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">
                                        <div class="alert-box info">
                                            <i class="fas fa-info-circle fa-lg"></i>
                                            <div><strong>Information :</strong> Les membres cochés "Afficher sur récépissé"
                                                (max 3) apparaîtront sur le récépissé définitif.</div>
                                        </div>

                                        <div class="add-member-card blue">
                                            <div class="add-member-title">
                                                <i class="fas fa-user-tie"></i>
                                                <span>Ajouter un membre du bureau</span>
                                            </div>
                                            <div class="form-grid" style="gap: 0.75rem;">
                                                <div class="form-group">
                                                    <input type="text" class="form-input-modern" id="membre_nip"
                                                        placeholder="NIP *">
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" class="form-input-modern" id="membre_nom"
                                                        placeholder="Nom *">
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" class="form-input-modern" id="membre_prenom"
                                                        placeholder="Prénom *">
                                                </div>
                                                <div class="form-group">
                                                    <select class="form-select-modern" id="membre_fonction">
                                                        <option value="">Fonction *</option>
                                                        <option value="Président(e)">Président(e)</option>
                                                        <option value="Vice-Président(e)">Vice-Président(e)</option>
                                                        <option value="Secrétaire Général(e)">Secrétaire Général(e)</option>
                                                        <option value="Secrétaire Général(e) Adjoint(e)">Secrétaire
                                                            Général(e) Adjoint(e)</option>
                                                        <option value="Trésorier(ère)">Trésorier(ère)</option>
                                                        <option value="Trésorier(ère) Adjoint(e)">Trésorier(ère) Adjoint(e)
                                                        </option>
                                                        <option value="Commissaire aux Comptes">Commissaire aux Comptes
                                                        </option>
                                                        <option value="Conseiller(ère)">Conseiller(ère)</option>
                                                        <option value="Coordonnateur(trice)">Coordonnateur(trice)</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" class="form-input-modern" id="membre_contact"
                                                        placeholder="Contact">
                                                </div>
                                                <div class="form-group">
                                                    <input type="text" class="form-input-modern" id="membre_domicile"
                                                        placeholder="Domicile">
                                                </div>
                                                <div class="form-group"
                                                    style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <input type="checkbox" id="membre_afficher_recepisse"
                                                        class="form-check-input">
                                                    <label for="membre_afficher_recepisse" class="form-check-label"
                                                        style="margin: 0; font-size: 0.85rem;">Sur récépissé</label>
                                                </div>
                                                <div class="form-group">
                                                    <button type="button" class="btn-add blue" id="btnAddMembreBureau">
                                                        <i class="fas fa-plus"></i> Ajouter
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="data-table-wrapper">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>NIP</th>
                                                        <th>Nom</th>
                                                        <th>Prénom</th>
                                                        <th>Fonction</th>
                                                        <th>Contact</th>
                                                        <th>Récépissé</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="membresBureauList">
                                                    <tr>
                                                        <td colspan="7" class="empty-state"><i
                                                                class="fas fa-user-tie"></i>Aucun membre du bureau ajouté
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="hiddenMembresBureauInputs"></div>

                                        <div class="section-footer">
                                            <button type="button" class="btn-nav btn-nav-prev"
                                                onclick="goToSection('collapseFondateurs')">
                                                <i class="fas fa-arrow-left"></i> Précédent
                                            </button>
                                            <button type="button" class="btn-nav btn-nav-next"
                                                onclick="goToSection('collapseAdherents')">
                                                Suivant <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÉTAPE 6: ADHÉRENTS -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-trigger collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseAdherents" aria-expanded="false">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box cyan"><i class="fas fa-user-friends"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Étape 7 sur 8</div>
                                                <div class="step-title">Adhérents</div>
                                                <span class="step-badge" id="adherentsBadge">Optionnel</span>
                                            </div>
                                        </div>
                                        <div class="accordion-badge" id="adherentsCount">0</div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseAdherents" class="accordion-collapse collapse"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">
                                        <div class="alert-box info" id="adherentsRequirements">
                                            <i class="fas fa-info-circle fa-lg"></i>
                                            <div id="adherentsRequirementsText">Section optionnelle pour ce type
                                                d'organisation</div>
                                        </div>

                                        <div class="add-member-card cyan">
                                            <div class="add-member-title">
                                                <i class="fas fa-user-plus"></i>
                                                <span>Ajouter un adhérent</span>
                                            </div>
                                            <div class="add-member-grid adherent-grid">
                                                <input type="text" class="form-input-modern" id="adherent_nip"
                                                    placeholder="NIP">
                                                <input type="text" class="form-input-modern" id="adherent_nom"
                                                    placeholder="Nom">
                                                <input type="text" class="form-input-modern" id="adherent_prenom"
                                                    placeholder="Prénom">
                                                <input type="text" class="form-input-modern" id="adherent_profession"
                                                    placeholder="Profession">
                                                <button type="button" class="btn-add cyan" id="btnAddAdherent">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="data-table-wrapper">
                                            <table class="data-table">
                                                <thead>
                                                    <tr>
                                                        <th>NIP</th>
                                                        <th>Nom</th>
                                                        <th>Prénom</th>
                                                        <th>Profession</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="adherentsList">
                                                    <tr>
                                                        <td colspan="5" class="empty-state"><i
                                                                class="fas fa-user-friends"></i>Aucun adhérent ajouté</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="hiddenAdherentsInputs"></div>

                                        <div class="section-footer">
                                            <button type="button" class="btn-nav btn-nav-prev"
                                                onclick="goToSection('collapseMembresBureau')">
                                                <i class="fas fa-arrow-left"></i> Précédent
                                            </button>
                                            <button type="button" class="btn-nav btn-nav-next"
                                                onclick="goToSection('collapseDocuments')">
                                                Suivant <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ÉTAPE 7: DOCUMENTS -->
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-trigger collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseDocuments" aria-expanded="false">
                                        <div class="accordion-trigger-content">
                                            <div class="step-icon-box orange"><i class="fas fa-file-alt"></i></div>
                                            <div class="step-text-group">
                                                <div class="step-eyebrow">Étape 8 sur 8</div>
                                                <div class="step-title">Documents Justificatifs</div>
                                            </div>
                                        </div>
                                        <div class="accordion-badge" id="documentsCount">0</div>
                                        <i class="fas fa-chevron-down accordion-chevron"></i>
                                    </button>
                                </div>
                                <div id="collapseDocuments" class="accordion-collapse collapse"
                                    data-parent="#accordionDossier">
                                    <div class="accordion-content">
                                        {{-- Documents existants --}}
                                        @if(isset($dossier) && $dossier->documents && $dossier->documents->count() > 0)
                                            <div class="alert-box success mb-4">
                                                <i class="fas fa-check-circle fa-lg"></i>
                                                <div>
                                                    <strong>{{ $dossier->documents->count() }} document(s) déjà téléversé(s)</strong>
                                                    <p class="mb-0 mt-1" style="font-size: 0.85rem;">Les documents ci-dessous sont déjà associés à ce dossier.</p>
                                                </div>
                                            </div>
                                            <div class="document-grid mb-4">
                                                @foreach($dossier->documents as $doc)
                                                    <div class="document-card existing">
                                                        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;">
                                                            <i class="fas fa-file-pdf" style="color:var(--gabon-green);font-size:1.5rem;"></i>
                                                            <div>
                                                                <strong>{{ $doc->documentType->nom ?? 'Document' }}</strong>
                                                                <span style="background:var(--gabon-green);color:white;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.7rem;margin-left:0.5rem;">
                                                                    Téléversé
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div style="font-size:0.85rem;color:var(--text-muted);">
                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                            {{ $doc->created_at->format('d/m/Y H:i') }}
                                                            <span class="ms-2" style="font-size:0.8rem;color:#666;">
                                                                ({{ number_format($doc->taille / 1024, 1) }} Ko)
                                                            </span>
                                                        </div>
                                                        <div style="margin-top:0.5rem;display:flex;gap:0.5rem;">
                                                            <a href="{{ asset('storage/' . $doc->chemin_fichier) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> Voir
                                                            </a>
                                                            <a href="{{ asset('storage/' . $doc->chemin_fichier) }}" download="{{ $doc->nom_original ?? $doc->nom_fichier ?? 'document' }}" class="btn btn-sm btn-outline-success">
                                                                <i class="fas fa-download"></i> Télécharger
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <h6 style="color:var(--text-muted);margin-bottom:1rem;">
                                                <i class="fas fa-plus-circle me-2"></i>Ajouter ou remplacer des documents
                                            </h6>
                                        @endif

                                        <div id="documentsContainer">
                                            <div class="empty-documents">
                                                <i class="fas fa-folder-open"></i>
                                                <p>Sélectionnez un type d'organisation pour voir les documents requis</p>
                                            </div>
                                        </div>

                                        <!-- Champ caché pour l'action -->
                                        <input type="hidden" name="action" id="formAction" value="brouillon">

                                        <div class="section-footer final">
                                            <button type="button" class="btn-nav btn-nav-prev"
                                                onclick="goToSection('collapseAdherents')">
                                                <i class="fas fa-arrow-left"></i> Précédent
                                            </button>
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($dossier->canBeCancelled())
                                                    <button type="button" class="btn btn-danger" data-toggle="modal"
                                                        data-target="#cancelModal">
                                                        <i class="fas fa-times-circle"></i> Annuler
                                                    </button>
                                                @endif
                                                <!-- Bouton Fermer (retour sans enregistrer) -->
                                                <a href="{{ route('admin.dossiers.index') }}" class="btn-close-dossier">
                                                    <i class="fas fa-door-open"></i>
                                                    <span>Fermer</span>
                                                </a>
                                                <!-- Bouton Enregistrer Brouillon -->
                                                <button type="button" class="btn-draft" id="btnSaveDraft" onclick="submitFormWithAction('brouillon')">
                                                    <i class="fas fa-save"></i>
                                                    <span>Enregistrer Brouillon</span>
                                                </button>
                                                <!-- Bouton Soumettre -->
                                                <button type="button" class="btn-submit" id="btnSubmitForm" onclick="submitFormWithAction('soumettre')">
                                                    <i class="fas fa-paper-plane"></i>
                                                    <span>Soumettre</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>

            <!-- Modal d'annulation du dossier -->
            @if(isset($dossier) && $dossier->canBeCancelled())
                <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="cancelModalLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer l'annulation
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Fermer"></button>
                            </div>
                            <form action="{{ route('admin.dossiers.cancel', $dossier->id) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-warning me-2"></i>
                                        <strong>Attention !</strong> Cette action va annuler le dossier
                                        <strong>{{ $dossier->numero_dossier }}</strong>.
                                    </div>
                                    <div class="mb-3">
                                        <label for="motif" class="form-label">Motif de l'annulation (optionnel)</label>
                                        <textarea class="form-control" id="motif" name="motif" rows="3"
                                            placeholder="Indiquez le motif de l'annulation..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Annuler
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-check me-1"></i>Confirmer l'Annulation
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        /* ========================================================================
                                                                                       FORMULAIRE CRÉATION DOSSIER - DESIGN FUTURISTE GABONAIS
                                                                                       Bootstrap 5 - Thème Clair
                                                                                       ======================================================================== */

        :root {
            --gabon-green: #009e3f;
            --gabon-green-light: #e6f7ed;
            --gabon-green-dark: #007830;
            --gabon-yellow: #ffcd00;
            --gabon-yellow-light: #fff8e0;
            --gabon-blue: #003f7f;
            --gabon-blue-light: #e6eef5;

            --bg-primary: #f8fafc;
            --bg-secondary: #f1f5f9;
            --bg-card: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;

            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.12);

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        .dossier-form-wrapper {
            min-height: 100vh;
            background: var(--bg-primary);
            position: relative;
        }

        /* Fond animé subtil */
        .cosmic-bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, var(--gabon-green-light) 0%, var(--bg-primary) 50%, var(--gabon-blue-light) 100%);
            z-index: -3;
        }

        .floating-orbs {
            position: fixed;
            inset: 0;
            z-index: -2;
            overflow: hidden;
            pointer-events: none;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: orbFloat 25s ease-in-out infinite;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: var(--gabon-green);
            top: -150px;
            left: -100px;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: var(--gabon-blue);
            bottom: -100px;
            right: -100px;
            animation-delay: -8s;
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: var(--gabon-yellow);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -16s;
            opacity: 0.1;
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -20px) scale(1.05);
            }

            66% {
                transform: translate(-20px, 30px) scale(0.95);
            }
        }

        .neon-grid {
            position: fixed;
            inset: 0;
            z-index: -1;
            background-image: linear-gradient(rgba(0, 158, 63, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 158, 63, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .particles-container {
            display: none;
        }

        /* Barre de progression globale */
        .global-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--border-color);
            z-index: 1000;
        }

        .global-progress-bar {
            height: 100%;
            width: 14%;
            background: linear-gradient(90deg, var(--gabon-green), var(--gabon-yellow), var(--gabon-blue));
            transition: width 0.5s ease;
        }

        /* Hero Header */
        .hero-header {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            border-top: 4px solid;
            border-image: linear-gradient(90deg, var(--gabon-green), var(--gabon-yellow), var(--gabon-blue)) 1;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .hero-title-group {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .hero-icon {
            width: 70px;
            height: 70px;
            border-radius: var(--radius-lg);
            background: linear-gradient(135deg, var(--gabon-green), var(--gabon-green-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 8px 25px rgba(0, 158, 63, 0.3);
        }

        .hero-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .hero-text p {
            color: var(--text-secondary);
            margin: 0.25rem 0 0;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: var(--gabon-green);
            color: white;
        }

        /* Stepper */
        .stepper-container {
            margin-bottom: 2rem;
            overflow-x: auto;
            padding: 0.5rem 0;
        }

        .stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 700px;
            position: relative;
            padding: 0 1rem;
        }

        .stepper-track {
            position: absolute;
            top: 20px;
            left: 40px;
            right: 40px;
            height: 3px;
            background: var(--border-color);
            z-index: 0;
        }

        .stepper-progress {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--gabon-green), var(--gabon-yellow));
            transition: width 0.5s ease;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            z-index: 1;
            transition: transform 0.2s;
        }

        .step:hover {
            transform: translateY(-3px);
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .step.active .step-number {
            background: var(--gabon-green);
            border-color: var(--gabon-green);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 158, 63, 0.4);
        }

        .step.active .step-label {
            color: var(--gabon-green);
            font-weight: 600;
        }

        .step.completed .step-number {
            background: var(--gabon-green);
            border-color: var(--gabon-green);
            color: white;
        }

        /* Form Card */
        .form-card {
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Accordéon */
        .accordion-modern .accordion-item {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg) !important;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.3s;
        }

        .accordion-modern .accordion-item:hover {
            border-color: var(--gabon-green);
            box-shadow: var(--shadow-md);
        }

        .accordion-header {
            background: transparent;
            border: none;
            padding: 0;
            margin: 0;
        }

        .accordion-trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }

        .accordion-trigger:hover {
            background: var(--bg-secondary);
        }

        .accordion-trigger:focus {
            outline: none;
        }

        .accordion-trigger[aria-expanded="true"] {
            background: linear-gradient(135deg, rgba(0, 158, 63, 0.08), rgba(0, 158, 63, 0.02));
        }

        .accordion-trigger-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .step-icon-box {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }

        .step-icon-box.green {
            background: linear-gradient(135deg, var(--gabon-green), #00b347);
        }

        .step-icon-box.yellow {
            background: linear-gradient(135deg, var(--gabon-yellow), #ffa500);
        }

        .step-icon-box.blue {
            background: linear-gradient(135deg, var(--gabon-blue), #0056b3);
        }

        .step-icon-box.purple {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .step-icon-box.pink {
            background: linear-gradient(135deg, #ec4899, #f472b6);
        }

        .step-icon-box.cyan {
            background: linear-gradient(135deg, #06b6d4, #22d3ee);
        }

        .step-icon-box.orange {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .step-text-group {
            flex: 1;
        }

        .step-eyebrow {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .step-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .step-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            font-size: 0.7rem;
            border-radius: 20px;
            margin-left: 0.5rem;
        }

        .accordion-badge {
            background: var(--gabon-green);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 1rem;
        }

        .accordion-chevron {
            color: var(--text-muted);
            transition: transform 0.3s;
        }

        .accordion-trigger[aria-expanded="true"] .accordion-chevron {
            transform: rotate(180deg);
        }

        .accordion-content {
            padding: 1.5rem;
        }

        /* Formulaires */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.span-2 {
            grid-column: span 2;
        }

        .form-group.span-3 {
            grid-column: span 3;
        }

        /* Zone Selector - Urbaine/Rurale */
        .zone-selector {
            margin-bottom: 1.5rem;
        }

        .zone-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .zone-option {
            cursor: pointer;
        }

        .zone-option input[type="radio"] {
            display: none;
        }

        .zone-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            background: var(--bg-card);
            transition: all 0.2s ease;
            text-align: center;
        }

        .zone-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
        }

        .zone-card span {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .zone-card small {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .zone-option input[type="radio"]:checked+.zone-card {
            border-color: var(--gabon-green);
            background: var(--gabon-green-light);
        }

        .zone-option input[type="radio"]:checked+.zone-card i {
            color: var(--gabon-green);
        }

        .zone-option:hover .zone-card {
            border-color: var(--gabon-green);
        }

        .form-label-modern {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .required {
            color: #ef4444;
        }

        .form-input-modern,
        .form-select-modern,
        .form-textarea-modern {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            color: var(--text-primary);
            transition: all 0.2s;
        }

        .form-input-modern:focus,
        .form-select-modern:focus,
        .form-textarea-modern:focus {
            outline: none;
            border-color: var(--gabon-green);
            background: var(--bg-card);
            box-shadow: 0 0 0 4px rgba(0, 158, 63, 0.1);
        }

        .form-textarea-modern {
            resize: vertical;
            min-height: 100px;
        }

        /* Type Selection */
        .type-selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .type-option {
            cursor: pointer;
        }

        .type-option input {
            display: none;
        }

        .type-card-inner {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }

        .type-option:hover .type-card-inner {
            border-color: var(--gabon-green);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .type-option input:checked+.type-card-inner {
            border-color: var(--gabon-green);
            background: var(--gabon-green-light);
            box-shadow: 0 4px 20px rgba(0, 158, 63, 0.2);
        }

        .type-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }

        .type-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .type-details {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .type-details span {
            display: block;
        }

        /* Alerts */
        .alert-box {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-box.info {
            background: var(--gabon-blue-light);
            color: var(--gabon-blue);
        }

        .alert-box.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .alert-box.success {
            background: var(--gabon-green-light);
            color: var(--gabon-green-dark);
        }

        /* Add Member */
        .add-member-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .add-member-card.cyan {
            background: #e0f2fe;
        }

        .add-member-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--gabon-green);
            margin-bottom: 1rem;
        }

        .add-member-card.cyan .add-member-title {
            color: #0891b2;
        }

        .add-member-grid {
            display: grid;
            grid-template-columns: 80px 1fr 1fr 1fr 1fr 120px;
            gap: 0.75rem;
        }

        .adherent-grid {
            grid-template-columns: 1fr 1fr 1fr 1fr 60px;
        }

        .btn-add {
            padding: 0.75rem 1rem;
            background: var(--gabon-green);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-add:hover {
            background: var(--gabon-green-dark);
            transform: translateY(-2px);
        }

        .btn-add.cyan {
            background: #0891b2;
        }

        .btn-add.cyan:hover {
            background: #0e7490;
        }

        /* Data Table */
        .data-table-wrapper {
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            background: var(--gabon-blue);
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            text-align: left;
        }

        .data-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .data-table tbody tr:hover {
            background: var(--gabon-green-light);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }

        .data-table .empty-state i {
            display: block;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.3;
        }

        .code-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: var(--gabon-green-light);
            color: var(--gabon-green);
            font-family: monospace;
            font-size: 0.85rem;
            border-radius: var(--radius-sm);
        }

        .function-badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            background: var(--gabon-blue);
            color: white;
            font-size: 0.8rem;
            border-radius: 20px;
        }

        /* Documents */
        .empty-documents {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-documents i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .document-grid {
            display: grid;
            gap: 1rem;
        }

        .document-card {
            background: var(--bg-secondary);
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            transition: all 0.2s;
        }

        .document-card:hover {
            border-color: var(--gabon-green);
        }

        .document-card.required {
            border-color: #fca5a5;
            background: #fef2f2;
        }

        /* Navigation Buttons */
        .section-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .section-footer.final {
            border-top: 2px solid var(--gabon-green);
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-nav-prev {
            background: var(--bg-secondary);
            color: var(--text-secondary);
        }

        .btn-nav-prev:hover {
            background: var(--border-color);
            color: var(--text-primary);
        }

        .btn-nav-next {
            background: var(--gabon-green);
            color: white;
        }

        .btn-nav-next:hover {
            background: var(--gabon-green-dark);
            transform: translateX(3px);
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--gabon-green), var(--gabon-blue));
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(0, 158, 63, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 158, 63, 0.4);
        }

        /* Bouton Enregistrer Brouillon */
        .btn-draft {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--gabon-yellow), #e6a800);
            color: #1a1a1a;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 205, 0, 0.3);
        }

        .btn-draft:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 205, 0, 0.4);
        }

        /* Bouton Fermer */
        .btn-close-dossier {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-close-dossier:hover {
            background: var(--border-color);
            color: var(--text-primary);
            text-decoration: none;
        }

        .btn-gps {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-secondary);
            border: 2px solid var(--gabon-blue);
            border-radius: var(--radius-md);
            color: var(--gabon-blue);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-gps:hover {
            background: var(--gabon-blue);
            color: white;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-group.span-3 {
                grid-column: span 2;
            }

            .add-member-grid {
                grid-template-columns: 1fr 1fr;
            }

            .adherent-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                text-align: center;
            }

            .hero-title-group {
                flex-direction: column;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.span-2,
            .form-group.span-3 {
                grid-column: span 1;
            }

            .add-member-grid,
            .adherent-grid {
                grid-template-columns: 1fr;
            }

            .section-footer {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-nav {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            var fondateurs = [];
                            var adherents = [];
                            var membresBureau = [];
                            var typeConfig = null;
                            var currentStep = 1;

                            // Fonction pour soumettre le formulaire avec une action spécifique
                            window.submitFormWithAction = function(action) {
                                document.getElementById('formAction').value = action;
                                document.getElementById('editDossierForm').submit();
                            };

                            // Exposer les tableaux globalement pour le débogage
                            window.fondateursList = fondateurs;
                            window.membresBureauList = membresBureau;
                            window.adherentsList = adherents;

                            // Fonction helper pour ajouter un écouteur d'événement de manière sécurisée
                            function safeAddEventListener(elementId, eventType, handler) {
                                var element = document.getElementById(elementId);
                                if (element) {
                                    element.addEventListener(eventType, handler);
                                } else {
                                    console.warn('Element "' + elementId + '" non trouvé pour l\'écouteur d\'événement');
                                }
                            }

                            // Navigation accordéon
                            window.goToSection = function (sectionId) {
                                // Fermer toutes les sections
                                document.querySelectorAll('#accordionDossier .accordion-collapse.show').forEach(function (el) {
                                    if (el.id !== sectionId) {
                                        el.classList.remove('show');
                                        var btn = el.previousElementSibling.querySelector('.accordion-trigger');
                                        if (btn) {
                                            btn.classList.add('collapsed');
                                            btn.setAttribute('aria-expanded', 'false');
                                        }
                                    }
                                });

                                // Ouvrir la section cible
                                setTimeout(function () {
                                    var target = document.getElementById(sectionId);
                                    if (target) {
                                        target.classList.add('show');
                                        var btn = target.previousElementSibling.querySelector('.accordion-trigger');
                                        if (btn) {
                                            btn.classList.remove('collapsed');
                                            btn.setAttribute('aria-expanded', 'true');
                                        }

                                        // Scroll vers la section
                                        setTimeout(function () {
                                            var item = target.closest('.accordion-item');
                                            if (item) {
                                                var offset = item.getBoundingClientRect().top + window.pageYOffset - 100;
                                                window.scrollTo({ top: offset, behavior: 'smooth' });
                                            }
                                        }, 100);
                                    }
                                    updateStepper(sectionId);
                                }, 300);
                            };

                            function updateStepper(sectionId) {
                                var stepMap = { 'collapseType': 1, 'collapseDeclarant': 2, 'collapseOrganisation': 3, 'collapseLocalisation': 4, 'collapseFondateurs': 5, 'collapseMembresBureau': 6, 'collapseAdherents': 7, 'collapseDocuments': 8 };
                                currentStep = stepMap[sectionId] || 1;

                                var progress = ((currentStep - 1) / 7) * 100;
                                document.getElementById('stepperProgress').style.width = progress + '%';
                                document.getElementById('globalProgress').style.width = ((currentStep / 8) * 100) + '%';

                                document.querySelectorAll('.step').forEach(function (el) {
                                    var step = parseInt(el.getAttribute('data-step'));
                                    el.classList.remove('active', 'completed');
                                    if (step < currentStep) {
                                        el.classList.add('completed');
                                        el.querySelector('.step-number').innerHTML = '<i class="fas fa-check"></i>';
                                    } else if (step === currentStep) {
                                        el.classList.add('active');
                                        el.querySelector('.step-number').textContent = step;
                                    } else {
                                        el.querySelector('.step-number').textContent = step;
                                    }
                                });
                            }

                            // Type organisation
                            document.querySelectorAll('input[name="organisation_type_id"]').forEach(function (radio) {
                                radio.addEventListener('change', function () {
                                    loadTypeConfiguration(this.value);
                                });
                            });

                            function loadTypeConfiguration(typeId) {
                                fetch('{{ url("admin/api/geo/organisation-types") }}/' + typeId + '/rules')
                                    .then(function (r) { return r.json(); })
                                    .then(function (response) {
                                        if (response.success && response.data) {
                                            typeConfig = response.data;
                                            applyTypeConfiguration(response.data);
                                        }
                                    })
                                    .catch(function (e) { console.error('Erreur chargement config:', e); });
                            }

                            function applyTypeConfiguration(config) {
                                var infoHtml = '<strong>' + config.nom + '</strong><br>';
                                infoHtml += '<span style="display:inline-block;padding:0.2rem 0.5rem;background:var(--gabon-green);color:white;border-radius:4px;margin:0.25rem 0.25rem 0 0;font-size:0.8rem;">Min. ' + config.nb_min_fondateurs + ' fondateurs</span>';
                                if (config.nb_min_adherents > 0) {
                                    infoHtml += '<span style="display:inline-block;padding:0.2rem 0.5rem;background:var(--gabon-blue);color:white;border-radius:4px;margin:0.25rem 0.25rem 0 0;font-size:0.8rem;">Min. ' + config.nb_min_adherents + ' adhérents</span>';
                                }
                                document.getElementById('typeConfigText').innerHTML = infoHtml;
                                document.getElementById('typeConfigInfo').style.display = 'flex';
                                document.getElementById('minFondateursText').textContent = config.nb_min_fondateurs;

                                if (config.nb_min_adherents > 0) {
                                    document.getElementById('adherentsBadge').textContent = 'Obligatoire';
                                    document.getElementById('adherentsBadge').style.background = '#ef4444';
                                    document.getElementById('adherentsBadge').style.color = 'white';
                                    document.getElementById('adherentsRequirements').className = 'alert-box warning';
                                    document.getElementById('adherentsRequirementsText').innerHTML = '<strong>Minimum requis :</strong> ' + config.nb_min_adherents + ' adhérent(s)';
                                } else {
                                    document.getElementById('adherentsBadge').textContent = 'Optionnel';
                                    document.getElementById('adherentsBadge').style.background = 'var(--bg-secondary)';
                                    document.getElementById('adherentsBadge').style.color = 'var(--text-secondary)';
                                    document.getElementById('adherentsRequirements').className = 'alert-box info';
                                    document.getElementById('adherentsRequirementsText').textContent = 'Section optionnelle pour ce type';
                                }

                                updateDocuments(config);
                            }

                            function updateDocuments(config) {
                                var container = document.getElementById('documentsContainer');
                                var docsObl = config.documents_obligatoires || [];
                                var docsFac = config.documents_facultatifs || [];

                                if (docsObl.length === 0 && docsFac.length === 0) {
                                    container.innerHTML = '<div class="alert-box info"><i class="fas fa-info-circle fa-lg"></i><div>Aucun document requis pour ce type</div></div>';
                                    return;
                                }

                                var html = '<div class="document-grid">';
                                if (docsObl.length > 0) {
                                    html += '<h6 style="color:#ef4444;margin-bottom:1rem;"><i class="fas fa-asterisk me-2"></i>Documents obligatoires</h6>';
                                    docsObl.forEach(function (doc) {
                                        html += '<div class="document-card required">';
                                        html += '<div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">';
                                        html += '<i class="fas fa-file-pdf" style="color:#ef4444;font-size:1.5rem;"></i>';
                                        html += '<div><strong>' + doc.nom + '</strong> <span style="background:#ef4444;color:white;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.7rem;">Obligatoire</span></div>';
                                        html += '</div>';
                                        html += '<input type="file" class="form-input-modern" name="documents[' + doc.id + ']">';
                                        html += '</div>';
                                    });
                                }
                                if (docsFac.length > 0) {
                                    html += '<h6 style="color:var(--text-muted);margin:1.5rem 0 1rem;"><i class="fas fa-file me-2"></i>Documents facultatifs</h6>';
                                    docsFac.forEach(function (doc) {
                                        html += '<div class="document-card">';
                                        html += '<div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">';
                                        html += '<i class="fas fa-file" style="color:var(--text-muted);font-size:1.5rem;"></i>';
                                        html += '<div><strong>' + doc.nom + '</strong></div>';
                                        html += '</div>';
                                        html += '<input type="file" class="form-input-modern" name="documents[' + doc.id + ']">';
                                        html += '</div>';
                                    });
                                }
                                html += '</div>';
                                container.innerHTML = html;
                            }

                            // ========== GESTION TYPE DE ZONE ==========
                            function resetSelect(id, placeholder) {
                                var select = document.getElementById(id);
                                if (select) {
                                    select.innerHTML = '<option value="">' + placeholder + '</option>';
                                    select.disabled = true;
                                }
                            }

                            document.querySelectorAll('input[name="zone_type"]').forEach(function (radio) {
                                radio.addEventListener('change', function () {
                                    var isUrbaine = this.value === 'urbaine';

                                    // Afficher/masquer les champs selon la zone
                                    document.querySelectorAll('.zone-urbaine-field').forEach(function (el) {
                                        el.style.display = isUrbaine ? '' : 'none';
                                    });
                                    document.querySelectorAll('.zone-rurale-field').forEach(function (el) {
                                        el.style.display = isUrbaine ? 'none' : '';
                                    });

                                    // Réinitialiser les selects de la zone opposée
                                    if (isUrbaine) {
                                        resetSelect('org_canton_id', 'Sélectionner département...');
                                        resetSelect('org_regroupement_id', 'Sélectionner canton...');
                                        resetSelect('org_village_id', 'Sélectionner regroupement...');
                                        document.getElementById('canton_nom').value = '';
                                        document.getElementById('regroupement_nom').value = '';
                                        document.getElementById('village_nom').value = '';
                                    } else {
                                        resetSelect('org_commune_id', 'Sélectionner département...');
                                        resetSelect('org_arrondissement_id', 'Sélectionner commune...');
                                        resetSelect('org_quartier_id', 'Sélectionner arrondissement...');
                                        document.getElementById('commune_nom').value = '';
                                        document.getElementById('arrondissement_text').value = '';
                                        document.getElementById('quartier_nom').value = '';
                                    }

                                    // Recharger selon le département sélectionné
                                    var deptId = document.getElementById('org_departement_id').value;
                                    if (deptId) {
                                        if (isUrbaine) {
                                            loadCommunes(deptId);
                                        } else {
                                            loadCantons(deptId);
                                        }
                                    }
                                });
                            });

                            // ========== CASCADE PROVINCE → DÉPARTEMENT ==========
                            safeAddEventListener('org_province_id', 'change', function () {
                                var id = this.value;
                                var selectedOption = this.options[this.selectedIndex];
                                var dept = document.getElementById('org_departement_id');

                                document.getElementById('province_nom').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';

                                // Réinitialiser tous les champs dépendants
                                resetSelect('org_departement_id', 'Chargement...');
                                resetSelect('org_commune_id', 'Sélectionner département...');
                                resetSelect('org_arrondissement_id', 'Sélectionner commune...');
                                resetSelect('org_quartier_id', 'Sélectionner arrondissement...');
                                resetSelect('org_canton_id', 'Sélectionner département...');
                                resetSelect('org_regroupement_id', 'Sélectionner canton...');
                                resetSelect('org_village_id', 'Sélectionner regroupement...');

                                document.getElementById('departement_nom').value = '';
                                document.getElementById('commune_nom').value = '';
                                document.getElementById('arrondissement_text').value = '';
                                document.getElementById('quartier_nom').value = '';
                                document.getElementById('canton_nom').value = '';
                                document.getElementById('regroupement_nom').value = '';
                                document.getElementById('village_nom').value = '';

                                if (id) {
                                    fetch('{{ url("admin/api/geo/departements") }}/' + id)
                                        .then(function (r) { return r.json(); })
                                        .then(function (data) {
                                            if (data.success) {
                                                var html = '<option value="">Sélectionner...</option>';
                                                data.data.forEach(function (d) {
                                                    html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                                                });
                                                dept.innerHTML = html;
                                                dept.disabled = false;
                                            }
                                        });
                                }
                            });

                            // ========== CASCADE DÉPARTEMENT → COMMUNE/CANTON ==========
                            safeAddEventListener('org_departement_id', 'change', function () {
                                var id = this.value;
                                var selectedOption = this.options[this.selectedIndex];
                                var isUrbaine = document.querySelector('input[name="zone_type"]:checked').value === 'urbaine';

                                document.getElementById('departement_nom').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';

                                if (id) {
                                    if (isUrbaine) {
                                        loadCommunes(id);
                                    } else {
                                        loadCantons(id);
                                    }
                                }
                            });

                            // ========== ZONE URBAINE : CHARGEMENT COMMUNES ==========
                            function loadCommunes(departementId) {
                                var com = document.getElementById('org_commune_id');
                                resetSelect('org_commune_id', 'Chargement...');
                                resetSelect('org_arrondissement_id', 'Sélectionner commune...');
                                resetSelect('org_quartier_id', 'Sélectionner arrondissement...');

                                document.getElementById('commune_nom').value = '';
                                document.getElementById('arrondissement_text').value = '';
                                document.getElementById('quartier_nom').value = '';

                                fetch('{{ url("admin/api/geo/communes") }}/' + departementId)
                                    .then(function (r) { return r.json(); })
                                    .then(function (data) {
                                        if (data.success) {
                                            var html = '<option value="">(Optionnel)</option>';
                                            data.data.forEach(function (d) {
                                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                                            });
                                            com.innerHTML = html;
                                            com.disabled = false;
                                        }
                                    });
                            }

                            // ========== ZONE URBAINE : CASCADE COMMUNE → ARRONDISSEMENT ==========
                            safeAddEventListener('org_commune_id', 'change', function () {
                                var id = this.value;
                                var selectedOption = this.options[this.selectedIndex];
                                var arr = document.getElementById('org_arrondissement_id');

                                document.getElementById('commune_nom').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';
                                document.getElementById('arrondissement_text').value = '';
                                document.getElementById('quartier_nom').value = '';

                                resetSelect('org_arrondissement_id', 'Chargement...');
                                resetSelect('org_quartier_id', 'Sélectionner arrondissement...');

                                if (id) {
                                    fetch('{{ url("admin/api/geo/arrondissements") }}/' + id)
                                        .then(function (r) { return r.json(); })
                                        .then(function (data) {
                                            if (data.success) {
                                                var html = '<option value="">(Optionnel)</option>';
                                                data.data.forEach(function (d) {
                                                    html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                                                });
                                                arr.innerHTML = html;
                                                arr.disabled = false;
                                            }
                                        });
                                }
                            });

                            // ========== ZONE URBAINE : CASCADE ARRONDISSEMENT → QUARTIER ==========
                            safeAddEventListener('org_arrondissement_id', 'change', function () {
                                var id = this.value;
                                var selectedOption = this.options[this.selectedIndex];
                                var qrt = document.getElementById('org_quartier_id');

                                document.getElementById('arrondissement_text').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';
                                document.getElementById('quartier_nom').value = '';

                                resetSelect('org_quartier_id', 'Chargement...');

                                if (id) {
                                    fetch('{{ url("admin/api/geo/quartiers") }}/' + id)
                                        .then(function (r) { return r.json(); })
                                        .then(function (data) {
                                            if (data.success) {
                                                var html = '<option value="">(Optionnel)</option>';
                                                data.data.forEach(function (d) {
                                                    html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                                                });
                                                qrt.innerHTML = html;
                                                qrt.disabled = false;
                                            }
                                        });
                                }
                            });

                            // ========== ZONE URBAINE : QUARTIER SÉLECTION ==========
                            safeAddEventListener('org_quartier_id', 'change', function () {
                                var selectedOption = this.options[this.selectedIndex];
                                document.getElementById('quartier_nom').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';
                            });

                            // ========== ZONE RURALE : CHARGEMENT CANTONS ==========
                            function loadCantons(departementId) {
                                var can = document.getElementById('org_canton_id');
                                resetSelect('org_canton_id', 'Chargement...');
                                resetSelect('org_regroupement_id', 'Sélectionner canton...');
                                resetSelect('org_village_id', 'Sélectionner regroupement...');

                                document.getElementById('canton_nom').value = '';
                                document.getElementById('regroupement_nom').value = '';
                                document.getElementById('village_nom').value = '';

                                fetch('{{ url("admin/api/geo/cantons") }}/' + departementId)
                                    .then(function (r) { return r.json(); })
                                    .then(function (data) {
                                        if (data.success) {
                                            var html = '<option value="">(Optionnel)</option>';
                                            data.data.forEach(function (d) {
                                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                                            });
                                            can.innerHTML = html;
                                            can.disabled = false;
                                        }
                                    });
                            }

                            // ========== ZONE RURALE : CASCADE CANTON → REGROUPEMENT ==========
                            safeAddEventListener('org_canton_id', 'change', function () {
                                var id = this.value;
                                var selectedOption = this.options[this.selectedIndex];
                                var reg = document.getElementById('org_regroupement_id');

                                document.getElementById('canton_nom').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';
                                document.getElementById('regroupement_nom').value = '';
                                document.getElementById('village_nom').value = '';

                                resetSelect('org_regroupement_id', 'Chargement...');
                                resetSelect('org_village_id', 'Sélectionner regroupement...');

                                if (id) {
                                    fetch('{{ url("admin/api/geo/regroupements") }}/' + id)
                                        .then(function (r) { return r.json(); })
                                        .then(function (data) {
                                            if (data.success) {
                                                var html = '<option value="">(Optionnel)</option>';
                                                data.data.forEach(function (d) {
                                                    html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                                                });
                                                reg.innerHTML = html;
                                                reg.disabled = false;
                                            }
                                        });
                                }
                            });

                            // ========== ZONE RURALE : CASCADE REGROUPEMENT → VILLAGE ==========
                            safeAddEventListener('org_regroupement_id', 'change', function () {
                                var id = this.value;
                                var selectedOption = this.options[this.selectedIndex];
                                var vil = document.getElementById('org_village_id');

                                document.getElementById('regroupement_nom').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';
                                document.getElementById('village_nom').value = '';

                                resetSelect('org_village_id', 'Chargement...');

                                if (id) {
                                    fetch('{{ url("admin/api/geo/villages") }}/' + id)
                                        .then(function (r) { return r.json(); })
                                        .then(function (data) {
                                            if (data.success) {
                                                var html = '<option value="">(Optionnel)</option>';
                                                data.data.forEach(function (d) {
                                                    html += '<option value="' + d.id + '" data-nom="' + d.nom + '">' + d.nom + '</option>';
                                                });
                                                vil.innerHTML = html;
                                                vil.disabled = false;
                                            }
                                        });
                                }
                            });

                            // ========== ZONE RURALE : VILLAGE SÉLECTION ==========
                            safeAddEventListener('org_village_id', 'change', function () {
                                var selectedOption = this.options[this.selectedIndex];
                                document.getElementById('village_nom').value = selectedOption.getAttribute('data-nom') || selectedOption.text || '';
                            });

                            // ========== INITIALISATION ZONE ==========
                            (function () {
                                var zoneType = document.querySelector('input[name="zone_type"]:checked');
                                if (zoneType) {
                                    var isUrbaine = zoneType.value === 'urbaine';
                                    document.querySelectorAll('.zone-urbaine-field').forEach(function (el) {
                                        el.style.display = isUrbaine ? '' : 'none';
                                    });
                                    document.querySelectorAll('.zone-rurale-field').forEach(function (el) {
                                        el.style.display = isUrbaine ? 'none' : '';
                                    });
                                }
                            })();

                            // ========== GPS ==========
                            safeAddEventListener('btnGetLocation', 'click', function () {
                                var btn = document.getElementById('btnGetLocation');
                                if (navigator.geolocation) {
                                    btn.disabled = true;
                                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation...';
                                    navigator.geolocation.getCurrentPosition(
                                        function (pos) {
                                            document.getElementById('org_latitude').value = pos.coords.latitude.toFixed(6);
                                            document.getElementById('org_longitude').value = pos.coords.longitude.toFixed(6);
                                            btn.innerHTML = '<i class="fas fa-check"></i> Position trouvée';
                                            btn.disabled = false;
                                            setTimeout(function () { btn.innerHTML = '<i class="fas fa-crosshairs"></i> Ma position GPS'; }, 2000);
                                        },
                                        function () {
                                            btn.innerHTML = '<i class="fas fa-times"></i> Erreur';
                                            btn.disabled = false;
                                            setTimeout(function () { btn.innerHTML = '<i class="fas fa-crosshairs"></i> Ma position GPS'; }, 2000);
                                        }
                                    );
                                }
                            });

                            // Fondateurs
                            safeAddEventListener('btnAddFondateur', 'click', function () {
                                var nip = document.getElementById('fondateur_nip').value.trim();
                                var nom = document.getElementById('fondateur_nom').value.trim();
                                var prenom = document.getElementById('fondateur_prenom').value.trim();
                                var fonction = document.getElementById('fondateur_fonction').value;
                                var civilite = document.getElementById('fondateur_civilite').value;

                                if (!nip || !nom || !prenom || !fonction) { alert('Veuillez remplir tous les champs obligatoires'); return; }
                                if (fondateurs.some(function (f) { return f.nip === nip; })) { alert('Ce NIP est déjà ajouté'); return; }

                                fondateurs.push({ nip: nip, civilite: civilite, nom: nom, prenom: prenom, fonction: fonction });
                                updateFondateursList();

                                document.getElementById('fondateur_nip').value = '';
                                document.getElementById('fondateur_nom').value = '';
                                document.getElementById('fondateur_prenom').value = '';
                            });

                            function updateFondateursList() {
                                var tbody = document.getElementById('fondateursList');
                                var hidden = document.getElementById('hiddenFondateursInputs');

                                if (fondateurs.length === 0) {
                                    tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><i class="fas fa-users"></i>Aucun fondateur ajouté</td></tr>';
                                    hidden.innerHTML = '';
                                } else {
                                    var html = '', hid = '';
                                    fondateurs.forEach(function (f, i) {
                                        html += '<tr>';
                                        html += '<td>' + f.civilite + '</td>';
                                        html += '<td><span class="code-badge">' + f.nip + '</span></td>';
                                        html += '<td>' + f.nom + '</td>';
                                        html += '<td>' + f.prenom + '</td>';
                                        html += '<td><span class="function-badge">' + f.fonction + '</span></td>';
                                        html += '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeFondateur(' + i + ')"><i class="fas fa-trash"></i></button></td>';
                                        html += '</tr>';
                                        hid += '<input type="hidden" name="fondateurs[' + i + '][nip]" value="' + f.nip + '">';
                                        hid += '<input type="hidden" name="fondateurs[' + i + '][civilite]" value="' + f.civilite + '">';
                                        hid += '<input type="hidden" name="fondateurs[' + i + '][nom]" value="' + f.nom + '">';
                                        hid += '<input type="hidden" name="fondateurs[' + i + '][prenom]" value="' + f.prenom + '">';
                                        hid += '<input type="hidden" name="fondateurs[' + i + '][fonction]" value="' + f.fonction + '">';
                                        hid += '<input type="hidden" name="fondateurs[' + i + '][telephone]" value="">';
                                        hid += '<input type="hidden" name="fondateurs[' + i + '][email]" value="">';
                                    });
                                    tbody.innerHTML = html;
                                    hidden.innerHTML = hid;
                                }
                                document.getElementById('fondateursCount').textContent = fondateurs.length;
                            }

                            window.removeFondateur = function (i) {
                                fondateurs.splice(i, 1);
                                updateFondateursList();
                            };

                            // Membres du Bureau
                            safeAddEventListener('btnAddMembreBureau', 'click', function () {
                                var nip = document.getElementById('membre_nip').value.trim();
                                var nom = document.getElementById('membre_nom').value.trim();
                                var prenom = document.getElementById('membre_prenom').value.trim();
                                var fonction = document.getElementById('membre_fonction').value;
                                var contact = document.getElementById('membre_contact').value.trim();
                                var domicile = document.getElementById('membre_domicile').value.trim();
                                var afficherRecepisse = document.getElementById('membre_afficher_recepisse').checked;

                                if (!nip || !nom || !prenom || !fonction) {
                                    alert('Veuillez remplir NIP, Nom, Prénom et Fonction');
                                    return;
                                }
                                if (membresBureau.some(function (m) { return m.nip === nip; })) {
                                    alert('Ce NIP est déjà ajouté');
                                    return;
                                }

                                // Vérifier max 3 pour récépissé
                                if (afficherRecepisse) {
                                    var countRecepisse = membresBureau.filter(function (m) { return m.afficher_recepisse; }).length;
                                    if (countRecepisse >= 3) {
                                        alert('Maximum 3 membres peuvent être affichés sur le récépissé');
                                        return;
                                    }
                                }

                                membresBureau.push({
                                    nip: nip,
                                    nom: nom,
                                    prenom: prenom,
                                    fonction: fonction,
                                    contact: contact,
                                    domicile: domicile,
                                    afficher_recepisse: afficherRecepisse
                                });
                                updateMembresBureauList();

                                document.getElementById('membre_nip').value = '';
                                document.getElementById('membre_nom').value = '';
                                document.getElementById('membre_prenom').value = '';
                                document.getElementById('membre_fonction').value = '';
                                document.getElementById('membre_contact').value = '';
                                document.getElementById('membre_domicile').value = '';
                                document.getElementById('membre_afficher_recepisse').checked = false;
                            });

                            function updateMembresBureauList() {
                                var tbody = document.getElementById('membresBureauList');
                                var hidden = document.getElementById('hiddenMembresBureauInputs');

                                if (membresBureau.length === 0) {
                                    tbody.innerHTML = '<tr><td colspan="7" class="empty-state"><i class="fas fa-user-tie"></i>Aucun membre du bureau ajouté</td></tr>';
                                    hidden.innerHTML = '';
                                } else {
                                    var html = '', hid = '';
                                    membresBureau.forEach(function (m, i) {
                                        html += '<tr>';
                                        html += '<td><span class="code-badge">' + m.nip + '</span></td>';
                                        html += '<td>' + m.nom + '</td>';
                                        html += '<td>' + m.prenom + '</td>';
                                        html += '<td><span class="function-badge">' + m.fonction + '</span></td>';
                                        html += '<td>' + (m.contact || '-') + '</td>';
                                        html += '<td>' + (m.afficher_recepisse ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>') + '</td>';
                                        html += '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeMembreBureau(' + i + ')"><i class="fas fa-trash"></i></button></td>';
                                        html += '</tr>';
                                        hid += '<input type="hidden" name="membresBureau[' + i + '][nip]" value="' + m.nip + '">';
                                        hid += '<input type="hidden" name="membresBureau[' + i + '][nom]" value="' + m.nom + '">';
                                        hid += '<input type="hidden" name="membresBureau[' + i + '][prenom]" value="' + m.prenom + '">';
                                        hid += '<input type="hidden" name="membresBureau[' + i + '][fonction]" value="' + m.fonction + '">';
                                        hid += '<input type="hidden" name="membresBureau[' + i + '][contact]" value="' + (m.contact || '') + '">';
                                        hid += '<input type="hidden" name="membresBureau[' + i + '][domicile]" value="' + (m.domicile || '') + '">';
                                        hid += '<input type="hidden" name="membresBureau[' + i + '][afficher_recepisse]" value="' + (m.afficher_recepisse ? '1' : '0') + '">';
                                    });
                                    tbody.innerHTML = html;
                                    hidden.innerHTML = hid;
                                }
                                document.getElementById('membresBureauCount').textContent = membresBureau.length;
                            }

                            window.removeMembreBureau = function (i) {
                                membresBureau.splice(i, 1);
                                updateMembresBureauList();
                            };
                            // Adhérents
                            safeAddEventListener('btnAddAdherent', 'click', function () {
                                var nip = document.getElementById('adherent_nip').value.trim();
                                var nom = document.getElementById('adherent_nom').value.trim();
                                var prenom = document.getElementById('adherent_prenom').value.trim();
                                var profession = document.getElementById('adherent_profession').value.trim();

                                if (!nip || !nom || !prenom) { alert('Veuillez remplir NIP, Nom et Prénom'); return; }
                                if (adherents.some(function (a) { return a.nip === nip; })) { alert('Ce NIP est déjà ajouté'); return; }

                                adherents.push({ nip: nip, nom: nom, prenom: prenom, profession: profession });
                                updateAdherentsList();

                                document.getElementById('adherent_nip').value = '';
                                document.getElementById('adherent_nom').value = '';
                                document.getElementById('adherent_prenom').value = '';
                                document.getElementById('adherent_profession').value = '';
                            });

                            function updateAdherentsList() {
                                var tbody = document.getElementById('adherentsList');
                                var hidden = document.getElementById('hiddenAdherentsInputs');

                                if (adherents.length === 0) {
                                    tbody.innerHTML = '<tr><td colspan="5" class="empty-state"><i class="fas fa-user-friends"></i>Aucun adhérent ajouté</td></tr>';
                                    hidden.innerHTML = '';
                                } else {
                                    var html = '', hid = '';
                                    adherents.forEach(function (a, i) {
                                        html += '<tr>';
                                        html += '<td><span class="code-badge">' + a.nip + '</span></td>';
                                        html += '<td>' + a.nom + '</td>';
                                        html += '<td>' + a.prenom + '</td>';
                                        html += '<td>' + (a.profession || '-') + '</td>';
                                        html += '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeAdherent(' + i + ')"><i class="fas fa-trash"></i></button></td>';
                                        html += '</tr>';
                                        hid += '<input type="hidden" name="adherents[' + i + '][nip]" value="' + a.nip + '">';
                                        hid += '<input type="hidden" name="adherents[' + i + '][nom]" value="' + a.nom + '">';
                                        hid += '<input type="hidden" name="adherents[' + i + '][prenom]" value="' + a.prenom + '">';
                                        hid += '<input type="hidden" name="adherents[' + i + '][profession]" value="' + a.profession + '">';
                                        hid += '<input type="hidden" name="adherents[' + i + '][telephone]" value="">';
                                    });
                                    tbody.innerHTML = html;
                                    hidden.innerHTML = hid;
                                }
                                document.getElementById('adherentsCount').textContent = adherents.length;
                            }

                            window.removeAdherent = function (i) {
                                adherents.splice(i, 1);
                                updateAdherentsList();
                            };

                            // Validation
                            safeAddEventListener('editDossierForm', 'submit', function (e) {
                                var errors = [];

                                if (!document.querySelector('input[name="organisation_type_id"]:checked')) errors.push('Type d\'organisation');
                                if (!document.getElementById('demandeur_nip').value.trim()) errors.push('NIP du déclarant');
                                if (!document.getElementById('demandeur_nom').value.trim()) errors.push('Nom du déclarant');
                                if (!document.getElementById('demandeur_prenom').value.trim()) errors.push('Prénom du déclarant');
                                if (!document.getElementById('demandeur_telephone').value.trim()) errors.push('Téléphone du déclarant');
                                if (!document.getElementById('org_nom').value.trim()) errors.push('Nom de l\'organisation');
                                if (!document.getElementById('org_objet').value.trim()) errors.push('Objet social');
                                if (!document.getElementById('org_date_creation').value) errors.push('Date de création');
                                if (!document.getElementById('org_telephone').value.trim()) errors.push('Téléphone organisation');
                                if (!document.getElementById('org_province_id').value) errors.push('Province');
                                if (!document.getElementById('org_departement_id').value) errors.push('Département');
                                if (!document.getElementById('org_adresse').value.trim()) errors.push('Adresse du siège');

                                var minF = typeConfig ? typeConfig.nb_min_fondateurs : 2;
                                if (fondateurs.length < minF) errors.push('Minimum ' + minF + ' fondateurs requis (actuellement ' + fondateurs.length + ')');

                                var minA = typeConfig ? typeConfig.nb_min_adherents : 0;
                                if (minA > 0 && adherents.length < minA) errors.push('Minimum ' + minA + ' adhérents requis (actuellement ' + adherents.length + ')');

                                if (errors.length > 0) {
                                    e.preventDefault();
                                    alert('Veuillez corriger les erreurs suivantes :\n\n• ' + errors.join('\n• '));
                                    return false;
                                }

                                // IMPORTANT: Activer tous les selects disabled avant soumission pour qu'ils soient envoyés
                                document.querySelectorAll('#editDossierForm select[disabled]').forEach(function (select) {
                                    select.disabled = false;
                                });

                                document.getElementById('btnSubmitForm').disabled = true;
                                document.getElementById('btnSubmitForm').innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Enregistrement en cours...</span>';
                            });

                            // Charger fonctions dynamiquement
                            function loadFonctions() {
                                // Essayer d'abord la route nommée, sinon URL directe
                                var apiUrl = '{{ route("admin.api.fonctions", [], false) }}?grouped=1';

                                fetch(apiUrl)
                                    .then(function (r) {
                                        if (!r.ok) throw new Error('HTTP ' + r.status);
                                        return r.json();
                                    })
                                    .then(function (response) {
                                        var selectF = document.getElementById('fondateur_fonction');
                                        var selectR = document.getElementById('demandeur_role');

                                        if (!selectF || !selectR) {
                                            console.error('Selects non trouvés');
                                            return;
                                        }

                                        var htmlF = '<option value="">Sélectionner une fonction...</option>';
                                        var htmlR = '<option value="Déclarant" selected>Déclarant</option>';

                                        if (response.success && response.data) {
                                            var categories = [
                                                { key: 'bureau', label: 'Bureau Exécutif' },
                                                { key: 'commission', label: 'Commission' },
                                                { key: 'membre', label: 'Membres' }
                                            ];

                                            categories.forEach(function (cat) {
                                                var items = response.data[cat.key];
                                                if (items && items.length > 0) {
                                                    htmlF += '<optgroup label="' + cat.label + '">';
                                                    htmlR += '<optgroup label="' + cat.label + '">';
                                                    items.forEach(function (f) {
                                                        var nom = f.nom || f.name || f;
                                                        htmlF += '<option value="' + nom + '">' + nom + '</option>';
                                                        htmlR += '<option value="' + nom + '">' + nom + '</option>';
                                                    });
                                                    htmlF += '</optgroup>';
                                                    htmlR += '</optgroup>';
                                                }
                                            });

                                            console.log('✅ Fonctions chargées dynamiquement');
                                        } else if (response.data && Array.isArray(response.data)) {
                                            // Réponse non groupée
                                            response.data.forEach(function (f) {
                                                var nom = f.nom || f.name || f;
                                                htmlF += '<option value="' + nom + '">' + nom + '</option>';
                                                htmlR += '<option value="' + nom + '">' + nom + '</option>';
                                            });
                                            console.log('✅ Fonctions chargées (liste simple)');
                                        }

                                        selectF.innerHTML = htmlF;
                                        selectR.innerHTML = htmlR;
                                    })
                                    .catch(function (err) {
                                        console.warn('⚠️ API fonctions indisponible, utilisation du fallback:', err.message);
                                        applyFallbackFonctions();
                                    });
                            }

                            function applyFallbackFonctions() {
                                var htmlF = '<option value="">Sélectionner une fonction...</option>';
                                htmlF += '<optgroup label="Bureau Exécutif">';
                                htmlF += '<option value="Président">Président</option>';
                                htmlF += '<option value="Vice-Président">Vice-Président</option>';
                                htmlF += '<option value="Secrétaire Général">Secrétaire Général</option>';
                                htmlF += '<option value="Secrétaire Général Adjoint">Secrétaire Général Adjoint</option>';
                                htmlF += '<option value="Trésorier Général">Trésorier Général</option>';
                                htmlF += '<option value="Trésorier Adjoint">Trésorier Adjoint</option>';
                                htmlF += '</optgroup>';
                                htmlF += '<optgroup label="Commission">';
                                htmlF += '<option value="Commissaire aux Comptes">Commissaire aux Comptes</option>';
                                htmlF += '<option value="Commissaire aux Comptes Adjoint">Commissaire aux Comptes Adjoint</option>';
                                htmlF += '</optgroup>';
                                htmlF += '<optgroup label="Membres">';
                                htmlF += '<option value="Conseiller">Conseiller</option>';
                                htmlF += '<option value="Membre Fondateur">Membre Fondateur</option>';
                                htmlF += '<option value="Membre Actif">Membre Actif</option>';
                                htmlF += '<option value="Membre">Membre</option>';
                                htmlF += '</optgroup>';

                                var htmlR = '<option value="Déclarant" selected>Déclarant</option>' + htmlF.replace('<option value="">Sélectionner une fonction...</option>', '');

                                var selectF = document.getElementById('fondateur_fonction');
                                var selectR = document.getElementById('demandeur_role');

                                if (selectF) selectF.innerHTML = htmlF;
                                if (selectR) selectR.innerHTML = htmlR;
                            }

                            // Charger les fonctions au démarrage
                            loadFonctions();

                            // Init
                            var selectedType = document.querySelector('input[name="organisation_type_id"]:checked');
                            if (selectedType) loadTypeConfiguration(selectedType.value);

                            // =================================================================
                            // CHARGEMENT DES DONNÉES EXISTANTES POUR L'ÉDITION
                            // =================================================================
                            @if(isset($dossier) && $dossier->organisation)
                                @php
                                    $org = $dossier->organisation;
                                    $fondateursData = $org->fondateurs ? $org->fondateurs->toArray() : [];
                                    $membresBureauData = $org->membresBureau ? $org->membresBureau->toArray() : [];
                                    $adherentsData = $org->adherents ? $org->adherents->toArray() : [];
                                @endphp

                                // ----- CHARGEMENT ZONE TYPE -----
                                @if($org->zone_type)
                                    var zoneType = '{{ $org->zone_type }}';
                                    var zoneRadio = document.querySelector('input[name="zone_type"][value="' + zoneType + '"]');
                                    if (zoneRadio) {
                                        zoneRadio.checked = true;
                                        zoneRadio.dispatchEvent(new Event('change'));
                                    }
                                @endif

                                // ----- CHARGEMENT CASCADE GÉOLOCALISATION -----
                                @if($org->province_ref_id)
                                    // Pré-sélectionner la province
                                    var provinceSelect = document.getElementById('org_province_id');
                                    if (provinceSelect) {
                                        provinceSelect.value = '{{ $org->province_ref_id }}';

                                        // Charger les départements puis sélectionner
                                        fetch('{{ url("admin/api/geo/departements") }}/{{ $org->province_ref_id }}')
                                            .then(function(r) { return r.json(); })
                                            .then(function(data) {
                                                if (data.success) {
                                                    var deptSelect = document.getElementById('org_departement_id');
                                                    var html = '<option value="">Sélectionner...</option>';
                                                    data.data.forEach(function(d) {
                                                        var selected = (d.id == '{{ $org->departement_ref_id ?? '' }}') ? ' selected' : '';
                                                        html += '<option value="' + d.id + '" data-nom="' + d.nom + '"' + selected + '>' + d.nom + '</option>';
                                                    });
                                                    deptSelect.innerHTML = html;
                                                    deptSelect.disabled = false;

                                                    @if($org->departement_ref_id)
                                                        // Charger la cascade suivante selon le type de zone
                                                        var isUrbaine = '{{ $org->zone_type }}' === 'urbaine';
                                                        if (isUrbaine) {
                                                            // Charger les communes
                                                            @if($org->commune_ville_ref_id)
                                                                fetch('{{ url("admin/api/geo/communes") }}/{{ $org->departement_ref_id }}')
                                                                    .then(function(r) { return r.json(); })
                                                                    .then(function(data) {
                                                                        if (data.success) {
                                                                            var comSelect = document.getElementById('org_commune_id');
                                                                            var html = '<option value="">(Optionnel)</option>';
                                                                            data.data.forEach(function(d) {
                                                                                var selected = (d.id == '{{ $org->commune_ville_ref_id }}') ? ' selected' : '';
                                                                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '"' + selected + '>' + d.nom + '</option>';
                                                                            });
                                                                            comSelect.innerHTML = html;
                                                                            comSelect.disabled = false;

                                                                            @if($org->arrondissement_ref_id)
                                                                                // Charger les arrondissements
                                                                                fetch('{{ url("admin/api/geo/arrondissements") }}/{{ $org->commune_ville_ref_id }}')
                                                                                    .then(function(r) { return r.json(); })
                                                                                    .then(function(data) {
                                                                                        if (data.success) {
                                                                                            var arrSelect = document.getElementById('org_arrondissement_id');
                                                                                            var html = '<option value="">(Optionnel)</option>';
                                                                                            data.data.forEach(function(d) {
                                                                                                var selected = (d.id == '{{ $org->arrondissement_ref_id }}') ? ' selected' : '';
                                                                                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '"' + selected + '>' + d.nom + '</option>';
                                                                                            });
                                                                                            arrSelect.innerHTML = html;
                                                                                            arrSelect.disabled = false;
                                                                                        }
                                                                                    });
                                                                            @endif
                                                                        }
                                                                    });
                                                            @endif
                                                        } else {
                                                            // Charger les cantons (zone rurale)
                                                            @if($org->canton_ref_id)
                                                                fetch('{{ url("admin/api/geo/cantons") }}/{{ $org->departement_ref_id }}')
                                                                    .then(function(r) { return r.json(); })
                                                                    .then(function(data) {
                                                                        if (data.success) {
                                                                            var cantonSelect = document.getElementById('org_canton_id');
                                                                            var html = '<option value="">(Optionnel)</option>';
                                                                            data.data.forEach(function(d) {
                                                                                var selected = (d.id == '{{ $org->canton_ref_id }}') ? ' selected' : '';
                                                                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '"' + selected + '>' + d.nom + '</option>';
                                                                            });
                                                                            cantonSelect.innerHTML = html;
                                                                            cantonSelect.disabled = false;

                                                                            @if($org->regroupement_ref_id)
                                                                                // Charger les regroupements
                                                                                fetch('{{ url("admin/api/geo/regroupements") }}/{{ $org->canton_ref_id }}')
                                                                                    .then(function(r) { return r.json(); })
                                                                                    .then(function(data) {
                                                                                        if (data.success) {
                                                                                            var regSelect = document.getElementById('org_regroupement_id');
                                                                                            var html = '<option value="">(Optionnel)</option>';
                                                                                            data.data.forEach(function(d) {
                                                                                                var selected = (d.id == '{{ $org->regroupement_ref_id }}') ? ' selected' : '';
                                                                                                html += '<option value="' + d.id + '" data-nom="' + d.nom + '"' + selected + '>' + d.nom + '</option>';
                                                                                            });
                                                                                            regSelect.innerHTML = html;
                                                                                            regSelect.disabled = false;
                                                                                        }
                                                                                    });
                                                                            @endif
                                                                        }
                                                                    });
                                                            @endif
                                                        }
                                                    @endif
                                                }
                                            });
                                    }
                                @endif

                                // ----- CHARGEMENT DES FONDATEURS -----
                                var existingFondateurs = @json($fondateursData);
                                if (existingFondateurs && existingFondateurs.length > 0) {
                                    console.log('📋 Chargement de ' + existingFondateurs.length + ' fondateur(s)');
                                    existingFondateurs.forEach(function(f) {
                                        if (f.nom || f.prenom) {
                                            fondateurs.push({
                                                civilite: f.civilite || 'M',
                                                nip: f.nip || '',
                                                nom: f.nom || '',
                                                prenom: f.prenom || '',
                                                fonction: f.fonction || ''
                                            });
                                        }
                                    });
                                    updateFondateursList();
                                } else {
                                    console.log('⚠️ Aucun fondateur trouvé');
                                }

                                // ----- CHARGEMENT DES MEMBRES DU BUREAU -----
                                var existingMembresBureau = @json($membresBureauData);
                                if (existingMembresBureau && existingMembresBureau.length > 0) {
                                    console.log('📋 Chargement de ' + existingMembresBureau.length + ' membre(s) du bureau');
                                    existingMembresBureau.forEach(function(m) {
                                        if (m.nom || m.prenom) {
                                            membresBureau.push({
                                                nip: m.nip || '',
                                                nom: m.nom || '',
                                                prenom: m.prenom || '',
                                                fonction: m.fonction || '',
                                                contact: m.contact || '',
                                                domicile: m.domicile || '',
                                                afficher_recepisse: m.afficher_recepisse || 0
                                            });
                                        }
                                    });
                                    updateMembresBureauList();
                                } else {
                                    console.log('⚠️ Aucun membre du bureau trouvé');
                                }

                                // ----- CHARGEMENT DES ADHÉRENTS -----
                                var existingAdherents = @json($adherentsData);
                                if (existingAdherents && existingAdherents.length > 0) {
                                    console.log('📋 Chargement de ' + existingAdherents.length + ' adhérent(s)');
                                    existingAdherents.forEach(function(a) {
                                        if (a.nom || a.prenom) {
                                            adherents.push({
                                                nip: a.nip || '',
                                                nom: a.nom || '',
                                                prenom: a.prenom || '',
                                                profession: a.profession || ''
                                            });
                                        }
                                    });
                                    updateAdherentsList();
                                } else {
                                    console.log('⚠️ Aucun adhérent trouvé');
                                }

                                // ----- AFFICHAGE DES DOCUMENTS EXISTANTS -----
                                @if($dossier->documents && $dossier->documents->count() > 0)
                                    console.log('📄 ' + {{ $dossier->documents->count() }} + ' document(s) existant(s)');
                                @endif
                            @endif

                            console.log('✅ Formulaire SGLP (édition) initialisé');
                            });
                        </script>
@endsection