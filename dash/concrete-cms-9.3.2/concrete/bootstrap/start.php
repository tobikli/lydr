<?php

use Concrete\Core\Foundation\ClassAutoloader;

defined('C5_EXECUTE') or die("Access Denied.");

/*
 * ----------------------------------------------------------------------------
 * Ensure we're not accessing this file directly.
 * ----------------------------------------------------------------------------
 */
if (basename($_SERVER['PHP_SELF']) == DISPATCHER_FILENAME_CORE) {
    die("Access Denied.");
}

/**
 * ----------------------------------------------------------------------------
 * Disable phar stream wrapper.
 * ----------------------------------------------------------------------------
 */
if (in_array('phar', stream_get_wrappers(), true)) {
    stream_wrapper_unregister('phar');
}

/*
 * ----------------------------------------------------------------------------
 * Handle text encoding.
 * ----------------------------------------------------------------------------
 */
\Patchwork\Utf8\Bootup::initAll();

/*
 * ----------------------------------------------------------------------------
 * Instantiate Concrete
 * ----------------------------------------------------------------------------
 */
/** @var \Concrete\Core\Application\Application $app */
$app = require DIR_APPLICATION . '/bootstrap/start.php';
$app->instance('app', $app);

// Bind fully application qualified class names
$app->instance('Concrete\Core\Application\Application', $app);
$app->instance('Illuminate\Container\Container', $app);
$app->instance(ClassAutoloader::class, ClassAutoloader::getInstance());

// Boot the runtime
$app->getRuntime()->boot();

return $app;
