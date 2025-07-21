<?php

use App\Livewire\ShowInvitation;
use App\Livewire\ShowTemplate;
use App\Models\InvitationGuest;
use Illuminate\Support\Facades\Route;

Route::get('/template-views/{slug}', ShowTemplate::class)->name('templates.show');
Route::get('/{slug}', ShowInvitation::class)->name('invitation.show');

Route::post('/rsvp', function (Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'guest_id' => 'required|uuid',
        'rsvp' => 'required|boolean',
    ]);

    InvitationGuest::where('id', $validated['guest_id'])->update(['rsvp' => $validated['rsvp']]);

    return response()->json(['success' => true]);
})->name('rsvp');

