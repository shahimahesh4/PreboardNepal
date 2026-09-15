<?php

namespace App\Providers;

use App\Models\AuditEntry;
use App\Models\Chapter;
use App\Models\ContentReport;
use App\Models\Exam;
use App\Models\Order;
use App\Models\Product;
use App\Models\StudyDocument;
use App\Models\Subject;
use App\Models\User;
use App\Policies\StaffPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        foreach ([Product::class, Order::class, Subject::class, Chapter::class, StudyDocument::class, Exam::class, ContentReport::class, User::class, AuditEntry::class] as $model) {
            Gate::policy($model, StaffPolicy::class);
        }

        View::composer('components.layouts.app', function ($view): void {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();
            $completedSets = $user->attempts()->where('status', 'submitted')->count();
            $weeklyReads = DB::table('reading_events')
                ->where('user_id', $user->id)
                ->where('last_read_at', '>=', now()->startOfWeek())
                ->count();
            $weeklyPractice = $user->attempts()->where('started_at', '>=', now()->startOfWeek())->count();

            $view->with('sidebarSummary', [
                'weeklyActivities' => $weeklyReads + $weeklyPractice,
                'completedSets' => $completedSets,
                'milestonePercent' => min(100, $completedSets * 20),
            ]);
        });
    }
}
