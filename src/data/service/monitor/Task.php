<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/22
 * Time: 11:42
 */

/**
 * Class Task
 */
abstract class Task
{
    protected $_sql;
    protected $_handler;
    protected $_data;

    /**
     * 处理任务
     * @return bool
     */
    public function handle()
    {
        $handler = $this->getHandler();
        if ($handler instanceof Closure) {
            $this->_data = call_user_func($handler);
        } else {
            /** @var Model $model */
            $model = Model();
            $this->_data = $model->query($handler);
        }
        if(count($this->_data)<=0) return true;
        $resEmail = $this->sendEmail();
        $resSms = $this->sendSms();
        return $resEmail && $resSms;
    }

    public function sendEmail()
    {
        $data = $this->renderData();
        $email = new Email();
        $receivers = (array)$this->getEmailReceiver();
        $title = $this->getTitle();
        $description = $this->getDescription(count($this->_data));
        $statement = $this->getStatement();
        $content = <<<HTML
<p>尊敬的后台管理员：您好！</p>
<p>$description</p>
<p>$statement</p>
<p>$data</p>
HTML;
        v($content);
        foreach ($receivers as $receiver){
            $email->send_sys_email($receiver,$title,$content);
        }
        return true;
    }

    public function sendSms()
    {
        $sms = new Sms();
        $receivers = (array)$this->getSmsReceiver();
        $description = $this->getDescription(count($this->_data));
        foreach ($receivers as $receiver){
            $result = $sms->send($receiver,$description);
        }
        return true;
    }

    abstract public function getHandler();

    abstract public function getEmailReceiver();

    abstract public function getSmsReceiver();

    abstract public function getId();

    abstract public function getName();

    abstract public function getTitle();

    abstract public function getDescription($count=0);

    public function getStatement()
    {
        $handler = $this->getHandler();
        if (is_string($handler)) return $handler;
        return 'SQL语句未设置';
    }

    abstract function renderData();
}