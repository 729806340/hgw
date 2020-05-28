<?php

/**
 * Author: Shen.L
 * Email: shen@shenl.com
 * Date: 2016/8/18
 * Time: 18:21
 */
class PageCacheService
{
    private $_open = false;
    private $_key = false;
    private $_uri = false;
    private $_token = '123456';
    private $_expires = 900;
    private $_debug = null;
    private $_page_start_at=0;
    private $_page_finish_at=0;
    private $_redis= array(
        'prefix'=>'page_',
        'port'=>6379,
        'host'=>'192.168.11.124',
        'pconnect'=>false,
        'db_index'=>15,
    );
    private $_allowed=array(
        /** 允许缓存操作列表，格式为ctl/op（动态模式）或者uri（伪静态模式），*为通配符 */
        'index/index',
        'index/josn_class',
        'index/json_area',
        'index/json_area_show',
        'index/getweekofmonth',
        'redpacket/sku',
        'goods/index',
        'item-*',
        'tm-*',
        'tm_list-*',
        'search/*',
        'special-*',
        'special/show',
        'cate-*',
        'channel-*',
        'shop-*',
    );


    /** @var Redis */
    protected $_cache;

    public function __construct()
    {
        if ($this->_open) {
            $this->_cache = new Redis();
            
            $use_global_config = true;//使用data/config/config.php的redis配置
            if ($use_global_config) {
            	$_config = include(dirname(dirname(__FILE__)).'/config/config.ini.php');
            	unset($config);
            	
            	if ('redis' != $_config['cache']['type']) {
            	    $this->_open = false;
            		return false;
            	}
            	$this->_redis = array(
                    'prefix'=>'page_',
                    'port'=> $_config['redis']['master']['port'],
                    'host'=> $_config['redis']['master']['host'],
                    'pconnect'=> $_config['redis']['master']['pconnect'],
                    'db_index'=> $_config['redis']['master']['db_index']?:0,
                    'auth'=> !empty($_config['redis']['master']['auth']) ? : '',
                );
            }
            $connect = $this->_redis['pconnect'] ? 'pconnect' : 'connect';
            $this->enable = $this->_cache->$connect($this->_redis['host'], $this->_redis['port']);
            !empty($this->_redis['auth']) && $this->_cache->auth($this->_redis['auth']);

            //切换到指定的数据库
            $db_index = empty($this->_redis['db_index']) ? 0 : $this->_redis['db_index'];
            $this->_cache->select($db_index);
            //
            if (!$this->_cache) {
                throw new Exception('Cannot fetch cache object!');
            }
        }
        
    }

    public function getKey()
    {
        if($this->_key == false) {
            $keys = $_GET;
            if(isset($keys['debug'])) unset($keys['debug']);
            if(isset($keys['clean'])) unset($keys['clean']);
            ksort($keys);
            $this->_key = $this->_redis['prefix'].$_SERVER['HTTP_HOST'].'_page_cache_'.$this->getUri().http_build_query($keys);
        }
        if(empty($this->_key)) $this->_key = $this->_redis['prefix'].$_SERVER['HTTP_HOST'].'_page_cache_ctl=index';
        return $this->_key;
    }
    public function getUri()
    {
        if($this->_uri == false) {
            $ctl = isset($_GET['act']) ? $_GET['act'] : (isset($_POST['act'])?$_POST['act']:'index');
            $act = isset($_GET['op']) ? $_GET['op'] : (isset($_POST['op'])?$_POST['op']:'index');
            $this->_uri = strtolower($ctl.'/'.$act);
            if($this->_uri =='index/index'){
                $path_info = $_SERVER['REQUEST_URI'];
                $path_info = substr($path_info,strrpos($path_info,'/')+1);
                if(strpos($path_info, '?')!==false) {
                    $path_info = substr($path_info, 0, (int) strpos($path_info, '?'));
                }
                $this->_uri = empty($path_info)||$path_info=='index.php'?$this->_uri:$path_info;
            }
        }
        return $this->_uri;
    }

    public function get()
    {
        if($this->isDebug()){
            $this->_page_start_at = microtime(true);
            echo '页面开始时间'.$this->_page_start_at.'<br />';
            echo 'uri='.$this->getUri().'<br />';
            echo 'key='.$this->getKey().'<br />';
        }
        if(!$this->isCacheable()) return false;
        $this->isCleanable()&&$this->clean();
        ob_start();
        $key = $this->getKey();
        $content = $this->_cache->get($key);
        if($this->isDebug()){
            echo empty($content)?'缓存未命中<br />':'缓存命中<br />';
            $this->_page_finish_at = microtime(true);
            echo '缓存读取完成，用时'.($this->_page_finish_at-$this->_page_start_at).'<br />';
        }
        if(empty($content)) return false;
        return $content;
    }

    public function save()
    {
        $content = ob_get_contents();
        if($this->isCacheable()) $this->_cache->setex($this->getKey(),$this->_expires,$content);
        $this->_page_finish_at = microtime(true);
        if($this->isDebug()){
            echo '页面加载完成，用时'.($this->_page_finish_at-$this->_page_start_at).'<br />';
        }
        ob_flush();
    }

    public function clean()
    {
        $this->_cache->del($this->getKey());
    }

    public function isCleanable()
    {
        return isset($_GET['clean'])&&$_GET['clean']==$this->_token;
        if(isset($_GET['clean'])&&$_GET['clean']==$this->_token) {
            return true;
        }
        return false;
    }

    /** 判断当前页面是否需要缓存 */
    public function isCacheable(){
        if($this->_open !== true) return false;
        $allowed = (array)$this->_allowed;
        $uri = $this->getUri();
        foreach ($allowed as $filter){
            if ($filter === '*' || $filter === $uri || (($pos = strpos($filter, '*')) !== false && !strncmp($uri, $filter, $pos))) {
                return true;
            }
        }
        if($this->isDebug())
        echo '当前页不在缓存范围内！<br />';
        return false;
    }

    public function isDebug()
    {
        if($this->_debug === null){
            if(isset($_GET['debug'])&&$_GET['debug']){
                //unset($_GET['debug']);
                echo 'Debug:<br />';
                $this->_debug = true;
            }else{
                $this->_debug = false;
            }
        }
        return $this->_debug;
    }
    public function setRedis($redis){
        $this->_redis = $redis;
    }
    public function setRedisPrefix($prefix){
        $this->_redis['prefix'] = $prefix;
    }
    public function setAllowed($allow){
        $this->_allowed = $allow;
    }
}
