<?php

/***************************************************************
 *   $Program: www.zhuishukan.com (novel search engine)
 *    $Author: pakey $
 *     $Email: Pakey@ptcms.com
 * $Copyright: 2009 - 2012 Ptcms Studio $
 *      $Link: http://www.ptcms.com $
 *   $License: http://www.ptcms.com/service-license.html $
 *      $Date: 2013-05-06 12:10:52 +0800 (星期一, 06 五月 2013) $
 *      $File: Stmp.class.php $
 *  $Revision: 151 $
 *      $Desc: SMTP类 可以发送附件
 **************************************************************/

defined('MC_PATH') || exit('Permission denied');

/**
 * SMTP类
 * 使用实例
 * $smtpserver = "smtp.qq.com";     //SMTP服务器
 * $smtpport  = 25;            //SMTP服务器端口
 * $smtpusermail= "10000@qq.com"; //SMTP服务器的用户邮箱
 * $smtpsender="发信人名称"; //发信人名称
 * $smtpemailto= "100000@qq.com";   //发送给谁
 * $smtpuser  = "10000@qq.com";   //SMTP服务器的用户帐号
 * $smtppass  = "mypassword";          //SMTP服务器的用户密码
 * $mailsubject= "邮件发送测试";     //邮件主题
 * $mailbody  = "<b>asdasdasd</b>";   //邮件内容
 * $mailtype  = "HTML";          //邮件格式（HTML/TXT）,TXT为文本邮件
 * $mailattachment=array(__DIR__.'/1.rar'); //附件 数组形式
 * ##########################################
 * //返里面的一个true是表示使用身份验证,否则bu使用身份验证.
 * $smtp = new Smtp($smtpserver,$smtpport,true,$smtpuser,$smtppass);//实例化SMTP类
 * $smtp->sendmail($smtpemailto, $smtpusermail, $smtpsender , $mailsubject, $mailbody, $mailtype,'','',$mailattachment);//发送邮件
 */
class Smtp {
	/**
	 * smtp服务器，例如 smtp.163.com
	 * @var string
	 */
	var $smtp_host;

	/**
	 * smtp服务器端口，默认为25
	 * @var string
	 */
	var $smtp_port = 25;

	/**
	 * smtp服务器是否需要认证
	 * @var bool
	 */
	var $auth = TRUE;

	/**
	 * 邮件帐号，例如 phpcms@163.com
	 * @var string
	 */
	var $user;

	/**
	 * 邮件密码
	 * @var string
	 */
	var $pass;

	/**
	 * 是否显示调试信息
	 * @var bool
	 */
	var $debug = FALSE;

	/**
	 * 超时时间
	 * @access private
	 */
	var $time_out;
	/**
	 * 主机名
	 * @access private
	 */
	var $host_name;
	/**
	 * 日志文件
	 * @access private
	 */
	var $log_file;
	/**
	 *
	 * @access private
	 */
	var $sock;

	/**
	 * 构造函数，连接smtp服务器
	 * @param string $smtp_host smtp服务器
	 * @param int    $smtp_port smtp服务器端口
	 * @param bool   $auth smtp服务器是否需要认证
	 * @param string $user smtp服务器邮件帐号
	 * @param string $pass smtp服务器邮件密码
	 */
	public function __construct($smtp_host, $smtp_port = 25, $auth = TRUE, $user = '', $pass = '') {
		$this->debug = FALSE;
		$this->smtp_port = trim ( $smtp_port );
		$this->smtp_host = trim ( $smtp_host );
		$this->time_out = 30; // is used in fsockopen()
		$this->auth = trim ( $auth ); // auth
		$this->user = trim ( $user );
		$this->pass = trim ( $pass );
		$this->host_name = "localhost"; // is used in HELO command
		$this->log_file = '';
		$this->sock = FALSE;
	}

