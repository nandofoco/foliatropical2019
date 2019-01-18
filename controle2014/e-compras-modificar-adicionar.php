<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$item = $_POST['item'];
$valor = $_POST['valor'];
$quantidade = $_POST['quantidade'];
$desconto = $_POST['desconto'];
$overinterno = $_POST['overinterno'];
$overexterno = $_POST['overexterno'];
$cod = (int) $_POST['compra'];

//-----------------------------------------------------------------------------//

if(is_numeric($cod) && (count($item) > 0) && (count($valor) > 0)) {
	
	//Adicionar à sessao
	$i = count($_SESSION['compra-modificar'][$cod]);

	//Procuramos se ja existe cadastrada
	if($i > 0) {
		
		foreach ($item as $k => $value) {
			foreach ($_SESSION['compra-modificar'][$cod] as $key => $compra) {
				
				// Formatar valor
				// $valorf = number_format(str_replace(".", "",format($valor[$value])), 2, ".", "");
				$valorf = str_replace(",", ".",str_replace(".", "",format($valor[$value])));
				$descontof = str_replace(",", ".",str_replace(".", "",format($desconto[$value])));
				$overexternof = str_replace(",", ".",str_replace(".", "",format($overexterno[$value])));
				$overinternof = str_replace(",", ".",str_replace(".", "",format($overinterno[$value])));

				$valorf = $valorf + $overinternof + $overexternof - $descontof;

				if(($compra['item'] == $value) && (($compra['valor'] == $valorf) && ($compra['desconto'] == $descontof) && ($compra['overinterno'] == $overinternof) && ($compra['overexterno'] == $overexternof))) {

					$qtde = ((int) $quantidade[$key]) > 0 ? (int) $quantidade[$key] : 1;
					$_SESSION['compra-modificar'][$cod][$key]['qtde'] += $qtde;

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

			$_SESSION['compra-modificar'][$cod][$i]['item'] = $value;
			$_SESSION['compra-modificar'][$cod][$i]['valorbase'] = $valorf;
			$_SESSION['compra-modificar'][$cod][$i]['valor'] = $valorftotal;
			$_SESSION['compra-modificar'][$cod][$i]['desconto'] = $descontof;
			$_SESSION['compra-modificar'][$cod][$i]['overexterno'] = $overexternof;
			$_SESSION['compra-modificar'][$cod][$i]['overinterno'] = $overinternof;
			$_SESSION['compra-modificar'][$cod][$i]['qtde'] = 1;
			$i++;
		}
	}
	
	ksort($_SESSION['compra-modificar'][$cod]);

	?>
	<script type="text/javascript">
		location.href='<? echo SITE; ?>compras/modificar/<? echo $cod; ?>/';
	</script>
	<?
	exit();
}

//-----------------------------------------------------------------------------//

$c = (int) $_GET['c'];
$cod = (int) $_GET['cod'];
$acao = format($_GET['a']);

if(is_numeric($cod) && is_numeric($c) && !empty($acao)) {

	switch ($acao) {
		case 'excluir':			
			unset($_SESSION['compra-modificar'][$cod][$c]);
			sort($_SESSION['compra-modificar'][$cod]);

			?>
			<script type="text/javascript">
				// alert('Ingresso retirado do carrinho');
				location.href='<? echo SITE; ?>compras/modificar/<? echo $cod; ?>/';
			</script>
			<?
			exit();

		break;

		case 'quantidade':
			$quantidade = (int) $_GET['quantidade'];
			if($quantidade > 0) $_SESSION['compra-modificar'][$cod][$c]['qtde'] = $quantidade;
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