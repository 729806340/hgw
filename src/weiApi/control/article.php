<?php
/**
 * 手机端首页控制
 *
 *by wansyb QQ群：111731672
 *你正在使用的是由网店 运 维提供S2.0系统！保障你的网络安全！ 购买授权请前往shopnc
 */


defined('ByShopWWI') or exit('Access Invalid!');

class articleControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function viewOp()
    {
        $id = intval($_POST['id']);
        if ($id <= 0) {
            output_error('参数错误');
        }
        /** @var cms_articleModel $articleModel */
        $articleModel = Model('cms_article');
        $article = $articleModel->getOne(array('article_id' => $id));
        if (empty($article)) output_error('没有找到文章');

        unset($article['article_image_all']);
        $article['article_image'] = getCMSArticleImageUrl($article['article_attachment_path'], $article['article_image']);
        $article['article_content'] = str_replace(array("\r\n", "\r", "\n", "\t"), "", $article['article_content']);
        $article['article_content'] = str_replace('"', "'", $article['article_content']);
        $article['article_content'] = str_replace("src='/", "src='" . C('shop_site_url') . "/", $article['article_content']);

        /** @var memberModel $memberModel */
        /*$memberModel = Model('member');
        $member = $memberModel->getMemberInfo(array('member_id' => $article['article_publisher_id']));*/
        // TODO 检查图像是否正确
        //$article['article_publisher_avatar'] = getMemberAvatarForID($member['member_id']);
        $article['article_publisher_avatar'] = getCMSArticleImageUrl($article['article_attachment_path'], $article['article_publisher_avatar']);

        //计数加1
        $articleModel->modify(array('article_click' => array('exp', 'article_click+1')), array('article_id' => $id));
        output_data($article);
    }

    public function commentOp()
    {

        $id = intval($_POST['id']);
        if ($id <= 0) {
            output_error('参数错误');
        }

        /** @var cms_commentModel $commentModel */
        $commentModel = Model('cms_comment');

        //查询子集
        $where['comment_object_id'] = $id;
        $where['_string'] = "comment_quote IS NOT NULL AND comment_quote !=''";//不为空
        $list = $commentModel->where($where)->field('comment_quote')->select();
        $subset = '';
        if (!empty($list)) {
            foreach ($list as $row) {
                $subset .= $row['comment_quote'] . ',';
            }
        }
        $subset = array_filter(explode(',', $subset));
        //查询根节点
        $filter['comment_object_id'] = $id;
        $quoteArray = array();
        if (count($subset) > 0) {
            $subset = implode(',', $subset);
            $filter['comment_id'] = array('not in', $subset);
            $quoteList = $commentModel->getListWithUserInfo(array('comment_id' => array('in', $subset)));
            $quoteArray = array_under_reset($quoteList, 'comment_id');
        }

        $comments = $commentModel->getListWithUserInfo($filter, $this->page, 'comment_id desc');
        if (empty($comments)) output_error('没有找到评论');

        $res = array();
        foreach ($comments as $comment) {
            $res[] = $this->parseItem($comment, $quoteArray);
        }

        output_data(array('items' => $res, 'total' => $commentModel->gettotalnum(), 'totalPage' => $commentModel->gettotalpage()));
    }

    private function parseItem($comment, $quoteArray)
    {
        $quotes = $this->getSubset($comment, $quoteArray);
        $item = array(
            'comment_id'        => $comment['comment_id'],
            'comment_type'      => $comment['comment_type'],
            'comment_object_id' => $comment['comment_object_id'],
            'comment_message'   => $comment['comment_message'],
            'comment_member_id' => $comment['comment_member_id'],
            'comment_time'      => $comment['comment_time'],
            'comment_images'    => empty($comment['comment_images']) ? array() : explode(',', $comment['comment_images']),
            'comment_quote'     => count($quotes),
            'comment_up'        => $comment['comment_up'],
            'member_name'       => $comment['member_name'],
            'member_avatar'     => $comment['member_avatar'],
            'comment_quotes'    => $quotes,
        );
        return $item;
    }

    private function getSubset($comment, $quoteArray)
    {
        $subset = array();
        $quotes = array_filter(explode(',', $comment['comment_quote']));
        foreach ($quotes as $id) {
            if (isset($quoteArray[$id])) $subset[] = $this->parseItem($quoteArray[$id], $quoteArray);
        }
        return $subset;
    }

    public function comment_viewOp()
    {
        $id = intval($_POST['id']);
        if ($id <= 0) {
            output_error('参数错误');
        }
        /** @var cms_commentModel $commentModel */
        $commentModel = Model('cms_comment');
        $comments = $commentModel->getListWithUserInfo(array('comment_id' => $id), 10);
        if (empty($comments)) output_error('没有找到评论');

        $comment_quote_id = '';
        $quoteList = array();
        if (!empty($comments)) {
            foreach ($comments as $value) {
                if (!empty($value['comment_quote'])) {
                    $comment_quote_id .= $value['comment_quote'] . ',';
                }
            }
        }
        if (!empty($comment_quote_id)) {
            $quoteList = $commentModel->getListWithUserInfo(array('comment_id' => array('in', $comment_quote_id)));
        }
        $res = array();
        $quoteArray = array();
        foreach ($quoteList as $comment) {
            $quoteArray[$comment['comment_id']] = array(
                'comment_id'        => $comment['comment_id'],
                'comment_type'      => $comment['comment_type'],
                'comment_object_id' => $comment['comment_object_id'],
                'comment_message'   => $comment['comment_message'],
                'comment_member_id' => $comment['comment_member_id'],
                'comment_time'      => $comment['comment_time'],
                'comment_quote'     => 0,
                'comment_up'        => $comment['comment_up'],
                'member_name'       => $comment['member_name'],
                'member_avatar'     => $comment['member_avatar'],
            );
        }
        $item = array();
        foreach ($comments as $comment) {
            $quotes = explode(',', $comment['comment_quote']);
            $item = array(
                'comment_id'        => $comment['comment_id'],
                'comment_type'      => $comment['comment_type'],
                'comment_object_id' => $comment['comment_object_id'],
                'comment_message'   => $comment['comment_message'],
                'comment_member_id' => $comment['comment_member_id'],
                'comment_time'      => $comment['comment_time'],
                'comment_images'    => empty($comment['comment_images']) ? array() : explode(',', $comment['comment_images']),
                'comment_quote'     => count($quotes),
                'comment_up'        => $comment['comment_up'],
                'member_name'       => $comment['member_name'],
                'member_avatar'     => $comment['member_avatar'],
            );
            $item['comment_quotes'] = array();
            foreach ($quotes as $quote) {
                if (isset($quoteArray[$quote])) $item['comment_quotes'][] = $quoteArray[$quote];
            }
            break;
            //$res[] = $item;
        }
        if (empty($item)) output_error('没有找到评论');
        output_data($item);
    }

}
