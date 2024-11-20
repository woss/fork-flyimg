<?php

include_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MockServer
{
    public Request $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    public function getResponse(int $responseCode): Response
    {
        $response = new Response($responseCode,
    $responseCode,
            ['content-type' => 'text/html']
        );
        
        return $response;
    }
}

$server = new MockServer();

$code = $server->request->query->get('code', 200);
return $server->getResponse($code)->send();
