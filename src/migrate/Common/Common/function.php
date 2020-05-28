<?php
/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/7/21
 * Time: 14:47
 */

/**
 * @param $name
 * @return \Think\Model
 */
function ecM($name)
{
    return M($name,'sdb_','DB_CONFIG1');
}