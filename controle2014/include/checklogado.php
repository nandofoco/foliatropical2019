<?

function checklogado(){

	global $conexao, $conexao_params, $conexao_options;

	//Codificar dados para usar no cookie
	$pageinclude = __DIR__.'/focoencrypt.php';
	include ($pageinclude);		
	

	//Objeto de criptografia
	$focoenc = new FocoEncrypt;

	//Checar cookie
	if(!empty($_COOKIE['ftrop'])) {
		
		$cookie_set_valor = $_COOKIE['ftrop'];

		//Pegar valores do cookie
		$focoenc->descriptografar($cookie_set_valor);
		$cookie_usuario = $focoenc->usuario;
		$cookie_senha = $focoenc->senha;

		$sql_logon_cookie = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE US_LOGIN='".$cookie_usuario."' AND US_SENHA='".$cookie_senha."' AND D_E_L_E_T_=0 ", $conexao_params, $conexao_options);
		
		if(sqlsrv_num_rows($sql_logon_cookie) > 0) {

			$logon_cookie = sqlsrv_fetch_array($sql_logon_cookie);
			$_SESSION['us-cod'] = $logon_cookie['US_COD'];
			$_SESSION['us-senha'] = $logon_cookie['US_SENHA'];

		} else {
			unsetsessao();
			return false;
		}

	
	}
	
	//VERIFICA SE O USUARIO ESTA LOGADO
	if (empty($_SESSION['us-cod']) || empty($_SESSION['us-senha']) || ($_SESSION['us-cod']==0)) {

		unsetsessao();
		return false;

	} else {

		$sql_logon = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE US_COD='".$_SESSION['us-cod']."' AND US_SENHA='".$_SESSION['us-senha']."' AND D_E_L_E_T_=0 ", $conexao_params, $conexao_options);
		
		if(sqlsrv_num_rows($sql_logon) == 1) {

			$logon = sqlsrv_fetch_array($sql_logon);

			$_SESSION['us-cod'] = $logon['US_COD'];
			$_SESSION['us-login'] = $logon['US_LOGIN'];
			$_SESSION['us-senha'] = $logon['US_SENHA'];
			$_SESSION['us-nome'] = $logon['US_NOME'];
			$_SESSION['us-email'] = $logon['US_EMAIL'];
			$_SESSION['us-filial'] = $logon['US_FILIAL'];
			$_SESSION['us-grupo'] = $logon['US_GRUPO'];
			$_SESSION['us-parceiro'] = $logon['US_PARCEIRO'];
			
			
			$user_ip = $_SERVER["REMOTE_ADDR"];
			$user_host = gethostbyaddr($user_ip); //pego o host

			//Criptografar dados para usar no cookie
			$cookie_valor = $focoenc->criptografar($_SESSION['us-login'], $_SESSION['us-senha']);

			define('USLOGADO', 'true');
			
			setcookie('ftrop', $cookie_valor, time()+3600, '/');
			return true;

		} else {
			unsetsessao();
			return false;
		}
		
	}
}

function unsetsessao(){

	setcookie('ftrop', "", time()-3600, "/");
	
	unset(
		$_SESSION['us-cod'],
		$_SESSION['us-login'],
		$_SESSION['us-senha'],
		$_SESSION['us-nome'],
		$_SESSION['us-email'],
		$_SESSION['us-filial'],
		$_SESSION['us-grupo'],
		$_SESSION['us-parceiro']
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