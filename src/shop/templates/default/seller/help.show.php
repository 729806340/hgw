<?php defined('ByShopWWI') or exit('Access Invalid!');?>
<link href="<?php echo MEMBER_TEMPLATES_URL;?>/css/layout.css" rel="stylesheet" type="text/css">
<div class="nch-container wrapper" style="width: 980px">
        <div class="nch-article-con">
            <h1><?php echo $output['article']['article_title'];?></h1>
            <h2><?php echo date('Y-m-d H:i',$output['article']['article_time']);?></h2>
            <div class="default">
                <p><?php echo $output['article']['article_content'];?></p>
            </div>
            <div class="more_article">
                <span class="fl">
                    <?php if(!empty($output['pre_article']) and is_array($output['pre_article'])){?>
                        上一篇：<a <?php if($output['pre_article']['article_url']!=''){?>target="_blank"<?php }?> href="<?php if($output['pre_article']['article_url']!='')echo $output['pre_article']['article_url'];else echo urlShop('store_help', 'show', array('article_id'=>$output['pre_article']['article_id']));?>"><?php echo $output['pre_article']['article_title'];?></a>
                    <?php }else{?>
                        没有符合条件的文章
                    <?php }?>
                </span>
                <span class="fr">
                    <?php if(!empty($output['next_article']) and is_array($output['next_article'])){?>
                        下一篇：<a <?php if($output['next_article']['article_url']!=''){?>target="_blank"<?php }?> href="<?php if($output['next_article']['article_url']!='')echo $output['next_article']['article_url'];else echo urlShop('store_help', 'show', array('article_id'=>$output['next_article']['article_id']));?>"><?php echo $output['next_article']['article_title'];?></a>
                    <?php }else{?>
                        没有符合条件的文章
                    <?php }?>
                </span>
            </div>
        </div>
</div>
