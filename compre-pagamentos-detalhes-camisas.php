<?

define('PGMODAL','true');

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

//-----------------------------------------------------------------//


$evento = setcarnaval();
$cod = (int) $_GET['c'];

$usuario_cod = $_SESSION['usuario-cod'];


if(!empty($cod) && !empty($evento)) {

	unset($_SESSION['compras-camisas'][$cod]);

	$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_CLIENTE='$usuario_cod' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_data = $loja['DATA'];
		$loja_cliente = $loja['LO_CLIENTE'];
		$loja_forma = $loja['LO_FORMA_PAGAMENTO'];
		// $loja_diferenca_dias = (5 - $loja['DIFERENCA']);
		// $loja_status_transacao = $loja['LO_STATUS_TRANSACAO'];
		// $loja_pago = (bool) $loja['LO_PAGO'];
		// $loja_delivery = (bool) $loja['LO_DELIVERY'];

		$cartao_credito = ($loja_forma == 1) ? true : false;
		$faturado = ($loja_forma == 7) ? true : false;
		$reserva = ($loja_forma == 5) ? true : false;

		// $loja_cliente = utf8_encode($loja['CL_NOME']);
		$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

		$loja_cliente = trim($loja_cliente_ar['NOMEPARC']);
		$loja_cliente_exibir = (strlen($loja_cliente) > 25) ? substr($loja_cliente, 0, 25)."..." : $loja_cliente;
		
		$loja_valor_total = $loja['LO_VALOR_TOTAL'];
		$loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');

		//Forma de pagamento
		// LO_FORMA_PAGAMENTO		
		$sql_forma = sqlsrv_query($conexao, "SELECT TOP 1 FP_NOME FROM formas_pagamento WHERE FP_COD='$loja_forma'", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_forma) > 0) {
			$loja_forma_ar = sqlsrv_fetch_array($sql_forma);
			$loja_forma_pagamento = utf8_encode($loja_forma_ar['FP_NOME']);
		}


		//Buscar itens
		#$folia_item = ($_SERVER['SERVER_NAME'] == "bruno") ? 28 :  176;
		$sql_itens = sqlsrv_query($conexao, "SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO NOT IN (SELECT v.VE_COD FROM vendas v LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA WHERE ((v.VE_TIPO=4 AND d.ED_DATA IN ('2015-02-13', '2015-02-14', '2016-02-05', '2016-02-06')) OR (v.VE_TIPO IN (1,2))) AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0)  AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
		$itens_total = sqlsrv_num_rows($sql_itens);

		//Numero de camisas
		$sql_camisas = sqlsrv_query($conexao, "
			SELECT COUNT(CA_COD) AS TOTAL, 
			SUM(CASE WHEN CA_TAMANHO='P' THEN 1 ELSE 0 END) AS P,
			SUM(CASE WHEN CA_TAMANHO='M' THEN 1 ELSE 0 END) AS M,
			SUM(CASE WHEN CA_TAMANHO='G' THEN 1 ELSE 0 END) AS G,
			SUM(CASE WHEN CA_TAMANHO='GG' THEN 1 ELSE 0 END) AS GG,
			SUM(CASE WHEN CA_TAMANHO='EXG' THEN 1 ELSE 0 END) AS EXG
			FROM loja_camisas WHERE CA_COMPRA='$loja_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_camisas) > 0){

			$camisas = sqlsrv_fetch_array($sql_camisas);

			$camisas_total = $camisas['TOTAL'];
			$camisas_total_tamanho['P'] = $camisas['P'];
			$camisas_total_tamanho['M'] = $camisas['M'];
			$camisas_total_tamanho['G'] = $camisas['G'];
			$camisas_total_tamanho['GG'] = $camisas['GG'];
			$camisas_total_tamanho['EXG'] = $camisas['EXG'];

			$icamisas = 0;
			foreach ($camisas_total_tamanho as $key => $camisas_total_qtde) {
				if($camisas_total_qtde > 0) {

					$_SESSION['compras-camisas'][$cod][$icamisas]['tamanho'] = $key;
					$_SESSION['compras-camisas'][$cod][$icamisas]['qtde'] = $camisas_total_qtde;
					
					$icamisas++;
				}
			}

		}

		$tamanhos_camisas = array('P','M','G','GG','EXG');

?>
<section id="conteudo" class="camisas">
	<section id="compre-aqui">
		<section id="camisas-detalhes" class="secao label-top">
			<table class="lista">
				<thead>
					<tr>
						<th class="first"><strong>VCH</strong></th>
						<th><strong>Cliente</strong></th>
						<!-- <th><strong>Data da Compra</strong></th> -->
						<th><strong>Forma Pagamento</strong></th>
						<th class="right"><strong>Valor (R$)</strong></th>
					</tr>
				</thead>
				<tbody>				
					<tr>	
						<td class="first"><? echo $loja_cod; ?></td>
						<td <? if($loja_cliente != $loja_cliente_exibir) { echo 'title="'.utf8_encode($loja_cliente).'"'; } ?>>
							<? echo utf8_encode($loja_cliente_exibir); ?>
						</td>
						<!-- <td><? echo $loja_data; ?></td> -->
						<td><? echo $loja_forma_pagamento; ?></td>
						<td class="valor"><? echo $loja_valor_total_f; ?></td>
					</tr>
				</tbody>
			</table>
		</section>

		<header class="titulo">
			<input type="hidden" name="total-ingressos" value="<? echo $itens_total; ?>" />

			<h1>Tamanho das Camisas</h1>
			<div class="tamanhos">
				<span><? echo $camisas_total; ?></span> / <? echo $itens_total; ?>
			</div>
		</header>

		<section id="camisas-adicionar" class="secao">

			<form id="adicionar" class="controle" method="post" action="#">

				<input type="hidden" name="cod" value="<? echo $cod; ?>" />
			
				<h3>Adicionar camisa:</h3>		
				<p class="coluna">
					<label for="camisa-quantidade">Qtde:</label>
					<input type="text" name="quantidade" class="input" id="camisa-quantidade" value="1" />
				</p>

				<section class="selectbox coluna" id="camisa-tamanho">
					<a href="#" class="arrow"><strong>Tamanho:</strong><span></span></a>
					<ul class="drop">
						<? foreach ($tamanhos_camisas as $tamanho) { ?>
		                <li><label class="item"><input type="radio" name="tamanho" alt="<? echo $tamanho; ?>" value="<? echo $tamanho; ?>"><? echo $tamanho; ?></label></li>
						<? } ?>                       
					</ul>
					<div class="clear"></div>
				</section>

				<input type="submit" class="submit coluna" value="Adicionar" />

				<div class="clear"></div>

			</form>
		</section>

		<section id="camisas-lista">
			<form id="camisas" class="controle" method="post" action="<? echo SITE.$link_lang; ?>minhas-compras/detalhes/camisas/post/">

				<section class="secao">
				<input type="hidden" name="cod" value="<? echo $cod; ?>" />

				<ul>
				<?

				if(count($_SESSION['compras-camisas'][$cod]) > 0) {

					foreach ($_SESSION['compras-camisas'][$cod] as $key => $compra) {		
						
						echo '<li>
						<strong>'.$compra['qtde'].'</strong> Tam: '.$compra['tamanho'].'
						<a href="'.SITE.$link_lang.'include/camisas-adicionar.php?c='.$cod.'&i='.$key.'&a=excluir" class="remover">&times;</a>
						<input type="hidden" name="quantidade-item" value="'.$compra['qtde'].'" />
						</li>';
					}

				}

				?>
				</ul>

				</section>
				
				<footer class="controle">
					<input type="submit" class="submit coluna" value="Cadastrar" />
					<a href="#" class="cancel no-cancel fancy-close coluna">Cancelar</a>
					<div class="clear"></div>
				</footer>

			</form>
		</section>


		</form>
	</section>

</section>
<?
	}
}

//-----------------------------------------------------------------//

// include('include/footer.php');

//Fechar conexoes
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>
<input type="hidden" id="base-site" value="<? echo SITE; ?>" />
</body>
</html>