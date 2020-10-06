<?php

use PHPMailer\PHPMailer;

namespace app\api\controller\v1;

use PHPMailer\PHPMailer\PHPMailer;

class Captcha extends Common
{
    /**
     * 取得验证码
     *
     * @return void
     */
    public function get_captcha()
    {
        //取得账户名称
        $account_name = $this->params['account_name'];
        //信息是否存在标志
        $is_exist = $this->params['is_exist'];
        //检查账户信息是否正确
        $account_type = $this->check_account_type($account_name);
        $this->get_captcha_by_account_type($account_name, $account_type, $is_exist);
    }

    /**
     * Undocumented function
     *
     * @param [type] $account_name
     * @param [type] $account_type
     * @param [type] $is_exist
     * @return void
     */
    public function get_captcha_by_account_type($account_name, $account_type, $is_exist)
    {
        //取得账户类型描述
        $account_type_desc = $account_type == 'mobile' ? '手机' : '邮箱';
        //检查账户信息是否正确
        $this->check_user_exist($account_name, $account_type, $is_exist);
        //上次发送时间不为空
        if (session('?' . $account_name . '_last_sand_time')) {
            //判断时间间隔小于30秒
            if (time() - session($account_name . '_last_sand_time') < 30) {
                //打印错误信息
                $this->return_msg(400, $account_type_desc . '验证码请求时间间隔小鱼30秒');
            }
        }

        //生成指定位数的验证码
        $captcha = $this->make_captcha(6);
        $md5_captcha = md5('Account' . '*' . md5($captcha));
        //保存加密后的验证码
        session($account_name . '_captcha', $md5_captcha);
        //保存生成验证码时间
        session($account_name . '_last_update_time', time());
        if ($account_type == 'mobile') {
            //生成手机验证码
            $this->send_captcha_by_mobile($account_name, $captcha);
        } else {
            //生成邮箱验证码
            $this->send_captcha_by_email($account_name, $captcha);
        }
    }


    /**
     * 生成指定位数的验证码
     *
     * @param [int] $num 生成验证码位数
     * @return [string] 生成的验证码
     */
    public function make_captcha($num)
    {
        $max = pow(10, $num) - 1;
        $min = pow(10, ($num - 1));
        return rand($min, $max);
    }

    /**
     * 生成手机验证码
     *
     * @param [string] $account_name 账户名称
     * @param [string] $captcha 验证码
     * @return void
     */
    public function send_captcha_by_mobile($account_name, $captcha)
    {
        echo 'send_captcha_by_mobile';
    }

    /**
     * 生成邮箱验证码
     *
     * @param [string] $account_name 账户名称
     * @param [string] $captcha 验证码
     * @return void
     */
    public function send_captcha_by_email($account_name, $captcha)
    {
        #echo 'send_captcha_by_email';
        $mailto = $account_name;
        $captcha_code = $captcha;
        $mail = new PHPMailer();
        //服务器配置
        $mail->CharSet = "UTF-8";                     //设定邮件编码
        $mail->SMTPDebug = 0;                        // 调试模式输出
        $mail->isSMTP();                             // 使用SMTP
        $mail->Host = 'smtp.sina.com';                // SMTP服务器
        $mail->SMTPAuth = true;                      // 允许 SMTP 认证
        $mail->Username = 'casp@sina.com';                // SMTP 用户名  即邮箱的用户名
        $mail->Password = 'A12345678';             // SMTP 密码  部分邮箱是授权码(例如163邮箱)
        $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
        $mail->Port = 465;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

        $mail->setFrom('casp@sina.com', 'Ray');  //发件人
        $mail->addAddress($mailto, 'ray');  // 收件人
        //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
        $mail->addReplyTo('casp@sina.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
        //$mail->addCC('cc@example.com');                    //抄送
        //$mail->addBCC('bcc@example.com');                    //密送

        //发送附件
        // $mail->addAttachment('../xy.zip');         // 添加附件
        // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

        //Content
        $mail->isHTML(false);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
        $mail->Subject = "你有新的验证码";
        $mail->Body    = "这是一个测试邮件，你的验证码是" . $captcha_code . "，验证码的有效期为一分钟，本邮件请勿回复!";
        //$mail->AltBody = "邮件客户端不支持HTML则显示此内容";

        if ($mail->send()) {
            echo 'mail send success!';
        } else {
            $this->return_msg(400, $mail->ErrorInfo);
        }
    }
}
