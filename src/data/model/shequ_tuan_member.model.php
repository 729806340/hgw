<?php
defined('ByShopWWI') or exit('Access Invalid!');
class shequ_tuan_memberModel extends Model
{
    /**
     * 发送手机验证码
     * @param $phone
     * @param $type
     * @return array
     * @throws Exception
     */
    public function sendCaptcha($phone,$type=self::TYPE_REGISTER){

        // 发送手机验证码
        $sms = new Sms();
        $code = rand(100000,999999);
        $content = "您的验证码为：{$code},有效期10分钟";
//        S('phone-captcha-'.$phone,$code);
        $res = $sms->send($phone,$content);
        if (!$res){
            throw new Exception("发送失败");
        }
        return array();
    }

}