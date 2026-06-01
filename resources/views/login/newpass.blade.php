@extends('layouts.masterlayouts')

@section('content')

<main class="flex-1 pt-20 md:pt-24">
    <section class="py-12 md:py-16">
        <div class="container mx-auto px-4 md:px-6">
            <div class="max-w-md mx-auto">
                <div data-animate class="bg-card border border-border rounded-3xl p-6 md:p-8 shadow-elevated">
                    <div class="text-center space-y-2 mb-8">
                        <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center mx-auto">
                            <i data-lucide="lock" class="w-7 h-7 text-primary-foreground"></i>
                        </div>
                        <h1 class="text-2xl font-bold">Reset Password</h1>
                        <p class="text-muted-foreground">Enter the OTP sent to your email and choose a new password</p>
                    </div>

                    <form action="{{ route('newpassword') }}" method="POST" id="forgotOtpForm" class="space-y-5">
                        @csrf

                        <!-- New Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">New Password</label>
                            <input type="password" name="newpassword" placeholder="••••••••"
                                class="w-full rounded-2xl border border-border bg-background px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary" required>
                            <p class="text-xs text-muted-foreground">Use at least 8 characters.</p>
                        </div>

                        <input type="hidden" name="email" value="{{ session('email') }}">
                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Confirm Password</label>
                            <input type="password" name="newpassword_confirmation" placeholder="••••••••"
                                class="w-full rounded-2xl border border-border bg-background px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="w-full h-12 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity">
                            Reset Password
                        </button>
                    </form>
            @if ($errors->any())
                <ul>
                    @foreach ($errors->all() as $error)
                        <li class="text-red-500">{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
                </div>

            </div>

        </div>

    </section>
</main>

@endsection
