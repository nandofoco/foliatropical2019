<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");

//-----------------------------------------------------------------//


$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_GET['c'];
$tipo = format($_GET['t']);

if(!empty($cod) && !empty($evento)) {

	$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 *, 
		SUBSTRING(CONVERT(VARCHAR, LO_DATA_ENTREGA, 103),1,10) AS DATA_ENTREGA, 
		SUBSTRING(CONVERT(VARCHAR, LO_DATA_ENTREGA, 108),1,5) AS HORA_ENTREGA, 
		CONVERT(CHAR, LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA,

		SUBSTRING(CONVERT(VARCHAR, LO_DATA_ENCAMINHAMENTO, 103),1,10) AS DATA_ENCAMINHAMENTO, 
		SUBSTRING(CONVERT(VARCHAR, LO_DATA_ENCAMINHAMENTO, 108),1,5) AS HORA_ENCAMINHAMENTO, 

		SUBSTRING(CONVERT(VARCHAR, LO_DATA_RECEBIMENTO, 103),1,10) AS DATA_RECEBIMENTO, 
		SUBSTRING(CONVERT(VARCHAR, LO_DATA_RECEBIMENTO, 108),1,5) AS HORA_RECEBIMENTO

		FROM loja WHERE LO_COD='$cod' AND LO_EVENTO='$evento'", $conexao_params, $conexao_options);
	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_delivery = (bool) $loja['LO_DELIVERY'];
		$loja_delivery_periodo = $loja['LO_CLI_PERIODO'];
		$loja_retirada = $loja['LO_RETIRADA'];
		
		$loja_entregue = (bool) $loja['LO_ENTREGUE'];		
		$loja_data_para_entrega = $loja['DATA_PARA_ENTREGA'];
		$loja_entregue_nome = utf8_encode($loja['LO_ENTREGUE_NOME']);
		$loja_data_entrega = $loja['DATA_ENTREGA'];
		$loja_hora_entrega = $loja['HORA_ENTREGA'];
		
		$loja_encaminhado = (bool) $loja['LO_ENCAMINHADO'];
		//$loja_data_para_encaminhamento = $loja['DATA_PARA_ENCAMINHAMENTO'];
		$loja_motoqueiro_nome = utf8_encode($loja['LO_MOTOQUEIRO_NOME']);
		$loja_data_encaminhamento = $loja['DATA_ENCAMINHAMENTO'];
		$loja_hora_encaminhamento = $loja['HORA_ENCAMINHAMENTO'];
		$loja_encaminhado_local = $loja['LO_ENCAMINHADO_LOCAL'];

		$loja_recebido = (bool) $loja['LO_RECEBIDO'];
		//$loja_data_para_recebimento = $loja['DATA_PARA_RECEBIMENTO'];
		$loja_atendente_nome = utf8_encode($loja['LO_ATENDENTE_NOME']);
		$loja_data_recebimento = $loja['DATA_RECEBIMENTO'];
		$loja_hora_recebimento = $loja['HORA_RECEBIMENTO'];
		$loja_recebido_local = $loja['LO_RECEBIDO_LOCAL'];

?>
<section id="conteudo" class="expedicao">

	<form id="expedicao" method="post" class="<? echo $tipo; ?>" action="<? echo SITE; ?>ingressos/expedicao/post/">
		
		<input type="hidden" name="cod" value="<? echo $cod; ?>" />

		<header class="titulo">
			<h1>
			<?
			switch ($tipo) {
				case 'encaminhado': ?>Confirmar <span>Encaminhamento</span><? break;
				case 'recebido': ?>Confirmar <span>Recebimento</span><? break;
				default: ?>Confirmar <span>Entrega ou Retirada</span><? break;
			}
			?>
			</h1>
		</header>

		<section id="expedicao-detalhes" class="secao label-top">
			<table class="lista">
				<thead>
					<tr>
						<th class="first"><strong>VCH</strong></th>
						<th><strong>Cliente</strong></th>
						<th><strong>Tipo</strong></th>
						<th><strong>Dia</strong></th>
						<th><strong>Setor</strong></th>
						<th class="right"><strong>Valor (R$)</strong></th>
					</tr>
				</thead>
				<tbody>				
					<?

					$sql_item = sqlsrv_query($conexao, "SELECT li.*, v.VE_DIA, v.VE_FILA, v.VE_SETOR, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE li.LI_COMPRA='$cod' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);

					if(sqlsrv_num_rows($sql_item) !== false) {
						$i = 1;
						while ($item = sqlsrv_fetch_array($sql_item)) {
							$item_cod = $item['LI_COD'];
							$item_id = $item['LI_ID'];
							$item_compra = $item['LI_COMPRA'];
							$item_nome = utf8_encode($item['LI_NOME']);
							$item_tipo = utf8_encode($item['TI_NOME']);
							$item_dia = utf8_encode($item['dia']);
							$item_fila = utf8_encode($item['VE_FILA']);
							$item_setor = $item['ES_NOME'];
							$item_valor = number_format($item['LI_VALOR'], 2, ",", ".");

					?>
					<tr>	
						<td class="first"><? echo $item_compra."/".$item_id; ?></td>
						<td><? echo $item_nome; ?></td>
						<td><? echo $item_tipo; ?> <? if(!empty($item_fila)) { echo $item_fila; } ?></td>
						<td><? echo $item_dia; ?></td>
						<td><? echo $item_setor; ?></td>
						<td class="valor"><? echo $item_valor; ?></td>
					</tr>
					<?
						}
					}
					?>
				</tbody>
			</table>
		</section>

		<section class="secao label-top expedicao">
			
			<?

			switch ($tipo) {
				
				case 'encaminhado':

				?>
				<p class="coluna">
					<label for="ingresso-entregue-nome">Nome do Motoqueiro</label>
					<input type="text" name="nome" class="input" id="ingresso-entregue-nome" value="<? echo $loja_motoqueiro_nome; ?>" />
				</p>

				<p class="coluna last">
					<label for="ingresso-entregue-atendente">Nome do Atendente</label>
					<input type="text" name="atendente" class="input" id="ingresso-entregue-atendente" value="<? echo $loja_atendente_nome; ?>" />
				</p>

				<div class="clear"></div>

				<!-- <p class="coluna">
					<label>Data prevista</label>
					<input type="text" name="data-para" class="input disabled" disabled="disabled" value="<? echo $loja_data_para_encaminhamento; ?>" />
				</p> -->

				<p class="coluna">
					<label for="ingresso-entregue-data">Data da entrega</label>
					<input type="text" name="data" class="input" id="ingresso-entregue-data" value="<? echo ($loja_encaminhado) ? $loja_data_encaminhamento : date('d/m/Y'); ?>" />
				</p>

				<p class="coluna">
					<label for="ingresso-entregue-hora">Hora da entrega</label>
					<input type="text" name="hora" class="input" id="ingresso-entregue-hora" value="<? echo ($loja_encaminhado) ? $loja_hora_encaminhamento : date('H:i'); ?>" />
				</p>

				<section id="ingresso-entregue-locais" class="selectbox coluna">
					<h3>Encaminhado para</h3>
					<a href="#" class="arrow"><strong>Encaminhado para</strong><span></span></a>
					<ul class="drop">
						<li><label class="item"><input type="radio" name="encaminhado" value="centro" alt="Centro"/>Centro</label></li>
						<li><label class="item"><input type="radio" name="encaminhado" value="ipanema" alt="Ipanema"/>Ipanema</label></li>
						<li><label class="item"><input type="radio" name="encaminhado" value="leblon" alt="Leblon"/>Leblon</label></li>
						<li><label class="item"><input type="radio" name="encaminhado" value="hotel" alt="Hotel"/>Hotel</label></li>
						<li><label class="item"><input type="radio" name="encaminhado" value="balcao" alt="Balcão"/>Balcão</label></li>
					</ul>
				</section>
				<script type="text/javascript">$(document).ready(function() { $('section#ingresso-entregue-locais.selectbox input:radio').radioSel('<? echo $loja_encaminhado_local; ?>') })</script>
				<?

				break;

				case 'recebido':

				?>
				<p>
					<label for="ingresso-entregue-nome">Nome do Atendente</label>
					<input type="text" name="nome" class="input" id="ingresso-entregue-nome" value="<? echo $loja_atendente_nome; ?>" />
				</p>

				<!-- <p class="coluna">
					<label>Data prevista</label>
					<input type="text" name="data-para" class="input disabled" disabled="disabled" value="<? echo $loja_data_para_recebimento; ?>" />
				</p> -->

				<p class="coluna">
					<label for="ingresso-entregue-data">Data da entrega</label>
					<input type="text" name="data" class="input" id="ingresso-entregue-data" value="<? echo ($loja_recebido) ? $loja_data_recebimento : date('d/m/Y'); ?>" />
				</p>

				<p class="coluna">
					<label for="ingresso-entregue-hora">Hora da entrega</label>
					<input type="text" name="hora" class="input" id="ingresso-entregue-hora" value="<? echo ($loja_recebido) ? $loja_hora_recebimento : date('H:i'); ?>" />
				</p>

				<section id="ingresso-entregue-locais" class="selectbox coluna">
					<h3>Recebido em</h3>
					<a href="#" class="arrow"><strong>Recebido em</strong><span></span></a>
					<ul class="drop">
						<li><label class="item"><input type="radio" name="recebido" value="centro" alt="Centro"/>Centro</label></li>
						<li><label class="item"><input type="radio" name="recebido" value="ipanema" alt="Ipanema"/>Ipanema</label></li>
						<li><label class="item"><input type="radio" name="recebido" value="leblon" alt="Leblon"/>Leblon</label></li>
						<li><label class="item"><input type="radio" name="recebido" value="hotel" alt="Hotel"/>Hotel</label></li>
					</ul>
				</section>

				<script type="text/javascript">$(document).ready(function() { $('section#ingresso-entregue-locais.selectbox input:radio').radioSel('<? echo $loja_recebido_local; ?>') })</script>
				<?

				break;

				//-----------------------------------------------------------------//

				default:
				
					$tipo = 'entregue';

				?>
				<p>
					<label for="ingresso-entregue-nome">Nome</label>
					<input type="text" name="nome" class="input" id="ingresso-entregue-nome" value="<? echo $loja_entregue_nome; ?>" />
				</p>
				
				<p class="coluna">
					<label>Data prevista</label>
					<input type="text" name="data-para" class="input disabled" disabled="disabled" value="<? echo $loja_data_para_entrega; ?>" />
				</p>

				<p class="coluna">
					<label for="ingresso-entregue-data">Data da entrega</label>
					<input type="text" name="data" class="input" id="ingresso-entregue-data" value="<? echo ($loja_entregue) ? $loja_data_entrega : date('d/m/Y'); ?>" />
				</p>
				<p class="coluna">
					<label for="ingresso-entregue-hora">Hora da entrega</label>
					<input type="text" name="hora" class="input" id="ingresso-entregue-hora" value="<? echo ($loja_entregue) ? $loja_hora_entrega : date('H:i'); ?>" />
				</p>
				<?

				break;
			}

			?>
		
			<input type="hidden" name="tipo" value="<? echo $tipo ?>" />

			<div class="clear"></div>

		</section>
		
		<footer class="controle">
			<input type="submit" class="submit coluna" value="<? echo $loja_entregue ? 'Alterar' : 'Confirmar'; ?>" />
			<? if($loja_entregue || $loja_encaminhado || $loja_recebido) { ?><input type="submit" class="submit coluna cancelar-expedicao" name="cancelar" value="Desfazer" /><? } ?>
			<a href="#" class="cancel no-cancel fancy-close coluna">Cancelar</a>
			<p>
			<?
			if($tipo == 'entregue') {
				if($loja_delivery) echo 'Delivery - '.ucfirst($loja_delivery_periodo);
				else { echo 'Retirada'; if(!empty($loja_retirada)) { echo ' - '.ucfirst($loja_retirada); }}
			}
			?>
			</p>
			<div class="clear"></div>
		</footer>


	</form>

</section>
<?

	}
}

//-----------------------------------------------------------------//

// include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>
</body>
</html>