<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$adm = ($_SESSION['us-grupo'] == 'ADM') ? true : false;

define('CODRESERVA','5');
define('CODPERMUTA','8,9');

//-----------------------------------------------------------------//

$tipo = $_GET['tipo'];
$dia = (int) $_GET['dia'];
$setor = (int) $_GET['setor'];
$fila = format($_GET['fila']);
$acao = format($_GET['a']);

$q = format($_POST['q']);
if(!empty($q)) {
	if(!is_numeric($q)) {
		// $search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$search_query = " AND NOMEPARC LIKE '%$q%' ";
		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC, CLIENTE, VENDEDOR FROM TGFPAR WHERE (CLIENTE='S' OR VENDEDOR='S') AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		// $sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_clientes_cods = $ar_parceiros_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) {
				if($cods['CLIENTE'] == 'S') array_push($ar_clientes_cods, $cods['CODPARC']);
				if($cods['VENDEDOR'] == 'S') array_push($ar_parceiros_cods, $cods['CODPARC']);
			}
			
			$busca = " AND (";
			if(count($ar_clientes_cods) > 0) {
				$clientes_cods = implode(",", $ar_clientes_cods);
				$busca .= " LO_CLIENTE IN ($clientes_cods) ";
			}
			if(count($ar_parceiros_cods) > 0) {
				$parceiros_cods = implode(",", $ar_parceiros_cods);
				if(count($ar_clientes_cods) > 0) $busca .= " OR ";
				$busca .= " LO_PARCEIRO IN ($parceiros_cods) ";
			}
			$busca .= ") ";
		} else {
			// $busca = " AND LO_CLIENTE IN ('') ";
			$nosearch = true;
		}
	} else {
		$busca = " AND LO_COD='$q' ";
	}
}

$search_cods = " SELECT l.LI_COMPRA FROM loja_itens l, vendas v WHERE v.VE_COD=l.LI_INGRESSO AND l.D_E_L_E_T_='0' " ;

$search = "SELECT (v.VE_TIPO_ESPECIFICO) AS ESPECIFICO, (v.VE_VAGAS) AS VAGAS, (li.LI_COMPRA), 
((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) - ((li.LI_VALOR_TABELA - li.LI_DESCONTO + li.LI_OVER_INTERNO) * lo.LO_COMISSAO / 100) - ISNULL(CASE WHEN tx.TX_TAXA IS NOT NULL THEN li.LI_VALOR * (tx.TX_TAXA / 100) ELSE 0 END,0)) AS valor_dia 
FROM loja_itens li, vendas v, loja lo
LEFT JOIN taxa_cartao tx
			ON (lo.LO_FORMA_PAGAMENTO=1 AND lo.LO_CARTAO=tx.TX_CARTAO AND lo.LO_PARCELAS >= tx.TX_PARCELAS_INICIO AND lo.LO_PARCELAS <= tx.TX_PARCELAS_FIM)
			OR (lo.LO_FORMA_PAGAMENTO=6 AND tx.TX_CARTAO='pos')
			OR (lo.LO_FORMA_PAGAMENTO=2014 AND tx.TX_CARTAO='pos')
WHERE lo.LO_COD=li.LI_COMPRA AND v.VE_COD=li.LI_INGRESSO AND li.D_E_L_E_T_='0' ";

/*if(!empty($tipo)) $search .= " AND v.VE_TIPO='$tipo' ";
if(!empty($dia)) $search .= " AND v.VE_DIA='$dia' ";
if(!empty($setor)) $search .= " AND v.VE_SETOR='$setor' ";
if(!empty($fila)) $search .= " AND v.VE_FILA='$fila' ";

switch ($acao) {
	case 'pagos':
		$search_acao = "AND LO_PAGO='1' AND LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") ";
	break;	
	case 'aguardando':
		$search_acao = "AND LO_PAGO='0' AND LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND LO_FORMA_PAGAMENTO<>'".CODRESERVA."' ";
	break;
	case 'reservados':
		$search_acao = "AND LO_PAGO='0' AND LO_FORMA_PAGAMENTO NOT IN (".CODPERMUTA.") AND LO_FORMA_PAGAMENTO='".CODRESERVA."' ";
	break;
	case 'permuta':
		$search_acao = "AND LO_FORMA_PAGAMENTO IN (".CODPERMUTA.") ";
	break;
}*/

include("include/relatorios-parametros.php");

if(!empty($filtros['tipos'][$tipo])) $search_cods .= " AND ".$filtros['tipos'][$tipo];
if(!empty($dia)) $search_cods .= " AND VE_DIA=".$dia;
if(!empty($filtros['status'][$acao])) $search_acao .= " AND ".$filtros['status'][$acao];

