<?php
/**
 * 前台模块编辑(首页)
 *
 *
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */



defined('ByShopWWI') or exit('Access Invalid!');
class web_configControl extends SystemControl{
    public function __construct(){
        parent::__construct();
        Language::read('web_config');
        Language::read('adv');
    }

    public function indexOp() {
        $this->web_configOp();
    }

    /**
     * 板块列表
     */
    public function web_configOp(){
        $model_web_config = Model('web_config');
        $style_array = $model_web_config->getStyleList();//板块样式数组
        Tpl::output('style_array',$style_array);
        $web_list = $model_web_config->getWebList(array('web_page' => 'index'));
        Tpl::output('web_list',$web_list);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('web_config.index');
    }

    /**
     * 板块列表
     */
    public function web_config_categoryOp(){
        /** @var web_configModel $model_web_config */
        $model_web_config = Model('web_config');
        $style_array = $model_web_config->getStyleList();//板块样式数组
        Tpl::output('style_array',$style_array);
        $web_list = $model_web_config->getWebList(array('web_page' => 'category'));
        Tpl::output('web_list',$web_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('web_config_category.index');
    }

    /**
     * 自定义板块列表
     */
    public function web_tab_customOp(){
        $model_web_config = Model('web_config');
        $style_array = $model_web_config->getStyleList(); // 板块样式数组
        Tpl::output('style_array', $style_array);
        $web_list = $model_web_config->getWebList(array(
            'web_page' => 'custom'
        ));
        Tpl::output('web_list', $web_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('web_tab_custom.index');
    }

    /**
     * 头部自营区
     */
    public function web_tab_selfOp() {
        /** @var web_configModel $model_web_config */
        $model_web_config = Model('web_config');
        $style_array = $model_web_config->getStyleList(); // 板块样式数组
        Tpl::output('style_array', $style_array);
        $web_list = $model_web_config->getWebList(array(
            'web_page' => 'self'
        ));
        Tpl::output('web_list', $web_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('web_tab_self.index');

    }

    /**
     * 分类板块基本设置
     */
    public function web_config_category_editOp(){
        /** @var web_configModel $model_web_config */
        $model_web_config = Model('web_config');
        $web_id = empty($_GET["web_id"]) ? 0 : intval($_GET["web_id"]);
        if (chksubmit()) {
            $web_array = array();
            $web_id = empty($_POST["web_id"]) ? 0 : intval($_POST["web_id"]);
            $web_array['web_name'] = $_POST["web_name"];
            $web_array['style_name'] = $_POST["style_name"];
            $web_array['web_sort'] = intval($_POST["web_sort"]);
            $web_array['web_show'] = intval($_POST["web_show"]);
            $web_array['web_page'] = 'category';
            $web_array['update_time'] = time();

            //判断编辑的风格名，是否已经存在
            $style_exist = $model_web_config->table('web')->where(array('web_id' => array('neq', $web_id), 'style_name' => $web_array['style_name']))->count();
            if ($style_exist) {
                showMessage("这个风格【{$web_array['style_name']}】已经存在了啦，请重新指定！");
            }

            if (empty($web_id)) {
                $web_id = $model_web_config->addWeb($web_array);
                // 添加web成功后，添加具体子版块 空的代码数据
                $web_code_arr = array(
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'tit',
                        'code_info' => '',
                        'show_name' => '标题图片'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'category_list',
                        'code_info' => '',
                        'show_name' => '推荐分类'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'act',
                        'code_info' => '',
                        'show_name' => '活动图片'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'recommend_list',
                        'code_info' => '',
                        'show_name' => '商品推荐'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'adv',
                        'code_info' => '',
                        'show_name' => '广告图片'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'brand_list',
                        'code_info' => '',
                        'show_name' => '品牌推荐'
                    )
                );
                $model_web_config->table('web_code')->insertAll($web_code_arr);

            } else {
                $model_web_config->updateWeb(array(
                    'web_id' => $web_id
                ), $web_array);
                $model_web_config->updateWebHtml($web_id); // 更新前台显示的html内容
            }

            $this->log(l('web_config_code_edit') . '[' . $_POST["web_name"] . ']', 1);
            showMessage(Language::get('nc_common_save_succ'), 'index.php?act=web_config&op=web_config_category');
        }
        $web_list = array();
        if (empty($web_id)) {
            $web_list[0] = array();
        } else {
            $web_list = $model_web_config->getWebList(array(
                'web_id' => $web_id
            ));
        }

        Tpl::output('web_array', $web_list[0]);
        Tpl::setDirquna('shop');
        Tpl::showpage('web_config_category.edit');
    }

    /**
     * 自定义板块基本设置
     */
    public function web_tab_self_editOp(){
        $model_web_config = Model('web_config');
        $web_id = empty($_GET["web_id"]) ? 0 : intval($_GET["web_id"]);
        if (chksubmit()) {
            $web_array = array();
            $web_id = empty($_POST["web_id"]) ? 0 : intval($_POST["web_id"]);
            $web_array['web_name'] = $_POST["web_name"];
            $web_array['style_name'] = $_POST["style_name"];
            $web_array['web_sort'] = intval($_POST["web_sort"]);
            $web_array['web_show'] = intval($_POST["web_show"]);
            $web_array['web_page'] = 'self';
            $web_array['update_time'] = time();

            //判断编辑的风格名，是否已经存在
            $style_exist = $model_web_config->table('web')->where(array('web_id' => array('neq', $web_id), 'style_name' => $web_array['style_name']))->count();
            if ($style_exist) {
                showMessage("这个风格【{$web_array['style_name']}】已经存在了啦，请重新指定！");
            }

            if (empty($web_id)) {
                $web_id = $model_web_config->addWeb($web_array);
                // 添加web成功后，添加具体子版块 空的代码数据
                $web_code_arr = array(
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'sale_list',
                        'code_info' => '',
                        'show_name' => '商品组'
                    )
                );
                $model_web_config->table('web_code')->insertAll($web_code_arr);

            } else {
                $model_web_config->updateWeb(array(
                    'web_id' => $web_id
                ), $web_array);
                $model_web_config->updateWebHtml($web_id); // 更新前台显示的html内容
            }

            $this->log(l('web_config_code_edit') . '[' . $_POST["web_name"] . ']', 1);
            showMessage(Language::get('nc_common_save_succ'), 'index.php?act=web_config&op=web_tab_custom');
        }
        $web_list = array();
        if (empty($web_id)) {
            $web_list[0] = array();
        } else {
            $web_list = $model_web_config->getWebList(array(
                'web_id' => $web_id
            ));
        }

        Tpl::output('web_array', $web_list[0]);
        Tpl::setDirquna('shop');
        Tpl::showpage('web_tab_custom.edit');
    }

    /**
     * 自营区编辑
     */
    public function web_self_code_editOp() {
        $model_web_config = Model('web_config');
        $web_id = empty($_GET["web_id"]) ? 0 : intval($_GET["web_id"]);

        if (!$web_id) {
            showMessage("请先选择需要编辑的记录");
        }

        $code_list = $model_web_config->getCodeList(array('web_id'=> $web_id));

        if(is_array($code_list) && !empty($code_list)) {
            $model_class = Model('goods_class');
            $goods_class = $model_class->getTreeClassList(1);//第一级商品分类
            Tpl::output('goods_class',$goods_class);
            foreach ($code_list as $key => $val) {//将变量输出到页面
                $var_name = $val['var_name'];
                $code_info = $val['code_info'];
                $code_type = $val['code_type'];
                $val['code_info'] = $model_web_config->get_array($code_info,$code_type);
                Tpl::output('code_'.$var_name,$val);
            }
        }
        Tpl::setDirquna('shop');
        Tpl::showpage('web_tab_self.edit');
    }

    /**
     * 自定义板块基本设置
     */
    public function web_tab_custom_editOp(){
        $model_web_config = Model('web_config');
        $web_id = empty($_GET["web_id"]) ? 0 : intval($_GET["web_id"]);
        if (chksubmit()) {
            $web_array = array();
            $web_id = empty($_POST["web_id"]) ? 0 : intval($_POST["web_id"]);
            $web_array['web_name'] = $_POST["web_name"];
            $web_array['style_name'] = $_POST["style_name"];
            $web_array['web_sort'] = intval($_POST["web_sort"]);
            $web_array['web_show'] = intval($_POST["web_show"]);
            $web_array['web_page'] = 'custom';
            $web_array['update_time'] = time();

            //判断编辑的风格名，是否已经存在
            $style_exist = $model_web_config->table('web')->where(array('web_id' => array('neq', $web_id), 'style_name' => $web_array['style_name']))->count();
            if ($style_exist) {
                showMessage("这个风格【{$web_array['style_name']}】已经存在了啦，请重新指定！");
            }

            if (empty($web_id)) {
                $web_id = $model_web_config->addWeb($web_array);
                // 添加web成功后，添加具体子版块 空的代码数据
                $web_code_arr = array(
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'words',
                        'code_info' => '',
                        'show_name' => '文字链'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'adv',
                        'code_info' => '',
                        'show_name' => '图片组'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'focus_list',
                        'code_info' => '',
                        'show_name' => '焦点组'
                    ),
                    array(
                        'web_id' => $web_id,
                        'code_type' => 'array',
                        'var_name' => 'recommend_list',
                        'code_info' => '',
                        'show_name' => '商品组'
                    )
                );
                $model_web_config->table('web_code')->insertAll($web_code_arr);

            } else {
                $model_web_config->updateWeb(array(
                    'web_id' => $web_id
                ), $web_array);
                $model_web_config->updateWebHtml($web_id); // 更新前台显示的html内容
            }

            $this->log(l('web_config_code_edit') . '[' . $_POST["web_name"] . ']', 1);
            showMessage(Language::get('nc_common_save_succ'), 'index.php?act=web_config&op=web_tab_custom');
        }
        $web_list = array();
        if (empty($web_id)) {
            $web_list[0] = array();
        } else {
            $web_list = $model_web_config->getWebList(array(
                'web_id' => $web_id
            ));
        }

        Tpl::output('web_array', $web_list[0]);
        Tpl::setDirquna('shop');
        Tpl::showpage('web_tab_custom.edit');
    }

