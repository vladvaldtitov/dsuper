<?
class Restore{	
	function process($a,$get){		
		switch (array_shift($a)) {
			case 'get_user_password':
				return $this->get_user_password(json_decode(file_get_contents("php://input"),TRUE));
				 
				break;				
				case 'email_username':
				return $this->email_username(json_decode(file_get_contents("php://input"),TRUE));
					case 'email_password':
				return $this->email_password(json_decode(file_get_contents("php://input"),TRUE));
			
			default:
				
				break;
		}
	}
	
	function getValue($index,$db){		
		return $db->getField("SELECT val FROM extra WHERE ind = '$index' ");		
	}
	
	function get_user_password($ar){
		$email = $ar['email'];
		if(!$email) {
			return 'ERROR,No email';
		}
		$db =  new MyConnector(0);
		$sql='SELECT username,password FROM users WHERE email=?';
		$res = $db->queryA($sql, array($email));
		if($res && count($res)){
			$user = $res[username];
			$pass = $res[password];
			$user = $db->getField("SELECT value FROM extra WHERE index='$user'");
			$pass = $db->getField("SELECT value FROM extra WHERE index='$pass'");
			return 'RESULT,'.$user.','.$pass;			
		}
		return 'ERROR,no_user_with_email,'.$email;
	}
	
	
	function email_username($ar){
		$email = $ar['email'];
		if(!$email) {
			return 'ERROR,No email';
		}
		$db =  new MyConnector(0);
		$sql='SELECT username FROM users WHERE email=?';
		$res = $db->queryA($sql, array($email));
		if($res && count($res)){
			$res = $res[0];		
			$index = $res['username'];		
			$username = $this->getValue($index,$db);			
			if(!$username) return 'ERROR,no_value_for,'.$email;
			$to= $email;
			 $subject= 'username restore ';
			 $message ='Your username is: '.$username;
			 $headers = 'From: admin@front-desk.ca' . "\r\n" . 'Reply-To: admin@front-desk.ca' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
			mail($to, $subject, $message,$headers);			
			return 'RESULT,username_sent_to'.$email;		
		}
		return 'ERROR,no_user_with_email,'.$email;
	}
	
	function email_password($ar){
		$username = $ar['username'];
		if(!$username) {
			return 'ERROR,No email';
		}

		$usernameS = md5($username);
		$db =  new MyConnector(0);
		$sql='SELECT pass,email FROM users WHERE username=?';
		$res = $db->queryA($sql, array($usernameS));		
		if($res && count($res)){
			$res= $res[0];
			$pass = $res['pass'];
			$email = $res['email'];		
			$password = $this->getValue($pass,$db);//$db->getField("SELECT value FROM extra WHERE index='$pass'");
			if(!$password) return 'ERROR,no_value_for,'.$username;
			$to= $email;
			 $subject= 'Password restore for '.$username;
			 $message ='Your password is: '.$password;
			 $headers = 'From: admin@front-desk.ca' . "\r\n" . 'Reply-To: admin@front-desk.ca' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
				mail($to, $subject, $message,$headers);
			return 'RESULT,password_sent_to,'.$email;//'RESULT,'.$password.','.$email;
		}
		return 'ERROR,no_user_with_username,'.$username;
	}
}
