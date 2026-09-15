@php($heading=match($mode){'register'=>'A fresh start, right here.','forgot'=>'Let’s get you back in.','reset'=>'Choose a new password.',default=>'Welcome back.'})
<x-layouts.app :title="$heading">
    <section class="auth-section">
        <p class="eyebrow">YOUR PREBOARD NEPAL ACCOUNT</p>
        <h1>{{ $heading }}</h1>
        <p>{{ $mode==='register'?'Create your student dashboard, save resources, and track practice.':'Your next chapter is waiting for you.' }}</p>
        <form method="post" action="{{ match($mode){'register'=>route('register'),'forgot'=>route('password.email'),'reset'=>route('password.update'),default=>route('login')} }}" class="auth-card">
            @csrf
            @if($mode==='reset')<input type="hidden" name="token" value="{{ $token }}">@endif
            @if($mode==='register')
                <label for="name">Your name</label>
                <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" maxlength="100">
                <label for="grade">Your grade</label>
                <select id="grade" name="grade" required>
                    <option value="">Choose your grade</option>
                    <option value="10" @selected(old('grade')==='10')>Grade 10 / SEE</option>
                    <option value="11" @selected(old('grade')==='11')>Grade 11</option>
                    <option value="12" @selected(old('grade','12')==='12')>Grade 12</option>
                </select>
            @endif
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email',request('email')) }}" required autocomplete="email">
            @if($mode!=='forgot')
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="{{ $mode==='login'?'current-password':'new-password' }}" @if($mode!=='login') minlength="10" @endif>
            @endif
            @if(in_array($mode,['register','reset']))
                <p class="field-hint">Use at least 10 characters, including letters and numbers.</p>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            @endif
            @foreach($errors->all() as $error)<p class="field-error" role="alert">{{ $error }}</p>@endforeach
            @if($mode==='login')<div class="auth-extras"><label class="remember"><input type="checkbox" name="remember" value="1">Remember me</label><a href="{{ route('password.request') }}">Forgot password?</a></div>@endif
            <button class="btn btn-primary w-full">{{ match($mode){'register'=>'Create student dashboard','forgot'=>'Send reset link','reset'=>'Reset password',default=>'Sign in'} }} <span>→</span></button>
        </form>
        <p class="auth-footer">@if($mode==='login')New here? <a href="{{ route('register') }}">Create an account</a>@else<a href="{{ route('login') }}">Back to sign in</a>@endif</p>
        @if($mode==='login' && app()->environment('local') && config('preboard.demo_enabled'))<form action="{{ route('demo') }}" method="post" class="auth-footer">@csrf<button class="text-link">Explore local student demo →</button></form>@endif
    </section>
</x-layouts.app>
