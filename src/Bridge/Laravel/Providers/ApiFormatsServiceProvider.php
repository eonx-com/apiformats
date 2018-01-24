<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Providers;

use EoneoPay\ApiFormats\Bridge\Laravel\Interfaces\ApiFormatsServiceProviderInterface;
use EoneoPay\ApiFormats\External\Interfaces\Psr7FactoryInterface;
use EoneoPay\ApiFormats\External\Libraries\Psr7Factory;
use EoneoPay\ApiFormats\Interfaces\RequestEncoderGuesserInterface;
use EoneoPay\ApiFormats\RequestEncoderGuesser;
use Illuminate\Support\ServiceProvider;

class ApiFormatsServiceProvider extends ServiceProvider implements ApiFormatsServiceProviderInterface
{
    private const CONFIG_PATH = __DIR__ . '/../config/api-formats.php';

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
        // Load config from config file if created
        $this->app->configure('api-formats');

        $this->app->singleton(RequestEncoderGuesserInterface::class, $this->getRequestEncoderGuesserClosure());
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
            return new RequestEncoderGuesser($this->app->get('config')->get('api-formats.formats', []));
        };
    }
}
