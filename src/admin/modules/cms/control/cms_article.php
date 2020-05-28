<?php
/**
 * cms文章分类
 *
 *
 *
 ** 本系统由汉购网 hangowa.com提供
 */

//use Shopwwi\Tpl;

defined('ByShopWWI') or exit('Access Invalid!');

class cms_articleControl extends SystemControl
{
    //文章状态草稿箱
    const ARTICLE_STATE_DRAFT = 1;
    //文章状态待审核
    const ARTICLE_STATE_VERIFY = 2;
    //文章状态已发布
    const ARTICLE_STATE_PUBLISHED = 3;
    //文章状态回收站
    const ARTICLE_STATE_RECYCLE = 4;

    public function __construct()
    {
        parent::__construct();
        Language::read('cms');
    }

    public function indexOp()
    {
        $this->cms_article_listOp();
    }

    public function cms_article_addOp()
    {
        if (chksubmit()) {
            //插入文章
            $param = array();
            $param['article_title'] = trim($_POST['article_title']);
            if (empty($_POST['article_title_short'])) {
                $param['article_title_short'] = mb_substr($_POST['article_title'], 0, 12, CHARSET);
            } else {
                $param['article_title_short'] = $_POST['article_title_short'];
            }
            $param['article_class_id'] = intval($_POST['article_class']);
            $param['article_origin'] = trim($_POST['article_origin']);
            $param['article_origin_address'] = trim($_POST['article_origin_address']);
            $param['article_author'] = trim($_POST['article_author']);
            $param['article_abstract'] = mb_substr(trim($_POST['article_abstract']), 0, 140, CHARSET);
            $param['article_content'] = trim($_POST['article_content']);
            $param['article_link'] = trim($_POST['article_link']);
            $param['article_keyword'] = trim($_POST['article_keyword']);
            $param['article_type'] = '1';//管理员发布
            $param['article_commend_flag'] = 0;
            $param['article_tag'] = trim($_POST['article_tag']);

            //发布时间
            $param['article_publish_time'] = time();
            $param['article_modify_time'] = time();
            $param['article_state'] = self::ARTICLE_STATE_PUBLISHED;

            if (!empty($_FILES['article_image']['name'])) {
                $upload = new UploadFile();
                $file_path = '';
                $upload->set('default_dir', ATTACH_CMS . DS . 'article' . DS . $file_path);
                $upload->set('thumb_width', '1024,240');
                $upload->set('thumb_height', '50000,5000');
                $upload->set('thumb_ext', '_max,_list');

                $result = $upload->upfile('article_image');
                if (!$result) {
                    showMessage($upload->error);
                }
                $article_image['name'] = $upload->file_name;
                $article_image['path'] = $file_path;
                $param['article_image'] = serialize($article_image);
            } else {
                showMessage("文章封面不能为空");
            }
            if (!empty($_FILES['article_publisher_avatar']['name'])) {
                $upload = new UploadFile();
                $file_path = '';
                $upload->set('default_dir', ATTACH_CMS . DS . 'article' . DS . $file_path);
                $upload->set('thumb_width', '1024,240');
                $upload->set('thumb_height', '50000,5000');
                $upload->set('thumb_ext', '_max,_list');

                $result = $upload->upfile('article_publisher_avatar');
                if (!$result) {
                    showMessage($upload->error);
                }
                $article_publisher_avatar['name'] = $upload->file_name;
                $article_publisher_avatar['path'] = $file_path;
                $param['article_publisher_avatar'] = serialize($article_publisher_avatar);
            } else {
                showMessage("用户头像不能为空");
            }

            if (empty($param['article_content'])) showMessage("文章内容不能为空");
            if (empty($param['article_image'])) showMessage("文章封面不能为空");
            if (empty($param['article_publisher_avatar'])) showMessage("用户头像不能为空");

            if (Model('cms_article')->save($param)) {
                showMessage("添加成功", 'index.php?act=cms_article');
            } else {
                showMessage("数据保存失败");
            }

        }
        $model_article_class = Model('cms_article_class');
        $article_class_list = $model_article_class->getList(TRUE, null, 'class_sort asc');
        Tpl::output('article_class_list', $article_class_list);
        Tpl::setDirquna('cms');
        Tpl::showpage('cms_article.add');
    }

