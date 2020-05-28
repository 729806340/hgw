<?php
require_once(BASE_PATH.DS.'api'.DS.'qq'.DS.'comm'.DS."config.php");


function qq_login($appid, $scope, $callback)
{
    $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
    $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" 
        . $appid . "&redirect_uri=" . urlencode($callback)
        . "&state=" . $_SESSION['state']
        . "&scope=".$scope;
    header("Location:$login_url");
}

//用户点击qq登录按钮调用此函数
qq_login($_SESSION["appid"], $_SESSION["scope"], "http://www.hangowa.com/member/api-toqq-g.html");
?>
