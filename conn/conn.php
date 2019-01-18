<?

/*if(!$_GET['testefoco']) {
	if(($_SERVER["REMOTE_ADDR"] == '65.97.50.10') || ($_SERVER["SERVER_NAME"] == 'foliatropical-px.rtrk.com.br')) {
		exit();
	}	
}*/

if (basename($_SERVER["PHP_SELF"]) == "conn.php") {
        die("Este arquivo não pode ser acessado diretamente.");
}

if(!defined("EVENTO")) define ("EVENTO", 1);

if ($_SERVER['SERVER_NAME'] == "localhost"){
	
	$db_name = "foliatropical";
	$conn = mysql_connect("www.foliatropical.com.br","foliatropical","qE7xZKCIDjY0");

	$s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 's' : '';
	
	if(!defined("BASE")) define ("BASE","/Users/FOCO11/Sites/foliatropical/");
	if(!defined("SITE")) define ("SITE", "http".$s."://localhost/~foco11/foliatropical/");
		
} elseif ($_SERVER['SERVER_NAME'] == "server" || $_SERVER['SERVER_NAME'] == "192.168.1.120"){
	
	$db_name = "foliatropical";
	$conn = mysql_connect("192.168.1.120", "foco", "foco");
	
	if(!defined("BASE")) define ("BASE","\\\\server\\web\\foliatropical\\");
	if(!defined("SITE")) define ("SITE", "http://server/foliatropical/");
		
} else {
	
	// $db_name = "foliatropical";
 	// $conexao = mysql_connect("localhost","foliatropical","fol+sql@foco");
	
	// define ("BASE","/home/httpd/vhosts/foliatropical.com.br/");
	// define ("SITE", "http://www.foliatropical.com.br/");

	$s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 's' : '';
	// $s = (!empty($_SERVER['HTTPS'])) ? 's' : '';

	$db_name = "foliatropical";
    $conn = mysql_connect("localhost","foliatropical","qE7xZKCIDjY0");

	if(!defined("BASE")) define ("BASE","C:\\web\\ingressos.foliatropical.com.br\\public_html\\");
	if(!defined("SITE")) define ("SITE", "http".$s."://ingressos.foliatropical.com.br/");
	
}

//producao
if(!defined("CLEARSALE_APP")) define ("CLEARSALE_APP","ae89f55a8f");
//testes
// if(!defined("CLEARSALE_APP")) define ("CLEARSALE_APP","ae89f55a8f");


//Captcha
$publickey = "6LeD1tQSAAAAAGGigAZ9SC-b20LY2fxl1k9nXMx9";
$privatekey = "6LeD1tQSAAAAANMMXYCgTwMyHA3tVegtZkRw2Bvj";

$db = mysql_select_db($db_name,$conn);
if ($conn == false) print('Erro ao conectar ao Servidor de Banco de Dados 1');
if ($db == false) print('Erro ao selecionar Banco de Dados');


// Forçar Codificação
mysql_query("SET character_set_results = 'latin1', character_set_client = 'latin1', character_set_connection = 'latin1', character_set_database = 'latin1', character_set_server = 'latin1'", $conn);

?>