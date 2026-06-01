<?php

namespace App\Http\Controllers;

use App\Models\Subtitle;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Student;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendWelcomeEmail;
use App\Services\GeminiService;
use App\Services\ModelApi;


use App\Events\userRegistered;

class WorkController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'srtfile' => ['required','file','max:2048'],
            'target_language' =>['required'],
        ]);


        $targetlanguage =$request->target_language;
    

        $userId = auth()->id();
        if (!$userId) {
            return redirect()->route('loginn');
        }

        $file = $request->file('srtfile');

        // Only .srt
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'srt') {
            return back()->withErrors(['srtfile' => 'Only .srt files are allowed.']);
        }

        $originalName = $file->getClientOriginalName();
        $clean = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $storedName = $clean . '_' . time() . '.' . $ext;

        $path = $file->storeAs('originalsrt', $storedName);

        // Read file
        $fullPath = Storage::path($path);
        $content = file_get_contents($fullPath);

        $cues = $this->parseSrt($content);

        if (count($cues) === 0) {
            return back()->withErrors(['srtfile' => 'SRT file parsing failed or file is empty.']);
        }

        // Save to DB in a transaction
        $subtitle = DB::transaction(function () use ($originalName, $storedName, $path, $cues,$targetlanguage) {

            $subtitle = Subtitle::create([
                'user_id' => Auth::id(),
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'path' => $path,
                'status' => 'parsed',
                'target_language' => $targetlanguage,
                'total_lines' => count($cues),
            ]);

            $rows = [];
            foreach ($cues as $cue) {
                $rows[] = [
                    'subtitle_id' => $subtitle->id,
                    'seq' => $cue['seq'],
                    'start_time' => $cue['start'],
                    'end_time' => $cue['end'],
                    'text_original' => $cue['text'],
                    'ai_description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // bulk insert
            $subtitle->lines()->insert($rows);

            return $subtitle;
        });

        // Redirect to first line
        $firstSeq = $subtitle->lines()->min('seq');

        return redirect()->route('subtitles.line', [$subtitle->id, $firstSeq]);

    }

   public function line(Subtitle $subtitle, int $seq)
{
    // Security: user apni hi file dekhe
    abort_unless($subtitle->user_id === Auth::id(), 403);

    $line = $subtitle->lines()->where('seq', $seq)->firstOrFail();

    $prev = $subtitle->lines()->where('seq', '<', $seq)->max('seq');
    $next = $subtitle->lines()->where('seq', '>', $seq)->min('seq');

    $analysis = null;
    $aiError = null;

    try {
        $analysis = app(\App\Services\ModelApi::class)->analyzeSentence(
            $line->text_original,
            $subtitle->target_language ?? 'English'
        );
    } catch (\Throwable $e) {
        report($e); // logs error
        $aiError = 'AI analysis is temporarily unavailable.';
    }

    return view('subtitles.line', compact(
        'subtitle',
        'line',
        'prev',
        'next',
        'analysis',
        'aiError'
    ));
}

    private function parseSrt(string $srt): array
    {
        $srt = str_replace("\r\n", "\n", trim($srt));
        $blocks = preg_split("/\n{2,}/", $srt);

        $out = [];
        foreach ($blocks as $block) {
            $lines = array_values(array_filter(explode("\n", trim($block)), fn($l) => $l !== ''));
            if (count($lines) < 3) continue;

            $seq = (int) trim($lines[0]);
            if (!str_contains($lines[1], '-->')) continue;

            [$start, $end] = array_map('trim', explode('-->', $lines[1], 2));
            $text = trim(implode("\n", array_slice($lines, 2)));

            $out[] = ['seq'=>$seq,'start'=>$start,'end'=>$end,'text'=>$text];
        }

        usort($out, fn($a, $b) => $a['seq'] <=> $b['seq']);
        return $out;
    }

    public function fatchsubtitle(){

        $subtitles=Subtitle::where('user_id',Auth::id())
        ->with('lines')->get();
        
        foreach ($subtitles as $value) {
            $value->min_seq = $value->lines->min('seq');
        }
        
        return view('subtitles/saved',['subtitles'=>$subtitles]);

    
    }

   public function towardssub(Request $request)
{
    $subtitleid = $request->input('subtitleid');
    $seqArray = $request->input('seq', []);

    $firstSeq = is_array($seqArray) ? ($seqArray[0] ?? null) : $seqArray;

    if (empty($subtitleid) || empty($firstSeq)) {
        return redirect()->back()->with('error', 'Please select at least one subtitle.');
    }

    return redirect()->route('subtitles.line', [$subtitleid, $firstSeq]);
}

public function index(Request $request)
{


$page = $request->page ?? 1;

$alluser= [];

// $user =Student::chunk(2,function($user)use (&$alluser){
//     foreach($user as $users){

//      $alluser[] = $users->name;
//     }
// });


$alluser = Cache::remember('users',60,function(){
    return Student::all();
});


$a=[
    'user'=> 'deleted',
    // 'data' => $user,
    // 'page' => $page,

    'name' => $alluser,
];
$a=50;

SendWelcomeEmail::dispatch($a);



return response()->json($a,200); 
}

public function checkservice(){
$a =10;
event(new userRegistered($a));
}

}
