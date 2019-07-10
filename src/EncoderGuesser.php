<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats;

use EoneoPay\ApiFormats\Encoders\JsonEncoder;
use EoneoPay\ApiFormats\Exceptions\InvalidEncoderException;
use EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedAcceptHeaderException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedContentTypeHeaderException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface;
use EoneoPay\ApiFormats\Interfaces\EncoderInterface;
use Psr\Http\Message\ServerRequestInterface;

class EncoderGuesser implements EncoderGuesserInterface
{
    /**
     * @var string[]
     */
    private static $headers = ['accept', 'content-type'];

    /**
     * @var string
     */
    private $defaultEncoder;

    /**
     * @var string[]
     */
    private $formats;

    /**
     * @var string[]
     */
    private $mimeTypes = [];

    /**
     * EncoderGuesser constructor.
     *
     * @param mixed[] $formats
     * @param null|string $defaultEncoder
     */
    public function __construct(array $formats, ?string $defaultEncoder = null)
    {
        $this->formats = $formats;
        $this->defaultEncoder = $defaultEncoder ?? JsonEncoder::class;
    }

    /**
     * Get default encoder when formats configuration invalid.
     *
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     */
    public function defaultEncoder(?ServerRequestInterface $request = null): EncoderInterface
    {
        return $this->instantiateEncoder($this->defaultEncoder, $request);
    }

    /**
     * Guess format based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param null|string[] $headers
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function guessEncoder(ServerRequestInterface $request, ?array $headers = null): EncoderInterface
    {
        $mimeType = $this->guessMimeType($request, $headers);

        // Get encoder for this mimetype
        $encoderClass = $this->getEncoderClass($mimeType);

        // Throw exception if mime type not supported
        if ($encoderClass === null) {
            throw new UnsupportedRequestFormatException(\sprintf(
                'Unsupported requested format "%s". Supported formats: [%s].',
                $mimeType,
                \implode(', ', \array_keys($this->mimeTypes))
            ));
        }

        // Instantiate encoder
        return $this->instantiateEncoder($encoderClass, $request);
    }

    /**
     * Guess request encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedContentTypeHeaderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     */
    public function guessRequestEncoder(ServerRequestInterface $request): EncoderInterface
    {
        $mimeType = $this->guessMimeType($request, $this->getHeaderWithFallbacks('content-type'));

        // Get encoder for this mimetype
        $encoderClass = $this->getEncoderClass($mimeType);

        // Throw exception if mime type not supported
        if ($encoderClass === null) {
            throw new UnsupportedContentTypeHeaderException(\sprintf(
                'Unsupported Content-Type header value "%s". Supported values are: [%s].',
                $mimeType,
                \implode(', ', \array_keys($this->mimeTypes))
            ));
        }

        // Instantiate encoder
        return $this->instantiateEncoder($encoderClass, $request);
    }

    /**
     * Guess response encoder based on given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedAcceptHeaderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     */
    public function guessResponseEncoder(ServerRequestInterface $request): EncoderInterface
    {
        $mimeType = $this->guessMimeType($request, $this->getHeaderWithFallbacks('accept'));

        // Get encoder for this mimetype
        $encoderClass = $this->getEncoderClass($mimeType);

        // Throw exception if mime type not supported
        if ($encoderClass === null) {
            throw new UnsupportedAcceptHeaderException(\sprintf(
                'Unsupported Accept header value "%s". Supported values are: [%s].',
                $mimeType,
                \implode(', ', \array_keys($this->mimeTypes))
            ));
        }

        // Instantiate encoder
        return $this->instantiateEncoder($encoderClass, $request);
    }

    /**
     * Get encoder class based on given MIME type.
     *
     * @param string|null $requestedMimeType The requested mime type
     *
     * @return null|string
     */
    private function getEncoderClass(?string $requestedMimeType = null): ?string
    {
        // If there is no mimetype, use default encoder
        if ($requestedMimeType === null) {
            return $this->defaultEncoder;
        }

        // Get encoder for this mimetype
        foreach ($this->mimeTypes as $mimeType => $encoderClass) {
            if (\preg_match(\sprintf('#%s#i', $mimeType), $requestedMimeType) === 1) {
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
     * @return string[]
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
     * Get requested mimetype from a header
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param null|string[] $headers
     *
     * @return string|null
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    private function guessMimeType(ServerRequestInterface $request, ?array $headers = null): ?string
    {
        $this->validateFormats($this->formats);
        $this->setMimeTypes($this->formats);

        // Try to guess using headers
        foreach ($headers ?? static::$headers as $headerName) {
            $header = $request->getHeader($headerName);
            $mimeType = (string)\reset($header);

            // Skip if header not set or invalid
            if ($mimeType === '*/*' || $mimeType === '' || \count($header) === 0) {
                continue;
            }

            return $mimeType;
        }

        // Mime type not found in headers
        return null;
    }

    /**
     * Instantiate the encoder based on the class and the request.
     *
     * @param string $encoderClass
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \EoneoPay\ApiFormats\Interfaces\EncoderInterface
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     */
    private function instantiateEncoder(string $encoderClass, ?ServerRequestInterface $request = null): EncoderInterface
    {
        if (\class_exists($encoderClass) === false) {
            throw new InvalidEncoderException(\sprintf('Encoder "%s" does not exist', $encoderClass));
        }

        $encoder = new $encoderClass($request);

        if (($encoder instanceof EncoderInterface) === false) {
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
     * @param mixed[] $formats
     *
     * @return void
     */
    private function setMimeTypes(array $formats): void
    {
        foreach ($formats as $encoder => $mimeTypes) {
            /** @var array $mimeTypes */
            foreach ($mimeTypes as $mimeType) {
                $this->mimeTypes[$mimeType] = $encoder;
            }
        }
    }

    /**
     * Validate supported formats array.
     *
     * @param mixed[] $formats
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    private function validateFormats(array $formats): void
    {
        if (\count($formats) === 0) {
            throw new InvalidSupportedRequestFormatsConfigException('No supported request formats configured');
        }

        foreach ($formats as $encoder => $mimeTypes) {
            if (\is_string($encoder) === false) {
                throw new InvalidSupportedRequestFormatsConfigException(\sprintf(
                    'Supported format name has to be a string, %s given.',
                    \gettype($encoder)
                ));
            }

            if (\is_array($mimeTypes) === false) {
                throw new InvalidSupportedRequestFormatsConfigException(\sprintf(
                    'Supported MIME types has to be an array, "%s" => %s given.',
                    $encoder,
                    \gettype($mimeTypes)
                ));
            }
        }
    }
}
