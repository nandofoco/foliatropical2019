<?

//Verificamos o dominio
include("include/includes.php");

$cod = (int) $_POST['cod'];
$loja = $_POST['loja'];
$item = (int) $_POST['item'];

$nloja = count($loja);

if(!empty($cod) && ($nloja > 0) && !empty($item)){

	//Inserir as alterações no log
	foreach ($loja as $value) {
		$sql_log = sqlsrv_query($conexao, "INSERT INTO loja_alteracao (LA_ITEM, LA_ANTERIOR, LA_ATUAL, LA_DATA) VALUES ('$value', (SELECT TOP 1 LI_INGRESSO FROM loja_itens WHERE LI_COMPRA='$cod' AND LI_COD='$value'), '$item', GETDATE())", $conexao_params, $conexao_options);
	}

	//Atualizar
	$cods = implode(',', $loja);
	
	$sql_update = sqlsrv_query($conexao, "UPDATE loja_itens SET LI_INGRESSO='$item' WHERE LI_COMPRA='$cod' AND LI_COD IN ($cods)", $conexao_params, $conexao_options);
	
	?>
	<script type="text/javascript">
		alert('Ingressos alterados');
		location.href='<? echo SITE; ?>financeiro/detalhes/<? echo $cod; ?>/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");

	exit();

}

// Inserir Canal de venda

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>