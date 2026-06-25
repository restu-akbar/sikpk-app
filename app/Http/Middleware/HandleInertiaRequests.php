<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => function () use ($request) {
                    $reporter = $request->user('google');
                    $satgas   = $request->user();

                    if ($reporter) {
                        return [
                            'id'             => $reporter->id,
                            'name'           => $reporter->name,
                            'email'          => $reporter->email,
                            'whatsapp'       => $reporter->whatsapp,
                            'status_civitas' => $reporter->status_civitas,
                            'jurusan'        => $reporter->jurusan,
                            'prodi'          => $reporter->prodi,
                            'role'           => null,
                            'must_change_password' => false,
                            'public_key'           => null,
                            'emek_password'        => null,
                            'emek_password_salt'   => null,
                        ];
                    }

                    if ($satgas) {
                        return [
                            'id'    => $satgas->id,
                            'name'  => $satgas->name,
                            'email' => $satgas->email,
                            'role'  => $satgas->role,
                            'must_change_password' => $satgas->must_change_password,
                            'public_key'           => $satgas->public_key,
                            'emek_password'        => $satgas->emek_password,
                            'emek_password_salt'   => $satgas->emek_password_salt,
                        ];
                    }

                    return null;
                },
            ],
            'flash' => [
                'toast' => fn() => $request->session()->get('toast'),
                'reportSubmitted' => fn() => $request->session()->get('reportSubmitted'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
