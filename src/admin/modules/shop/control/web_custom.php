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

class web_customControl extends SystemControl {

    public function __construct() {
        parent::__construct();
        Language::read('web_config');
    }

    public function indexOp() {
        $this->web_configOp();
    }

    /**
     * 板块列表
     */
    public function web_configOp() {
        $model_web_config = Model('web_config');
        $style_array = $model_web_config->getStyleList(); // 板块样式数组
        Tpl::output('style_array', $style_array);
        $web_list = $model_web_config->getWebList(array(
            'web_page' => 'custom'
        ));
        Tpl::output('web_list', $web_list);
        Tpl::setDirquna('shop');
        Tpl::showpage('web_custom.index');
    }

    /**
     * 基本设置
     */
    public function web_editOp() {
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
            showMessage(Language::get('nc_common_save_succ'), 'index.php?act=web_custom&op=web_config');
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
        Tpl::showpage('web_custom.edit');
    }

    /**
     * 板块编辑
     */
    public function code_editOp() {
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
            Tpl::showpage('web_custom.code_edit');
        } else {
            showMessage(Language::get('nc_no_record'));
        }
    }

    /**
     * 更新前台显示的html内容
     */
    public function web_htmlOp() {
        $model_web_config = Model('web_config');
        $web_id = intval($_GET["web_id"]);
        $web_list = $model_web_config->getWebList(array(
            'web_id' => $web_id
        ));
        $web_array = $web_list[0];
        if (! empty($web_array) && is_array($web_array)) {
            $model_web_config->updateWebHtml($web_id, $web_array);
            showMessage(Language::get('nc_common_op_succ'), 'index.php?act=web_custom&op=web_config');
        } else {
            showMessage(Language::get('nc_common_op_fail'));
        }
    }

    /**
     * 头部切换图设置
     */
    public function focus_editOp() {
        $model_web_config = Model('web_config');
        
        if (! empty($_GET['web_id'])) {
            $web_id = intval($_GET['web_id']);
        }
        empty($web_id) && showMessage('未指定web id');
        
        $code_list = $model_web_config->getCodeList(array(
            'web_id' => $web_id
        ));
        if (is_array($code_list) && ! empty($code_list)) {
            foreach ($code_list as $key => $val) { // 将变量输出到页面
                $var_name = $val['var_name'];
                $code_info = $val['code_info'];
                $code_type = $val['code_type'];
                $val['code_info'] = $model_web_config->get_array($code_info, $code_type);
                Tpl::output('code_' . $var_name, $val);
            }
        }
        $screen_adv_list = $model_web_config->getAdvList("screen"); // 焦点大图广告数据
        Tpl::output('screen_adv_list', $screen_adv_list);
        $focus_adv_list = $model_web_config->getAdvList("focus"); // 三张联动区广告数据
        Tpl::output('focus_adv_list', $focus_adv_list);
        Tpl::setDirquna('shop'); 
        
        $web_list = $model_web_config->getWebList(array(
            'web_id' => $web_id
        ));
        Tpl::output('web_array', $web_list[0]);
        
        Tpl::showpage('web_custom.focus_edit');
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
}
