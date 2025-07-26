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

        .footer {
            position: absolute;
            text-align: right;
            bottom: 60px;
            right: 60px;
            font-size: 18px;
        }

        .back-footer {
            position: absolute;
            bottom: 60px;
            left: 60px;
            font-size: 18px;
        }

        .table-page {
            padding: 60px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 60px;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }
        
        th {
            font-weight: bold;
            border-bottom: 1px solid #aaa;
            text-align: center; /* ✅ Center all headers */
        }

        td.center {
            text-align: center; /* ✅ Use for selected cells */
        }

        .date-top {
            text-align: right;
            font-size: 14px;
            margin-top: -20px;
        }
    </style>
</head>

<body>
    @php
        $sameDay = $dateStart->toDateString() === $dateEnd->toDateString();
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

    {{-- Table Page --}}
    @foreach ($guestsByDate as $date => $guests)
        @php
            $chunks = collect($guests)->chunk(15);
        @endphp

        @foreach ($chunks as $pageIndex => $chunk)
        <div class="page table-page page-break">
            <div style="font-weight: bold;">{{ $invitation->event_name }}</div>
            
            <div class="date-top">
                {{ $date ? \Carbon\Carbon::parse($date)->format('F d, Y') : 'Not Attended' }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Attended</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($chunk as $index => $guest)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $guest['name'] }}</td>
                        <td class="center">{{ $guest['type'] }}</td>
                        <td class="center">{{ $guest['attended_at'] ?: '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
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