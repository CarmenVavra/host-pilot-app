<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\DeleteAccountRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Services\AccountDeleter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.edit')->with('status', 'Profil gespeichert.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()->route('profile.edit')->with('status', 'Passwort geändert.');
    }

    /**
     * Permanently deletes the account and all of its data, then signs out.
     */
    public function destroy(DeleteAccountRequest $request, AccountDeleter $deleter): RedirectResponse
    {
        $user = $request->user();

        Auth::guard('web')->logout();
        $deleter->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Dein Konto und alle Daten wurden gelöscht.');
    }
}
