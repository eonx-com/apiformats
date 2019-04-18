<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\Encoders;

use EoneoPay\ApiFormats\Encoders\JsonApiEncoder;
use EoneoPay\ApiFormats\Encoders\JsonEncoder;
use EoneoPay\ApiFormats\Encoders\UrlEncodedDataEncoder;
use EoneoPay\ApiFormats\Encoders\XmlEncoder;
use Tests\EoneoPay\ApiFormats\TestCases\RequestEncoderGuesserTestCase;

class RequestEncodersTest extends RequestEncoderGuesserTestCase
{
    /**
     * @var string[]
     */
    private static $encoders = [
        JsonApiEncoder::class,
        JsonEncoder::class,
        UrlEncodedDataEncoder::class,
        XmlEncoder::class
    ];

    /**
     * Encoders::decode should return array no matters the input.
     *
     * @return void
     */
    public function testDecodeReturnsArray(): void
    {
        foreach (static::$encoders as $encoderClass) {
            /** @var \EoneoPay\ApiFormats\Interfaces\EncoderInterface $encoder */
            $encoder = new $encoderClass();

            foreach ($this->getEncodersTests() as $test) {
                $encoder->setContent($encoder->encode($test)->getBody()->getContents())->decode();
            }
        }

        // If no exceptions were thrown, test is good
        $this->addToAssertionCount(1);
    }

    /**
     * Encoders::encode should return ResponseInterface no matters the input.
     *
     * @return void
     */
    public function testEncodeReturnsResponseInterface(): void
    {
        foreach (static::$encoders as $encoderClass) {
            /** @var \EoneoPay\ApiFormats\Interfaces\EncoderInterface $encoder */
            $encoder = new $encoderClass($this->getRequest());

            foreach ($this->getEncodersTests() as $test) {
                $encoder->encode($test);
            }
        }

        // If no exceptions were thrown, test is good
        $this->addToAssertionCount(1);
    }
}
