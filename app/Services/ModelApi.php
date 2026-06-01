<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class ModelApi
{
    public function analyzeSentence(string $sentence, string $targetLanguage): array
    {
        $payload = [
            'sentence' => $sentence,
            'targetLanguage' => $targetLanguage,
        ];

        $data = $this->runPython('analyze', $payload);

        if (! is_array($data)) {
            throw new RuntimeException('Local AI returned invalid analyze payload.');
        }

        return $data;
    }

public function chatAboutSentence(
    string $sentence,
    string $targetLanguage,
    string $question,
    array $history = []
): array {
    $payload = [
        'sentence' => $sentence,
        'targetLanguage' => $targetLanguage,
        'question' => $question,
        'history' => $history,
    ];

    $data = $this->runPython('chat', $payload);

    return [
        'reply' => (string) ($data['reply'] ?? ''),
        'source_reply' => (string) ($data['source_reply'] ?? ''),
        'target_reply' => (string) ($data['target_reply'] ?? ''),
    ];
}

    protected function runPython(string $action, array $payload): array
    {
        $python = (string) config('local_ai.python');
        $script = (string) config('local_ai.script');
        $timeout = (int) config('local_ai.timeout', 180);

        if ($python === '') {
            throw new RuntimeException('LOCAL_AI_PYTHON is not configured.');
        }

        if ($script === '' || ! is_file($script)) {
            throw new RuntimeException("LOCAL_AI_SCRIPT is invalid or missing: {$script}");
        }

        $command = sprintf(
            '%s %s %s',
            escapeshellcmd($python),
            escapeshellarg($script),
            escapeshellarg($action)
        );

        $result = Process::path(dirname($script))
            ->timeout($timeout)
            ->input(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->run($command);

        if ($result->failed()) {
            throw new RuntimeException(
                'Local AI process failed: ' . trim($result->errorOutput() ?: $result->output())
            );
        }

        $rawOutput = trim($result->output());
        $decoded = json_decode($rawOutput, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Local AI returned invalid JSON: ' . $rawOutput);
        }

        if (($decoded['ok'] ?? false) !== true) {
            throw new RuntimeException(
                'Local AI error: ' . ($decoded['error'] ?? 'Unknown error')
            );
        }

        return (array) ($decoded['data'] ?? []);
    }
}