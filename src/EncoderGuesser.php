<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats;

use EoneoPay\ApiFormats\Exceptions\InvalidEncoderException;
use EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface;
use EoneoPay\ApiFormats\Interfaces\EncoderInterface;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
use Psr\Http\Message\ServerRequestInterface;

class EncoderGuesser implements EncoderGuesserInterface
{
    /**
     * @var array
     */
    private static $headers = ['accept', 'content-type'];

    /**
     * @var string
     */
    private $defaultEncoder;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var array
     */
    private $mimeTypes = [];

    /**
     * RequestFormatGuesser constructor.
     *
     * @param array $formats
     */
    public function __construct(array $formats)
    {
        $this->formats = $formats;
    }

    /**
     * Get default encoder when formats configuration invalid.
     *
     * @param null|ServerRequestInterface $request
     *
     * @return EncoderInterface
     */
    public function defaultEncoder(?ServerRequestInterface $request = null): EncoderInterface
    {
        return new JsonRequestEncoder($request);
    }

    /**
     * Guess format based on given request.
     *
     * @param ServerRequestInterface $request
     * @param null|array $headers
     *
     * @return EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    public function guessEncoder(ServerRequestInterface $request, ?array $headers = null): EncoderInterface
    {
        $this->validateFormats($this->formats);
        $this->setMimeTypes($this->formats);

        // Try to guess using headers
        foreach ($headers ?? static::$headers as $headerName) {
            $header = $request->getHeader($headerName);

            // Skip if header not set
            if (empty($header) || '*/*' === \reset($header)) {
                continue;
            }

            // Retrieve MIME type from request header
            $mimeType = (string)\reset($header);
            // Get encoder class to use
            $encoderClass = $this->getEncoderClass($mimeType);

            // Throw exception if not supported
            if (null === $encoderClass) {
                throw new UnsupportedRequestFormatException(\sprintf(
                    'Unsupported requested format "%s". Supported formats: [%s].',
                    $mimeType,
                    \implode(', ', \array_keys($this->mimeTypes))
                ));
            }

            return $this->instantiateEncoder($encoderClass, $request);
        }

        // Fallback to default format
        return $this->instantiateEncoder($this->defaultEncoder, $request);
    }

    /**
     * Guess request encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     */
    public function guessRequestEncoder(ServerRequestInterface $request): EncoderInterface
    {
        return $this->guessEncoder($request, $this->getHeaderWithFallbacks('content-type'));
    }

    /**
     * Guess response encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     */
    public function guessResponseEncoder(ServerRequestInterface $request): EncoderInterface
    {
        return $this->guessEncoder($request, $this->getHeaderWithFallbacks('accept'));
    }

    /**
     * Get encoder class based on given MIME type.
     *
     * @param string $requestMimeType
     *
     * @return null|string
     */
    private function getEncoderClass(string $requestMimeType): ?string
    {
        foreach ($this->mimeTypes as $mimeType => $encoderClass) {
            if (\preg_match(\sprintf('#%s#i', $mimeType), $requestMimeType)) {
                return $encoderClass;
            }
        }

        return null;
    }

    /**
     * Returns a list of header names with the given one as primary (first element).
     *
     * @param string $primaryHeader
     *
     * @return array
     */
    private function getHeaderWithFallbacks(string $primaryHeader): array
    {
        $headers = [$primaryHeader];

        foreach (static::$headers as $fallbackHeader) {
            if ($fallbackHeader === $primaryHeader) {
                continue;
            }

            $headers[] = $fallbackHeader;
        }

        return $headers;
    }

    /**
     * Instantiate the encoder based on the class and the request.
     *
     * @param string $encoderClass
     * @param ServerRequestInterface $request
     *
     * @return EncoderInterface
     *
     * @throws InvalidEncoderException
     */
    private function instantiateEncoder(string $encoderClass, ServerRequestInterface $request): EncoderInterface
    {
        if (!\class_exists($encoderClass)) {
            throw new InvalidEncoderException(\sprintf('Encoder "%s" does not exist', $encoderClass));
        }

        $encoder = new $encoderClass($request);

        if (!$encoder instanceof EncoderInterface) {
            throw new InvalidEncoderException(\sprintf(
                'Encoder "%s" does not implement %s',
                $encoderClass,
                EncoderInterface::class
            ));
        }

        return $encoder;
    }

    /**
     * Set mimeTypes based on given formats.
     *
     * @param array $formats
     *
     * @return void
     */
    private function setMimeTypes(array $formats): void
    {
        foreach ($formats as $encoder => $mimeTypes) {
            if (null === $this->defaultEncoder) {
                $this->defaultEncoder = $encoder;
            }

            /** @var array $mimeTypes */
            foreach ($mimeTypes as $mimeType) {
                $this->mimeTypes[$mimeType] = $encoder;
            }
        }
    }

    /**
     * Validate supported formats array.
     *
     * @param array $formats
     *
     * @return void
     *
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    private function validateFormats(array $formats): void
    {
        if (empty($formats)) {
            throw new InvalidSupportedRequestFormatsConfigException('No supported request formats configured');
        }

        foreach ($formats as $encoder => $mimeTypes) {
            if (!\is_string($encoder)) {
                throw new InvalidSupportedRequestFormatsConfigException(\sprintf(
                    'Supported format name has to be a string, %s given.',
                    \gettype($encoder)
                ));
            }

            if (!\is_array($mimeTypes)) {
                throw new InvalidSupportedRequestFormatsConfigException(\sprintf(
                    'Supported MIME types has to be an array, "%s" => %s given.',
                    $encoder,
                    \gettype($mimeTypes)
                ));
            }
        }
    }
}