$search_acao = str_replace('lo.', '', $search_acao);

/*$sql_loja = sqlsrv_query($conexao, "SELECT 
	LO_COD, 
	LO_CLIENTE, 
	LO_PARCEIRO, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_TOTAL,
	LO_ENVIADO,
	LO_DATA_COMPRA,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, 
	(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO,
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA_MINI, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO_MINI, 
	ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA
	FROM loja WHERE LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' AND LO_COD IN ($search) $search_acao
	ORDER BY LO_DATA_COMPRA DESC

	", $conexao_params, $conexao_options);*/

$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;

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
	LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' AND LO_COD IN ($search_cods) $search_acao), 0)) / @PageSize);

	WITH cadastro(NumeroLinha, LO_COD, LO_VENDEDOR, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_ENVIADO, LO_DATA_COMPRA, DATA_ENTREGA, DEADLINE, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA, LO_ENTREGUE, LO_RETIRADA, LO_ENTREGUE_NOME)
	AS (
	SELECT ROW_NUMBER() OVER (ORDER BY LO_COD DESC) AS NumeroLinha,
	LO_COD, 
	LO_VENDEDOR,
	LO_CLIENTE, 
	LO_PARCEIRO, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_TOTAL,
	LO_ENVIADO,
	LO_DATA_COMPRA,
	SUBSTRING(CONVERT(VARCHAR, LO_CLI_DATA_ENTREGA, 103),1,5) AS DATA_ENTREGA,
	/*SUBSTRING(CONVERT(VARCHAR, LO_DEADLINE, 103),1,5) AS DEADLINE,*/
	LO_DEADLINE,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA, 
	(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO,
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA_MINI, 
	(SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103),1,5)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO_MINI, 
	ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA,
	LO_ENTREGUE,
	LO_RETIRADA,
	LO_ENTREGUE_NOME
	FROM loja WHERE LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' AND LO_COD IN ($search_cods) $search_acao $busca
	)

	SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, LO_COD, LO_VENDEDOR, LO_CLIENTE, LO_PARCEIRO, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_ENVIADO, LO_DATA_COMPRA, DATA_ENTREGA, DEADLINE, DATA, DATA_PAGAMENTO, DATA_MINI, DATA_PAGAMENTO_MINI, DIFERENCA, LO_ENTREGUE, LO_RETIRADA, LO_ENTREGUE_NOME
	FROM cadastro
	WHERE NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize )
	ORDER BY LO_DATA_COMPRA DESC

	", $conexao_params, $conexao_options);

$n_loja = sqlsrv_num_rows($sql_loja);



// Formas de pagamento
$sql_formas_pagamento = sqlsrv_query($conexao, "SELECT FP_COD, FP_NOME FROM formas_pagamento WHERE D_E_L_E_T_=0 ORDER BY FP_NOME ASC", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_formas_pagamento)){
	while ($ar_formas_pagamento = sqlsrv_fetch_array($sql_formas_pagamento)) { 
		$forma_pagamento = $ar_formas_pagamento['FP_NOME'];
		$formas_pagamento[$ar_formas_pagamento['FP_COD']] = ($forma_pagamento == utf8_decode('Cartão de Crédito')) ? utf8_decode('Cartão Crédito') : $forma_pagamento;
	}
}


