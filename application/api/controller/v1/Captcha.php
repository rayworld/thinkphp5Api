<?php

namespace app\api\controller\v1;

class Captcha extends Common
{
    public function get_captcha()
    {
        $account_name = $this->params['account_name'];
        $is_exist = $this->params['is_exist'];
        $account_type = $this->check_account_type($account_name);
        $this->get_captcha_by_account($account_name, $account_type, $is_exist);
    }

    public function get_captcha_by_account($account_name, $account_type, $is_exist)
    {
        $account_type_desc = $account_type == 'mobile' ? '手机' : '邮箱';
        $this->check_exist($account_name, $account_type, $is_exist);
        if (session('?' . $account_name . '_last_update_time')) {
            if (time() - session($account_name . '_last_update_time') < 30) {
                $this->return_msg(400, $account_type_desc . 'timespan too shot');
            }
        }

        $captcha = $this->make_captcha(6);
        $md5_captcha = md5('account' . md5($captcha));
        session($account_name . 'captcha', $md5_captcha);
        session($account_name . '_last_update_time', time());
        if ($account_type == 'mobile') {
            $this->send_captcha_by_mobile($account_name, $account_type, $captcha);
        } else {
            $this->send_captcha_by_email($account_name, $account_type, $captcha);
        }
    }
}
