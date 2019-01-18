<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l, clientes c WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Detalhes da <span>Compra</span></h1>
	</header>
	<section class="padding">
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_cliente = $loja['LO_CLIENTE'];

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
		$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
		$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));
	?>
		<section class="secao" id="compra-dados">
			<aside><? echo $loja_cod; ?></aside>
			<section>
				<h1><? echo $loja_nome; ?></h1>
				<p><? echo $loja_email; ?></p>
				<p><? echo $loja_telefone; ?></p>
			</section>

			<div class="clear"></div>
		</section>		
		<section id="financeiro-lista" class="secao label-top">
			<table class="lista tablesorter">
				<thead>
					<tr>
						<th class="first"><strong>VCH</strong><span></span></th>
						<th><strong>Cliente</strong><span></span></th>
						<th><strong>Tipo</strong><span></span></th>
						<th><strong>Dia</strong><span></span></th>
						<th><strong>Setor</strong><span></span></th>
						<th class="right"><span></span><strong>Valor (R$)</strong></th>
						<th>&nbsp;</th>
					</tr>
					<tr class="spacer"><td colspan="7">&nbsp;</td></tr>
				</thead>
				<tbody>
				<?
				$sql_itens = sqlsrv_query($conexao, "SELECT li.*, v.VE_DIA, v.VE_SETOR, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE li.LI_COMPRA='$loja_cod' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_itens) > 0) {
					$i = 1;
					while ($item = sqlsrv_fetch_array($sql_itens)) {
						$item_cod = $item['LI_COD'];
						$item_id = $item['LI_ID'];
						$item_nome = utf8_encode($item['LI_NOME']);
						$item_tipo = utf8_encode($item['TI_NOME']);
						$item_dia = utf8_encode($item['dia']);
						$item_setor = $item['ES_NOME'];
						$item_valor = number_format($item['LI_VALOR'], 2, ",", ".");

						?>
							<tr>	
								<td class="first"><? echo $loja_cod."/".$item_id; ?></td>
								<td><? echo $item_nome; ?></td>
								<td><? echo $item_tipo; ?></td>
								<td><? echo $item_dia; ?></td>
								<td><? echo $item_setor; ?></td>
								<td class="valor"><? echo $item_valor; ?></td>
								<td class="ctrl"><a href="<? echo SITE; ?>ingressos/comentario/novo/<? echo $item_cod; ?>/" class="comentario fancybox fancybox.iframe width600"></a></td>
							</tr>
						<?
						$i++;
					}
				}
				?>
				</tbody>
			</table>
			<div class="pager-tablesorter">
		        <a href="#" class="first"></a>
		        <a href="#" class="prev"></a>
		        <span class="pagedisplay"></span>
		        <a href="#" class="next"></a>
		        <a href="#" class="last"></a>
		        <input type="hidden" class="pagesize" value="30" />
	        </div>
	        <div class="clear"></div>
		</section>
		<footer class="controle">
			<a href="<? echo SITE; ?>compras/alterar/<? echo $loja_cod; ?>/" class="button coluna big">Alterar tipos</a>
			<a href="<? echo strpos($_SERVER['HTTP_REFERER'], 'financeiro') ? $_SERVER['HTTP_REFERER'] : SITE.'financeiro/'; ?>" class="cancel coluna">Voltar</a>
			<div class="clear"></div>
		</footer>
	<?
	}
	?>	
	</section>
</section>
<script type="text/javascript">
$(document).ready(function(){
	$("form#roteiro-novo").find("input[name='tipo']").radioSel('<? echo $loja_tipo; ?>');
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>