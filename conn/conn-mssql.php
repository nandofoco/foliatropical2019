<?

global $conexao, $conexao_params, $conexao_options;

if (basename($_SERVER["PHP_SELF"]) == "conn-mssql.php") {
    die("Este arquivo não pode ser acessado diretamente.");
} 
if ($_SERVER['SERVER_NAME'] == "localhost"){

	$connectionInfo = array( "Database"=>"foliatropical", "UID"=>"sa", "PWD"=>"sa");
	$conexao = sqlsrv_connect("server\sqlexpress", $connectionInfo);
		
} else if ($_SERVER['SERVER_NAME'] == "server"){

	$connectionInfo = array( "Database"=>"foliatropical", "UID"=>"sa", "PWD"=>"sa");
	$conexao = sqlsrv_connect("server\sqlexpress", $connectionInfo);

	// $connectionInfo = array( "Database"=>"foliatropical2014", "UID"=>"sa", "PWD"=>"Rtuu4476yyh");
	// $conexao = sqlsrv_connect("200.152.124.108", $connectionInfo);
	
	#define ("BASE","\\\\server\\web\\foliatropical\\controle\\");
	#define ("SITE", "http://server/foliatropical/controle2014/");
		
} else {
	
	#$connectionInfo = array( "Database"=>"foliatropical2014", "UID"=>"sa", "PWD"=>"dedland33#e");  // CLOUD ANTIGO
	#$conexao = sqlsrv_connect("200.152.124.108", $connectionInfo);                                 // CLOUD ANTIGO
	$connectionInfo = array( "Database"=>"foliatropical2014", "UID"=>"sa", "PWD"=>"Rtuu4476yyh");
	$conexao = sqlsrv_connect("200.152.124.108", $connectionInfo);
	
	#define ("BASE","C:\\web\\foliatropical.com.br\\public_html\\controle\\");
	#define ("SITE", "http://www.foliatropical.com.br/controle2014/");
	
}

// $db = mssql_select_db($db_name);
if ($conexao == false) print('Erro ao conectar ao Servidor de Banco de Dados 3');
// if ($db == false) print('Erro ao selecionar Banco de Dados');

$conexao_params = array();
$conexao_options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

?>