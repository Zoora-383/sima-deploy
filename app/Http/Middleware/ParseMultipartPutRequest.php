<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Response;

class ParseMultipartPutRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Illuminate\Support\Facades\Log::info('ParseMultipartPutRequest executing:', [
            'method' => $request->method(),
            'real_method' => $request->getRealMethod(),
            'content_type' => $request->header('Content-Type'),
            'has_files_initially' => !empty($request->allFiles()),
        ]);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if (!empty($request->allFiles())) {
                \Illuminate\Support\Facades\Log::info('ParseMultipartPutRequest files already present. Enabling test mode.');
                $enabledFiles = $this->enableTestMode($request->files->all());
                $request->files->replace($enabledFiles);
            } elseif (str_contains($request->header('Content-Type', ''), 'multipart/form-data')) {
                try {
                    \Illuminate\Support\Facades\Log::info('ParseMultipartPutRequest entering parsing block.');
                    [$post, $files] = request_parse_body();

                    \Illuminate\Support\Facades\Log::info('ParseMultipartPutRequest body parsed successfully:', [
                        'post_keys' => array_keys($post),
                        'files_keys' => array_keys($files),
                    ]);

                    // Merge the parsed body parameters into the Laravel request input
                    $request->merge($post);

                    // Convert the raw files array into Symfony UploadedFile objects with test mode enabled
                    $fileBag = new FileBag($files);
                    $enabledFiles = $this->enableTestMode($fileBag->all());
                    $request->files->add($enabledFiles);

                    \Illuminate\Support\Facades\Log::info('ParseMultipartPutRequest files added to request:', [
                        'files_added' => array_keys($enabledFiles),
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('ParseMultipartPutRequest exception:', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Recursively change the $test property to true for all UploadedFile instances.
     * This bypasses the is_uploaded_file() check which fails for manual multipart PUT/PATCH parsing.
     *
     * @param array $files
     * @return array
     */
    private function enableTestMode(array $files): array
    {
        foreach ($files as $key => $value) {
            if ($value instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $files[$key] = new \Symfony\Component\HttpFoundation\File\UploadedFile(
                    $value->getPathname(),
                    $value->getClientOriginalName(),
                    $value->getClientMimeType(),
                    $value->getError(),
                    true // Set test mode to true to bypass is_uploaded_file() check
                );
            } elseif (is_array($value)) {
                $files[$key] = $this->enableTestMode($value);
            }
        }

        return $files;
    }
}
