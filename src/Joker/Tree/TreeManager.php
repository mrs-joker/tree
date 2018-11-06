<?php

namespace MrsJoker\Tree;

use Closure;
use MrsJoker\Tree\Exception\TreeException;

class TreeManager
{
    /**
     * Config
     *
     * @var array
     */
    public $config = [
        'driver' => 'mysql'
    ];

    /**
     * Creates new instance of Image Manager
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->checkRequirements();
        $this->configure($config);
    }

    /**
     * Overrides configuration settings
     *
     * @param array $config
     *
     * @return self
     */
    public function configure(array $config = [])
    {
        $this->config = array_replace($this->config, $config);

        return $this;
    }

    /**
     * Initiates an Image instance from different input types
     *
     * @param  mixed $data
     *
     */
    public function make($data)
    {
        return $this->createDriver()->init($data, $this->config);
    }

    /**
     * Creates a driver instance according to config settings
     * @return mixed
     * @throws TreeException
     */
    private function createDriver()
    {
        if (is_string($this->config['driver'])) {
            $drivername = ucfirst($this->config['driver']);
            $driverclass = sprintf('MrsJoker\\Tree\\%s\\Driver', $drivername);

            if (class_exists($driverclass)) {
                return new $driverclass;
            }

            throw new TreeException("Driver ({$drivername}) could not be instantiated.");
        }

        if ($this->config['driver'] instanceof AbstractDriver) {
            return $this->config['driver'];
        }
        throw new TreeException(
            "Unknown driver type."
        );
    }

    /**
     * Check if all requirements are available
     *
     * @return void
     */
    private function checkRequirements()
    {
        return true;
//        if ( ! function_exists('finfo_buffer')) {
//            throw new \Intervention\Image\Exception\MissingDependencyException(
//                "PHP Fileinfo extension must be installed/enabled to use Intervention Image."
//            );
//        }
    }
}
