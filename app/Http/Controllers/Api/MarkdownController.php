<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class MarkdownController extends Controller
{
    public function releaseNote()
    {
        $directory = resource_path('release_note');
        if (!File::exists($directory)) {
            return response()->json([
                'success' => false,
                'text' => 'Release note folder not found',
                'result' => null,
            ], 404);
        }

        $files = collect(File::files($directory))
            ->filter(function ($file) {
                return strtolower($file->getExtension()) === 'md';
            })
            ->sortByDesc(function ($file) {
                return $file->getFilename();
            })
            ->values();

        $notes = $files->map(function ($file) {
            $content = File::get($file->getPathname());
            $title = $file->getFilename();
            foreach (preg_split('/\r?\n/', $content) as $line) {
                if (preg_match('/^#\s+(.*)$/', trim($line), $matches)) {
                    $title = $matches[1];
                    break;
                }
            }
            return [
                'filename' => $file->getFilename(),
                'title' => $title,
                'content' => $content,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'text' => 'Retrieve release notes success',
            'result' => $notes,
        ]);
    }
}
