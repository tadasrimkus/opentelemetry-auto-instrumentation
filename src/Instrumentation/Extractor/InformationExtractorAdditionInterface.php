<?php

namespace Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor;

interface InformationExtractorAdditionInterface
{
    public function addExtractor(InformationExtractorInterface $extractor): void;
}
