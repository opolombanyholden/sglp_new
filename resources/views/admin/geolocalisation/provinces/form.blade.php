{{-- resources/views/admin/provinces/form.blade.php --}}
<div class="row g-3">
    {{-- Informations de base --}}
    <div class="col-md-6">
        <label for="nom" class="form-label required">
            <i class="fas fa-map-marked-alt me-1"></i>
            Nom de la Province
        </label>
        <input type="text" 
               class="form-control @error('nom') is-invalid @enderror" 
               id="nom" 
               name="nom" 
               value="{{ old('nom', $province->nom) }}" 
               required
               placeholder="Ex: Estuaire, Haut-Ogooué...">
        @error('nom')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Le nom officiel de la province</div>
    </div>

    <div class="col-md-6">
        <label for="code" class="form-label">
            <i class="fas fa-tag me-1"></i>
            Code Province
        </label>
        <input type="text" 
               class="form-control @error('code') is-invalid @enderror" 
               id="code" 
               name="code" 
               value="{{ old('code', $province->code) }}" 
               maxlength="10"
               placeholder="Ex: EST, LIT, HOG..."
               style="text-transform: uppercase;">
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Code unique (auto-généré si vide)</div>
    </div>

    <div class="col-md-6">
        <label for="chef_lieu" class="form-label">
            <i class="fas fa-city me-1"></i>
            Chef-lieu
        </label>
        <input type="text" 
               class="form-control @error('chef_lieu') is-invalid @enderror" 
               id="chef_lieu" 
               name="chef_lieu" 
               value="{{ old('chef_lieu', $province->chef_lieu) }}" 
               placeholder="Ex: Libreville, Franceville...">
        @error('chef_lieu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Ville principale de la province</div>
    </div>

    <div class="col-md-6">
        <label for="ordre_affichage" class="form-label">
            <i class="fas fa-sort-numeric-down me-1"></i>
            Ordre d'affichage
        </label>
        <input type="number" 
               class="form-control @error('ordre_affichage') is-invalid @enderror" 
               id="ordre_affichage" 
               name="ordre_affichage" 
               value="{{ old('ordre_affichage', $province->ordre_affichage ?? 0) }}" 
               min="0" 
               max="999">
        @error('ordre_affichage')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Position dans les listes (0 = premier)</div>
    </div>

    {{-- Description --}}
    <div class="col-12">
        <label for="description" class="form-label">
            <i class="fas fa-align-left me-1"></i>
            Description
        </label>
        <textarea class="form-control @error('description') is-invalid @enderror" 
                  id="description" 
                  name="description" 
                  rows="3"
                  placeholder="Description de la province, ses caractéristiques, son histoire...">{{ old('description', $province->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Description optionnelle de la province</div>
    </div>

    {{-- Données géographiques --}}
    <div class="col-12">
        <hr class="my-4">
        <h6 class="text-primary">
            <i class="fas fa-globe me-2"></i>
            Données Géographiques
        </h6>
    </div>

    <div class="col-md-4">
        <label for="superficie_km2" class="form-label">
            <i class="fas fa-ruler-combined me-1"></i>
            Superficie (km²)
        </label>
        <input type="number" 
               class="form-control @error('superficie_km2') is-invalid @enderror" 
               id="superficie_km2" 
               name="superficie_km2" 
               value="{{ old('superficie_km2', $province->superficie_km2) }}" 
               min="0" 
               max="999999.99" 
               step="0.01"
               placeholder="Ex: 25000">
        @error('superficie_km2')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Superficie en kilomètres carrés</div>
    </div>

    <div class="col-md-4">
        <label for="population_estimee" class="form-label">
            <i class="fas fa-users me-1"></i>
            Population estimée
        </label>
        <input type="number" 
               class="form-control @error('population_estimee') is-invalid @enderror" 
               id="population_estimee" 
               name="population_estimee" 
               value="{{ old('population_estimee', $province->population_estimee) }}" 
               min="0" 
               max="99999999"
               placeholder="Ex: 850000">
        @error('population_estimee')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Nombre d'habitants estimé</div>
    </div>

    <div class="col-md-4">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input @error('is_active') is-invalid @enderror" 
                   type="checkbox" 
                   id="is_active" 
                   name="is_active" 
                   value="1"
                   {{ old('is_active', $province->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                <i class="fas fa-power-off me-1"></i>
                Province active
            </label>
            @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Visible dans les formulaires</div>
        </div>
    </div>

    {{-- Coordonnées GPS --}}
    <div class="col-12">
        <hr class="my-4">
        <h6 class="text-primary">
            <i class="fas fa-map-pin me-2"></i>
            Localisation GPS (optionnel)
        </h6>
    </div>

    <div class="col-md-6">
        <label for="latitude" class="form-label">
            <i class="fas fa-compass me-1"></i>
            Latitude
        </label>
        <input type="number" 
               class="form-control @error('latitude') is-invalid @enderror" 
               id="latitude" 
               name="latitude" 
               value="{{ old('latitude', $province->latitude) }}" 
               min="-90" 
               max="90" 
               step="0.00000001"
               placeholder="Ex: 0.3901">
        @error('latitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Entre -90 et 90 degrés</div>
    </div>

    <div class="col-md-6">
        <label for="longitude" class="form-label">
            <i class="fas fa-compass me-1"></i>
            Longitude
        </label>
        <input type="number" 
               class="form-control @error('longitude') is-invalid @enderror" 
               id="longitude" 
               name="longitude" 
               value="{{ old('longitude', $province->longitude) }}" 
               min="-180" 
               max="180" 
               step="0.00000001"
               placeholder="Ex: 9.4544">
        @error('longitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Entre -180 et 180 degrés</div>
    </div>

    {{-- Calcul de densité en temps réel --}}
    <div class="col-12">
        <div class="alert alert-info" id="densite-info" style="display: none;">
            <i class="fas fa-calculator me-2"></i>
            <strong>Densité calculée :</strong> <span id="densite-value">0</span> hab/km²
        </div>
    </div>
</div>

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.alert-info {
    background-color: #e7f3ff;
    border-color: #b8daff;
    color: #004085;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-conversion en majuscules pour le code
    const codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Calcul automatique de la densité
    const superficieInput = document.getElementById('superficie_km2');
    const populationInput = document.getElementById('population_estimee');
    const densiteInfo = document.getElementById('densite-info');
    const densiteValue = document.getElementById('densite-value');
    
    function calculerDensite() {
        const superficie = parseFloat(superficieInput?.value);
        const population = parseInt(populationInput?.value);
        
        if (superficie && population && superficie > 0) {
            const densite = (population / superficie).toFixed(2);
            densiteValue.textContent = new Intl.NumberFormat('fr-FR').format(densite);
            densiteInfo.style.display = 'block';
        } else {
            densiteInfo.style.display = 'none';
        }
    }
    
    superficieInput?.addEventListener('input', calculerDensite);
    populationInput?.addEventListener('input', calculerDensite);
    
    // Calcul initial si les valeurs existent
    calculerDensite();

    // Validation des coordonnées GPS
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    
    function validateCoordinate(input, min, max, name) {
        if (input) {
            input.addEventListener('blur', function() {
                const value = parseFloat(this.value);
                if (this.value && (isNaN(value) || value < min || value > max)) {
                    this.setCustomValidity(`${name} doit être comprise entre ${min} et ${max} degrés`);
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }
    }
    
    validateCoordinate(latInput, -90, 90, 'La latitude');
    validateCoordinate(lngInput, -180, 180, 'La longitude');

    // Formatage des nombres avec séparateurs de milliers
    function formatNumber(input) {
        input.addEventListener('blur', function() {
            const value = parseInt(this.value);
            if (!isNaN(value)) {
                // On garde la valeur brute pour le submit, mais on peut afficher formaté
                console.log(`Valeur formatée: ${new Intl.NumberFormat('fr-FR').format(value)}`);
            }
        });
    }
    
    if (populationInput) formatNumber(populationInput);
    if (superficieInput) formatNumber(superficieInput);
});
</script>
@endpush