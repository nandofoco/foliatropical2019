<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$tipo = (int) $_POST['tipo'];
$setor = (int) $_POST['setor'];
$dias = $_POST['dia'];
$valor = str_replace(",", ".", str_replace(".", "", format($_POST['valor'])));
$fornecedor = (int) $_POST['fornecedor'];
$grupo = (int) $_POST['grupo'];

if(!empty($evento) && !empty($tipo) && !empty($setor) && !empty($valor) && !empty($fornecedor) && !empty($grupo) && (count($dias) > 0)) {

	//-----------------------------------------------------------------------------//

	for ($idia=0; $idia<count($dias) ; $idia++) {

		$dia = (int) $dias[$idia];

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
				
				// Inserir compra
				$sql_ins_compra = sqlsrv_query($conexao, "INSERT INTO compras (CO_EVENTO, CO_TIPO, CO_DIA, CO_SETOR, CO_FORNECEDOR, CO_GRUPO, CO_VALOR, CO_DATA_CADASTRO, CO_BLOCK) VALUES ($evento, $tipo, $dia, $setor, $fornecedor, $grupo, $valor, GETDATE(), 0)", $conexao_params, $conexao_options);
				$compra = getLastId();
								
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
				$sql_uptade_opcoes = sqlsrv_query($conexao, "UPDATE compras SET $update_opcoes WHERE CO_COD='$compra' AND CO_EVENTO='$evento'", $conexao_params, $conexao_options);

			} // vagas
		} // quantidade
	} // dias
	
	?>
	<script type="text/javascript">
		alert('Ingresso cadastro com sucesso.');
		location.href='<? echo SITE; ?>ingressos/compra/novo/';
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