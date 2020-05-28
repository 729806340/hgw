<?php
/**
 * 竞价信息抓取配置
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');
class jingjiaModel extends Model{
    public function __construct() {
        parent::__construct('jingjia');
    }

    /**
     * 咨询列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getList($condition, $field = '*,(max(sales)-min(sales)) as sales_count', $page = 1, $order = 'id desc',$export=0) {
        //获取当前时间区间内最新的一条数据
//         return $this->where($condition)->field($field)->order($order)->group('prod_id')->page($page)->select();
        if(!$export){
            $limit=' limit '.(15*intval($page-1)).',15';
        }else{
            $limit="";
        }
        $condition['fetch_time'][1][1]+=4800;
        if(!empty($condition['prod_id'])){
            $condit=' prod_id '.$condition['prod_id'][0]. ' ('.$condition['prod_id'][1].') and ';
        }
        $sql="select *,(max(sales)-min(sales)) as sales_count from shopwwi_jingjia where".$condit." fetch_time BETWEEN ".$condition['fetch_time'][1][0].' AND '.$condition['fetch_time'][1][1].' GROUP BY prod_id'.' order by '.$order.$limit;
        return $this->query($sql);
    }

    /**
     * 咨询数量
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getCount($condition) {
        return $this->where($condition)->count();
    }

    /**
     * 单条咨询
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     */
    public function getInfo($condition, $field = '*', $order = 'id desc') {
        return $this->where($condition)->field($field)->order($order)->find();
    }

    public function getAllList(){
        return $this->select();
    }
}
