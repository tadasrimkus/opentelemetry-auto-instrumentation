<?php

namespace Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation;

/**
 * Class DynamicInstrumentationFactory
 * Uses static extractor chain to extract information from the object and arguments
 * Uses static collector to collect information from the object and arguments
 *
 */
class DynamicInstrumentationFactory
{
    private static array $extractors = [];
    private static array $collectors = [];

    public static function autoRegister(): void
    {
        array_walk(self::$collectors, function($collectorInformation) {
            class_exists($collectorInformation['class']) &&
            method_exists($collectorInformation['class'], $collectorInformation['method']) &&
            DynamicInstrumentation::register($collectorInformation['class'], $collectorInformation['method']);
        });

        array_walk(self::$extractors, function($extractor) {
            DynamicInstrumentation::addExtractor($extractor);
        });
    }

    public static function addExtractor(string $extractor): void
    {
        self::$extractors[] = $extractor;
    }

    public static function addCollector(string $collector, string $method): void
    {
        $key = sprintf('%s::%s', $collector, $method);
        self::$collectors[$key] = [
            'method' => $method,
            'class' => $collector
        ];
    }
}
