@extends('layouts.masterlayouts')

@section('content')
<main class="flex-1 pt-20 md:pt-24">
  <section class="py-10 md:py-14">
    <div class="container mx-auto px-4 md:px-6 max-w-3xl">

      <h2 class="text-2xl font-bold mb-6">File: {{ $subtitle->original_name }}</h2>

      <div class="bg-card border border-border rounded-3xl p-6 shadow-elevated space-y-6">

        {{-- Line Info --}}
        <div>
          <h3 class="text-lg font-semibold mb-1">Line #{{ $line->seq }}</h3>
          <p class="text-sm text-muted-foreground mb-2">
            <b>Time:</b> {{ $line->start_time }} → {{ $line->end_time }}
          </p>
          <p class="text-sm font-medium mb-1"><b>Text:</b></p>
          <pre class="whitespace-pre-wrap bg-background rounded-lg p-3 border border-border text-sm">{{ $line->text_original }}</pre>
        </div>

        {{-- AI Error --}}
        @if(!empty($aiError))
          <div class="bg-background border border-border rounded-xl p-4">
            <p class="text-sm text-muted-foreground">{{ $aiError }}</p>
          </div>
        @endif

        @if(is_array($analysis))

          {{-- Description / Meaning --}}
          <div class="space-y-4">

                       <div class="space-y-4">
            
               <div class="bg-background border border-border rounded-xl p-4">
                <p class="text-sm font-medium mb-2"><b>Full Translation (Local / Source Language)</b></p>
                <p class="text-sm text-muted-foreground">
                  {{ $analysis['translation_source'] ?? '—' }}
                </p>
              </div>

              <div class="bg-background border border-border rounded-xl p-4">
                <p class="text-sm font-medium mb-2"><b>Full Translation (Target Language)</b></p>
                <p class="text-sm text-muted-foreground">
                  {{ $analysis['translation_target'] ?? '—' }}
                </p>
              </div>
            </div>



            <p class="text-sm font-medium"><b>Description / Meaning:</b></p>

            @if(!empty($analysis['description_source']) || !empty($analysis['description_english']))
              <div class="bg-background border border-border rounded-xl p-4">
                <p class="text-sm font-medium mb-2"><b>Sentence Description (Source Language)</b></p>
                <p class="text-sm text-muted-foreground">
                  {{ $analysis['description_source'] ?? $analysis['description_english'] ?? '—' }}
                </p>
              </div>
            @endif

            <div class="bg-background border border-border rounded-xl p-4">
              <p class="text-sm font-medium mb-2"><b>Sentence Description (Target Language)</b></p>
              <p class="text-sm text-muted-foreground">
                {{ $analysis['description_target'] ?? '—' }}
              </p>
            </div>

          </div>

          {{-- Grammar --}}
          @if(!empty($analysis['grammar']) && is_array($analysis['grammar']))
            <div class="space-y-3">
              <p class="text-sm font-medium"><b>Grammar</b></p>

              <div class="bg-background border border-border rounded-xl p-4 space-y-2">
                <p class="text-sm">
                  <b>Tense:</b>
                  <span class="text-muted-foreground">{{ $analysis['grammar']['tense'] ?? '—' }}</span>
                </p>
                <p class="text-sm">
                  <b>Structure:</b>
                  <span class="text-muted-foreground">{{ $analysis['grammar']['structure'] ?? '—' }}</span>
                </p>
                <p class="text-sm">
                  <b>Subject:</b>
                  <span class="text-muted-foreground">{{ $analysis['grammar']['subject'] ?? '—' }}</span>
                </p>
                <p class="text-sm">
                  <b>Verb:</b>
                  <span class="text-muted-foreground">{{ $analysis['grammar']['verb'] ?? '—' }}</span>
                </p>
                <p class="text-sm">
                  <b>Object:</b>
                  <span class="text-muted-foreground">{{ $analysis['grammar']['object'] ?? '—' }}</span>
                </p>

                @if(!empty($analysis['grammar']['notes']) && is_array($analysis['grammar']['notes']))
                  <div class="pt-2">
                    <p class="text-sm font-medium mb-2"><b>Grammar Notes</b></p>
                    <ul class="list-disc pl-5 space-y-1">
                      @foreach($analysis['grammar']['notes'] as $note)
                        <li class="text-sm text-muted-foreground">{{ $note }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
              </div>
            </div>
          @endif

          {{-- Word Cards --}}
          <div>
            <p class="text-sm font-medium mb-2"><b>Word Meanings:</b></p>

            <div id="wildcards" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              @foreach($analysis['words'] ?? [] as $word)
                <div class="bg-background border border-border rounded-xl p-4 shadow-sm flex flex-col">
                  <span class="font-semibold text-sm mb-2">{{ $word['raw'] ?? '—' }}</span>

                  <span class="text-sm text-muted-foreground mb-2">
                    {{ $word['meaning_target'] ?? '— meaning —' }}
                  </span>

                  @if(!empty($word['meaning_source']))
                    <span class="text-xs text-muted-foreground mb-2">
                      <b>Source Meaning:</b> {{ $word['meaning_source'] }}
                    </span>
                  @endif

                  <span class="text-xs font-medium mb-1">
                    {{ $word['pos'] ?? '—' }}
                  </span>

                  <span class="text-xs text-muted-foreground">
                    {{ $word['note'] ?? '—' }}
                  </span>
                </div>
              @endforeach
            </div>

            {{-- Generate Button --}}
            <button
              id="btnGenerate"
              type="button"
              class="mt-4 px-4 py-2 rounded-xl bg-primary text-primary-foreground font-semibold hover:opacity-90 transition-opacity"
            >
              Generate Meaning
            </button>
          </div>

        @endif

        {{-- Ask AI --}}
        <div class="space-y-3">
          <p class="text-sm font-medium"><b>Ask about your query with AI</b></p>

          <div id="chatBox" class="bg-background border border-border rounded-xl p-4 space-y-3 max-h-80 overflow-y-auto">
            <div class="text-sm text-muted-foreground" id="chatPlaceholder">
              Ask anything about this sentence, its meaning, grammar, or word usage.
            </div>
          </div>

          <div class="space-y-3">
            <textarea
              id="chatQuestion"
              rows="3"
              class="w-full bg-background border border-border rounded-xl p-3 text-sm outline-none"
              placeholder="Ask something about this sentence..."
            ></textarea>

            <div class="flex gap-3">
              <button
                id="btnAskAi"
                type="button"
                class="px-4 py-2 rounded-xl bg-primary text-primary-foreground font-semibold hover:opacity-90 transition-opacity"
              >
                Ask AI
              </button>
            </div>
          </div>
        </div>

      </div>

      {{-- Navigation Links --}}
      <div class="mt-6 flex w-full text-sm text-primary font-medium">
        @if($prev)
          <a href="{{ route('subtitles.line', [$subtitle->id, $prev]) }}" class="mr-auto">⬅ Prev</a>
        @endif

        @if($next)
          <a href="{{ route('subtitles.line', [$subtitle->id, $next]) }}" class="ml-auto font-semibold text-primary-foreground shadow-soft hover:opacity-90 transition-opacity">Next ➡</a>
        @endif
      </div>

    </div>
  </section>
</main>
<script>
  const btnGenerate = document.getElementById('btnGenerate');
  if (btnGenerate) {
    btnGenerate.addEventListener('click', function () {
      window.location.reload();
    });
  }

  const chatBox = document.getElementById('chatBox');
  const chatQuestion = document.getElementById('chatQuestion');
  const btnAskAi = document.getElementById('btnAskAi');
  const chatPlaceholder = document.getElementById('chatPlaceholder');

  let chatHistory = [];
  let loadingNode = null;

  function appendMessage(role, text) {
    if (chatPlaceholder) {
      chatPlaceholder.style.display = 'none';
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'bg-card border border-border rounded-xl p-3';

    const title = document.createElement('div');
    title.className = 'text-xs font-semibold mb-1';
    title.textContent = role === 'user' ? 'You' : 'AI';

    const body = document.createElement('div');
    body.className = 'text-sm text-muted-foreground whitespace-pre-wrap';
    body.textContent = text;

    wrapper.appendChild(title);
    wrapper.appendChild(body);
    chatBox.appendChild(wrapper);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  function showLoading() {
    if (chatPlaceholder) {
      chatPlaceholder.style.display = 'none';
    }

    loadingNode = document.createElement('div');
    loadingNode.className = 'bg-card border border-border rounded-xl p-3';

    const title = document.createElement('div');
    title.className = 'text-xs font-semibold mb-1';
    title.textContent = 'AI';

    const body = document.createElement('div');
    body.className = 'text-sm text-muted-foreground whitespace-pre-wrap';
    body.textContent = 'AI is typing...';

    loadingNode.appendChild(title);
    loadingNode.appendChild(body);
    chatBox.appendChild(loadingNode);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  function hideLoading() {
    if (loadingNode) {
      loadingNode.remove();
      loadingNode = null;
    }
  }

  async function askAi() {
    const question = chatQuestion.value.trim();
    if (!question) return;

    appendMessage('user', question);
    chatHistory.push({
      role: 'user',
      content: question
    });

    chatQuestion.value = '';
    btnAskAi.disabled = true;
    chatQuestion.disabled = true;
    btnAskAi.textContent = 'Asking...';

    showLoading();

    try {
      const res = await fetch("{{ route('ai.chat') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          sentence: @json($line->text_original),
          targetLanguage: @json($subtitle->target_language ?? 'English'),
          question: question,
          history: chatHistory
        })
      });

      const data = await res.json();

      hideLoading();

      if (!res.ok || data.ok === false) {
        appendMessage('assistant', data.error ?? 'AI is temporarily unavailable.');
        return;
      }

      const payload = data.data ?? {};

      const sourceReply = payload.source_reply ?? '';
      const targetReply = payload.target_reply ?? '';
      const combinedReply =
        (sourceReply || targetReply)
          ? `Source Language:\n${sourceReply || '—'}\n\nTarget Language:\n${targetReply || '—'}`
          : (payload.reply ?? 'No response from AI.');

      appendMessage('assistant', combinedReply);

      chatHistory.push({
        role: 'assistant',
        content: combinedReply
      });
    } catch (error) {
      hideLoading();
      appendMessage('assistant', 'AI is temporarily unavailable.');
    } finally {
      btnAskAi.disabled = false;
      chatQuestion.disabled = false;
      btnAskAi.textContent = 'Ask AI';
      chatQuestion.focus();
    }
  }

  if (btnAskAi) {
    btnAskAi.addEventListener('click', askAi);
  }

  if (chatQuestion) {
    chatQuestion.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        askAi();
      }
    });
  }
</script>
@endsection
