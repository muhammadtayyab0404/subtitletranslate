<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function analyzeSentence(string $sentence, string $targetLanguage): array
{
    $prompt = <<<PROMPT
You are a subtitle tutor.

Analyze this subtitle sentence for a learner.

Sentence: {$sentence}
Target language: {$targetLanguage}

IMPORTANT RULES:
1. Detect the source language automatically from the sentence.
2. Return ONLY valid JSON.
3. "description_target" must explain the sentence in {$targetLanguage}.
4. "description_source" must explain the sentence in the same language as the original sentence.
5. "translation_target" must be the full natural translation in {$targetLanguage}.
6. "translation_source" must be the full natural sentence in the same language as the original sentence.
7. In "words", include every visible token in order, including punctuation if present.
8. "meaning_target" must be the word meaning in {$targetLanguage}.
9. "meaning_source" must be the word meaning in the same language as the original sentence.
10. "note" should be short and helpful in {$targetLanguage}.
11. Keep grammar notes short and learner-friendly.
12. Do not add any keys outside the schema.

PROMPT;

    $schema = [
        'type' => 'object',
        'properties' => [
            'description_target' => ['type' => 'string'],
            'description_source' => ['type' => 'string'],
            'translation_target' => ['type' => 'string'],
            'translation_source' => ['type' => 'string'],
            'grammar' => [
                'type' => 'object',
                'properties' => [
                    'tense' => ['type' => 'string'],
                    'structure' => ['type' => 'string'],
                    'subject' => ['type' => 'string'],
                    'verb' => ['type' => 'string'],
                    'object' => ['type' => 'string'],
                    'notes' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
                'required' => ['tense', 'structure', 'subject', 'verb', 'object', 'notes'],
            ],
            'words' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'i' => ['type' => 'integer'],
                        'raw' => ['type' => 'string'],
                        'clean' => ['type' => 'string'],
                        'meaning_target' => ['type' => 'string'],
                        'meaning_source' => ['type' => 'string'],
                        'pos' => ['type' => 'string'],
                        'note' => ['type' => 'string'],
                    ],
                    'required' => ['i', 'raw', 'clean', 'meaning_target', 'meaning_source', 'pos', 'note'],
                ],
            ],
        ],
        'required' => [
            'description_target',
            'description_source',
            'translation_target',
            'translation_source',
            'grammar',
            'words',
        ],
    ];

    $makeRequest = function (string $finalPrompt, float $temperature, int $maxOutputTokens) use ($schema) {
        $response = Http::timeout(90)
            ->withHeaders([
                'x-goog-api-key' => config('services.gemini.key'),
                'Content-Type' => 'application/json',
            ])
            ->post(
                config('services.gemini.base_url') . '/models/' . config('services.gemini.model') . ':generateContent',
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $finalPrompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $schema,
                        'temperature' => $temperature,
                        'maxOutputTokens' => $maxOutputTokens,
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException('Gemini request failed: ' . $response->body());
        }

        return $response->json();
    };

    // First attempt
    $raw = $makeRequest($prompt, 0.2, 2200);

    $text = (string) data_get($raw, 'candidates.0.content.parts.0.text', '');
    $finishReason = (string) data_get($raw, 'candidates.0.finishReason', '');

    $decoded = json_decode($text, true);

    if (is_array($decoded)) {
        return $decoded;
    }

    // Retry once with stricter/shorter instruction
    $retryPrompt = $prompt . "\nRETRY RULES:\n- Keep all values shorter.\n- Keep grammar notes very short.\n- Do not output anything except the JSON object.";

    $rawRetry = $makeRequest($retryPrompt, 0.1, 2600);

    $retryText = (string) data_get($rawRetry, 'candidates.0.content.parts.0.text', '');
    $retryFinishReason = (string) data_get($rawRetry, 'candidates.0.finishReason', '');

    $decodedRetry = json_decode($retryText, true);

    if (!is_array($decodedRetry)) {
        throw new RuntimeException(
            'Gemini returned invalid JSON. First finishReason: '
            . ($finishReason ?: 'unknown')
            . ', Retry finishReason: '
            . ($retryFinishReason ?: 'unknown')
            . '. Raw retry text: '
            . $retryText
        );
    }

    return $decodedRetry;
}

    public function chatAboutSentence(
    string $sentence,
    string $targetLanguage,
    string $question,
    array $history = []
): string {
    $messagesText = '';

    foreach ($history as $msg) {
        $role = $msg['role'] ?? 'user';
        $content = $msg['content'] ?? '';
        $messagesText .= strtoupper($role) . ': ' . $content . "\n";
    }

    $prompt = <<<PROMPT
You are a subtitle tutor.

Sentence: {$sentence}
Target language: {$targetLanguage}

Previous conversation:
{$messagesText}

User question:
{$question}

IMPORTANT RULES:
1. Detect the source language automatically from the sentence.
2. Read the user's question carefully and check whether the user explicitly asked for a specific response language.
3. If the user explicitly asks for a language (for example: "answer in English", "reply in Urdu", "explain in Arabic"), then reply ONLY in that requested language.
4. If the user does NOT explicitly ask for a specific response language, then reply in BOTH languages:
   - First in the source language of the sentence
   - Then in {$targetLanguage}
5. When replying in both languages, clearly separate them like this:
   Source Language:
   ...
   Target Language:
   ...
6. Stay focused only on this sentence and the user's question.
7. Be clear, concise, and learner-friendly.
8. If grammar is explained, keep it simple.

PROMPT;

    $response = Http::timeout(60)
        ->withHeaders([
            'x-goog-api-key' => config('services.gemini.key'),
            'Content-Type' => 'application/json',
        ])
        ->post(
            config('services.gemini.base_url') . '/models/' . config('services.gemini.model') . ':generateContent',
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 1000,
                ],
            ]
        );

    if ($response->failed()) {
        throw new RuntimeException('Gemini chat failed: ' . $response->body());
    }

    return (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
}

}
