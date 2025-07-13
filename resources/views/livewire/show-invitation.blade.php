<div>
    {!! $html !!}

    @if (!empty($css))
        <style>
            {!! $css !!}
        </style>
    @endif

    @if (!empty($js))
        <script>
            {!! $js !!}
        </script>
    @endif

    <script>
        function submitRSVP(guestId, rsvpValue, blockId) {
            fetch(`/rsvp`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        guest_id: guestId,
                        rsvp: rsvpValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const container = document.querySelector(`[data-rsvp-block-id="${blockId}"]`);
                    if (container) {
                        container.innerHTML = '<p style="font-size: 21px; text-align: center;">Thank you for confirming your attendance.</p>';
                    }
                })
                .catch(error => {
                    console.error('RSVP failed:', error);
                    alert('RSVP failed. Please try again.');
                });
        }
    </script>

</div>