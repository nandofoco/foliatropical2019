<?

header('Content-Type: text/html; charset=utf-8');

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$cupom = (int) $_GET['cupom'];
$v2 = (isset($_GET['v2'])) ? 'v2/' : '' ;

$cliente = $_SESSION['usuario-cod'];

//-----------------------------------------------------------------------------//

if(!empty($cod) && !empty($cupom)) {

	$loja_qtde_folia = 0;
	
	//Buscar o código do folia
	$sql_folia_cod = sqlsrv_query($conexao, "SELECT TOP 1 TI_COD FROM tipos WHERE TI_TAG='lounge'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_folia_cod) > 0) {
		$loja_cod_folia_ar = sqlsrv_fetch_array($sql_folia_cod);
		$loja_cod_folia = $loja_cod_folia_ar['TI_COD'];
	}

	unset($_SESSION['compra-modificar'][$cod]);

	$sql_item = sqlsrv_query($conexao, "SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_item) > 0) {

		$i = 0;
		while ($item = sqlsrv_fetch_array($sql_item)) {
			$item_cod = $item['COD'];
			$item_qtde = $item['QTDE'];
			$item_ingresso = $item['LI_INGRESSO'];
			$item_desconto = $item['LI_DESCONTO'];
			$item_over_interno = $item['LI_OVER_INTERNO'];
			$item_over_externo = $item['LI_OVER_EXTERNO'];
			$item_valor =  $item['LI_VALOR'];
			$item_exclusividade = (bool) $item['EXCLUSIVIDADE'];
			$item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];


			//Informações adicionais do item
			$sql_info_item = sqlsrv_query($conexao, "
			SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, tp.TI_NOME, tp.TI_TAG 
			FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
			WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

			if(sqlsrv_num_rows($sql_info_item) > 0) {
				$info_item = sqlsrv_fetch_array($sql_info_item);
			
				$item_setor = utf8_encode($info_item['ES_NOME']);
				$item_dia = utf8_encode($info_item['ED_NOME']);
				$item_tipo = utf8_encode($info_item['TI_NOME']);
				$item_tag = utf8_encode($info_item['TI_TAG']);
				
				$item_fila = utf8_encode($info_item['VE_FILA']);
				$item_vaga = utf8_encode($info_item['VE_VAGAS']);
				$item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);

				$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

				$item_data_n = $info_item['ED_DATA'];
				$item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));
			}

			if(($item_tag == 'lounge') && (!in_array($item_data_n, $dias_candybox))) {

				// loja_itens_adicionais
				if($item_fechado) $item_qtde = $item_qtde / $item_vaga;

				$_SESSION['compra-modificar'][$cod][$i]['cod'] = $item_cod;
				$_SESSION['compra-modificar'][$cod][$i]['item'] = $item_ingresso;
				$_SESSION['compra-modificar'][$cod][$i]['valorbase'] = $item_valor - $item_over_externo - $item_over_interno;
				$_SESSION['compra-modificar'][$cod][$i]['valor'] = $item_valor;
				$_SESSION['compra-modificar'][$cod][$i]['desconto'] = $item_desconto;
				$_SESSION['compra-modificar'][$cod][$i]['overexterno'] = $item_over_externo;
				$_SESSION['compra-modificar'][$cod][$i]['overinterno'] = $item_over_interno;
				$_SESSION['compra-modificar'][$cod][$i]['qtde'] = $item_qtde;
				$_SESSION['compra-modificar'][$cod][$i]['estoque'] = $item_qtde;
				$_SESSION['compra-modificar'][$cod][$i]['exclusividade'] = $item_exclusividade;
				$_SESSION['compra-modificar'][$cod][$i]['exclusividade-val'] = $item_exclusividade_val;

				$i++;

			}

		}
		unset($i);
		ksort($_SESSION['compra-modificar'][$cod]);

	}

	if(count($_SESSION['compra-modificar'][$cod]) > 0) {


		// Excluir itens referentes a compra atual
		$sql_ins_item = sqlsrv_query($conexao, "DELETE FROM loja_itens WHERE LI_COMPRA='$cod'", $conexao_params, $conexao_options);
		$sql_ins_comentarios = sqlsrv_query($conexao, "DELETE FROM loja_comentarios WHERE LC_COMPRA='$cod'", $conexao_params, $conexao_options);
		$sql_ins_comentarios_internos = sqlsrv_query($conexao, "DELETE FROM loja_comentarios_internos WHERE LC_COMPRA='$cod'", $conexao_params, $conexao_options);
		$sql_ins_adicionais = sqlsrv_query($conexao, "DELETE FROM loja_itens_adicionais WHERE LIA_COMPRA='$cod'", $conexao_params, $conexao_options);

		$valor = 0.00;
		$valor_ingressos = 0.00;
		$valor_ingressos_base = 0.00;
		$valor_adicionais = 0.00;
		$valor_desconto = 0.00;
		$valor_transfer = 0.00;
		$valor_over_interno = 0.00;
		$valor_over_externo = 0.00;

		$loja_qtde_folia = 0;
		
		foreach ($_SESSION['compra-modificar'][$cod] as $key => $carrinho) {
			// $_SESSION['compra-modificar'][$cod][$key]['qtde'] = $carrinho['qtde'] = (int) $quantidade[$key];
			
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

						if(($item_tipo == $loja_cod_folia) && (in_array($item_data, $dias_principais))){
							//Adicionamos na quantidade e excluimos do array
							$loja_qtde_folia++;
							foreach ($dias_principais as $key_dia => $item_dia_atual) {
								if ($item_dia_atual == $item_data) unset($dias_principais[$key_dia]);
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

					$sql_ins_item = sqlsrv_query($conexao, "INSERT INTO loja_itens (LI_COMPRA, LI_INGRESSO, LI_NOME, LI_ID, LI_VALOR_TABELA, LI_VALOR, LI_EXCLUSIVIDADE, LI_EXCLUSIVIDADE_VAL, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO) VALUES ($cod, ".$carrinho['item'].", '$nome_cliente', $item_id, '$item_valor_tabela', '".$carrinho['valor']."', '$excl', '$exclval', '$item_desconto', '$item_overinterno', '$item_overexterno')", $conexao_params, $conexao_options);
					$item = getLastId();
									
					//Adicionar valor exclusividade
					if(($iitemvaga == 1) && $excl) {
						$valor_adicionais += $item_valor_exclusividade;
						$item_valor_adicionais += $item_valor_exclusividade;
					}

					// Cadastrar comentarios
					$item_comentario = format($comentarios[$key]);
					if(!empty($item_comentario)) $sql_ins_comentarios = sqlsrv_query($conexao, "INSERT INTO loja_comentarios (LC_COMPRA, LC_ITEM, LC_COMENTARIO) VALUES ($cod, $item, '$item_comentario')", $conexao_params, $conexao_options);

					// Cadastrar comentarios internos
					$item_comentario_interno = format($comentariosinternos[$key]);
					if(!empty($item_comentario_interno)) $sql_ins_comentarios_internos = sqlsrv_query($conexao, "INSERT INTO loja_comentarios_internos (LC_COMPRA, LC_ITEM, LC_COMENTARIO) VALUES ($cod, $item, '$item_comentario_interno')", $conexao_params, $conexao_options);

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
										$sql_ins_adicionais = sqlsrv_query($conexao, "INSERT INTO loja_itens_adicionais (LIA_COMPRA, LIA_ITEM, LIA_ADICIONAL, LIA_VALOR, LIA_INCLUSO) VALUES ($cod, $item, $vendas_adicionais_cod, '$vendas_adicionais_opcoes_valor', $vendas_adicionais_opcoes_incluso_int)", $conexao_params, $conexao_options);
										
									}
									

									/*if(($vendas_adicionais_nome_exibicao == 'transfer') || ($vendas_adicionais_opcoes_incluso)) {
										$transfer_redirect = true;
										$link_retorno = SITE.$link_lang.'agendamentos/editar/'.$cod.'/';
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
			foreach ($_SESSION['compra-modificar'][$cod] as $delk => $del) { array_push($del_ar, $del['item']); }

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
				
				$sql_ins_delivery = sqlsrv_query($conexao, "INSERT INTO loja_itens_adicionais (LIA_COMPRA, LIA_ITEM, LIA_ADICIONAL, LIA_VALOR, LIA_INCLUSO) VALUES ($cod, $item, ".$vendas_adicionais_delivery['cod'].", '".$vendas_adicionais_delivery['valor']."', ".$vendas_adicionais_delivery['incluso'].")", $conexao_params, $conexao_options);
			}

		}
		
		
		$valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;

		$desconto = 0;
		//Desconto RTA
		if(($parceiro == 54) && $loja_qtde_folia >= 2) {
			$desconto = 1;
			$desconto_especial_folia = (10 * $valor) / 100;
			$valor = $valor - $desconto_especial_folia;
		}
		
		if($desconto) $valor_desconto = $valor_desconto + $desconto_especial_folia;
		
		//Atualizar valores	 
		#$sql_update_compra = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_PARCIAL='$valor_parcial', LO_VALOR_TOTAL='$valor', LO_VALOR_INGRESSOS='$valor_ingressos', LO_VALOR_ADICIONAIS='$valor_adicionais', LO_DELIVERY='$delivery', LO_NUM_ITENS='$n_itens', LO_DESCONTO='$desconto' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);
		//Atualizar valores	 
		if(!$delivery && !empty($data_retirada)) $update_data = ", LO_CLI_DATA_ENTREGA='$data_retirada' ";

		$sql_update_compra = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET 
			LO_CLIENTE=$cliente,
			LO_PARCEIRO=$canal,
			LO_FORMA_PAGAMENTO='$forma',
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

			LO_VALOR_TRANSFER='$valor_transfer', 
			LO_VALOR_DELIVERY='$delivery_valor', 
			LO_VALOR_DESCONTO='$valor_desconto', 
			LO_VALOR_OVER_INTERNO='$valor_over_interno', 
			LO_VALOR_OVER_EXTERNO='$valor_over_externo',
			
			LO_RETIRADA='$retirada',
			LO_CLI_PERIODO='$periodo'
			$update_data
			
			WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

		unset($_SESSION['compra-modificar'][$cod]);

	}

	$_SESSION['compra-cupom']['usuario'] = $cliente;
	$_SESSION['compra-cupom']['cod'] = $cupom;

	?>
	<script type="text/javascript">
		location.href='<? echo SITE.$link_lang; ?>ingressos/pagamento/<? echo $v2.$cod; ?>/';
	</script>
	<?

	//fechar conexao com o banco
	include("conn/close.php");
	include("conn/close-mssql.php");

	exit();

}

?>
<script type="text/javascript">
	alert('Ocorreu um erro, tente novamente!');
	history.go(-1);
</script>