<?php

namespace Home\Model;

use Think\Model;

//$model->query('select * from user where id=%d and status=%d',array($id,$status));
class DistributorModel extends Model {

    public function addDistributor($data, $id = '') {
        if (!$id) {
            return $this->add($data);
        } else {
//            $condition['id'] = $id;
            return $this->where('id=' . $id)->save($data);
        }
    }

}

// //将更新数据写入数据库
//   function update(){
//     $user=M('user');
//     $user->password=md5($user->password);
//     if($user->create()){
//       if($insertid=$user->save()){
//         $this->success('更新成功,受影响的行数为'.$insertid);
//       }else{
//         $this->error('更新失败');   
//       }