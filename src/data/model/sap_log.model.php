<?php
defined('ByShopWWI') or exit ('Access Invalid!');

class sap_logModel extends Model
{
    public $model;
    public function __construct()
    {
        parent::__construct('sap_log');
        $model = new TModel();
        $this->model = new TModel('sap_log','','DB_LOG',1);
        $this->model->db(1);
    }

    public function addLog($log)
    {
        if (empty($log) || !is_array($log) || empty($log['data'])) return false;
        if (empty($log['code'])) $log['code'] = 'error';
        if (isset($log['data']) && !is_string($log['data'])) $log['data'] = encode_json($log['data']);
        if (isset($log['rel']) && !is_string($log['rel'])) $log['rel'] = encode_json($log['rel']);
        if (isset($log['error']) && !is_string($log['error'])) $log['error'] = encode_json($log['error']);
        $log['add_time'] = time();
        $res = $this->model->data($log)->add();
        $this->model->db(0);
        return $res;
    }

    //清理30天前的数据
    public function clearLog()
    {
        $where['add_time'] = array('lt', strtotime("-30 day"));
        return $this->where($where)->delete();
    }
}