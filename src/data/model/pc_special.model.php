<?php
/**
 * PC专题模型
 *
 *
 *
 * * @汉购网 (c) 2015-2018 ShopWWI Inc. (http://www.hangowa.com)
 * @license    http://www.shopwwi.c om
 * @link       交流群号：
 * @since      汉购网提供技术支持 授权请购买shopnc授权
 */
defined('ByShopWWI') or exit('Access Invalid!');

class pc_specialModel extends Model {
    
    // 专题项目不可用状态
    const SPECIAL_ITEM_UNUSABLE = 0;
    // 专题项目可用状态
    const SPECIAL_ITEM_USABLE = 1;
    // 首页特殊专题编号
    const INDEX_SPECIAL_ID = 0;

    public function __construct() {
        parent::__construct('pc_special');
    }

    /**
     * 读取专题列表
     *
     * @param array $condition            
     *
     */
    public function getPCSpecialList($condition, $page = '', $order = 'special_id desc', $field = '*') {
        $list = $this->table('pc_special')
            ->field($field)
            ->where($condition)
            ->page($page)
            ->order($order)
            ->select();
        return $list;
    }
    
    /*
     * 增加专题 @param array $param @return bool
     */
    public function addPCSpecial($param) {
        return $this->table('pc_special')->insert($param);
    }
    /*
     * 更新专题 @param array $update @param array $condition @return bool
     */
    public function editPCSpecial($update, $special_id) {
        $special_id = intval($special_id);
        if ($special_id <= 0) {
            return false;
        }
        
        $condition = array();
        $condition['special_id'] = $special_id;
        $result = $this->table('pc_special')
            ->where($condition)
            ->update($update);
        if ($result) {
            // 删除缓存
            $this->_delPCSpecialCache($special_id);
            return $special_id;
        } else {
            return false;
        }
    }
    
    /*
     * 删除专题 @param int $special_id @return bool
     */
    public function delPCSpecialByID($special_id) {
        $special_id = intval($special_id);
        if ($special_id <= 0) {
            return false;
        }
        
        $condition = array();
        $condition['special_id'] = $special_id;
        
        $this->delPCSpecialItem($condition, $special_id);
        
        return $this->table('pc_special')
            ->where($condition)
            ->delete();
    }

    /**
     * 专题项目列表（用于后台编辑显示所有项目）
     *
     * @param int $special_id            
     *
     */
    public function getPCSpecialItemListByID($special_id) {
        $condition = array();
        $condition['special_id'] = $special_id;
        
        return $this->_getPCSpecialItemList($condition);
    }

    public function getPCSpecial($special_id) {
        $condition = array();
        $condition['special_id'] = $special_id;
        $list = $this->table('pc_special')
            ->where($condition)
            ->find();
        return $list;
    }

    public function getPCSpecialItem($item_id) {
        $condition = array();
        $condition['item_id'] = $item_id;
        $list = $this->table('pc_special_item')
            ->where($condition)
            ->find();
        return $list;
    }

    /**
     * 专题可用项目列表（用于前台显示仅显示可用项目）
     *
     * @param int $special_id            
     *
     */
    public function getPCSpecialItemUsableListByID($special_id) {
        $prefix = 'pc_special';
        
        $item_list = rcache($special_id, $prefix);
        // 缓存有效
        if (! empty($item_list)) {
            return unserialize($item_list['special']);
        }
        
        // 缓存无效查库并缓存
        $condition = array();
        $condition['special_id'] = $special_id;
        $condition['item_usable'] = self::SPECIAL_ITEM_USABLE;
        $item_list = $this->_getPCSpecialItemList($condition);
        if (! empty($item_list)) {
            $new_item_list = array();
            foreach ($item_list as $value) {
                // 处理图片
                $item_data = $this->_formatPCSpecialData($value['item_data'], $value['item_type']);
                $new_item_list[] = array(
                    $value['item_type'] => $item_data
                );
            }
            $item_list = $new_item_list;
        }
        $cache = array(
            'special' => serialize($item_list)
        );
        wcache($special_id, $cache, $prefix);
        return $item_list;
    }

