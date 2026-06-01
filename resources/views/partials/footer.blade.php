<footer class="border-t border-border bg-card" data-animate>
  <div class="container mx-auto px-4 md:px-6 py-10 md:py-16">
    <div class="flex flex-col md:flex-row items-center justify-between gap-8 md:gap-4">

      <!-- Logo + Branding -->
      <a href="/" class="flex items-center gap-3 group transition-transform duration-300 hover:scale-105">
        <div class="w-10 h-10 rounded-lg gradient-primary flex items-center justify-center transition-transform duration-500 group-hover:rotate-12">
          <i data-lucide="sparkles" class="w-6 h-6 text-primary-foreground"></i>
        </div>
        <span class="font-bold text-xl text-foreground">SubtitleAI</span>
      </a>

      <!-- Footer Navigation Links -->
      <div class="flex flex-wrap items-center gap-6 text-sm justify-center md:justify-start">
        <a href="#" class="text-muted-foreground hover:text-white hover:underline transition-colors duration-300">Privacy</a>
        <a href="#" class="text-muted-foreground hover:text-white hover:underline transition-colors duration-300">Terms</a>
        <a href="#" class="text-muted-foreground hover:text-white hover:underline transition-colors duration-300">Contact</a>
        <a href="#" class="text-muted-foreground hover:text-white hover:underline transition-colors duration-300">Support</a>
      </div>

      <!-- Social Icons -->
      <div class="flex items-center gap-4">
        <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-muted hover:bg-primary transition-colors duration-300">
          <i data-lucide="twitter" class="w-4 h-4 text-white"></i>
        </a>
        <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-muted hover:bg-primary transition-colors duration-300">
          <i data-lucide="linkedin" class="w-4 h-4 text-white"></i>
        </a>
        <a href="#" class="w-8 h-8 flex items-center justify-center rounded-full bg-muted hover:bg-primary transition-colors duration-300">
          <i data-lucide="github" class="w-4 h-4 text-white"></i>
        </a>
      </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="mt-8 text-center text-sm text-muted-foreground border-t border-border/50 pt-4">
      © <span id="year"></span> SubtitleAI. All rights reserved.
    </div>
  </div>

  <!-- Dynamic Year Script -->
  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</footer>
