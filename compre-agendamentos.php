<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");


unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = setcarnaval();
$usuario_cod = $_SESSION['usuario-cod'];

//-----------------------------------------------------------------//

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
	//$sql_cods_transfer = sqlsrv_query($conexao, "SELECT LIA_COMPRA, LIA_ITEM FROM loja_itens_adicionais WHERE LIA_ADICIONAL IN ($transfer_cod) AND LIA_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
	$sql_cods_transfer = sqlsrv_query($conexao, "SELECT lia.LIA_COMPRA, lia.LIA_ITEM FROM loja_itens li, vendas v, tipos t, loja_itens_adicionais lia WHERE lia.LIA_ITEM=li.LI_COD AND t.TI_TAG<>'camarote' AND li.LI_INGRESSO=v.VE_COD AND t.TI_COD=v.VE_TIPO AND lia.LIA_ADICIONAL IN ($transfer_cod) AND lia.LIA_BLOCK='0' AND lia.D_E_L_E_T_='0'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_cods_transfer) > 0) {
		$ar_cods_transfer = array();
		$ar_cods_transfer_item = array();
		while($cods_transfer = sqlsrv_fetch_array($sql_cods_transfer)) {
			array_push($ar_cods_transfer, $cods_transfer['LIA_COMPRA']);
			array_push($ar_cods_transfer_item, $cods_transfer['LIA_ITEM']);
		}
		$cods_transfer_compras = implode(",", array_unique($ar_cods_transfer));
		$cods_transfer_itens = implode(",", array_unique($ar_cods_transfer_item));
	}

}

