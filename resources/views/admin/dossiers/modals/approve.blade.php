{{-- ===================================================================== --}}
{{-- MODAL D'APPROBATION - BOUTONS ANNULER CORRIGÉS BOOTSTRAP 4 --}}
{{-- Fichier: resources/views/admin/dossiers/modals/approve.blade.php --}}
{{-- ===================================================================== --}}

<!-- Modal d'approbation -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Approuver le Dossier
                </h5>
                {{-- ✅ CORRECTION BOOTSTRAP 4 : Remplacer data-bs-dismiss par data-dismiss --}}
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="approveForm">
                @csrf
                <div class="modal-body">
                    
                    <!-- Informations du dossier -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-success">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6><i class="fas fa-check-circle"></i> <strong>Confirmation d'approbation</strong></h6>
                                        <strong>Dossier:</strong> {{ $dossier->numero_dossier ?? 'N/A' }}<br>
                                        <strong>Organisation:</strong> {{ $dossier->organisation->nom ?? 'N/A' }}<br>
                                        <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $dossier->organisation->type ?? 'N/A')) }}<br>
                                        <small class="text-muted">Cette action changera le statut à "Approuvé" et sera définitive.</small>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <div class="bg-white text-success p-2 rounded">
                                            <i class="fas fa-certificate fa-2x"></i><br>
                                            <small><strong>Approbation Officielle</strong></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Colonne gauche - Informations principales -->
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-certificate mr-2"></i>Informations du Récépissé</h6>
                            
                            <!-- Numéro de récépissé -->
                            <div class="mb-3">
                                <label for="numero_recepisse_final" class="form-label">
                                    <i class="fas fa-hashtag mr-1"></i>Numéro de Récépissé Final <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="numero_recepisse_final" 
                                       id="numero_recepisse_final" 
                                       class="form-control" 
                                       placeholder="Ex: REC-{{ date('Y') }}-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}"
                                       value="{{ $dossier->organisation->numero_recepisse ?? '' }}"
                                       required>
                                <small class="form-text text-muted">Numéro unique d'identification officielle</small>
                            </div>
                            
                            <!-- Date d'approbation -->
                            <div class="mb-3">
                                <label for="date_approbation" class="form-label">
                                    <i class="fas fa-calendar mr-1"></i>Date d'Approbation <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="date_approbation" 
                                       id="date_approbation" 
                                       class="form-control" 
                                       value="{{ date('Y-m-d') }}"
                                       required>
                                <small class="form-text text-muted">Date officielle de l'approbation</small>
                            </div>
                            
                            <!-- Durée de validité -->
                            <div class="mb-3">
                                <label for="validite_mois" class="form-label">
                                    <i class="fas fa-clock mr-1"></i>Durée de Validité
                                </label>
                                <select name="validite_mois" id="validite_mois" class="form-control">
                                    <option value="">Validité permanente</option>
                                    <option value="12">1 an</option>
                                    <option value="24">2 ans</option>
                                    <option value="36">3 ans</option>
                                    <option value="60">5 ans</option>
                                    <option value="120">10 ans</option>
                                </select>
                                <small class="form-text text-muted">Laissez vide pour une validité permanente</small>
                            </div>
                            
                        </div>
                        
                        <!-- Colonne droite - Actions et options -->
                        <div class="col-md-6">
                            <h6 class="mb-3"><i class="fas fa-cogs mr-2"></i>Options d'Approbation</h6>
                            
                            <!-- Actions automatiques -->
                            <div class="mb-3">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <small><strong>Actions Automatiques</strong></small>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="generer_recepisse" name="generer_recepisse" checked>
                                            <label class="form-check-label" for="generer_recepisse">
                                                <i class="fas fa-file-pdf text-danger mr-1"></i>Générer le récépissé définitif PDF
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="envoyer_email_approbation" name="envoyer_email_approbation" checked>
                                            <label class="form-check-label" for="envoyer_email_approbation">
                                                <i class="fas fa-envelope text-primary mr-1"></i>Envoyer email de confirmation
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="publier_annuaire" name="publier_annuaire">
                                            <label class="form-check-label" for="publier_annuaire">
                                                <i class="fas fa-globe text-info mr-1"></i>Publier dans l'annuaire public
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Validation par agent -->
                            <div class="mb-3">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <small><strong>Validation</strong></small>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user-check"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <strong>{{ auth()->user()->name }}</strong><br>
                                                <small class="text-muted">{{ auth()->user()->role ?? 'Agent validateur' }}</small><br>
                                                <small class="text-muted">{{ date('d/m/Y à H:i') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Commentaire d'approbation -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="commentaire_approbation" class="form-label">
                                    <i class="fas fa-comment mr-1"></i>Commentaire d'Approbation
                                </label>
                                <textarea name="commentaire_approbation" 
                                          id="commentaire_approbation" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Commentaire optionnel sur l'approbation du dossier...">Dossier approuvé conformément aux exigences légales et réglementaires en vigueur.</textarea>
                                <small class="form-text text-muted">Ce commentaire apparaîtra dans l'historique du dossier</small>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <div class="modal-footer">
                    {{-- ✅ CORRECTION BOOTSTRAP 4 : Remplacer data-bs-dismiss par data-dismiss --}}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle mr-1"></i>Confirmer l'Approbation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script JavaScript pour la modal d'approbation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveForm = document.getElementById('approveForm');
    const approveModal = document.getElementById('approveModal');
    
    // Gestionnaire de soumission du formulaire d'approbation
    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation côté client
            const numeroRecepisse = document.getElementById('numero_recepisse_final').value.trim();
            const dateApprobation = document.getElementById('date_approbation').value;
            
            if (!numeroRecepisse) {
                showAlert('warning', 'Le numéro de récépissé est obligatoire');
                return;
            }
            
            if (!dateApprobation) {
                showAlert('warning', 'La date d\'approbation est obligatoire');
                return;
            }
            
            // Désactiver le bouton pendant l'envoi
            const submitBtn = approveForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Approbation en cours...';
            submitBtn.disabled = true;
            
            // Préparer les données
            const formData = new FormData(approveForm);
            
            // Envoyer la requête
            fetch(`/admin/dossiers/${window.dossierId}/valider`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ✅ CORRECTION BOOTSTRAP 4 : Utiliser jQuery pour fermer la modal
                    $('#approveModal').modal('hide');
                    
                    // Afficher le succès
                    showAlert('success', 'Dossier approuvé avec succès !');
                    
                    // Recharger la page après un délai
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                } else {
                    showAlert('error', data.message || 'Erreur lors de l\'approbation');
                    
                    // Réactiver le bouton
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('error', 'Erreur technique lors de l\'approbation');
                
                // Réactiver le bouton
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Auto-générer un numéro de récépissé si le champ est vide
    if (approveModal) {
        $('#approveModal').on('shown.bs.modal', function() {
            const numeroField = document.getElementById('numero_recepisse_final');
            if (numeroField && !numeroField.value.trim()) {
                const year = new Date().getFullYear();
                const random = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
                const typeOrg = '{{ strtoupper(substr($dossier->organisation->type ?? "ORG", 0, 3)) }}';
                numeroField.value = `${typeOrg}-${year}-${random}`;
            }
        });
    }
});
</script>

<!-- Styles pour la modal -->
<style>
#approveModal .modal-dialog {
    max-width: 900px;
}

#approveModal .card {
    border: 1px solid #e3e8ee;
}

#approveModal .form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

#approveModal .alert-success {
    border-left: 4px solid #28a745;
}

#approveModal .bg-light {
    background-color: #f8f9fa !important;
}

/* ✅ CORRECTIONS BOOTSTRAP 4 : Styles boutons close */
#approveModal .close {
    color: #fff;
    opacity: 0.8;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    text-shadow: 0 1px 0 #fff;
}

#approveModal .close:hover {
    color: #fff;
    opacity: 1;
    text-decoration: none;
}

#approveModal .close:focus {
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}
</style>