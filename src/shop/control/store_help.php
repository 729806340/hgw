<?php
/**
 * 商家操作手册
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */



defined('ByShopWWI') or exit('Access Invalid!');
class store_helpControl extends BaseSellerControl{


    public function __construct() {
        parent::__construct() ;
    }

    public function indexOp()
    {
        $this->listOp();
    }

    /*
     * 文章列表
     */
    public function listOp() {
        $article_model  = Model('article');
        $condition  = array();
        $condition['ac_ids']    = 8;
        $condition['article_show']  = '1';
        $page   = new Page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        $article_list   = $article_model->getArticleList($condition,$page);
        Tpl::output('list',$article_list);
        Tpl::output('show_page',$page->show());

        $menu_array = array(
            array('menu_key'=>'list','menu_name'=>'商家操作手册','menu_url'=>'index.php?act=store_help')
        );
        Tpl::output('member_menu',$menu_array);
        Tpl::output('menu_key', 'list');

        Tpl::showpage('help.list');
    }

    public function showOp() {

        Language::read('home_article_index');
        $lang   = Language::getLangContent();
        if(empty($_GET['article_id'])){
            showMessage($lang['para_error'],'','html','error');//'缺少参数:文章编号'
        }
        /**
         * 根据文章编号获取文章信息
         */
        $article_model  = Model('article');
        $article    = $article_model->getOneArticle(intval($_GET['article_id']));
        if(empty($article) || !is_array($article) || $article['article_show']=='0'){
            showMessage($lang['article_show_not_exists'],'','html','error');//'该文章并不存在'
        }
        Tpl::output('article',$article);

        $ac_ids = array();
        if(!empty($child_class_list) && is_array($child_class_list)){
            foreach ($child_class_list as $v){
                $ac_ids[]   = $v['ac_id'];
            }
        }

 
        $condition  = array();
        $condition['ac_ids']    = 8;
        $condition['article_show']  = '1';
        $article_list   = $article_model->getArticleList($condition);
        /**
         * 寻找上一篇与下一篇
         */
        $pre_article    = $next_article = array();
        if(!empty($article_list) && is_array($article_list)){
            $pos    = 0;
            foreach ($article_list as $k=>$v){
                if($v['article_id'] == $article['article_id']){
                    $pos    = $k;
                    break;
                }
            }
            if($pos>0 && is_array($article_list[$pos-1])){
                $pre_article    = $article_list[$pos-1];
            }
            if($pos<count($article_list)-1 and is_array($article_list[$pos+1])){
                $next_article   = $article_list[$pos+1];
            }
        }
        Tpl::output('pre_article',$pre_article);
        Tpl::output('next_article',$next_article);

        Tpl::showpage('help.show');
    }

}
