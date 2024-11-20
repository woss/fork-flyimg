<?php

namespace Tests;

include_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$code = $request->query->get('code', 200);

$response = new Response(
    $code,
    $code,
    ['content-type' => 'text/html']
);

return $response->send();
