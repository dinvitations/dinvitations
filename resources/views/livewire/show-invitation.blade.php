<div>
    {!! $html !!}

    @if (!empty($qrcode))
        <div style="display: flex; justify-content: center; margin-top: 2rem;">
            <img src="data:image/png;base64,{{ $qrcode }}" alt="QR Code" style="max-width: 150px; height: auto;">
        </div>
    @endif

    @if(!empty($css))
        <style>{!! $css !!}</style>
    @endif

    @if(!empty($js))
        <script>{!! $js !!}</script>
    @endif
</div>
