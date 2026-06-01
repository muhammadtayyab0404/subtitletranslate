@extends('layouts.masterlayouts')



@section('content')
  <main class="flex-1 pt-20 md:pt-24">
    <section class="py-10 md:py-14">
      <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-3xl mx-auto space-y-8">
          <div class="text-center space-y-3">
            <h1 data-animate class="text-3xl md:text-4xl font-bold tracking-tight">
              Generate English Subtitles
            </h1>
            <p data-animate class="text-muted-foreground text-lg">
              Upload a video or audio file and download English subtitles in SRT or VTT.
            </p>
          </div>

          <!-- Main card -->
          <div data-animate class="bg-card border border-border rounded-3xl p-6 md:p-8 shadow-elevated">
            <!-- Input area (hidden when completed) -->
            <div id="inputArea" class="space-y-6">
              <!-- Dropzone wrapper -->
              <form action="{{ route('subtitles.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
              <div id="dropzoneWrap" class="space-y-4">
                <div id="dropzone" class="relative">
                  
                  <label class="block border-2 border-dashed border-border rounded-2xl p-8 md:p-12 text-center transition-all duration-300 hover:border-primary/50 hover:bg-secondary/50 cursor-pointer">
                    <input id="fileInput" name="srtfile" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                    <div class="flex flex-col items-center gap-4">
                      <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                        <i data-lucide="upload" class="w-8 h-8 text-primary"></i>
                      </div>
                      <div class="space-y-2">
                        <p id="dragHint" class="text-lg font-medium">Drag & drop your file</p>
                        <p class="text-muted-foreground">or <span class="text-primary font-medium">browse</span> to choose a file</p>
                      </div>
                      <div id="formatsWrap" class="flex flex-wrap justify-center gap-2"></div>
                      <p id="maxSizeText" class="text-xs text-muted-foreground"></p>
                    </div>
                  </label>
                </div>

                <!-- Selected file card -->
                <div id="selectedCard" class="hidden border border-border rounded-2xl p-4 md:p-6 bg-card shadow-card">
                  <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                      <i id="selectedIcon" data-lucide="file" class="w-8 h-8 text-primary"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p id="selectedName" class="font-medium text-foreground truncate"></p>
                      <p id="selectedSize" class="text-sm text-muted-foreground"></p>
                    </div>
                    <button id="removeFileBtn" class="flex-shrink-0 p-2 rounded-xl hover:bg-destructive/10 hover:text-destructive transition-colors" aria-label="Remove file">
                      <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                  </div>
                </div>

                <p id="dropError" class="hidden text-sm text-destructive text-center"></p>
              </div>
           

              {{-- <!-- Options -->
              <div id="optionsWrap" class="hidden space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="text-sm font-medium">Input language</label>
                    <select id="languageSelect" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                      <option value="auto">Auto-detect</option>
                      <option value="en">English</option>
                      <option value="ur">Urdu</option>
                      <option value="hi">Hindi</option>
                      <option value="ar">Arabic</option>
                      <option value="tr">Turkish</option>
                    </select>
                    <p class="text-xs text-muted-foreground">Choose “Auto” for best results.</p>
                  </div>

                  <div class="space-y-2">
                    <label class="text-sm font-medium">Output format</label>
                    <select id="formatSelect" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                      <option value="srt">SRT (.srt)</option>
                      <option value="vtt">VTT (.vtt)</option>
                    </select>
                    <p class="text-xs text-muted-foreground">SRT works everywhere, VTT is great for the web.</p>
                  </div>
                </div>
              </div> --}}

              {{-- <!-- Progress -->
              <div id="progressWrap" class="hidden space-y-4">
                <div class="flex items-center justify-center gap-2">
                  <span id="progressSpinner" class="hidden inline-flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                  </span>
                  <i id="progressIcon" data-lucide="upload" class="w-5 h-5"></i>
                  <span id="progressLabel" class="font-medium"></span>
                </div>

                <div class="relative w-full h-2 bg-secondary rounded-full overflow-hidden">
                  <div class="absolute inset-y-0 left-0 gradient-primary rounded-full transition-[width] duration-300 ease-out" id="progressBar" style="width:0%"></div>
                  <div class="absolute inset-y-0 w-1/4 bg-gradient-to-r from-transparent via-white/30 to-transparent shimmer"></div>
                </div>

                <p id="progressPercent" class="text-center text-sm text-muted-foreground">0% complete</p>
                <p id="progressErr" class="hidden text-center text-sm text-destructive"></p>
              </div> --}}

              <!-- Actions -->

