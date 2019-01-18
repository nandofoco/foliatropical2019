<?
//Verificamos o dominio
include("include/includes.php");
//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");
//-----------------------------------------------------------------------------//
$cliente = (int) $_POST['cliente'];
$canal = (int) $_POST['canal'];
$forma = (int) $_POST['forma'];
$comissao = (int) $_POST['comissao'];
$compra = (int) $_POST['compra'];
$parceiro = (int) $_POST['parceiro'];
$quantidade = $_POST['quantidade'];
$adicionaiscod = $_POST['adicionaiscod'];
$exclusividade = $_POST['exclusividade'];
$exclusividadeval = $_POST['exclusividadeval'];
$transferqtde = $_POST['transferqtde'];
$comentarios = $_POST['comentarios'];
$comentariosinternos = $_POST['comentariosinternos'];
$delivery = ($_POST['delivery']) ? 1 : 0;
$retida = ($_POST['retida'] == 'true') ? 1 : 0;
$frisa = $_POST['frisa'];
$folia = $_POST['folia'];
$delivery_valor = 0;
if($delivery) $delivery_valor = ($_POST['valoradicionaldelivery'] > 0) ? $_POST['valoradicionaldelivery'] : 0;
if(!$delivery) $retirada = format($_POST['retirada']);
if(!$delivery) $periodo = format($_POST['periodo']);
if(!$delivery) $data_retirada = todate(format($_POST['data-retirada']), 'ddmmaaaa');
$origem = format($_POST['origem']);
$deadline = ($forma == 5) ? "'".todate(format($_POST['deadline']), 'ddmmaaaa')."'" : 'NULL';
$vendedor_externo = ((int) $_POST['vendedor-externo'] > 0) ? "'".(int) $_POST['vendedor-externo']."'" : 'NULL';
if(is_numeric($compra)) {
	$n_itens = count($_SESSION['compra-modificar'][$compra]);
	$evento = (int) $_SESSION['usuario-carnaval'];
	$retorno = false;
	//-----------------------------------------------------------------------------//
	if($n_itens > 0) {
		foreach ($_SESSION['compra-modificar'][$compra] as $key => $carrinho) {
			//Atualizamos a quantidade
			$_SESSION['compra-modificar'][$compra][$key]['qtde'] = $carrinho['qtde'] = (int) $quantidade[$key];
			/*"SELECT v.*, t.TI_NOME, d.ED_NOME, s.ES_NOME,
				@ingresso:=v.VE_COD AS COD,
				@ingressos:=(SELECT COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.LI_INGRESSO=@ingresso AND li.D_E_L_E_T_=0) AS QTDE,
				@total := CAST((v.VE_ESTOQUE - @ingressos) AS SIGNED), IF(@total < 0,0, @total) AS TOTAL
				FROM vendas v, tipos t, eventos_dias d, eventos_setores s WHERE v.VE_COD='".$carrinho['item']."' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 AND d.ED_COD=v.VE_DIA AND t.TI_COD=v.VE_TIPO AND s.ES_COD=v.VE_SETOR AND d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0 LIMIT 1"*/
			//Buscando se há disponibilidade
			$sql_ingressos = sqlsrv_query($conexao, "
				DECLARE @ingresso INT='".$carrinho['item']."';
				DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));
				DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);
				INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
				SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM vendas WHERE VE_COD=@ingresso AND VE_BLOCK=0 AND D_E_L_E_T_=0;
				INSERT INTO @qtde (COD, QTDE)
				SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE li.LI_COMPRA<>'$compra' AND li.LI_INGRESSO=@ingresso AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;
				SELECT TOP 1 * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL, t.TI_NOME, d.ED_NOME, s.ES_NOME FROM @vendas v 
				LEFT JOIN @qtde q ON v.VE_COD = q.COD
				LEFT JOIN tipos t ON t.TI_COD=v.VE_TIPO
				LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA
				LEFT JOIN eventos_setores s ON s.ES_COD=v.VE_SETOR
				WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0) S", $conexao_params, $conexao_options);
			
			if(sqlsrv_next_result($sql_ingressos) && sqlsrv_next_result($sql_ingressos))
			if(sqlsrv_num_rows($sql_ingressos) !== false) {
				$ar_ingresso = sqlsrv_fetch_array($sql_ingressos);
				$ingresso_estoque = $ar_ingresso['TOTAL'];
				if($carrinho['estoque'] > 0) $ingressos_estoque += $carrinho['estoque'];
				//Se o estoque for 0 nao deixamos realizar a venda
				if($ingresso_estoque < $carrinho['qtde']) $retorno = true;
			}
		}
	}
	if($retorno) {
		?>
		<script type="text/javascript">
			location.href='<? echo SITE; ?>compras/modificar/<? echo $compra; ?>/';
		</script>
		<?
		exit();
	}
	//-----------------------------------------------------------------------------//
	$link_retorno = SITE.'financeiro/detalhes/'.$compra.'/';
	if(!empty($evento) && ($n_itens > 0)) {
		// Excluir itens referentes a compra atual
		// $sql_ins_item = sqlsrv_query($conexao, "DELETE FROM loja_itens WHERE LI_COMPRA='$compra'", $conexao_params, $conexao_options);
		// $sql_ins_comentarios = sqlsrv_query($conexao, "DELETE FROM loja_comentarios WHERE LC_COMPRA='$compra'", $conexao_params, $conexao_options);
		// $sql_ins_comentarios_internos = sqlsrv_query($conexao, "DELETE FROM loja_comentarios_internos WHERE LC_COMPRA='$compra'", $conexao_params, $conexao_options);
		// $sql_ins_adicionais = sqlsrv_query($conexao, "DELETE FROM loja_itens_adicionais WHERE LIA_COMPRA='$compra'", $conexao_params, $conexao_options);
		
		$sql_ins_item = sqlsrv_query($conexao, "UPDATE loja_itens SET D_E_L_E_T_='1' WHERE LI_COMPRA='$compra'", $conexao_params, $conexao_options);
		$sql_ins_comentarios = sqlsrv_query($conexao, "UPDATE loja_comentarios SET D_E_L_E_T_='1' WHERE LC_COMPRA='$compra'", $conexao_params, $conexao_options);
		$sql_ins_comentarios_internos = sqlsrv_query($conexao, "UPDATE loja_comentarios_internos SET D_E_L_E_T_='1' WHERE LC_COMPRA='$compra'", $conexao_params, $conexao_options);
		$sql_ins_adicionais = sqlsrv_query($conexao, "UPDATE loja_itens_adicionais SET D_E_L_E_T_='1' WHERE LIA_COMPRA='$compra'", $conexao_params, $conexao_options);

		//Buscar nome do cliente
		$valor = 0.00;
		$valor_ingressos = 0.00;
		$valor_ingressos_base = 0.00;
		$valor_adicionais = 0.00;
		$valor_desconto = 0.00;
		$valor_transfer = 0.00;
		$valor_over_interno = 0.00;
		$valor_over_externo = 0.00;
		$loja_qtde_folia = 0;
		$loja_qtde_frisa = 0;
		//Novos combos
		$loja_qtde_combo = array();
		//-----------------------------------------------------------------------------//
		$sql_data_compra = sqlsrv_query($conexao, "SELECT TOP 1 LO_DATA_COMPRA FROM loja WHERE LO_COD='$compra'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_data_compra) > 0) {
			$data_compra = sqlsrv_fetch_array($sql_data_compra);
			$loja_data = $data_compra['LO_DATA_COMPRA'];
		}
		//-----------------------------------------------------------------------------//
		//Buscar o código do folia
		$sql_folia_cod = sqlsrv_query($conexao, "SELECT TOP 1 TI_COD FROM tipos WHERE TI_TAG='lounge'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_folia_cod) > 0) {
			$loja_cod_folia_ar = sqlsrv_fetch_array($sql_folia_cod);
			$loja_cod_folia = $loja_cod_folia_ar['TI_COD'];
		}
		//Buscar o código do frisa
		$sql_frisa_cod = sqlsrv_query($conexao, "SELECT TOP 1 TI_COD FROM tipos WHERE TI_TAG='frisa'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_frisa_cod) > 0) {
			$loja_cod_frisa_ar = sqlsrv_fetch_array($sql_frisa_cod);
			$loja_cod_frisa = $loja_cod_frisa_ar['TI_COD'];
		}
		$loja_frisas_carrinho = $_SESSION['compra-modificar'];
		//-----------------------------------------------------------------------------//
		foreach ($_SESSION['compra-modificar'][$compra] as $key => $carrinho) {
			// $_SESSION['compra-modificar'][$compra][$key]['qtde'] = $carrinho['qtde'] = (int) $quantidade[$key];
			
			//Valores dos ingressos
			$item_valores = $carrinho['valor'] * $carrinho['qtde'];
			$item_valores_base = $carrinho['valorbase'] * $carrinho['qtde'];
			$valor_ingressos += $item_valores;
			$valor_ingressos_base += $item_valores_base;
			for ($iitem=1; $iitem <=$carrinho['qtde'] ; $iitem++) {
				
				//-----------------------------------------------------------------------------//
				
				//Procurar o overpricing
				$item_valor_tabela = 0.00;
				$item_valor_adicionais = 0.00;
				$item_valor_transfer = 0.00;
				$item_vagas = 1;
				$sql_item_infos = sqlsrv_query($conexao, "SELECT TOP 1 VE_VALOR, VE_VAGAS, VE_TIPO_ESPECIFICO, VE_VALOR_EXCLUSIVIDADE, VE_DIA, VE_TIPO FROM vendas WHERE VE_COD='".$carrinho['item']."'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_item_infos) !== false) {
					$ar_item_infos = sqlsrv_fetch_array($sql_item_infos);
					$item_dia = $ar_item_infos['VE_DIA'];
					$item_tipo = $ar_item_infos['VE_TIPO'];
					$item_valor_tabela = $ar_item_infos['VE_VALOR'];
					$item_valor_exclusividade = $ar_item_infos['VE_VALOR_EXCLUSIVIDADE'];
					$item_tipo_especifico = utf8_encode($ar_item_infos['VE_TIPO_ESPECIFICO']);
					if(($ar_item_infos['VE_VAGAS'] > 0) && ($item_tipo_especifico == 'fechado')) $item_vagas = utf8_encode($ar_item_infos['VE_VAGAS']);
					//Buscar informações de dia
					$sql_item_dia = sqlsrv_query($conexao, "SELECT TOP 1 ED_DATA FROM eventos_dias WHERE ED_COD='$item_dia'", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_item_dia) > 0) {
						$item_dia_ar = sqlsrv_fetch_array($sql_item_dia);
						$item_data = $item_dia_ar['ED_DATA'];
						//$item_data = (string) date('Y-m-d', strtotime($item_data->format('Y-m-d')));
						if(is_object($item_data)) $item_data = (string) date('Y-m-d', strtotime($item_data->format('Y-m-d')));
						/*if(($item_tipo == $loja_cod_folia) && (in_array($item_data, $dias_principais))){
							//Adicionamos na quantidade e excluimos do array
							$loja_qtde_folia++;
							foreach ($dias_principais as $key_dia => $item_dia_atual) {
								if ($item_dia_atual == $item_data) unset($dias_principais[$key_dia]);
							}
						}*/
						if(($item_tipo == $loja_cod_folia)) {
							//loja_qtde_combo
							if(count($combo_dias) > 0) {
								// Limite
								$loja_data_limite = (string) date('Y-m-d', strtotime($loja_data->format('Y-m-d')));
								foreach ($combo_dias as $k => $c) {
									//Verificar cada ocorrencia
									// if(in_array($item_data_n, $c['dias'])) {
									// Modificacao por causa da data de compra
									if(in_array($item_data, $c['dias']) && ($loja_data_limite >= $c['limite'][0]) && ($loja_data_limite <= $c['limite'][1])) {
										$loja_qtde_combo[$k] = 1 + ((int) $loja_qtde_combo[$k]);
										//Retiramos do combo o valor encontrado
										foreach ($c['dias'] as $kd => $ingressos_dia_atual) {
											if ($ingressos_dia_atual == $item_data) unset($combo_dias[$k]['dias'][$kd]);
										}
									}									
								}
							}
							
						}
						if($evento > 1) {
							if(($item_tipo == $loja_cod_frisa) && array_key_exists($key, $loja_frisas_carrinho[$compra])){
								
								$loja_frisa_fechadas = floor($carrinho['qtde'] / 6);
								if($loja_frisa_fechadas > 0) $loja_qtde_frisa = $loja_qtde_frisa + $loja_frisa_fechadas;
								unset($loja_frisas_carrinho[$compra][$key]);
							}
						}
					} // Item Dia
				}
				$excl = ($exclusividade[$key] == true) ? true : false;
				for ($iitemvaga=1; $iitemvaga <= $item_vagas; $iitemvaga++) { 
					$item_id = ($item_vagas > 1) ? $iitemvaga : $iitem;
					$valor_desconto += $carrinho['desconto'];
					$valor_over_interno += $carrinho['overinterno'];
					$valor_over_externo += $carrinho['overexterno'];
					$excl = ($excl == true) ? 1 : 0;
					$exclval = (float) $exclusividadeval[$key];
					
					//Cadastrar item
					$item_desconto = (float) $carrinho['desconto'];
					$item_overinterno = (float) $carrinho['overinterno'];
					$item_overexterno = (float) $carrinho['overexterno'];
					$sql_ins_item = sqlsrv_query($conexao, "INSERT INTO loja_itens (LI_COMPRA, LI_INGRESSO, LI_NOME, LI_ID, LI_VALOR_TABELA, LI_VALOR, LI_EXCLUSIVIDADE, LI_EXCLUSIVIDADE_VAL, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO) VALUES ($compra, ".$carrinho['item'].", '$nome_cliente', $item_id, '$item_valor_tabela', '".$carrinho['valor']."', '$excl', '$exclval', '$item_desconto', '$item_overinterno', '$item_overexterno')", $conexao_params, $conexao_options);
					$item = getLastId();
									
					//Adicionar valor exclusividade
					if(($iitemvaga == 1) && $excl) {
						$valor_adicionais += $item_valor_exclusividade;
						$item_valor_adicionais += $item_valor_exclusividade;
					}
					// Cadastrar comentarios
					$item_comentario = format($comentarios[$key]);
					if(!empty($item_comentario)) $sql_ins_comentarios = sqlsrv_query($conexao, "INSERT INTO loja_comentarios (LC_COMPRA, LC_ITEM, LC_COMENTARIO) VALUES ($compra, $item, '$item_comentario')", $conexao_params, $conexao_options);
					// Cadastrar comentarios internos
					$item_comentario_interno = format($comentariosinternos[$key]);
					if(!empty($item_comentario_interno)) $sql_ins_comentarios_internos = sqlsrv_query($conexao, "INSERT INTO loja_comentarios_internos (LC_COMPRA, LC_ITEM, LC_COMENTARIO) VALUES ($compra, $item, '$item_comentario_interno')", $conexao_params, $conexao_options);
					//-----------------------------------------------------------------------------//
					if(count($adicionaiscod[$key]) > 0){
						$cod_adicionais = implode(",", $adicionaiscod[$key]);
						//Valores adicionais
						$sql_vendas_adicionais = sqlsrv_query($conexao, "SELECT v.*, vv.* FROM vendas_adicionais v, vendas_adicionais_valores vv WHERE v.VA_COD IN ($cod_adicionais) AND vv.VAV_VENDA='".$carrinho['item']."' AND vv.VAV_ADICIONAL=v.VA_COD AND vv.VAV_BLOCK=0 AND vv.D_E_L_E_T_=0 AND v.VA_BLOCK=0 AND v.D_E_L_E_T_=0 ORDER BY v.VA_COD ASC", $conexao_params, $conexao_options);
						if(sqlsrv_num_rows($sql_vendas_adicionais) !== false) {
							while ($vendas_adicionais = sqlsrv_fetch_array($sql_vendas_adicionais)) {
								$vendas_adicionais_cod = $vendas_adicionais['VA_COD'];
								$vendas_adicionais_tipo = $vendas_adicionais['VA_TIPO'];
								$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
								$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
								$vendas_adicionais_nome_insercao = $vendas_adicionais['VA_NOME_INSERCAO'];
								$vendas_adicionais_multi = (bool) $vendas_adicionais['VA_VALOR_MULTI'];
								$vendas_adicionais_opcoes_cod = $vendas_adicionais['VAV_COD'];
								$vendas_adicionais_opcoes_valor = $vendas_adicionais['VAV_VALOR'];
								// if($vendas_adicionais_multi) $vendas_adicionais_opcoes_valor = $vendas_adicionais_opcoes_valor * $carrinho['qtde'];
								$vendas_adicionais_opcoes_incluso = (bool) $vendas_adicionais['VAV_INCLUSO'];
								$vendas_adicionais_opcoes_incluso_int = $vendas_adicionais_opcoes_incluso ? 1 : 0;
								if($vendas_adicionais_nome_exibicao == 'delivery'){
									/*if($delivery && (!$vendas_adicionais_delivery['incluso']) || $vendas_adicionais_opcoes_incluso || ($vendas_adicionais_opcoes_valor > $vendas_adicionais_delivery['valorn'])){
										$vendas_adicionais_delivery['incluso'] = $vendas_adicionais_opcoes_incluso;
										$vendas_adicionais_delivery['incluso'] = ($vendas_adicionais_opcoes_incluso) ? 1 : 0;
										$vendas_adicionais_delivery['cod'] = $vendas_adicionais_cod;
										$vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
										$vendas_adicionais_delivery['valor'] = $vendas_adicionais_opcoes_valor;
									}*/
								} else {
									$adicional_enable = true;
									//Limitamos o transfer
									if(!$vendas_adicionais_multi && ($carrinho['item'] == $item_anterior)) $adicional_enable = false;
									//if(($vendas_adicionais_nome_exibicao == 'transfer') && (isset($transferqtde[$key])) && ($iitemvaga > $transferqtde[$key])) $adicional_enable = false;
									if($adicional_enable) {
										//if(($vendas_adicionais_nome_exibicao == 'transfer') && (isset($transferqtde[$key]))) $item_valor_transfer = $vendas_adicionais_opcoes_valor;
										if($vendas_adicionais_nome_exibicao == 'transfer') $item_valor_transfer = $vendas_adicionais_opcoes_valor;
										else $item_valor_adicionais += $vendas_adicionais_opcoes_valor;
										$valor_adicionais += $vendas_adicionais_opcoes_valor;
										//Inserir no banco
										$sql_ins_adicionais = sqlsrv_query($conexao, "INSERT INTO loja_itens_adicionais (LIA_COMPRA, LIA_ITEM, LIA_ADICIONAL, LIA_VALOR, LIA_INCLUSO) VALUES ($compra, $item, $vendas_adicionais_cod, '$vendas_adicionais_opcoes_valor', $vendas_adicionais_opcoes_incluso_int)", $conexao_params, $conexao_options);
										
									}
									
									/*if(($vendas_adicionais_nome_exibicao == 'transfer') || ($vendas_adicionais_opcoes_incluso)) {
										$transfer_redirect = true;
										$link_retorno = SITE.'agendamentos/editar/'.$compra.'/';
									}*/
								}
							}
						}
						$valor_transfer += $item_valor_transfer;
					}
					
					//Atualizar
					if(($item_valor_transfer > 0) || ($item_valor_adicionais > 0)) $sql_up_item = sqlsrv_query($conexao, "UPDATE TOP (1) loja_itens SET LI_VALOR_TRANSFER='$item_valor_transfer', LI_VALOR_ADICIONAIS='$item_valor_adicionais' WHERE LI_COD='$item'", $conexao_params, $conexao_options);
					
					$item_anterior = $carrinho['item'];
				} //for iitemvaga
			}
		}
		
		if($delivery) {
			$del_ar = array();
			//Buscar codigos dos itens
			foreach ($_SESSION['compra-modificar'][$compra] as $delk => $del) { array_push($del_ar, $del['item']); }
			$sql_vendas_adicionais_delivery = sqlsrv_query($conexao, "SELECT TOP 1 v.*, vv.* FROM vendas_adicionais v, vendas_adicionais_valores vv WHERE v.VA_NOME_EXIBICAO='delivery' AND vv.VAV_VENDA IN(".implode(",", $del_ar).") AND vv.VAV_ADICIONAL=v.VA_COD AND vv.VAV_BLOCK=0 AND vv.D_E_L_E_T_=0 AND v.VA_BLOCK=0 AND v.D_E_L_E_T_=0 ORDER BY vv.VAV_INCLUSO DESC, vv.VAV_VALOR DESC, v.VA_COD ASC", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_vendas_adicionais_delivery) !== false) {
				$vendas_adicionais_delivery = sqlsrv_fetch_array($sql_vendas_adicionais_delivery);
				$vendas_adicionais_delivery_cod = $vendas_adicionais_delivery['VA_COD'];
				$vendas_adicionais_delivery_opcoes_cod = $vendas_adicionais_delivery['VAV_COD'];
				$vendas_adicionais_delivery_opcoes_valor = $vendas_adicionais_delivery['VAV_VALOR'];
				if($vendas_adicionais_delivery_multi) $vendas_adicionais_delivery_opcoes_valor = $vendas_adicionais_delivery_opcoes_valor * $carrinho['qtde'];
				$vendas_adicionais_delivery_opcoes_incluso = (bool) $vendas_adicionais_delivery['VAV_INCLUSO'];
				$vendas_adicionais_delivery_opcoes_incluso_int = $vendas_adicionais_delivery_opcoes_incluso ? 1 : 0;
				$vendas_adicionais_delivery['incluso'] = ($vendas_adicionais_delivery_opcoes_incluso) ? 1 : 0;
				$vendas_adicionais_delivery['cod'] = $vendas_adicionais_delivery_cod;
				$vendas_adicionais_delivery['label'] = $vendas_adicionais_delivery_label;
				//$vendas_adicionais_delivery['valor'] = $vendas_adicionais_delivery_opcoes_valor;
				$vendas_adicionais_delivery['valor'] = '0.00';
				if(!$vendas_adicionais_delivery['incluso']) $valor_adicionais += $vendas_adicionais_delivery['valor'];
				
				$sql_ins_delivery = sqlsrv_query($conexao, "INSERT INTO loja_itens_adicionais (LIA_COMPRA, LIA_ITEM, LIA_ADICIONAL, LIA_VALOR, LIA_INCLUSO) VALUES ($compra, $item, ".$vendas_adicionais_delivery['cod'].", '".$vendas_adicionais_delivery['valor']."', ".$vendas_adicionais_delivery['incluso'].")", $conexao_params, $conexao_options);
				#$link_retorno = SITE.'compras/delivery/'.$compra.'/';
				#if($transfer_redirect) $link_retorno .= 'transfer/';
			}
		}
		
		
		$valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;
		$desconto = $desconto_folia = $desconto_frisa = 0;
		//Desconto RTA
		/*if(($parceiro == 54) && $loja_qtde_folia >= 2) {
			$desconto = 1;
			// $valor = (90 * $valor) / 100;
			$desconto_especial_folia = (10 * $valor) / 100;
			$valor = $valor - $desconto_especial_folia;
		}*/
		$loja_combo_desconto = 0;
		//if($canal == 54) {
			foreach ($loja_qtde_combo as $k => $r) {
				if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
					$loja_combo_desconto = $combo_dias[$k]['desconto'];
					$loja_combo_nome = $combo_dias[$k]['nome'];
				}
			}
			if(!empty($folia) && ($loja_combo_desconto > 0)) {
				$desconto = $desconto_folia = 1;
				$desconto_especial_folia = ($loja_combo_desconto * $valor) / 100;
				$valor = $valor - $desconto_especial_folia;
			}
			
		//}
		
		if(!empty($frisa) && ($loja_qtde_frisa > 0)) {
			$desconto = $desconto_frisa = 1;
			// $valor = $valor - ($loja_qtde_frisa * 50);
			$desconto_especial_frisa = $loja_qtde_frisa * 50;
			$valor = $valor - $desconto_especial_frisa;
		}
		if($desconto) $valor_desconto = $valor_desconto + $desconto_especial_folia + $desconto_especial_frisa;
		
		//Atualizar valores	 
		#$sql_update_compra = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_PARCIAL='$valor_parcial', LO_VALOR_TOTAL='$valor', LO_VALOR_INGRESSOS='$valor_ingressos', LO_VALOR_ADICIONAIS='$valor_adicionais', LO_DELIVERY='$delivery', LO_NUM_ITENS='$n_itens', LO_DESCONTO='$desconto' WHERE LO_COD='$compra'", $conexao_params, $conexao_options);
		//Atualizar valores	 
		if(!$delivery && !empty($data_retirada)) $update_data = ", LO_CLI_DATA_ENTREGA='$data_retirada' ";

		if($forma > 0)
		{
			$pago = 0;
		}
		else 
		{
			$pago = 1;
		}


		//Verifica se a forma de pagamento é múltipla
		
		// $sql_data_compra = sqlsrv_query($conexao, "SELECT TOP 1 LO_FORMA_PAGAMENTO FROM loja WHERE LO_COD='$compra'", $conexao_params, $conexao_options);
		// if(sqlsrv_num_rows($sql_data_compra) > 0) 
		// {
		// 	$forma_anterior = sqlsrv_fetch_array($sql_data_compra);

		// 	if ($forma_anterior != $forma) {
		// 		# code...
		// 	}
		// }
		if ($forma == 10) 
		{
			$link_retorno = SITE.'compras/pagamento-multiplo/'.$compra.'/';
			$_SESSION['exclui_multiplo'] = true;
		}



		$sql_update_compra = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET 
			LO_CLIENTE=$cliente,
			LO_PARCEIRO=$canal,
			LO_FORMA_PAGAMENTO='$forma',
			LO_PAGO='$pago',
			LO_COMISSAO=$comissao,
			LO_COMISSAO_RETIDA=$retida,
			LO_DEADLINE=$deadline,
			LO_CONCIERGE=$vendedor_externo,
			LO_ORIGEM='$origem',
			LO_NUM_ITENS='$n_itens',
			LO_VALOR_PARCIAL='$valor_parcial',
			LO_VALOR_TOTAL='$valor',
			LO_VALOR_INGRESSOS='$valor_ingressos_base',
			LO_VALOR_ADICIONAIS='$valor_adicionais',
			LO_DESCONTO='$desconto',
			LO_DESCONTO_FRISA='$desconto_frisa',
			LO_DESCONTO_FOLIA='$desconto_folia',
			LO_DELIVERY='$delivery',
			LO_VALOR_TRANSFER='$valor_transfer', 
			LO_VALOR_DELIVERY='$delivery_valor', 
			LO_VALOR_DESCONTO='$valor_desconto', 
			LO_VALOR_OVER_INTERNO='$valor_over_interno', 
			LO_VALOR_OVER_EXTERNO='$valor_over_externo',
			
			LO_RETIRADA='$retirada',
			LO_CLI_PERIODO='$periodo'
			$update_data
			
			WHERE LO_COD='$compra'", $conexao_params, $conexao_options);
		unset($_SESSION['compra-modificar'][$compra]);
		//-----------------------------------------------------------------//
		//Apenas para homologação
		// $link_retorno = SITE.'compras/pagamento/'.$compra.'/';
		//-----------------------------------------------------------------//
		?>
		<script type="text/javascript">
			alert('Compra modificada');
			location.href='<? echo $link_retorno; ?>';
		</script>
		<?
		//Fechar conexoes
		include("conn/close.php");
		include("conn/close-sankhya.php");
		
		exit();
	}
	// Inserir Canal de venda
}
?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>