    //文章修改保存
    public function  cms_article_saveOp(){
    if(empty($_POST['article_id'])){
        showMessage("缺少文章id", 'index.php?act=cms_article');
    }
        $param = array();
        $param['article_title'] = trim($_POST['article_title']);
        if (empty($_POST['article_title_short'])) {
            $param['article_title_short'] = mb_substr($_POST['article_title'], 0, 12, CHARSET);
        } else {
            $param['article_title_short'] = $_POST['article_title_short'];
        }
        $param['article_class_id'] = intval($_POST['article_class']);
        $param['article_content'] = trim($_POST['article_content']);
        $param['article_modify_time'] = time();
        if (!empty($_FILES['article_image']['name'])) {
            $upload = new UploadFile();
            $file_path = '';
            $upload->set('default_dir', ATTACH_CMS . DS . 'article' . DS . $file_path);
            $upload->set('thumb_width', '1024,240');
            $upload->set('thumb_height', '50000,5000');
            $upload->set('thumb_ext', '_max,_list');

            $result = $upload->upfile('article_image');
            if (!$result) {
                showMessage($upload->error);
            }
            $article_image['name'] = $upload->file_name;
            $article_image['path'] = $file_path;
            $param['article_image'] = serialize($article_image);
        }
       if (empty($param['article_content'])) showMessage("文章内容不能为空");
        $condition['article_id']=$_POST['article_id'];
        if (Model('cms_article')->modify($param,$condition)) {
            showMessage("修改成功", 'index.php?act=cms_article');
        } else {
            showMessage("数据修改失败");
        }
    }

    //浏览文章
    public function  cms_article_detailOp(){
        if(empty($_GET['article_id'])){
            showMessage('文章id为空', 'index.php?act=cms_article');
        }
        $model_article = Model('cms_article');
        $condition['article_id']=$_GET['article_id'];
        $article_detail=$model_article->getone($condition);
        if(!$model_article->isExist($condition)){
            showMessage('此文章不存在','index.php?act=cms_article');
        }
        Tpl::output('article_detail',$article_detail);
        Tpl::setDirquna('cms');
        Tpl::showpage('cms_article_detail');
    }

    //文章编辑
    public  function  cms_article_updateOp(){
        if(empty($_GET['article_id'])){
            showMessage('文章id为空');
        }
        $model_article = Model('cms_article');
        $condition['article_id']=$_GET['article_id'];
        $article_detail=$model_article->getone($condition);
        if(!$model_article->isExist($condition)){
            showMessage('此文章不存在','index.php?act=cms_article');
        }
        $model_article_class = Model('cms_article_class');
        $article_class_list = $model_article_class->getList(TRUE, null, 'class_sort asc');
        Tpl::output('article_class_list', $article_class_list);
        Tpl::output('article_detail',$article_detail);
        Tpl::setDirquna('cms');
        Tpl::showpage('cms_article.add');
    }

    /**
     * cms文章列表
     **/
    public function cms_article_listOp()
    {
        $this->doAction(0, 'list');
    }

    /**
     * 待审核文章列表
     */
    public function cms_article_list_verifyOp()
    {
        $this->doAction(self::ARTICLE_STATE_VERIFY, 'list_verify');
    }

    /**
     * 已发布文章列表
     */
    public function cms_article_list_publishedOp()
    {
        $this->doAction(self::ARTICLE_STATE_PUBLISHED, 'list_published');
    }

