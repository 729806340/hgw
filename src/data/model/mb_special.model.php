<?php
/**
 * 手机专题模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class mb_specialModel extends Model
{

    //专题项目不可用状态
    const SPECIAL_ITEM_UNUSABLE = 0;
    //专题项目可用状态
    const SPECIAL_ITEM_USABLE = 1;
    //首页特殊专题编号
    const INDEX_SPECIAL_ID = 0;
    //App首页特殊专题编号
    const APP_INDEX_SPECIAL_ID = 900000001;
    const APP_CATEGORY_SPECIAL_ID = 900000002;
    const APP_SHIHUA_SPECIAL_ID = 900000003;
    const APP_SHILV_SPECIAL_ID = 900000004;
    const APP_FAXIAN_SPECIAL_ID = 900000005;
    const APP_ZHIDEMAI1_SPECIAL_ID = 900000006;
    const APP_ZHIDEMAI2_SPECIAL_ID = 900000007;
    const APP_ZHIDEMAI3_SPECIAL_ID = 900000008;
    const APP_ZHIDEMAI4_SPECIAL_ID = 900000009;

    public function __construct()
    {
        parent::__construct('mb_special');
    }

    /**
     * 读取专题列表
     * @param array $condition
     *
     */
    public function getMbSpecialList($condition, $page = '', $order = 'special_id desc', $field = '*')
    {
        $list = $this->table('mb_special')->field($field)->where($condition)->page($page)->order($order)->select();
        return $list;
    }

    /*
     * 增加专题
     * @param array $param
     * @return bool
     *
     */
    public function addMbSpecial($param)
    {
        return $this->table('mb_special')->insert($param);
    }

    /*
     * 更新专题
     * @param array $update
     * @param array $condition
     * @return bool
     *
     */
    public function editMbSpecial($update, $special_id)
    {
        $special_id = intval($special_id);
        if ($special_id <= 0) {
            return false;
        }

        $condition = array();
        $condition['special_id'] = $special_id;
        $result = $this->table('mb_special')->where($condition)->update($update);
        if ($result) {
            //删除缓存
            $this->_delMbSpecialCache($special_id);
            return $special_id;
        } else {
            return false;
        }
    }

    /*
     * 删除专题
     * @param int $special_id
     * @return bool
     *
     */
    public function delMbSpecialByID($special_id)
    {
        $special_id = intval($special_id);
        if ($special_id <= 0) {
            return false;
        }

        $condition = array();
        $condition['special_id'] = $special_id;

        $this->delMbSpecialItem($condition, $special_id);

        return $this->table('mb_special')->where($condition)->delete();
    }

    /**
     * 专题项目列表（用于后台编辑显示所有项目）
     * @param int $special_id
     *
     */
    public function getMbSpecialItemListByID($special_id)
    {
        $condition = array();
        $condition['special_id'] = $special_id;

        return $this->_getMbSpecialItemList($condition);
    }

    public function getMbSpecialdesc($special_id)
    {
        $condition = array();
        $condition['special_id'] = $special_id;
        $list = $this->table('mb_special')->where($condition)->find();
        $desc = $list['special_desc'];
        return $desc;
    }

    /**
     * 获取专题详细
     * @param $special_id
     * @return mixed
     */
    public function getMbSpecialInfo($special_id)
    {
        $condition = array();
        $condition['special_id'] = $special_id;
        return $this->table('mb_special')->where($condition)->find();
    }

    /**
     * 专题可用项目列表（用于前台显示仅显示可用项目）
     * @param int $special_id
     *
     */
    public function getMbSpecialItemUsableListByID($special_id)
    {
        $prefix = 'mb_special';

        $item_list = rcache($special_id, $prefix);
        //缓存有效
        if (!empty($item_list)) {
            return unserialize($item_list['special']);
        }

        //缓存无效查库并缓存
        $condition = array();
        $condition['special_id'] = $special_id;
        $condition['item_usable'] = self::SPECIAL_ITEM_USABLE;
        $item_list = $this->_getMbSpecialItemList($condition);
        if (!empty($item_list)) {
            $new_item_list = array();
            foreach ($item_list as $value) {
                //处理图片
                $item_data = $this->_formatMbSpecialData($value['item_data'], $value['item_type']);
                $new_item_list[] = array($value['item_type'] => $item_data);
            }
            $item_list = $new_item_list;
        }
        $cache = array('special' => serialize($item_list));
        wcache($special_id, $cache, $prefix);
        return $item_list;
    }

    /**
     * 首页专题
     */
    public function getMbSpecialIndex()
    {
        return $this->getMbSpecialItemUsableListByID(self::INDEX_SPECIAL_ID);
    }

    public function getAppSpecialIndex($special_id = self::APP_INDEX_SPECIAL_ID)
    {
        return $this->getMbSpecialItemUsableListByID($special_id);
    }

    /**
     * 处理专题数据，拼接图片URL
     */
    private function _formatMbSpecialData($item_data, $item_type)
    {
        switch ($item_type) {
            case 'home1':
            case 'layer':
                $item_data['image'] = getMbSpecialImageUrl($item_data['image']);
                break;
            case 'home2':
            case 'home4':
                $item_data['square_image'] = getMbSpecialImageUrl($item_data['square_image']);
                $item_data['rectangle1_image'] = getMbSpecialImageUrl($item_data['rectangle1_image']);
                $item_data['rectangle2_image'] = getMbSpecialImageUrl($item_data['rectangle2_image']);
                break;
            case 'home5':
                $item_data['square_image'] = getMbSpecialImageUrl($item_data['square_image']);
                $item_data['rectangle1_image'] = getMbSpecialImageUrl($item_data['rectangle1_image']);
                $item_data['rectangle2_image'] = getMbSpecialImageUrl($item_data['rectangle2_image']);
                $item_data['rectangle3_image'] = getMbSpecialImageUrl($item_data['rectangle3_image']);
                break;
            case 'home6':
                $new_item_data = array(
                    'image' =>  getMbSpecialImageUrl($item_data['image']),
                    'xian_shi' => array(),
                );
                if ($item_data['type'] == 'goods' && $item_data['data']) {
                    $new_item_data['info']['now_time'] = TIMESTAMP;
                    /** @var p_xianshi_goodsModel $p_goods_xian_model */
                    $p_goods_xian_model = Model('p_xianshi_goods');
                    $xian_shi_data = $p_goods_xian_model->getXianshiGoodsInfo(array(
                        'goods_id' => $item_data['data'],
                        'start_time' => array('lt', TIMESTAMP),
                        'end_time' => array('gt', TIMESTAMP),
                    ));
                    $new_item_data['xian_shi'] = $xian_shi_data ? $xian_shi_data : array();
                }
                $item_data['item'] = $new_item_data;
                break;
            case 'goods':
            case 'goods1':
            case 'goods2':
            case 'goods3':
                $new_item = array();
                foreach ((array)$item_data['item'] as $value) {
                    $value['goods_image'] = cthumb($value['goods_image']);
                    $value['goods_image_url'] = $value['goods_image'];
                    //$value['goods_price'] = $value['goods_promotion_price'];
                    $new_item[] = $value;
                }
                $item_data['item'] = $new_item;
                break;
            case 'miaosha':

                $new_item_data = array(
                    'xian_shi' => array(),
                    'ext_data' => array(),
                    'info'     => array(),
                );

                $miao_sha_data = current($item_data['item']);
                if ($miao_sha_data['type'] == 'miaosha' && $miao_sha_data['data']) {

                    $new_item_data['info'] = Model('p_xianshi')->getXianshiInfo(array('xianshi_id' => $miao_sha_data['data']));
                    if (!empty($new_item_data['info'])) {
                        $new_item_data['info']['now_time'] = time();
                        $xian_shi_data = Model('p_xianshi_goods')->getXianshiGoodsList(array('xianshi_id' => $miao_sha_data['data']));
                        foreach ($xian_shi_data as $k => $m) {
                            $m['goods_image'] = cthumb($m['goods_image'], 240, $m['store_id']);
                            $new_item_data['xian_shi']['list'][$k] = $m;
                        }
                    }
                }

                $item_data['item'] = $new_item_data;
                break;
            case 'miaosha_more':
                $new_item_data = array(
                    'now_time' => TIMESTAMP,
                    'config_ids' => array(),
                    'goods_list' => array(),
                    'current_xianshi_data' => array()
                );

                $config_ids = array();
                foreach ($item_data['item'] as $value) {
                    if ($value['type'] == 'miaosha' && $value['data']) {
                        $config_ids[] = $value['data'];
                    }
                }
                $config_ids = array_unique($config_ids);
                if (empty($config_ids)) {
                    $item_data['item'] = $new_item_data;
                    break;
                }
                $new_item_data['config_ids'] = $config_ids;
                $xian_shi_list = Model('p_xianshi')->getXianshiList(array('config_xianshi_id' => array('in', $config_ids)), null, 'start_time ASC');
                if (empty($xian_shi_list)) {
                    $item_data['item'] = $new_item_data;
                    break;
                }
                $current_config_id = 0;
                foreach ($xian_shi_list as $xian_shi) {
                    if ($xian_shi['start_time'] <= TIMESTAMP && TIMESTAMP <= $xian_shi['end_time']) {
                        $current_config_id = $xian_shi['config_xianshi_id'];
                        $new_item_data['current_xianshi_data'] = $xian_shi;
                        break;
                    }
                }
                if (!$current_config_id) {
                    $last_xianshi = reset($xian_shi_list);
                    $new_item_data['current_xianshi_data'] = $last_xianshi;
                    $current_config_id = $last_xianshi['config_xianshi_id'];
                }
                $xian_shi_ids = Model('p_xianshi')->getXianshiList(array('config_xianshi_id' => $current_config_id), null, 'start_time ASC', 'xianshi_id');
                $xian_shi_ids = array_column($xian_shi_ids, 'xianshi_id');
                $xian_shi_data = Model('p_xianshi_goods')->getXianshiGoodsList(array('xianshi_id' => array('in', $xian_shi_ids)), 2);
                foreach ($xian_shi_data as $m) {
                    $m['goods_image'] = cthumb($m['goods_image'], 240, $m['store_id']);
                    $new_item_data['goods_list'][] = $m;
                }
                $item_data['item'] = $new_item_data;
                break;
            default:
                $new_item = array();
                foreach ((array)$item_data['item'] as $key => $value) {
                    $value['image'] = getMbSpecialImageUrl($value['image']);
                    $new_item[] = $value;
                }
                $item_data['item'] = $new_item;
        }
        return $item_data;
    }

    /**
     * 查询专题项目列表
     */
    private function _getMbSpecialItemList($condition, $order = 'item_sort asc')
    {
        $item_list = $this->table('mb_special_item')->where($condition)->order($order)->select();
        foreach ($item_list as $key => $value) {
            $item_list[$key]['item_data'] = $this->_initMbSpecialItemData($value['item_data'], $value['item_type']);
            if ($value['item_usable'] == self::SPECIAL_ITEM_USABLE) {
                $item_list[$key]['usable_class'] = 'usable';
                $item_list[$key]['usable_text'] = '禁用';
            } else {
                $item_list[$key]['usable_class'] = 'unusable';
                $item_list[$key]['usable_text'] = '启用';
            }
        }
        return $item_list;
    }

    /**
     * 检查专题项目是否存在
     * @param array $condition
     *
     */
    public function isMbSpecialItemExist($condition)
    {
        $item_list = $this->table('mb_special_item')->where($condition)->select();
        if ($item_list) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取项目详细信息
     * @param int $item_id
     *
     */
    public function getMbSpecialItemInfoByID($item_id)
    {
        $item_id = intval($item_id);
        if ($item_id <= 0) {
            return false;
        }

        $condition = array();
        $condition['item_id'] = $item_id;
        $item_info = $this->table('mb_special_item')->where($condition)->find();
        $item_info['item_data'] = $this->_initMbSpecialItemData($item_info['item_data'], $item_info['item_type']);

        return $item_info;
    }

    /**
     * 整理项目内容
     *
     */
    private function _initMbSpecialItemData($item_data, $item_type)
    {
        if (!empty($item_data)) {
            $item_data = unserialize($item_data);
            if ($item_type == 'goods' || $item_type == 'goods1' || $item_type == 'goods2' ||  $item_type == 'goods3') {
                $item_data = $this->_initMbSpecialItemGoodsData($item_data, $item_type);
            } else if ($item_type == 'article') {
                $item_data = $this->_initMbSpecialItemArticleData($item_data, $item_type);
            }
        } else {
            $item_data = $this->_initMbSpecialItemNullData($item_type);
        }
        return $item_data;

    }

    /**
     * 处理goods类型内容
     */
    private function _initMbSpecialItemGoodsData($item_data, $item_type)
    {
        $goods_id_string = '';
        if (!empty($item_data['item'])) {
            foreach ($item_data['item'] as $value) {
                $goods_id_string .= $value . ',';
            }
            $goods_id_string = rtrim($goods_id_string, ',');

            //查询商品信息
            $condition['goods_id'] = array('in', $goods_id_string);
            /** @var goodsModel $model_goods */
            $model_goods = Model('goods');
            $goods_list = $model_goods->getGoodsList($condition, 'goods_id,goods_name,store_id,goods_price,goods_promotion_price,goods_image');
            $goods_list = array_under_reset($goods_list, 'goods_id');

            //整理商品数据
            $new_goods_list = array();
            foreach ($item_data['item'] as $value) {
                if (!empty($goods_list[$value])) {
                    $new_goods_list[] = $goods_list[$value];
                }
            }
            $item_data['item'] = $new_goods_list;
        }
        return $item_data;
    }

    private function _initMbSpecialItemArticleData($item_data, $item_type)
    {
        $goods_id_string = '';
        if (!empty($item_data['item'])) {
            foreach ($item_data['item'] as $value) {
                $goods_id_string .= $value . ',';
            }
            $goods_id_string = rtrim($goods_id_string, ',');

            //查询商品信息
            $condition['article_id'] = array('in', $goods_id_string);
            /** @var cms_articleModel $model_article */
            $model_article = Model('cms_article');
            $article_list = $model_article->getList($condition);
            $goods_list = array();
            foreach ($article_list as $v) {
                unset($v['article_image_all']);
                $v['article_image'] = getCMSArticleImageUrl($v['article_attachment_path'], $v['article_image']);
                $v['article_publisher_avatar'] = getCMSArticleImageUrl($v['article_attachment_path'], $v['article_publisher_avatar']);
                $v['article_content'] = str_replace(array("\r\n", "\r", "\n", "\t"), "", $v['article_content']);
                $v['article_content'] = str_replace('"', "'", $v['article_content']);
                $v['article_content'] = str_replace("src='/", "src='" . C('shop_site_url') . "/", $v['article_content']);
                $goods_list[$v['article_id']] = $v;
            }
            $goods_list = array_under_reset($goods_list, 'article_id');

            //整理商品数据
            $new_goods_list = array();
            foreach ($item_data['item'] as $value) {
                if (!empty($goods_list[$value])) {
                    $new_goods_list[] = $goods_list[$value];
                }
            }
            $item_data['item'] = $new_goods_list;
        }
        return $item_data;
    }

    /**
     * 初始化空项目内容
     */
    private function _initMbSpecialItemNullData($item_type)
    {
        $item_data = array();
        switch ($item_type) {
            case 'home1':
                $item_data = array(
                    'title' => '',
                    'image' => '',
                    'type'  => '',
                    'data'  => '',
                );
                break;
            case 'home2':
            case 'home4':
                $item_data = array(
                    'title'            => '',
                    'square_image'     => '',
                    'square_type'      => '',
                    'square_data'      => '',
                    'rectangle1_image' => '',
                    'rectangle1_type'  => '',
                    'rectangle1_data'  => '',
                    'rectangle2_image' => '',
                    'rectangle2_type'  => '',
                    'rectangle2_data'  => '',
                );
                break;
            default:
        }
        return $item_data;
    }

    /*
     * 增加专题项目
     * @param array $param
     * @return array $item_info
     *
     */
    public function addMbSpecialItem($param)
    {
        $param['item_usable'] = self::SPECIAL_ITEM_UNUSABLE;
        $param['item_sort'] = 255;
        $result = $this->table('mb_special_item')->insert($param);
        //删除缓存
        if ($result) {
            //删除缓存
            $this->_delMbSpecialCache($param['special_id']);
            $param['item_id'] = $result;
            return $param;
        } else {
            return false;
        }
    }

    /**
     * 编辑专题项目
     * @param array $update
     * @param int $item_id
     * @param int $special_id
     * @return bool
     *
     */
    public function editMbSpecialItemByID($update, $item_id, $special_id)
    {
        if (isset($update['item_data'])) {
            $update['item_data'] = serialize($update['item_data']);
        }
        $condition = array();
        $condition['item_id'] = $item_id;

        //删除缓存
        $this->_delMbSpecialCache($special_id);

        return $this->table('mb_special_item')->where($condition)->update($update);
    }

    /**
     * 编辑专题项目启用状态
     * @param string usable-启用/unsable-不启用
     * @param int $item_id
     * @param int $special_id
     *
     */
    public function editMbSpecialItemUsableByID($usable, $item_id, $special_id)
    {
        $update = array();
        if ($usable == 'usable') {
            $update['item_usable'] = self::SPECIAL_ITEM_USABLE;
        } else {
            $update['item_usable'] = self::SPECIAL_ITEM_UNUSABLE;
        }
        return $this->editMbSpecialItemByID($update, $item_id, $special_id);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     *
     */
    public function delMbSpecialItem($condition, $special_id)
    {
        //删除缓存
        $this->_delMbSpecialCache($special_id);

        return $this->table('mb_special_item')->where($condition)->delete();
    }

    /**
     * 获取专题URL地址
     * @param int $special_id
     *
     */
    public function getMbSpecialHtmlUrl($special_id)
    {
        return UPLOAD_SITE_URL . DS . ATTACH_MOBILE . DS . 'special_html' . DS . md5('special' . $special_id) . '.html';
    }

    /**
     * 获取专题静态文件路径
     * @param int $special_id
     *
     */
    public function getMbSpecialHtmlPath($special_id)
    {
        return BASE_UPLOAD_PATH . DS . ATTACH_MOBILE . DS . 'special_html' . DS . md5('special' . $special_id) . '.html';
    }

    /**
     * 获取专题模块类型列表
     * @return array
     *
     */
    public function getMbSpecialModuleList()
    {
        $module_list = array();
        $module_list['adv_list'] = array('name' => 'adv_list', 'desc' => '广告条版块');
        $module_list['home1'] = array('name' => 'home1', 'desc' => '模型版块布局A');
        $module_list['layer'] = array('name' => 'layer', 'desc' => '弹层: 小程序专用');
        $module_list['home6'] = array('name' => 'home6', 'desc' => '单个秒杀商品: 小程序专用');
        $module_list['home2'] = array('name' => 'home2', 'desc' => '模型版块布局B');
        $module_list['home3'] = array('name' => 'home3', 'desc' => '模型版块布局C');
        $module_list['home4'] = array('name' => 'home4', 'desc' => '模型版块布局D');
        $module_list['explode2'] = array('name' => 'explode2', 'desc' => '一行2张图片');
        $module_list['explode3'] = array('name' => 'explode3', 'desc' => '一行3张图片');
        $module_list['explode4'] = array('name' => 'explode4', 'desc' => '一行4张图片');
        $module_list['home5'] = array('name' => 'home5', 'desc' => '楼层版块布局');
        $module_list['miaosha'] = array('name' => 'miaosha', 'desc' => '秒杀-一个横排');
        $module_list['miaosha_more'] = array('name' => 'miaosha_more', 'desc' => '秒杀多竖列: 小程序专用');
        $module_list['goods'] = array('name' => 'goods', 'desc' => '商品版块');
        //$module_list['goods1'] = array('name' => 'goods1', 'desc' => '限时商品');
        //$module_list['goods2'] = array('name' => 'goods2', 'desc' => '团购商品');
        $module_list['goods3'] = array('name' => 'goods3', 'desc' => '商品版块_横列: 小程序专用');//goods
        $module_list['icon'] = array('name' => 'icon', 'desc' => '一行5分类: 小程序专用'); //home3
        $module_list['explode2pic'] = array('name' => 'explode2pic', 'desc' => '一行2个 2图2文: 小程序专用'); // explode2 + 一个文字
        $module_list['explode3pic'] = array('name' => 'explode3pic', 'desc' => '一行3个1图2文: 小程序专用'); // explode3 + 一个文字
        $module_list['article'] = array('name' => 'article', 'desc' => '文章板块');

        return $module_list;
    }

    /**
     * 清理缓存
     */
    private function _delMbSpecialCache($special_id)
    {
        //清理缓存
        dcache($special_id, 'mb_special');

        //删除静态文件
        $html_path = $this->getMbSpecialHtmlPath($special_id);
        if (is_file($html_path)) {
            @unlink($html_path);
        }
    }
}
