<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats;

use EoneoPay\ApiFormats\Exceptions\InvalidEncoderException;
use EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException;
use EoneoPay\ApiFormats\RequestEncoderGuesser;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\XmlRequestEncoder;
use PHPUnit\Util\Xml;
use Tests\EoneoPay\ApiFormats\Stubs\EncoderWithoutInterface;
use Tests\EoneoPay\ApiFormats\TestCases\RequestEncoderGuesserTestCase;

class RequestEncoderGuesserTest extends RequestEncoderGuesserTestCase
{
    /**
     * Guesser should throw exception if formats array is empty.
     */
    public function testEmptyFormatsException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        new RequestEncoderGuesser([]);
    }

    /**
     * Guesser should throw exception if encoder class doesn't exist.
     */
    public function testEncoderClassDoesntExistException(): void
    {
        $this->expectException(InvalidEncoderException::class);

        $formats = ['invalid_class' => ['application/json']];
        $request = $this->getRequest();

        (new RequestEncoderGuesser($formats))->guessEncoder($request);
    }

    /**
     * Guesser should throw exception if encoder doesn't implement request encoder interface.
     */
    public function testEncoderDoesntImplementRightInterfaceException(): void
    {
        $this->expectException(InvalidEncoderException::class);

        $formats = [EncoderWithoutInterface::class => ['application/json']];
        $request = $this->getRequest();

        (new RequestEncoderGuesser($formats))->guessEncoder($request);
    }

    /**
     * Guesser should throw exception if formats mime types are not array.
     */
    public function testNotArrayMimeTypesException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        new RequestEncoderGuesser([JsonRequestEncoder::class => 'application/json']);
    }

    /**
     * Guesser should throw exception if formats array keys are numeric.
     */
    public function testNumericKeysForFormatsException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        new RequestEncoderGuesser([['application/json']]);
    }

    /**
     * Guesser should guess encoder based on regex.
     */
    public function testEncoderGuessedUsingRegex(): void
    {
        $formats = [
            JsonRequestEncoder::class => [
                'application/json',
                'application/vnd.eoneopay.v[0-9]+\+json'
            ],
            XmlRequestEncoder::class => [
                '(application|text)/xml'
            ]
        ];

        $tests = [
            JsonRequestEncoder::class => [
                'application/json',
                'application/vnd.eoneopay.v1+json',
                'application/vnd.eoneopay.v2+json',
                'application/vnd.eoneopay.v3+json',
                'application/vnd.eoneopay.v31+json'
            ],
            XmlRequestEncoder::class => [
                'application/xml',
                'text/xml'
            ]
        ];

        $guesser = new RequestEncoderGuesser($formats);

        foreach ($tests as $encoderClass => $mimeTypes) {
            /** @var array $mimeTypes */
            foreach ($mimeTypes as $mimeType) {
                self::assertInstanceOf($encoderClass, $guesser->guessEncoder($this->getRequest($mimeType)));
            }
        }
    }
}
