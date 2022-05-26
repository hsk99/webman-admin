<?php

namespace support\hsk99\util;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    /**
     * 发送邮件
     *
     * @author HSK
     * @date 2022-04-16 18:12:59
     *
     * @param string|array $toMail
     * @param string $subject
     * @param string $body
     * @param boolean $isHTML
     * @param array $config
     *
     * @return array
     */
    public static function send($toMail, $subject = '', $body = '', $isHTML = true, $config = []): array
    {
        try {
            $config = $config ?: get_system(null, []);

            if (
                empty($config['smtp_host']) ||
                empty($config['smtp_user']) ||
                empty($config['smtp_pass']) ||
                empty($config['smtp_secure']) ||
                empty($config['smtp_port'])
            ) {
                throw new \Exception('未配置邮箱参数', 500);
            }

            $mail = new PHPMailer();

            $mail->CharSet   = "UTF-8";
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_user'];
            $mail->Password   = $config['smtp_pass'];
            $mail->SMTPSecure = $config['smtp_secure'];
            $mail->Port       = $config['smtp_port'];
            $mail->From       = $config['smtp_user'];

            if (is_array($toMail)) {
                foreach ($toMail as $email) {
                    $mail->addAddress($email);
                }
            } else {
                $mail->addAddress($toMail);
            }

            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $body;

            if ($mail->send()) {
                return ['code' => 200, 'msg' => '发送成功'];
            } else {
                return ['code' => 400, 'msg' => $mail->ErrorInfo];
            }
        } catch (\Throwable $th) {
            return ['code' => 400, 'msg' => $th->getMessage()];
        }
    }
}
