<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\EventListeners;

use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SetFormatOnRequestListener
{
    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * Headers used to guess request format.
     *
     * @var array
     */
    private static $headers = [
        'accept',
        'content-type'
    ];

    /**
     * Supported MIME types as [<mime_type> => <format>].
     *
     * @var array
     */
    private $mimeTypes = [];

    /**
     * Set MIME types and default format based on given formats.
     *
     * @param array $formats
     */
    public function __construct(array $formats)
    {
        foreach ($formats as $format => $mimeTypes) {
            if (null === $this->defaultFormat) {
                $this->defaultFormat = $format;
            }

            foreach ((array) $mimeTypes as $mimeType) {
                $this->mimeTypes[$mimeType] = $format;
            }
        }
    }

    /**
     * Set format on request.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        // Try to guess using headers
        foreach (static::$headers as $header) {
            // Retrieve MIME type from request header
            $mimeType = $request->headers->get($header);

            // Skip if header not set
            if (null === $mimeType) {
                continue;
            }

            // Throw exception if not supported
            if (!isset($this->mimeTypes[(string) $mimeType])) {
                throw $this->unsupportedRequestFormat($mimeType);
            }

            // Set format on request
            $request->setRequestFormat($this->mimeTypes[(string) $mimeType]);

            return;
        }

        // Fallback to default format
        $request->setRequestFormat($this->defaultFormat);
    }

    /**
     * Create unsupported request format exception.
     *
     * @param string $format
     *
     * @return \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     */
    private function unsupportedRequestFormat(string $format): UnsupportedRequestFormatException
    {
        return new UnsupportedRequestFormatException(\sprintf(
            'Unsupported requested format "%s". Supported formats: [%s].',
            $format,
            \implode(', ', \array_keys($this->mimeTypes))
        ));
    }
}
