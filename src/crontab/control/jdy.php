<?php
/**
 * 任务计划 - 分钟执行的任务
 *
 * @汉购网提供技术支持 授权请购买shopnc授权
 * @license    http://www.hangowa.com
 * @link       交流群号：
 */
defined ( 'ByShopWWI' ) or exit ( 'Access Invalid!' );
class jdyControl extends BaseCronControl {
    public function parseBillsOp() {
        /** @var jdyLogic $jdy */
        $jdy = Logic('jdy');
        $jdy->paresBill();
        return true;
    }

    public function mapEntriesOp() {
        /** @var jdyLogic $jdy */
        $jdy = Logic('jdy');
        $jdy->mapEntries();
        return true;
    }

    public function pushBillsOp(){
        $jdy = Logic('jdy');
        $jdy->pushBills();
        return true;
    }

    public function purchaseReturnOp() {
        /** @var jdyLogic $jdy */
        $jdy = Logic('jdy');
        $jdy->purchaseReturn();
        return true;
    }

}
