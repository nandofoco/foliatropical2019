<?

global $conexao_sankhya, $conexao_params, $conexao_options;

if (basename($_SERVER["PHP_SELF"]) == "conn-sankhya.php") {
    die("Este arquivo não pode ser acessado diretamente.");
} 


if ($_SERVER['SERVER_NAME'] == "localhost"){

	$connectionSankhyaInfo = array( "Database"=>"parceiros", "UID"=>"sa", "PWD"=>"Rtuu4476yyh");
	$conexao_sankhya = sqlsrv_connect("200.152.124.108", $connectionSankhyaInfo);	
	
} else if ($_SERVER['SERVER_NAME'] == "server"){

	$connectionSankhyaInfo = array("Database"=>"parceiros", "UID"=>"sa", "PWD"=>"sa");
	$conexao_sankhya = sqlsrv_connect("server\sqlexpress", $connectionSankhyaInfo);
	
} else {

	$connectionSankhyaInfo = array( "Database"=>"parceiros", "UID"=>"sa", "PWD"=>"Rtuu4476yyh");
	$conexao_sankhya = sqlsrv_connect("200.152.124.108", $connectionSankhyaInfo);	
	
}

// $db = mssql_select_db($db_name);
if ($conexao_sankhya == false) print('Erro ao conectar ao Servidor de Banco de Dados 2');

$conexao_params = array();
$conexao_options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

?>