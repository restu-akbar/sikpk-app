<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Mail\ProfileEditLinkMail;
use App\Services\MailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected MailService $mailService
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function secureEdit(Request $request): Response
    {
        if ((string) $request->user !== (string) auth()->id()) {
            abort(403);
        }
        return Inertia::render('settings/ProfileEditSecure');
    }

    public function requestEdit(Request $request)
    {
        $user = $request->user();

        $signedUrl = URL::temporarySignedRoute(
            'settings.profile.secure-edit',
            now()->addMinutes(15),
            [
                'user' => $user->id,
            ]
        );

        $this->mailService->send(
            $user->email,
            new ProfileEditLinkMail(
                $user->name,
                $signedUrl
            )
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Verification link sent.')]);
        return back();
    }
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('settings.profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