?>
<section id="conteudo" class="relatorio-lista-voucher wide">
	<!-- <header class="titulo">
		<h1>Vouchers <span>Confirmados</span></h1>
	</header> -->
	<?

		$exibe_valor = true;
		$exibe_forma_pagamento = true;
		$exibe_data_pagamento = true;
		$exibe_entregue_para = true;
		$exibe_entregue_data = true;
		$exibe_deadline = true;

	// Selecionar colunas
	switch ($acao) {
		case 'pagos':
			$exibe_entregue_para = false;
			$exibe_entregue_data = false;
			$exibe_deadline = false;
		break;

		case 'posterior':
			$exibe_entregue_para = false;
			$exibe_entregue_data = false;
			$exibe_data_pagamento = false;
			
		break;

		case 'cortesias':
			$exibe_valor = false;
			$exibe_forma_pagamento = false;
			$exibe_data_pagamento = false;
			$exibe_deadline = false;
		break;

		case 'permutas':
			$exibe_valor = false;
			$exibe_forma_pagamento = false;
			$exibe_data_pagamento = false;
			$exibe_deadline = false;
		break;

		case 'reservas':
			$exibe_forma_pagamento = false;
			$exibe_data_pagamento = false;
			$exibe_entregue_para = false;
		break;
	}



	// Marcar de vermelho o deadline

	?>
	<section class="secao bottom">
		<header class="titulo">
			<h1><? echo ucfirst($acao); ?></h1>
			<form id="busca-lista" class="busca-lista" method="post" action="<? echo SITE; ?>relatorios-lista-voucher.php?a=<? echo $acao; ?>">
				<p class="coluna">
					<label for="busca-lista-input" class="infield">Pesquisar</label>
					<? if(!empty($q)){ ?><a href="<? echo SITE; ?>relatorios-lista-voucher.php?a=<? echo $acao; ?>" class="limpar-busca">&times;</a><? } ?>
					<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
				</p>
				<input type="submit" class="submit" value="" />
			</form>
		</header>
		<table class="lista mini tablesorter-nopager">
			<thead>
				<tr>
					<th class="first"><strong>VCH</strong><span></span></th>
					<th><strong>Vendedor Interno</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>Parceiro</strong><span></span></th>
					<th><strong>Qtde.</strong><span></span></th>
					<? if($adm && $exibe_valor) { ?><th class="right"><span></span><strong>Valor (R$)</strong></th><? } ?>
					<? if($exibe_forma_pagamento) { ?><th><strong>Forma Pgto.</strong><span></span></th><? } ?>
					<th><strong>Dt. Compra</strong><span></span></th>
					<? if($exibe_data_pagamento) { ?><th><strong>Dt. Pagamento</strong><span></span></th><? } ?>
					<? if($exibe_deadline) { ?><th><strong>Deadline</strong><span></span></th><? } ?>
					<? if($exibe_entregue_data) { ?><th><strong>Dt. Entrega</strong><span></span></th><? } ?>
					<th><strong>Entregue</strong><span></span></th>
					<? if($exibe_entregue_para) { ?><th><strong>Entregue Para</strong><span></span></th><? } ?>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="<? echo ($adm) ? '7' : '6' ; ?>">&nbsp;</td></tr>
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
					$loja_data_entrega = $loja['DATA_ENTREGA'];
					// $loja_data_deadline = $loja['DEADLINE'];

					$loja_deadline = $loja['DEADLINE'];
					if(!empty($loja_deadline)) $loja_data_deadline = date('d/m', strtotime($loja_deadline->format('Y-m-d')));

					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_parceiro_cod = $loja['LO_PARCEIRO'];
					$loja_vendedor_cod = $loja['LO_VENDEDOR'];
					$loja_tipo_pagamento = $loja['LO_FORMA_PAGAMENTO'];
					// $loja_valor = number_format($loja['LO_VALOR_TOTAL'], 2, ",", ".");
					$loja_entrega = (bool) $loja['LO_ENVIADO'];
					$loja_block = (bool) $loja['LO_BLOCK'];
					$entrega = ($loja_entrega) ? 'ativo' : 'ativar';			
					$acao_entrega = ($loja_entrega) ? 'cancelar' : 'confirmar';

					$loja_entregue = (bool) $loja['LO_ENTREGUE'] ? 'Sim' : '';
					$loja_encaminhado_local = $loja['LO_ENCAMINHADO_LOCAL'];
					$loja_motoqueiro_nome = $loja['LO_MOTOQUEIRO_NOME'];
					$loja_recebido_local = $loja['LO_RECEBIDO_LOCAL'];
					$loja_atendente_nome = $loja['LO_ATENDENTE_NOME'];
					
					$loja_entregue_nome = $loja['LO_ENTREGUE_NOME'];
					$loja_entregue_nome_exibir = (strlen($loja_entregue_nome) > 20) ? substr($loja_entregue_nome, 0, 20)."..." : $loja_entregue_nome;

					// $loja_forma_pagamento = utf8_encode($loja['FP_NOME']);
					$loja_forma_pagamento = utf8_encode($formas_pagamento[$loja_tipo_pagamento]);

					unset($loja_cliente, $loja_cliente_exibir, $loja_parceiro, $loja_parceiro_exibir, $loja_vendedor, $loja_vendedor_exibir);

					$sql_vendedor = sqlsrv_query($conexao, "SELECT TOP 1 US_NOME FROM usuarios WHERE US_COD ='$loja_vendedor_cod'", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_vendedor) > 0) {
						$loja_vendedor_ar = sqlsrv_fetch_array($sql_vendedor);
						$loja_vendedor = trim($loja_vendedor_ar['US_NOME']);
						$loja_vendedor_exibir = (strlen($loja_vendedor) > 20) ? substr($loja_vendedor, 0, 20)."..." : $loja_vendedor;							
					}
					
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
					$sql_itens = sqlsrv_query($conexao, "$search AND li.LI_COMPRA='$loja_cod'", $conexao_params, $conexao_options);

					$loja_valor_total = 0;
					$n_itens_total = 0;

					while($itens = sqlsrv_fetch_array($sql_itens)) {

						$loja_valor = $itens['valor_dia'];

						$item_vaga = utf8_encode($itens['VAGAS']);
						$item_tipo_especifico = utf8_encode($itens['ESPECIFICO']);


						$n_itens = 1;
						$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;			
						
						if($item_fechado) { 
							$n_itens = $n_itens/$item_vaga;
							$loja_valor = $loja_valor/$item_vaga;
						} 

						$n_itens_total += $n_itens;
						$loja_valor_total += $loja_valor;

					}							

					$loja_valor = number_format($loja_valor_total, 2, ",", ".");

					$loja_deadline_vencido = false;					
					if(!empty($loja_deadline) && $exibe_deadline) {
						$loja_deadline = date('Y-m-d', strtotime($loja_deadline->format('Y-m-d')));
						$loja_deadline_vencido = ($loja_deadline < date('Y-m-d')) ? true : false;
					}

					?>
						<tr <? if ($loja_block || $loja_deadline_vencido){ echo 'class="block"'; } ?>>	
							<td class="first detalhes-voucher" data-cod="<? echo $loja_cod; ?>" data-cancelado="false">
								<div class="relative">
									<? echo $loja_cod; ?>
									<section class="detalhes"></section>
								</div>
							</td>
							<td <? if($loja_vendedor != $loja_vendedor_exibir) { echo 'title="'.utf8_encode($loja_vendedor).'"'; } ?>>
								<? echo utf8_encode($loja_vendedor_exibir); ?>
							</td>
							<td <? if($loja_cliente != $loja_cliente_exibir) { echo 'title="'.utf8_encode($loja_cliente).'"'; } ?>>
								<? echo utf8_encode($loja_cliente_exibir); ?>
							</td>
							<td <? if($loja_parceiro != $loja_parceiro_exibir) { echo 'title="'.utf8_encode($loja_parceiro).'"'; } ?>>
								<? echo utf8_encode($loja_parceiro_exibir); ?>
							</td>
							<td><? echo $n_itens_total; ?></td>
							<? if($adm && $exibe_valor) { ?><td class="valor"><? echo $loja_valor; ?></td><? } ?>
							<? if($exibe_forma_pagamento) { ?><td><? echo $loja_forma_pagamento; ?></td><? } ?>
							<td title="<? echo $loja_data; ?>"><? echo $loja_data_mini; ?></td>
							<? if($exibe_data_pagamento) { ?><td title="<? echo $loja_data_pagamento; ?>"><? echo $loja_data_pagamento_mini; ?></td><? } ?>
							<? if($exibe_deadline) { ?><td><? echo $loja_data_deadline; ?></td><? } ?>
							<? if($exibe_entregue_data) { ?><td><? echo $loja_data_entrega; ?></td><? } ?>
							<td><? echo $loja_entregue; ?></td>
							<? if($exibe_entregue_para) { ?><td <? if($loja_entregue_nome != $loja_entregue_nome_exibir) { echo 'title="'.utf8_encode($loja_entregue_nome).'"'; } ?>>
								<? echo utf8_encode($loja_entregue_nome_exibir); ?>
							</td><? } ?>
							<td class="ctrl small">
								<a href="<? echo SITE; ?>financeiro/detalhes/<? echo $loja_cod; ?>/" class="ver" target="_blank"></a>
							</td>
						</tr>

					<?
					$i++;
					$exibe_loja = true;
				}

			} 
			if(!$exibe_loja) {
			?>
				<tr>
					<td colspan="<? echo ($adm) ? '7' : '6' ; ?>" class="nenhum">Nenhum voucher encontrado</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<?
		if ($exibe_loja) {
			
			$item_link;

			if(!empty($tipo)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'tipo='.$tipo; }
			if(!empty($acao)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'a='.$acao; }
			if(!empty($dia)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'dia='.$dia; }
			if(!empty($setor)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'setor='.$setor; }
			if(!empty($fila)) { $item_link .= !empty($item_link) ? '&' : '?';  $item_link .= 'fila='.$fila; }

		?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>relatorios-lista-voucher.php<? echo $item_link; ?>" class="first"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-voucher.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>relatorios-lista-voucher.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>relatorios-lista-voucher.php<? echo $item_link; echo !empty($item_link) ? '&' : '?'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
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