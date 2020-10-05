<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Validate;


class Common extends Controller
{
    protected $request;
    protected $params;
    protected $validater;
    protected $rules = array(
        'user' => array(
            'login' => array(
                'user_name' => 'require|chsDash|max:20',
                'user_pwd' => 'require|length:32'
            ),
            'index' => array(),

        ),
        'captcha' => array(
            'get_captcha' => array(),
        ),
    );

    /**
     * 构造函数，验证参数是否合法
     * PHP 5 用 _initialzer()
     */
    public function __construct()
    {
        parent::__construct(); //执行Controller类的构造函数
        //$this->check_time($this->request->only(['time']));//时间戳验证
        //$this->check_token($this->request->param());//Token验证
        $this->params = $this->validate_params($this->request->except(['time', 'token']));
    }

    /**
     * Undocumented function
     *
     * @param [type] $arr [全部请求参数]
     * @return void
     */
    public function validate_params($arr)
    {
        $controller_name = substr($this->request->controller(), strpos($this->request->controller(), '.') + 1);
        $action_name = $this->request->action();
        $rule = $this->rules[$controller_name][$action_name];
        $this->validater = new validate($rule);
        if (!$this->validater->check($arr)) {
            $this->return_msg(404, $this->validater->getError());
        }
        return $arr;
    }

    /**
     * token验证
     *
     * @param [type] $arr [全部请求参数]
     * @return [json] [token 验证结果]
     */
    public function check_token($arr)
    {
        if (!isset($arr['token']) || empty($arr['token'])) {
            $this->return_msg(402, 'token is empty');
        }

        $client_token = $arr['token'];
        unset($arr['token']); //去掉token参数
        $server_token = '';
        foreach ($arr as $key => $Value) {
            $server_token .= $Value;
        }
        $server_token = md5("api_" . $server_token . "_api");
        if ($client_token !== $server_token) {
            $this->return_msg(403, 'token error');
        }
    }

    /**
     * time 验证
     *
     * @param [type] $arr [全部请求参数]
     * @return [json] [time 验证结果]
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
     * 返回错误信息
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

    /**
     * Undocumented function
     *
     * @param [type] $account_name
     * @return void
     */
    public function check_account_type($account_name)
    {
        $is_email = filter_var($account_name, FILTER_VALIDATE_EMAIL) ? 1 : 0;
        $is_mobile = strlen($account_name) == 11 && preg_match('/^1[3|4|5|8][0-9]\d{4,8}$/', $account_name) ? 4 : 2;
        $flag = $is_mobile + $is_email;
        switch ($flag) {
            case 2:
                $this->return_msg(404, 'not mobile or email');
                break;
            case 3:
                return 'email';
                break;
            case 4:
                return 'mobile';
                break;
        }
    }
}
