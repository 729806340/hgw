<?php

namespace Home\Model;

use Think\Model;

//$model->query('select * from user where id=%d and status=%d',array($id,$status));
class B2cMembersModel extends Model {

    public function UpdateMembers($data, $id = '') {
        if (!$id) {
            if($this->checkusername($username))
                return false;
            return $this->add($data);
        } else {
//            $condition['id'] = $id;
            return $this->where('id=' . $id)->save($data);
        }
    }
    
      public function getOneMembers($username = '', $uid = '') {
        if ($username != '') {
            $conditions['uid'] = $uid;
        }
        if (!($uid === '')) {
            $conditions['member_id'] = $uid;
        }
        if ($pid != '') {
            $conditions['pid'] = $pid;
        }
        return $this->where($conditions)->select();
    }

}