    /**
     * 自定义板块设计
     */
    public function tab_custom_code_editOp(){
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        $code_list = $model_web_config->getCodeList(array(
            'web_id' => "$web_id"
        ));
        if (is_array($code_list) && ! empty($code_list)) {
            $model_class = Model('goods_class');
            $parent_goods_class = $model_class->getTreeClassList(2); // 商品分类父类列表，只取到第二级
            if (is_array($parent_goods_class) && ! empty($parent_goods_class)) {
                foreach ($parent_goods_class as $k => $v) {
                    $parent_goods_class[$k]['gc_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['gc_name'];
                }
            }
            Tpl::output('parent_goods_class', $parent_goods_class);

            $goods_class = $model_class->getTreeClassList(1); // 第一级商品分类
            Tpl::output('goods_class', $goods_class);

            foreach ($code_list as $key => $val) { // 将变量输出到页面
                $var_name = $val["var_name"];
                $code_info = $val["code_info"];
                $code_type = $val["code_type"];
                $val['code_info'] = $model_web_config->get_array($code_info, $code_type);
                Tpl::output('code_' . $var_name, $val);
            }
            $style_array = $model_web_config->getStyleList(); // 样式数组
            Tpl::output('style_array', $style_array);
            $web_list = $model_web_config->getWebList(array(
                'web_id' => $web_id
            ));
            Tpl::output('web_array', $web_list[0]);
            Tpl::setDirquna('shop');
            Tpl::showpage('web_tab_custom.code_edit');
        } else {
            showMessage(Language::get('nc_no_record'));
        }
    }

    /**
     * 自定义广告位设计
     */
    public function tab_custom_advOp(){
        Tpl::output('ap_name',"真品真实惠");
        Tpl::setDirquna('shop');
        Tpl::showpage('web_tab_custom_adv.index');
    }

    /**
     *
     * 修改广告
     */
    public function adv_editOp(){
        if (empty($_GET['adv_id'])) {
            showMessage('参数错误');
        }
        if($_POST['form_submit'] != 'ok'){
            $adv  = Model('adv');
            $condition['adv_id'] = intval($_GET['adv_id']);
            $adv_list = $adv->getList($condition);
            $ap_info  = $adv->getApList();
            Tpl::output('ref_url',getReferer());
            Tpl::output('adv_list',$adv_list);
            Tpl::output('ap_info',$ap_info);
            Tpl::setDirquna('shop');
            Tpl::showpage('web_tab_custom_adv.edit');
        }else{
            $lang = Language::getLangContent();
            $adv  = Model('adv');
            $upload     = new UploadFile();
            /**
             * 验证
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["adv_name"], "require"=>"true", "message"=>$lang['ap_can_not_null']),
                array("input"=>$_POST["adv_start_date"], "require"=>"true","message"=>$lang['must_select_start_time']),
                array("input"=>$_POST["adv_end_date"], "require"=>"true", "message"=>$lang['must_select_end_time'])
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $param['adv_id']         = intval($_GET['adv_id']);
                $param['adv_title']      = trim($_POST['adv_name']);
                $param['adv_start_date'] = $this->getunixtime(trim($_POST['adv_start_date']));
                $param['adv_end_date']   = $this->getunixtime(trim($_POST['adv_end_date']));
                /**
                 * 建立图片广告信息的入库数组
                 */
                if($_POST['mark'] == '0'){
                    if($_FILES['adv_pic']['name'] != ''){
                        $upload->set('default_dir',ATTACH_ADV);
                        $result = $upload->upfile('adv_pic');
                        if (!$result){
                            showMessage($upload->error,'','','error');
                        }
                        $ac = array(
                            'adv_pic'     =>$upload->file_name,
                            'adv_pic_url' =>trim($_POST['adv_pic_url'])
                        );
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    }else{
                        $ac = array(
                            'adv_pic'     =>trim($_POST['pic_ori']),
                            'adv_pic_url' =>trim($_POST['adv_pic_url'])
                        );
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    }
                }
                /**
                 * 建立文字广告信息的入库数组
                 */
                if($_POST['mark'] == '1'){
                    //判断页面编码确定汉字所占字节数
                    switch (CHARSET){
                        case 'UTF-8':
                            $charrate = 3;
                            break;
                        case 'GBK':
                            $charrate = 2;
                            break;
                    }
                    if(strlen($_POST['adv_word'])>($_POST['adv_word_len']*$charrate)){
                        $error = $lang['wordadv_toolong'];
                        showMessage($error);die;
                    }
                    $ac = array(
                        'adv_word'    =>trim($_POST['adv_word']),
                        'adv_word_url'=>trim($_POST['adv_word_url'])
                    );
                    $ac = serialize($ac);
                    $param['adv_content'] = $ac;
                }
                /**
                 * 建立Flash广告信息的入库数组
                 */
                if($_POST['mark'] == '3'){
                    if($_FILES['flash_swf']['name'] != ''){
                        $upload->set('default_dir',ATTACH_ADV);
                        $result = $upload->upfile('flash_swf');
                        $ac = array(
                            'flash_swf'  =>$upload->file_name,
                            'flash_url'  =>trim($_POST['flash_url'])
                        );
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    }else{
                        $ac = array(
                            'flash_swf'  =>trim($_POST['flash_ori']),
                            'flash_url'  =>trim($_POST['flash_url'])
                        );
                        $ac = serialize($ac);
                        $param['adv_content'] = $ac;
                    }
                }
                $result = $adv->updates($param);

                if ($result){
                    $url = array(
                        array(
                            'url'=>trim($_POST['ref_url']),
                            'msg'=>$lang['goback_ap_manage'],
                        )
                    );
                    $this->log(L('adv_change_succ').'['.$_POST["adv_name"].']',null);
                    showMessage($lang['adv_change_succ'],$url);
                }else {
                    showMessage($lang['adv_change_fail'],$url);
                }
            }
        }
    }

