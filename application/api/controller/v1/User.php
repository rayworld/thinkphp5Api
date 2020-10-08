<?php

namespace app\api\controller\v1;

use think\db\Where;
use think\facade\Request;

class User extends Common
{
    public function index()
    {
        return 'user index';
    }

    /**
     * 用户登录过程
     *
     * @return 用户信息
     */
    public function login()
    {
        ////只允许以post方式请求数据，报错可不管
        if (Request::isPost()) {
            //取得参数
            $data = $this->params;
            //取得账户类型
            $account_type = $this->check_account_type($data['account_name']);
            switch ($account_type) {
                case 'mobile':
                    $res = db('user')
                        ->field(`user_id`, `nick_name`, `mobile`, `register_time`, `email`)
                        ->Where('mobile', $data['account_name'])
                        ->find();
                    break;
                case "email":
                    $res = db('user')
                        ->field(`user_id`, `nick_name`, `mobile`, `register_time`, `email`)
                        ->Where('email', $data['account_name'])
                        ->find();
                    break;
            }

            if ($res['password'] != $data['user_pwd']) {
                $this->return_msg(400, '用户信息不存在');
            } else {
                Session('user_info', $data);
                $this->return_msg(200, '用户登录成功', $res);
            }
        } else {
            $this->return_msg(400, '数据请求方式不正确！');
        }
    }

    /**
     * 用户注册过程
     *
     * @return 空
     */
    public function register()
    {
        //只允许以post方式请求数据，报错可不管
        if (Request::isPost()) {
            //取得参数
            $data = $this->params;
            //检查验证码是否正确
            $this->check_captcha($data['account_name'], $data['captcha']);
            //取得账户类型
            $account_type = $this->check_account_type($data['account_name']);
            switch ($account_type) {
                case 'mobile':
                    //用户是手机号注册
                    $this->check_user_exist($data['account_name'], 'mobile', 0);
                    $data['mobile'] = $data['account_name'];
                    break;
                case 'email':
                    //用户是邮箱注册
                    $this->check_user_exist($data['account_name'], 'email', 0);
                    $data['email'] = $data['account_name'];
                    break;
            }
            //设置用户昵称
            $data['nick_name'] = $data['user_name'];
            //设置注册时间
            $data['register_time'] = time();
            //写入用户注册信息，$data报错可不管
            $res = db('user')->insert($data);
            if (!$res) {
                $this->return_msg(400, '写入用户注册信息错误');
            } else {
                $this->return_msg(200, '用户注册成功！');
            }
        } else {
            $this->return_msg(400, '数据请求方式不正确！');
        }
    }
}