    /**
     * 首页专题
     */
    public function getPCSpecialIndex() {
        return $this->getPCSpecialItemUsableListByID(self::INDEX_SPECIAL_ID);
    }

    /**
     * 处理专题数据，拼接图片URL
     */
    private function _formatPCSpecialData($item_data, $item_type) {
        switch ($item_type) {
            case 'home1':
                $item_data['image'] = getMBSpecialImageUrl($item_data['image']);
                break;
            case 'home2':
            case 'home4':
                $item_data['square_image'] = getMBSpecialImageUrl($item_data['square_image']);
                $item_data['rectangle1_image'] = getMBSpecialImageUrl($item_data['rectangle1_image']);
                $item_data['rectangle2_image'] = getMBSpecialImageUrl($item_data['rectangle2_image']);
                break;
            case 'home5':
                $item_data['square_image'] = getMBSpecialImageUrl($item_data['square_image']);
                $item_data['rectangle1_image'] = getMBSpecialImageUrl($item_data['rectangle1_image']);
                $item_data['rectangle2_image'] = getMBSpecialImageUrl($item_data['rectangle2_image']);
                $item_data['rectangle3_image'] = getMBSpecialImageUrl($item_data['rectangle3_image']);
                break;
            case 'goods':
            case 'goods1':
            case 'goods2':
                $new_item = array();
                foreach ((array) $item_data['item'] as $value) {
                    $value['goods_image'] = cthumb($value['goods_image']);
                    $new_item[] = $value;
                }
                $item_data['item'] = $new_item;
                break;
            default:
                $new_item = array();
                foreach ((array) $item_data['item'] as $key => $value) {
                    $value['image'] = getMBSpecialImageUrl($value['image']);
                    $new_item[] = $value;
                }
                $item_data['item'] = $new_item;
        }
        return $item_data;
    }

