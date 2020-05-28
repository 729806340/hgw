<?php
/**
 * Class ShequBuyService
 */
class ShequBuyService
{

    //下单初始化成团中
    public function createTuan($order_info) {
        $tz_id = $order_info['shequ_tz_id'];
        $tuan_id = $order_info['shequ_tuan_id'];
        //检查是否存在
        if (empty($tz_id) || empty($tuan_id)) {
            return true;
        }
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $check_exist = $shequ_tuan_model->getOne(array(
            'tz_id' => $tz_id,
            'config_id' => $tuan_id,
        ));
        if ($check_exist) {
            $shequ_tuan_model->edit(array('id'=>$check_exist['id']),array(
                'order_num'=>array('exp','order_num+1'),
                'update_time'=>TIMESTAMP,
                'total_amount'=>array('exp','total_amount+'.$order_info['total_amount']),
                'commis_amount'=>array('exp','commis_amount+'.$order_info['shequ_return_amount']),
            ));
            return true;
        }
        /** @var shequ_tuan_configModel $shequ_tuan_config_model */
        $shequ_tuan_config_model = Model('shequ_tuan_config');
        $shequ_tuan_config_info = $shequ_tuan_config_model->getTuanConfigInfo(array('config_tuan_id' => $tuan_id));
        if (empty($shequ_tuan_config_info)) {
            return true;
        }
        /** @var shequ_tuanzhangModel $shequ_tuanzhang_model */
        $shequ_tuanzhang_model = Model('shequ_tuanzhang');
        $shequ_tuanzhang_info = $shequ_tuanzhang_model->getOne(array(
            'id' => $tz_id,
        ));
        if (empty($shequ_tuanzhang_info)) {
            return true;
        }
        /** @var shequ_addressModel $shequ_address */
        $shequ_address = Model('shequ_address');
        $address_info = $shequ_address->getOne(array(
            'tuanzhang_id' => $shequ_tuanzhang_info['id']
        ));
        if (empty($address_info)) {
            return true;
        }

        $tuan_sn = mt_rand(10,99)
        . sprintf('%010d',time() - 946656000)
        . sprintf('%03d', (float) microtime() * 1000)
        . sprintf('%03d', (int) $shequ_tuanzhang_info['member_id'] % 1000);

        $insert_data = array(
            'tuan_sn' => $tuan_sn,
            'tz_id' => $tz_id,
            'config_id' => $tuan_id,
            'member_id' => $shequ_tuanzhang_info['member_id'],
            'start_time' => $shequ_tuan_config_info['config_start_time'],
            'end_time' => $shequ_tuan_config_info['config_end_time'],
            'address_id' => $address_info['id'],
            'address' => $address_info['address'],
            'building' => $address_info['building'],
            'longitude' => $address_info['longitude'],
            'latitude' => $address_info['latitude'],
            'total_amount' => $order_info['order_amount'],
            'commis_amount' => $order_info['shequ_return_amount'],
            'state' => 10,
            'order_num' => 1,
            'add_time' => TIMESTAMP,
            'update_time' => TIMESTAMP,
            'tz_name' => $shequ_tuanzhang_info['name'],
            'tz_nick_name' => $shequ_tuanzhang_info['nick_name'],
            'tz_avatar' => $shequ_tuanzhang_info['avatar'],
            'tz_phone' => $shequ_tuanzhang_info['phone'],
            'send_product_date' => $shequ_tuan_config_info['send_product_date'],
        );
        /** @var shequ_tuanModel $shequ_tuan_model */
        $shequ_tuan_model = Model('shequ_tuan');
        $shequ_tuan_model->addItem($insert_data);
        return true;

    }

}