<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$editar = $_POST['editar'];
$nome = format($_POST['nome']);
$ano = format($_POST['ano']);
$escola_dia = $_POST['escola-dia'];
$carnaval_dias = $_SESSION['carnaval-dias'];
$carnaval_setores = $_SESSION['carnaval-setores'];
$setores_remover = $_SESSION['setores-remover'];

if(!$editar && !empty($ano) && !empty($nome) && (count($carnaval_dias) > 0) && (count($carnaval_setores) > 0) && (count($carnaval_dias) == count($escola_dia))) {

	//insere o carnaval
	$sql_eventos = sqlsrv_query($conexao, "INSERT INTO eventos (EV_NOME, EV_ANO, EV_DATA_CADASTRO) VALUES ('$nome', '$ano', GETDATE())", $conexao_params, $conexao_options);
	$evento = getLastId();

	if(!empty($evento)) {
		//busca os dias cadastrados na sessão

		foreach ($carnaval_dias as $key => $value) {

			$dia = date("Y-m-d", $value['data']);
			$atracoes = format($escola_dia[$key]);
			$nome_dia = ($key+1)."°";
			$nome_dia = utf8_decode($nome_dia);

			//insere os dias
			$sql_dias = sqlsrv_query($conexao, "INSERT INTO eventos_dias (ED_EVENTO, ED_NOME, ED_DATA, ED_ATRACOES) VALUES ('$evento', '$nome_dia', '$dia', '$atracoes')", $conexao_params, $conexao_options);
		}
		//busca os setores cadastrados na sessão
		foreach ($carnaval_setores as $key => $value) {
			//insere os setores
			$sql_setores = sqlsrv_query($conexao, "INSERT INTO eventos_setores (ES_EVENTO, ES_NOME) VALUES ('$evento', '$value')", $conexao_params, $conexao_options);
		}
	}

	?>
	<script type="text/javascript">
		alert('Evento cadastrado com sucesso.');
		location.href='<? echo SITE; ?>carnaval/lista/';
	</script>
	<?

	exit();
} elseif($editar  && !empty($cod) && !empty($nome) && (count($carnaval_dias) > 0) && (count($carnaval_setores) > 0) && (count($carnaval_dias) == count($escola_dia))) {
	
	//editar carnaval
	$sql_eventos = sqlsrv_query($conexao, "UPDATE TOP(1) eventos SET EV_NOME='$nome' WHERE EV_COD='$cod'", $conexao_params, $conexao_options);

	//busca os dias cadastrados na sessão
	foreach ($carnaval_dias as $key => $value) {
		$cod_dia = $value['cod'];
		$dia = date("Y-m-d", $value['data']);
		$atracoes = format($escola_dia[$key]);
		$nome_dia = ($key+1)."°";
		$nome_dia = utf8_decode($nome_dia);

		if(!empty($cod_dia)) {
			// echo $cod_dia." - ".$dia." - ".$atracoes." - ".$nome_dia."<br />";
			//edita os dias
			// echo "UPDATE eventos_dias SET ED_NOME='$nome_dia', ED_DATA='$dia', ED_ATRACOES='$atracoes' WHERE ED_COD='$cod_dia' LIMIT 1"."<br/><br/>";
			$sql_update_dias = sqlsrv_query($conexao, "UPDATE TOP(1) eventos_dias SET ED_NOME='$nome_dia', ED_DATA='$dia', ED_ATRACOES='$atracoes' WHERE ED_COD='$cod_dia'", $conexao_params, $conexao_options);
		} else {
			// echo $dia." - ".$atracoes." - ".$nome_dia."<br />";
			// echo "INSERT INTO eventos_dias (ED_EVENTO, ED_NOME, ED_DATA, ED_ATRACOES) VALUES ('$cod', '$nome_dia', '$dia', '$atracoes')"."<br/><br/>";
			$sql_dias = sqlsrv_query($conexao, "INSERT INTO eventos_dias (ED_EVENTO, ED_NOME, ED_DATA, ED_ATRACOES) VALUES ('$cod', '$nome_dia', '$dia', '$atracoes')", $conexao_params, $conexao_options);
		}		
	}

	//busca setores para remover
	if(count($setores_remover) > 0) {
		foreach ($setores_remover as $key => $value) {
			// echo "UPDATE eventos_setores SET D_E_L_E_T_='1' WHERE ES_COD='$value' LIMIT 1"."<br />";
			$sql_remover_setor = sqlsrv_query($conexao, "UPDATE TOP(1) eventos_setores SET D_E_L_E_T_='1' WHERE ES_COD='$value'", $conexao_params, $conexao_options);
		}
	}

	foreach ($carnaval_setores as $key => $value) {
		$cod_setor = $value['cod'];
		$nome_setor = $value['nome'];

		if(empty($cod_setor)) {
			//busca se existe setor com o mesmo nome e marcado como deletado
			$sql_busca_setor = sqlsrv_query($conexao, "SELECT ES_COD FROM eventos_setores WHERE ES_NOME='$nome_setor' AND D_E_L_E_T_='1' AND ES_EVENTO='$cod'", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_busca_setor) > 0) {
				$busca_setor = sqlsrv_fetch_array($sql_busca_setor);
				$busca_setor_cod = $busca_setor['ES_COD'];
				// echo "UPDATE eventos_setores SET D_E_L_E_T_='0' WHERE ES_COD='$busca_setor_cod' LIMIT 1"."<br/>";
				$sql_update_setores = sqlsrv_query($conexao, "UPDATE TOP(1) eventos_setores SET D_E_L_E_T_='0' WHERE ES_COD='$busca_setor_cod'", $conexao_params, $conexao_options);
			} else {
				// echo "INSERT INTO eventos_setores (ES_EVENTO, ES_NOME) VALUES ('$cod', '$nome_setor')"."<br/>";
				$sql_setores = sqlsrv_query($conexao, "INSERT INTO eventos_setores (ES_EVENTO, ES_NOME) VALUES ('$cod', '$nome_setor')", $conexao_params, $conexao_options);
			}
		}
	}
	?>
	<script type="text/javascript">
		alert('Evento alterado com sucesso.');
		location.href='<? echo SITE; ?>carnaval/editar/<? echo $cod; ?>/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");

	exit();
}
?>
<script type="text/javascript">
	alert('Ocorreu um erro no cadastro, tente novamente.');
	history.go(-1);
</script>