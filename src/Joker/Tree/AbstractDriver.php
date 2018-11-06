<?php

namespace MrsJoker\Tree;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Tree\Exception\TreeException;

abstract class AbstractDriver
{
    const ITEM_ALL_KEY = 'abstract_driver_tree_item_all_key';

    public $model;

    public $config;

    abstract protected function newItem($itemData);

    abstract protected function editItem($itemData);

    abstract protected function getItemData($id);

    abstract protected function delItemData($id);

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\' . ltrim($this->model, '\\');

        return new $class;
    }



    /**
     * Initiates new image from given input
     *
     * @param  mixed $data
     * @return
     */
    public function init($data, $config)
    {

        if (is_string($data)) {
            if (isset($config['tables']["{$data}"])){
                $this->model = $config['tables']["{$data}"];
                $this->config = $config;
                return $this;
            }
        }
        throw new TreeException("Model ({$data}) could not be instantiated.");


//        if (is_string($this->config['driver'])) {
//            $drivername = ucfirst($this->config['driver']);
//            $driverclass = sprintf('Intervention\\Image\\%s\\Driver', $drivername);
//
//            if (class_exists($driverclass)) {
//                return new $driverclass;
//            }
//
//            throw new \Intervention\Image\Exception\NotSupportedException(
//                "Driver ({$drivername}) could not be instantiated."
//            );
//        }
//
//        if ($this->config['driver'] instanceof AbstractDriver) {
//            return $this->config['driver'];
//        }
//
//        throw new \Intervention\Image\Exception\NotSupportedException(
//            "Unknown driver type."
//        );
//

//        $this->model = $data;
//        return $this->config['driver'];
    }

    /**
     * 拉取缓存数据
     * @return mixed
     */
    public function cachedItems(){

        $model = $this->createModel();
        $tableName = $model->getTable();

        $cacheKey = self::ITEM_ALL_KEY.$tableName;
        return Cache::tags([$tableName])->remember($cacheKey, 60*24*30, function () use ($model) {
            return $model->orderBy('order_num','ASC')->get();
        });
    }

    /**
     * Executes named command on given image
     *
     * @param  Image $image
     * @param  string $name
     * @param  array $arguments
     * @return \Intervention\Image\Commands\AbstractCommand
     */
    public function executeCommand($image, $name, $arguments)
    {
        $commandName = $this->getCommandClassName($name);
        $command = new $commandName($arguments);
        $command->execute($image);

        return $command;
    }

    /**
     * Returns classname of given command name
     *
     * @param  string $name
     * @return string
     */
    private function getCommandClassName($name)
    {
        $name = mb_convert_case($name[0], MB_CASE_UPPER, 'utf-8') . mb_substr($name, 1, mb_strlen($name));

        $drivername = $this->getDriverName();
        $classnameLocal = sprintf('\Intervention\Image\%s\Commands\%sCommand', $drivername, ucfirst($name));
        $classnameGlobal = sprintf('\Intervention\Image\Commands\%sCommand', ucfirst($name));

        if (class_exists($classnameLocal)) {
            return $classnameLocal;
        } elseif (class_exists($classnameGlobal)) {
            return $classnameGlobal;
        }

        throw new \Intervention\Image\Exception\NotSupportedException(
            "Command ({$name}) is not available for driver ({$drivername})."
        );
    }

    /**
     * Returns name of current driver instance
     *
     * @return string
     */
    public function getDriverName()
    {
        $reflect = new \ReflectionClass($this);
        $namespace = $reflect->getNamespaceName();

        return substr(strrchr($namespace, "\\"), 1);
    }
}
