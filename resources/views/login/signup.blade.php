@extends('layouts.masterlayouts')

@section('content')

<main class="flex-1 pt-20 md:pt-24">
    <section class="py-12 md:py-16">
        <div class="container mx-auto px-4 md:px-6">
            <div class="max-w-md mx-auto">
                <div data-animate class="bg-card border border-border rounded-3xl p-6 md:p-8 shadow-elevated">
                    <div class="text-center space-y-2 mb-8">
                        <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center mx-auto">
                            <i data-lucide="user-plus" class="w-7 h-7 text-primary-foreground"></i>
                        </div>
                        <h1 class="text-2xl font-bold">Create your account</h1>
                        <p class="text-muted-foreground">Start generating subtitles in minutes</p>
                    </div>

                    <form action="{{ route('registerSave') }}" method="POST" id="signupForm" class="space-y-5">
                        @csrf

                        <!-- Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Full Name</label>
                            <input type="text" name="name" placeholder="Your name"
                                class="w-full rounded-2xl border border-border bg-background px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary"
                                value="{{ old('name') }}">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Email</label>
                            <input type="email" name="email" placeholder="you@example.com"
                                class="w-full rounded-2xl border border-border bg-background px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary"
                                value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Password</label>
                            <div class="relative">
                                <input type="password" name="password" placeholder="••••••••"
                                    class="w-full rounded-2xl border border-border bg-background px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-primary" />
                                <button type="button" class="absolute inset-y-0 right-0 px-4 text-muted-foreground hover:text-foreground">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted-foreground">Use at least 8 characters.</p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Confirm Password</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" placeholder="••••••••"
                                    class="w-full rounded-2xl border border-border bg-background px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-primary" />
                                <button type="button" class="absolute inset-y-0 right-0 px-4 text-muted-foreground hover:text-foreground">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms -->
                        <label class="inline-flex items-start gap-2 text-sm text-muted-foreground">
                            <input type="checkbox" name="agree" class="mt-1 rounded border-border text-primary focus:ring-primary" />
                            <span>I agree to the <a href="#" class="text-primary hover:underline">Terms</a> and <a href="#" class="text-primary hover:underline">Privacy Policy</a>.</span>
                        </label>

                        <!-- Submit -->
                        <button type="submit" class="w-full h-12 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity">
                            Create Account
                        </button>

                        <p class="text-sm text-muted-foreground text-center">
                            Already have an account?
                            <a href="{{ route('loginn') }}" class="text-primary font-medium hover:underline">Log in</a>
                        </p>
                    </form>

                    <!-- Global errors (e.g., duplicate email) -->
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

                <div data-animate class="mt-6 text-center text-xs text-muted-foreground">
                    Note: This is frontend-only. Hook signup/login to Laravel later.
                </div>
            </div>
        </div>
    </section>
</main>

@endsection
