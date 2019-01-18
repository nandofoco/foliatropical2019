<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

/*if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}*/

//-----------------------------------------------------------------------------//

$item = $_POST['item'];
$quantidade = $_POST['quantidade'];

//-----------------------------------------------------------------------------//

if(count($item) > 0) {
	

	//Valor dos itens
	foreach ($item as $k => $value) {

		// Valor do item
		$sql_ingressos = sqlsrv_query($conexao, "SELECT TOP 1 VE_VALOR FROM vendas WHERE VE_COD='$value' AND VE_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_ingressos) !== false) {

			$ingresso = sqlsrv_fetch_array($sql_ingressos);
			$valor[$value] = number_format($ingresso['VE_VALOR'],2,",",".");
		}
	}

	
	//Adicionar à sessao
	$i = count($_SESSION['compra-site']);

	//Procuramos se ja existe cadastrada
	if($i > 0) {
		
		foreach ($item as $k => $value) {
			
			foreach ($_SESSION['compra-site'] as $key => $compra) {

				
				// Formatar valor
				// $valorf = number_format(str_replace(".", "",format($valor[$value])), 2, ".", "");
				$valorf = str_replace(",", ".",str_replace(".", "",format($valor[$value])));

				if(($compra['item'] == $value) && ($compra['valor'] == $valorf)) {

					$qtde = ((int) $quantidade[$key]) > 0 ? (int) $quantidade[$key] : 1;
					$_SESSION['compra-site'][$key]['qtde'] += $qtde;

					unset($item[$k], $valor[$value], $quantidade[$value]);
				}
			}
		}
	}
	
	//Se não existe adicionamos o item ao array
	if(count($item) > 0) {
		foreach ($item as $value) {
			$_SESSION['compra-site'][$i]['item'] = $value;
			// $_SESSION['compra-site'][$i]['valor'] = number_format(str_replace(".", "",format($valor[$value])), 2, ".", "");
			$_SESSION['compra-site'][$i]['valor'] = str_replace(",", ".",str_replace(".", "",format($valor[$value])));
			$_SESSION['compra-site'][$i]['qtde'] = 1;
			$i++;
		}
	}
	
	ksort($_SESSION['compra-site']);

	?>
	<script type="text/javascript">
		location.href='<? echo SITE.$link_lang; ?>ingressos/';
	</script>
	<?
	exit();
}

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = format($_GET['a']);

if(is_numeric($cod) && !empty($acao)) {

	switch ($acao) {
		case 'excluir':			
			unset($_SESSION['compra-site'][$cod]);
			sort($_SESSION['compra-site']);

			?>
			<script type="text/javascript">
				// alert('Ingresso retirado do carrinho');
				location.href='<? echo SITE.$link_lang; ?>ingressos/';
			</script>
			<?
			exit();

		break;

		case 'quantidade':
			$quantidade = (int) $_GET['quantidade'];
			if($quantidade > 0) $_SESSION['compra-site'][$cod]['qtde'] = $quantidade;
			echo json_encode(array('sucesso' => true));
			exit();
		break;
		
	}

}

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>