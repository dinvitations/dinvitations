<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 0cm;
            padding: 0cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            background-color: #F8F8F8;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            padding: 60px;
            box-sizing: border-box;
        }

        .page-break {
            page-break-after: always;
        }

        .cover-date {
            font-size: 72px;
            line-height: 1.2;
            text-align: center;
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .footer, .footer-right, .footer-left, .back-footer {
            font-size: 18px;
        }

        .footer {
            position: absolute;
            text-align: right;
            bottom: 60px;
            right: 60px;
        }

        .back-footer {
            position: absolute;
            bottom: 60px;
            left: 60px;
        }

        .footer-left {
            position: absolute;
            bottom: 40px;
            left: 60px;
            font-size: 14px;
        }

        .footer-right {
            position: absolute;
            bottom: 40px;
            right: 60px;
            font-size: 14px;
        }

        .guest-name {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .guest-time {
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }

        .greeting-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            text-align: center;
            margin: 0 auto;
        }

        .greeting-container img {
            max-width: 80%;
            height: auto;
            border-radius: 16px;
            /* Shadow dengan fallback untuk PDF renderer */
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            -webkit-box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            -moz-box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
    </style>
</head>

<body>

    @php
        $sameDay = $dateStart->toDateString() === $dateEnd->toDateString();
        $pageCount = 1;
    @endphp

    {{-- Cover Page --}}
    <div class="page page-break">
        <div class="cover-date">
            <div>{{ $dateStart->format('d') }}</div>
            <div>{{ $dateStart->format('m') }}</div>
            <div>{{ $dateStart->format('y') }}</div>
        </div>
        <div class="footer">
            <strong>{{ $invitation->event_name }}</strong><br>
            @if ($sameDay)
                {{ $dateStart->format('M d, h.i A') }} to {{ $dateEnd->format('h.i A') }}
            @else
                {{ $dateStart->format('M d, h.i A') }} to {{ $dateEnd->format('M d, h.i A') }}
            @endif
        </div>
    </div>

    {{-- Greeting Pages --}}
    @foreach ($greetingGuests as $guest)
        <div class="page page-break">
            <div class="guest-name">{{ $guest['name'] }}</div>
            <div class="guest-time">
                {{ \Carbon\Carbon::parse($guest['attended_at'])->format('F d, Y \a\t h:i A') }}
            </div>

            <div class="greeting-container">
                @if ($guest['greeting_wall_image_url'])
                    <img src="{{ $guest['greeting_wall_image_url'] }}" alt="Greeting of {{ $guest['name'] }}">
                @endif
            </div>

            <div class="footer-left">{{ $guest['type'] }}</div>
            <div class="footer-right">{{ $pageCount }}/{{ count($greetingGuests) }}</div>
        </div>
        @php $pageCount++; @endphp
    @endforeach

    {{-- Back Cover Page --}}
    <div class="page">
        <div class="cover-date">
            <div>{{ $dateEnd->format('d') }}</div>
            <div>{{ $dateEnd->format('m') }}</div>
            <div>{{ $dateEnd->format('y') }}</div>
        </div>
        <div class="back-footer">
            <strong>{{ $invitation->event_name }}</strong><br>
            @if ($sameDay)
                {{ $dateStart->format('M d, h.i A') }} to {{ $dateEnd->format('h.i A') }}
            @else
                {{ $dateStart->format('M d, h.i A') }} to {{ $dateEnd->format('M d, h.i A') }}
            @endif
        </div>
    </div>
</body>

</html>
