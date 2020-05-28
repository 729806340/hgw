<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class CommonController extends Controller {

    public $start;
    public $total = 0;
    public $pageSize = 1000;
    public $steps;

    public $items;

    protected function start()
    {
        if($this->start === null) $this->start = I('get.start',0);
        return $this->start;
    }

    protected function next()
    {
        return $this->start()+$this->pageSize;
    }


    protected function hasNext()
    {
        return $this->next() < $this->total;
    }

    protected function getItems($name,$pk)
    {
        $model = ecM($name);
        $this->total = (int)$model->count($pk);
        $this->items = $model->order($pk)->limit($this->start(),$this->pageSize)->select();
        return array();
    }

    protected function nextAction($msg='操作成功')
    {
        if($this->hasNext()&&C('ON_DEV')!==true) $next = U('',array('start'=>$this->next()));
        else $next = U($this->nextStep());
        $this->success($msg,$next);
    }

    protected function nextStep()
    {
        foreach ($this->steps as $k=>$step)
        {
            if($step != ACTION_NAME) continue;
            if(isset($this->steps[$k+1])) return $this->steps[$k+1];
        }
        return 'Index/index';
    }

    protected function copyTo($from,$map)
    {
        $res = array();
        foreach ($map as $k=>$v)
        {
            $res[$v] = isset($from[$k])?$from[$k]:'';
        }
        return $res;
    }

    /**
     * @param $names array
     */
    protected function clearTable($names)
    {
        foreach ((array)$names as $name){
            M()->execute('TRUNCATE TABLE __PREFIX__'.$name.';');
        }
        return;
    }

}