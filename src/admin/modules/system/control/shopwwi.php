<?php
/**
 * 运维控件管理 汉购网 shop wwi .com
 *by wansyb QQ499063702
 */
defined('ByShopWWI') or exit('Access Invalid!');
class shopwwiControl extends SystemControl{
	 private $links = array(
	    array('url'=>'act=shopwwi&op=base','lang'=>'shopwwi_set'),
        array('url'=>'act=shopwwi&op=banner','lang'=>'top_set'),
        array('url'=>'act=shopwwi&op=lc','lang'=>'lc_set'),
		array('url'=>'act=shopwwi&op=sms','lang'=>'sms_set'),
		array('url'=>'act=shopwwi&op=rc','lang'=>'rc_set'),
		array('url'=>'act=shopwwi&op=webchat','lang'=>'webchat_set'),
        
    );
	public function __construct(){
		parent::__construct();
		Language::read('shopwwi,setting');
	}
	    public function indexOp() {
        $this->baseOp();
    }
		 /**
     * 基本信息
     */
    public function baseOp(){
        $model_setting = Model('setting');
        if (chksubmit()){
            $list_setting = $model_setting->getListSetting();
            $update_array = array();
            $update_array['shopwwi_stitle'] = $_POST['shopwwi_stitle'];
            $update_array['shopwwi_phone'] = $_POST['shopwwi_phone'];
            $update_array['shopwwi_time'] = $_POST['shopwwi_time'];
			$update_array['shopwwi_invite2'] = $_POST['shopwwi_invite2'];
			$update_array['shopwwi_invite3'] = $_POST['shopwwi_invite3'];
            $result = $model_setting->updateSetting($update_array);
            if ($result === true){
                $this->log(L('nc_edit,shopwwi_set'),1);
                showMessage(L('nc_common_save_succ'));
            }else {
                $this->log(L('nc_edit,shopwwi_set'),0);
                showMessage(L('nc_common_save_fail'));
            }
        }
        $list_setting = $model_setting->getListSetting();

        Tpl::output('list_setting',$list_setting);

        //输出子菜单
        Tpl::output('top_link',$this->sublink($this->links,'base'));
		//网 店 运 维shop wwi.com
		Tpl::setDirquna('system');
        Tpl::showpage('shopwwi.base');
    }
	 /**
     * 顶部广告信息
     */
    public function bannerOp(){
        $model_setting = Model('setting');
        if (chksubmit()){
			 if (!empty($_FILES['shopwwi_top_banner_pic']['name'])){
                $upload = new UploadFile();
                $upload->set('default_dir',ATTACH_COMMON);
                $result = $upload->upfile('shopwwi_top_banner_pic');
                if ($result){
                    $_POST['shopwwi_top_banner_pic'] = $upload->file_name;
                }else {
                    showMessage($upload->error,'','','error');
                }
            }
            $list_setting = $model_setting->getListSetting();
            $update_array = array();
            $update_array['shopwwi_top_banner_name'] = $_POST['top_banner_name'];
            $update_array['shopwwi_top_banner_url'] = $_POST['top_banner_url'];
            $update_array['shopwwi_top_banner_color'] = $_POST['top_banner_color'];
            $update_array['shopwwi_top_banner_status'] = $_POST['top_banner_status'];
			if (!empty($_POST['shopwwi_top_banner_pic'])){
                $update_array['shopwwi_top_banner_pic'] = $_POST['shopwwi_top_banner_pic'];
            }
            $result = $model_setting->updateSetting($update_array);
			if ($result === true){
                //判断有没有之前的图片，如果有则删除
                if (!empty($list_setting['shopwwi_top_banner_pic']) && !empty($_POST['shopwwi_top_banner_pic'])){
                    @unlink(BASE_UPLOAD_PATH.DS.ATTACH_COMMON.DS.$list_setting['shopwwi_top_banner_pic']);
                }
                $this->log(L('nc_edit,top_set'),1);
                showMessage(L('nc_common_save_succ'));
            }else {
                $this->log(L('nc_edit,top_set'),0);
                showMessage(L('nc_common_save_fail'));
            }
        }
         
        $list_setting = $model_setting->getListSetting();

        Tpl::output('list_setting',$list_setting);

        //输出子菜单
        Tpl::output('top_link',$this->sublink($this->links,'banner'));
		//网 店 运 维shop wwi.com
		Tpl::setDirquna('system');
        Tpl::showpage('shopwwi.banner');
    }
	
	 /**
     * 楼层快速直达列表
     */
    public function lcOp() {
        $model_setting = Model('setting');
        $lc_info = $model_setting->getRowSetting('shopwwi_lc');
        if ($lc_info !== false) {
            $lc_list = @unserialize($lc_info['value']);
        }
        if (!$lc_list && !is_array($lc_list)) {
            $lc_list = array();
        }
        Tpl::output('lc_list',$lc_list);
        Tpl::output('top_link',$this->sublink($this->links,'lc'));
		Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shopwwi.lc');
    }

