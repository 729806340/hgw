<?php
/*提供给SAP接口的统一入口*/
defined('ByShopWWI') or exit('Access Invalid!');

class sapControl
{
    private $finish = false;

    public function callbackOp()
    {
        $this->begin();
        list($code, $params) = $this->parse_request();
        $msg = '';
        $result = Service('Sap')->callback($code, $params, $msg);
        if($result == false){
            $this->send_error($msg);
        } else {
            $this->send_success($result);
        }
    }

    private function parse_request()
    {

        $request = file_get_contents('php://input');
        $sign = $_REQUEST['sign'];
        $code = $_REQUEST['code'];

        if (empty($code) || $sign != 'fac58a7560f573b6c238948359c01085') {
            $this->send_error(1000, 'code or sign error');
            return false;
        }
        return array($code, $request);
    }

    public function shutdown()
    {
        $this->end(true);
    }

    private function begin()
    {
        ignore_user_abort();
        set_time_limit(0);
        header('Content-Type: application/json;charset=utf8;');
        header('Connection: close');
        register_shutdown_function(array(&$this, 'shutdown'));
        @ob_start();

    }

    private function end($shutdown = false)
    {
        if ($this->finish) return null;
        $this->finish = true;
        $content = ob_get_contents();
        ob_end_clean();
        if ($shutdown) {
            $res = array(
                'code' => 2000,
                'data' => '',
                'tips' => 'System internal error',
                'description' => $content,
            );
            $this->send_result($res);
        }
        return $content;
    }

    private function send_error($msg, $code = 200)
    {
        $res = array(
            'code' => intval($code) > 0 ? intval($code) : 200,
            'data' => '',
            'tips' => $msg,
            'description' => $this->end(),
        );
        $this->send_result($res);

    }

    private function send_success($data = array(), $msg = 'success')
    {
        $res = array(
            'code' => 0,
            'data' => $data,
            'tips' => $msg,
            'description' => $this->end(),
        );
        $this->send_result($res);
    }

    private function send_result($data)
    {
        echo encode_json($data);
        exit;
    }

}