    protected function doAction($state, $menuKey)
    {
        $this->show_menu($menuKey);
        Tpl::output('currentState', $state);

        $states = $this->get_article_state_list();
        Tpl::output('states', $states);

        Tpl::setDirquna('cms');
        Tpl::showpage("cms_article.list");
    }

    public function cms_article_list_xmlOp()
    {
        $condition = array();

        if ($_REQUEST['advanced']) {
            if (strlen($q = trim((string)$_REQUEST['article_title']))) {
                $condition['article_title'] = array('like', '%' . $q . '%');
            }
            if (strlen($q = trim((string)$_REQUEST['article_publisher_name']))) {
                $condition['article_publisher_name'] = $q;
            }

            if (strlen($q = trim((string)$_REQUEST['article_commend_flag']))) {
                $condition['article_commend_flag'] = (int)$q;
            }
            if (strlen($q = trim((string)$_REQUEST['article_commend_image_flag']))) {
                $condition['article_commend_image_flag'] = (int)$q;
            }
            if (strlen($q = trim((string)$_REQUEST['article_comment_flag']))) {
                $condition['article_comment_flag'] = (int)$q;
            }
            if (strlen($q = trim((string)$_REQUEST['article_attitude_flag']))) {
                $condition['article_attitude_flag'] = (int)$q;
            }
            if (strlen($q = trim((string)$_REQUEST['article_state']))) {
                $condition['article_state'] = (int)$q;
            }

        } else {
            if (strlen($q = trim($_REQUEST['query'])) > 0) {
                switch ($_REQUEST['qtype']) {
                    case 'article_title':
                        $condition[$_REQUEST['qtype']] = array('like', '%' . $q . '%');
                        break;
                    case 'article_publisher_name':
                        $condition[$_REQUEST['qtype']] = $q;
                        break;
                }
            }
        }

        if ($_GET['article_state']) {
            $condition['article_state'] = (int)$_GET['article_state'];
        }

        $model_article = Model('cms_article');
        $list = (array)$model_article->getList($condition, $_REQUEST['rp'], 'article_id desc');

        $data = array();
        $data['now_page'] = $model_article->shownowpage();
        $data['total_num'] = $model_article->gettotalnum();

        $states = $this->get_article_state_list();

        foreach ($list as $val) {
            $o = '<a class="btn red" href="javascript:;" data-j="drop"><i class="fa fa-trash-o"></i>删除</a>';

            $o .= '<span class="btn"><em><i class="fa fa-cog"></i>设置<i class="arrow"></i></em><ul>';

            if ($val['article_state'] == self::ARTICLE_STATE_VERIFY) {
                $o .= '<li><a href="javascript:;" data-j="audit">审核文章</a></li>';
            }
            if ($val['article_state'] == self::ARTICLE_STATE_PUBLISHED) {
                $o .= '<li><a href="javascript:;" data-j="callback">收回文章</a></li>';
            }
            /*$o .= '<li><a target="_blank" href="' .
                CMS_SITE_URL .
                '/index.php?act=article&op=article_detail&article_id=' .
                $val['article_id'] .
                '">查看文章</a></li><li><a target="_blank">修改文章</a></li>';*/
            $o .= '<li><a target="_blank" href="' .
                'index.php?act=cms_article&op=cms_article_detail&article_id=' .
                $val['article_id'] .
                '">查看文章</a></li><li><a target="_blank" href="'.'index.php?act=cms_article&op=cms_article_update&article_id='.$val['article_id'].'" >修改文章</a></li>';
            if ($val['article_commend_flag'] == 1) {
                $o .= '<li><a href="javascript:;" data-j="article_commend_flag" data-val="0">取消推荐文章</a></li>';
            } else {
                $o .= '<li><a href="javascript:;" data-j="article_commend_flag" data-val="1">推荐文章</a></li>';
            }
            if ($val['article_commend_image_flag'] == 1) {
                $o .= '<li><a href="javascript:;" data-j="article_commend_image_flag" data-val="0">取消推荐图文</a></li>';
            } else {
                $o .= '<li><a href="javascript:;" data-j="article_commend_image_flag" data-val="1">推荐图文</a></li>';
            }
            if ($val['article_comment_flag'] == 1) {
                $o .= '<li><a href="javascript:;" data-j="article_comment_flag" data-val="0">关闭评论</a></li>';
            } else {
                $o .= '<li><a href="javascript:;" data-j="article_comment_flag" data-val="1">开启评论</a></li>';
            }
            if ($val['article_attitude_flag'] == 1) {
                $o .= '<li><a href="javascript:;" data-j="article_attitude_flag" data-val="0">关闭心情</a></li>';
            } else {
                $o .= '<li><a href="javascript:;" data-j="article_attitude_flag" data-val="1">开启心情</a></li>';
            }

            $o .= '</ul></span>';

            $i = array();
            $i['operation'] = $o;

            $i['article_sort'] = '<span class="editable" title="可编辑" style="width:50px;" data-live-inline-edit="article_sort">' .
                $val['article_sort'] . '</span>';

            $i['article_title'] = $val['article_title'];

            $img = getCMSArticleImageUrl($val['article_attachment_path'], $val['article_image']);
            $i['img'] = <<<EOB
<a href="javascript:;" class="pic-thumb-tip" onMouseOut="toolTip()" onMouseOver="toolTip('<img src=\'{$img}\'>')">
<i class='fa fa-picture-o'></i></a>
EOB;

            $i['article_publisher_name'] = $val['article_publisher_name'];

            $i['article_click'] = '<span class="editable" title="可编辑" style="width:50px;" data-live-inline-edit="article_click">' .
                $val['article_click'] . '</span>';

            $i['article_commend_flag'] = $val['article_commend_flag'] == 1
                ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>'
                : '<span class="no"><i class="fa fa-ban"></i>否</span>';

            $i['article_commend_image_flag'] = $val['article_commend_image_flag'] == 1
                ? '<span class="yes"><i class="fa fa-check-circle"></i>是</span>'
                : '<span class="no"><i class="fa fa-ban"></i>否</span>';

            $i['article_comment_flag'] = $val['article_comment_flag'] == 1
                ? '<span class="yes"><i class="fa fa-check-circle"></i>开启</span>'
                : '<span class="no"><i class="fa fa-ban"></i>关闭</span>';

            $i['article_attitude_flag'] = $val['article_attitude_flag'] == 1
                ? '<span class="yes"><i class="fa fa-check-circle"></i>开启</span>'
                : '<span class="no"><i class="fa fa-ban"></i>关闭</span>';

            $i['article_state'] = $states[$val['article_state']]['text'];

            $data['list'][$val['article_id']] = $i;
        }

        echo Tpl::flexigridXML($data);
        exit;
    }

