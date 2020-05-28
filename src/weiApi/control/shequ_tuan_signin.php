
<?php
/**
 * 我的团长页面
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
defined('ByShopWWI') or exit('Access Invalid!');

class shequ_tuan_signinControl extends mobileMemberTuanControl {
    //农猫速达
    private $nmsd =array(
        'app_id'=>'wx5d330dfa99409ea7',
        'app_secret'=>'68006a24e7d4af313638e6631fa57108',
    );

    public function __construct(){
        parent::__construct();
        /** @var shequ_tuanzhangModel $tuanzhang_model */
        $condition['state'] = '1';
        $condition['member_id'] = $this->member_info['member_id'];
        $tuanzhang_model = Model('shequ_tuanzhang');
        $this->tuanzhang_info = $tuanzhang_model->getOne($condition);
        if (empty($this->tuanzhang_info)) {
            output_error('不是团长');
        }
        if($this->tuanzhang_info['area']!=''){
            $this->tuanzhang_info['wuliu_type'] ='自提';
        }else{
            output_error('二维码仅自提团长可用');
        }
    }

    
    /**
     * 签到单详情接口
     */
    public function signInfoOp(){
        $data = array();
        $data['goods_list'] = array();
        $condition['id'] = $_POST['tuan_id']?intval($_POST['tuan_id']):output_error('tuan_id参数错误');
        /** @var shequ_tuanModel $tuan_model */
        $tuan_model = Model('shequ_tuan');
        $tuan_info = $tuan_model->getOne($condition);
        empty($tuan_info) and output_data($data,mobile_page(0));
        $data['delivery_time'] = $tuan_info['send_product_date']?date('Y-m-d',$tuan_info['send_product_date']):"";
        $data['state'] = $tuan_info['fetch_goods_state'];
        $data['driver_name']  = $tuan_info['driver_name'];
        $data['driver_phone'] = $tuan_info['driver_phone'];
        $data['address'] = $tuan_info['address'].$tuan_info['building'];
        $data['tuan_name'] = $this->tuanzhang_info['name'];
        $data['tuan_phone'] = $this->tuanzhang_info['phone'];
        /** @var shequ_peisongdanModel $peisongdan_model */
        $peisongdan_model = Model('shequ_peisongdan');
        $peisong_condition = array(
            'tz_id'=>$this->tuanzhang_info['id'],
            'tuan_config_id'=>$tuan_info['config_id'],
            'tuan_id'=>$tuan_info['id'],
        );
        $goods_list = $peisongdan_model->where($peisong_condition)->page($this->page)->select();
        empty($goods_list) and output_data($data,mobile_page(0));
        $goods_ids = array_column($goods_list,'goods_id');
        /** @var goodsModel $goods_model */
        $goods_model = Model('goods');
        $goods_fields = "goods_id,goods_name,spec_name,goods_spec";
        $list = $goods_model->getGoodsList(array('goods_id'=>array('in',$goods_ids)),$goods_fields);
           foreach($list as $gk=>$gv){
               $list[$gk]['guige'] = $this->spec($gv);
           }
           $list = array_column($list,null,'goods_id');
        foreach($goods_list as $gk=>$gv){
            $tmp_array  = array();
            $tmp_array['goods_name'] = $gv['goods_name'];
            $tmp_array['goods_num'] = $gv['goods_num'];
            $tmp_array['guige'] = isset($list[$gv['goods_id']])?$list[$gv['goods_id']]['guige']:"";
            $tmp_array['goods_image'] = thumb(array('goods_image'=>$gv['goods_image'],'store_id'=>$gv['store_id']),360);
            $data['goods_list'][] = $tmp_array;
        }
        output_data($data,mobile_page($peisongdan_model->gettotalpage()));
    }

    /**
     * 确认签收接口
     */
    public function signChangeOp(){
        $condition['id'] = $_POST['tuan_id'];
        /** @var shequ_tuanModel $tuan_model */
        $tuan_model = Model('shequ_tuan');
        $tuan_info = $tuan_model->getOne($condition);

        empty($tuan_info) || !in_array($tuan_info['state'],array('20','30')) and output_error('参数错误');
        $tuan_info['fetch_goods_state'] == '30' and output_error('请勿重复点击签收');

        $update_arr['fetch_goods_state'] = '30';
        $update_arr['fetch_goods_time'] = time();
        $update_arr['delivery_state'] = '30';
        $update_arr['delivery_finished_time'] = time();
        $res  = $tuan_model->edit($condition,$update_arr);
        output_data('签收成功');
    }

    /**
     * 轮询接口
     * @param
     * @return boolean|json
     */
    public function pollingOp(){
        $data = array(
            'state'=>'false',
            'tuan_id' => '',
        );
        /** @var shequ_tuan_configModel $tuan_config_model */
        $tuan_config_model  =Model('shequ_tuan_config');
        $tuan_config_condition = array(
            'state'=>'30 ',
            'type'=>'2'
        );
            $tuan_info  = $tuan_config_model->getTuanConfigInfo($tuan_config_condition);//为空表示没有配送中的团,
        if(empty($tuan_info)){
            output_data($data);
        }
        /** @var shequ_tuanModel $tuan_model */
         $tuan_model = Model('shequ_tuan');
         $condition['config_id'] = $tuan_info['config_tuan_id'];
         $condition['tz_id'] = $this->tuanzhang_info['id'];
         $tuan_info = $tuan_model->getOne($condition);
         if(!empty($tuan_info)){
            if($tuan_info['update_time']=='0'){
                output_data($data);
            }else{
                output_data(array('state'=>'true','tuan_id'=>$tuan_info['id']));
            }
         }else{
             output_data($data);
         }
    }
    /**
     * 二维码接口
     * @return mixed string
     */
    public function indexOp(){

        $tuanzhang_id = $this->member_info['tuanzhang_id'];
        /** @var shequ_addressModel $shequ_addressModel */
        $shequ_addressModel = Model('shequ_address');
        $shequ_address = $shequ_addressModel->getOne(array(
            'tuanzhang_id' => $tuanzhang_id,
        ));
        $return = array(
            'tuan_qr_code' => $this->makeerweima($this->member_info['tuanzhang_id']),
            'tuanzhang_address' =>  $shequ_address['area'] . $shequ_address['street'] . $shequ_address['community']. $shequ_address['address']. $shequ_address['building'],
        );
        output_data_new($return);
    }

    /**
     * @param $tuan_id
     * @return mixed|string
     */
    protected function makeerweima($tuan_id)
    {
        
       // $default['accesstoken'] = $this->getAccessToken();  //todo nmsd页面发布之后取消注释
        /** @var  wx_small_appLogic $wx_samll_app */
        $wx_samll_app = Logic('wx_small_app');
        try {
         //   $res = $wx_samll_app->getQr("pages/groupOrderDetail/groupOrderDetail", $tuan_id,$default);  //todo 发布之后改地址
            $res = $wx_samll_app->getQr("pages/community/community", $tuan_id,$default);
            $type = getimagesizefromstring($res)['mime'];
            $base64String = 'data:' . $type . ';base64,' . chunk_split(base64_encode($res));
            return $base64String;
        } catch (Exception $e) {
            //默认
            $base64String = '';
            return $base64String;
        }
    }

    /**
     * @return mixed
     * @throws \ErrorException|\Exception
     */
    protected function getAccessToken()
    {
        $cacheKey = 'wx.small-app.access-token';
        $res = rkcache($cacheKey);
        $res = array();
        if (isset($res['access_token']) && !empty($res['access_token']) && isset($res['expires_at']) && $res['expires_at'] - 600 > TIMESTAMP) return $res['access_token'];
        $param = array(
            'grant_type' => 'client_credential',
            'appid' => $this->nmsd['app_id'],
            'secret' => $this->nmsd['app_secret'],
        );
        $curl = new Curl();
        $curl->setJsonDecoder(function ($response) {
            return json_decode($response, true);
        });
        $curl->setOpt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $curl->get('https://api.weixin.qq.com/cgi-bin/token', $param);
        if ($curl->error) {
            throw new \Exception('AccessToken获取失败;Error:' . $curl->errorCode . ': ' . $curl->errorMessage);
        }
        $res = $curl->response;
        $res['expires_at'] = time() + $res['expires_in'];
        wkcache($cacheKey, $res);
        return $res['access_token'];
    }

    public function spec($goods_info){
        $spec = '';
        $_tmp_name = unserialize($goods_info['spec_name']);
        $_tmp_value = unserialize($goods_info['goods_spec']);
        if (is_array($_tmp_name) && is_array($_tmp_value)) {
            $_tmp_name = array_values($_tmp_name);$_tmp_value = array_values($_tmp_value);
            foreach ($_tmp_name as $sk => $sv) {
                $spec .= $sv.'：'.$_tmp_value[$sk].'，';
            }
            return rtrim($spec,'，');
        }
        return $spec;
    }
}

