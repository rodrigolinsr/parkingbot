<?php
namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Class PackageServiceProvider
 *
 * @package App\Providers
 */
abstract class PackageServiceProvider extends ServiceProvider
{

    /**
     * Override the derived package name
     *
     * @var null
     */
    protected $packageName = null;

    /**
     * Load views for this package from a Views folder, and use the package name as the namespace for them
     *
     * @var bool
     */
    protected $loadViews = false;

    /**
     * An array of events, and the listeners which should run on that event.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * @var array
     */
    protected $subscribe = [];

    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var array
     */
    protected $policies = [];

    /**
     * @var
     */
    private $packageRootDir;

    /**
     * Package service provider boot
     */
    public function boot()
    {
        /**
         * If the package name has not been set by the service provider then we assume the package has a
         * service provider named ExamplePackageServiceProvider and set the package name to be the
         * snake case version. So in this case we'd set packageName to example_package.
         */
        if (is_null($this->packageName)) {
            $classBasename = class_basename($this);
            if (ends_with($classBasename, 'ServiceProvider')) {
                $this->packageName = snake_case(str_replace("ServiceProvider", "", $classBasename));
            }
        }

        /**
         * If the loadViews is set to true, then look for a folder called Views in the package folder
         * and load the views from it using the snake_case package name
         */
        if (!is_null($this->packageName) && $this->loadViews === true) {
            $viewsDirectory = $this->getViewsDirectoryForPackage($this->packageName);

            abort_if(!is_dir($viewsDirectory), 500, "Views folder not created for " . $this->packageName);

            $this->loadViewsFrom($viewsDirectory, $this->packageName);
        }

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            Event::subscribe($subscriber);
        }
    }

    /**
     * PackageServiceProvider Registration
     */
    public function register()
    {
        $this->commands($this->commands);

        if (!empty($this->policies)) {
            foreach ($this->policies as $key => $value) {
                Gate::policy($key, $value);
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getViewsDirectoryForPackage()
    {
        return $this->getPackageRootDir() . '/Views';
    }

    /**
     * @return string
     */
    public function getPackageRootDir(): string
    {
        if (null === $this->packageRootDir) {
            $cacheKey      = sprintf('package:%s:root_dir' . uniqid(), $this->packageName);
            $cacheLifetime = 1440; // 1440 minutes is 24 hours

            $r              = new \ReflectionClass($this);
            $packageRootDir = realpath(dirname($r->getFileName()) . '/..');

            if (false === $packageRootDir) {
                throw new \RuntimeException(sprintf('Could not determine package root dir for "%s".', static::class));
            }

            $this->packageRootDir = $packageRootDir;
        }

        return $this->packageRootDir;
    }
}