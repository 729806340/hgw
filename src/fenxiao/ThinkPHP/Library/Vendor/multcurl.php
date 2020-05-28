<?php
/**
 * @package   /
 * @author    guiyj <guiyj007@gmail.com>
 * @copyright  2013 - 2014 Candou Inc. All Rights Reserved.
 * 
 * @history 
 *  2014-12-19 Created by guiyj
 *      添加内容：完成基本框架代码编写。
 *      
 */

class multCurl {

    public $_response = array();

    public function start( $allUrls = array() ) {

        $multApiUrls = array();
        $n = count($allUrls) > 10 ? 10 : count($allUrls); //调用服务端接口的并发请求个数

        //$times = count($allUrls) / $n ; //需要请求的次数
        //$times = ceil($times) ;
        $i = 1 ;

        foreach ($allUrls as $_url) {
            $multApiUrls[] = $_url;
            if ( count($multApiUrls) >= $n || $i == count($allUrls)  ) {
                // 根据进程数量，批量远程获取内容
                $rs = self::sendMultiRequest($multApiUrls);
                $multApiUrls = array();
                //sleep(2);
                foreach($rs as $resps) {
                    $this -> _response[] = $resps ;
                }
                sleep(2);
            }
            $i++ ;
        }

        return $this -> _response;
    }

    

    /**
     * 从被请求的多个应用信息，来判断哪些应用是最近更新的应用
     * 通过curl进行多进程并发请求
     *
     * @param unknown $apps
     *            - 应用信息数组，每个应用必须包含键名：appid, release_date
     * @param number $delay
     * @param number $timeout
     * @return multitype:multitype:unknown
     */
    public static function sendMultiRequest($urls, $delay = 3, $timeout = 20) {
        $queue = curl_multi_init();
        $map = array();

        foreach ($urls as $url) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            curl_setopt($ch, CURLOPT_VERBOSE, 1);

            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.122 Safari/537.36');
            curl_multi_add_handle($queue, $ch);
            $map[(string) $ch] = $url;
        }

        $responses = array();
        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM);

            if ($code != CURLM_OK) {
                break;
            }

            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($queue)) {

                // get the info and content returned on the request
                $info = curl_getinfo($done['handle']);
                $error = curl_error($done['handle']);
                $results = '';
                if (200 == $info['http_code']) {
                    $data = curl_multi_getcontent($done['handle']);
                    //$results = self::callback($data, $delay);
                }

                // $responses[$map[(string)$done['handle']]] = compact('info', 'error');
                $responses[] = $data;

                // remove the curl handle that just completed
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }
        } while ($active);

        curl_multi_close($queue);
        return $responses;
    }

}
?>
