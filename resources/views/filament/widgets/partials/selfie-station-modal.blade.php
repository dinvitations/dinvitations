@php
    $category = match($record->type) {
        'reg' => 'General',
        'vip' => 'VIP',
        'vvip' => 'VVIP',
        default => strtoupper($record->type),
    };

    $attendedAt = optional($record->selfie_at)->format('F j, Y \a\t h:i A') ?? '-';
@endphp

<div class="flex flex-col gap-4 w-full">
    <div class="flex-1 w-full">
        <table class="w-full text-left table-auto">
            <tbody>
                <tr>
                    <td class="py-4 font-semibold w-1/3">Category</td>
                    <td class="py-4">{{ $category }}</td>
                </tr>
                <tr>
                    <td class="py-4 font-semibold">Attendance Date</td>
                    <td class="py-4">{{ $attendedAt }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="py-4 font-semibold">Photos</td>
                </tr>
                <tr>
                    <td colspan="2" class="py-4">
                        @if ($record->selfie_photo_url)
                            <img
                                src="{{ Storage::disk('minio')->temporaryUrl($record->selfie_photo_url, now()->addMinutes(5)) }}"
                                alt="Selfie Photo"
                                class="w-full object-cover border rounded-[16px]"
                            />
                        @else
                            <div class="text-gray-400 italic">No photo available</div>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
