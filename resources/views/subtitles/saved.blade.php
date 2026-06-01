@extends('layouts.masterlayouts')

@section('content')
<main class="flex-1 pt-20 md:pt-24">
  <section class="py-10 md:py-14">
    <div class="container mx-auto px-4 md:px-6 max-w-3xl">

      <h2 class="text-2xl font-bold mb-6">Select your Subtitle</h2>

      @if(session('error'))
        {{ session('error') }}
        @endif
      <form action="{{ route('towardssubss') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Meaning Type Dropdown -->
        <div class="bg-card border border-border rounded-3xl p-4 shadow-elevated space-y-3">
          <label for="meaning_type" class="text-sm font-medium">Choose: </label>
          
          <select name="subtitleid" id="meaning_type" single
                  class="w-full rounded-xl border border-border bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            @foreach ($subtitles as $item)
                <option value="{{ $item->id }}" data-seq="{{ $item->min_seq }}">
                    {{ $item->original_name }} (ID: {{ $item->id }})
                </option>
            @endforeach
          </select>

          <input type="hidden" name="seq[]" id="seqInput">

        </div>

        <!-- Navigation and Save -->
        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2 rounded-xl bg-primary  text-primary-foreground font-semibold font-medium gradient-primary text-primary-foreground shadow-soft hover:opacity-90 transition-opacity">
             Proceed
          </button>
        </div>

      </form>

    </div>
  </section>
</main>

<script>
    // Add hidden inputs for seq when submitting
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Remove old hidden seq inputs if any
        document.querySelectorAll('input[name="seq[]"]').forEach(el => el.remove());

        const select = document.getElementById('meaning_type');
        for (const option of select.selectedOptions) {
            const seq = option.dataset.seq;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'seq[]';
            hidden.value = seq;
            form.appendChild(hidden);
        }
    });
</script>
@endsection
