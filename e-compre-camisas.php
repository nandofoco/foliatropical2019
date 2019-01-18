<?

define('PGMODAL','true');

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");


unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

$resposta = 'Ocorreu um erro, tente novamente!';

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$evento = setcarnaval();

$usuario_cod = $_SESSION['usuario-cod'];

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
				<section id="compre-aqui">
					<header class="titulo"><h1>Tamanhos de camisas atualizados</h1></header>
				</section>
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

include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	history.go(-1);
</script>