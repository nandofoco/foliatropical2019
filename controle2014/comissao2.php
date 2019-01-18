<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];

$tipo = format($_GET['t']);
switch ($tipo) {
	case 'pendentes': $search_pendentes = " AND LO_COMISSAO_RETIDA='0' AND LO_COMISSAO_PAGA='0' "; break;
	case 'retidas': $search_pendentes = " AND LO_COMISSAO_RETIDA='1' "; break;
	case 'pagas': $search_pendentes = " AND LO_COMISSAO_PAGA='1' "; break;
	default: unset($tipo);
}

//-----------------------------------------------------------------//

$q = format($_GET['q']);
if(!empty($q)) {

	if(!is_numeric($q)) {

		// $search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$search_query = " AND NOMEPARC LIKE '%$q%' ";

		//$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, CLIENTE, VENDEDOR FROM TGFPAR WHERE (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, CLIENTE, VENDEDOR FROM TGFPAR WHERE VENDEDOR='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_clientes_cods = $ar_parceiros_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) {
				if($cods['CLIENTE'] == 'S') array_push($ar_clientes_cods, $cods['CODPARC']);
				if($cods['VENDEDOR'] == 'S') array_push($ar_parceiros_cods, $cods['CODPARC']);
			}
			
			$search = " AND (";

			if(count($ar_clientes_cods) > 0) {
				$clientes_cods = implode(",", $ar_clientes_cods);
				$search .= " LO_CLIENTE IN ($clientes_cods) ";
			}
			if(count($ar_parceiros_cods) > 0) {
				$parceiros_cods = implode(",", $ar_parceiros_cods);
				if(count($ar_clientes_cods) > 0) $search .= " OR ";
				$search .= " LO_PARCEIRO IN ($parceiros_cods) ";
			}

			$search .= ") ";

		} else {
			// $search = " AND LO_CLIENTE IN ('') ";
			$nosearch = true;
		}

	} else {
		$search = " AND LO_COD='$q' ";
	}
}


// Formas de pagamento
$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_formas_pagamento)){
	while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) { 
		$forma_pagamento = $ar_formas_pagamento['FP_NOME'];
		$formas_pagamento[$ar_formas_pagamento['FP_COD']] = ($forma_pagamento == utf8_decode('Cartão de Crédito')) ? utf8_decode('Cartão Crédito') : $forma_pagamento;
	}
}

//-----------------------------------------------------------------//

$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;

// $sql_loja = sqlsrv_query($conexao, "SELECT l.*, p.FP_NOME, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, (CONVERT(VARCHAR, l.LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO FROM loja l, formas_pagamento p WHERE l.LO_PAGO='1' AND l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND  l.LO_FORMA_PAGAMENTO=p.FP_COD $search ORDER BY l.LO_DATA_COMPRA DESC", $conexao_params, $conexao_options);

