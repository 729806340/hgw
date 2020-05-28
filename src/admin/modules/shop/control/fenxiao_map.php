<?php
/**
 * 分销管理
 */

defined('ByShopWWI') or exit('Access Invalid!');
class fenxiao_mapControl extends SystemControl{
    private $links = array(
        array('url'=>'act=fenxiao_map&op=beibeiwang','text'=>'贝贝网'),
        array('url'=>'act=fenxiao_map&op=juanpi','text'=>'卷皮'),
        array('url'=>'act=fenxiao_map&op=fanli','text'=>'返利'),
        array('url'=>'act=fenxiao_map&op=mengdian','text'=>'萌店'),
        array('url'=>'act=fenxiao_map&op=renrenyoupin','text'=>'人人优品'),
        array('url'=>'act=fenxiao_map&op=pinduoduo','text'=>'拼多多'),
        array('url'=>'act=fenxiao_map&op=grsc','text'=>'果然商城'),
        array('url'=>'act=fenxiao_map&op=hangoweimeng','text'=>'汉购微盟'),
        array('url'=>'act=fenxiao_map&op=suningnonggu','text'=>'苏宁易购农谷鲜'),
        array('url'=>'act=fenxiao_map&op=chuchutong','text'=>'楚楚通'),
        array('url'=>'act=fenxiao_map&op=chuchujie','text'=>'楚楚街'),
        array('url'=>'act=fenxiao_map&op=jingdongfx','text'=>'京东'),
        array('url'=>'act=fenxiao_map&op=ylmg','text'=>'云联美购'),
        array('url'=>'act=fenxiao_map&op=hangohongmao','text'=>'汉购红锚'),
    );

    public function __construct(){
        parent::__construct();
    }

    public function baseOp($source,$name){
        //更新数据库数据
        $this->refresh_sku($source,$name);
        Tpl::output('top_link',$this->sublink($this->links,$name));
        Tpl::output('source',$name);
        Tpl::setDirquna('shop');
        Tpl::showpage('fenxiao_map.index');
    }

    public function indexOp(){
        /*第一个名字对应的是在data/service/fenxiao的文件名，第二个名字对应的是在member_fenxiao表格的名字*/
        $this->baseOp('pinduoduo','pinduoduo');
    }

    public function beibeiwangOp(){
        $this->baseOp('beibeiwang','beibeiwang');
    }

    public function juanpiOp(){
        $this->baseOp('juanpi','juanpi');
    }

    public function fanliOp(){
        $this->baseOp('fanli','fanli');
    }

    public function mengdianOp(){
        $this->baseOp('mengdian','mengdian');
    }

    public function renrenyoupinOp(){
        $this->baseOp('renrenyoupin','renrenyoupin');
    }

    public function pinduoduoOp(){
        $this->baseOp('pinduoduo','pinduoduo');
    }

    public function grscOp(){
        $this->baseOp('grsc','grsc');
    }

    public function hangoweimengOp(){
        $this->baseOp("hangoweimeng","hangoweimeng");
    }

    public function suningnongguOp(){
        $this->baseOp("suningnonggu","suningnonggu");
    }

    public function chuchutongOp(){
        $this->baseOp('chuchutong','chuchutong');
    }

    public function chuchujieOp(){
        $this->baseOp('chuchujie','chuchujie');
    }

    public function jingdongfxOp(){
        $this->baseOp('jingdongfx','jingdongfx');
    }

    public function ylmgOp(){
        $this->baseOp('ylmg','ylmg');
    }

    public function hangohongmaoOp(){
        $this->baseOp('hangohongmao','hangohongmao');
    }

    //刷新数据库,更新最新sku
    private function refresh_sku($source,$name){
        /** @var FenxiaoService $fenxiao_service */
        $fenxiao_service = Service("Fenxiao") ;
        $fenxiao_service -> init( $source ) ;

        $page_max = 3;
        for($page = 1;$page <= $page_max;$page++){
            $params = array();
            $params['page_no'] = $page;
            $params['page_size'] = 50;
            $sku_list = $fenxiao_service -> getSkuList ($params);
            $res = Model('fenxiao_sku')->addSkuList($sku_list,$name);
            if(!$res) return false;
        }
        return true;
    }

    public function index_xmlOp(){
        /** @var fenxiao_skuModel $model_fenxiao_sku */
        $model_fenxiao_sku = Model('fenxiao_sku');
        $condition = array();

        if ($_GET['source'] != '') {
            $condition['source'] = $_GET['source'];
        }

        if ($_POST['query'] != '') {
            $condition[$_POST['qtype']] = array('like', '%' . $_POST['query'] . '%');
        }

        $order = '';
        $param = array('spu_id');
        if (in_array($_POST['sortname'], $param) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $order = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }

        $page = $_POST['rp'];
        $sku_list = $model_fenxiao_sku->getSkuList($condition,$page,$order);

        $data = array();
        $data['now_page'] = $model_fenxiao_sku->shownowpage();
        $data['total_num'] = $model_fenxiao_sku->gettotalnum();
        foreach($sku_list as $val){
            $item = array();
            $item['goods_name'] = $val['goods_name'];
            $item['sku_id'] = $val['sku_id'];
            $item['spu_id'] = $val['spu_id']?$val['spu_id']:'<font color="red">未匹配</font>';
            $data['list'][$val['sku_id']] = $item;
        }
        echo Tpl::flexigridXML($data);
        exit;
    }

}