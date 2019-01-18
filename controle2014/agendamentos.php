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

//-----------------------------------------------------------------//

$q = format($_GET['q']);

if(!empty($q)) {

	if(is_numeric($q)) {
		$search = " AND LO_COD='$q' ";
	} else {

		$search_query = is_numeric($q) ? " AND CODPARC='$q' " : " AND NOMEPARC LIKE '%$q%' ";
		$sql_search = sqlsrv_query($conexao_sankhya, "SELECT CODPARC FROM TGFPAR WHERE CLIENTE='S' AND BLOQUEAR='N' $search_query ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_search) > 0) {
			$ar_cods = array();
			while ($cods = sqlsrv_fetch_array($sql_search)) array_push($ar_cods, $cods['CODPARC']);
			$cods = implode(",", $ar_cods);
			$search = " AND LO_CLIENTE IN ($cods) ";
		} else {
			// $search = " AND l.LO_CLIENTE IN ('') ";
			$nosearch = true;
		}
	}

}

//Buscar apenas os que possuem transfer
$cods_transfer_compras = "''";
$cods_transfer_itens = "''";

//Selecionar código do transfer
$sql_transfer = sqlsrv_query($conexao, "SELECT VA_COD FROM vendas_adicionais WHERE (VA_NOME_EXIBICAO='transfer' OR VA_NOME_EXIBICAO='transferinout') AND VA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_transfer) > 0) {
	$transfer_cod = array();
	while($ar_transfer = sqlsrv_fetch_array($sql_transfer)) array_push($transfer_cod, $ar_transfer['VA_COD']);
	$transfer_cod = implode(",", $transfer_cod);
	
	//Selecionar somente os que tem transfer
	$sql_cods_transfer = sqlsrv_query($conexao, "SELECT LIA_COMPRA, LIA_ITEM FROM loja_itens_adicionais WHERE LIA_ADICIONAL IN ($transfer_cod) AND LIA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_cods_transfer) > 0) {
		$ar_cods_transfer = array();
		$ar_cods_transfer_item = array();
		while($cods_transfer = sqlsrv_fetch_array($sql_cods_transfer)) {
			array_push($ar_cods_transfer, $cods_transfer['LIA_COMPRA']);
			array_push($ar_cods_transfer_item, $cods_transfer['LIA_ITEM']);
		}
		$cods_transfer_compras = implode(",", array_unique($ar_cods_transfer));
		$cods_transfer_itens = implode(",", array_unique($ar_cods_transfer_item));


		// Verificar apenas os que são frisas / arquibancada e camarote do setor 8 
		$sql_filtradas = sqlsrv_query($conexao, "SELECT li.LI_COMPRA, li.LI_COD
			FROM loja_itens li 
			LEFT JOIN vendas v ON v.VE_COD = li.LI_INGRESSO
			LEFT JOIN eventos_setores s ON s.ES_COD=v.VE_SETOR

			WHERE li.LI_COMPRA IN ($cods_transfer_compras) 
			AND li.LI_COD IN ($cods_transfer_itens) 
			AND (v.VE_TIPO IN (1,2) OR (v.VE_TIPO=3 AND s.ES_NOME='8'))
			AND li.D_E_L_E_T_=0", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_filtradas) > 0) {

			$ar_cods_transfer = array();
			$ar_cods_transfer_item = array();

			while($cods_transfer = sqlsrv_fetch_array($sql_filtradas)) {
				array_push($ar_cods_transfer, $cods_transfer['LI_COMPRA']);
				array_push($ar_cods_transfer_item, $cods_transfer['LI_COD']);
			}
			$cods_transfer_compras = implode(",", array_unique($ar_cods_transfer));
			$cods_transfer_itens = implode(",", array_unique($ar_cods_transfer_item));

		} else {
			$cods_transfer_compras = "''";
			$cods_transfer_itens = "''";
		}
		
	}

}

//-----------------------------------------------------------------//

$p = (int) $_GET['p'];
if(!($p > 0)) $p = 1;

