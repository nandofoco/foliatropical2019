<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");


//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$nome = format($_POST['nome']);
$atendente = format($_POST['atendente']);
$tipo = format($_POST['tipo']);
$data = todate(format($_POST['data']), 'ddmmaaaa');
$hora = format($_POST['hora']);
$cancelar = (!empty($_POST['cancelar'])) ? true :  false;

if(!empty($cod) && !empty($tipo) && (!empty($nome) || !empty($atendente)) && !empty($data) && !empty($hora)) {

	$hora = $hora.':00';

	switch ($tipo) {
		case 'encaminhado':
			$label = (!$cancelar) ? 'Voucher <span>encaminhado</span>' : 'Encaminhamento <span>cancelado</span>';
			$encaminhado = format($_POST['encaminhado']);
			if(!$cancelar && !empty($encaminhado)) $sql_compra = sqlsrv_query($conexao, "UPDATE TOP(1) loja SET LO_DATA_ENCAMINHAMENTO=CONVERT(DATETIME, '$data $hora', 120), LO_ENCAMINHADO='1', LO_ENCAMINHADO_LOCAL='$encaminhado', LO_MOTOQUEIRO_NOME='$nome', LO_ATENDENTE_NOME='$atendente' WHERE LO_COD='$cod' ", $conexao_params, $conexao_options);
			if($cancelar) $sql_compra = sqlsrv_query($conexao, "UPDATE TOP(1) loja SET LO_DATA_ENCAMINHAMENTO=NULL, LO_ENCAMINHADO='0', LO_ENCAMINHADO_LOCAL=NULL, LO_MOTOQUEIRO_NOME=NULL, LO_ATENDENTE_NOME=NULL WHERE LO_COD='$cod' ", $conexao_params, $conexao_options);

			$log = (!$cancelar) ? substr('Voucher encaminhado (Motoqueiro: '.$nome.' / Atendente: '.$atendente.' / '.$data.':'.$hora.')', 0, 254) : 'Encaminhamento cancelado';

		break;
		case 'recebido':

			$label = (!$cancelar) ? 'Voucher <span>recebido</span>' : 'Recebimento <span>cancelado</span>';
			$recebido = format($_POST['recebido']);
			if(!$cancelar && !empty($recebido)) $sql_compra = sqlsrv_query($conexao, "UPDATE TOP(1) loja SET LO_DATA_RECEBIMENTO=CONVERT(DATETIME, '$data $hora', 120), LO_RECEBIDO='1', LO_RECEBIDO_LOCAL='$recebido', LO_ATENDENTE_NOME='$nome' WHERE LO_COD='$cod' ", $conexao_params, $conexao_options);
			if($cancelar) $sql_compra = sqlsrv_query($conexao, "UPDATE TOP(1) loja SET LO_DATA_RECEBIMENTO=NULL, LO_RECEBIDO='0', LO_RECEBIDO_LOCAL=NULL, LO_ATENDENTE_NOME=NULL WHERE LO_COD='$cod' ", $conexao_params, $conexao_options);
			
			$log = (!$cancelar) ? substr('Voucher recebido (Atendente: '.$nome.' / '.$data.':'.$hora.')', 0, 254) : 'Recebimento cancelado';

		break;
		default:

			$label = (!$cancelar) ? 'Entrega <span>confirmada</span>' : ' <span>cancelada</span>';
			if(!$cancelar) $sql_compra = sqlsrv_query($conexao, "UPDATE TOP(1) loja SET LO_DATA_ENTREGA=CONVERT(DATETIME, '$data $hora', 120), LO_ENTREGUE='1', LO_ENTREGUE_NOME='$nome' WHERE LO_COD='$cod' ", $conexao_params, $conexao_options);
			if($cancelar) $sql_compra = sqlsrv_query($conexao, "UPDATE TOP(1) loja SET LO_DATA_ENTREGA=NULL, LO_ENTREGUE='0', LO_ENTREGUE_NOME='' WHERE LO_COD='$cod' ", $conexao_params, $conexao_options);

			$log = (!$cancelar) ? substr('Entrega confirmada (Nome: '.$nome.' / '.$data.':'.$hora.')', 0, 254) : 'Entrega cancelada';

		break;
	}

	if(!empty($log)) $sql_log = sqlsrv_query($conexao, "INSERT INTO log (LG_VOUCHER, LG_USUARIO, LG_NOME, LG_ACAO, LG_DATA) VALUES ('$cod', '".$_SESSION['us-cod']."', '".$_SESSION['us-nome']."', '$log', GETDATE())", $conexao_params, $conexao_options);

	//arquivos de layout
	include("include/head.php");

	?>
	<section id="conteudo" class="comentario atualizado">
		<header class="titulo"><h1><? echo $label; ?></h1></header>
	</section>
	</body>
	</html>
	<script type="text/javascript">
		setTimeout(function(){ 
			parent.$.fancybox.close(); 
			parent.location.reload(); 
		},500);
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