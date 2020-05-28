<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 15:17
 */
require_once('Renderer.php');

class RendererSelect extends Renderer
{
    public function input($attribute, $model)
    {
        $value = $this->getValue($attribute, $model);
        if(isset($attribute['mod'])&&$attribute['mod'] = 'control') $value = '';

        $options = '';
        if (isset($attribute['items']) && is_array($attribute['items']))
            foreach ($attribute['items'] as $k => $v) {
                $options .= '<option ' . ($k == $value ? 'selected="selected"' : '') . ' value="'.$k.'">'.$v.'</option>';
            }
        return <<<HTML
        
<dl class="row">
<dt class="tit">
    <label for="{$attribute['name']}"><em>*</em>{$attribute['label']}：</label>
</dt>
<dd class="opt">
            <select nc_type="select" data-type="{$attribute['name']}" id="{$attribute['name']}">
            $options
            </select>
            <span class="err"></span>
                <p class="notic">{$attribute['notice']}</p>
          </dd>
</dl>
HTML;
    }

    public function output($attribute, $model)
    {
        $value = $this->getValue($attribute, $model);
        if ($value === '') return '';
        $oldValue = $this->getOldValue($attribute, $model);
        $oldValue = $oldValue === '' ? '空' : $attribute['items'][$oldValue];
        return <<<HTML
      <dl class="row">
        <dt class="tit">{$attribute['label']}</dt>
        <dd class="opt">
          修改为：<strong style="color: red;">{$attribute['items'][$value]}</strong>；
          原值：<strong style="color: red;">$oldValue</strong>
        </dd>
      </dl>

HTML;
    }

}