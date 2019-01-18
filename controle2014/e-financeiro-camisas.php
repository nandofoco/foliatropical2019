<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

$resposta = 'Ocorreu um erro, tente novamente!';

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$evento = (int) $_SESSION['usuario-carnaval'];

if(is_numeric($cod) && !empty($evento)) {

	if(count($_SESSION['compras-camisas'][$cod]) > 0) {

		//Verificar se quantidade total atinge o limite;
		$folia_item = ($_SERVER['SERVER_NAME'] == "server") ? 28 :  176;
		$sql_itens = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$cod' AND LI_INGRESSO<>'$folia_item' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
		$itens_total = sqlsrv_num_rows($sql_itens);

		$camisas_total = 0;
		foreach ($_SESSION['compras-camisas'][$cod] as $key => $compra) {
			if(($compra['qtde'] > 0) && !empty($compra['tamanho'])) $camisas_total += $compra['qtde'];
		}

		//-----------------------------------------------------------------//

		if($camisas_total <= $itens_total) {

			$sql_del = sqlsrv_query($conexao, "DELETE FROM loja_camisas WHERE CA_COMPRA='$cod'", $conexao_params, $conexao_options);

			foreach ($_SESSION['compras-camisas'][$cod] as $key => $compra) {
				if(($compra['qtde'] > 0) && !empty($compra['tamanho'])) {
					for ($i=1; $i <= $compra['qtde'] ; $i++) $sql_camisa = sqlsrv_query($conexao, "INSERT INTO loja_camisas (CA_COMPRA, CA_TAMANHO) VALUES ('$cod', '".$compra['tamanho']."')", $conexao_params, $conexao_options);
				}
			}		

			//arquivos de layout
			include("include/head.php");

			?>
			<section id="conteudo" class="camisas atualizado">
				<header class="titulo"><h1>Tamanhos de camisas <span>atualizados</span></h1></header>
			</section>
			</body>
			</html>
			<script type="text/javascript">
				setTimeout(function(){ parent.$.fancybox.close(); },500);
			</script>
			<?

			//Fechar conexoes
			include("conn/close.php");
			
			exit();

		} else {
			$resposta = 'O limite de camisas foi atingido';
		}

	}
}

?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	history.go(-1);
</script>