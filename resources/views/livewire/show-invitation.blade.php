<div>
    {!! $html !!}

    @if (!empty($guest))
        <div style="margin-top: 2rem; text-align: center;">
            @if (!empty($guest['rsvp']))
                <button
                    wire:click="rsvp('{{ $guest['id'] }}')"
                    style="
                        padding: 0.75rem 1.5rem;
                        font-size: 1rem;
                        background-color: #4CAF50;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        transition: background-color 0.3s ease;
                    "
                    onmouseover="this.style.backgroundColor='#45A049'"
                    onmouseout="this.style.backgroundColor='#4CAF50'"
                >
                    RSVP
                </button>
            @else
                <p style="font-size: 1.125rem; color: #4CAF50; font-weight: 500;">
                    ✅ Thank you for confirming your attendance.
                </p>
            @endif
        </div>

        @if (!empty($guest['qrcode']))
            <div style="display: flex; justify-content: center; margin-top: 2rem;">
                <img
                    src="data:image/png;base64,{{ $guest['qrcode'] }}"
                    alt="QR Code"
                    style="max-width: 150px; height: auto; border: 1px solid #ccc; padding: 0.5rem; border-radius: 8px;"
                >
            </div>
        @endif
    @endif

    @if (!empty($css))
        <style>{!! $css !!}</style>
    @endif

    @if (!empty($js))
        <script>{!! $js !!}</script>
    @endif
</div>