if(!$nosearch) {

	/*
	DECLARE @loja TABLE (LO_COD INT, LO_CLIENTE INT, LO_FORMA_PAGAMENTO INT, LO_STATUS_TRANSACAO INT, LO_VALOR_TOTAL DECIMAL(10,2), LO_DATA_COMPRA DATETIME, DATA VARCHAR(255), DATA_PAGAMENTO VARCHAR(255));
	DECLARE @total TABLE (COD INT, QTDE INT DEFAULT 0);
	DECLARE @agendados TABLE (COD INT, QTDE INT DEFAULT 0);

	INSERT INTO @loja (LO_COD, LO_CLIENTE, LO_FORMA_PAGAMENTO, LO_STATUS_TRANSACAO, LO_VALOR_TOTAL, LO_DATA_COMPRA, DATA, DATA_PAGAMENTO)
	SELECT LO_COD, 
	LO_CLIENTE, 
	LO_FORMA_PAGAMENTO, 
	LO_STATUS_TRANSACAO, 
	LO_VALOR_TOTAL, 
	LO_DATA_COMPRA,
	(CONVERT(VARCHAR, LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_COMPRA, 108),1,5)) AS DATA,
	(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 103)+' '+SUBSTRING(CONVERT(VARCHAR, LO_DATA_PAGAMENTO, 108),1,5)) AS DATA_PAGAMENTO
	FROM loja (NOLOCK) WHERE LO_COD IN ($cods_transfer_itens) AND LO_EVENTO='$evento' AND LO_CLIENTE='$usuario_cod' AND LO_BLOCK='0' AND D_E_L_E_T_='0';

	INSERT INTO @total (COD, QTDE)
	SELECT LI_COMPRA, COUNT(LI_COD) FROM loja_itens WHERE D_E_L_E_T_='0' GROUP BY LI_COMPRA;

	INSERT INTO @agendados (COD, QTDE)
	SELECT l.LI_COMPRA, COUNT(t.TA_ITEM) FROM transportes_agendamento t, loja_itens l WHERE l.LI_COD=t.TA_ITEM AND t.D_E_L_E_T_='0' AND l.D_E_L_E_T_='0' GROUP BY l.LI_COMPRA;

	SELECT * FROM (
		SELECT 
		ISNULL(t.QTDE, 0) AS ITENS,
		ISNULL(p.QTDE, 0) AS AGENDADOS,
		f.FP_NOME AS FP_NOME,
		l.*

		FROM @loja l 
		LEFT JOIN @total t ON l.LO_COD = t.COD
		LEFT JOIN @agendados p ON l.LO_COD = p.COD
		LEFT JOIN formas_pagamento f ON f.FP_COD = l.LO_FORMA_PAGAMENTO
	) S WHERE (ITENS - AGENDADOS) > 0
	ORDER BY LO_DATA_COMPRA DESC;*/

	// $sql_loja = sqlsrv_query($conexao, "SELECT l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_COD IN ($cods_transfer_itens) AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' $search ORDER BY l.LO_DATA_COMPRA DESC", $conexao_params, $conexao_options);
	$sql_loja = sqlsrv_query($conexao, "

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
	FROM loja (NOLOCK) WHERE LO_COD IN ($cods_transfer_compras) AND LO_EVENTO='$evento' AND LO_CLIENTE='$usuario_cod' AND LO_BLOCK='0' AND D_E_L_E_T_='0' $search;

	INSERT INTO @total (COD, QTDE)
	SELECT LI_COMPRA, COUNT(LI_COD) FROM loja_itens WHERE LI_COD IN ($cods_transfer_itens) AND D_E_L_E_T_='0' GROUP BY LI_COMPRA;

	INSERT INTO @agendados (COD, QTDE)
	SELECT l.LI_COMPRA, COUNT(t.TA_ITEM) FROM transportes_agendamento t, loja_itens l WHERE l.LI_COD=t.TA_ITEM AND t.D_E_L_E_T_='0' AND l.D_E_L_E_T_='0' GROUP BY l.LI_COMPRA;

	SELECT * FROM (
		SELECT 
		ISNULL(t.QTDE, 0) AS ITENS,
		ISNULL(p.QTDE, 0) AS AGENDADOS,
		f.FP_NOME AS FP_NOME,
		l.*

		FROM @loja l 
		LEFT JOIN @total t ON l.LO_COD = t.COD
		LEFT JOIN @agendados p ON l.LO_COD = p.COD
		LEFT JOIN formas_pagamento f ON f.FP_COD = l.LO_FORMA_PAGAMENTO
	) S WHERE (ITENS - AGENDADOS) > 0
	ORDER BY LO_DATA_COMPRA DESC;

	", $conexao_params, $conexao_options);
	if(sqlsrv_next_result($sql_loja) && sqlsrv_next_result($sql_loja) && sqlsrv_next_result($sql_loja))
	$n_loja = sqlsrv_num_rows($sql_loja);
} else {
	$n_loja = false;
}

?>
<section id="conteudo">
	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; <? echo $lg['menu_minhas_compras']; ?>
	</div>
	<section id="compre-aqui">

		<header class="titulo">
			<h1>Minhas compras</h1>
			<ul class="menu">
				<li><a href="<? echo SITE.$link_lang; ?>minhas-compras/">Todas</a></li>
				<li><a href="<? echo SITE.$link_lang; ?>minhas-compras/pagas/">Pagas</a></li>
				<li><a href="<? echo SITE.$link_lang; ?>minhas-compras/pendentes/">Pendentes</a></li>
				<li><a href="<? echo SITE.$link_lang; ?>minhas-compras/agendamentos/" class="agendamentos checked">Agendamentos Pendentes</a></li>
			</ul>
		</header>
		<section class="padding">
		
			<section id="financeiro-lista" class="secao label-top">
				<table class="lista tablesorter">
					<thead>
						<tr>
							<th class="first"><strong>VCH</strong><span></span></th>
							<th><strong>Data da Compra</strong><span></span></th>
							<th><strong>Data do Pgto</strong><span></span></th>
							<th class="right"><span></span><strong>Valor (R$)</strong></th>
							<th><strong>Forma de Pgto</strong><span></span></th>
							<th><strong>Itens</strong><span></span></th>
							<th><strong>Itens Pendentes</strong><span></span></th>
							<th>&nbsp;</th>
						</tr>
						<tr class="spacer"><td colspan="8">&nbsp;</td></tr>
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
							$loja_itens = $loja['ITENS'];
							$loja_agendados = $loja['AGENDADOS'];

							$loja_data_pagamento = $loja['DATA_PAGAMENTO'];
							$loja_valor = number_format($loja['LO_VALOR_TOTAL'], 2, ",", ".");

							$loja_tipo_pagamento = utf8_encode($loja['LO_FORMA_PAGAMENTO']);
							$loja_forma_pagamento = utf8_encode($loja['FP_NOME']);
							
							?>
								<tr>	
									<td class="first"><? echo $loja_cod; ?></td>
									<td><? echo $loja_data; ?></td>
									<td><? echo $loja_data_pagamento; ?></td>
									<td class="valor"><? echo $loja_valor; ?></td>
									<td><? echo $loja_forma_pagamento; ?></td>
									<td><? echo $loja_itens; ?></td>
									<td><? echo ($loja_itens - $loja_agendados); ?></td>
									<td class="ctrl">
										<a href="<? echo SITE.$link_lang; ?>minhas-compras/agendamentos/<? echo $loja_cod; ?>/" class="ver"></a>								
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
							<td colspan="8" class="nenhum">Nenhum agendamento pendente.</td>
						</tr>
					<?
					}
					?>
					</tbody>
				</table>
				<? if ($exibe_loja) { ?>
		        <div class="pager-tablesorter">
			        <a href="#" class="first"></a>
			        <a href="#" class="prev"></a>
			        <span class="pagedisplay"></span>
			        <a href="#" class="next"></a>
			        <a href="#" class="last"></a>
			        <input type="hidden" class="pagesize" value="30" />
		        </div>
		        <? } ?>
				<div class="clear"></div>

			</section>			
			
		</section>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>