	/**
	 * 发送邮件的主函数
	 * @param $to               收件人
	 * @param $mail_from        发件人
	 * @param string $subject   邮件主题
	 * @param string $body      邮件正文
	 * @param string $mailtype  邮件类型，可选项为 HTML 、TEXT
	 * @param string $cc        抄送邮件
	 * @param string $bcc       暗送邮件
	 * @param string $files     附件
	 * @return bool
	 */
	public function sendmail($to, $mail_from,$sender, $subject = '', $body = '', $mailtype = "HTML", $cc = '', $bcc = '', $files = '') {
		$to = trim ( $to );
		$body = preg_replace ( "/(^|(\r\n))(\.)/", "\\1.\\3", $body );
		$header = "MIME-Version:1.0\r\n";
		if (strtoupper ( $mailtype ) == "HTML") {
			$header .= "Content-Type:text/html;charset=utf-8\r\n";
		}
		$header .= "To: " . $to . "\r\n";
		if ($cc != '') {
			$header .= "Cc: " . $cc . "\r\n";
		}
		if (!empty($sender)){
			$sender=" ({$sender})";
		}
		$header .= "From: " . $this->get_mailfrom ( $mail_from, $mail_from ) .$sender ."\r\n";
		$header .= "Subject: " . $subject . "\r\n";
		// boundary
		$semi_rand = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
		// preparing attachments
		for($i=0,$j=count($files);$i<$j;$i++){
			if(is_file($files[$i])){
				$header .= "--{$mime_boundary}\n";
				$fp =    @fopen($files[$i],"rb");
				$data =    @fread($fp,filesize($files[$i]));
				@fclose($fp);
				$data = chunk_split(base64_encode($data));
				$header .= "Content-Type: application/octet-stream; name=\"".basename($files[$i])."\"\n" .
					"Content-Description: ".basename($files[$i])."\n" .
					"Content-Disposition: attachment;\n" . " filename=\"".basename($files[$i])."\"; size=".filesize($files[$i]).";\n" .
					"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
			}
		}

		$header .= "Date: " . date ( "r" ) . "\r\n";
		$header .= "X-Mailer:By Apache (PHP/" . phpversion () . ")\r\n";
		list ( $msec, $sec ) = explode ( ' ', microtime () );
		$header .= "Message-ID: <" . date ( 'YmdHis', $sec ) . "." . ($msec * 1000000) . "." . substr ( $mail_from, strpos ( $mail_from, '@' ) ) . ">\r\n";
		$TO = explode ( ',', $this->strip_comment ( $to ) );
		if ($cc != '') {
			$TO = array_merge ( $TO, explode ( ',', $this->strip_comment ( $cc ) ) );
		}
		if ($bcc != '') {
			$TO = array_merge ( $TO, explode ( ',', $this->strip_comment ( $bcc ) ) );
		}
		$sent = TRUE;
		foreach ( $TO as $rcpt_to ) {
			$rcpt_to = $this->get_address ( $rcpt_to );
			if (! $this->smtp_sockopen ( $rcpt_to )) {
				$this->log_write ( "Error: Cannot send email to " . $rcpt_to . "\n" );
				$sent = FALSE;
				continue;
			}
			if ($this->smtp_send ( $this->host_name, $mail_from, $rcpt_to, $header, $body )) {
				$this->log_write ( "E-mail has been sent to <" . $rcpt_to . ">\n" );
			} else {
				$this->log_write ( "Error: Cannot send email to <" . $rcpt_to . ">\n" );
				$sent = FALSE;
			}
			fclose ( $this->sock );
			$this->log_write ( "Disconnected from remote host\n" );
		}
		return $sent;
	}

	// 发送
	private function smtp_send($helo, $from, $to, $header, $body = '') {
		if (! $this->smtp_putcmd ( "HELO", $helo )) {
			return $this->smtp_error ( "sending HELO command" );
		}
		// uth
		if ($this->auth) {
			if (! $this->smtp_putcmd ( "AUTH LOGIN", base64_encode ( $this->user ) )) {
				return $this->smtp_error ( "sending HELO command" );
			}
			if (! $this->smtp_putcmd ( '', base64_encode ( $this->pass ) )) {
				return $this->smtp_error ( "sending HELO command" );
			}
		}
		if (! $this->smtp_putcmd ( "MAIL", "FROM:<" . $from . ">" )) {
			return $this->smtp_error ( "sending MAIL FROM command" );
		}
		if (! $this->smtp_putcmd ( "RCPT", "TO:<" . $to . ">" )) {
			return $this->smtp_error ( "sending RCPT TO command" );
		}
		if (! $this->smtp_putcmd ( "DATA" )) {
			return $this->smtp_error ( "sending DATA command" );
		}
		if (! $this->smtp_message ( $header, $body )) {
			return $this->smtp_error ( "sending message" );
		}
		if (! $this->smtp_eom ()) {
			return $this->smtp_error ( "sending <CR><LF>.<CR><LF> [EOM]" );
		}
		if (! $this->smtp_putcmd ( "QUIT" )) {
			return $this->smtp_error ( "sending QUIT command" );
		}
		return TRUE;
	}

	// 打开sock
	function smtp_sockopen($address) {
		if ($this->smtp_host == '') {
			return $this->smtp_sockopen_mx ( $address );
		} else {
			return $this->smtp_sockopen_relay ();
		}
	}

