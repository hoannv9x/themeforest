<?php
declare(strict_types=1);

function parseArgs(array $argv): array {
    $args = [
        'promptFile' => __DIR__ . DIRECTORY_SEPARATOR . 'ai_pr_review_tool_prompt_and_plan_docs.md',
        'title' => null,
        'description' => null,
        'descriptionFile' => null,
        'commits' => null,
        'commitsFile' => null,
        'diff' => null,
        'diffFile' => null,
        'git' => null,
        'cwd' => getcwd() ?: null,
        'out' => null,
        'model' => getenv('OPENAI_MODEL') ?: 'gpt-5',
        'baseUrl' => rtrim(getenv('OPENAI_BASE_URL') ?: 'https://api.openai.com', '/'),
        'maxChars' => (int)(getenv('REVIEW_MAX_CHARS') ?: 120000),
        'maxOutputTokens' => (int)(getenv('REVIEW_MAX_OUTPUT_TOKENS') ?: 2500),
        'temperature' => (float)(getenv('REVIEW_TEMPERATURE') ?: 0.2),
    ];

    $i = 1;
    while ($i < count($argv)) {
        $arg = $argv[$i];
        $next = $argv[$i + 1] ?? null;
        if ($arg === '--prompt-file' && $next !== null) { $args['promptFile'] = $next; $i += 2; continue; }
        if ($arg === '--title' && $next !== null) { $args['title'] = $next; $i += 2; continue; }
        if ($arg === '--description' && $next !== null) { $args['description'] = $next; $i += 2; continue; }
        if ($arg === '--description-file' && $next !== null) { $args['descriptionFile'] = $next; $i += 2; continue; }
        if ($arg === '--commits' && $next !== null) { $args['commits'] = $next; $i += 2; continue; }
        if ($arg === '--commits-file' && $next !== null) { $args['commitsFile'] = $next; $i += 2; continue; }
        if ($arg === '--diff' && $next !== null) { $args['diff'] = $next; $i += 2; continue; }
        if ($arg === '--diff-file' && $next !== null) { $args['diffFile'] = $next; $i += 2; continue; }
        if ($arg === '--git' && $next !== null) { $args['git'] = $next; $i += 2; continue; }
        if ($arg === '--cwd' && $next !== null) { $args['cwd'] = $next; $i += 2; continue; }
        if ($arg === '--out' && $next !== null) { $args['out'] = $next; $i += 2; continue; }
        if ($arg === '--model' && $next !== null) { $args['model'] = $next; $i += 2; continue; }
        if ($arg === '--base-url' && $next !== null) { $args['baseUrl'] = rtrim($next, '/'); $i += 2; continue; }
        if ($arg === '--max-chars' && $next !== null) { $args['maxChars'] = max(1000, (int)$next); $i += 2; continue; }
        if ($arg === '--max-output-tokens' && $next !== null) { $args['maxOutputTokens'] = max(256, (int)$next); $i += 2; continue; }
        if ($arg === '--temperature' && $next !== null) { $args['temperature'] = (float)$next; $i += 2; continue; }
        if ($arg === '--help' || $arg === '-h') { $args['help'] = true; $i += 1; continue; }
        $args['unknown'][] = $arg;
        $i += 1;
    }
    return $args;
}

function usage(): string {
    $script = basename(__FILE__);
    return implode(PHP_EOL, [
        "Usage:",
        "  php $script --cwd <repo-path> [--title <text>] [--description <text|--description-file path>] [--git <range>]",
        "  php $script --diff-file <path> [--commits-file <path>] [--title <text>] [--description-file <path>]",
        "  git diff | php $script [--title <text>] [--description <text>]",
        "",
        "Required:",
        "  OPENAI_API_KEY environment variable",
        "",
        "Options:",
        "  --prompt-file <path>           Prompt source (default: tools/review/ai_pr_review_tool_prompt_and_plan_docs.md)",
        "  --cwd <path>                   Working directory (default: current directory)",
        "  --git <range>                  Use git to gather diff/commits (example: origin/main...HEAD)",
        "  --diff-file <path>             Read diff from file (or pipe diff into stdin)",
        "  --commits-file <path>          Read commit messages from file",
        "  --title <text>                 PR title",
        "  --description <text>           PR description",
        "  --description-file <path>      PR description file",
        "  --out <path>                   Write review markdown to file",
        "  --model <model>                Override model (default: OPENAI_MODEL or gpt-5)",
        "  --base-url <url>               Override OpenAI base URL (default: https://api.openai.com)",
        "  --max-chars <n>                Truncate input payload (default: REVIEW_MAX_CHARS or 120000)",
        "  --max-output-tokens <n>        Output token cap (default: REVIEW_MAX_OUTPUT_TOKENS or 2500)",
        "  --temperature <float>          Sampling temperature (default: REVIEW_TEMPERATURE or 0.2)",
    ]) . PHP_EOL;
}

function readFileText(string $path): string {
    $data = @file_get_contents($path);
    if ($data === false) {
        throw new RuntimeException("Cannot read file: $path");
    }
    return $data;
}

