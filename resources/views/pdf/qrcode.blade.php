<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Guest QR Code</title>
    <style>
        @page {
            margin: 0;
        }

        html,
        body {
            width: 164.4pt;
            height: 113.4pt;
            margin: 0;
            padding: 0;
            font-size: 7pt;
            font-family: Arial, sans-serif;
            overflow: hidden;
            position: relative;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 4pt;
            box-sizing: border-box;
        }

        h2 {
            font-size: 8pt;
            margin: 0 0 2pt 0;
            word-break: break-word;
            line-height: 1.2;
            max-height: 2.4em;
            overflow: hidden;
        }

        p {
            margin: 0 0 2pt 0;
        }

        img {
            margin-top: 2pt;
            width: auto;
            max-height: 60pt;
        }

        .vertical-text {
            position: absolute;
            top: calc(50% + 16pt);
            transform: rotate(-90deg) translateY(-50%);
            transform-origin: top left;
            font-size: 8pt;
            font-weight: bold;
            color: #999;
            opacity: 0.5;
            letter-spacing: 1pt;
        }

        .vertical-text.left {
            left: 8pt;
        }

        .vertical-text.right {
            right: 8pt;
            left: auto;
            transform: rotate(90deg) translateY(50%);
            transform-origin: top right;
        }
    </style>
</head>

<body>
    <div class="vertical-text left">Souvenir</div>
    <div class="vertical-text right">Souvenir</div>

    <h2>{{ $guest->guest->name }}</h2>
    <p>Checked in at:</p>
    <p>{{ \Carbon\Carbon::parse($guest->attended_at)->format('M d, Y \a\t h:i A') }}</p>

    <br>

    <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code">

    @if (request()->has('print'))
    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = () => window.close();
        };
    </script>
    @endif
</body>

</html>