	// sock传送
	function smtp_sockopen_relay() {
		$this->log_write ( "Trying to " . $this->smtp_host . ":" . $this->smtp_port . "\n" );
		$this->sock = @fsockopen ( $this->smtp_host, $this->smtp_port, $errno, $errstr, $this->time_out );
		if (! ($this->sock && $this->smtp_ok ())) {
			$this->log_write ( "Error: Cannot connenct to relay host " . $this->smtp_host . "\n" );
			$this->log_write ( "Error: " . $errstr . " (" . $errno . ")\n" );
			return FALSE;
		}
		$this->log_write ( "Connected to relay host " . $this->smtp_host . "\n" );
		return TRUE;
	}

	private function smtp_sockopen_mx($address) {
		$domain = ereg_replace ( "^.+@([^@]+)$", "\1", $address );
		if (! @getmxrr ( $domain, $MXHOSTS )) {
			$this->log_write ( "Error: Cannot resolve MX \"" . $domain . "\"\n" );
			return FALSE;
		}
		foreach ( $MXHOSTS as $host ) {
			$this->log_write ( "Trying to " . $host . ":" . $this->smtp_port . "\n" );
			$this->sock = @fsockopen ( $host, $this->smtp_port, $errno, $errstr, $this->time_out );
			if (! ($this->sock && $this->smtp_ok ())) {
				$this->log_write ( "Warning: Cannot connect to mx host " . $host . "\n" );
				$this->log_write ( "Error: " . $errstr . " (" . $errno . ")\n" );
				continue;
			}
			$this->log_write ( "Connected to mx host " . $host . "\n" );
			return TRUE;
		}
		$this->log_write ( "Error: Cannot connect to any mx hosts (" . implode ( ", ", $MXHOSTS ) . ")\n" );
		return FALSE;
	}

	private function smtp_message($header, $body) {
		fputs ( $this->sock, $header . "\r\n" . $body );
		$this->smtp_debug ( "> " . str_replace ( "\r\n", "\n" . "> ", $header . "\n> " . $body . "\n> " ) );
		return TRUE;
	}

	private  function smtp_eom() {
		fputs ( $this->sock, "\r\n.\r\n" );
		$this->smtp_debug ( ". [EOM]\n" );
		return $this->smtp_ok ();
	}

	private function smtp_ok() {
		$response = str_replace ( "\r\n", '', fgets ( $this->sock, 512 ) );
		$this->smtp_debug ( $response . "\n" );
		if (! preg_match("/^[23]/", $response )) {
			fputs ( $this->sock, "QUIT\r\n" );
			fgets ( $this->sock, 512 );
			$this->log_write ( "Error: Remote host returned \"" . $response . "\"\n" );
			return FALSE;
		}
		return TRUE;
	}

	private function smtp_putcmd($cmd, $arg = '') {
		if ($arg != '') {
			if ($cmd == '')
				$cmd = $arg;
			else
				$cmd = $cmd . " " . $arg;
		}
		fputs ( $this->sock, $cmd . "\r\n" );
		$this->smtp_debug ( "> " . $cmd . "\n" );
		return $this->smtp_ok ();
	}

	private function smtp_error($string) {
		$this->log_write ( "Error: Error occurred while " . $string . ".\n" );
		return FALSE;
	}

	private function log_write($message) {
		$this->smtp_debug ( $message );
		if ($this->log_file == '') {
			return TRUE;
		}
		$message = date ( "M d H:i:s " ) . get_current_user () . "[" . getmypid () . "]: " . $message;
		if (! @file_exists ( $this->log_file ) || ! ($fp = @fopen ( $this->log_file, "a" ))) {
			$this->smtp_debug ( "Warning: Cannot open log file \"" . $this->log_file . "\"\n" );
			return FALSE;
		}
		flock ( $fp, LOCK_EX );
		fputs ( $fp, $message );
		fclose ( $fp );
		return TRUE;
	}

	private function strip_comment($address) {
		$comment = "/\([^()]*\)/";
		while ( preg_match( $comment, $address ) ) {
			$address = preg_replace ( $comment, '', $address );
		}
		return $address;
	}

	private function get_address($address) {
		return trim ( preg_replace ( "/(.*[<])?([^<>]+)[>]?/i", "$2", $address ) );
	}

	private function get_mailfrom($address, $mail_from) {
		return strpos ( $address, '@' ) ? trim ( preg_replace ( "/^([^<]*?)<([^>]+)>$/i", "$1<" . $mail_from . ">", $address ) ) : $address . "<$mail_from>";
	}

	private function smtp_debug($message) {
		if ($this->debug) {
			Debug::R($message);
			return $message;
		}
	}
}