    /**
     * 楼层快速直达添加
     */
    public function lc_addOp() {
        $model_setting = Model('setting');
        $lc_info = $model_setting->getRowSetting('shopwwi_lc');
        if ($lc_info !== false) {
            $lc_list = @unserialize($lc_info['value']);
        }
        if (!$lc_list && !is_array($lc_list)) {
            $lc_list = array();
        }
        if (chksubmit()) {
            if (count($lc_list) >= 8) {
                showMessage('最多可设置8个楼层','index.php?act=shopwwi&op=lc');
            }
            if ($_POST['lc_name'] != '' && $_POST['lc_value'] != '') {
                $data = array('name'=>stripslashes($_POST['lc_name']),'value'=>stripslashes($_POST['lc_value']));
                array_unshift($lc_list, $data);
            }
            $result = $model_setting->updateSetting(array('shopwwi_lc'=>serialize($lc_list)));
            if ($result){
                showMessage('保存成功','index.php?act=shopwwi&op=lc');
            }else {
                showMessage('保存失败');
            }
        }
		Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/

        Tpl::showpage('shopwwi.lc_add');
    }

    /**
     * 删除
     */
    public function lc_delOp() {
        $model_setting = Model('setting');
        $lc_info = $model_setting->getRowSetting('shopwwi_lc');
        if ($lc_info !== false) {
            $lc_list = @unserialize($lc_info['value']);
        }
        if (!empty($lc_list) && is_array($lc_list) && intval($_GET['id']) >= 0) {
            unset($lc_list[intval($_GET['id'])]);
        }
        if (!is_array($lc_list)) {
            $lc_list = array();
        }
        $result = $model_setting->updateSetting(array('shopwwi_lc'=>serialize(array_values($lc_list))));
        if ($result){
            showMessage('删除成功');
        }
        showMessage('删除失败');
    }

    /**
     * 编辑
     */
    public function lc_editOp() {
        $model_setting = Model('setting');
        $lc_info = $model_setting->getRowSetting('shopwwi_lc');
        if ($lc_info !== false) {
            $lc_list = @unserialize($lc_info['value']);
        }
        if (!is_array($lc_list)) {
            $lc_list = array();
        }
        if (!chksubmit()) {
            if (!empty($lc_list) && is_array($lc_list) && intval($_GET['id']) >= 0) {
                $current_info = $lc_list[intval($_GET['id'])];
            }
            Tpl::output('current_info',is_array($current_info) ? $current_info : array());
			Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('shopwwi.lc_add');
        } else {
            if ($_POST['lc_name'] != '' && $_POST['lc_value'] != '' && $_POST['id'] != '' && intval($_POST['id']) >= 0) {
                $lc_list[intval($_POST['id'])] = array('name'=>stripslashes($_POST['lc_name']),'value'=>stripslashes($_POST['lc_value']));
            }
            $result = $model_setting->updateSetting(array('shopwwi_lc'=>serialize($lc_list)));
            if ($result){
                showMessage('编辑成功','index.php?act=shopwwi&op=lc');
            }
            showMessage('编辑失败');
        }


    }
	
		 /**
     * 首页热门关键词链接
     */
    public function rcOp() {
        $model_setting = Model('setting');
        $rc_info = $model_setting->getRowSetting('shopwwi_rc');
        if ($rc_info !== false) {
            $rc_list = @unserialize($rc_info['value']);
        }
        if (!$rc_list && !is_array($rc_list)) {
            $rc_list = array();
        }
        Tpl::output('rc_list',$rc_list);
        Tpl::output('top_link',$this->sublink($this->links,'rc'));
		Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shopwwi.rc');
    }

    /**
     * 楼层快速直达添加
     */
    public function rc_addOp() {
        $model_setting = Model('setting');
        $rc_info = $model_setting->getRowSetting('shopwwi_rc');
        if ($rc_info !== false) {
            $rc_list = @unserialize($rc_info['value']);
        }
        if (!$rc_list && !is_array($rc_list)) {
            $rc_list = array();
        }
        if (chksubmit()) {
            if (count($rc_list) >= 8) {
                showMessage('最多可设置8个楼层','index.php?act=shopwwi&op=rc');
            }
            if ($_POST['rc_name'] != '' && $_POST['rc_value'] != '' && $_POST['rc_blod'] != '') {
                $data = array('name'=>stripslashes($_POST['rc_name']),'value'=>stripslashes($_POST['rc_value']),'is_blod'=>stripslashes($_POST['rc_blod']));
                array_unshift($rc_list, $data);
            }
            $result = $model_setting->updateSetting(array('shopwwi_rc'=>serialize($rc_list)));
            if ($result){
                showMessage('保存成功','index.php?act=shopwwi&op=rc');
            }else {
                showMessage('保存失败');
            }
        }
		Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/

        Tpl::showpage('shopwwi.rc_add');
    }

