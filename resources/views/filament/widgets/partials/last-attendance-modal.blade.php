<div class="flex flex-col md:flex-row gap-4">
    <!-- Left column: Attendance details table -->
    <div class="flex-1 max-w-lg">
        <table class="w-full text-left">
            <tbody>
                <tr>
                    <td class="py-4 font-semibold">Name</td>
                    <td class="py-4">{{ $guest }}</td>
                </tr>
                <tr>
                    <td class="py-4 font-semibold">Category</td>
                    <td class="py-4">{{ $category }}</td>
                </tr>
                <tr>
                    <td class="py-4 font-semibold">Total Guests</td>
                    <td class="py-4">{{ $guestCount }}</td>
                </tr>
                <tr>
                    <td class="py-4 font-semibold">Attendance Date</td>
                    <td class="py-4">{{ $attendedAt }}</td>
                </tr>
                <tr>
                    <td class="py-4 font-semibold">Souvenir At</td>
                    <td class="py-4">{{ $souvenirAt }}</td>
                </tr>
                <tr>
                    <td class="py-4 font-semibold">Left At</td>
                    <td class="py-4">{{ $leftAt }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Right column: QR Code -->
    <div class="flex-1 flex justify-center items-center">
        @if (isset($souvenirQrPath))
            <img src="{{ $souvenirQrPath }}" alt="Souvenir QR Code" class="max-w-40 max-h-40 object-contain border rounded" />
        @else
            <span>No QR Code</span>
        @endif
    </div>
</div>
