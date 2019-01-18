<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");


//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$codinterno = (int) $_POST['codinterno'];
$item = (int) $_POST['item'];
$compra = (int) $_POST['compra'];
$comentario = format($_POST['comentario'], false);
$comentariointerno = format($_POST['comentario-interno'], false);

$alterado = false;

if(!empty($comentario)) {

	/*if(!empty($cod)) echo "UPDATE loja_comentarios SET LC_COMENTARIO='$comentario' WHERE LC_COD='$cod'";
	if(!empty($item) && !empty($compra)) echo "INSERT INTO loja_comentarios (LC_COMPRA, LC_ITEM, LC_COMENTARIO) VALUES ($compra, $item, '$comentario')";
	$alterado = true;

	exit();*/

	if(!empty($cod)) $sql_update = sqlsrv_query($conexao, "UPDATE loja_comentarios SET LC_COMENTARIO='$comentario' WHERE LC_COD='$cod'", $conexao_params, $conexao_options);
	if(!empty($item) && !empty($compra)) $sql_ins_comentarios = sqlsrv_query($conexao, "INSERT INTO loja_comentarios (LC_COMPRA, LC_ITEM, LC_COMENTARIO) VALUES ($compra, $item, '$comentario')", $conexao_params, $conexao_options);
	$alterado = true;
}

if(!empty($comentariointerno)) {

	if(!empty($codinterno)) $sql_update = sqlsrv_query($conexao, "UPDATE loja_comentarios_internos SET LC_COMENTARIO='$comentariointerno' WHERE LC_COD='$codinterno'", $conexao_params, $conexao_options);
	if(!empty($item) && !empty($compra)) $sql_ins_comentarios = sqlsrv_query($conexao, "INSERT INTO loja_comentarios_internos (LC_COMPRA, LC_ITEM, LC_COMENTARIO) VALUES ($compra, $item, '$comentariointerno')", $conexao_params, $conexao_options);
	$alterado = true;
}

if($alterado) {
	//arquivos de layout
	include("include/head.php");

	?>
	<section id="conteudo" class="comentario atualizado">
		<header class="titulo"><h1>Comentário <span>atualizado</span></h1></header>
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

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>