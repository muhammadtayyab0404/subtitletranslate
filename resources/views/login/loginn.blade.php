


@extends('layouts.masterlayouts')


@section('content')
    
  <main class="flex-1 pt-20 md:pt-24">
    <section class="py-12 md:py-16">
      <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-md mx-auto">
          <div data-animate class="bg-card border border-border rounded-3xl p-6 md:p-8 shadow-elevated">
            <div class="text-center space-y-2 mb-8">
              <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center mx-auto">
                <i data-lucide="log-in" class="w-7 h-7 text-primary-foreground"></i>
              </div>
              <h1 class="text-2xl font-bold">Welcome back</h1>
              <p class="text-muted-foreground">Log in to continue</p>
            </div>

            <form action="{{ route('loginMatch') }}" method="POST" id="loginForm" class="space-y-5">
                  @csrf
              <div class="space-y-2">
                <label class="text-sm font-medium">Email</label>
                <input id="loginEmail" type="email" name="email" placeholder="you@example.com" class="w-full rounded-2xl border border-border bg-background px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary" />
                <p id="loginEmailErr" class="hidden text-sm text-destructive"></p>
                          
                
                @error('email')
     <h1>{{$message}}</h1>
            @enderror
            </div>

              <div class="space-y-2">
                <label class="text-sm font-medium">Password</label>
                <div class="relative">
                  <input id="loginPassword" name="password" type="password" placeholder="••••••••"
                    class="w-full rounded-2xl border border-border bg-background px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-primary" />
                  <button id="loginTogglePass" type="button" class="absolute inset-y-0 right-0 px-4 text-muted-foreground hover:text-foreground">
                    <i id="loginEye" data-lucide="eye" class="w-5 h-5"></i>
                  </button>
                </div>
                <p id="loginPassErr" class="hidden text-sm text-destructive"></p>
            
             @error('password')
     <h1>{{$message}}</h1>

     @enderror
            
            </div>

              <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2 text-muted-foreground select-none">
                  <input type="checkbox" class="rounded border-border text-primary focus:ring-primary" />
                  Remember me
                </label>
                <a href="{{route('forget')}}" class="text-primary hover:underline">Forgot password?</a>
              </div>

              <button id="loginSubmit" type="submit" class="w-full h-12 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity">
                Log In
              </button>

              <p class="text-sm text-muted-foreground text-center">
                Don’t have an account?
                <a href="{{route('signup')}}" class="text-primary font-medium hover:underline">Sign up</a>
              </p>
            </form>
          </div>

          <div data-animate class="mt-6 text-center text-xs text-muted-foreground">
            Connect this form to your Laravel auth endpoints (Sanctum/JWT/session) when ready.
          </div>
        </div>
      </div>
    </section>
  </main>

      
  @endsection
 \