<?php
/**
 * Author: Shen.L
 * Date: 2016/11/21
 * Time: 10:25
 */

/**
 * Class DataMonitor
 */
class DataMonitorService
{
	public $tasks = array(
	    'unsetCommission',
	    'emptyCommission',
	    'emptyCost',
	    'platformTax',
	    'manageType',
	    'orderRpt',
	    'orderAmount',
    );

    private $_error=0;
	public function __construct()
	{
        // 构造方法
	}

	public function launch()
    {
        // 启动数据监控
        $total = count($this->tasks);
        $success=$error=0;
        foreach ($this->tasks as $taskName)
        {
            try{
                if($this->getTask($taskName)->handle()) $success++;
                else $error++;
            }catch (Exception $e){
                $error++;
            }
        }
        $this->setError($error);
        return $total==$success;
    }

    /**
     * @param $name
     * @return Task
     * @throws Exception
     */
    public function getTask($name)
    {

        $className = 'Task'.ucfirst($name);
        $fileName = __DIR__ . '/monitor/' . $className . '.php';
        if (file_exists($fileName)) {
            require_once($fileName);
            if (class_exists($className)) return new $className();
        }
        throw new Exception('找不到对应任务对象！名称：' . $name);
    }

    public function setError($error)
    {
        $this->_error = $error;
    }

}