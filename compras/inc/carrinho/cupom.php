<?

//Banco de dados
include '../../conn/conn.php';
include '../../conn/conn-sankhya.php';
include BASE.'inc/funcoes.php';
include BASE.'inc/checklogado.php';
include BASE.'inc/language.php';
include BASE.'inc/checkwww.php';

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

//-----------------------------------------------------------------//

/* if(!$logado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE; ?>';
</script>
<?
	exit();
} */



$cod = (int) $_POST['cod'];
$cupom = format($_POST['cupom']);
$financeiro = (bool) $_POST['financeiro'];
$cliente = $_SESSION['usuario-cod'];
$v2 = (isset($_POST['paypal'])) ? 'paypal/' : 'v2/' ;

$mensagem = 'Cupom indisponível';
$sucesso = false;

if(!empty($cupom)) {
	//Definir se o cupom é de Parceiro ou de Usuário
	switch (true) {
		case strpos($cupom, 'FOLIA') === 0:

			$sql_parceiro = sqlsrv_query($conexao_sankhya, "SELECT * FROM TGFPAR WHERE CUPOM='$cupom' AND VENDEDOR='S'", $conexao_params, $conexao_options);

			if(sqlsrv_num_rows($sql_parceiro) > 0) {

				$parceiro = sqlsrv_fetch_array($sql_parceiro);
				$parceiro_cod = $parceiro['CODPARC'];
				$parceiro_nome = trim(utf8_encode($parceiro['NOMEPARC']));
				$parceiro_cupom = trim(utf8_encode($parceiro['CUPOM']));
				$parceiro_comissao = trim($parceiro['AD_COMISSAO']);
				$parceiro_desconto = trim($parceiro['DESCONTO']);

                // Estamos adicionando um cupom à sessão e não mais à compra
				// $sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_PARCEIRO='$parceiro_cod', LO_COMISSAO='$parceiro_comissao' WHERE LO_COD='$cod' AND LO_COMISSAO_RETIDA=0 AND LO_COMISSAO_PAGA=0", $conexao_params, $conexao_options);
		

				//Adicionando a comissão e o código do parceiro à variáveis de sessão
				//Será usado para inserir na tabela da loja, no e-compra.php
				$_SESSION['compra-cupom']['comissao'] = $parceiro_comissao;
				$_SESSION['compra-cupom']['parceiro_cod'] = $parceiro_cod;

				
				//Se houver desconto a ser aplicado, criamos um cupom e associamos a compra a ele
				if($parceiro_desconto > 0) {
					$caracteres_aceitos = 'abcdefghijklmnopqrstuvwxyz0123456789';
					$max = strlen($caracteres_aceitos)-1;
					$codigo_aleatorio = null;
					
					for($i=0; $i < 3; $i++) { $codigo_aleatorio .= $caracteres_aceitos{mt_rand(0, $max)}; }	
					
					$cupom_codigo = strtoupper($cupom.$codigo_aleatorio);
					$cupom_valor = $parceiro_desconto;
					$cupom_nome = trim($parceiro['NOMEPARC']).' - Parceria';
					$cupom_validade = date('Y-m-d', strtotime('+1day'));
					$cupom_tipo = 1;


					$sql_insert = sqlsrv_query($conexao, "INSERT INTO cupom (CP_NOME, CP_TIPO, CP_DATA_VALIDADE, CP_DESCONTO, CP_CUPOM) VALUES ('$cupom_nome','$cupom_tipo','$cupom_validade','$cupom_valor','$cupom_codigo')", $conexao_params, $conexao_options);
					$cupom_cod = getLastId();

					$_SESSION['compra-cupom']['usuario'] = $cliente;
					$_SESSION['compra-cupom']['cod'] = $cupom_cod;
					
					$mensagem = 'Você ganhou um desconto em sua compra.';
				} else {
					$mensagem = 'A comissão ao parceiro foi adicionada.';
				}
				
				$update = true;

			}
			
		break;

		default:

			$sql_cupom = sqlsrv_query($conexao, "SELECT * FROM cupom WHERE CP_CUPOM='$cupom' AND CP_UTILIZADO=0 AND CP_BLOCK=0 AND D_E_L_E_T_=0 AND CP_DATA_VALIDADE >= GETDATE()", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_cupom) > 0) {
				$dados_cupom = sqlsrv_fetch_array($sql_cupom);

				$cupom_cod = $dados_cupom['CP_COD'];
				
				$_SESSION['compra-cupom']['usuario'] = $cliente;
				$_SESSION['compra-cupom']['cod'] = $cupom_cod;

				$mensagem = 'Você ganhou um desconto em sua compra.';

			}

		break;

        /* 
        // Não é mais possível adicionar uma compra a um usuário através dessa função pois nesse momento a compra não existe
        case strpos($cupom, 'USUARIO') === 0:

			$codigo = (int) str_replace('USUARIO','', $cupom);
			if($codigo > 0) {

				$sql_usuarios = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE D_E_L_E_T_='0' AND US_COD='$codigo'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_usuarios) > 0) {

					$sql_update = sqlsrv_query($conexao, "UPDATE loja SET LO_VENDEDOR='$codigo' WHERE LO_COD='$cod' AND (LO_VENDEDOR IS NULL OR LO_VENDEDOR=0)", $conexao_params, $conexao_options);

					$update = true;
					$mensagem = 'Sua compra foi direcionada para o atendente.';
					
				}				
			}

		break; */		
		
	}

	$sucesso = true;
}    


//Get
$cupom = (int) $_GET['c'];
if(!empty($cupom)) {

	if($_SESSION['compra-cupom']['cod'] = $cupom) unset($_SESSION['compra-cupom']);
	?>
	<script type="text/javascript">
		location.href='<? echo SITE.'br/' ?>ingressos/';
	</script>
	<?

	//fechar conexao com o banco
	exit();	

}

//fechar conexao com o banco
include BASE."conn/close.php";
include BASE."conn/close-sankhya.php";

echo json_encode(array('sucesso' => $sucesso, 'mensagem' => $mensagem));

?>