    /**
     *
     * 获取UNIX时间戳
     */
    public function getunixtime($time){
        $array     = explode("-", $time);
        $unix_time = mktime(0,0,0,$array[1],$array[2],$array[0]);
        return $unix_time;
    }

    public function get_adv_xmlOp(){
        $lang = Language::getLangContent();
        $adv  = Model('adv');
        $condition  = array();
//        $condition['ap_id'] = intval($_GET['ap_id']);
        $condition['ap_in_id'] = '39,40,41,42,43,44,45,46';
        $condition['adv_buy_id'] = 38;
        if ($_POST['query'] != '' && in_array($_POST['qtype'],array('adv_title'))) {
            $condition[$_POST['qtype']] = $_POST['query'];
        }
        $sort_fields = array('ap_id','adv_start_date','adv_end_date');
        if ($_POST['sortorder'] != '' && in_array($_POST['sortname'],$sort_fields)) {
            $order = $_POST['sortname'].' '.$_POST['sortorder'];
        }
        $condition['is_allow'] = '1';
        $page = new Page();
        $page->setEachNum($_POST['rp']);
        $page->setStyle('admin');
        $adv_list  = $adv->getList($condition,$page,'',$order);
        $ap_list = $adv->getApList();
        $data = array();
        $data['now_page'] = $page->get('now_page');
        $data['total_num'] = $page->get('total_num');
        foreach ((array)$adv_list as $k => $adv_info) {
            $list = array();$operation_detail = '';
            $list['operation'] = "<a class='btn red' onclick=\"fg_delete({$adv_info['adv_id']})\"><i class='fa fa-trash-o'></i>删除</a><a class='btn blue' href='index.php?act=web_config&op=adv_edit&adv_id={$adv_info['adv_id']}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
            $list['adv_title'] = $adv_info['adv_title'];
            $list['ap_id'] = $list['ap_class'];
            $list['ap_class'] = '';
            foreach ($ap_list as $ap_k => $ap_v){
                if($adv_info['ap_id'] == $ap_v['ap_id']){
                    $list['ap_id'] = $ap_v['ap_name'];
                    $list['ap_class'] = str_replace(array(0,1,3), array('图片','文字','Flash'),$ap_v['ap_class']);
                    break;
                }
            }
            $list['adv_start_date'] = date('Y-m-d',$adv_info['adv_start_date']);
            $list['adv_end_date'] = date('Y-m-d',$adv_info['adv_end_date']);
            $data['list'][$adv_info['adv_id']] = $list;
        }
        exit(Tpl::flexigridXML($data));
    }


