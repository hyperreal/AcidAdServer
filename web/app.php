<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

umask(0000);

//if (function_exists('apc_add')) {
    // Use APC for autoloading to improve performance
    // Change 'sf2' by the prefix you want in order to prevent key conflict with another application

//    $loader = new ApcClassLoader('acid', $loader);
//    $loader->register(true);
//} else {
    $loader = require_once __DIR__.'/../app/bootstrap.php.cache';
//}

require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
