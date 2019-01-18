<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$titulo = format($_POST['titulo']);
$tipo = $_POST['tipo'];
$nome = $_POST['nome'];
$endereco = $_POST['endereco'];
$telefone = $_POST['telefone'];
$horario = $_POST['horario'];
$editar = $_POST['editar'];
$editar_item = $_POST['editar-item'];
$editar_horario = $_POST['editar-horario'];

//-----------------------------------------------------------------------------//

if(!$editar && !empty($titulo) && !empty($tipo) && (count($nome) > 0) && (count($endereco) > 0) && (count($telefone) > 0) && (count($horario) > 0)) {
	
	$sql_insert = sqlsrv_query($conexao, "INSERT INTO roteiros (RO_NOME, RO_TIPO) VALUES ('$titulo','$tipo')", $conexao_params, $conexao_options);

	$cod_insert = getLastId();
	if(!empty($cod_insert)) {
		foreach ($nome as $key => $value) {
			$nome_item = format($value);
			$endereco_item = format($endereco[$key]);
			$telefone_item = format($telefone[$key]);
			$sql_item = sqlsrv_query($conexao, "INSERT INTO transportes (TR_ROTEIRO, TR_NOME, TR_ENDERECO, TR_TELEFONE) VALUES ('$cod_insert','$nome_item','$endereco_item','$telefone_item')", $conexao_params, $conexao_options);

			$cod_insert_item = getLastId();
			
			foreach ($horario[$key] as $key => $hora) {
				$sql_horario = sqlsrv_query($conexao, "INSERT INTO transportes_horarios (TH_TRANSPORTE, TH_HORA) VALUES ('$cod_insert_item', '$hora')", $conexao_params, $conexao_options);
			}
		}
	}
	?>
	<script type="text/javascript">
		alert('Roteiro cadastrado com sucesso!');
		location.href='<? echo SITE; ?>roteiros/';
	</script>
	<?
	exit();

} elseif($editar && !empty($titulo) && !empty($tipo) && (count($nome) > 0) && (count($endereco) > 0) && (count($telefone) > 0) && (count($horario) > 0)) {

	$sql_update = sqlsrv_query($conexao, "UPDATE TOP (1) roteiros SET RO_NOME='$titulo', RO_TIPO='$tipo' WHERE RO_COD='$cod'", $conexao_params, $conexao_options);

	foreach ($nome as $key => $value) {
		$editar_cod = $editar_item[$key];
		$nome_item = format($value);
		$endereco_item = format($endereco[$key]);
		$telefone_item = format($telefone[$key]);
		
		if(!empty($editar_cod)) {
			$sql_update_item = sqlsrv_query($conexao, "UPDATE TOP (1) transportes SET TR_NOME='$nome_item', TR_ENDERECO='$endereco_item', TR_TELEFONE='$telefone_item' WHERE TR_COD='$editar_cod'", $conexao_params, $conexao_options);
			foreach ($horario[$key] as $item => $hora) {			
				$horario_cod = $editar_horario[$key][$item];				
				$sql_update_horario = sqlsrv_query($conexao, "UPDATE TOP (1) transportes_horarios SET TH_HORA='$hora' WHERE TH_COD='$horario_cod'", $conexao_params, $conexao_options);
			}
		} else {
			$sql_item = sqlsrv_query($conexao, "INSERT INTO transportes (TR_ROTEIRO, TR_NOME, TR_ENDERECO, TR_TELEFONE) VALUES ('$cod','$nome_item','$endereco_item','$telefone_item')", $conexao_params, $conexao_options);
			$cod_insert_item = getLastId();
			
			foreach ($horario[$key] as $key => $hora) {
				$sql_horario = sqlsrv_query($conexao, "INSERT INTO transportes_horarios (TH_TRANSPORTE, TH_HORA) VALUES ('$cod_insert_item', '$hora')", $conexao_params, $conexao_options);
			}
		}
	}
	$resposta = "Roteiro alterado com sucesso.";
	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		location.href='<? echo SITE; ?>roteiros/editar/<? echo $cod; ?>/';
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