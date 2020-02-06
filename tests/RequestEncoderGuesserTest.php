<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats;

use EoneoPay\ApiFormats\EncoderGuesser;
use EoneoPay\ApiFormats\Encoders\JsonApiEncoder;
use EoneoPay\ApiFormats\Encoders\JsonEncoder;
use EoneoPay\ApiFormats\Encoders\XmlEncoder;
use EoneoPay\ApiFormats\Exceptions\DecodeNullRequestException;
use EoneoPay\ApiFormats\Exceptions\InvalidEncoderException;
use EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException;
use EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException;
use Tests\EoneoPay\ApiFormats\Stubs\EncoderWithoutInterface;
use Tests\EoneoPay\ApiFormats\TestCases\RequestEncoderGuesserTestCase;

class RequestEncoderGuesserTest extends RequestEncoderGuesserTestCase
{
    /**
     * Encoder should throw exception when trying to decode on null request.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     */
    public function testDecodeNullRequestException(): void
    {
        $this->expectException(DecodeNullRequestException::class);

        (new EncoderGuesser([JsonEncoder::class => ['application/json']]))->defaultEncoder()->decode();
    }

    /**
     * Guesser should throw exception if formats array is empty.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function testEmptyFormatsException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        (new EncoderGuesser([]))->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should throw exception if encoder class doesn't exist.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function testEncoderClassDoesntExistException(): void
    {
        $this->expectException(InvalidEncoderException::class);

        $formats = ['invalid_class' => ['application/json']];

        (new EncoderGuesser($formats))->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should throw exception if encoder doesn't implement request encoder interface.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function testEncoderDoesntImplementRightInterfaceException(): void
    {
        $this->expectException(InvalidEncoderException::class);

        $formats = [EncoderWithoutInterface::class => ['application/json']];

        (new EncoderGuesser($formats))->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should guess encoder based on regex.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function testEncoderGuessedUsingRegex(): void
    {
        $formats = [
            JsonEncoder::class => [
                'application/json',
                'application/vnd.eoneopay.v[0-9]+\+json'
            ],
            XmlEncoder::class => [
                '(application|text)/xml'
            ],
            JsonApiEncoder::class => [
                'application/vnd.api\+json',
                'application/vnd.eoneopay.v[0-9]+.api\+json'
            ]
        ];

        $tests = [
            JsonEncoder::class => [
                'application/json',
                'application/vnd.eoneopay.v1+json',
                'application/vnd.eoneopay.v2+json',
                'application/vnd.eoneopay.v3+json',
                'application/vnd.eoneopay.v31+json'
            ],
            XmlEncoder::class => [
                'application/xml',
                'text/xml'
            ],
            JsonApiEncoder::class => [
                'application/vnd.api+json',
                'application/vnd.eoneopay.v1.api+json',
                'application/vnd.eoneopay.v2.api+json',
                'application/vnd.eoneopay.v3.api+json',
                'application/vnd.eoneopay.v31.api+json'
            ]
        ];

        $guesser = new EncoderGuesser($formats);

        /** @var mixed[] $mimeTypes */
        foreach ($tests as $encoderClass => $mimeTypes) {
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
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedAcceptHeaderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedContentTypeHeaderException
     */
    public function testEncoderGuesserReturnsTwoDifferentEncodersBasedOnHeaders(): void
    {
        $formats = [
            JsonEncoder::class => ['application/json'],
            XmlEncoder::class => ['(application|text)/xml']
        ];

        $request = $this->getRequest(null, 'application/xml');
        $guesser = new EncoderGuesser($formats);

        /** @noinspection UnnecessaryAssertionInspection Return type hint is interface not specific class */
        self::assertInstanceOf(XmlEncoder::class, $guesser->guessRequestEncoder($request));
        /** @noinspection UnnecessaryAssertionInspection Return type hint is interface not specific class */
        self::assertInstanceOf(JsonEncoder::class, $guesser->guessResponseEncoder($request));
    }

    /**
     * Guesser should throw exception if encoder class is invalid
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function testEncoderUnsupportedRequestFormatException(): void
    {
        $this->expectException(UnsupportedRequestFormatException::class);

        $formats = [
            JsonEncoder::class => ['application/json'],
            XmlEncoder::class => ['(application|text)/xml']
        ];

        $request = $this->getRequest('invalid');

        (new EncoderGuesser($formats))->guessEncoder($request);
    }

    /**
     * Guesser should throw exception if formats mime types are not array.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function testNotArrayMimeTypesException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        (new EncoderGuesser([JsonEncoder::class => 'application/json']))
            ->guessEncoder($this->getRequest());
    }

    /**
     * Guesser should throw exception if formats array keys are numeric.
     *
     * @return void
     *
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidEncoderException
     * @throws \EoneoPay\ApiFormats\Exceptions\UnsupportedRequestFormatException
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function testNumericKeysForFormatsException(): void
    {
        $this->expectException(InvalidSupportedRequestFormatsConfigException::class);

        (new EncoderGuesser([['application/json']]))->guessEncoder($this->getRequest());
    }
}
