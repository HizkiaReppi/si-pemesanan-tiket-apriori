<?php

namespace App\Providers;

use App\Events\OrderCompleted;
use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Listeners\GenerateETicketOnPayment;
use App\Listeners\LogAuditTrail;
use App\Listeners\SendOrderCompletedEmail;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\SendPaymentConfirmationEmail;
use App\Models\Order;
use App\Models\Payment;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureEvents();
        $this->configureProduction();
    }

    /**
     * Configure event listeners and subscribers.
     */
    protected function configureEvents(): void
    {
        // Observers
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);

        // Order created
        Event::listen(OrderCreated::class, SendOrderConfirmationEmail::class);

        // Order paid
        Event::listen(OrderPaid::class, GenerateETicketOnPayment::class);
        Event::listen(OrderPaid::class, SendPaymentConfirmationEmail::class);

        // Order completed
        Event::listen(OrderCompleted::class, SendOrderCompletedEmail::class);

        // Audit logging
        Event::subscribe(LogAuditTrail::class);
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

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );

        // Enforce strict model behavior
        Model::shouldBeStrict(! app()->isProduction());
    }

    /**
     * Configure production-specific optimizations.
     */
    protected function configureProduction(): void
    {
        // Force HTTPS in production
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Prevent N+1 queries in development
        Model::preventLazyLoading(! app()->isProduction());
    }
}
