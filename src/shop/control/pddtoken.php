<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/5
 * Time: 10:07
 */
class pddtokenControl extends BaseHomeControl
{
    public function gettokenOp()
    {
        $code = $_GET['code'];
        $param = $pinduoduo = array();
        if (empty($code)) {
//            $web = "http://mms.pinduoduo.com/open.html?response_type=code&client_id=2e69fabb07364ccca583cb8db044246c&redirect_uri=http://www3.hangowa.com/shop/api/pddtoken/gettoken.php&state=1000";
            $web = "http://mms.pinduoduo.com/open.html?response_type=code&client_id=2e69fabb07364ccca583cb8db044246c&redirect_uri=http://www.hangowa.com/shop/api/pddtoken/gettoken.php&state=1000";
            header('Location:' . $web);
            exit();
        } else {
            $param['code'] = $code;
            $param['client_id'] = '2e69fabb07364ccca583cb8db044246c';
            $param['client_secret'] = 'ca97e4d04edd61c82dd716955d0481bb4d822aae';
            $param['grant_type'] = 'authorization_code';
            $param['redirect_uri'] = 'http://www.hangowa.com/shop/api/pddtoken/gettoken.php';
//            $param['redirect_uri'] = 'http://www.hangowa.com/shop/api/pddtoken/gettoken.php';
            $param['state'] = 1000;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://open-api.pinduoduo.com/oauth/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // post数据
            curl_setopt($ch, CURLOPT_POST, 1);
            // post的变量
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
            $output = curl_exec($ch);
            curl_close($ch);
            $res_json = json_decode($output);
            if (!empty($res_json->error_response)) {
                header("Content-type:text/html;charset=utf-8");
                echo $res_json->error_response->error_msg;
                sleep(10);
                redirect($param['redirect_uri']);
            } else {
                $p['access_token'] = $res_json->access_token;
                $p['refresh_token'] = $res_json->refresh_token;
                $p['expires_in'] = $res_json->expires_in;
                $p['owner_id'] = $res_json->owner_id;
                $p['owner_name'] = $res_json->owner_name;
                $p['update_time'] = time();
                $model = Model('pddtoken');
                $isdone = $model->where(array('owner_id' => $res_json->owner_id))->limit(1)->select();
                //查询数据库，是否已经存在该店铺的授权，如果存在，直接更新；不存在，就插入
                if ($isdone) {
                    $r = $model->where(array('owner_id' => $res_json->owner_id))->update($p);
                } else {
                    $r = $model->insert($p);
                }
                if (!$r) {
                    header("Content-type:text/html;charset=utf-8");
                    echo 'token写入失败';
                    sleep(10);
                    redirect($param['redirect_uri']);
                } else {
                    header("Content-type:text/html;charset=utf-8");
                    echo "token写入成功";
                }
            }

        }

    }
}