<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Responses\LoginResponse;
use App\Models\Report;
use App\Observers\ReportObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        $this->configureDefaults();

        // Google reporter yang sudah login diarahkan ke /landing, bukan /dashboard
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if (auth('google')->check()) {
                return route('landing');
            }
            return route('satgas.dashboard');
        });
        $this->app->singleton(
            LoginResponseContract::class,
            LoginResponse::class,
        );

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = route('password.reset', ['token' => $token])
                . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

            return (new MailMessage)
                ->subject('Reset Password SIKPK POLBAN')
                ->greeting('Halo!')
                ->line('Kami menerima permintaan reset password untuk akun Anda.')
                ->action('Reset Password', $url)
                ->line('Link reset password berlaku selama 15 menit.')
                ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
        });
        Report::observe(ReportObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}
