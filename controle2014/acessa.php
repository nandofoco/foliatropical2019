<?

define("NOCHECK","true");

//Incluir funções básicas
include("include/includes.php");


if ($_GET['c']=='logout') {

	unsetsessao();
	?>
	<script type="text/javascript">location.href='<? echo SITE; ?>';</script>
	<?
	exit();
}

$continue = $_GET['cc'];

//TRATANTO AS VARIAVEIS DO LOGON
$login = format($_POST['login']);
$senha = md5(format($_POST['senha']));

//BUSCANDO DADOS DO USUARIO
$sql_logon = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE US_LOGIN='$login' AND US_SENHA='$senha' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);

$logon = sqlsrv_num_rows($sql_logon);

if ($logon==0) {

	$user_ip = $_SERVER["REMOTE_ADDR"];
	$user_host = gethostbyaddr($user_ip); //pego o host
	
	?>
	<script type="text/javascript">location.href='<? echo SITE; ?>?erro=true';</script>
	<?
	
exit();
}

if ($logon==1) {

	$result = sqlsrv_fetch_array($sql_logon);

	//Setamos as variáveis de sessão
	$_SESSION['us-cod'] = $result['US_COD'];
	$_SESSION['us-login'] = $result['US_LOGIN'];
	$_SESSION['us-senha'] = $result['US_SENHA'];
	$_SESSION['us-nome'] = $result['US_NOME'];
	$_SESSION['us-email'] = $result['US_EMAIL'];
	$_SESSION['us-filial'] = $result['US_FILIAL'];
	$_SESSION['us-grupo'] = $result['US_GRUPO'];
	$_SESSION['us-parceiro'] = $result['US_PARCEIRO'];
	
	$user_ip = $_SERVER["REMOTE_ADDR"];
	$user_host = gethostbyaddr($user_ip); //pego o host

	//Codificar dados para usar no cookie
	include ('include/focoencrypt.php');

	$focoenc = new FocoEncrypt;
	$cookie_valor = $focoenc->criptografar($_SESSION['us-login'], $_SESSION['us-senha']);

	setcookie('ftrop', $cookie_valor, time()+3600, '/');

	unset($_SESSION['us-erro']);

	// $link = is_numeric($result['US_PARCEIRO']) || ($result['US_GRUPO'] == 'VIN') ? 'compras/novo/' : 'relatorios/';
	switch (true) {
		case (is_numeric($result['US_PARCEIRO']) || ($result['US_GRUPO'] == 'VIN')):
			$link = 'compras/novo/';
		break;

		case ($result['US_GRUPO'] == 'ATE'):
			$link = 'ingressos/expedicao/';
		break;
		
		default:
			$link = 'relatorios/';
		break;
	}

	?>
	<script type="text/javascript">
		location.href='<? echo SITE.$link; ?>';
		// location.href='<? echo SITE; ?>compras/novo/foliatropical/';
	</script>
	<?

}

//Fechar conexoes
include("conn/close.php");

?>