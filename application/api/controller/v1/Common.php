<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Validate;
use think\Db;

class Common extends Controller
{
    //请求
    protected $request;
    //返回的参数
    protected $params;
    //验证器
    protected $validater;
    //验证规则
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
        //执行Controller类的构造函数
        parent::__construct();
        //时间戳验证
        //$this->check_time($this->request->only(['time']));
        //Token验证
        //$this->check_token($this->request->param());
        //参数过滤
        $this->params = $this->validate_params($this->request->except(['time', 'token']));
    }

    /**
     * 参数过滤
     *
     * @param [array] $arr [全部请求参数]
     * @return 过滤后的参数
     */
    public function validate_params($arr)
    {
        //取得控制器名，去掉前面的版本号
        $controller_name = substr($this->request->controller(), strpos($this->request->controller(), '.') + 1);
        //取得操作器名
        $action_name = $this->request->action();
        //取得验证规则
        $rule = $this->rules[$controller_name][$action_name];
        //实力话验证器
        $this->validater = new validate($rule);
        //验证
        if (!$this->validater->check($arr)) {
            //输出错误信息
            $this->return_msg(404, $this->validater->getError());
        }
        //返回过滤后的参数
        return $arr;
    }

    /**
     * token验证
     *
     * @param [array] $arr [全部请求参数]
     * @return [json] [token 验证结果]
     */
    public function check_token($arr)
    {
        //token是否存在
        if (!isset($arr['token']) || empty($arr['token'])) {
            $this->return_msg(402, 'token is empty');
        }
        //请求发过来的token
        $client_token = $arr['token'];
        //从全部请求参数中去掉token参数
        unset($arr['token']);
        //服务器端的token
        $server_token = '';
        //生成服务器端的token
        foreach ($arr as $key => $Value) {
            $server_token .= $Value;
        }
        //加密token
        $server_token = md5("api*" . $server_token . "*api");
        //对比请求过来的token和服务器token是否一致
        if ($client_token !== $server_token) {
            //打印错误信息
            $this->return_msg(403, 'token error');
        }
    }

    /**
     * 时间戳验证
     *
     * @param [array] $arr [全部请求参数]
     * @return [json] [时间戳验证结果]
     */
    public function check_time($arr)
    {
        //验证时间戳参数是否存在
        if (!isset($arr['time']) || intval($arr['time'] <= 1)) {
            $this->return_msg(400, '时间戳不正确!');
        }
        //验证时间戳是否超时
        if (time() - intval($arr['time']) > 60) {
            $this->return_msg(401, '验证超时!');
        }
    }

    /**
     * 返回错误信息，终止程序继续执行
     *
     * @param [int] $code 【错误代码】
     * @param 【string】 $msg 【错误信息】
     * @param 【array】 $data 【返回数据】
     * @return 空
     */
    public function return_msg($code, $msg = '', $data = [])
    {
        //取得错误代码
        $return_data['code'] = $code;
        //取得错误信息
        $return_data['msg'] = $msg;
        //取得返回数据
        $return_data['data'] = $data;
        //输出错误信息
        echo json_encode($return_data);
        //终止程序继续执行
        die;
    }

    /**
     * 取得注册账户类型
     *
     * @param [string] $account_name 注册账户信息
     * @return 【string】【注册账户类型email或者mobile】
     */
    public function check_account_type($account_name)
    {
        //验证是不是email
        $is_email = filter_var($account_name, FILTER_VALIDATE_EMAIL) ? 1 : 0;
        //验证是不是手机
        $is_mobile = strlen($account_name) == 11 && preg_match('/^1[3|4|5|8][0-9]\d{4,8}$/', $account_name) ? 4 : 2;
        $flag = $is_mobile + $is_email;
        switch ($flag) {
            case 2:
                $this->return_msg(404, '账户信息不正确');
                break;
            case 3:
                return 'email';
                break;
            case 4:
                return 'mobile';
                break;
        }
    }


    /**
     * 检查账户信息是否正确
     *
     * @param [string] $account_name 账户信息
     * @param [string] $account_type 账户信息类型：mobile，email
     * @param [bool] $is_exist 账户信息是否应该存在
     * @return void
     */
    public function check_user_exist($account_name, $account_type, $is_exist)
    {
        $type_value = $account_type == 'mobile' ? 2 : 4;
        $flag = $is_exist + $type_value;
        $mobile_res = db('account')->where('mobile', $account_name)->find();
        $email_res = db('account')->where('email', $account_name)->find();
        switch ($flag) {
            case 2:
                if ($mobile_res) {
                    $this->return_msg(400, '手机号被占用');
                }
                break;
            case 3:
                if (!$mobile_res) {
                    $this->return_msg(400, '手机号不存在');
                }
                break;
            case 4:
                if ($email_res) {
                    $this->return_msg(400, '邮箱被占用');
                }
                break;
            case 5:

                if (!$email_res) {
                    $this->return_msg(400, '邮箱不存在');
                }
                break;
        }
    }
}
