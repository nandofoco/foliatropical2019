<?

define("NOCHECK","true");

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");


if ($_GET['c']=='logout') {

	unsetsessao();
	?>
	<!-- <script type="text/javascript">location.href='<? echo SITE.$link_lang; ?>';</script> -->
	<script type="text/javascript">location.href='https://ingressos.foliatropical.com.br/compras/<? echo $link_lang; ?>';</script>
	<?
	exit();
}

$continue = $_GET['cc'];

//TRATANTO AS VARIAVEIS DO LOGON
$login = format($_POST['email']);
$senha = md5(format($_POST['senha']));

$imprimir = (int) $_POST['imprimir'];
$compre = (bool) $_POST['compre'];
$sessionID = format($_POST['SessionID']);

if($compre) $retorno = 'compre-adicionais/';
elseif ($imprimir > 0) $retorno = 'imprimir/'.$imprimir.'/';

//BUSCANDO DADOS DO USUARIO
$sql_logon = sqlsrv_query($conexao_sankhya, "SELECT * FROM TGFPAR WHERE EMAIL='$login' AND AD_SENHA='$senha'", $conexao_params, $conexao_options);
$logon = sqlsrv_num_rows($sql_logon);

if ($logon==0) {

	$user_ip = $_SERVER["REMOTE_ADDR"];
	$user_host = gethostbyaddr($user_ip); //pego o host
	
	?>
	<script type="text/javascript">
		(function (a, b, c, d, e, f, g) {
		 a['CsdmObject'] = e; a[e] = a[e] || function () {
		 (a[e].q = a[e].q || []).push(arguments)
		 }, a[e].l = 1 * new Date(); f = b.createElement(c),
		 g = b.getElementsByTagName(c)[0]; f.async = 1; f.src = d; g.parentNode.insertBefore(f, g)
		})(window, document, 'script', '//device.clearsale.com.br/m/cs.js', 'csdm');
		csdm('app', '<?php echo CLEARSALE_APP; ?>');
		csdm('mode', 'manual');
		csdm('send', 'login-fail');
		alert('Os dados parecem estar incorretos, tente novamente');
		location.href='<? echo SITE.$link_lang; ?>login/<? echo $retorno; ?>';
	</script>
	<?

	//fechar conexao com o banco
	include("conn/close.php");
	include("conn/close-sankhya.php");
	
	exit();
}

if ($logon==1) {

	$result = sqlsrv_fetch_array($sql_logon);

	//Setamos as variáveis de sessão
	$_SESSION['usuario-cod'] = trim($result['CODPARC']);
	$_SESSION['usuario-login'] = trim($result['EMAIL']);
	$_SESSION['usuario-senha'] = trim($result['AD_SENHA']);
	$_SESSION['usuario-nome'] = trim($result['NOMEPARC']);
	$_SESSION['usuario-razao-social'] = trim($result['RAZAOSOCIAL']);
	$_SESSION['usuario-tipo-pessoa'] = trim($result['TIPPESSOA']);
	$_SESSION['usuario-email'] = trim($result['EMAIL']);
	$_SESSION['usuario-telefone'] = trim($result['TELEFONE']);
	$_SESSION['SessionID'] = format($sessionID);
	
	$user_ip = $_SERVER["REMOTE_ADDR"];
	$user_host = gethostbyaddr($user_ip); //pego o host

	//Codificar dados para usar no cookie
	// include ('include/focoencrypt.php');

	$cliente_cpf_cnpj = trim($result['CGC_CPF']);
	$cliente_passaporte = trim($result['AD_IDENTIFICACAO']);
	
	$session_language = (!empty($cliente_passaporte)) ? 'US' : 'BR';
	setcookie('ftropsitelang', $session_language, time()+(3600*24*30*12*5), '/');
	
	$focoenc = new FocoEncrypt;
	$cookie_valor = $focoenc->criptografar($_SESSION['usuario-login'], $_SESSION['usuario-senha']);

	setcookie('ftropsite', $cookie_valor, time()+3600, '/');

	unset($_SESSION['usuario-erro']);

	$link = 'ingressos/';

	if($compre) $link = 'ingressos/adicionais/';
	elseif ($imprimir > 0) $link = 'minhas-compras/imprimir/'.$imprimir.'/';
	

	?>
	<script type="text/javascript">
		location.href='<? echo SITE.$link_lang.$link; ?>';
	</script>
	<?

}

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-sankhya.php");

?>