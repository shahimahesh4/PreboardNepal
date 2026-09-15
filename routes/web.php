<?php

use App\Actions\Learning\AttemptService;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PageController;
use App\Livewire\Account;
use App\Livewire\Library;
use App\Livewire\PracticeRunner;
use App\Livewire\Reader;
use App\Models\Exam;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/library', Library::class)->name('library');
Route::get('/subjects/{subject}', [PageController::class, 'subject'])->name('subjects.show');
Route::get('/resources/{document}', Reader::class)->name('resources.show');
Route::get('/practice', [PageController::class, 'practice'])->name('practice');
Route::view('/plans', 'pages.plans')->name('plans');
Route::view('/help', 'pages.help')->name('help');
Route::view('/content-policy', 'pages.policy')->name('policy');
Route::post('/demo', [AuthController::class, 'demo'])->middleware('throttle:10,1')->name('demo');
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.form', ['mode' => 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::view('/register', 'auth.form', ['mode' => 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/create-student-account', [AuthController::class, 'logoutToRegister'])->name('register.switch');
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/saved', Library::class)->defaults('savedOnly', true)->name('saved');
    Route::get('/account', Account::class)->name('account');
    Route::post('/practice/{exam}/start', function (Exam $exam, AttemptService $service) {
        return redirect()->route('attempts.show', $service->start(auth()->user(), $exam));
    })->middleware('throttle:20,1')->name('practice.start');
    Route::get('/attempts/{attempt}', PracticeRunner::class)->name('attempts.show');
    Route::view('/email/verify', 'auth.verify')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'Your email is verified.');
    })->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent.');
    })->middleware('throttle:3,1')->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/billing', [BillingController::class, 'history'])->name('billing.history');
    Route::get('/checkout/{product}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::post('/checkout/{product}', [BillingController::class, 'store'])->middleware('throttle:5,1')->name('billing.store');
    Route::get('/orders/{order}', [BillingController::class, 'show'])->name('billing.show');
    Route::get('/orders/{order}/return', [BillingController::class, 'verify'])->middleware('throttle:10,1')->name('billing.return');
    Route::post('/orders/{order}/verify', [BillingController::class, 'verify'])->middleware('throttle:10,1')->name('billing.verify');
});
Route::view('/forgot-password', 'auth.form', ['mode' => 'forgot'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.email');
Route::get('/reset-password/{token}', fn (string $token) => view('auth.form', ['mode' => 'reset', 'token' => $token]))->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
