<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class AdminLogController extends Controller
{
    public function index()
    {
        $dir = storage_path('logs');
        $items = [];

        if (File::exists($dir)) {
            foreach (File::files($dir) as $file) {
                $name = $file->getFilename();
                if (!preg_match('/^laravel-(\d{4}-\d{2}-\d{2})\.log$/', $name, $m)) {
                    continue;
                }

                $items[] = [
                    'date' => $m[1],
                    'filename' => $name,
                    'size_bytes' => $file->getSize(),
                    'updated_at' => Carbon::createFromTimestamp($file->getMTime())->toDateTimeString(),
                ];
            }
        }

        usort($items, fn ($a, $b) => strcmp($b['date'], $a['date']));

        return response()->json([
            'items' => array_slice($items, 0, 3),
        ]);
    }

    public function show(Request $request, string $date)
    {
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), 422, 'Invalid date format');

        $lines = (int) $request->query('lines', 800);
        if ($lines < 50) $lines = 50;
        if ($lines > 3000) $lines = 3000;

        $filename = "laravel-{$date}.log";
        $path = storage_path("logs/{$filename}");

        abort_unless(File::exists($path), 404, 'Log file not found');

        [$content, $truncated] = $this->tail($path, $lines, 2 * 1024 * 1024);

        return response()->json([
            'date' => $date,
            'filename' => $filename,
            'lines' => $lines,
            'truncated' => $truncated,
            'size_bytes' => File::size($path),
            'content' => $content,
        ]);
    }

    public function download(string $date)
    {
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), 422, 'Invalid date format');

        $filename = "laravel-{$date}.log";
        $path = storage_path("logs/{$filename}");

        abort_unless(File::exists($path), 404, 'Log file not found');

        return response()->download($path, $filename, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    private function tail(string $path, int $lines, int $maxBytes): array
    {
        $fp = fopen($path, 'rb');
        if ($fp === false) {
            return ['', false];
        }

        try {
            fseek($fp, 0, SEEK_END);
            $pos = ftell($fp);
            if ($pos === false) {
                return ['', false];
            }

            $buffer = '';
            $readBytes = 0;
            $chunkSize = 8192;
            $truncated = false;

            while ($pos > 0 && substr_count($buffer, "\n") < $lines && $readBytes < $maxBytes) {
                $seek = max(0, $pos - $chunkSize);
                $len = $pos - $seek;
                fseek($fp, $seek);
                $chunk = fread($fp, $len);
                if ($chunk === false) {
                    break;
                }

                $buffer = $chunk . $buffer;
                $readBytes += $len;
                $pos = $seek;
            }

            if ($pos > 0) {
                $truncated = true;
            }
            if ($readBytes >= $maxBytes) {
                $truncated = true;
            }

            $rows = preg_split("/\r\n|\r|\n/", $buffer);
            if (!$rows) {
                return ['', $truncated];
            }

            $tail = array_slice($rows, -$lines);
            return [implode("\n", $tail), $truncated];
        } finally {
            fclose($fp);
        }
    }
}
