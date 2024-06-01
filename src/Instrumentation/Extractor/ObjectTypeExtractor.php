<?php

namespace Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor;

use OpenTelemetry\API\Trace\SpanInterface;

class ObjectTypeExtractor implements InformationExtractorInterface
{

    public function extractInformation(object $object, SpanInterface $span, array $arguments = []): void
    {
        if ($this->supportsExtraction($object)) {
            $span->setAttribute('object_type', gettype($object));
        }
    }

    public function supportsExtraction(object $object): bool
    {
        return TRUE;
    }

    public function supportsArgumentExtraction(mixed $argument): bool
    {
        return TRUE;
    }

    public function extractArgumentInformation(mixed $argument, SpanInterface $span, int $counter): void
    {
        if ($this->supportsArgumentExtraction($argument)) {
            $argumentValue = match (gettype($argument)) {
                'string', 'double', 'integer', 'boolean' => $argument,
                'array' => json_encode($argument),
                'object' => get_class($argument),
                default => gettype($argument),
            };

            $span->setAttribute(sprintf('argument_%s', $counter), $argumentValue);
        }
    }

}
