<?php
/**
 * Created by CharlesChen
 * Date: 2018/1/25
 * Time: 10:44
 * File name:fenxiao_api_test.logic.php
 */
defined('ByShopWWI') or exit('Access Invalid!');

class fenxiao_api_testLogic
{
    private $service_obj;
    public function __construct()
    {
        $this->service_obj=Service("GetApiConf");
    }

    public function getFormConfig($channel,$api){
        $this->service_obj->init($channel,$api);
        $config=$this->service_obj->getFormConfig();
        return $config[$api];
    }
    public function getChannel(){
        return $this->service_obj->getChannel();
    }
    public function getResult($param){
        $channel=$param['channel'];
        $api=$param['api'];
        $this->service_obj->init($channel,$api);
        $params=$this->service_obj->dealWithParam($param);
        $request=$this->sendRequest($params);
        return $request;
    }
    public function sendRequest($param){
        $url=$param['request']['url'];
        if($param['request']['para_is_json']){
            $request=json_encode($param['param']);
        }else{
            $request=$param['param'];
        }
        return $param['request']['curl_type']?$this->http_get($url,$request):$this->http_post($url,$request);
    }
    protected function http_get($url, $request = '')
    {
        if(is_array($request)&&!empty($request['param'])){
            $url.='?'.$request['param'];
        }else{
            $url .= '?' . http_build_query($request);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(is_array($request)&&!empty($request['header'])){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$request['header']);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    protected function http_post($url, $request = '')
    {
        $data=$request;
        if(is_array($request)&&!empty($request['param'])){
            $data=$request['param'];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(is_string($data)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data))
            );
        }
        if(is_array($request)&&!empty($request['header'])){
            curl_setopt($ch,CURLOPT_HTTPHEADER,$request['header']);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

}