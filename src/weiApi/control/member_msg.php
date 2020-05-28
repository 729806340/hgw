<?php
defined('ByShopWWI') or exit('Access Invalid!');
class member_msgControl extends mobileMemberControl {

    public function __construct(){
        parent::__construct();
    }

    public function msg_listOp() {

        $cur_page = $_POST['cur_page'];
        $cur_page = max($cur_page, 1);
        $page_num = $this->page;
        $start_num = ($cur_page- 1) * $page_num;
        $limit = $start_num . ',' . $page_num;
        /** @var messageModel $model_message */
        $model_message = Model('message');
        $member_info = $this->member_info;

        $condition = array(
            'message_parent_id'   => 0,
            'to_member_id'        => $member_info['member_id'],
            'message_type'        => 1,
            'message_state'       => 0, //正常状态
            'message_json' => array('neq', '')
        );
        $message_list = $model_message->getWeiMessageList($condition, $limit);
        $msg_list = array();
        foreach ($message_list as $list) {
            $msg_list[] = array(
                'msg_id'       => $list['message_id'],
                'msg_content'  => json_decode($list['message_json'], true),
                'msg_read'     => $list['message_open'] ? true : false,
                'message_time' => $list['message_time'] ? date('Y-m-d H:i:s', $list['message_time']) : '',
            );
        }
        output_data(array('msg_list' => $msg_list, 'has_more' => count($message_list) == $page_num));
    }

    public function read_msgOp() {
        $msg_id = trim($_POST['msg_id']);
        $msg_id = json_decode($msg_id, true);
        if (empty($msg_id)) {
            output_data(array('result' => 'no_param'));
        }
        $result = Model('message')->updateCommonMessage(array('message_open' => 2), array('message_id', array('in', $msg_id)));
        if (!$result) {
            output_data(array('result' => 'deal_false'));
        }
        output_data(array('result' => true));
    }

    public function drop_msg() {
        $msg_id = trim($_POST['msg_id']);
        $msg_id = json_decode($msg_id, true);
        if (empty($msg_id)) {
            output_data(array('result' => 'no_param'));
        }
        $result = Model('message')->updateCommonMessage(array('message_state' => 2), array('message_id', array('in', $msg_id)));
        if (!$result) {
            output_data(array('result' => 'deal_false'));
        }
        output_data(array('result' => true));
    }

}