if(!$nosearch) {
	$sql_loja = sqlsrv_query($conexao, "

		DECLARE @PageNumber INT;
		DECLARE @PageSize INT;
		DECLARE @TotalPages INT;

		SET @PageSize = 20;
		SET @PageNumber = $p;

		IF @PageNumber = 0 BEGIN
		SET @PageNumber = 1
		END;

		SET @TotalPages = CEILING(CONVERT(NUMERIC(20,10), ISNULL((SELECT COUNT(*) FROM loja (NOLOCK) WHERE 
		LO_PAGO='1' AND LO_COMISSAO>0 AND LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search $search_pendentes), 0)) / @PageSize);

		WITH cadastro(NumeroLinha, LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_INGRESSOS, LO_VALOR_DESCONTO, LO_VALOR_OVER_INTERNO, LO_VALOR_OVER_EXTERNO, LO_ENVIADO, LO_DATA_COMPRA, LO_COMISSAO, LO_COMISSAO_RETIDA, LO_COMISSAO_PAGA, CP_CUPOM, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA)
		AS (
		SELECT ROW_NUMBER() OVER (ORDER BY LO_COD DESC) AS NumeroLinha,
		LO_COD, 
		LO_CLIENTE, 
		LO_PARCEIRO, 
		LO_FORMA_PAGAMENTO, 
		LO_STATUS_TRANSACAO, 
		LO_VALOR_INGRESSOS,
		LO_VALOR_DESCONTO,
		LO_VALOR_OVER_INTERNO,
		LO_VALOR_OVER_EXTERNO,
		LO_ENVIADO,
		LO_DATA_COMPRA,
		LO_COMISSAO,
		LO_COMISSAO_RETIDA,
		LO_COMISSAO_PAGA,
		CP_CUPOM,
		(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, 
		(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO,
		(SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA_MINI, 
		(SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO_MINI, 
		ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA
		FROM cupom, loja (NOLOCK) WHERE LO_COD=CP_COMPRA and
		LO_PAGO='1' AND LO_COMISSAO>0 AND LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search $search_pendentes
		)

		SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, LO_COD, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_INGRESSOS, LO_VALOR_DESCONTO, LO_VALOR_OVER_INTERNO, LO_VALOR_OVER_EXTERNO, LO_ENVIADO, LO_COMISSAO, LO_COMISSAO_RETIDA, LO_COMISSAO_PAGA, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA
		FROM cadastro
		WHERE NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize )
		ORDER BY LO_DATA_COMPRA DESC

		", $conexao_params, $conexao_options);
	$n_loja = sqlsrv_num_rows($sql_loja);
} else {
	$n_loja = false;
}

?>
<section id="conteudo">
	<header class="titulo">
		<h1>
			Comissões de Parceiros
			<span class="expedicao">
				<a href="<? echo SITE; ?>comissao/pendentes/" <? if($tipo == 'pendentes') { echo 'class="checked"'; } ?>>Pendentes</a> • 
				<a href="<? echo SITE; ?>comissao/retidas/" <? if($tipo == 'retidas') { echo 'class="checked"'; } ?>>Retidas</a> • 
				<a href="<? echo SITE; ?>comissao/pagas/" <? if($tipo == 'pagas') { echo 'class="checked"'; } ?>>Pagas</a>
			</span>
		</h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>comissao/">
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>comissao/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>
	</header>
	<section class="secao bottom">
		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first"><strong>VCH</strong><span></span></th>
					<th><strong>Parceiro</strong><span></span></th>
					<th><strong>Forma Pgto</strong><span></span></th>
					<th class="right"><span></span><strong>Total (R$)</strong></th>
					<th class="right"><span></span><strong>Over (R$)</strong></th>
					<th class="right"><span></span><strong>Comissão (R$)</strong></th>
					<th class="right"><span></span><strong>Comissao (%)</strong></th>
					<th class="center padding" title="Retida"><strong>Ret.</strong></th>
					<th class="center"><strong>Paga</strong></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="9">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_loja !== false) {

				$i=1;
				while($loja = sqlsrv_fetch_array($sql_loja)) {

					//Total de paginas
					$total_paginas = $loja['TOTAL'];

					$loja_cod = $loja['LO_COD'];
					$loja_data = $loja['DATA'];
					$loja_data_pagamento = $loja['DATA_PAGAMENTO'];
					$loja_data_mini = $loja['DATA_MINI'];
					$loja_data_pagamento_mini = $loja['DATA_PAGAMENTO_MINI'];
					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_parceiro_cod = $loja['LO_PARCEIRO'];
					$loja_tipo_pagamento = $loja['LO_FORMA_PAGAMENTO'];
					$loja_comissao = $loja['LO_COMISSAO'];
					//$loja_valor = $loja['LO_VALOR_INGRESSOS'];
					$loja_over = $loja['LO_VALOR_OVER_INTERNO'];
					$loja_over_externo = $loja['LO_VALOR_OVER_EXTERNO'];
					$loja_valor_desconto = $loja['LO_VALOR_DESCONTO'];
					$loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
					$loja_comissao_paga = (bool) $loja['LO_COMISSAO_PAGA'];
					$loja_entrega = (bool) $loja['LO_ENVIADO'];
					$loja_block = (bool) $loja['LO_BLOCK'];
					$entrega = ($loja_entrega) ? 'ativo' : 'ativar';			
					$acao_entrega = ($loja_entrega) ? 'cancelar' : 'confirmar';

					$sql_valor_ingressos = sqlsrv_query($conexao, "SELECT SUM(LI_VALOR_TABELA) AS INGRESSOS FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
			      	if(sqlsrv_num_rows($sql_valor_ingressos) > 0) {
			      		$loja_valor_ingressos_ar = sqlsrv_fetch_array($sql_valor_ingressos);
			      		$loja_valor_ingressos = $loja_valor_ingressos_ar['INGRESSOS'];
			      	}

					$loja_valor_total = number_format(($loja_valor_ingressos - $loja_valor_desconto + $loja_over), 2, ",", ".");
					//if($loja_cod == '4314') echo " (($loja_valor_ingressos - $loja_valor_desconto + $loja_valor_over_interno) * $loja_comissao / 100) ";
					$loja_comissao_valor = number_format((($loja_valor_ingressos - $loja_valor_desconto + $loja_over) * $loja_comissao / 100), 2, ",", ".");
					$loja_over = number_format($loja_over, 2, ",", ".");
					$loja_over_externo = number_format($loja_over_externo, 2, ",", ".");

					// $loja_forma_pagamento = utf8_encode($loja['FP_NOME']);
					$loja_forma_pagamento = utf8_encode($formas_pagamento[$loja_tipo_pagamento]);

					unset($loja_cliente, $loja_cliente_exibir, $loja_parceiro, $loja_parceiro_exibir);
					
					// $loja_cliente = utf8_encode($loja['CL_NOME']);
					$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 2 NOMEPARC, CODPARC FROM TGFPAR WHERE CODPARC IN ('$loja_cliente_cod','$loja_parceiro_cod') AND (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cliente) > 0) {
						while($loja_cliente_ar = sqlsrv_fetch_array($sql_cliente)) {
							switch ($loja_cliente_ar['CODPARC']) {
								case $loja_cliente_cod:
									$loja_cliente = trim($loja_cliente_ar['NOMEPARC']);
									$loja_cliente_exibir = (strlen($loja_cliente) > 20) ? substr($loja_cliente, 0, 20)."..." : $loja_cliente;									
								break;

								case $loja_parceiro_cod:
									$loja_parceiro = trim($loja_cliente_ar['NOMEPARC']);
									$loja_parceiro_exibir = (strlen($loja_cliente) > 20) ? substr($loja_parceiro, 0, 20)."..." : $loja_parceiro;									
								break;
							}							
						}
					}


					//buscar itens
					$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_itens = sqlsrv_num_rows($sql_itens);
					?>
						<tr <? if ($loja_block){ echo 'class="block"'; } ?>>
							<td class="first detalhes-voucher" data-cod="<? echo $loja_cod; ?>" data-cancelado="false">
								<div class="relative">
									<? echo $loja_cod; ?>
									<section class="detalhes"></section>
								</div>
							</td>
							<td <? if($loja_parceiro != $loja_parceiro_exibir) { echo 'title="'.utf8_encode($loja_parceiro).'"'; } ?>>
								<? echo utf8_encode($loja_parceiro_exibir); ?>
							</td>
							<td><? echo $loja_forma_pagamento; ?></td>
							<td class="valor"><? echo $loja_valor_total; ?></td>
							<td class="valor"><? echo $loja_over_externo; ?></td>
							<td class="valor"><? echo $loja_comissao_valor; ?></td>
							<td class="valor"><? echo $loja_comissao; ?>%</td>
							<td class="ctrl small marcar"><a href="<? echo SITE; ?>e-comissao-gerenciar.php?c=<? echo $loja_cod; ?>&t=retida&a=<? echo $loja_comissao_retida ? 'cancelar' : 'confirmar' ; ?>" class="<? if($loja_comissao_retida) { echo 'ativo '; } ?>ativar" onClick="return confirm('Deseja realmente <? echo $loja_comissao_retida ? 'cancelar' : 'confirmar' ; ?> o recolhimento da comissão');"></a></td>
							<td class="ctrl small marcar no-padding"><a href="<? echo SITE; ?>e-comissao-gerenciar.php?c=<? echo $loja_cod; ?>&t=paga&a=<? echo $loja_comissao_paga ? 'cancelar' : 'confirmar' ; ?>" class="<? if($loja_comissao_paga) { echo 'ativo '; } ?>ativar" onClick="return confirm('Deseja realmente <? echo $loja_comissao_paga ? 'cancelar' : 'confirmar' ; ?> o pagamento da comissão');"></a></td>
							<td class="ctrl small no-padding detalhes"><a href="<? echo SITE; ?>financeiro/detalhes/<? echo $loja_cod; ?>/" class="ver"></a></td>
						</tr>
					<?
					$i++;
				}

				$exibe_loja = true;
			} 
			if(!$exibe_loja) {
			?>
				<tr>
					<td colspan="9" class="nenhum">Nenhum pagamento confirmado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($exibe_loja) { ?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>comissao/?<? if(!empty($tipo)) echo 't='.$tipo.'&'; if(!empty($q)) { echo (!empty($tipo)) ? '&': '?'; echo 'q='.urlencode(utf8_encode($q)); } ?>" class="first"></a>
	        <a href="<? echo SITE; ?>comissao/?<? if(!empty($tipo)) echo 't='.$tipo.'&'; if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>comissao/?<? if(!empty($tipo)) echo 't='.$tipo.'&'; if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>comissao/?<? if(!empty($tipo)) echo 't='.$tipo.'&'; if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
	        <!-- <input type="hidden" class="pagesize" value="30" /> -->
        </div>
        <? } ?>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>