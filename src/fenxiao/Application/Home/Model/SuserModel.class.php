<?php

namespace Home\Model;

use Think\Model;

//$model->query('select * from user where id=%d and status=%d',array($id,$status));
class SuserModel extends Model {

    public function adduser($data, $id = '') {
        if (!$id) {
            if($this->checkusername($username))
                return false;
            return $this->add($data);
        } else {
//            $condition['id'] = $id;
            return $this->where('id=' . $id)->save($data);
        }
    }

    public function checkLogin($username, $password) {
//        $User->where('status=1')->order('create_time')->limit(10)->select();
        if (!$username || !$password) {
            //账号不存在
            return array('status' => 1, 'msg' => '用户名或者密码不能为空！');
        }
        $user = $this->where('username=' . $username)->find();
        if (!$user) {
            //账号不存在
            return array('status' => 2, 'msg' => '账号不存在！');
        }
        if ($user && $user['password'] != md5($password . $user['ctime'])) {
            //账号或者密码错误
            return array('status' => 3, 'msg' => '账号或者密码错误!');
        }
        session('uid', $user['id']);
        session('shopName', $user['shopname']);
//        p(session());die;
        return array('status' => 4, 'msg' => 'success');
        ;
    }

    public function getUserById($uid) {
        $data['id'] = $uid;
        return $this->where($data)->find();
    }
    
    public function checkusername($username){
        $data['username'] = $username;
        $result =$this->where($data)->find();
        if(count($result)>0){
            return true;
        }
        return false;
    }
//FROM_UNIXTIME(addtime,'%Y年%m月%d') 
    public function getUserList($pagesize = 6, $page = 1,$distributorname) {
        $data = array();
        if($distributorname){
            $data['shopName']=array('like','%'.$distributorname.'%');
        }
        $offset = 0;
        if ($page > 1)
            $offset = ($page - 1) * $pagesize;
        $result = $this->order('id desc')->limit($offset, $pagesize)->where($data)->field('*,FROM_UNIXTIME(ctime,"%Y-%m-%d %H:%i:%S") as datetime ')->select();
        $totalrows = $this->where($data)->count();
        $pagetotal = ceil($totalrows / $pagesize);
        return array($totalrows, $result,$pagetotal);
    }

//    public function showOne() {
//        return $this->find();
//    }
//
//    public function showPageData($page = 2, $pagesize = 10) {
//        return $this->page($page, $pagesize)->order('aid asc')->select();
//    }
//
//    //测试其他数据库
//    public function showTestData() {
//        return $this->db(1, 'DB_CONFIG_BBS')->table('bbs_common_member')->limit('0,10')->select();
//    }
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