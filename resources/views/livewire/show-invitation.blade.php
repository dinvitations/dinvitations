<div>
    {!! $html !!}

    @if (!empty($css))
        <style>{!! $css !!}</style>
    @endif

    @if (!empty($js))
        <script>{!! $js !!}</script>
    @endif
</div>
