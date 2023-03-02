<?php declare(strict_types=1);

/**
 * The Fuel PHP Framework is a fast, simple and flexible development framework
 *
 * @package    fuel
 * @version    2.0.0
 * @author     FlexCoders Ltd, Fuel The PHP Framework Team
 * @license    MIT License
 * @copyright  2023 FlexCoders Ltd, The Fuel PHP Framework Team
 * @link       https://fuelphp.org
 */

namespace Fuel\Helpers\Providers;

use Fuel\Container\ServiceProvider\{AbstractServiceProvider, BootableServiceProviderInterface};

use function array_merge;
use function in_array;

/**
 * Fuel\Framework Service Provider
 *
 * @since 2.0
 */
class FuelServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * this method has access to the container itself and can interact
     * with it however you wish, the difference is that the boot method
     * is invoked as soon as you register the service provider with the
     *  ontainer meaning that everythingin this method is eagerly loaded.
     *
     * If you wish to apply inflectors or register further service providers
     * from this one, it must be from a bootable service provider like
     * this one, otherwise they will be ignored.
     */
    public function boot(): void
    {
    }

    /**
     * The provides method is a way to let the container
     * know that a service is provided by this service
     * provider. Every service that is registered via
     * this service provider must have an alias added
     * to this array or it will be ignored.
     */
    public function provides(string $id): bool
    {
        $services = [
        ];

        return in_array($id, $services);
    }

    /**
     * The register method is where you define services
     * in the same way you would directly with the container.
     * A convenience getter for the container is provided, you
     * can invoke any of the methods you would when defining
     * services directly, but remember, any alias added to the
     * container here, when passed to the `provides` nethod
     * must return true, or it will be ignored by the container.
     */
    public function register(): void
    {
    }
}