    /**
     * 删除
     */
    public function rc_delOp() {
        $model_setting = Model('setting');
        $rc_info = $model_setting->getRowSetting('shopwwi_rc');
        if ($rc_info !== false) {
            $rc_list = @unserialize($rc_info['value']);
        }
        if (!empty($rc_list) && is_array($rc_list) && intval($_GET['id']) >= 0) {
            unset($rc_list[intval($_GET['id'])]);
        }
        if (!is_array($rc_list)) {
            $rc_list = array();
        }
        $result = $model_setting->updateSetting(array('shopwwi_rc'=>serialize(array_values($rc_list))));
        if ($result){
            showMessage('删除成功');
        }
        showMessage('删除失败');
    }

    /**
     * 编辑
     */
    public function rc_editOp() {
        $model_setting = Model('setting');
        $rc_info = $model_setting->getRowSetting('shopwwi_rc');
        if ($rc_info !== false) {
            $rc_list = @unserialize($rc_info['value']);
        }
        if (!is_array($rc_list)) {
            $rc_list = array();
        }
        if (!chksubmit()) {
            if (!empty($rc_list) && is_array($rc_list) && intval($_GET['id']) >= 0) {
                $current_info = $rc_list[intval($_GET['id'])];
            }
            Tpl::output('current_info',is_array($current_info) ? $current_info : array());
			Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('shopwwi.rc_add');
        } else {
            if ($_POST['rc_name'] != '' && $_POST['rc_value'] != '' && $_POST['rc_blod'] != '' && $_POST['id'] != '' && intval($_POST['id']) >= 0) {
                $rc_list[intval($_POST['id'])] = array('name'=>stripslashes($_POST['rc_name']),'value'=>stripslashes($_POST['rc_value']),'is_blod'=>stripslashes($_POST['rc_blod']));
            }
            $result = $model_setting->updateSetting(array('shopwwi_rc'=>serialize($rc_list)));
            if ($result){
                showMessage('编辑成功','index.php?act=shopwwi&op=rc');
            }
            showMessage('编辑失败');
        }


    }
		/**
	 * 短信平台设置 
	 */
	public function smsOp(){
		$model_setting = Model('setting');
		if (chksubmit()){
			$update_array = array();
			$update_array['shopwwi_sms_type'] 	= $_POST['shopwwi_sms_type'];
			$update_array['shopwwi_sms_tgs'] 	= $_POST['shopwwi_sms_tgs'];
			$update_array['shopwwi_sms_zh'] 	= $_POST['shopwwi_sms_zh'];
			$update_array['shopwwi_sms_pw'] 	= $_POST['shopwwi_sms_pw'];
			$update_array['shopwwi_sms_key'] 	= $_POST['shopwwi_sms_key'];
			$update_array['shopwwi_sms_signature'] 		= $_POST['shopwwi_sms_signature'];
			$update_array['shopwwi_sms_bz'] 	= $_POST['shopwwi_sms_bz'];
			$result = $model_setting->updateSetting($update_array);
			if ($result === true){
				$this->log(L('nc_edit,sms_set'),1);
				showMessage(L('nc_common_save_succ'));
			}else {
				$this->log(L('nc_edit,sms_set'),0);
				showMessage(L('nc_common_save_fail'));
			}
		}
		$list_setting = $model_setting->getListSetting();
		Tpl::output('list_setting',$list_setting);
		
        Tpl::output('top_link',$this->sublink($this->links,'sms'));
		Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shopwwi.sms');
	}
			/**
	 * 默认微信公众号设置 
	 */
	public function webchatOp(){
		$model_setting = Model('setting');
		if (chksubmit()){
			$update_array = array();
			$update_array['shopwwi_webchat_appid'] 	= $_POST['shopwwi_webchat_appid'];
			$update_array['shopwwi_webchat_appsecret'] 	= $_POST['shopwwi_webchat_appsecret'];
			$result = $model_setting->updateSetting($update_array);
			if ($result === true){
				$this->log(L('nc_edit,sms_set'),1);
				showMessage(L('nc_common_save_succ'));
			}else {
				$this->log(L('nc_edit,sms_set'),0);
				showMessage(L('nc_common_save_fail'));
			}
		}
		$list_setting = $model_setting->getListSetting();
		Tpl::output('list_setting',$list_setting);
		
        Tpl::output('top_link',$this->sublink($this->links,'webchat'));
		Tpl::setDirquna('system');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('shopwwi.webchat');
	}
}