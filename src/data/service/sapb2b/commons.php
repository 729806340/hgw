<?php

class commons
{
    public $failed = array();//记录失败或错误信息 用于邮件推送
    public $setting = array();//相关设置内容
    public $api = array();//SAP提供的API地址
    public $method = array();//对应的类及方法
    private $hasInit = false;//初始化标识
    private $register = array();

    public function __construct()
    {
        $this->init();
    }

    //初始化 加载配置文件
    public function init()
    {
        if ($this->hasInit === false) {
            $config = base::load_config('sapb2b');
            $this->method = $config['method'];
            $this->api = $config['api'];
            $this->setting = $config['setting'];
        }
        $this->hasInit = true;
    }

    //根据 class fun 获取code
    public function getCode($class, $fun)
    {
        $map = array_flip($this->method);
        $code = $map[$class . '.' . $fun];
        if (empty($code)) throw new Exception('Error: Could not find code ' . $class . '.' . $fun . '!');
        return $code;
    }

    //根据交易获取每次推送数据条数
    public function getLimit($code)
    {
        $limit = $this->setting['limit'][$code] ? $this->setting['limit'][$code] : $this->setting['limit']['default'];
        return intval($limit);
    }

    public function instantiation($code)
    {
        $key = $this->method[$code];
        if (!is_string($key) || !$p = strrpos($key, '.')) throw new Exception('Error: Could not call ' . $code);

        if (!isset($this->register['instance'][$code])) {
            $class = substr($key, 0, $p);
            $file = __DIR__ . '/' . $class . '.php';
            if (is_file($file)) {
                include_once($file);
                $action = new $class();
            } else {
                throw new Exception('Error: Could not call ' . $class . '!');
            }
            $method = substr($key, $p + 1);
            $this->register['instance'][$code] = array($action, $class, $method);
        }
        return $this->register['instance'][$code];
    }

    public function execute(&$action, $class, $method, array $args = array())
    {
        if (!isset($this->register['reflection'][$class])) {
            $this->register['reflection'][$class] = new ReflectionClass($class);
        }
        $reflection = $this->register['reflection'][$class];
        if ($reflection->hasMethod($method) && $reflection->getMethod($method)->getNumberOfRequiredParameters() <= count($args)) {
            return call_user_func_array(array($action, $method), $args);
        } else {
            throw new Exception('Error: Could not call ' . $class . '->' . $method . '!');
        }
    }

    //记录到日志信息表
    public function log($log)
    {
        if (!$this->setting['log']) return true; //日志开关
        Model('sapb2b_log')->addLog($log);
    }

    //析构函数 用于业务处理完后 邮件推送提示信息
    public function __destruct()
    {
        if (empty($this->failed)) return true;
        //邮件推送失败的数据信息
        $notice = $this->setting['notice'];
        if (!$notice['send'] || empty($notice['email'])) return true;
        $obj = new Email();
        $ems = explode(',', $notice['email']);
        foreach ($this->failed as $v) {
            foreach ($ems as $to) {
                $obj->send_sys_email($to, $v['title'], $v['msg']);
            }
        }
        $this->failed = array();
    }
}