    /**
     * 查询专题项目列表
     */
    private function _getPCSpecialItemList($condition, $order = 'item_sort asc') {
        $item_list = $this->table('pc_special_item')
            ->where($condition)
            ->order($order)
            ->select();
        foreach ($item_list as $key => $value) {
            $item_list[$key]['item_data'] = $this->_initPCSpecialItemData($value['item_data'], $value['item_type']);
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
     *
     * @param array $condition            
     *
     */
    public function isPCSpecialItemExist($condition) {
        $item_list = $this->table('pc_special_item')
            ->where($condition)
            ->select();
        if ($item_list) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取项目详细信息
     *
     * @param int $item_id            
     *
     */
    public function getPCSpecialItemInfoByID($item_id) {
        $item_id = intval($item_id);
        if ($item_id <= 0) {
            return false;
        }
        
        $condition = array();
        $condition['item_id'] = $item_id;
        $item_info = $this->table('pc_special_item')
            ->where($condition)
            ->find();
        $item_info['item_data'] = $this->_initPCSpecialItemData($item_info['item_data'], $item_info['item_type']);
        
        return $item_info;
    }

    /**
     * 整理项目内容
     */
    private function _initPCSpecialItemData($item_data, $item_type) {
        if (! empty($item_data)) {
            $item_data = unserialize($item_data);
            if (is_array($item_data['item']['goods']) && !empty($item_data['item']['goods'])) {
                $item_data = $this->_initPCSpecialItemGoodsData($item_data, $item_type);
            } else {
                foreach ($item_data['item'] as $key => $value) {
                    if (!empty($value['data']) && !empty($value['image'])) {
                        switch ($value['type']) {
                            case 'url':
                                $item_data['item'][$key]['url'] = $value['data'];
                                break;
                            case 'keyword':
                                $item_data['item'][$key]['url'] = '/?act=search&op=index&keyword=' . $value['data'];
                                break;    
                            case 'special':
                                $item_data['item'][$key]['url'] = urlShop('special','show',array('special_id'=>$value['data']));
                                break; 
                            case 'goods':
                                $item_data['item'][$key]['url'] = urlShop('goods','index',array('goods_id'=>$value['data']));;
                                break;                                        
                            default:
                                $item_data['item'][$key]['url'] = $value['data'];
                                break;
                        }
                    }
                }

            }
        } else {
            $item_data = $this->_initPCSpecialItemNullData($item_type);
        }
        return $item_data;
    }

    /**
     * 处理goods类型内容
     */
    private function _initPCSpecialItemGoodsData($item_data, $item_type) {
        $goods_id_string = '';
        if (! empty($item_data['item']['goods'])) {
            foreach ($item_data['item']['goods'] as $value) {
                $goods_id_string .= $value . ',';
            }
            $goods_id_string = rtrim($goods_id_string, ',');
            // 查询商品信息
            $condition['goods_id'] = array(
                'in',
                $goods_id_string
            );
            $model_goods = Model('goods');
            $goods_list = $model_goods->getGoodsList($condition, 'goods_id,goods_name,goods_jingle,goods_price,goods_promotion_price,goods_marketprice,goods_image,goods_salenum,goods_storage,goods_state');
            $goods_list = array_under_reset($goods_list, 'goods_id');
            
            // 整理商品数据
            $new_goods_list = array();
            foreach ($item_data['item']['goods'] as $value) {
                if (! empty($goods_list[$value])) {
                    $goods_list[$value]['url'] = urlShop('goods','index',array('goods_id'=>$value));
                    $new_goods_list[] = $goods_list[$value];
                }
            }
            $item_data['item']['goods'] = $new_goods_list;
        }

        return $item_data;
    }

    /**
     * 初始化空项目内容
     */
    private function _initPCSpecialItemNullData($item_type) {
        $item_data = array();
        switch ($item_type) {
            case 'home1':
                $item_data = array(
                    'title' => '',
                    'image' => '',
                    'type' => '',
                    'data' => ''
                );
                break;
            case 'home2':
            case 'home4':
                $item_data = array(
                    'title' => '',
                    'square_image' => '',
                    'square_type' => '',
                    'square_data' => '',
                    'rectangle1_image' => '',
                    'rectangle1_type' => '',
                    'rectangle1_data' => '',
                    'rectangle2_image' => '',
                    'rectangle2_type' => '',
                    'rectangle2_data' => ''
                );
                break;
            default:
        }
        return $item_data;
    }
    
    /*
     * 增加专题项目 @param array $param @return array $item_info
     */
    public function addPCSpecialItem($param) {
        $param['item_usable'] = self::SPECIAL_ITEM_UNUSABLE;
        $param['item_sort'] = 255;
        $result = $this->table('pc_special_item')->insert($param);
        // 删除缓存
        if ($result) {
            // 删除缓存
            $this->_delPCSpecialCache($param['special_id']);
            $param['item_id'] = $result;
            return $param;
        } else {
            return false;
        }
    }

    /**
     * 编辑专题项目
     *
     * @param array $update            
     * @param int $item_id            
     * @param int $special_id            
     * @return bool
     *
     */
    public function editPCSpecialItemByID($update, $item_id, $special_id, $item_template='') {
        if (isset($update['item_data'])) {
            $update['item_data'] = serialize($update['item_data']);
        }
        if (!empty($item_template)) {
            $update['item_template'] = strip_tags($item_template);
        }
        $condition = array();
        $condition['item_id'] = $item_id;
        
        // 删除缓存
        $this->_delPCSpecialCache($special_id);
        return $this->table('pc_special_item')
            ->where($condition)
            ->update($update);
    }

    /**
     * 编辑专题项目启用状态
     *
     * @param
     *            string usable-启用/unsable-不启用
     * @param int $item_id            
     * @param int $special_id            
     *
     */
    public function editPCSpecialItemUsableByID($usable, $item_id, $special_id) {
        $update = array();
        if ($usable == 'usable') {
            $update['item_usable'] = self::SPECIAL_ITEM_USABLE;
        } else {
            $update['item_usable'] = self::SPECIAL_ITEM_UNUSABLE;
        }
        return $this->editPCSpecialItemByID($update, $item_id, $special_id);
    }
    
    /*
     * 删除 @param array $condition @return bool
     */
    public function delPCSpecialItem($condition, $special_id) {
        // 删除缓存
        $this->_delPCSpecialCache($special_id);
        
        return $this->table('pc_special_item')
            ->where($condition)
            ->delete();
    }

    /**
     * 获取专题URL地址
     *
     * @param int $special_id            
     *
     */
    public function getPCSpecialHtmlUrl($special_id) {
        return UPLOAD_SITE_URL . DS . ATTACH_MOBILE . DS . 'special_html' . DS . md5('special' . $special_id) . '.html';
    }

    /**
     * 获取专题静态文件路径
     *
     * @param int $special_id            
     *
     */
    public function getPCSpecialHtmlPath($special_id) {
        return BASE_UPLOAD_PATH . DS . ATTACH_MOBILE . DS . 'special_html' . DS . md5('special' . $special_id) . '.html';
    }

    /**
     * 获取专题模块类型列表
     *
     * @return array
     *
     */
    public function getPCSpecialModuleList() {
        $module_list = array(
            'pics' => array(
                'desc' => '广告图',
                'items' => array(
                    'focus_pic' => array(
                        'name' => 'focus_pic',
                        'desc' => '焦点图',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/focus_pic/preview.png',
                    ),
                    'line_1pic' => array(
                        'name' => 'line_1pic',
                        'desc' => '一行1张广告图',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_1pic/preview.png',
                    ),
                    'line_2pic' => array(
                        'name' => 'line_2pic',
                        'desc' => '一行2张广告图',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_2pic/preview.png',
                    ),
                    'line_3pic' => array(
                        'name' => 'line_3pic',
                        'desc' => '一行3张广告图',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_3pic/preview.png',
                    ),
                    'line_4pic' => array(
                        'name' => 'line_4pic',
                        'desc' => '一行4张广告图',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_4pic/preview.png',
                    ),
                ),
            ),
            
            'goods' => array(
                'desc' => '商品',
                'items' => array(
                    'line_1goods' => array(
                        'name' => 'line_1goods',
                        'desc' => '一行1个产品',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_1pic/preview.png',
                    ),
                    'line_2goods' => array(
                        'name' => 'line_2goods',
                        'desc' => '一行2个产品',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_2goods/preview.png',
                    ),
                    'line_3goods' => array(
                        'name' => 'line_3goods',
                        'desc' => '一行3个产品',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_3goods/preview.png',
                    ),
                    'line_4goods' => array(
                        'name' => 'line_4goods',
                        'desc' => '一行4个产品',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/line_4goods/preview.png',
                    ),
                ),
                ),
                
            'combine' => array(
                'desc' => '图、文字链、商品等组合形式',
                'items' => array(
                    'combine1' => array(
                        'name' => 'combine1',
                        'desc' => '组合形式1',
                        'preview' => RESOURCE_SITE_URL . '/pc_special/widgets/combine1/preview.png',
                    ),
                ),
            ),
            
        )
        ;
        
        return $module_list;
    }

    /**
     * 清理缓存
     */
    private function _delPCSpecialCache($special_id) {
        // 清理缓存
        dcache($special_id, 'pc_special');
        
        // 删除静态文件
        $html_path = $this->getPCSpecialHtmlPath($special_id);
        if (is_file($html_path)) {
            @unlink($html_path);
        }
    }
}
