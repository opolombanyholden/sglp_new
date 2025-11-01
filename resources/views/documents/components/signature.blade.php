@if($has_signature ?? false)
<div style="margin-top: 30px;">
    <p style="margin-bottom: 10px;"><strong>Le Directeur Général</strong></p>
    
    @if(isset($signature_path) && file_exists($signature_path))
        <img src="{{ $signature_path }}" alt="Signature" class="signature-image">
    @else
        <div style="height: 60px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #999; font-size: 9pt;">
            [Signature électronique]
        </div>
    @endif
    
    <p style="margin-top: 10px;">
        <strong>{{ $agent['nom'] ?? 'Direction Générale' }}</strong>
    </p>
</div>
@endif