<?php

declare(strict_types=1);

use Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\DynamicInstrumentationFactory;
use OpenTelemetry\SDK\Sdk;

if (class_exists(Sdk::class)) {
    return;
}

if (extension_loaded('opentelemetry') === FALSE) {
    trigger_error('The opentelemetry extension must be loaded in order to autoload the OpenTelemetry Drupal auto-instrumentation', E_USER_WARNING);

    return;
}

try {
    /**
     * Register the instrumentation.
     * - introduce levels of instrumentation
     * - introduce object extraction middleware
     *
     * Levels of instrumentation:
     * - low level: for example, database queries, cache hits/misses, http requests, etc. Good for debugging.
     * - high level: for example, controller calls, service calls, etc. Good for monitoring.
     *
     * Use low level locally and high level in production.
     *
     * Object extraction middleware:
     * - It should be registered independently of the instrumentation.
     * - Chain of responsibility pattern could also work.
     */
    DynamicInstrumentationFactory::autoRegister();
}
catch (Throwable $exception) {
    trigger_error($exception->getMessage(), E_USER_WARNING);
}
