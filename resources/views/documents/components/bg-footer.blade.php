{{-- Image de pied de page - Pleine largeur A4 (21cm) avec marge négative gauche --}}
@if(isset($bg_pied_page_base64) && $bg_pied_page_base64)
    <div style="position: fixed; bottom: -3cm; left: -2cm; width: 21cm; margin: 0; padding: 0; z-index: -1;">
        <img src="{{ $bg_pied_page_base64 }}" alt="Pied de page"
            style="width: 100%; height: auto; display: block; margin: 0; padding: 0;">
    </div>
@endif