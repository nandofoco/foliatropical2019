<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$cliente = (int) $_POST['cliente'];
$cupom = format($_POST['cupom']);
$financeiro = (bool) $_POST['financeiro'];
$v2 = (isset($_POST['v2'])) ? 'v2/' : '' ;
$paypal = (isset($_POST['paypal'])) ? 'paypal/' : '' ;

//-----------------------------------------------------------------------------//

$retorno = $financeiro ? 'financeiro/detalhes/' : 'compras/pagamento/'.$v2.$paypal;

if(!empty($cod) && !empty($cupom)) {

	//Verificar a existencia de cupom de desconto para essa compra
	$sql_exist_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_exist_cupom) > 0) {
		?>
		<script type="text/javascript">
			alert('Um cupom já foi utilizado para esta compra.');
			location.href='<? echo SITE.$retorno.$cod; ?>/';
		</script>
		<?
		exit();

	} else {

		$sql_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_CUPOM='$cupom' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='0' AND DATEADD(day, 1, CP_DATA_VALIDADE) >= GETDATE() ", $conexao_params, $conexao_options);
		$n_cupom = sqlsrv_num_rows($sql_cupom);
		
		if($n_cupom > 0) {

			$cupom = sqlsrv_fetch_array($sql_cupom);
			$cupom_cod = $cupom['CP_COD'];

			if($financeiro) {

				$cupom_nome = utf8_encode($cupom['CP_NOME']);
				$cupom_codigo = $cupom['CP_CUPOM'];
				$cupom_valor = $cupom['CP_DESCONTO'];
				$cupom_tipo = $cupom['CP_TIPO'];

				// 1 Porcentagem
				// 2 Valor

				$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 LO_VALOR_INGRESSOS, LO_VALOR_ADICIONAIS FROM loja WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_loja) > 0) {
					$loja = sqlsrv_fetch_array($sql_loja);
					$loja_valor_total = $loja['LO_VALOR_INGRESSOS'];
					$loja_valor_adicionais = $loja['LO_VALOR_ADICIONAIS'];
				} else {
					$erro = true;
				}

				switch ($cupom_tipo) {
					case 1:
						$loja_valor_total = $loja_valor_total - (($cupom_valor * $loja_valor_total) / 100);
					break;
					
					case 2:
						if($loja_valor_total >= $cupom_valor) $loja_valor_total = $loja_valor_total - $cupom_valor;
						else $erro = true;
					break;
				}
				
				if(!$erro) {

					$loja_valor_total = $loja_valor_total + $loja_valor_adicionais;

					$sql_cupom_usado = sqlsrv_query($conexao, "UPDATE TOP (1) cupom SET CP_UTILIZADO=1, CP_COMPRA='$cod', CP_DATA_UTILIZACAO=GETDATE() WHERE CP_COD='$cupom_cod'", $conexao_params, $conexao_options);
					$sql_compra_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_TOTAL='$loja_valor_total' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

				}

			} else {
				$_SESSION['compra-cupom']['usuario'] = $cliente;
				$_SESSION['compra-cupom']['cod'] = $cupom_cod;				
			}

		
			?>
			<script type="text/javascript">
				location.href='<? echo SITE.$retorno.$cod; ?>/';
			</script>
			<?

			//fechar conexao com o banco
			include("conn/close.php");
			include("conn/close-mssql.php");

			exit();

		}

	}	

}


$cupom = (int) $_GET['c'];
$cod = format($_GET['i']);
$financeiro = (bool) $_GET['financeiro'];
$v2 = (isset($_GET['v2'])) ? 'v2/' : '' ;
$paypal = (isset($_GET['paypal'])) ? 'paypal/' : '' ;

$retorno = $financeiro ? 'financeiro/detalhes/' : 'compras/pagamento/'.$v2.$paypal;

if(!empty($cod) && !empty($cupom)) {

	echo 'Teste Cupom';

	if($financeiro) {
		$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 LO_VALOR_PARCIAL FROM loja WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_loja) > 0) {

			$loja = sqlsrv_fetch_array($sql_loja);
			$loja_valor_total = $loja['LO_VALOR_PARCIAL'];

			$sql_cupom_usado = sqlsrv_query($conexao, "UPDATE TOP (1) cupom SET CP_UTILIZADO=0, CP_COMPRA='$cod', CP_DATA_UTILIZACAO=NULL WHERE CP_COD='$cupom'", $conexao_params, $conexao_options);
			$sql_compra_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_TOTAL='$loja_valor_total' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

		}
	} else {
		if($_SESSION['compra-cupom']['cod'] = $cupom) unset($_SESSION['compra-cupom']);		
	}
	?>
	<script type="text/javascript">
		location.href='<? echo SITE.$retorno.$cod; ?>/';
	</script>
	<?

	//fechar conexao com o banco
	include("conn/close.php");

	exit();	

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>