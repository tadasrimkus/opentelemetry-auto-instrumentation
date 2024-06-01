<?php

namespace Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor;

use OpenTelemetry\API\Trace\SpanInterface;

interface InformationExtractorInterface
{

    public function supportsExtraction(object $object): bool;

    public function extractInformation(object $object, SpanInterface $span, array $arguments = []): void;

    public function supportsArgumentExtraction(mixed $argument): bool;

    public function extractArgumentInformation(mixed $argument, SpanInterface $span, int $counter): void;

}
