@if(isset($qr_code_svg) && !empty($qr_code_svg))
<div class="qr-code">
    {!! $qr_code_svg !!}
    <p class="qr-code-label">
        <strong>Vérifier ce document</strong><br>
        {{ $document['numero_document'] ?? '' }}
    </p>
</div>
@endif