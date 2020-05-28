<?php
/**
 * 任务计划 - 天执行的任务
 *
 * 
 *
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined('ByShopWWI') or exit('Access Invalid!');

class messageControl extends BaseCronControl {

    /**
     * 该文件中所有任务执行频率，默认1天，单位：秒
     * @var int
     */
    const EXE_TIMES = 86400;


    /**
     * 默认方法
     */
    public function indexOp() {
        $this->_change_message() ;
    }


    public function _change_message()
    {
        $message = Model('message');
        // 消息模板
        $msg_tpl_list = $message->get_msg_tpl();

        ini_set('memory_limit','4G');
        $has_next = true;
        while ($has_next) {
            $message_list = $message->getMessageList("select * from shopwwi_message where message_json = '' limit 100");
            if (empty($message_list)) {
                die('所有数据已处理完成。');
            }

            foreach ($message_list as $k => $v) {
                try {
                    $data = array();
                    // 获取当前消息的模板
                    $mmt_code = $message->get_cur_msg_tpl($v['message_body']);
                    if (empty($mmt_code)) { // 用户消息
                        $data['title'] = '用户消息';
                        $data['code'] = 'user_message';
                        $data['content'] = $v['message_body'];
                        $data['param'] = array();
                        $data['label'] = '';
                    } else {
                        $cur_msg_tpl = $msg_tpl_list[$mmt_code];

                        $data['title'] = $cur_msg_tpl['mmt_name'];
                        $data['code'] = $cur_msg_tpl['mmt_code'];
                        $content = $v['message_body'];
                        preg_match('/<a .*?href="(.*?)".*?>(.*)?<\/a>/is', $content, $match);
                        $url = $match[1];
                        $url = parse_url($url);
                        parse_str($url['query'], $param_arr);
                        unset($param_arr['act'], $param_arr['op']);
                        $content = substr($content, 0, strpos($content, '。'));
                        $data['content'] = $content;
                        $data['param'] = $param_arr;
                        $data['label'] = $match[2];
                    }

                    Model('message')->updateCommonMessage(array('message_json' => json_encode($data, JSON_UNESCAPED_UNICODE)), array('message_id' => $v['message_id']));
                } catch (Exception $e) {
                    echo $e->getMessage();continue;
                }
            }
        }
    }
}