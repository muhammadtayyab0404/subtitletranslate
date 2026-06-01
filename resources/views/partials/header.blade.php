<header class="fixed top-0 left-0 right-0 z-50 glass border-b border-border/50" data-animate>
  <nav class="container mx-auto px-4 md:px-6">
    <div class="flex items-center justify-between h-16 md:h-18">
      
      <!-- Logo -->
      <a href="/" class="flex items-center gap-2 group">
        <div class="w-8 h-8 rounded-lg gradient-primary flex items-center justify-center transition-transform duration-500 group-hover:rotate-180">
          <i data-lucide="sparkles" class="w-5 h-5 text-primary-foreground"></i>
        </div>
        <span class="font-bold text-lg text-foreground">SubtitleAI</span>
      </a>

      <!-- Desktop Navigation -->
      <div class="hidden md:flex items-center gap-2">
        <a data-nav-link href="/" 
           class="relative px-3 py-2 rounded-lg text-sm transition-all duration-300 
           {{ request()->routeIs('homell') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white scale-105' : 'hover:bg-secondary hover:text-foreground hover:scale-105' }}">
          Home
          <span class="absolute bottom-0 left-0 h-0.5 bg-blue-500 transition-all duration-300 {{ request()->routeIs('homell') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
        </a>
        <a data-nav-link href="{{ route('work') }}" 
           class="relative px-3 py-2 rounded-lg text-sm transition-all duration-300 
           {{ request()->routeIs('work') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white scale-105' : 'hover:bg-secondary hover:text-foreground hover:scale-105' }}">
          Working
          <span class="absolute bottom-0 left-0 h-0.5 bg-blue-500 transition-all duration-300 {{ request()->routeIs('work') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
        </a>


       @guest

        <a data-nav-link href="{{ route('signup') }}" 
           class="relative px-3 py-2 rounded-lg text-sm transition-all duration-300 
           {{ request()->routeIs('signup') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white scale-105' : 'hover:bg-secondary hover:text-foreground hover:scale-105' }}">
          Signup
          <span class="absolute bottom-0 left-0 h-0.5 bg-blue-500 transition-all duration-300 {{ request()->routeIs('signup') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
        </a>
        <a data-nav-link href="{{ route('loginn') }}" 
           class="relative px-3 py-2 rounded-lg text-sm transition-all duration-300 
           {{ request()->routeIs('loginn') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white scale-105' : 'hover:bg-secondary hover:text-foreground hover:scale-105' }}">
          Login
          <span class="absolute bottom-0 left-0 h-0.5 bg-blue-500 transition-all duration-300 {{ request()->routeIs('loginn') ? 'w-full' : 'w-0 group-hover:w-full' }}"></span>
        </a>
    @endguest
          @auth

                 <a data-nav-link href="{{ route('saved') }}" class="relative px-3 py-2 rounded-lg text-sm transition-all duration-300 
           {{ request()->routeIs('saved') ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white scale-105' : 'hover:bg-secondary hover:text-foreground hover:scale-105' }}">Saved</a>
       <a data-nav-link href="{{ route('logout') }}" class="block py-3 px-4 rounded-lg  transition-colors hover:bg-secondary hover:text-foreground">Logout</a>

     @endauth     
     
      </div>

      <!-- CTA Button -->
      <div class="hidden md:block">
        <a href="{{ route('work') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-medium gradient-primary text-primary-foreground shadow-soft hover:opacity-90 transition-opacity">
          Try Now
        </a>
      </div>

      <!-- Mobile Menu Button -->
      <button id="mobileMenuBtn" data-open="0" aria-expanded="false" class="md:hidden p-2 rounded-lg hover:bg-secondary transition-colors" aria-label="Toggle menu">
        <i id="iconMenu" data-lucide="menu" class="w-6 h-6 text-foreground transition-transform duration-300"></i>
        <i id="iconClose" data-lucide="x" class="w-6 h-6 text-foreground hidden transition-transform duration-300"></i>
      </button>
    </div>
  </nav>

  <!-- Mobile Menu -->
  <div id="mobileMenuPanel" class="md:hidden glass border-t border-border/50 hidden">
    <div class="container mx-auto px-4 py-4 space-y-2">

      <a data-nav-link  href="/" class="block py-3 px-4 rounded-lg transition-colors hover:bg-secondary hover:text-foreground">Home</a>
      <a data-nav-link href="{{ route('work') }}" class="block py-3 px-4 rounded-lg transition-colors hover:bg-secondary hover:text-foreground">Working</a>
      @guest
      <a data-nav-link href="{{ route('signup') }}" class="block py-3 px-4 rounded-lg transition-colors hover:bg-secondary hover:text-foreground">Signup</a>
      <a data-nav-link href="{{ route('loginn') }}" class="block py-3 px-4 rounded-lg transition-colors hover:bg-secondary hover:text-foreground">Login</a>
      @endguest


    @auth
 
    <div class="pt-2 w-full">
    <a data-nav-link href="{{ route('saved') }}" class="block py-3 px-4 rounded-lg transition-colors hover:bg-secondary hover:text-foreground">Saved</a>
    <a data-nav-link href="{{ route('logout') }}" class="block py-3 px-4 rounded-lg transition-colors hover:bg-secondary hover:text-foreground">Logout</a>
    </div>


    @endauth

      <div class="pt-2">
        <a href="{{route('work')}}" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-medium gradient-primary text-primary-foreground shadow-soft">
          Try Now
        </a>
      </div>
    </div>
  </div>

  <script>
    const btn = document.getElementById('mobileMenuBtn');
    const panel = document.getElementById('mobileMenuPanel');

    btn.addEventListener('click', () => {
      panel.classList.toggle('hidden');
      btn.querySelector('#iconMenu').classList.toggle('hidden');
      btn.querySelector('#iconClose').classList.toggle('hidden');
    });
  </script>
</header>
