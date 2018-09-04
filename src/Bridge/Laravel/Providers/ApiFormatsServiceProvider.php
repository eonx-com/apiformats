<?php
declare(strict_types=1);

namespace EoneoPay\ApiFormats\Bridge\Laravel\Providers;

use EoneoPay\ApiFormats\Bridge\Laravel\Interfaces\ApiFormatsServiceProviderInterface;
use EoneoPay\ApiFormats\EncoderGuesser;
use EoneoPay\ApiFormats\External\Interfaces\Psr7\Psr7FactoryInterface;
use EoneoPay\ApiFormats\External\Libraries\Psr7\Psr7Factory;
use EoneoPay\ApiFormats\Interfaces\EncoderGuesserInterface;
use Illuminate\Support\ServiceProvider;

class ApiFormatsServiceProvider extends ServiceProvider implements ApiFormatsServiceProviderInterface
{
    public const CONFIG_PATH = __DIR__ . '/../config/api-formats.php';

    /**
     * Boot api formats services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([self::CONFIG_PATH => $this->app->basePath() . '/config/api-formats.php']);
    }

    /**
     * Register api formats services.
     *
     * @return void
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
     */
    private function getRequestEncoderGuesserClosure(): \Closure
    {
        return function (): EncoderGuesser {
            $config = $this->app->get('config');

            return new EncoderGuesser(
                $config->get('api-formats.formats', []),
                $config->get('api-formats.default', null)
            );
        };
    }
}
