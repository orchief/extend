<?php

namespace Mail;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

Class Mail
{
	public static function send($body, $projectName = '基础模板', $from = '18230373213@163.com')
	{
        // TODO: 检查当前环境是否为本地 
        $addr = $_SERVER['SERVER_ADDR'];

        $host = $addr;
        if($addr == '127.0.0.1' || $addr == '::1'){
            $host = '本地';
        }else{
            $host = '线上';
        }

        $transport = (new Swift_SmtpTransport('smtp.163.com', 25))
        ->setUsername($from)
        ->setPassword('035212a');

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message($host))
        ->setFrom(['18230373213@163.com' => $projectName . '(' . $host . ')'])
        ->setTo(['orchief@163.com'])
        ->setBody($body);

        $result = $mailer->send($message);

		return $result;

    }
}