<?

if ($_SERVER['SERVER_NAME'] != "server" && $_SERVER['SERVER_NAME'] != "localhost"){
	$server_name = $_SERVER['SERVER_NAME'];
	$server_uri = $_SERVER ['REQUEST_URI'];

	$s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 's' : '';

	if(!is_numeric(strpos($server_name, 'www.'))) {
		#header("Location: http".$s."://www.$server_name$server_uri");
	};
}

session_start();
ob_start();


if(!empty($_GET['lang'])) {
	switch($_GET['lang']){
		case "us": $lang = "US"; break;		
		case "br": default: $lang = "BR"; break;
	}

	$_SESSION['language'] = $lang;
	// setcookie('ftropsitelang', $lang, time()+(3600*24*30*12*5), '/');
}

?>