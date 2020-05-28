<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 15:17
 */
require_once('Renderer.php');

class RendererText extends Renderer
{
    public function input($attribute,$model)
    {
        $value = $this->getValue($attribute,$model);
        //if($value==='') return '';
        return <<<HTML
        
<dl class="row">
<dt class="tit">
    <label for="{$attribute['name']}"><em>*</em>{$attribute['label']}：</label>
</dt>
<dd class="opt">
    <input type="text" value="$value" nc_type="text" data-type="{$attribute['name']}" class="input-txt" id="{$attribute['name']}" /> <span class="err"></span>
    <p class="notic">{$attribute['notice']}</p>
</dd>
</dl>
HTML;
    }
    public function output($attribute,$model)
    {
        $value = $this->getValue($attribute,$model);
        if($value==='') return '';
        $oldValue = $this->getOldValue($attribute,$model);
        $oldValue = $oldValue===''?'空':$oldValue;
        return <<<HTML
      <dl class="row">
        <dt class="tit">{$attribute['label']}</dt>
        <dd class="opt">
          修改为：<strong style="color: red;">$value</strong>；
          原值：<strong style="color: red;">$oldValue</strong>
        </dd>
      </dl>

HTML;
    }

}