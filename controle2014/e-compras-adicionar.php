<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$item = $_POST['item'];
$quantidade = $_POST['quantidade'];
$desconto = $_POST['desconto'];
$overinterno = $_POST['overinterno'];
$overexterno = $_POST['overexterno'];
$valor = $_POST['valor'];

//-----------------------------------------------------------------------------//

if((count($item) > 0) && (count($valor) > 0)) {
	
	//Adicionar à sessao
	$i = count($_SESSION['compra-interna']);

	//Procuramos se ja existe cadastrada
	if($i > 0) {
		
		foreach ($item as $k => $value) {
			foreach ($_SESSION['compra-interna'] as $key => $compra) {
				
				// Formatar valor
				// $valorf = number_format(str_replace(".", "",format($valor[$value])), 2, ".", "");
				$valorf = str_replace(",", ".",str_replace(".", "",format($valor[$value])));
				$descontof = str_replace(",", ".",str_replace(".", "",format($desconto[$value])));
				$overexternof = str_replace(",", ".",str_replace(".", "",format($overexterno[$value])));
				$overinternof = str_replace(",", ".",str_replace(".", "",format($overinterno[$value])));

				$valorf = $valorf + $overinternof + $overexternof - $descontof;

				if(($compra['item'] == $value) && (($compra['valor'] == $valorf) && ($compra['desconto'] == $descontof) && ($compra['overinterno'] == $overinternof) && ($compra['overexterno'] == $overexternof))) {

					$qtde = ((int) $quantidade[$key]) > 0 ? (int) $quantidade[$key] : 1;
					$_SESSION['compra-interna'][$key]['qtde'] += $qtde;

					unset($item[$k], $valor[$value], $desconto[$value], $overinterno[$value], $overexterno[$value], $quantidade[$value]);
				}
			}
		}
	}
	
	//Se não existe adicionamos o item ao array
	if(count($item) > 0) {
		foreach ($item as $value) {

			$valorf = str_replace(",", ".",str_replace(".", "",format($valor[$value])));
			$descontof = str_replace(",", ".",str_replace(".", "",format($desconto[$value])));
			$overexternof = str_replace(",", ".",str_replace(".", "",format($overexterno[$value])));
			$overinternof = str_replace(",", ".",str_replace(".", "",format($overinterno[$value])));

			$valorftotal = $valorf + $overinternof + $overexternof - $descontof;

			$_SESSION['compra-interna'][$i]['item'] = $value;
			$_SESSION['compra-interna'][$i]['valorbase'] = $valorf;
			$_SESSION['compra-interna'][$i]['valor'] = $valorftotal;
			$_SESSION['compra-interna'][$i]['desconto'] = $descontof;
			$_SESSION['compra-interna'][$i]['overexterno'] = $overexternof;
			$_SESSION['compra-interna'][$i]['overinterno'] = $overinternof;
			$_SESSION['compra-interna'][$i]['qtde'] = 1;
			$i++;
		}
	}
	
	ksort($_SESSION['compra-interna']);

	?>
	<script type="text/javascript">
		location.href='<? echo SITE; ?>compras/novo/';
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
			unset($_SESSION['compra-interna'][$cod]);
			sort($_SESSION['compra-interna']);

			?>
			<script type="text/javascript">
				// alert('Ingresso retirado do carrinho');
				location.href='<? echo SITE; ?>compras/novo/';
			</script>
			<?
			exit();

		break;

		case 'quantidade':
			$quantidade = (int) $_GET['quantidade'];
			if($quantidade > 0) $_SESSION['compra-interna'][$cod]['qtde'] = $quantidade;
			echo json_encode(array('sucesso' => true));
			exit();
		break;
		
	}

}

//Fechar conexoes
include("conn/close.php");

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>