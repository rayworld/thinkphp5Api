<?php

namespace app\api\controller\v1;

use think\Request;
use think\Controller;

class Common extends Controller
{
    protected $request;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        //$this->check_time($this->request->only(['time']));
        $this->check_token($this->request->param());
    }

    /**
     * Undocumented function
     *
     * @param [type] $arr
     * @return void
     */
    public function check_token($arr)
    {
        if (!isset($arr['token']) || empty($arr['token'])) {
            $this->return_msg(402, 'token is empty');
        }

        $client_token = $arr['token'];

        unset($arr['token']);
        $server_token = '';
        foreach ($arr as $key => $Value){
            $server_token .= $Value;
        }
        $server_token=md5("api_".$server_token."_api");
        //echo $server_token;
        if ($client_token !== $server_token) {
            $this->return_msg(403, 'token error');
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $arr
     * @return void
     */
    public function check_time($arr)
    {
        if (!isset($arr['time']) || intval($arr['time'] <= 1)) {
            $this->return_msg(400, 'timespan error!');
        }

        if (time() - intval($arr['time']) > 60) {
            $this->return_msg(401, 'time out!');
        }
    }

    /**
     * Undocumented function
     *
     * @param [int] $code
     * @param string $msg
     * @param array $data
     * @return void
     */
    public function return_msg($code, $msg = '', $data = [])
    {
        $return_data['code'] = $code;
        $return_data['msg'] = $msg;
        $return_data['data'] = $data;
        echo json_encode($return_data);
        die;
    }
}
