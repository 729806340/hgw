<?php
/**
 * 上传设置
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class big_dataControl extends SystemControl{
    private $links = array(
        array('url'=>'act=upload&op=param','text'=>'大屏设置'),
        //array('url'=>'act=upload&op=default_thumb','lang'=>'default_thumb'),
    );
    public function __construct(){
        parent::__construct();
        Language::read('setting');
    }

    public function indexOp() {
        $this->paramOp();
    }

    /**
     * 上传参数设置
     *
     */
    public function paramOp(){
        /** @var settingModel $model_setting */
        $model_setting = Model('setting');
        if (chksubmit()){
            $obj_validate = new Validate();
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                wkcache('bigdata_setting',array(
                        'big_data_rate'=>$_POST['big_data_rate'],
                        'big_data_rate_sale'=>$_POST['big_data_rate_sale'],
                        'big_data_rate_logistics'=>$_POST['big_data_rate_logistics'],
                        'big_data_rate_channel'=>$_POST['big_data_rate_channel'],
                        'big_data_rate_province'=>$_POST['big_data_rate_province'],
                    )
                );
                showMessage(L('nc_common_save_succ'));
            }
        }


        //获取默认图片设置属性
        $bigdata_setting = rkcache('bigdata_setting');
        Tpl::output('bigdata_setting',$bigdata_setting);

        //输出子菜单
        Tpl::output('top_link',$this->sublink($this->links,'param'));
		//网 店 运 维shop wwi.com
		Tpl::setDirquna('system');
        Tpl::showpage('big_data.param');
    }

}
