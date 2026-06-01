


@extends('layouts.masterlayouts')


@section('content')
    
  <main class="flex-1 pt-20 md:pt-24">
    <!-- Hero -->
    <section class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-accent/5 to-purple-500/10"></div>
      <div class="absolute top-20 left-10 w-72 h-72 bg-primary/20 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 right-10 w-72 h-72 bg-accent/20 rounded-full blur-3xl"></div>

      <div class="container mx-auto px-4 md:px-6 py-16 md:py-24 relative">
        <div class="max-w-4xl mx-auto text-center space-y-8">
          <div data-animate class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-medium text-sm border border-primary/20">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
            AI-Powered Subtitle Generation
          </div>

          <div class="space-y-4">
            <h1 data-animate class="text-4xl md:text-6xl font-bold tracking-tight leading-tight">
              Generate <span class="gradient-text">English Subtitles</span><br class="hidden md:block" /> instantly from any video
            </h1>
            <p data-animate class="text-lg md:text-xl text-muted-foreground max-w-2xl mx-auto leading-relaxed">
              Upload your audio or video file and get perfectly timed English subtitles in seconds.
              Powered by advanced AI speech recognition.
            </p>
          </div>

          <div data-animate class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{route('work')}}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity text-lg">
              Start Generating
              <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
            </a>
            <a href="#how-it-works" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 rounded-2xl border border-border bg-card/50 text-foreground font-semibold hover:bg-card transition-colors text-lg">
              Learn More
            </a>
          </div>

          <div data-animate class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-8">
            <div class="bg-card/60 rounded-2xl p-6 border border-border/50 shadow-card">
              <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="zap" class="w-6 h-6 text-primary"></i>
              </div>
              <h3 class="font-semibold text-foreground mb-1">10x Faster</h3>
              <p class="text-sm text-muted-foreground">Generate subtitles in minutes, not hours</p>
            </div>
            <div class="bg-card/60 rounded-2xl p-6 border border-border/50 shadow-card">
              <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="target" class="w-6 h-6 text-accent"></i>
              </div>
              <h3 class="font-semibold text-foreground mb-1">High Accuracy</h3>
              <p class="text-sm text-muted-foreground">AI-powered speech recognition</p>
            </div>
            <div class="bg-card/60 rounded-2xl p-6 border border-border/50 shadow-card">
              <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="download" class="w-6 h-6 text-purple-500"></i>
              </div>
              <h3 class="font-semibold text-foreground mb-1">Multiple Formats</h3>
              <p class="text-sm text-muted-foreground">Download SRT or VTT files</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="py-16 md:py-24 bg-card">
      <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-3xl mx-auto text-center mb-12">
          <h2 data-animate class="text-3xl md:text-4xl font-bold mb-4">How it works</h2>
          <p data-animate class="text-lg text-muted-foreground">Three simple steps to get your subtitles ready</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div data-animate class="text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto">
              <i data-lucide="upload" class="w-8 h-8 text-primary"></i>
            </div>
            <h3 class="text-xl font-semibold">Upload File</h3>
            <p class="text-muted-foreground leading-relaxed">Drag and drop your video or audio file. We support MP4, MP3, WAV and more.</p>
          </div>

          <div data-animate class="text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-accent/10 flex items-center justify-center mx-auto">
              <i data-lucide="wand-2" class="w-8 h-8 text-accent"></i>
            </div>
            <h3 class="text-xl font-semibold">AI Processing</h3>
            <p class="text-muted-foreground leading-relaxed">Our AI analyzes speech and creates accurate English subtitles with timestamps.</p>
          </div>

          <div data-animate class="text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-purple-500/10 flex items-center justify-center mx-auto">
              <i data-lucide="download" class="w-8 h-8 text-purple-500"></i>
            </div>
            <h3 class="text-xl font-semibold">Download</h3>
            <p class="text-muted-foreground leading-relaxed">Get your subtitles in SRT or VTT format and use them anywhere.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="py-16 md:py-24 bg-background">
      <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-3xl mx-auto text-center mb-12">
          <h2 data-animate class="text-3xl md:text-4xl font-bold mb-4">Why choose SubtitleAI?</h2>
          <p data-animate class="text-lg text-muted-foreground">Built for creators, educators, and teams who want speed without sacrificing quality.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div data-animate class="bg-card rounded-2xl p-6 border border-border shadow-card hover:shadow-elevated transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
              <i data-lucide="languages" class="w-6 h-6 text-primary"></i>
            </div>
            <h3 class="font-semibold text-lg mb-2">Language Detection</h3>
            <p class="text-muted-foreground">Auto-detects speech and produces clean English subtitles.</p>
          </div>

          <div data-animate class="bg-card rounded-2xl p-6 border border-border shadow-card hover:shadow-elevated transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center mb-4">
              <i data-lucide="clock" class="w-6 h-6 text-accent"></i>
            </div>
            <h3 class="font-semibold text-lg mb-2">Accurate Timing</h3>
            <p class="text-muted-foreground">Well-spaced timestamps that look great on any player.</p>
          </div>

          <div data-animate class="bg-card rounded-2xl p-6 border border-border shadow-card hover:shadow-elevated transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center mb-4">
              <i data-lucide="shield-check" class="w-6 h-6 text-purple-500"></i>
            </div>
            <h3 class="font-semibold text-lg mb-2">Privacy-first</h3>
            <p class="text-muted-foreground">Designed to plug into your own backend so you control storage.</p>
          </div>

          <div data-animate class="bg-card rounded-2xl p-6 border border-border shadow-card hover:shadow-elevated transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-4">
              <i data-lucide="sparkles" class="w-6 h-6 text-primary"></i>
            </div>
            <h3 class="font-semibold text-lg mb-2">Clean UI</h3>
            <p class="text-muted-foreground">A modern, mobile-first interface that feels premium.</p>
          </div>

          <div data-animate class="bg-card rounded-2xl p-6 border border-border shadow-card hover:shadow-elevated transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-accent/10 flex items-center justify-center mb-4">
              <i data-lucide="file-output" class="w-6 h-6 text-accent"></i>
            </div>
            <h3 class="font-semibold text-lg mb-2">SRT + VTT</h3>
            <p class="text-muted-foreground">Download your subtitles in the format you need.</p>
          </div>

          <div data-animate class="bg-card rounded-2xl p-6 border border-border shadow-card hover:shadow-elevated transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center mb-4">
              <i data-lucide="settings" class="w-6 h-6 text-purple-500"></i>
            </div>
            <h3 class="font-semibold text-lg mb-2">Easy Laravel hookup</h3>
            <p class="text-muted-foreground">Frontend is ready—connect to Laravel APIs when you’re ready.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-16 md:py-24 bg-card">
      <div class="container mx-auto px-4 md:px-6">
        <div data-animate class="max-w-4xl mx-auto rounded-3xl p-10 md:p-14 border border-border shadow-elevated relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-primary/10 via-accent/5 to-purple-500/10"></div>
          <div class="relative text-center space-y-6">
            <h2 class="text-3xl md:text-4xl font-bold">Ready to subtitle your next video?</h2>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
              Upload a file, pick an output format, and download your English subtitles. Simple.
            </p>
            <a href="{{ route('work') }}" class="inline-flex items-center justify-center px-8 py-4 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity text-lg">
              Try SubtitleAI Free
              <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
            </a>
          </div>
        </div>
      </div>
    </section>
  </main>

@endsection
