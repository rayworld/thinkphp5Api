<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Validate;
use think\Image;

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
                'account_name' => 'require',
                'user_pwd' => 'require|length:32',
            ),
            'register' => array(
                'account_name' => 'require',
                'user_pwd' => 'require|length:32',
                'captcha' => 'require|number|length:6',
            ),
            'upload_header_image' => array(
                'user_id' => 'require|number',
                'header_image' => 'require|image|fileSize:4000000|fileExt:jpg,png,bmp,jpeg',
            ),
            'change_password' => array(
                'account_name' => 'require',
                'origin_password' => 'require|length:32',
                'new_password' => 'require|length:32',
            ),
            'reset_password' => array(
                'account_name' => 'require',
                'captcha' => 'require|number|length:6',
                'password' => 'require|length:32',
            ),
            'bind_mobile_or_email' => array(
                'account_name' => 'require',
                'captcha' => 'require|number|length:6',
                'user_id' => 'require|number',
            ),
            'update_nick_name' => array(
                'account_name' => 'require',
                'nick_name' => 'require',
            ),
            'index' => array(),
        ),
        'captcha' => array(
            'get_captcha' => array(),
        ),
        'blog' => array(
            'get_list' => array(
                'user_id' => 'require',
            ),
            'detail' => array(
                'article_id' => 'require|number',
            ),
            'insert' => array(),
            'update' => array(
                'article_id' => 'require|number',
                'comment' => 'require|chsDash',
            ),
            'delete' => array(
                'article_id' => 'require|number',
            ),
        )
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
        $this->params = $this->validate_params($this->request->param(true));
    }

    /**
     * 参数过滤
     *
     * @param array $arr [全部请求参数]
     * @return array 过滤后的参数
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
            $this->return_msg(402, 'token不能为空！');
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
        $mobile_res = db('user')->where('mobile', $account_name)->find();
        $email_res = db('user')->where('email', $account_name)->find();
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

    /**
     * 验证验证吗是否正确
     *
     * @param [string] $account_name【账户信息】
     * @param [number] $captcha【要验证验证吗】
     * @return void
     */
    public function check_captcha($account_name, $captcha)
    {
        //从Seesion取得该账号的验证码。
        $session_captcha = session($account_name . '_captcha');
        //如果Seesion上次发送时间间隔效益60秒，报错。
        if (time() - session($account_name . '_last_sand_time') > 6000000) {
            //打印错误信息
            $this->return_msg(400,  '验证码已经过期');
        }
        //session验证码不相等，报错。
        if ($session_captcha != md5('account' . '_' . md5($captcha))) {
            $data['dfs'] = $session_captcha;
            $data['ddd'] = md5('account' . '_' . md5($captcha));

            $this->return_msg(400, '验证码错误!', $data);
        }
        //删除session验证码
        session($account_name . '_captcha', null);
        session($account_name . '_last_sand_time', null);
    }

    /**
     * 上传文件
     *
     * @param [type] $file
     * @param string $type
     * @return void
     */
    public function upload_file($file, $type = '')
    {
        //$root = new Env();
        $info = $file->move(env('root_path') . 'public/static/uploads');
        if ($info) {
            $path = 'static/uploads/' . date('Ymd') . '/' . $info->getFileName();
        } else {
            $this->return_msg(400, $info->getError());
        }
        if (!empty($type)) {
            $this->cut_image($path);
        }
        return $path;
    }

    /**
     * 切割图像
     *
     * @param string $path [文件路径]
     * @return void 空
     */
    public function cut_image($path)
    {
        $image = Image::open(env('root_path') . 'public/' . $path);
        $image->thumb(200, 200, Image::THUMB_CENTER)->save(env('root_path') . 'public/' . $path);
    }

    /**
     * 检查Api数据请求方式
     *
     * @param [string] $methor [请求方式]
     * @return 空
     */
    public function validate_request($methor)
    {
        switch ($methor) {
            case 'get':
                if (!request()->isGet()) {
                    $this->return_msg(400, 'Api数据请求方式错误！');
                }
                break;
            case 'post':
                if (!request()->isPost()) {
                    $this->return_msg(400, 'Api数据请求方式错误！');
                }
                break;
            case 'put':
                if (!request()->isPut()) {
                    $this->return_msg(400, 'Api数据请求方式错误！');
                }
                break;
            case 'delete':
                if (!request()->isDelete()) {
                    $this->return_msg(400, 'Api数据请求方式错误！');
                }
                break;
        }
    }
}
