# Integration

## Configuration
Add to `composer.json` `autoload` section:
```bash
    "files": [
      "_register.php"
    ]
```
`_register.php` is a file that registers collectors and extractors.

Usage:
```php
<?php
...
\Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\DynamicInstrumentationFactory::addCollector(
    /** collector */
);

\Buratinas\OpenTelemetryAutoInstrumentation\Instrumentation\DynamicInstrumentationFactory::addExtractor(
    /** extractor */
);
```
