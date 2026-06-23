<?php

namespace Tests\Feature;

use App\Http\Middleware\ParseMultipartPutRequest;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ParseMultipartPutRequestTest extends TestCase
{
    public function test_middleware_passes_through_non_multipart_requests()
    {
        $request = Request::create('/test', 'PUT', ['name' => 'John']);
        
        $middleware = new ParseMultipartPutRequest();
        $response = $middleware->handle($request, function ($req) {
            $this->assertEquals('John', $req->input('name'));
            return new \Symfony\Component\HttpFoundation\Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_middleware_passes_through_non_put_or_patch_requests()
    {
        $request = Request::create('/test', 'POST', ['name' => 'John']);
        $request->headers->set('Content-Type', 'multipart/form-data; boundary=---boundary');

        $middleware = new ParseMultipartPutRequest();
        $response = $middleware->handle($request, function ($req) {
            $this->assertEquals('John', $req->input('name'));
            return new \Symfony\Component\HttpFoundation\Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }
}
