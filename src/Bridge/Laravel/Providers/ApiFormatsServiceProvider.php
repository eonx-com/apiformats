<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Providers;

use EoneoPay\ApiFormats\Bridge\Laravel\Interfaces\ApiFormatsServiceProviderInterface;
use EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface;
use EoneoPay\ApiFormats\External\Libraries\Psr7\Psr7Factory;
use EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface;
use EoneoPay\ApiFormats\EncoderGuesser;
use Illuminate\Support\ServiceProvider;

class ApiFormatsServiceProvider extends ServiceProvider implements ApiFormatsServiceProviderInterface
{
    public const CONFIG_PATH = __DIR__ . '/../config/api-formats.php';

    /**
     * Boot api formats services.
     */
    public function boot(): void
    {
        $this->publishes([self::CONFIG_PATH => $this->app->basePath() . '/config/api-formats.php']);
    }

    /**
     * Register api formats services.
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    public function register(): void
    {
        // Merge config to have default values if not set
        $this->mergeConfigFrom(self::CONFIG_PATH, 'api-formats');

        $this->app->singleton(EncoderGuesserInterface::class, $this->getRequestEncoderGuesserClosure());
        $this->app->bind(Psr7FactoryInterface::class, Psr7Factory::class);
    }

    /**
     * Return closure to instantiate request encoder guesser.
     *
     * @return \Closure
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \EoneoPay\ApiFormats\Exceptions\InvalidSupportedRequestFormatsConfigException
     */
    private function getRequestEncoderGuesserClosure(): \Closure
    {
        return function () {
            return new EncoderGuesser($this->app->get('config')->get('api-formats.formats', []));
        };
    }
}
