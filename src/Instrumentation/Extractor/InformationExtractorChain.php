<?php

namespace Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor;

use OpenTelemetry\API\Trace\SpanInterface;

class InformationExtractorChain implements InformationExtractorInterface, InformationExtractorAdditionInterface {

  private array $extractors = [];

  public function addExtractor(InformationExtractorInterface $extractor): void {
    $this->extractors[] = $extractor;
  }

  public function extractInformation(object $object, SpanInterface $span, array $arguments = []): void {
    /** @var InformationExtractorInterface $extractor */
    foreach ($this->extractors as $extractor) {
      if ($extractor->supportsExtraction($object)) {
        $extractor->extractInformation($object, $span);
      }

      $counter = 0;
      foreach ($arguments as $argument) {
        if ($extractor->supportsArgumentExtraction($argument)) {
          $extractor->extractArgumentInformation($argument, $span, $counter);
        }
        $counter++;
      }
    }
  }

  public function supportsExtraction(object $object): bool {
    return TRUE;
  }

  public function extractArgumentInformation(mixed $argument, SpanInterface $span, int $counter): void {
    /** @var InformationExtractorInterface $extractor */
    foreach ($this->extractors as $extractor) {
      if ($extractor->supportsArgumentExtraction($argument)) {
        $extractor->extractArgumentInformation($argument, $span, $counter);
      }
    }
  }

  public function supportsArgumentExtraction(mixed $argument): bool {
    return TRUE;
  }

}
