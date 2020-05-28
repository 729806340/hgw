<?php defined('ByShopWWI') or exit('Access Invalid!'); ?>

<div class="page">
    <div class="fixed-bar">
        <div class="item-title"><a class="back" href="index.php?act=sap" title="返回列表"><i
                    class="fa fa-arrow-circle-o-left"></i></a>

            <div class="subject">
                <h3>日志详情</h3>
                <h5>&nbsp;</h5>
            </div>
        </div>
    </div>

    <div class="ncap-form-default">
        <dl class="row">
            <dt class="tit">
                <label>ID</label>
            </dt>
            <dd class="opt">
                <input type="text" class="input-txt" value="<?php echo $output['log_info']['log_id']; ?>" readonly />
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label>交易码</label>
            </dt>
            <dd class="opt">
                <input type="text" class="input-txt" value="<?php echo $output['log_info']['code']; ?>" readonly />
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label>类型</label>
            </dt>
            <dd class="opt">
                <input type="text" class="input-txt" value="<?php echo $output['log_info']['method']; ?>" readonly />
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label>数据</label>
            </dt>
            <dd class="opt">
                <textarea style="width: 100%; height: 250px;"><?php echo $output['log_info']['data']; ?></textarea>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label>结果</label>
            </dt>
            <dd class="opt">
                <textarea style="width: 100%; height: 100px;"><?php echo $output['log_info']['rel']; ?></textarea>
            </dd>
        </dl>
        <dl class="row">
            <dt class="tit">
                <label>错误信息</label>
            </dt>
            <dd class="opt">
                <textarea style="width: 100%; height: 100px;"><?php echo $output['log_info']['error']; ?></textarea>
            </dd>
        </dl>
    </div>

</div>
