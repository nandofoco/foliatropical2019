<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$tipo = (int) $_POST['tipo'];
$venda = (int) $_POST['cod'];
$setor = (int) $_POST['setor'];
$dia = $_POST['dia'];

$adicionaiscad = $_POST['adicionaiscad'];
$adicionaiscod = $_POST['adicionaiscod'];
$adicionaisincluso = $_POST['adicionaisincluso'];
$adicionaisvalor = $_POST['adicionaisvalor'];

if(!empty($venda) && !empty($evento) && !empty($tipo) && !empty($setor) && !empty($dia)) {

	//-----------------------------------------------------------------------------//

	// Opcoes de quantidade

	// Inserir venda
	$sql_edit_venda = sqlsrv_query($conexao, "UPDATE TOP(1) vendas SET VE_DIA='$dia', VE_SETOR='$setor' WHERE VE_COD='$venda' AND VE_EVENTO='$evento' AND VE_TIPO='$tipo'", $conexao_params, $conexao_options);
				
	//-----------------------------------------------------------------------------//

	$update_opcoes = array();

	//-----------------------------------------------------------------------------//

	// Selecionar opcoes
	$sql_vendas_opcoes = sqlsrv_query($conexao, "SELECT * FROM vendas_opcoes WHERE VO_TIPO='$tipo' AND D_E_L_E_T_=0 ORDER BY VO_ORDEM ASC", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_vendas_opcoes) > 0) {

		while ($vendas_opcoes = sqlsrv_fetch_array($sql_vendas_opcoes)) {
			
			$vendas_opcoes_cod = $vendas_opcoes['VO_COD'];
			$vendas_opcoes_nome_exibicao = $vendas_opcoes['VO_NOME_EXIBICAO'];
			$vendas_opcoes_nome_insercao = $vendas_opcoes['VO_NOME_INSERCAO'];
			$vendas_opcoes_modelo = $vendas_opcoes['VO_MODELO'];
			$vendas_opcoes_valores = $vendas_opcoes['VO_VALORES'];
			$vendas_opcoes_tamanho = $vendas_opcoes['VO_TAMANHO'];
			$vendas_opcoes_acao = $vendas_opcoes['VO_ACAO'];

			switch ($vendas_opcoes_modelo) {
				case 'checkbox-outfield':
				case 'checkbox-infield':
				case 'radio-infield':
				case 'radio-outfield':

					$vendas_opcoes_tipo = (preg_match("/^radio/", $vendas_opcoes_modelo)) ? 'radio' : 'checkbox';

					$$vendas_opcoes_nome_exibicao = $_POST[$vendas_opcoes_nome_exibicao];

					if($vendas_opcoes_tipo == 'radio') {
						// Inserindo input radio
						$$vendas_opcoes_nome_exibicao = format($$vendas_opcoes_nome_exibicao)	;
						if(!empty($$vendas_opcoes_nome_exibicao)) array_push($update_opcoes, " $vendas_opcoes_nome_insercao = '".$$vendas_opcoes_nome_exibicao."' ");
					} else {

						$n_vendas_opcoes = count($$vendas_opcoes_nome_exibicao);
						if($n_vendas_opcoes == 1) {
							// Checkbox de apenas um campo
							$$vendas_opcoes_nome_exibicao = format($_POST[$vendas_opcoes_nome_exibicao]);
							if(!empty($$vendas_opcoes_nome_exibicao)) array_push($update_opcoes, " $vendas_opcoes_nome_insercao = '".$$vendas_opcoes_nome_exibicao."' ");
						} elseif ($n_vendas_opcoes > 1) {
							// Checkbox de array
							foreach ($$vendas_opcoes_nome_exibicao as $key => $value) {
								$value = format($value);
								if(!empty($value)) array_push($update_opcoes, " $vendas_opcoes_nome_insercao = '".$value."' ");
							}
						}

					}
				
				break;

				case 'selectbox':

					$$vendas_opcoes_nome_exibicao = format($_POST[$vendas_opcoes_nome_exibicao]);
					if(!empty($$vendas_opcoes_nome_exibicao)) array_push($update_opcoes, " $vendas_opcoes_nome_insercao = '".$$vendas_opcoes_nome_exibicao."' ");

				break;

				case 'hidden':

					$$vendas_opcoes_nome_exibicao = format($_POST[$vendas_opcoes_nome_exibicao]);
					if(!empty($$vendas_opcoes_nome_exibicao)) array_push($update_opcoes, " $vendas_opcoes_nome_insercao = '".$$vendas_opcoes_nome_exibicao."' ");

				break;

				case 'input':
					$$vendas_opcoes_nome_exibicao = format($_POST[$vendas_opcoes_nome_exibicao]);
					if($vendas_opcoes_acao == 'money') $$vendas_opcoes_nome_exibicao = number_format(str_replace(".", "", $$vendas_opcoes_nome_exibicao), 2, ".", "");
					
					//Caso seja estoque e exista vaga
					if(($vendas_opcoes_nome_exibicao == 'estoque') && ($vagas > 0)) $$vendas_opcoes_nome_exibicao = $$vendas_opcoes_nome_exibicao * $vagas;

					if(!empty($$vendas_opcoes_nome_exibicao)) array_push($update_opcoes, " $vendas_opcoes_nome_insercao = '".$$vendas_opcoes_nome_exibicao."' ");

				break;

				case 'range':
					
					$range = $_POST[$compras_opcoes_qtde_nome_exibicao];
					$range_de = (int) $range['de'];
					$range_ate = (int) $range['ate'];
					if(($range_de <= $range_ate) && ($range_de > 0)) {
						$value = (int) (($range_ate + 1) - $range_de);
						if(!empty($value)) array_push($update_opcoes, " $vendas_opcoes_nome_insercao = '".$value."' ");							
					}

				break;
				
			}
		}
	}

	//Update
	$update_opcoes = implode(",", $update_opcoes);
	$sql_uptade_opcoes = sqlsrv_query($conexao, "UPDATE vendas SET $update_opcoes WHERE VE_COD='$venda' AND VE_EVENTO='$evento'", $conexao_params, $conexao_options);

	if(count($adicionaiscod) > 0) {
		// Inserir adicionais
		foreach ($adicionaiscod as $adicional_cod) {

			$adicional_valor = !empty($adicionaisvalor[$adicional_cod]) ? number_format(str_replace(".", "", str_replace('R$ ', '', $adicionaisvalor[$adicional_cod])), 2, ".", "") : '0.00';
			$adicional_incluso = isset($adicionaisincluso[$adicional_cod]) ? 1 : 0;

			$adicional_cadastrado = $adicionaiscad[$adicional_cod];
			if(!empty($adicional_cadastrado)) {
				unset($adicionaiscad[$adicional_cod]);
				$sql_adicional = sqlsrv_query($conexao, "UPDATE TOP(1) vendas_adicionais_valores SET VAV_VALOR='$adicional_valor', VAV_INCLUSO='$adicional_incluso' WHERE VAV_COD='$adicional_cadastrado' AND VAV_EVENTO='$evento' AND VAV_VENDA='$venda' AND VAV_ADICIONAL='$adicional_cod'", $conexao_params, $conexao_options);
			}
			else $sql_adicional = sqlsrv_query($conexao, "INSERT INTO vendas_adicionais_valores (VAV_EVENTO, VAV_VENDA, VAV_ADICIONAL, VAV_VALOR, VAV_INCLUSO) VALUES ($evento, $venda, $adicional_cod, '$adicional_valor', $adicional_incluso)", $conexao_params, $conexao_options);

		}
	}

	//Excluir registros anteriores
	if(count($adicionaiscad) > 0) {
		$adicionais_del = implode(",", $adicionaiscad);
		$sql_delete_adicional = sqlsrv_query($conexao, "DELETE FROM vendas_adicionais_valores WHERE VAV_COD IN ($adicionais_del) AND VAV_EVENTO='$evento' AND VAV_VENDA='$venda'", $conexao_params, $conexao_options);
	}
	
	?>
	<script type="text/javascript">
		alert('Ingresso alterado com sucesso.');
		// location.href='<? echo SITE; ?>ingressos/venda/';
		location.href='<? echo SITE; ?>ingressos/venda/editar/<? echo $venda; ?>/';
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