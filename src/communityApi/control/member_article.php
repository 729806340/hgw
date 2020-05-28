<?php


defined('ByShopWWI') or exit('Access Invalid!');

class member_articleControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }


    public function upOp()
    {
        $id = intval($_POST['id']);
        if ($id <= 0) {
            output_error('参数错误');
        }
        $key = 'article_up_' . $id . '_member_' . $this->member_info['member_id'];
        if (rkcache($key) == 1)
            output_error('您已经点过赞！');
        /** @var cms_articleModel $articleModel */
        $articleModel = Model('cms_article');
        $article = $articleModel->getOne(array('article_id' => $id));
        if (empty($article)) output_error('没有找到文章');
        $update = $articleModel->modify(array('article_up' => $article['article_up'] + 1), array('article_id' => $article['article_id']));
        if ($update) {
            wkcache($key, 1);
            output_data($update);
        }
        output_error('操作失败');
    }

    public function comment_addOp()
    {
        $id = intval($_POST['id']);
        $message = trim($_POST['message']);
        if ($id <= 0 || strlen($message) < 3) {
            output_error('参数错误');
        }
        /** @var cms_articleModel $articleModel */
        $articleModel = Model('cms_article');
        $article = $articleModel->getOne(array('article_id' => $id));
        if (empty($article)) output_error('没有找到文章');

        foreach (array('image1', 'image2', 'image3') as $image) {
            if (isset($_POST[$image]) && !empty($_POST[$image])) {
                $images[] = $_POST[$image];
            }
        }

        /** @var cms_commentModel $commentModel */
        $commentModel = Model('cms_comment');
        $res = $commentModel->insert(
            array(
                'comment_object_id' => $id,
                'comment_type'      => '1',
                'comment_message'   => $message,
                'comment_images'    => implode(',', $images),
                'comment_member_id' => $this->member_info['member_id'],
                'comment_time'      => TIMESTAMP,
            )
        );
        /*评论成功 评论数+1*/
        if ($res) {
            $update['article_comment_count'] = array('exp', 'article_comment_count+1');
            $articleModel->modify($update, array('article_id' => $id));
        }
        output_data($res);
    }

    public function comment_replyOp()
    {
        $id = intval($_POST['id']);
        $message = trim($_POST['message']);
        if ($id <= 0 || strlen($message) < 3) {
            output_error('参数错误');
        }
        /** @var cms_commentModel $commentModel */
        $commentModel = Model('cms_comment');
        $comment = $commentModel->getOne(array('comment_id' => $id));
        if (empty($comment)) output_error('没有找到评论');
        $res = $commentModel->insert(
            array(
                'comment_object_id' => $comment['comment_object_id'],
                'comment_type'      => '1',
                'comment_message'   => $message,
                'comment_member_id' => $this->member_info['member_id'],
                'comment_time'      => TIMESTAMP,
            )
        );

        if (!$res) output_error('评论失败');
        $quotes = empty($comment['comment_quote']) ? array() : explode(',', $comment['comment_quote']);
        $quotes[] = $res;
        $update = $commentModel->modify(
            array('comment_quote' => implode(',', $quotes)),
            array('comment_id' => $id)
        );

        if (!$update) output_error('评论失败');
        output_data($res);
    }

    public function comment_upOp()
    {
        $id = intval($_POST['id']);
        if ($id <= 0) {
            output_error('参数错误');
        }

        $key = 'article_comment_up_' . $id . '_member_' . $this->member_info['member_id'];
        if (rkcache($key) == 1)
            output_error('您已经点过赞！');
        /** @var cms_commentModel $commentModel */
        $commentModel = Model('cms_comment');
        $comment = $commentModel->getOne(array('comment_id' => $id));
        if (empty($comment)) output_error('没有找到评论');

        $update = $commentModel->modify(array('comment_up' => $comment['comment_up'] + 1), array('comment_id' => $comment['comment_id']));
        if ($update) {
            wkcache($key, 1);
            output_data($update);
        }
        output_error('操作失败');
    }
}