<!-- Target Language Select -->
          <div class="space-y-2 mb-4">
            <label for="targetLanguage" class="text-sm font-medium">Target Language</label>
            <select name="target_language" id="targetLanguage" class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
              <option value="" selected disabled>Select target language</option>
              <option value="en">English</option>
              <option value="ar">Arabic</option>
              <option value="bn">Bengali</option>
              <option value="zh">Chinese (Simplified)</option>
              <option value="zh-TW">Chinese (Traditional)</option>
              <option value="cs">Czech</option>
              <option value="da">Danish</option>
              <option value="nl">Dutch</option>
              <option value="fi">Finnish</option>
              <option value="fr">French</option>
              <option value="de">German</option>
              <option value="el">Greek</option>
              <option value="he">Hebrew</option>
              <option value="hi">Hindi</option>
              <option value="hu">Hungarian</option>
              <option value="id">Indonesian</option>
              <option value="it">Italian</option>
              <option value="ja">Japanese</option>
              <option value="ko">Korean</option>
              <option value="ms">Malay</option>
              <option value="no">Norwegian</option>
              <option value="fa">Persian</option>
              <option value="pl">Polish</option>
              <option value="pt">Portuguese</option>
              <option value="ro">Romanian</option>
              <option value="ru">Russian</option>
              <option value="sr">Serbian</option>
              <option value="sk">Slovak</option>
              <option value="es">Spanish</option>
              <option value="sv">Swedish</option>
              <option value="ta">Tamil</option>
              <option value="th">Thai</option>
              <option value="tr">Turkish</option>
              <option value="uk">Ukrainian</option>
              <option value="ur">Urdu</option>
              <option value="vi">Vietnamese</option>
            </select>
            <p class="text-xs text-muted-foreground">Select the language you want your subtitles to be translated into.</p>
          </div>




              <div class="space-y-3">
                <button id="generateBtn" type="submit" class="w-full h-12 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed">
                  <span class="inline-flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-5 h-5 mr-2"></i>
                    Generate Subtitles
                  </span>
                </button>

               </form>

            @error('srtfile')
              <p class="text-red-500 text-sm text-center mt-4">
                {{ $message }}
              </p>
            @enderror

                <p class="text-xs text-muted-foreground text-center">
                  Tip: For best accuracy, upload clear audio with minimal background noise.
                </p>
              </div>
            </div>

            <!-- Results -->
            <div id="resultsWrap" class="hidden space-y-6">
              <div class="flex items-center justify-center gap-2 py-4">
                <div class="w-12 h-12 rounded-full bg-accent/20 flex items-center justify-center">
                  <i data-lucide="check" class="w-6 h-6 text-accent"></i>
                </div>
              </div>

              <button id="downloadPrimaryBtn" class="w-full h-12 rounded-2xl gradient-primary text-primary-foreground font-semibold shadow-soft hover:opacity-90 transition-opacity">
                <span class="inline-flex items-center justify-center">
                  <i data-lucide="download" class="w-5 h-5 mr-2"></i>
                  <span id="downloadPrimaryLabel">Download English Subtitles (.SRT)</span>
                </span>
              </button>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <button id="downloadAltBtn" class="h-11 rounded-2xl border border-border bg-card hover:bg-secondary transition-colors font-medium">
                  <span class="inline-flex items-center justify-center">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                    <span id="altLabel">Other</span>
                  </span>
                </button>

                <button id="copyBtn" class="h-11 rounded-2xl border border-border bg-card hover:bg-secondary transition-colors font-medium">
                  <span class="inline-flex items-center justify-center">
                    <i id="copyIcon" data-lucide="copy" class="w-4 h-4 mr-2"></i>
                    <span id="copyLabel">Copy Text</span>
                  </span>
                </button>

                <button id="downloadTxtBtn" class="h-11 rounded-2xl border border-border bg-card hover:bg-secondary transition-colors font-medium">
                  <span class="inline-flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                    TXT
                  </span>
                </button>
              </div>

              <button id="resetBtn" class="w-full rounded-2xl py-3 text-muted-foreground hover:text-foreground hover:bg-secondary/50 transition-colors font-medium">
                <span class="inline-flex items-center justify-center">
                  <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                  Upload another file
                </span>
              </button>
            </div>
          </div>

          <!-- Preview cards -->
          <div id="previewWrap" class="hidden space-y-4" data-animate>
            <h3 class="text-lg font-semibold text-foreground text-center">Subtitle Preview</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-card rounded-2xl p-5 shadow-card border border-border hover:shadow-elevated transition-shadow">
                <div class="flex items-center gap-2 mb-4">
                  <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i data-lucide="flag" class="w-4 h-4 text-primary"></i>
                  </div>
                  <h4 class="font-semibold text-foreground">Start</h4>
                </div>
                <div id="previewStart" class="space-y-2 min-h-[80px]"></div>
              </div>

              <div class="bg-card rounded-2xl p-5 shadow-card border border-border hover:shadow-elevated transition-shadow">
                <div class="flex items-center gap-2 mb-4">
                  <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4 text-primary"></i>
                  </div>
                  <h4 class="font-semibold text-foreground">Middle</h4>
                </div>
                <div id="previewMid" class="space-y-2 min-h-[80px]"></div>
              </div>

              <div class="bg-card rounded-2xl p-5 shadow-card border border-border hover:shadow-elevated transition-shadow">
                <div class="flex items-center gap-2 mb-4">
                  <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-4 h-4 text-primary"></i>
                  </div>
                  <h4 class="font-semibold text-foreground">End</h4>
                </div>
                <div id="previewEnd" class="space-y-2 min-h-[80px]"></div>
              </div>
            </div>
          </div>

          <!-- Small FAQ -->
          <div class="bg-card border border-border rounded-3xl p-6 md:p-8 shadow-card" data-animate>
            <h3 class="text-xl font-bold mb-3">Quick notes</h3>
            <ul class="space-y-2 text-muted-foreground leading-relaxed">
              <li class="flex gap-2"><i data-lucide="check-circle-2" class="w-5 h-5 text-accent flex-shrink-0"></i><span>Supported formats: MP3, WAV, MP4, MOV, WEBM, M4A, OGG</span></li>
              <li class="flex gap-2"><i data-lucide="check-circle-2" class="w-5 h-5 text-accent flex-shrink-0"></i><span>Maximum size: 500MB (you can enforce server-side too)</span></li>
              <li class="flex gap-2"><i data-lucide="check-circle-2" class="w-5 h-5 text-accent flex-shrink-0"></i><span>When you connect Laravel, replace the mock API functions inside <code class="px-1 rounded bg-secondary text-foreground">assets/app.js</code></span></li>
            </ul>
          </div>

        </div>
      </div>
    </section>
  </main>


@endsection

</html>