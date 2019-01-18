<?

function checklogado(){

	global $conexao, $conexao_params, $conexao_options;

	//Codificar dados para usar no cookie
	$pageinclude = __DIR__.'/focoencrypt.php';
	include ($pageinclude);		
	

	//Objeto de criptografia
	$focoenc = new FocoEncrypt;

	//Checar cookie
	if(!empty($_COOKIE['ftrop-parc'])) {
		
		$cookie_set_valor = $_COOKIE['ftrop-parc'];

		//Pegar valores do cookie
		$focoenc->descriptografar($cookie_set_valor);
		$cookie_usuario = $focoenc->usuario;
		$cookie_senha = $focoenc->senha;

		$sql_logon_cookie = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE US_LOGIN='".$cookie_usuario."' AND US_SENHA='".$cookie_senha."' AND D_E_L_E_T_=0 ", $conexao_params, $conexao_options);
		
		if(sqlsrv_num_rows($sql_logon_cookie) > 0) {

			$logon_cookie = sqlsrv_fetch_array($sql_logon_cookie);
			$_SESSION['us-par-cod'] = $logon_cookie['US_COD'];
			$_SESSION['us-par-senha'] = $logon_cookie['US_SENHA'];

		} else {
			unsetsessao();
			return false;
		}

	
	}
	
	//VERIFICA SE O USUARIO ESTA LOGADO
	//if (empty($_SESSION['us-par-cod']) || empty($_SESSION['us-par-senha']) || ($_SESSION['us-par-cod']==0)) {
	if (empty($_SESSION['us-par-cod']) || empty($_SESSION['us-par-senha'])) {

		unsetsessao();
		return false;

	} else {

		$sql_logon = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE US_COD='".$_SESSION['us-par-cod']."' AND US_SENHA='".$_SESSION['us-par-senha']."' AND D_E_L_E_T_=0 ", $conexao_params, $conexao_options);
		
		if(sqlsrv_num_rows($sql_logon) == 1) {

			$logon = sqlsrv_fetch_array($sql_logon);

			$_SESSION['us-par-cod'] = $logon['US_COD'];
			$_SESSION['us-par-login'] = $logon['US_LOGIN'];
			$_SESSION['us-par-senha'] = $logon['US_SENHA'];
			$_SESSION['us-par-nome'] = $logon['US_NOME'];
			$_SESSION['us-par-email'] = $logon['US_EMAIL'];
			$_SESSION['us-par-filial'] = $logon['US_FILIAL'];
			$_SESSION['us-par-grupo'] = $logon['US_GRUPO'];
			$_SESSION['us-par-parceiro'] = $logon['US_PARCEIRO'];
			
			
			$user_ip = $_SERVER["REMOTE_ADDR"];
			$user_host = gethostbyaddr($user_ip); //pego o host

			//Criptografar dados para usar no cookie
			$cookie_valor = $focoenc->criptografar($_SESSION['us-par-login'], $_SESSION['us-par-senha']);

			define('USLOGADO', 'true');
			
			setcookie('ftrop-parc', $cookie_valor, time()+3600, '/');
			return true;

		} else {
			unsetsessao();
			return false;
		}
		
	}
}

function unsetsessao(){

	setcookie('ftrop-parc', "", time()-3600, "/");
	
	unset(
		$_SESSION['us-par-cod'],
		$_SESSION['us-par-login'],
		$_SESSION['us-par-senha'],
		$_SESSION['us-par-nome'],
		$_SESSION['us-par-email'],
		$_SESSION['us-par-filial'],
		$_SESSION['us-par-grupo'],
		$_SESSION['us-par-parceiro']
	);
	session_unset(); 
	session_destroy();

}

//-----------------------------------------------------------------------------//

if(!defined("NOCHECK")) {

	if(!checklogado()){

		?>
		<script type="text/javascript">
		    location.href='<? echo SITE; ?>'; 
		</script>
		<?
		exit();

	}
}

?>