function extractPromptFromDocs(string $docs): string {
    $pos = strpos($docs, '# ai-pr-review-prompt.md');
    if ($pos === false) {
        throw new RuntimeException('Prompt section not found in docs');
    }
    $slice = substr($docs, $pos);
    if (!preg_match('/```md\\R(.*?)\\R```/s', $slice, $m)) {
        throw new RuntimeException('Prompt fenced block not found in docs');
    }
    $prompt = trim($m[1]);
    if ($prompt === '') {
        throw new RuntimeException('Prompt is empty');
    }
    return $prompt;
}

function runProcess(string $command, ?string $cwd): array {
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = @proc_open($command, $descriptorSpec, $pipes, $cwd ?: null);
    if (!is_resource($process)) {
        return ['code' => 127, 'stdout' => '', 'stderr' => 'Failed to start process'];
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]) ?: '';
    $stderr = stream_get_contents($pipes[2]) ?: '';
    fclose($pipes[1]);
    fclose($pipes[2]);
    $code = proc_close($process);
    return ['code' => (int)$code, 'stdout' => $stdout, 'stderr' => $stderr];
}

function isGitRepo(?string $cwd): bool {
    $res = runProcess('git rev-parse --is-inside-work-tree', $cwd);
    return $res['code'] === 0 && trim($res['stdout']) === 'true';
}

function normalizeNewlines(string $s): string {
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    return $s;
}

function redactSecrets(string $s): string {
    $patterns = [
        '/(OPENAI_API_KEY\\s*[:=]\\s*)(\\S+)/i',
        '/(AWS_SECRET_ACCESS_KEY\\s*[:=]\\s*)(\\S+)/i',
        '/(AWS_ACCESS_KEY_ID\\s*[:=]\\s*)(\\S+)/i',
        '/(GITHUB_TOKEN\\s*[:=]\\s*)(\\S+)/i',
        '/(GITLAB_TOKEN\\s*[:=]\\s*)(\\S+)/i',
        '/(API_KEY\\s*[:=]\\s*)(\\S+)/i',
        '/(SECRET\\s*[:=]\\s*)(\\S+)/i',
        '/(PASSWORD\\s*[:=]\\s*)(\\S+)/i',
        '/(TOKEN\\s*[:=]\\s*)(\\S+)/i',
        '/(Authorization:\\s*Bearer\\s+)(\\S+)/i',
    ];
    foreach ($patterns as $p) {
        $s = preg_replace($p, '$1[REDACTED]', $s) ?? $s;
    }
    return $s;
}

function truncate(string $s, int $maxChars, string $label): array {
    if (mb_strlen($s, '8bit') <= $maxChars) {
        return [$s, null];
    }
    $truncated = substr($s, 0, $maxChars);
    $note = "$label truncated to {$maxChars} chars";
    return [$truncated, $note];
}

function readStdinIfPiped(): ?string {
    $meta = stream_get_meta_data(STDIN);
    if (($meta['stream_type'] ?? '') === 'STDIO') {
        if (function_exists('stream_isatty') && stream_isatty(STDIN)) { return null; }
        if (function_exists('posix_isatty') && posix_isatty(STDIN)) { return null; }
        if (!function_exists('stream_isatty') && !function_exists('posix_isatty')) { return null; }
    }
    $data = stream_get_contents(STDIN);
    if ($data === false) {
        return null;
    }
    $data = trim($data);
    return $data === '' ? null : $data;
}

function buildUserPayload(array $ctx): string {
    $parts = [];
    $parts[] = "PR Title:\n" . ($ctx['title'] ?? '(missing)');
    $parts[] = "PR Description:\n" . ($ctx['description'] ?? '(missing)');
    $parts[] = "Commit Messages:\n" . ($ctx['commits'] ?? '(missing)');
    $parts[] = "Git Diff:\n" . ($ctx['diff'] ?? '(missing)');
    return implode("\n\n---\n\n", $parts);
}

function extractResponseText(array $json): string {
    if (isset($json['output_text']) && is_string($json['output_text'])) {
        return $json['output_text'];
    }
    $texts = [];
    if (isset($json['output']) && is_array($json['output'])) {
        foreach ($json['output'] as $item) {
            if (!is_array($item)) { continue; }
            if (isset($item['content']) && is_array($item['content'])) {
                foreach ($item['content'] as $c) {
                    if (is_array($c) && ($c['type'] ?? null) === 'output_text' && isset($c['text']) && is_string($c['text'])) {
                        $texts[] = $c['text'];
                    }
                    if (is_array($c) && ($c['type'] ?? null) === 'text' && isset($c['text']) && is_string($c['text'])) {
                        $texts[] = $c['text'];
                    }
                }
            }
        }
    }
    $out = trim(implode("\n", $texts));
    if ($out !== '') { return $out; }
    return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '';
}

