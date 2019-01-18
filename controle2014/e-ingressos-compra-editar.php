<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_POST['cod'];
$grupo = (int) $_POST['grupo'];
$tipo = (int) $_POST['tipo'];
$setor = (int) $_POST['setor'];
$dia = (int) $_POST['dia'];
#$valor = number_format(str_replace(".", "",format($_POST['valor'])), 2, ".", "");
$valor = str_replace(",", ".", str_replace(".", "", format($_POST['valor'])));
$fornecedor = (int) $_POST['fornecedor'];


if(!empty($evento) && !empty($cod) && !empty($grupo) && !empty($tipo) && !empty($setor) && !empty($valor) && !empty($fornecedor) && !empty($dia)) {

	// Buscando os ingressos que estao no mesmo grupo
	$sql_ingressos_valores = sqlsrv_query($conexao, "SELECT * FROM compras WHERE CO_COD='$cod'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_ingressos_valores) > 0) {

		$ingressos_valores = sqlsrv_fetch_array($sql_ingressos_valores);
		
		$ingressos_valores_tipo = is_null($ingressos_valores['CO_TIPO']) ? " CO_TIPO IS NULL " : " CO_TIPO='".$ingressos_valores['CO_TIPO']."'";
		$ingressos_valores_setor = is_null($ingressos_valores['CO_SETOR']) ? " AND CO_SETOR IS NULL " : " AND CO_SETOR='".$ingressos_valores['CO_SETOR']."'";
		$ingressos_valores_dia = is_null($ingressos_valores['CO_DIA']) ? " AND CO_DIA IS NULL " : " AND CO_DIA='".$ingressos_valores['CO_DIA']."'";
		$ingressos_valores_fila = is_null($ingressos_valores['CO_FILA']) ? " AND CO_FILA IS NULL " : " AND CO_FILA='".$ingressos_valores['CO_FILA']."'";
		$ingressos_valores_nivel = is_null($ingressos_valores['CO_NIVEL']) ? " AND CO_NIVEL IS NULL " : " AND CO_NIVEL='".$ingressos_valores['CO_NIVEL']."'";
		$ingressos_valores_valor = is_null($ingressos_valores['CO_VALOR']) ? " AND CO_VALOR IS NULL " : " AND CO_VALOR='".$ingressos_valores['CO_VALOR']."'";
		$ingressos_valores_grupo = is_null($ingressos_valores['CO_GRUPO']) ? " AND CO_GRUPO IS NULL " : " AND CO_GRUPO='".$ingressos_valores['CO_GRUPO']."'";

		$sql_ingressos = sqlsrv_query($conexao, "SELECT CO_COD FROM compras WHERE $ingressos_valores_tipo $ingressos_valores_setor $ingressos_valores_dia $ingressos_valores_fila $ingressos_valores_nivel $ingressos_valores_valor $ingressos_valores_grupo", $conexao_params, $conexao_options);
		$cadastrados = sqlsrv_num_rows($sql_ingressos);
		if($cadastrados > 0) {
			$ar_ingressos = array();
			while ($ingressos = sqlsrv_fetch_array($sql_ingressos)) array_push($ar_ingressos, $ingressos['CO_COD']);
		}
	}


	//-----------------------------------------------------------------------------//
	// Opcoes de quantidade
	$quantidade = 1;
	$vagas = 1;

	// Selecionar opcoes
	$sql_compras_opcoes_qtde = sqlsrv_query($conexao, "SELECT * FROM compras_opcoes WHERE CP_TIPO='$tipo' AND (CP_MODELO='range' OR CP_NOME_EXIBICAO='vagas') AND D_E_L_E_T_=0 ORDER BY CP_ORDEM ASC", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_compras_opcoes_qtde) > 0) {

		while ($compras_opcoes_qtde = sqlsrv_fetch_array($sql_compras_opcoes_qtde)) {
			
			$compras_opcoes_qtde_cod = $compras_opcoes_qtde['CP_COD'];
			$compras_opcoes_qtde_nome_exibicao = $compras_opcoes_qtde['CP_NOME_EXIBICAO'];
			$compras_opcoes_qtde_nome_insercao = $compras_opcoes_qtde['CP_NOME_INSERCAO'];
			$compras_opcoes_qtde_modelo = $compras_opcoes_qtde['CP_MODELO'];
			$compras_opcoes_qtde_valores = $compras_opcoes_qtde['CP_VALORES'];
			$compras_opcoes_qtde_tamanho = $compras_opcoes_qtde['CP_TAMANHO'];
			$compras_opcoes_qtde_acao = $compras_opcoes_qtde['CP_ACAO'];

			switch ($compras_opcoes_qtde_modelo) {
				case 'input':

					$$compras_opcoes_qtde_nome_exibicao = format($_POST[$compras_opcoes_qtde_nome_exibicao]);
					$input_opcoes = $compras_opcoes_qtde_nome_insercao;

				break;

				case 'range':
					
					$range = $_POST[$compras_opcoes_qtde_nome_exibicao];
					$range_de = (int) $range['de'];
					$range_ate = (int) $range['ate'];
					if(($range_de <= $range_ate) && ($range_de > 0)) {
						$value = (int) (($range_ate + 1) - $range_de);
						if(!empty($value)) {
							$quantidade = $value;
							$range_opcoes_nome = $compras_opcoes_qtde_nome_insercao;
							for ($irange=$range_de; $irange<($range_de + $quantidade) ; $irange++) { 
								$range_opcoes[($irange-$range_de)] = $irange;
							}

						}
					}

				break;
				
			}
		}
	}

	for ($i=0; $i < $quantidade ; $i++) {

		//Vagas
		for ($ivaga=1; $ivaga <= $vagas ; $ivaga++) {
			
			unset($compra);

			if($ar_ingressos[$ivaga-1]) {
				//Se o ingresso existe atualizamos
				$compra = $ar_ingressos[$ivaga-1];
				unset($ar_ingressos[$ivaga-1]);
			} else {
				// Inserir compra
				$sql_ins_compra = sqlsrv_query($conexao, "INSERT INTO compras (CO_EVENTO, CO_TIPO, CO_DIA, CO_SETOR, CO_FORNECEDOR, CO_GRUPO, CO_VALOR, CO_DATA_CADASTRO, CO_BLOCK) VALUES ($evento, $tipo, $dia, $setor, $fornecedor, $grupo, $valor, GETDATE(), 0)", $conexao_params, $conexao_options);
				$compra = getLastId();
			}

							
			//-----------------------------------------------------------------------------//

			$update_opcoes = array();

			// Numero da vaga
			if($vagas > 1) array_push($update_opcoes, " $input_opcoes = '$ivaga' ");

			//-----------------------------------------------------------------------------//

			// Numero
			if($range_opcoes_nome) array_push($update_opcoes, " $range_opcoes_nome = '".$range_opcoes[$i]."' ");
			

			//-----------------------------------------------------------------------------//

			// Selecionar opcoes
			$sql_compras_opcoes = sqlsrv_query($conexao, "SELECT * FROM compras_opcoes WHERE CP_TIPO='$tipo' AND CP_MODELO<>'range' AND CP_NOME_EXIBICAO<>'vagas' AND D_E_L_E_T_=0 ORDER BY CP_ORDEM ASC", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_compras_opcoes) > 0) {

				while ($compras_opcoes = sqlsrv_fetch_array($sql_compras_opcoes)) {
					
					$compras_opcoes_cod = $compras_opcoes['CP_COD'];
					$compras_opcoes_nome_exibicao = $compras_opcoes['CP_NOME_EXIBICAO'];
					$compras_opcoes_nome_insercao = $compras_opcoes['CP_NOME_INSERCAO'];
					$compras_opcoes_modelo = $compras_opcoes['CP_MODELO'];
					$compras_opcoes_valores = $compras_opcoes['CP_VALORES'];
					$compras_opcoes_tamanho = $compras_opcoes['CP_TAMANHO'];
					$compras_opcoes_acao = $compras_opcoes['CP_ACAO'];

					switch ($compras_opcoes_modelo) {
						case 'checkbox-outfield':
						case 'checkbox-infield':
						case 'radio-infield':
						case 'radio-outfield':

							$compras_opcoes_tipo = (preg_match("/^radio/", $compras_opcoes_modelo)) ? 'radio' : 'checkbox';

							$$compras_opcoes_nome_exibicao = $_POST[$compras_opcoes_nome_exibicao];

							if($compras_opcoes_tipo == 'radio') {
								// Inserindo input radio
								$$compras_opcoes_nome_exibicao = format($$compras_opcoes_nome_exibicao)	;
								if(!empty($$compras_opcoes_nome_exibicao)) array_push($update_opcoes, " $compras_opcoes_nome_insercao = '".$$compras_opcoes_nome_exibicao."' ");
							} else {

								$n_compras_opcoes = count($$compras_opcoes_nome_exibicao);
								if($n_compras_opcoes == 1) {
									// Checkbox de apenas um campo
									$$compras_opcoes_nome_exibicao = format($_POST[$compras_opcoes_nome_exibicao]);
									if(!empty($$compras_opcoes_nome_exibicao)) array_push($update_opcoes, " $compras_opcoes_nome_insercao = '".$$compras_opcoes_nome_exibicao."' ");
								} elseif ($n_compras_opcoes > 1) {
									// Checkbox de array
									foreach ($$compras_opcoes_nome_exibicao as $key => $value) {
										$value = format($value);
										if(!empty($value)) array_push($update_opcoes, " $compras_opcoes_nome_insercao = '".$value."' ");
									}
								}

							}
						
						break;

						case 'selectbox':

							$$compras_opcoes_nome_exibicao = format($_POST[$compras_opcoes_nome_exibicao]);
							if(!empty($$compras_opcoes_nome_exibicao)) array_push($update_opcoes, " $compras_opcoes_nome_insercao = '".$$compras_opcoes_nome_exibicao."' ");

						break;

						case 'input':
							
							$$compras_opcoes_nome_exibicao = format($_POST[$compras_opcoes_nome_exibicao]);
							if(!empty($$compras_opcoes_nome_exibicao)) array_push($update_opcoes, " $compras_opcoes_nome_insercao = '".$$compras_opcoes_nome_exibicao."' ");

						break;
						
					}
				}
			}

			//Update
			$update_opcoes = implode(",", $update_opcoes);				
			$sql_uptade_opcoes = sqlsrv_query($conexao, "UPDATE compras SET CO_TIPO='$tipo', CO_DIA='$dia', CO_SETOR='$setor', CO_FORNECEDOR='$fornecedor', CO_GRUPO='$grupo', CO_VALOR='$valor', $update_opcoes WHERE CO_COD='$compra' AND CO_EVENTO='$evento'", $conexao_params, $conexao_options);

		} // vagas
	} // quantidade
	
	if(count($ar_ingressos) > 0) {
		//Excluir
		$ar_excluir = implode(",", $ar_ingressos);
		$sql_delete = sqlsrv_query($conexao, "DELETE FROM compras WHERE CO_COD IN ($ar_excluir) AND CO_EVENTO='$evento'", $conexao_params, $conexao_options);
	}

	?>
	<script type="text/javascript">
		alert('Ingresso alterado com sucesso.');
		location.href='<? echo SITE; ?>ingressos/compra/editar/<? echo $compra; ?>/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");

	exit();

}


?>
<script type="text/javascript">
	alert('Ocorreu um erro na alteração, tente novamente.');
	history.go(-1);
</script>