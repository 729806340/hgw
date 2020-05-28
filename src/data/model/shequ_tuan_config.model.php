<?php
/**
 * 社区团购活动模型
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_tuan_configModel extends Model{

    const CONFIG_STATE_NORMAL = 1;
    const CONFIG_STATE_CLOSE = 2;
    const CONFIG_STATE_CANCEL = 3;

    const STATE_CREATED = 0; // 新团
    const STATE_SUCCESS = 10; // 成团
    const STATE_SHIPPING = 20; // 配送中
    const STATE_PAYING = 30; // 清算中
    const STATE_DONE = 40; // 已完成


    private $config_state_array = array(
        0 => '全部',
        self::CONFIG_STATE_NORMAL => '正常',
        self::CONFIG_STATE_CLOSE => '已结束',
        self::CONFIG_STATE_CANCEL => '管理员关闭'
    );

    public function __construct(){
        parent::__construct('shequ_tuan_config');
    }

    /**
     * 读取社区团购配置列表
     * @param array $condition 查询条件
     * @param int $page 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @return array 限时折扣列表
     *
     */
    public function getTuanConfigList($condition, $page=null, $order='', $field='*') {
        return $this->field($field)->where($condition)->page($page)->order($order)->select();
    }


    public function getTuanConfigInfo($condition) {
        return $this->where($condition)->find();
    }

    /**
     * 社区团购状态数组
     *
     */
    public function getTuanConfigStateArray() {
        return $this->config_state_array;
    }

    public function addTuanConfig($param){
        return $this->insert($param);
    }
    public function edit($condition,$param){
        return $this->where($condition)->update($param);
    }


}
