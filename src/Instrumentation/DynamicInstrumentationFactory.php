<?php

namespace Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation;

use Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor\InformationExtractorInterface;

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
        array_walk(self::$collectors, fn($collector) => self::addCollector($collector['class'], $collector['method']));
        array_walk(self::$extractors, fn($extractor) => self::addExtractor($extractor));
    }

    public static function addExtractor(mixed $extractor): void
    {
        $extractor instanceof InformationExtractorInterface &&
        DynamicInstrumentation::addExtractor($extractor);
    }

    public static function addCollector(string $collector, string $method): void
    {
        class_exists($collector) &&
        method_exists($collector, $method) &&
        DynamicInstrumentation::register($collector, $method);
    }
}
