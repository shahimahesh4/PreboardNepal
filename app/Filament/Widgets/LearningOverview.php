<?php

namespace App\Filament\Widgets;

use App\Models\Attempt;
use App\Models\ContentReport;
use App\Models\StudyDocument;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LearningOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [Stat::make('Published resources', StudyDocument::published()->count())->description('Available in the student library')->color('primary'), Stat::make('Completed practice', Attempt::where('status', 'submitted')->count())->description('Submitted learner attempts')->color('success'), Stat::make('Open reports', ContentReport::whereIn('status', ['open', 'reviewing'])->count())->description('Content concerns awaiting resolution')->color('warning')];
    }
}
