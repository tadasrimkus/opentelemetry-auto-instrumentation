<?php

namespace Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation;

use Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor\InformationExtractorAdditionInterface;
use Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor\InformationExtractorChain;
use Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\Extractor\InformationExtractorInterface;
use Closure;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;

use Throwable;
use function OpenTelemetry\Instrumentation\hook;

class DynamicInstrumentation
{
    public static null|InformationExtractorInterface|InformationExtractorAdditionInterface $extractor = NULL;

    public static function register(string $class, string $function, int $spanKind = SpanKind::KIND_SERVER): bool
    {
        $instrumentation = new CachedInstrumentation('io.opentelemetry.contrib.php.symfony');

        hook(
            $class,
            $function,
            static::preClosure($instrumentation, $spanKind),
            static::postClosure()
        );

        return TRUE;
    }

    public static function preClosure(CachedInstrumentation $instrumentation, int $spanKind = SpanKind::KIND_SERVER): Closure
    {
        return static function (object $kernel, array $params, string $class, string $function, ?string $filename, ?int $lineno) use ($instrumentation, $spanKind) {
            $spanName = sprintf('%s::%s', substr(strrchr($class, '\\'), 1), $function);
            $span = $instrumentation->tracer()
                ->spanBuilder($spanName)
                ->setSpanKind($spanKind)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::CODE_FILEPATH, $filename)
                ->setAttribute(TraceAttributes::CODE_LINENO, $lineno)
                ->startSpan();

            static::getExtractor()?->extractInformation($kernel, $span, $params);

            Context::storage()->attach($span->storeInContext(Context::getCurrent()));
        };
    }

    public static function postClosure(): Closure
    {
        return static function (object $kernel, array $params, $returnValue, ?Throwable $exception) {
            $scope = Context::storage()->scope();
            if (!$scope) {
                return;
            }
            $scope->detach();
            $span = Span::fromContext($scope->context());

            if ($exception) {
                $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => TRUE]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            }

            $span->end();
        };
    }

    public static function addExtractor(InformationExtractorInterface $extractor): void
    {
        static::getExtractor()->addExtractor($extractor);
    }

    public static function getExtractor(): null|InformationExtractorInterface|InformationExtractorAdditionInterface
    {
        return static::$extractor ??= new InformationExtractorChain();
    }
}
