<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/6
 * Time: 16:18
 */
defined('ByShopWWI') or exit('Access Invalid!');
class pddtokenModel extends Model
{

    public function __construct()
    {
        parent::__construct('pddtoken');
    }
}