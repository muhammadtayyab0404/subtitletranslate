<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ModelApi;
use Illuminate\Http\JsonResponse;
use Throwable;


class SubtitleAiController extends Controller
{
   public function analyze(Request $request, ModelApi $modelApi): JsonResponse
    {
        $validated = $request->validate([
            'sentence' => ['required', 'string'],
            'targetLanguage' => ['required', 'string'],
        ]);

        try {
            $result = $modelApi->analyzeSentence(
                $validated['sentence'],
                $validated['targetLanguage']
            );

            return response()->json([
                'ok' => true,
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function chat(Request $request, ModelApi $modelApi): JsonResponse
{
    $validated = $request->validate([
        'sentence' => ['required', 'string'],
        'targetLanguage' => ['required', 'string'],
        'question' => ['required', 'string'],
        'history' => ['nullable', 'array'],
    ]);

    try {
        $chat = $modelApi->chatAboutSentence(
            $validated['sentence'],
            $validated['targetLanguage'],
            $validated['question'],
            $validated['history'] ?? []
        );

        return response()->json([
            'ok' => true,
            'data' => [
                'reply' => $chat['reply'] ?? '',
                'source_reply' => $chat['source_reply'] ?? '',
                'target_reply' => $chat['target_reply'] ?? '',
            ],
        ]);
    } catch (Throwable $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}
}