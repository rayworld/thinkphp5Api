<?php

namespace app\api\controller\v1;

use think\Request;
use think\Controller;

class Common extends Controller
{
    protected $request;

    public function _initialize()
    {

        echo 'init<br/>';

        //parent::_initialize();
        //$this->request = Request::instance();
        //$this->check_time($this->request->only(['time']));
    }

    //
    public function check_time($arr)
    {
        if (!isset($arr['time']) || intval($arr['time'] <= 1)) {
            $this->return_msg(400, 'timespan error!');
        }

        if (time() - intval($arr['time']) > 60) {
            $this->return_msg(401, 'time out!');
        }
    }

    //*** */
    public function return_msg($code, $msg = '', $data = [])
    {
        $return_data['code'] = $code;
        $return_data['msg'] = $msg;
        $return_data['data'] = $data;
        echo json_encode($return_data);
        die;
    }
}
