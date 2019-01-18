<?

if(!defined("PGVALIDACAO")){

	header('Content-Type: text/html; charset=utf-8'); 
	
	//Conectamos no banco
	//include("../conn/conn.php");

}

//Funções de captcha
require_once('recaptchalib.php');

$captcha_validade = false;

# was there a reCAPTCHA response?
if ($_POST["recaptcha_response_field"]) {
	
	$resp = recaptcha_check_answer ($privatekey,
							$_SERVER["REMOTE_ADDR"],
							$_POST["recaptcha_challenge_field"],
							$_POST["recaptcha_response_field"]);
	
	if ($resp->is_valid) {
		$captcha_validade = 1;
	} else {
		$captcha_validade = 0;
	}
}

if(!defined("PGVALIDACAO")){
	echo $captcha_validade;
}

?>