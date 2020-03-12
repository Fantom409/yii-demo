<?php

use hiqdev\composer\config\Builder;
use Yiisoft\Di\Container;
use Yiisoft\Http\Method;
use Yiisoft\Yii\Debug\Debugger;
use Yiisoft\Yii\Debug\DebugServiceProvider;
use Yiisoft\Yii\Web\Application;
use Yiisoft\Yii\Web\SapiEmitter;
use Yiisoft\Yii\Web\ServerRequestFactory;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Don't do it in production, assembling takes it's time
Builder::rebuild();
require_once dirname(__DIR__) . '/src/globals.php';

$definitions = (require Builder::path('web'));
$providers = [];

$debugEnabled = (bool)($params['debugger.enabled'] ?? false) && class_exists(Debugger::class);
if ($debugEnabled) {
    $providers[] = new DebugServiceProvider();
    $providers[] = new \Yiisoft\Yii\Debug\Viewer\DebugViewerServiceProvider();
}

$container = new Container($definitions, $providers);
$application = $container->get(Application::class);

$request = $container->get(ServerRequestFactory::class)->createFromGlobals();

try {
    $application->start();
    $response = $application->handle($request);
    $emitter = new SapiEmitter();
    $emitter->emit($response, $request->getMethod() === Method::HEAD);
} finally {
    $application->shutdown();
}