    /**
     * cms文章审核
     */
    public function cms_article_verifyOp()
    {
        if (intval($_REQUEST['verify_state']) === 1) {
            $this->cms_article_state_modify(self::ARTICLE_STATE_PUBLISHED);
        } else {
            $this->cms_article_state_modify(self::ARTICLE_STATE_DRAFT, array('article_verify_reason' => $_POST['verify_reason']));
        }
    }

    /**
     * cms文章收回
     */
    public function cms_article_callbackOp()
    {
        $this->cms_article_state_modify(self::ARTICLE_STATE_VERIFY);
    }

    /**
     * 修改文章状态
     */
    private function cms_article_state_modify($new_state, $param = array())
    {
        $article_id = $_REQUEST['article_id'];
        $model_article = Model('cms_article');
        $param['article_state'] = $new_state;
        $model_article->modify($param, array('article_id' => array('in', $article_id)));
        showMessage(Language::get('nc_common_op_succ'), '');
    }

    /**
     * cms文章分类排序修改
     */
    public function update_article_sortOp()
    {
        if (intval($_GET['id']) <= 0) {
            echo json_encode(array('result' => FALSE, 'message' => Language::get('param_error')));
            die;
        }
        $new_sort = intval($_GET['value']);
        if ($new_sort > 255) {
            echo json_encode(array('result' => FALSE, 'message' => Language::get('class_sort_error')));
            die;
        } else {
            $model_class = Model("cms_article");
            $result = $model_class->modify(array('article_sort' => $new_sort), array('article_id' => $_GET['id']));
            if ($result) {
                echo json_encode(array('result' => TRUE, 'message' => 'class_add_success'));
                die;
            } else {
                echo json_encode(array('result' => FALSE, 'message' => Language::get('class_add_fail')));
                die;
            }
        }
    }

