<div class="max-w-lg">
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
