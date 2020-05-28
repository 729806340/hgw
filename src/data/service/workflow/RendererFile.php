<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 15:25
 */
require_once('Renderer.php');

class RendererFile extends Renderer
{

    public function input($attribute, $model)
    {
        $value = $this->getValue($attribute, $model);
        //如果附件有值显示
        $img = $value?"<img src=\"{$value}\">":'';
        return <<<HTML

<dl class="row">
    <dt class="tit">
        <label for="{$attribute['name']}">{$attribute['label']}：</label>
    </dt>
    <dd class="opt">
        <div id="show-{$attribute['name']}-image" class="upload-image">{$img}</div>
        <div class="input-file-show">
        <a href="javascript:void(0);">
        <span>
            <input type="hidden" id="{$attribute['name']}" data-type="{$attribute['name']}" nc_type="text" value="{$value}">
            <input type="file" hidefocus="true" 
            data-type="{$attribute['name']}" 
            size="1" class="input-file" data-upload="{$attribute['upaction']}" name="sign" nc_type="upload_sign" />
        </span>
        <p><i class="icon-upload-alt">上传文件</i></p>
        </a>
    </div>
                        <p class="notic">{$attribute['notice']}</p>
    </dd>
</dl>
HTML;
    }

    public function output($attribute, $model)
    {

        $value = $this->getValue($attribute,$model);
        if($value==='') return '';
        $oldValue = $this->getOldValue($attribute,$model);
        $oldValue = $oldValue===''?'空':"<img src='$oldValue' />";
        return <<<HTML
      <dl class="row">
        <dt class="tit">{$attribute['label']}</dt>
        <dd class="opt">
          修改为：<img src="$value" />；
          原值：$oldValue
        </dd>
      </dl>

HTML;
        // TODO: Implement view() method.
    }
}