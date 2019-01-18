<?

session_start();
ob_start();

global $conexao, $conexao_params, $conexao_options;

if (basename($_SERVER["PHP_SELF"]) == "conn.php") {
    die("Este arquivo não pode ser acessado diretamente.");
} 
if ($_SERVER['SERVER_NAME'] == "server" || $_SERVER['SERVER_NAME'] == "localhost"){

	$connectionInfo = array( "Database"=>"foliatropical", "UID"=>"sa", "PWD"=>"sa");
	$conexao = sqlsrv_connect("server\sqlexpress", $connectionInfo);

	$s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 's' : '';
	
	define ("BASE","/Users/FOCO11/Sites/foliatropical/novo/");
	define ("SITE", "http".$s."://localhost/~foco11/foliatropical/compras/");
	define ("RAIZ", "http".$s."://localhost/~foco11/foliatropical/");
			
} else {

	$connectionInfo = array( "Database"=>"foliatropical2014", "UID"=>"sa", "PWD"=>"Rtuu4476yyh");
	$conexao = sqlsrv_connect("200.152.124.108", $connectionInfo);

	$s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 's' : '';
	
	define ("BASE", "C:\\web\\ingressos.foliatropical.com.br\\public_html\\compras\\");
	define ("SITE", "http".$s."://ingressos.foliatropical.com.br/compras/");
	define ("RAIZ", "http".$s."://ingressos.foliatropical.com.br/");
	
}
//producao

//comente a linha caso queira testar - os testes em ambiente de produção são cobrados
if(!defined("CLEARSALE_ENTITY_CODE")) define ("CLEARSALE_ENTITY_CODE","D5DBB480-A7DE-4131-BDCF-AA14DF51A0A3");

//testes
// if(!defined("CLEARSALE_ENTITY_CODE")) define ("CLEARSALE_ENTITY_CODE","88E12F32-9350-4F62-970B-4B6574CA375C");

// $db = mssql_select_db($db_name);
if ($conexao == false) print('Erro ao conectar ao Servidor de Banco de Dados');

$conexao_params = array();
$conexao_options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

?>