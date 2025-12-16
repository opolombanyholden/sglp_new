@if($has_signature ?? false)
    @if(!empty($signature_text))
        {{-- Signature dynamique depuis le template --}}
        <div style="margin-top:5px;">

            {!! $signature_text !!}
        </div>
    @else
        {{-- Signature par défaut --}}
        <div style="margin-top: 40px;">
            <p style="margin-bottom: 10px;">
                <strong>Fait à {{ $geographie['lieu_edition'] ?? 'Libreville' }}, le {{ now()->format('d/m/Y') }}</strong>
            </p>

            <p style="margin-top: 30px; margin-bottom: 5px;">
                <strong>{{ $ministere['nom_court'] ?? 'Le Ministre de l\'Intérieur' }}</strong>
            </p>

            @if(!empty($signature_path) && file_exists($signature_path))
                <div style="margin-top: 20px;">
                    <img src="{{ $signature_path }}" alt="Signature" style="max-width: 200px; height: auto;" />
                </div>
            @endif
        </div>
    @endif
@endif