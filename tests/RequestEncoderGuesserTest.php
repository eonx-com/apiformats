<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats;

use EoneoPay\ApiFormats\Exceptions\InvalidEncoderException;
use EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException;
use EoneoPay\ApiFormats\RequestEncoderGuesser;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
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
}
