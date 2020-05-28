<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 10:16
 * File name:FenxiaoApiTest.php
 */
require_once('api_test_config/Common.php');

class GetApiConfService
{
    private $channel;
    private $api;
    private $channel_obj;

    public function init($channel, $api)
    {
        $this->api = $api;
        $this->channel_obj = $this->getIntance($channel);
    }

    public function getFormConfig()
    {
        return $this->channel_obj->getConf();

    }

    public function dealWithParam($param)
    {
        return $this->channel_obj->dealWithParam($param);

    }

    public function getIntance($channel)
    {
        $c = trim($channel);
        if ($channel !== null && is_string($c)) {
            $className = $c . 'Conf';
            $fileName =dirname(__FILE__)."/api_test_config/" . $className . '.php';
            require_once($fileName);
            if (class_exists($className)) return new $className();
        }
        throw new Exception("找不到对应的获取配置信息:");
    }

    public function getChannel()
    {
        return array(
            'beibeiwang' => '贝贝网',
            'chuchujie' => '楚楚街',
            'chuchutong' => '楚楚通',
            'fanli' => '返利',
            'gegejia' => '格格家-宜昌',
            'gegejia1' => '格格家-恩施',
            'grsc' => '果然商城',
            'hangohongmao' => '汉购红锚',
            'hangoweimeng' => '汉购微盟',
            'huiguo' => '会过',
            'jingdongfx' => '京东',
            'juanpi' => '卷皮',
            'mbyd1' => '脉宝云店1',
            'mbyd2' => '脉宝云店2',
            'mbyd3' => '脉宝云店3',
            'mengdian' => '萌店',
            'pinduoduo' => '拼多多',
            'renrendian' => '人人店',
            'renrenyoupin' => '人人优品',
            'sgsx' => '拼多多水果生鲜店',
            'youzan' => '有赞',
            'suningnonggu' => '苏宁易购农谷鲜',
            'ylmg' => '云联美购',
            'huiguogr'=>'果然旗舰店'
        );
    }
}