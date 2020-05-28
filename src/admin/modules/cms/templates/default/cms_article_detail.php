<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>
<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=cms_article" title="返回列表"><i
                    class="fa fa-arrow-circle-o-left"></i></a>

            <div class="subject">
                <h3>文章管理 - 查看文章</h3>
                <h5>查看文章内容</h5>
            </div>
        </div>
    </div>
    <div style="padding:1% 10%;">
        <img src="<?php echo !empty($output['article_detail']['article_image']) ? getCMSArticleImageUrl($output['article_detail']['article_attachment_path'], $output['article_detail']['article_image']):'';?>">
        <?php echo  $output['article_detail']['article_content']?>
    </div>
</div>

