@extends('layouts.masterlayouts')

@section('content')

<main class="flex-1 pt-20 md:pt-24">
    <section class="py-12 md:py-16">
        <div class="container mx-auto px-4 md:px-6">
            <div class="max-w-md mx-auto">
                <div data-animate class="bg-card border border-border rounded-3xl p-6 md:p-8 shadow-elevated">
                    <div class="text-center space-y-2 mb-8">
                        <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center mx-auto">
                            <i data-lucide="mail" class="w-7 h-7 text-primary-foreground"></i>
                        </div>
                        <h1 class="text-2xl font-bold">Forgot Password</h1>
                        <p class="text-muted-foreground">Enter your registered email to receive OTP</p>
                    </div>

                    <form action="{{ route('forgetotp') }}" method="POST" id="forgotEmailForm" class="space-y-5">
                        @csrf

                        <!-- Email -->
                        <div class="space-y-2">
                            
                            <label class="text-sm font-medium">Email</label>
                            <input type="email" name="email" placeholder="you@example.com"
                                class="w-full rounded-2xl border border-border bg-background px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        
                            <input type="hidden" name="flow" value="forgot">
                            </div>

                        <!-- Submit -->
                        <button type="submit" class="w-full h-12 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity">
                            Send OTP
                        </button>

                        <p class="text-sm text-muted-foreground text-center">
                            Remember your password? 
                            <a href="{{ route('loginn') }}" class="text-primary font-medium hover:underline">Login</a>
                        </p>
                    </form>

                     @if ($errors->any())
                        <div class="mt-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </section>
</main>

@endsection
