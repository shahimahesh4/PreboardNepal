<?php

use App\Actions\Learning\AttemptService;
use App\Models\Attempt;
use App\Models\Order;
use App\Models\User;
use App\Services\MembershipPayments;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('preboard:admin {email} {--name=Administrator}', function () {
    $email = $this->argument('email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Enter a valid email address.');

        return 1;
    }
    if (User::where('email', $email)->exists()) {
        $this->error('This account already exists. This command only creates new staff accounts.');

        return 1;
    }
    $password = $this->secret('New administrator password (at least 12 characters)');
    if (strlen($password ?? '') < 12) {
        $this->error('Password must contain at least 12 characters.');

        return 1;
    }
    $user = new User(['name' => $this->option('name'), 'email' => $email, 'password' => $password]);
    $user->role = 'admin';
    $user->email_verified_at = now();
    $user->save();
    $this->info('Administrator created. Open /stnapanel and set up authenticator MFA at first sign-in.');
})->purpose('Create a new verified staff account; requires server access');

Artisan::command('preboard:expire-attempts', function () {
    Attempt::where('status', 'active')->where('deadline_at', '<=', now())->select('id')->chunkById(100, function ($attempts) {
        foreach ($attempts as $attempt) {
            app(AttemptService::class)->expire($attempt->id);
        }
    });
})->purpose('Finalize overdue practice attempts');
Schedule::command('preboard:expire-attempts')->everyMinute()->withoutOverlapping();

Artisan::command('preboard:reconcile-payments', function () {
    Order::whereNotNull('provider_reference')->where(function ($query) {
        $query->whereIn('status', ['pending', 'initiating'])->orWhere(function ($paid) {
            $paid->where('status', 'paid')->where('verified_at', '>=', now()->subDays(30));
        });
    })->chunkById(100, function ($orders) {
        foreach ($orders as $order) {
            if (app(MembershipPayments::class)->canVerify($order)) {
                app(MembershipPayments::class)->verify($order);
            }
        }
    });
})->purpose('Reconcile pending payments and recent refunds with the configured payment providers');
Schedule::command('preboard:reconcile-payments')->everyFiveMinutes()->withoutOverlapping();
