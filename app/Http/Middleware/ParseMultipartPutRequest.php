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
        if (($request->isMethod('PUT') || $request->isMethod('PATCH')) &&
            str_contains($request->header('Content-Type', ''), 'multipart/form-data')) {
            try {
                [$post, $files] = request_parse_body();

                // Merge the parsed body parameters into the Laravel request input
                $request->merge($post);

                // Convert the raw files array into Symfony UploadedFile objects and add to the request
                $request->files->add((new FileBag($files))->all());
            } catch (\Exception $e) {
                // If parsing fails, proceed without modification
            }
        }

        return $next($request);
    }
}