function openaiRequest(string $baseUrl, string $apiKey, array $payload): array {
    $url = $baseUrl . '/v1/responses';
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Failed to init curl');
    }
    $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
    if ($body === false) {
        throw new RuntimeException('Failed to encode request payload');
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_TIMEOUT => 180,
    ]);
    $resp = curl_exec($ch);
    $errno = curl_errno($ch);
    $err = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp === false || $errno !== 0) {
        throw new RuntimeException('HTTP request failed: ' . $err);
    }
    $json = json_decode($resp, true);
    if (!is_array($json)) {
        throw new RuntimeException("Invalid JSON response (HTTP $status): " . substr($resp, 0, 2000));
    }
    if ($status >= 400) {
        $msg = $json['error']['message'] ?? ('HTTP ' . $status);
        throw new RuntimeException("OpenAI API error: $msg");
    }
    return $json;
}

$args = parseArgs($argv);
if (($args['help'] ?? false) === true) {
    fwrite(STDOUT, usage());
    exit(0);
}
if (!empty($args['unknown'])) {
    fwrite(STDERR, "Unknown arguments: " . implode(', ', $args['unknown']) . PHP_EOL);
    fwrite(STDERR, usage());
    exit(2);
}

$apiKey = getenv('OPENAI_API_KEY') ?: '';
if ($apiKey === '') {
    fwrite(STDERR, "Missing OPENAI_API_KEY environment variable" . PHP_EOL);
    fwrite(STDERR, usage());
    exit(2);
}

$docsText = readFileText($args['promptFile']);
$systemPrompt = extractPromptFromDocs($docsText);

$title = $args['title'];
$description = $args['description'];
if ($description === null && $args['descriptionFile'] !== null) {
    $description = readFileText($args['descriptionFile']);
}

$commits = $args['commits'];
if ($commits === null && $args['commitsFile'] !== null) {
    $commits = readFileText($args['commitsFile']);
}

$diff = $args['diff'];
if ($diff === null && $args['diffFile'] !== null) {
    $diff = readFileText($args['diffFile']);
}
if ($diff === null) {
    $piped = readStdinIfPiped();
    if ($piped !== null) {
        $diff = $piped;
    }
}

$cwd = $args['cwd'];
if ($diff === null && isGitRepo($cwd)) {
    $range = $args['git'];
    if ($range !== null) {
        $res = runProcess('git diff ' . escapeshellarg($range), $cwd);
        if ($res['code'] === 0) { $diff = $res['stdout']; }
        $cres = runProcess('git log --oneline ' . escapeshellarg($range), $cwd);
        if ($cres['code'] === 0 && $commits === null) { $commits = $cres['stdout']; }
    } else {
        $staged = runProcess('git diff --cached', $cwd);
        if ($staged['code'] === 0 && trim($staged['stdout']) !== '') {
            $diff = $staged['stdout'];
        } else {
            $unstaged = runProcess('git diff', $cwd);
            if ($unstaged['code'] === 0) { $diff = $unstaged['stdout']; }
        }
        if ($commits === null) {
            $log = runProcess('git log --oneline -n 20', $cwd);
            if ($log['code'] === 0) { $commits = $log['stdout']; }
        }
    }
}

$title = $title !== null ? normalizeNewlines($title) : null;
$description = $description !== null ? normalizeNewlines($description) : null;
$commits = $commits !== null ? normalizeNewlines($commits) : null;
$diff = $diff !== null ? normalizeNewlines($diff) : null;

$notes = [];
if ($diff !== null) {
    $diff = redactSecrets($diff);
    [$diff, $note] = truncate($diff, $args['maxChars'], 'Diff');
    if ($note !== null) { $notes[] = $note; }
}
if ($commits !== null) {
    $commits = redactSecrets($commits);
    [$commits, $note] = truncate($commits, (int)floor($args['maxChars'] / 3), 'Commits');
    if ($note !== null) { $notes[] = $note; }
}
if ($description !== null) {
    $description = redactSecrets($description);
    [$description, $note] = truncate($description, (int)floor($args['maxChars'] / 3), 'Description');
    if ($note !== null) { $notes[] = $note; }
}

$ctx = [
    'title' => $title,
    'description' => $description,
    'commits' => $commits,
    'diff' => $diff,
];

$userPayload = buildUserPayload($ctx);
if (!empty($notes)) {
    $userPayload = "Notes:\n- " . implode("\n- ", $notes) . "\n\n---\n\n" . $userPayload;
}

$payload = [
    'model' => $args['model'],
    'input' => [
        [
            'role' => 'system',
            'content' => [
                ['type' => 'text', 'text' => $systemPrompt],
            ],
        ],
        [
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => $userPayload],
            ],
        ],
    ],
    'temperature' => $args['temperature'],
    'max_output_tokens' => $args['maxOutputTokens'],
];

try {
    $json = openaiRequest($args['baseUrl'], $apiKey, $payload);
    $text = extractResponseText($json);
    $text = trim($text) . PHP_EOL;
    if ($args['out'] !== null) {
        $ok = @file_put_contents($args['out'], $text);
        if ($ok === false) {
            fwrite(STDERR, "Failed to write output file: {$args['out']}" . PHP_EOL);
            exit(1);
        }
    } else {
        fwrite(STDOUT, $text);
    }
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

