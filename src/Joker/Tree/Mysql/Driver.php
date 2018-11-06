<?php

namespace MrsJoker\Tree\Mysql;

use Illuminate\Support\Facades\Cache;
use MrsJoker\Tree\Exception\TreeException;

class Driver extends \MrsJoker\Tree\AbstractDriver
{
    /**
     * Creates new instance of driver
     *
     * @param Decoder $decoder
     * @param Encoder $encoder
     */
    public function __construct()
    {
        if (!$this->coreAvailable()) {
            throw new TreeException("Tree module not available with this PHP Joker.");
        }
    }

    /**
     * 树状拉取数据
     * @param int $parentId
     * @return array
     */
    public function itemTrees($parentId = 0)
    {

        $items = $this->cachedItems();
        if (empty($items))
            return [];

        $childItems = [];
        foreach ($items as $key => $val) {
            if ($val->parent_id == $parentId) {
                $childItems[$val->id] = $val->toArray();
                $child = $this->itemTrees($val->id);
                if (!empty($child)) {
                    $childItems[$val->id]['child'] = $child;
                }
            }
        }
        return $childItems;
    }


    /**
     * 校验
     * @param $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($data)
    {
        $rules['order_num'] = 'numeric|max:999';
        $rules['name'] = 'required|string|max:255';
        $rules['additional_data'] = [function ($attribute, $value, $fail) {
            if (!is_array($value)) {
                $fail(':attribute must be array!');
            }
        }];

        if (isset($data['id']) && !empty($data['id'])) {
            $model = $this->createModel()->findOrFail($data['id']);
            $rules['parent_id'] = [function ($attribute, $value, $fail) use ($model) {
                if ($model->id == $value) {
                    $fail(':attribute can not be your own father!');
                }
                $dot = array_dot($this->itemTrees($model->id));
                foreach ($dot as $k => $v) {
                    if (str_is('*.id', $k) && $v === (int)$value) {
                        $fail(':attribute needs more cowbell!');
                    }
                }
            }, function ($attribute, $value, $fail) {
                if (empty($this->createModel()->find($value)) && $value != 0) {
                    $fail(':attribute non-existent!');
                }
            }];
            $rules['tree_key'] = [function ($attribute, $value, $fail) use ($model) {
                if ($model->$attribute != $value) {
                    $fail(':attribute can not be change');
                }
            }];

        } else {

            $model = $this->createModel();
            $rules['parent_id'] = [function ($attribute, $value, $fail) use ($model) {
                $find = $model->find($value);
                if (empty($find) && $value != 0) {
                    $fail(':attribute needs more cowbell!');
                }
            }];
            $rules['tree_key'] = "required|unique:{$model->getTable()}|string|max:255";
        }
        return \Illuminate\Support\Facades\Validator::make($data, $rules);
    }

    /**
     * 新加
     * @param $itemData
     * @return bool
     * @throws TreeException
     */
    public function newItem($itemData)
    {
        $itemData['tree_key'] = isset($itemData['tree_key']) && !empty($itemData['tree_key']) ? $itemData['tree_key'] : str_random(32);
        $error = $this->validator($itemData)->errors()->first();
        if (empty($error)) {
            $model = $this->createModel();
            $model->parent_id = $itemData['parent_id'];
            $model->tree_key = $itemData['tree_key'];
            $model->name = $itemData['name'];
            $model->additional_data = isset($itemData['additional_data']) ? $itemData['additional_data'] : [];
            $model->order_num = isset($itemData['order_num']) ? $itemData['order_num'] : 0;

            if ($model->save()) {
                Cache::tags($model->getTable())->flush();
                return true;
            }
            throw new TreeException("The server is busy. Please try again later.");
        }
        throw new TreeException($error);
    }

    /**
     * 修改
     * @param $itemData
     * @return bool
     * @throws TreeException
     */
    public function editItem($itemData)
    {
        if (isset($itemData['id']) && !empty($itemData['id'])) {
            $error = $this->validator($itemData)->errors()->first();
            if (empty($error)) {
                $model = $this->createModel()->find($itemData['id']);
                $model->parent_id = $itemData['parent_id'];
                $model->name = $itemData['name'];
                if (isset($itemData['additional_data']) && is_array($itemData['additional_data'])) {
                    $model->additional_data = $itemData['additional_data'];
                }
                if (isset($itemData['order_num'])) {
                    $model->order_num = $itemData['order_num'];
                }

                if ($model->save()) {
                    Cache::tags($model->getTable())->flush();
                    return true;
                }
                throw new TreeException("The server is busy. Please try again later.");
            }
            throw new TreeException($error);
        }

        throw new TreeException("id can not be null.");
    }


    public function getItemData($id)
    {

        $model = $this->createModel();

        return $model->orderBy('order_num', 'ASC')->get();
    }

    public function delItemData($id)
    {
        $model = $this->createModel();
        $parent = $model->where('parent_id',$id)->first();
        if ($parent){
            throw new TreeException("please delete {$parent->name} frist");
        }
        $current = $model->find($id);
        if (empty($current)){
            throw new TreeException("{$id} not available ");
        }
        if ($current->delete()){
            Cache::tags($model->getTable())->flush();
            return true;
        }
        throw new TreeException("The server is busy. Please try again later.");

    }

    /**
     * Checks if core module installation is available
     *
     * @return boolean
     */
    protected function coreAvailable()
    {
//        return (extension_loaded('imagick') && class_exists('Imagick'));
        return true;
    }
}
