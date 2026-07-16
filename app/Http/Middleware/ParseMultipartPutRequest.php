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
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if ($request->files->count() > 0) {
                $enabledFiles = $this->enableTestMode($request->files->all());
                $request->files->replace($enabledFiles);
            } elseif (str_contains($request->header('Content-Type', ''), 'multipart/form-data')) {
                try {
                    [$post, $files] = request_parse_body();

                    // Merge the parsed body parameters into the Laravel request input
                    $request->merge($post);

                    // Convert the raw files array into Symfony UploadedFile objects with test mode enabled
                    $fileBag = new FileBag($files);
                    $enabledFiles = $this->enableTestMode($fileBag->all());
                    $request->files->add($enabledFiles);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to parse multipart PUT/PATCH request.');
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
                $files[$key] = new \Illuminate\Http\UploadedFile(
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
