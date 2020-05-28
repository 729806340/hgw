<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/11/2
 * Time: 15:17
 */
require_once('Renderer.php');

class RendererHidden extends Renderer
{
    public function input($attribute,$model)
    {
        return '';
    }
    public function output($attribute,$model)
    {
        return '';
    }

}