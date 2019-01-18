<?
function checklogado(){
	
	if(!$conexao_sankhya) include(BASE.'conn/conn-sankhya.php');

	$conexao_params = array();
	$conexao_options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	
	//Codificar dados para usar no cookie
	if(!defined('FOCOENCRYPT')) include_once(BASE.'inc/focoencrypt.php');

	//Objeto de criptografia
	$focoenc = new FocoEncrypt;

	//Checar cookie
	if(!empty($_COOKIE['ftropsite'])) {
		
		$cookie_set_valor = $_COOKIE['ftropsite'];

		//Pegar valores do cookie
		$focoenc->descriptografar($cookie_set_valor);
		$cookie_usuario = $focoenc->usuario;
		$cookie_senha = $focoenc->senha;

		$sql_logon_cookie = sqlsrv_query($conexao_sankhya, "SELECT * FROM TGFPAR WHERE EMAIL='$cookie_usuario' AND AD_SENHA='$cookie_senha'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_logon_cookie) > 0 && !empty($_SESSION['SessionID'])) {

			$logon_cookie = sqlsrv_fetch_array($sql_logon_cookie);

			//Setamos as variáveis de sessão
			$_SESSION['usuario-cod'] = trim($logon_cookie['CODPARC']);
			$_SESSION['usuario-login'] = trim($logon_cookie['EMAIL']);
			$_SESSION['usuario-senha'] = trim($logon_cookie['AD_SENHA']);
			$_SESSION['usuario-nome'] = trim($logon_cookie['NOMEPARC']);
			$_SESSION['usuario-razao-social'] = trim($logon_cookie['RAZAOSOCIAL']);
			$_SESSION['usuario-tipo-pessoa'] = trim($logon_cookie['TIPPESSOA']);
			$_SESSION['usuario-email'] = trim($logon_cookie['EMAIL']);
			$_SESSION['usuario-telefone'] = trim($logon_cookie['TELEFONE']);
			
			//Criptografar dados para usar no cookie
			$cookie_valor = $focoenc->criptografar($_SESSION['usuario-login'], $_SESSION['usuario-senha']);

			setcookie('ftropsite', $cookie_valor, time()+(3600*24*30*12*5), '/');

			// Conectar-se ao banco
			if(!$conexao) include(BASE."conn/conn-mssql.php");

			$log_carrinho;

			// Se houverem itens no carrinho
			if(count($_SESSION['compra-site']) > 0) {

				/*foreach ($_SESSION['compra-site'] as $key => $carrinho) {

					$sql_ingressos = sqlsrv_query($conexao, "SELECT
						v.*,
						t.TI_NOME,
						d.ED_NOME,
						s.ES_NOME

						FROM vendas v, tipos t, eventos_dias d, eventos_setores s 

						WHERE v.VE_COD='".$carrinho['item']."'
						AND v.VE_BLOCK=0
						AND v.D_E_L_E_T_=0
						AND d.ED_COD=v.VE_DIA
						AND t.TI_COD=v.VE_TIPO
						AND s.ES_COD=v.VE_SETOR
						AND d.D_E_L_E_T_=0
						AND t.D_E_L_E_T_=0
						AND s.D_E_L_E_T_=0", $conexao_params, $conexao_options);

					if( ($errors = sqlsrv_errors() ) != null) {
				        foreach( $errors as $error ) {
				            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
				            echo "code: ".$error[ 'code']."<br />";
				            echo "message: ".$error[ 'message']."<br />";
				        }
				    }
					
					if(sqlsrv_num_rows($sql_ingressos) !== false) {
					
						$ingressos = sqlsrv_fetch_array($sql_ingressos);
						$ingressos_cod = $ingressos['VE_COD'];
						$ingressos_setor = $ingressos['ES_NOME'];
						$ingressos_dia = $ingressos['ED_NOME'];
						$ingressos_tipo = ($ingressos['TI_NOME'] == 'Lounge') ? 'Folia Tropical' : $ingressos['TI_NOME'];
						$carrinho['qtde'];
						
						$ingressos_fila = $ingressos['VE_FILA'];
						$ingressos_vaga = $ingressos['VE_VAGAS'];
						$ingressos_estoque = (int) $ingressos['TOTAL'];
						$ingressos_tipo_especifico = $ingressos['VE_TIPO_ESPECIFICO'];
						//Calculo de estoque
						
						$ingresso_texto = $ingressos_tipo;
						if(!empty($ingressos_fila)) { $ingresso_texto .= " ".$ingressos_fila; }
						if(!empty($ingressos_tipo_especifico)) { $ingresso_texto .= " ".$ingressos_tipo_especifico; }
						if(!empty($ingressos_vaga) && ($ingressos_tipo_especifico == 'fechado')) { $ingresso_texto .= " (".$ingressos_vaga." vagas)"; }

						$ingresso_texto .= ' - Setor '.$ingressos_setor;
						$ingresso_texto .= ' - '.$ingressos_dia.' dia';

						$log_carrinho .= $ingresso_texto;
						$log_carrinho .= "\n";
					
					}
				}*/
			}


			// Página acessada
			$log_pagina = $_SERVER['REQUEST_URI'];
			$log_usuario = $_SESSION['usuario-cod'];

			$user_ip = $_SERVER["REMOTE_ADDR"];
			$user_host = gethostbyaddr($user_ip); //pego o host

			//Verificar se não há dados repetidos
			$sql_log = sqlsrv_query($conexao, "INSERT INTO usuarios_log (
				LOG_USUARIO,
				LOG_PAGINA,
				LOG_CARRINHO,
				LOG_DATA,
				LOG_IP,
				LOG_HOST
			) VALUES (
				'$log_usuario',
				'$log_pagina',
				'$log_carrinho',
				GETDATE(),
				'$user_ip',
				'$user_host'

			)", $conexao_params, $conexao_options);
		
			return true;

		} else {
			unsetsessao();
			return false;
		}

	
	}

}

?>