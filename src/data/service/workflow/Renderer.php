<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 15:09
 */
abstract class Renderer
{
    abstract public function input($attribute,$model);
    abstract public function output($attribute,$model);
    public function getValue($attribute,$model)
    {
        $newValue = isset($model['new_value'])?$model['new_value']:array();
        return isset($newValue[$attribute['name']])?$newValue[$attribute['name']]:'';
    }
    public function getOldValue($attribute,$model)
    {
        $newValue = isset($model['old_value'])?$model['old_value']:array();
        return isset($newValue[$attribute['name']])?$newValue[$attribute['name']]:'';
    }
}