    /**
     * 基本设置
     */
    public function web_editOp(){
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        if (chksubmit()){
            $web_array = array();
            $web_id = intval($_POST["web_id"]);
            $web_array['web_name'] = $_POST["web_name"];
            $web_array['style_name'] = $_POST["style_name"];
            $web_array['web_sort'] = intval($_POST["web_sort"]);
            $web_array['web_show'] = intval($_POST["web_show"]);
            $web_array['update_time'] = time();
            $model_web_config->updateWeb(array('web_id'=>$web_id),$web_array);
            $model_web_config->updateWebHtml($web_id);//更新前台显示的html内容
            $this->log(l('web_config_code_edit').'['.$_POST["web_name"].']',1);
            showMessage(Language::get('nc_common_save_succ'),'index.php?act=web_config&op=web_config');
        }
        $web_list = $model_web_config->getWebList(array('web_id'=>$web_id));
        Tpl::output('web_array',$web_list[0]);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('web_config.edit');
    }

    /**
     * 板块编辑
     */
    public function code_editOp(){
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        $code_list = $model_web_config->getCodeList(array('web_id'=>"$web_id"));
        if(is_array($code_list) && !empty($code_list)) {
            $model_class = Model('goods_class');
            $parent_goods_class = $model_class->getTreeClassList(2);//商品分类父类列表，只取到第二级
            if (is_array($parent_goods_class) && !empty($parent_goods_class)){
                foreach ($parent_goods_class as $k => $v){
                    $parent_goods_class[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['gc_name'];
                }
            }
            Tpl::output('parent_goods_class',$parent_goods_class);

            $goods_class = $model_class->getTreeClassList(1);//第一级商品分类
            Tpl::output('goods_class',$goods_class);

            foreach ($code_list as $key => $val) {//将变量输出到页面
                $var_name = $val["var_name"];
                $code_info = $val["code_info"];
                $code_type = $val["code_type"];
                $val['code_info'] = $model_web_config->get_array($code_info,$code_type);
                Tpl::output('code_'.$var_name,$val);
            }
            $style_array = $model_web_config->getStyleList();//样式数组
            Tpl::output('style_array',$style_array);
            $web_list = $model_web_config->getWebList(array('web_id'=>$web_id));
            Tpl::output('web_array',$web_list[0]);
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('web_code.edit');
        } else {
            showMessage(Language::get('nc_no_record'));
        }
    }

    /**
     * 分类板块编辑
     */
    public function category_code_editOp(){
        /** @var web_configModel $model_web_config */
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        $code_list = $model_web_config->getCodeList(array('web_id'=>"$web_id"));
        if(is_array($code_list) && !empty($code_list)) {
            $model_class = Model('goods_class');
            $parent_goods_class = $model_class->getTreeClassList(2);//商品分类父类列表，只取到第二级
            if (is_array($parent_goods_class) && !empty($parent_goods_class)){
                foreach ($parent_goods_class as $k => $v){
                    $parent_goods_class[$k]['gc_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['gc_name'];
                }
            }
            Tpl::output('parent_goods_class',$parent_goods_class);

            $goods_class = $model_class->getTreeClassList(1);//第一级商品分类
            Tpl::output('goods_class',$goods_class);

            foreach ($code_list as $key => $val) {//将变量输出到页面
                $var_name = $val["var_name"];
                $code_info = $val["code_info"];
                $code_type = $val["code_type"];
                $val['code_info'] = $model_web_config->get_array($code_info,$code_type);
                Tpl::output('code_'.$var_name,$val);
            }
            $style_array = $model_web_config->getStyleList();//样式数组
            Tpl::output('style_array',$style_array);
            $web_list = $model_web_config->getWebList(array('web_id'=>$web_id));
            Tpl::output('web_array',$web_list[0]);
            Tpl::setDirquna('shop');
            Tpl::showpage('web_code_category.edit');
        } else {
            showMessage(Language::get('nc_no_record'));
        }
    }

    /**
     * 更新自定义板块显示的html内容
     */
    public function tab_custom_web_htmlOp(){
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        $web_list = $model_web_config->getWebList(array(
            'web_id' => $web_id
        ));
        $web_array = $web_list[0];
        if (! empty($web_array) && is_array($web_array)) {
            $model_web_config->updateWebHtml($web_id, $web_array);
            showMessage(Language::get('nc_common_op_succ'), 'index.php?act=web_config&op=web_tab_custom');
        } else {
            showMessage(Language::get('nc_common_op_fail'));
        }
    }

    /**
     * 更新前台显示的html内容
     */
    public function web_htmlOp(){
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        $web_list = $model_web_config->getWebList(array('web_id'=>$web_id));
        $web_array = $web_list[0];
        if(!empty($web_array) && is_array($web_array)) {
            $model_web_config->updateWebHtml($web_id,$web_array);
            showMessage(Language::get('nc_common_op_succ'),'index.php?act=web_config&op=web_config');
        } else {
            showMessage(Language::get('nc_common_op_fail'));
        }
    }



    /**
     * 头部切换图设置
     */
    public function focus_editOp() {
        $model_web_config = Model('web_config');
        
        if (!empty($_GET['web_id'])) {
            $web_id =  intval($_GET['web_id']);
            $tpl = 'web_focus.edit.custom';
        } else {
            $web_id =  array('in','101,102');
            $tpl = 'web_focus.edit';
        }
        
        $code_list = $model_web_config->getCodeList(array('web_id'=> $web_id));
        if(is_array($code_list) && !empty($code_list)) {
            foreach ($code_list as $key => $val) {//将变量输出到页面
                $var_name = $val['var_name'];
                $code_info = $val['code_info'];
                $code_type = $val['code_type'];
                $val['code_info'] = $model_web_config->get_array($code_info,$code_type);
                Tpl::output('code_'.$var_name,$val);
            }
        }
        $screen_adv_list = $model_web_config->getAdvList("screen");//焦点大图广告数据
        Tpl::output('screen_adv_list',$screen_adv_list);
        $focus_adv_list = $model_web_config->getAdvList("focus");//三张联动区广告数据
        Tpl::output('focus_adv_list',$focus_adv_list);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/

        Tpl::showpage($tpl);
    }

    /**
     * 更新html内容
     */
    public function html_updateOp() {
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        $web_list = $model_web_config->getWebList(array('web_id'=> $web_id));
        $web_array = $web_list[0];
        if(!empty($web_array) && is_array($web_array)) {
            $model_web_config->updateWebHtml($web_id,$web_array);
            showMessage(Language::get('nc_common_op_succ'));
        } else {
            showMessage(Language::get('nc_common_op_fail'));
        }
    }

    /**
     * 头部促销区
     */
    public function sale_editOp() {
        $model_web_config = Model('web_config');
        $web_id = '121';
        $code_list = $model_web_config->getCodeList(array('web_id'=> $web_id));
        if(is_array($code_list) && !empty($code_list)) {
            $model_class = Model('goods_class');
            $goods_class = $model_class->getTreeClassList(1);//第一级商品分类
            Tpl::output('goods_class',$goods_class);
            foreach ($code_list as $key => $val) {//将变量输出到页面
                $var_name = $val['var_name'];
                $code_info = $val['code_info'];
                $code_type = $val['code_type'];
                $val['code_info'] = $model_web_config->get_array($code_info,$code_type);
                Tpl::output('code_'.$var_name,$val);
            }
        }
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('web_sale.edit');
    }

    /**
     * 商品分类
     */
    public function category_listOp() {
        $model_class = Model('goods_class');
        $gc_parent_id = intval($_GET['id']);
        $goods_class = $model_class->getGoodsClassListByParentId($gc_parent_id);
        Tpl::output('goods_class',$goods_class);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('web_goods_class','null_layout');
    }

    /**
     * 商品推荐
     */
    public function recommend_listOp() {
        $model_web_config = Model('web_config');
        $condition = array();
        $gc_id = intval($_GET['id']);
        if ($gc_id > 0) {
            $condition['gc_id'] = $gc_id;
        }
        $goods_name = trim($_GET['goods_name']);
        if (!empty($goods_name)) {
            $goods_id = intval($_GET['goods_name']);
            if (is_numeric($goods_name)) {
                $condition['goods_id'] = $goods_id;
            } else {
                $condition['goods_name'] = array('like','%'.$goods_name.'%');
            }
        }
        $goods_list = $model_web_config->getGoodsList($condition,'goods_id desc',6);
        Tpl::output('show_page',$model_web_config->showpage(2));
        Tpl::output('goods_list',$goods_list);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('web_goods.list','null_layout');
    }

    /**
     * 商品排序查询
     */
    public function goods_listOp() {
        $model_web_config = Model('web_config');
        $condition = array();
        $order = 'goods_salenum desc,goods_id desc';//销售数量
        $goods_order = trim($_GET['goods_order']);
        if (!empty($goods_order)) {
            $order = $goods_order.' desc,goods_id desc';
        }
        $gc_id = intval($_GET['id']);
        if ($gc_id > 0) {
            $condition['gc_id'] = $gc_id;
        }
        $goods_name = trim($_GET['goods_name']);
        if (!empty($goods_name)) {
            $goods_id = intval($_GET['goods_name']);
            if ($goods_id === $goods_name) {
                $condition['goods_id'] = $goods_id;
            } else {
                $condition['goods_name'] = array('like','%'.$goods_name.'%');
            }
        }
        $goods_list = $model_web_config->getGoodsList($condition,$order,6);
        Tpl::output('show_page',$model_web_config->showpage(2));
        Tpl::output('goods_list',$goods_list);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('web_goods_order','null_layout');
    }

    /**
     * 品牌
     */
    public function brand_listOp() {
        $model_brand = Model('brand');
        /**
         * 检索条件
         */
        $condition = array();
        if (!empty($_GET['brand_name'])) {
            $condition['brand_name'] = array('like', '%' . trim($_GET['brand_name']) . '%');
        }
        if (!empty($_GET['brand_initial'])) {
            $condition['brand_initial'] = trim($_GET['brand_initial']);
        }
        $brand_list = $model_brand->getBrandPassedList($condition, '*', 6);
        Tpl::output('show_page',$model_brand->showpage());
        Tpl::output('brand_list',$brand_list);
		Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
        Tpl::showpage('web_brand.list','null_layout');
    }

    /**
     * 保存设置
     */
    public function code_updateOp() {
        $code_id = intval($_POST['code_id']);
        $web_id = intval($_POST['web_id']);
        $model_web_config = Model('web_config');
        $code = $model_web_config->getCodeRow($code_id,$web_id);
        if (!empty($code)) {
            $code_type = $code['code_type'];
            $var_name = $code['var_name'];
            $code_info = $_POST[$var_name];
            $code_info = $model_web_config->get_str($code_info,$code_type);
            $state = $model_web_config->updateCode(array('code_id'=> $code_id),array('code_info'=> $code_info));
        }
        if($state) {
            echo '1';exit;
        } else {
            echo '0';exit;
        }
    }

    /**
     * 保存图片
     */
    public function upload_picOp() {
        $code_id = intval($_POST['code_id']);
        $web_id = intval($_POST['web_id']);
        $model_web_config = Model('web_config');
        $code = $model_web_config->getCodeRow($code_id,$web_id);
        if (!empty($code)) {
            $code_type = $code['code_type'];
            $var_name = $code['var_name'];
            $code_info = $_POST[$var_name];

            $file_name = 'web-'.$web_id.'-'.$code_id;
            $pic_name = $this->_upload_pic($file_name);//上传图片
            if (!empty($pic_name)) {
                $code_info['pic'] = $pic_name;
            }

            Tpl::output('var_name',$var_name);
            Tpl::output('pic',$code_info['pic']);
            Tpl::output('type',$code_info['type']);
            Tpl::output('ap_id',$code_info['ap_id']);
            $code_info = $model_web_config->get_str($code_info,$code_type);
            $state = $model_web_config->updateCode(array('code_id'=> $code_id),array('code_info'=> $code_info));
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('web_upload_pic','null_layout');
        }
    }

    /**
     * 中部推荐图片
     */
    public function recommend_picOp() {
        $code_id = intval($_POST['code_id']);
        $web_id = intval($_POST['web_id']);
        $model_web_config = Model('web_config');
        $code = $model_web_config->getCodeRow($code_id,$web_id);
        $key_id = intval($_POST['key_id']);
        $pic_id = intval($_POST['pic_id']);
        if (!empty($code) && $key_id > 0 && $pic_id > 1) {
            $code_info = $code['code_info'];
            $code_type = $code['code_type'];
            $code_info = $model_web_config->get_array($code_info,$code_type);//原数组

            $var_name = "pic_list";
            $pic_info = $_POST[$var_name];
            $pic_info['pic_id'] = $pic_id;
            if (!empty($code_info[$key_id]['pic_list'][$pic_id]['pic_img'])) {//原图片
                $pic_info['pic_img'] = $code_info[$key_id]['pic_list'][$pic_id]['pic_img'];
            }

            $file_name = 'web-'.$web_id.'-'.$code_id.'-'.$key_id.'-'.$pic_id;
            $pic_name = $this->_upload_pic($file_name);//上传图片
            if (!empty($pic_name)) {
                $pic_info['pic_img'] = $pic_name;
            }

            $recommend_list = array();
            $recommend_list = $_POST['recommend_list'];
            $recommend_list['pic_list'] = $code_info[$key_id]['pic_list'];
            $code_info[$key_id] = $recommend_list;
            $code_info[$key_id]['pic_list'][$pic_id] = $pic_info;

            Tpl::output('pic',$pic_info);
            $code_info = $model_web_config->get_str($code_info,$code_type);
            $state = $model_web_config->updateCode(array('code_id'=> $code_id),array('code_info'=> $code_info));
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/
            Tpl::showpage('web_recommend_pic','null_layout');
        }
    }

    /**
     * 保存切换图片
     */
    public function slide_advOp() {
        $code_id = intval($_POST['code_id']);
        $web_id = intval($_POST['web_id']);
        $model_web_config = Model('web_config');
        $code = $model_web_config->getCodeRow($code_id,$web_id);
        if (!empty($code)) {
            $code_type = $code['code_type'];
            $var_name = $code['var_name'];
            $code_info = $_POST[$var_name];

            $pic_id = intval($_POST['slide_id']);
            if ($pic_id > 0) {
                $var_name = "slide_pic";
                $pic_info = $_POST[$var_name];
                $pic_info['pic_id'] = $pic_id;
                if (!empty($code_info[$pic_id]['pic_img'])) {//原图片
                    $pic_info['pic_img'] = $code_info[$pic_id]['pic_img'];
                }
                $file_name = 'web-'.$web_id.'-'.$code_id.'-'.$pic_id;
                $pic_name = $this->_upload_pic($file_name);//上传图片
                if (!empty($pic_name)) {
                    $pic_info['pic_img'] = $pic_name;
                }

                $code_info[$pic_id] = $pic_info;
                Tpl::output('pic',$pic_info);
            }
            $code_info = $model_web_config->get_str($code_info,$code_type);
            $model_web_config->updateCode(array('code_id'=> $code_id),array('code_info'=> $code_info));
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/

            Tpl::showpage('web_upload_slide','null_layout');
        }
    }

    /**
     * 保存焦点区切换大图
     */
    public function screen_picOp() {
        $code_id = intval($_POST['code_id']);
        $web_id = intval($_POST['web_id']);
        $model_web_config = Model('web_config');
        $code = $model_web_config->getCodeRow($code_id,$web_id);
        if (!empty($code)) {
            $code_type = $code['code_type'];
            $var_name = $code['var_name'];
            $code_info = $_POST[$var_name];

            $key = intval($_POST['key']);
            $ap_pic_id = intval($_POST['ap_pic_id']);
            if ($ap_pic_id > 0 && $ap_pic_id == $key) {
                $ap_color = $_POST['ap_color'];
                $code_info[$ap_pic_id]['color'] = $ap_color;
                Tpl::output('ap_pic_id',$ap_pic_id);
                Tpl::output('ap_color',$ap_color);
            }
            $pic_id = intval($_POST['screen_id']);
            if ($pic_id > 0 && $pic_id == $key) {
                $var_name = "screen_pic";
                $pic_info = $_POST[$var_name];
                $pic_info['pic_id'] = $pic_id;
                if (!empty($code_info[$pic_id]['pic_img'])) {//原图片
                    $pic_info['pic_img'] = $code_info[$pic_id]['pic_img'];
                }
                $file_name = 'web-'.$web_id.'-'.$code_id.'-'.$pic_id;
                $pic_name = $this->_upload_pic($file_name);//上传图片
                if (!empty($pic_name)) {
                    $pic_info['pic_img'] = $pic_name;
                }

                $code_info[$pic_id] = $pic_info;
                Tpl::output('pic',$pic_info);
            }
            $code_info = $model_web_config->get_str($code_info,$code_type);
            $model_web_config->updateCode(array('code_id'=> $code_id),array('code_info'=> $code_info));
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/

            Tpl::showpage('web_upload_screen','null_layout');
        }
    }

    /**
     * 保存焦点区切换小图
     */
    public function focus_picOp() {
        $code_id = intval($_POST['code_id']);
        $web_id = intval($_POST['web_id']);
        $model_web_config = Model('web_config');
        $code = $model_web_config->getCodeRow($code_id,$web_id);
        if (!empty($code)) {
            $code_type = $code['code_type'];
            $var_name = $code['var_name'];
            $code_info = $_POST[$var_name];

            $key = intval($_POST['key']);
            $slide_id = intval($_POST['slide_id']);
            $pic_id = intval($_POST['pic_id']);
            if ($pic_id > 0 && $slide_id == $key) {
                $var_name = "focus_pic";
                $pic_info = $_POST[$var_name];
                $pic_info['pic_id'] = $pic_id;
                if (!empty($code_info[$slide_id]['pic_list'][$pic_id]['pic_img'])) {//原图片
                    $pic_info['pic_img'] = $code_info[$slide_id]['pic_list'][$pic_id]['pic_img'];
                }
                $file_name = 'web-'.$web_id.'-'.$code_id.'-'.$slide_id.'-'.$pic_id;
                $pic_name = $this->_upload_pic($file_name);//上传图片
                if (!empty($pic_name)) {
                    $pic_info['pic_img'] = $pic_name;
                }

                $code_info[$slide_id]['pic_list'][$pic_id] = $pic_info;
                Tpl::output('pic',$pic_info);
            }
            $code_info = $model_web_config->get_str($code_info,$code_type);
            $model_web_config->updateCode(array('code_id'=> $code_id),array('code_info'=> $code_info));
			Tpl::setDirquna('shop');/*网 店 运 维shop wwi.com*/

            Tpl::showpage('web_upload_focus','null_layout');
        }
    }

    /**
     * 上传图片
     */
    private function _upload_pic($file_name) {
        $pic_name = '';
        if (!empty($file_name)) {
            if (!empty($_FILES['pic']['name'])) {//上传图片
                $upload = new UploadFile();
                $filename_tmparr = explode('.', $_FILES['pic']['name']);
                $ext = end($filename_tmparr);
                $upload->set('default_dir',ATTACH_EDITOR);
                $upload->set('file_name',$file_name.".".$ext);
                $result = $upload->upfile('pic');
                if ($result) {
                    $pic_name = ATTACH_EDITOR."/".$upload->file_name.'?'.mt_rand(100,999);//加随机数防止浏览器缓存图片
                }
            }
        }
        return $pic_name;
    }
}
