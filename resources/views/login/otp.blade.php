@extends('layouts.masterlayouts')

@section('content')

<main class="flex-1 pt-20 md:pt-24">
  <section class="py-12 md:py-16">
    <div class="container mx-auto px-4 md:px-6">
      <div class="max-w-md mx-auto">

        <div data-animate
          class="bg-card border border-border rounded-3xl p-6 md:p-8 shadow-elevated
                 animate-[fadeIn_0.6s_ease-out]">

          <!-- Header -->
          <div class="text-center space-y-2 mb-8">
            <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center mx-auto">
              <i data-lucide="shield-check" class="w-7 h-7 text-primary-foreground"></i>
            </div>
            <h1 class="text-2xl font-bold">Verify OTP</h1>
            <p class="text-muted-foreground">
              Enter the 4-digit code sent to your email
            </p>
          </div>

          <!-- Error from session -->
          @if(session('error'))
            <p class="text-sm text-destructive text-center mb-4">
              {{ session('error') }}
            </p>
          @endif

          <!-- OTP FORM -->
          <form action="{{ route('verifyotp') }}" method="POST" class="space-y-6">
            @csrf

            <!-- IMPORTANT: email hidden field -->
            <input type="hidden" name="email"
                   value="{{ old('email', session('email')) }}">

           <input type="hidden" name="flow"value="{{  session('flow') ?? 10}}">


            <!-- OTP input (single field – your logic preserved) -->
            <div class="flex justify-center">
              <input
                type="text"
                name="otp"
                maxlength="4"
                inputmode="numeric"
                placeholder="••••"
                class="w-32 h-14 text-center text-xl font-semibold
                       rounded-xl border border-border bg-background
                       focus:outline-none focus:ring-2 focus:ring-primary
                       tracking-widest transition-all"
              >
            </div>

            @error('otp')
              <p class="text-sm text-red-500  text-destructive text-center">{{ $message }}</p>
            @enderror

            <!-- Verify Button -->
            <button type="submit"
              class="w-full h-12 rounded-2xl gradient-primary
                     text-primary-foreground font-semibold
                     shadow-soft hover:opacity-90 transition-opacity">
              Verify OTP
            </button>

            <!-- Resend -->
            <p class="text-sm text-muted-foreground text-center">
              Didn’t receive code?
              <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">
                Resend OTP
              </a>
            </p>
          </form>
        </div>

        <div data-animate class="mt-6 text-center text-xs text-muted-foreground">
          OTP expires in 5 minutes
        </div>

      </div>
    </div>
  </section>
</main>

@endsection
