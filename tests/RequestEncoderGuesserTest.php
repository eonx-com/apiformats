<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats;

use EoneoPay\ApiFormats\Exceptions\DecodeNullRequestException;
use EoneoPay\ApiFormats\Exceptions\InvalidEncoderException;
use EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use EoneoPay\ApiFormats\RequestEncoderGuesser;
use EoneoPay\ApiFormats\RequestEncoders\JsonApiRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\XmlRequestEncoder;
use Tests\EoneoPay\ApiFormats\Stubs\EncoderWithoutInterface;
use Tests\EoneoPay\ApiFormats\TestCases\RequestEncoderGuesserTestCase;

class RequestEncoderGuesserTest extends RequestEncoderGuesserTestCase
{
    /**
     * Encoder should throw exception when trying to decode on null request.
     *
     * @return void
     */
    public function testDecodeNullRequestException(): void
    {
        $this->expectException(DecodeNullRequestException::class);

        (new RequestEncoderGuesser([JsonRequestEncoder::class => ['application/json']]))->defaultEncoder()->decode();
    }

    /**
     * Guesser should throw exception if formats array is empty.
     *
     * @return void
     *
     * @throws InvalidEncoderException
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    public function testEmptyFormatsException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        (new RequestEncoderGuesser([]))->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should throw exception if encoder class doesn't exist.
     *
     * @return void
     *
     * @throws InvalidEncoderException
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    public function testEncoderClassDoesntExistException(): void
    {
        $this->expectException(InvalidEncoderException::class);

        $formats = ['invalid_class' => ['application/json']];

        (new RequestEncoderGuesser($formats))->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should throw exception if encoder doesn't implement request encoder interface.
     *
     * @return void
     *
     * @throws InvalidEncoderException
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    public function testEncoderDoesntImplementRightInterfaceException(): void
    {
        $this->expectException(InvalidEncoderException::class);

        $formats = [EncoderWithoutInterface::class => ['application/json']];

        (new RequestEncoderGuesser($formats))->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should guess encoder based on regex.
     *
     * @return void
     *
     * @throws InvalidEncoderException
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
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
            ],
            JsonApiRequestEncoder::class => [
                'application/vnd.api\+json',
                'application/vnd.eoneopay.v[0-9]+.api\+json'
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
            ],
            JsonApiRequestEncoder::class => [
                'application/vnd.api+json',
                'application/vnd.eoneopay.v1.api+json',
                'application/vnd.eoneopay.v2.api+json',
                'application/vnd.eoneopay.v3.api+json',
                'application/vnd.eoneopay.v31.api+json'
            ]
        ];

        $guesser = new RequestEncoderGuesser($formats);

        foreach ($tests as $encoderClass => $mimeTypes) {
            /** @var array $mimeTypes */
            foreach ($mimeTypes as $mimeType) {
                /** @noinspection UnnecessaryAssertionInspection Return type hint is interface not specific class */
                self::assertInstanceOf($encoderClass, $guesser->guessEncoder($this->getRequest($mimeType)));
            }
        }
    }

    /**
     * Guesser should return two different encoder objects based on different headers for request and response.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     */
    public function testEncoderGuesserReturnsTwoDifferentEncodersBasedOnHeaders(): void
    {
        $formats = [
            JsonRequestEncoder::class => ['application/json'],
            XmlRequestEncoder::class => ['(application|text)/xml']
        ];

        $request = $this->getRequest(null, 'application/xml');
        $guesser = new RequestEncoderGuesser($formats);

        /** @noinspection UnnecessaryAssertionInspection Return type hint is interface not specific class */
        self::assertInstanceOf(XmlRequestEncoder::class, $guesser->guessRequestEncoder($request));
        /** @noinspection UnnecessaryAssertionInspection Return type hint is interface not specific class */
        self::assertInstanceOf(JsonRequestEncoder::class, $guesser->guessResponseEncoder($request));
    }

    /**
     * Guesser should throw exception if formats mime types are not array.
     *
     * @return void
     *
     * @throws InvalidEncoderException
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    public function testNotArrayMimeTypesException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        (new RequestEncoderGuesser([JsonRequestEncoder::class => 'application/json']))
            ->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should throw exception if formats array keys are numeric.
     *
     * @return void
     *
     * @throws InvalidEncoderException
     * @throws UnsupportedRequestFormatException
     * @throws InvalidSupportedRequestFormatsConfigException
     */
    public function testNumericKeysForFormatsException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        (new RequestEncoderGuesser([['application/json']]))->guessEncoder($this->getRequest());
    }
}