    /**
     * cms文章分类排序修改
     */
    public function update_article_clickOp()
    {
        if (intval($_GET['id']) <= 0 || intval($_GET['value']) < 0) {
            echo json_encode(array('result' => FALSE, 'message' => Language::get('param_error')));
            die;
        }
        $model_class = Model("cms_article");
        $result = $model_class->modify(array('article_click' => $_GET['value']), array('article_id' => $_GET['id']));
        if ($result) {
            echo json_encode(array('result' => TRUE, 'message' => ''));
            die;
        } else {
            echo json_encode(array('result' => FALSE, 'message' => Language::get('param_error')));
            die;
        }
    }


    /**
     * cms文章删除
     **/
    public function cms_article_dropOp()
    {
        $article_id = trim($_REQUEST['article_id']);
        $model_article = Model('cms_article');
        $condition = array();
        $condition['article_id'] = array('in', $article_id);
        $result = $model_article->drop($condition);
        if ($result) {
            $this->log(Language::get('cms_log_article_drop') . $article_id, 1);
            showMessage(Language::get('nc_common_del_succ'), '');
        } else {
            $this->log(Language::get('cms_log_article_drop') . $article_id, 0);
            showMessage(Language::get('nc_common_del_fail'), '', '', 'error');
        }
    }

    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        if (intval($_GET['id']) < 1) {
            exit('false');
        }

        switch ($_GET['column']) {
            case 'article_commend_flag':
            case 'article_commend_image_flag':
            case 'article_comment_flag':
            case 'article_attitude_flag':
                break;

            default:
                exit('false');
        }

        $model = Model('cms_article');
        $update[$_GET['column']] = trim($_GET['value']);
        $condition['article_id'] = intval($_GET['id']);
        $model->modify($update, $condition);

        echo 'true';
        die;
    }


    /**
     * 获取文章状态列表
     */
    private function get_article_state_list()
    {
        $array = array();
        $array[self::ARTICLE_STATE_DRAFT] = array('text' => Language::get('cms_text_draft'));
        $array[self::ARTICLE_STATE_VERIFY] = array('text' => Language::get('cms_text_verify'));
        $array[self::ARTICLE_STATE_PUBLISHED] = array('text' => Language::get('cms_text_published'));
        $array[self::ARTICLE_STATE_RECYCLE] = array('text' => Language::get('cms_text_recycle'));
        return $array;
    }

    private function show_menu($menu_key)
    {
        $menu_array = array(
            'list'           => array('menu_type' => 'link', 'menu_name' => Language::get('nc_list'), 'menu_url' => 'index.php?act=cms_article&op=cms_article_list'),
            'list_verify'    => array('menu_type' => 'link', 'menu_name' => Language::get('cms_article_list_verify'), 'menu_url' => 'index.php?act=cms_article&op=cms_article_list_verify'),
            'list_published' => array('menu_type' => 'link', 'menu_name' => Language::get('cms_article_list_published'), 'menu_url' => 'index.php?act=cms_article&op=cms_article_list_published'),
        );
        $menu_array[$menu_key]['menu_type'] = 'text';
        Tpl::output('menu', $menu_array);
    }

}
