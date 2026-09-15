@props(['title'=>'Learn with clarity'])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Explore Preboard Nepal study notes, chapter practice, and a clearer path to your next exam.">
    <title>{{ $title }} · Preboard Nepal</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
@auth
    <div class="workspace">
        <header class="mobile-topbar">
            <x-brand/>
            <button class="mobile-menu-toggle" type="button" aria-controls="student-sidebar" aria-expanded="false">
                <span class="sr-only">Open navigation menu</span>
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            </button>
        </header>
        <button class="sidebar-backdrop" type="button" tabindex="-1" aria-label="Close navigation menu"></button>
        <aside class="sidebar" id="student-sidebar" aria-label="Student menu">
            <button class="sidebar-close" type="button" aria-label="Close navigation menu"><span aria-hidden="true">×</span></button>
            <div class="sidebar-intro">
                <x-brand/>
                <p>Your focused space to learn, practise, and improve.</p>
            </div>

            <section class="sidebar-week" aria-label="This week's activity">
                <span><i></i> THIS WEEK</span>
                <strong>{{ number_format($sidebarSummary['weeklyActivities']) }}</strong>
                <p>{{ Illuminate\Support\Str::plural('learning step', $sidebarSummary['weeklyActivities']) }} completed this week</p>
            </section>

            <p class="nav-label">YOUR LEARNING JOURNEY</p>
            <nav aria-label="Main navigation">
                @foreach([
                    ['dashboard','home','Start here'],
                    ['library','academic-cap','Learning path'],
                    ['practice','pencil-square','Practice & results'],
                    ['saved','bookmark','Saved resources'],
                ] as [$route,$icon,$label])
                    <a href="{{ route($route) }}" @class(['nav-link','active'=>request()->routeIs($route)]) @if(request()->routeIs($route)) aria-current="page" @endif>
                        <span class="nav-icon"><x-preboard-icon :name="$icon"/></span>
                        <span>{{ $label }}</span>
                        @if(request()->routeIs($route))<span class="nav-status" aria-hidden="true">●</span>@endif
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-bottom">
                <div class="sidebar-progress">
                    <div><span>Practice milestone</span><strong>{{ $sidebarSummary['milestonePercent'] }}%</strong></div>
                    <div class="sidebar-progress-track" role="progressbar" aria-label="Practice milestone" aria-valuenow="{{ $sidebarSummary['milestonePercent'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $sidebarSummary['milestonePercent'] }}%"></span></div>
                    <p>{{ $sidebarSummary['completedSets'] }} of 5 starter sets completed</p>
                </div>
                <a class="sidebar-cta" href="{{ route('practice') }}">Continue learning <span>→</span></a>
                <a class="nav-link sidebar-help" href="{{ route('help') }}"><span class="nav-icon"><x-preboard-icon name="question-mark-circle"/></span><span>Help & guidance</span></a>
                @if(auth()->user()->role === 'admin' && auth()->user()->hasVerifiedEmail())
                    <a class="nav-link" href="/stnapanel"><span class="nav-icon"><x-preboard-icon name="adjustments-horizontal"/></span><span>Administration</span></a>
                @endif
                <p class="sidebar-legal">© {{ date('Y') }} Preboard Nepal · <a href="{{ route('policy') }}">Privacy</a></p>
            </div>
        </aside>

        <div class="workspace-main">
            <header class="workspace-header">
                <form action="{{ route('library') }}" class="global-search" role="search"><x-preboard-icon name="magnifying-glass"/><label class="sr-only" for="global-search">Search study resources</label><input id="global-search" name="q" placeholder="Search a chapter, subject, or resource…" value="{{ request('q') }}"><button class="search-submit" type="submit">Search</button></form>
                <a class="profile-link" href="{{ route('account') }}"><span class="profile-name">{{ auth()->user()->name }}<small>Grade {{ auth()->user()->grade }} learner</small></span><span class="avatar">{{ mb_substr(auth()->user()->name,0,1) }}</span></a>
            </header>
            <main id="main" class="workspace-content">@if(session('status'))<div class="alert success" role="status">{{ session('status') }}</div>@endif{{ $slot }}</main>
            <footer class="workspace-footer"><span>Made for your next step.</span><a href="{{ route('policy') }}">Content & privacy</a></footer>
        </div>
    </div>
@else
    <header class="public-header"><div class="public-nav"><x-brand/><nav aria-label="Main navigation"><a href="{{ route('library') }}">Study library</a><a href="{{ route('practice') }}">Practice</a><a href="{{ route('plans') }}">Membership</a></nav><div class="nav-actions"><a class="sign-in" href="{{ route('login') }}">Sign in</a><a class="btn btn-primary" href="{{ route('register') }}">Get started <span>↗</span></a></div></div></header>
    <main id="main">@if(session('status'))<div class="container alert success" role="status">{{ session('status') }}</div>@endif{{ $slot }}</main>
    <footer class="public-footer container"><div><x-brand/><p>A clearer way to study, one chapter at a time.</p></div><div><a href="{{ route('library') }}">Library</a><a href="{{ route('help') }}">Help</a><a href="{{ route('policy') }}">Content & privacy</a><span>© {{ date('Y') }} Preboard Nepal</span></div></footer>
@endauth
@livewireScripts
</body>
</html>
