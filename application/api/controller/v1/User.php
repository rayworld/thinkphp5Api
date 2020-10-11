<?php

namespace app\api\controller\v1;

class User extends Common
{
    public function index()
    {
        return 'user index';
    }

    /**
     * 上传用户图像
     *
     * @return void [用户图像路径]
     */
    public function upload_header_image()
    {
        //只允许以post方式请求数据
        $this->validate_request('post');
        //取得请求参数
        $data = $this->params;
        //上传图像，取得上传文件路径
        $upload_image_path = $this->upload_file($data['header_image']);
        //将图像路径写如User表header_image列
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
     * @return [用户信息]
     */
    public function login()
    {
        ////只允许以post方式请求数据
        $this->validate_request('post');
        //取得请求参数
        $data = $this->params;
        //取得账户类型
        $account_type = $this->check_account_type($data['account_name']);
        //取得用户信息
        $res = db('user')
            ->field(`user_id`, `nick_name`, `mobile`, `register_time`, `email`)
            ->Where($account_type, $data['account_name'])
            ->find();
        //验证用户密码是否正确
        if ($res['password'] != $data['user_pwd']) {
            $this->return_msg(400, '用户信息不存在');
        } else {
            //在显示信息中去掉密码，密码永不返回
            unset($res['password']);
            //保存用户信息到Session
            Session('user_info', $res);
            $this->return_msg(200, '用户登录成功', $res);
        }
    }

    /**
     * 用户注册过程
     *
     * @return [空]
     */
    public function register()
    {
        //只允许以post方式请求数据
        $this->validate_request('post');
        //取得参数
        $data = $this->params;
        //检查验证码是否正确
        $this->check_captcha($data['account_name'], $data['captcha']);
        //取得账户类型
        $account_type = $this->check_account_type($data['account_name']);
        //验证账号信息是否不存在
        $this->check_user_exist($data['account_name'], $account_type, 0);
        //写入账号信息
        $data[$account_type] = $data['account_name'];
        //设置用户昵称
        $data['nick_name'] = $data['user_name'];
        //设置注册时间
        $data['register_time'] = time();
        //写入用户注册信息
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
     * @return void [空]
     */
    public function change_password()
    {
        //只允许以post方式请求数据
        $this->validate_request('post');
        //取得参数
        $data = $this->params;
        //取得账户类型
        $account_type = $this->check_account_type($data['account_name']);
        //确认账户信息是否存在
        $this->check_user_exist($data['account_name'], $account_type, 1);
        //取得查询范围
        $where[$account_type] = $data['account_name'];
        //取得用户原来密码
        $origin_pass = db('user')->where($where)->value('password');
        //验证原密码是否正确
        if ($origin_pass == $data['origin_password']) {
            //写入新密码
            $res = db('user')
                ->where($where)
                ->setField('password', $data['new_password']);
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
     * @return void [空]
     */
    public function reset_password()
    {
        //只允许以post方式请求数据
        $this->validate_request('post');
        //取得参数
        $data = $this->params;
        //验证验证码是否正确
        $this->check_captcha($data['account_name'], $data['captcha']);
        //取得账户类型
        $account_type = $this->check_account_type($data['account_name']);
        //账户信息是否存在
        $this->check_user_exist($data['account_name'], $account_type, 1);
        //取得账户信息
        $where[$account_type] = $data['account_name'];
        //重置密码
        $res = db('user')
            ->where($where)
            ->setField('password', $data['password']);
        if ($res !== false) {
            $this->return_msg(200, '密码修改成功！');
        } else {
            $this->return_msg(400, '密码修改失败！');
        }
    }

    /**
     * 绑定用户手机号/邮箱
     *
     * @return void [空]
     */
    public function bind_mobile_or_email()
    {
        //只允许以post方式请求数据
        $this->validate_request('post');
        //取得参数
        $data = $this->params;
        //验证验证码是否正确
        $this->check_captcha($data['account_name'], $data['captcha']);
        //取得账户类型
        $account_type = $this->check_account_type($data['account_name']);
        //取得账户类型描述
        $account_type_desc = $account_type == 'mobile' ? '手机号' : '邮箱';
        //绑定用户手机号/邮箱
        $res = db('user')
            ->where('user_id', $data['user_id'])
            ->setField($account_type, $data['account_name']);
        if ($res !== false) {
            $this->return_msg(200, $account_type_desc . '绑定成功！');
        } else {
            $this->return_msg(400, $account_type_desc . '绑定失败！');
        }
    }

    /**
     * 修改用户昵称
     *
     * @return void [空]
     */
    public function update_nick_name()
    {
        //只允许以post方式请求数据
        $this->validate_request('post');
        //取得参数
        $data = $this->params;
        //取得账户类型
        $account_type = $this->check_account_type($data['account_name']);
        //验证用户信息是否存在
        $this->check_user_exist($data['account_name'], $account_type, 1);
        //修改用户昵称
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