if(!$nosearch) {

	// $sql_loja = sqlsrv_query($conexao, "SELECT l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_COD IN ($cods_transfer_itens) AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' $search ORDER BY l.LO_DATA_COMPRA DESC", $conexao_params, $conexao_options);
	$sql_loja = sqlsrv_query($conexao, "

	DECLARE @geral TABLE (ITENS INT, AGENDADOS INT, LO_COD INT, LO_CLIENTE INT, LO_FORMA_PAGAMENTO INT, LO_STATUS_TRANSACAO INT, LO_VALOR_TOTAL DECIMAL(10,2), LO_DATA_COMPRA DATETIME, DATA VARCHAR(255));
	
	DECLARE @loja TABLE (LO_COD INT, LO_CLIENTE INT, LO_FORMA_PAGAMENTO INT, LO_STATUS_TRANSACAO INT, LO_VALOR_TOTAL DECIMAL(10,2), LO_DATA_COMPRA DATETIME, DATA VARCHAR(255));
	DECLARE @total TABLE (COD INT, QTDE INT DEFAULT 0);
	DECLARE @agendados TABLE (COD INT, QTDE INT DEFAULT 0);

	INSERT INTO @loja (LO_COD, LO_CLIENTE, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_DATA_COMPRA, DATA)
	SELECT LO_COD, 
	LO_CLIENTE, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_TOTAL, 
	LO_DATA_COMPRA,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA	
	FROM loja (NOLOCK) WHERE LO_COD IN ($cods_transfer_compras) AND LO_EVENTO='$evento' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search;

	INSERT INTO @total (COD, QTDE)
	SELECT LI_COMPRA, COUNT(LI_COD) FROM loja_itens WHERE LI_COD IN ($cods_transfer_itens) AND D_E_L_E_T_='0' GROUP BY LI_COMPRA;

	INSERT INTO @agendados (COD, QTDE)
	SELECT l.LI_COMPRA, COUNT(t.TA_ITEM) FROM transportes_agendamento t, loja_itens l WHERE l.LI_COD=t.TA_ITEM AND t.D_E_L_E_T_='0' AND l.D_E_L_E_T_='0' GROUP BY l.LI_COMPRA;

	INSERT INTO @geral (ITENS, AGENDADOS, LO_COD, LO_CLIENTE, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_DATA_COMPRA, DATA)
	SELECT * FROM (SELECT ISNULL(t.QTDE, 0) AS TOTAL, ISNULL(p.QTDE, 0) AS AGENDADOS, l.* FROM @loja l 
	LEFT JOIN @total t ON l.LO_COD = t.COD
	LEFT JOIN @agendados p ON l.LO_COD = p.COD) S WHERE (TOTAL - AGENDADOS) <= 0;


	DECLARE @PageNumber INT;
	DECLARE @PageSize INT;
	DECLARE @TotalPages INT;

	SET @PageSize = 20;
	SET @PageNumber = $p;

	IF @PageNumber = 0 BEGIN
	SET @PageNumber = 1
	END;

	SET @TotalPages = CEILING(CONVERT(NUMERIC(20,10), ISNULL((SELECT COUNT(*) FROM @geral), 0)) / @PageSize);

	WITH cadastro(NumeroLinha, ITENS, AGENDADOS, LO_COD, LO_CLIENTE, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_DATA_COMPRA, DATA)
	AS (
	SELECT ROW_NUMBER() OVER (ORDER BY LO_COD DESC) AS NumeroLinha,
	ITENS,
	AGENDADOS,
	LO_COD, 
	LO_CLIENTE, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_TOTAL, 
	LO_DATA_COMPRA,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA	
	FROM @geral)

	SELECT @TotalPages AS TOTAL, @PageNumber AS PAGINA, NumeroLinha, ITENS, AGENDADOS, LO_COD, LO_CLIENTE, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, DATA
	FROM cadastro
	WHERE NumeroLinha BETWEEN ( ( ( @PageNumber - 1 ) * @PageSize ) + 1 ) AND ( @PageNumber * @PageSize )
	ORDER BY LO_DATA_COMPRA DESC

	", $conexao_params, $conexao_options);
	if(sqlsrv_next_result($sql_loja) && sqlsrv_next_result($sql_loja) && sqlsrv_next_result($sql_loja) && sqlsrv_next_result($sql_loja))
	$n_loja = sqlsrv_num_rows($sql_loja);

	//SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'
	//SELECT * FROM transportes_agendamento WHERE TA_ITEM='$itens_cod' AND D_E_L_E_T_='0'

} else {
	$n_loja = false;
}

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Agendamentos <span>Confirmados</span></h1>
		<form id="busca-lista" class="busca-lista" method="get" action="<? echo SITE; ?>agendamentos/">
			<p class="coluna">
				<label for="busca-lista-input" class="infield">Pesquisar</label>
				<? if(!empty($q)){ ?><a href="<? echo SITE; ?>agendamentos/" class="limpar-busca">&times;</a><? } ?>
				<input type="text" name="q" class="input" id="busca-lista-input" value="<? echo utf8_encode($q); ?>" />
			</p>
			<input type="submit" class="submit" value="" />
		</form>
	</header>
	<section class="secao bottom">
		<table class="lista tablesorter-nopager">
			<thead>
				<tr>
					<th class="first"><strong>ID</strong><span></span></th>
					<th><strong>Cliente</strong><span></span></th>
					<th><strong>Data da Compra</strong><span></span></th>
					<th><strong>Total de Itens</strong><span></span></th>
					<th>&nbsp;</th>
				</tr>
				<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
			</thead>
			<tbody>
			<?
			
			if($n_loja !== false)	 {

				$i=1;
				while($loja = sqlsrv_fetch_array($sql_loja)) {

					//Total de paginas
					$total_paginas = $loja['TOTAL'];

					$loja_cod = $loja['LO_COD'];
					$loja_data = $loja['DATA'];
					$loja_cliente_cod = $loja['LO_CLIENTE'];
					$loja_itens = $loja['ITENS'];

					unset($loja_cliente);
					
					// $loja_cliente = utf8_encode($loja['CL_NOME']);
					$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC FROM TGFPAR WHERE CODPARC='$loja_cliente_cod' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_cliente) > 0) {
						$loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);
						$loja_cliente = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
					}

					//buscar itens
					/*$sql_itens = sqlsrv_query($conexao, "SELECT * FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
					$n_itens = sqlsrv_num_rows($sql_itens);

					if($n_itens > 0) {
						$agendamentos = 0;
						while ($itens = sqlsrv_fetch_array($sql_itens)) {
							$itens_cod = $itens['LI_COD'];
							//busca agendamentos
							$sql_agendamentos = sqlsrv_query($conexao, "SELECT * FROM transportes_agendamento WHERE TA_ITEM='$itens_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
							if(sqlsrv_num_rows($sql_agendamentos)) {
								$agendamentos++;
							}
						}
					}

					if(($n_itens-$agendamentos) == 0) {*/
					?>
						<tr>	
							<td class="first detalhes-voucher" data-cod="<? echo $loja_cod; ?>" data-cancelado="false">
								<div class="relative">
									<? echo $loja_cod; ?>
									<section class="detalhes"></section>
								</div>
							</td>
							<td><? echo $loja_cliente; ?></td>
							<td><? echo $loja_data; ?></td>
							<td><? echo $loja_itens; ?></td>
							<td class="ctrl">
								<a href="<? echo SITE; ?>agendamentos/editar/<? echo $loja_cod; ?>/" class="ver"></a>
								<!-- <a href="<? echo SITE; ?>e-fornecedor-gerenciar.php?c=<? echo $loja_cod; ?>&a=deletar" class="excluir confirm" title="Tem certeza que deseja excluir esse fornecedor?"></a> -->
							</td>
						</tr>
					<?
						$i++;

						$exibe_loja = true;
					//}
				}
			} 
			if(!$exibe_loja) {
			?>
				<tr>
					<td colspan="6" class="nenhum">Nenhum agendamento confirmado.</td>
				</tr>
			<?
			}
			?>
			</tbody>
		</table>
		<? if ($exibe_loja) { ?>
        <div class="pager-tablesorter">
	        <a href="<? echo SITE; ?>agendamentos/<? if(!empty($q)) echo '?q='.urlencode(utf8_encode($q)); ?>" class="first"></a>
	        <a href="<? echo SITE; ?>agendamentos/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p > 1) ? ($p - 1) : 1; ?>" class="prev"></a>
	        <span class="pagedisplay"><? echo $p; ?>/<? echo $total_paginas; ?></span>
	        <a href="<? echo SITE; ?>agendamentos/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo ($p < $total_paginas) ? ($p + 1) : $total_paginas; ?>" class="next"></a>
	        <a href="<? echo SITE; ?>agendamentos/?<? if(!empty($q)) echo 'q='.urlencode(utf8_encode($q)).'&'; ?>p=<? echo $total_paginas; ?>" class="last"></a>
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