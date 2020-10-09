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
     * 上传用户图像
     *
     * @return void 空
     */
    public function upload_header_image()
    {
        //只允许以post方式请求数据，报错可不管
        $this->validate_request('post');
        $data = $this->params;
        $upload_image_path = $this->upload_file($data['header_image'], 'header_iamge');
        $res = db('user')
            ->where('user_id', $data['user_id'])
            ->setField('header_image', $upload_image_path);
        if ($res) {
            $this->return_msg(200, '上传图像成功', $upload_image_path);
        } else {
            $this->return_msg(400, '上传图像失败');
        }
    }

    /**
     * 用户登录过程
     *
     * @return 用户信息
     */
    public function login()
    {
        ////只允许以post方式请求数据
        $this->validate_request('post');
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
            unset($res['password']);
            Session('user_info', $res);
            $this->return_msg(200, '用户登录成功', $res);
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
        $this->validate_request('post');
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
    }

    /**
     * 用户修改密码
     *
     * @return 空
     */
    public function change_password()
    {
        $this->validate_request('post');
        $data = $this->params;
        $account_type = $this->check_account_type($data['account_name']);
        $this->check_user_exist($data['account_name'], $account_type, 1);
        $where[$account_type] = $data['account_name'];
        $origin_pass = db('user')->where($where)->value('password');
        if ($origin_pass == $data['origin_password']) {
            $res = db('user')->where($where)->setField('password', $data['new_password']);
            if ($res !== false) {
                $this->return_msg(200, '密码修改成功！');
            } else {
                $this->return_msg(400, '密码修改失败！');
            }
        } else {
            $this->return_msg(400, '原密码不正确');
        }
    }

    /**
     * 用户重置密码
     *
     * @return void
     */
    public function reset_password()
    {
        $this->validate_request('post');
        $data = $this->params;
        $this->check_captcha($data['account_name'], $data['captcha']);
        $account_type = $this->check_account_type($data['account_name']);
        $this->check_user_exist($data['account_name'], $account_type, 1);
        $where[$account_type] = $data['account_name'];
        $res = db('user')->where($where)->setField('password', $data['password']);
        if ($res !== false) {
            $this->return_msg(200, '密码修改成功！');
        } else {
            $this->return_msg(400, '密码修改失败！');
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function bind_mobile_or_email()
    {
        $this->validate_request('post');
        $data = $this->params;
        $this->check_captcha($data['account_name'], $data['captcha']);
        $account_type = $this->check_account_type($data['account_name']);
        $account_type_desc =
            $res = db('user')
            ->where('user_id', $data['user_id'])
            ->setField($account_type, $data['account_name']);
        if ($res !== false) {
            $this->return_msg(200, $account_type_desc . '绑定成功！');
        } else {
            $this->return_msg(400, $account_type_desc . '绑定失败！');
        }
    }

    public function update_nick_name()
    {
        $this->validate_request('post');
        $data = $this->params;
        $account_type = $this->check_account_type($data['account_name']);
        $this->check_user_exist($data['account_name'], $account_type, 1);
        $res = db('user')
            ->where($account_type, $data['account_name'])
            ->setField('nick_name', $data['nick_name']);
        if ($res !== false) {
            $this->return_msg(200, '用户昵称修改成功！');
        } else {
            $this->return_msg(400, '用户昵称修改失败！');
        }
    }
}
