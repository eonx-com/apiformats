<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\RequestEncoders;

use EoneoPay\ApiFormats\RequestEncoders\JsonApiRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\JsonRequestEncoder;
use EoneoPay\ApiFormats\RequestEncoders\XmlRequestEncoder;
use Psr\Http\Message\ResponseInterface;
use Tests\EoneoPay\ApiFormats\TestCases\RequestEncoderGuesserTestCase;

class RequestEncodersTest extends RequestEncoderGuesserTestCase
{
    /**
     * @var array
     */
    private static $encoders = [
        JsonRequestEncoder::class,
        XmlRequestEncoder::class,
        JsonApiRequestEncoder::class
    ];

    /**
     * RequestEncoders::decode should return array no matters the input.
     *
     * @return void
     */
    public function testDecodeReturnsArray(): void
    {
        foreach (static::$encoders as $encoderClass) {
            /** @var \EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface $encoder */
            $encoder = new $encoderClass();

            foreach ($this->getEncodersTests() as $test) {
                self::assertNotEmpty(
                    \is_array($encoder->setContent($encoder->encode($test)->getBody()->getContents())->decode())
                );
            }
        }
    }

    /**
     * RequestEncoders::encode should return ResponseInterface no matters the input.
     *
     * @return void
     */
    public function testEncodeReturnsResponseInterface(): void
    {
        foreach (static::$encoders as $encoderClass) {
            /** @var \EoneoPay\ApiFormats\Interfaces\RequestEncoderInterface $encoder */
            $encoder = new $encoderClass($this->getRequest());

            foreach ($this->getEncodersTests() as $test) {
                self::assertTrue(\is_subclass_of($encoder->encode($test), ResponseInterface::class));
            }
        }
    }
}
