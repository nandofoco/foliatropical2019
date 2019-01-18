<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

//Pagina atual
define('PGATUAL', 'ingressos/venda/novo/');

$tipo_ingresso = format($_GET['t']);
$evento = (int) $_SESSION['usuario-carnaval'];

?>
<section id="conteudo">
	<form id="ingresso-venda-novo" method="post" action="<? echo SITE; ?>ingressos/venda/novo/post/">
		<header class="titulo">
			<h1>Ingresso Venda <span>Novo</span></h1>
		</header>
		
		<? include('include/secao-tipo-setor.php'); ?>

		<section class="secao label-top">
			<section class="checkbox infield dias coluna">
				<h3>Selecione os dias</h3>
				<ul>
				<?

				$sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, SUBSTRING(CONVERT(CHAR(8), ED_DATA, 103), 1, 5) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_eventos_dias)){

					while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
						
						$eventos_dias_cod = $ar_eventos_dias['ED_COD'];
						$eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);
						$eventos_dias_data = $ar_eventos_dias['DATA'];
						$eventos_dias_semana = $semana_min[($ar_eventos_dias['SEMANA']-1)];
						
					?>
					<li>
						<label class="item"><input type="checkbox" name="dia[]" value="<? echo $eventos_dias_cod; ?>" />
							<h5><? echo $eventos_dias_nome; ?></h5>
							<p><? echo $eventos_dias_semana; ?></p>
							<span><? echo $eventos_dias_data; ?></span>
						</label>
					</li>
					<?
					}
				}

				?>
				</ul>
			</section>

			<?

			// Selecionar opcoes
			$sql_vendas_opcoes = sqlsrv_query($conexao, "SELECT * FROM vendas_opcoes WHERE VO_TIPO='$tipo_ingresso' AND D_E_L_E_T_=0 ORDER BY VO_ORDEM ASC", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_vendas_opcoes) > 0) {

				while ($vendas_opcoes = sqlsrv_fetch_array($sql_vendas_opcoes)) {
					
					$vendas_opcoes_cod = $vendas_opcoes['VO_COD'];
					$vendas_opcoes_label = utf8_encode($vendas_opcoes['VO_LABEL']);
					$vendas_opcoes_nome_exibicao = $vendas_opcoes['VO_NOME_EXIBICAO'];
					$vendas_opcoes_nome_insercao = $vendas_opcoes['VO_NOME_INSERCAO'];
					$vendas_opcoes_modelo = $vendas_opcoes['VO_MODELO'];
					$vendas_opcoes_valores = $vendas_opcoes['VO_VALORES'];
					$vendas_opcoes_tamanho = $vendas_opcoes['VO_TAMANHO'];
					$vendas_opcoes_acao = $vendas_opcoes['VO_ACAO'];

					if($vendas_opcoes_nome_exibicao == 'vagas') $vendas_opcoes_vagas = $vendas_opcoes_valores;

					switch ($vendas_opcoes_modelo) {
						case 'checkbox-outfield':
						case 'checkbox-infield':
						case 'radio-infield':
						case 'radio-outfield':

							$vendas_opcoes_valores = json_decode($vendas_opcoes_valores);

							$vendas_opcoes_tipo = (preg_match("/^radio/", $vendas_opcoes_modelo)) ? 'radio' : 'checkbox';
							$vendas_opcoes_position = (preg_match("/infield$/", $vendas_opcoes_modelo)) ? 'infield' : 'outfield';							

							?>
							<section id="<? echo $tipo; ?>-opcao-<? echo $vendas_opcoes_nome_exibicao; ?>" class="<? echo $vendas_opcoes_tipo; ?> <? echo $vendas_opcoes_position; ?> <? echo $vendas_opcoes_tamanho; ?> vendas-opcoes coluna">
								<h3><? echo $vendas_opcoes_label; ?></h3>
								<ul>
								<? foreach ($vendas_opcoes_valores as $key => $value) { ?>									
									<li>
										<label class="item">
											<input type="<? echo $vendas_opcoes_tipo; ?>" name="<? echo $vendas_opcoes_nome_exibicao; if(($vendas_opcoes_tipo == 'checkbox') && count($vendas_opcoes_valores) > 1) echo '[]'; ?>" value="<? echo $value; ?>" />
											<? if ($vendas_opcoes_position == 'infield'){ echo $key; } ?>
										</label>
									</li>
								<? } ?>
								</ul>
							</section>
							<?
						
						break;

						case 'selectbox':

							$vendas_opcoes_valores = json_decode($vendas_opcoes_valores);
							
							?>
							<section id="<? echo $tipo; ?>-opcao-<? echo $vendas_opcoes_nome_exibicao; ?>" class="selectbox vendas-opcoes coluna">
								<h3><? echo $vendas_opcoes_label; ?></h3>
								<a href="#" class="arrow"><strong><? echo ($vendas_opcoes_tamanho == 'small') ? 'Sel.' : 'Selecione'; ?></strong><span></span></a>
								<ul class="drop">
									<? foreach ($vendas_opcoes_valores as $key => $value) { ?>									
									<li><label class="item"><input type="radio" name="<? echo $vendas_opcoes_nome_exibicao; ?>" value="<? echo $value; ?>" alt="<? echo utf8_encode($key); ?>" /><? echo utf8_encode($key); ?></label></li>
									<? } ?>
								</ul>
							</section>
							<?

						break;

						case 'hidden':							
							?>
							<input type="hidden" name="<? echo $vendas_opcoes_nome_exibicao; ?>" id="ingresso-<? echo $vendas_opcoes_nome_exibicao; ?>" value="<? echo $vendas_opcoes_valores; ?>" />
							<?
						break;

						case 'input':
							
							?>
							<p id="<? echo $tipo; ?>-opcao-<? echo $vendas_opcoes_nome_exibicao; ?>" class="coluna vendas-opcoes">
								<label for="ingresso-<? echo $vendas_opcoes_nome_exibicao; ?>"><? echo $vendas_opcoes_label; ?></label>
								<input type="text" name="<? echo $vendas_opcoes_nome_exibicao; ?>" class="input <? echo $vendas_opcoes_acao; ?>" id="ingresso-<? echo $vendas_opcoes_nome_exibicao; ?>" value="<? echo $vendas_opcoes_valores; ?>" />
							</p>
							<?

							if($vendas_opcoes_nome_exibicao == 'estoque') {
							?>
							<section id="<? echo $tipo; ?>-opcao-<? echo $vendas_opcoes_nome_exibicao; ?>-aviso" class="coluna aviso">
								<a href="#" class="aviso">?</a>
								<div class="tooltip">Ao atingir o estoque será emitido um aviso, mas não bloqueará a venda do ingresso.<span></span></div>
							</section>
							<?
							}

						break;

						/*case 'range':
							
							if(!empty($vendas_opcoes_valores)) $vendas_opcoes_valores = json_decode($vendas_opcoes_valores);

							?>
							<p id="<? echo $tipo; ?>-opcao-<? echo $vendas_opcoes_nome_exibicao; ?>-de" class="coluna vendas-opcoes">
								<label for="ingresso-<? echo $vendas_opcoes_nome_exibicao; ?>-de"><? echo $vendas_opcoes_label; ?> de:</label>
								<input type="text" name="<? echo $vendas_opcoes_nome_exibicao; ?>[de]" class="input" id="ingresso-<? echo $vendas_opcoes_nome_exibicao; ?>-de" value="<? echo $vendas_opcoes_valores[0]; ?>" />
							</p>
							<p id="<? echo $tipo; ?>-opcao-<? echo $vendas_opcoes_nome_exibicao; ?>-ate" class="coluna vendas-opcoes">
								<label for="ingresso-<? echo $vendas_opcoes_nome_exibicao; ?>-ate"><? echo $vendas_opcoes_label; ?> até:</label>
								<input type="text" name="<? echo $vendas_opcoes_nome_exibicao; ?>[ate]" class="input" id="ingresso-<? echo $vendas_opcoes_nome_exibicao; ?>-ate" value="<? echo $vendas_opcoes_valores[1]; ?>" />
							</p>
							<?

						break;*/
						
					}
				}

			}

			?>


			<div class="clear"></div>
		</section>

		<?

		$sql_vendas_adicionais = sqlsrv_query($conexao, "SELECT * FROM vendas_adicionais WHERE VA_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY VA_COD ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_vendas_adicionais) > 0) {

		?>
		<section class="secao">

			<section id="ingresso-vendas-adicionais">
				<h3>Selecione os adicionais</h3>

				<table class="lista">
					<tbody>
					<?

					

						while ($vendas_adicionais = sqlsrv_fetch_array($sql_vendas_adicionais)) {
							$vendas_adicionais_cod = $vendas_adicionais['VA_COD'];
							$vendas_adicionais_tipo = $vendas_adicionais['VA_TIPO'];
							$vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
							$vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
							$vendas_adicionais_nome_insercao = $vendas_adicionais['VA_NOME_INSERCAO'];

						?>
						<tr>
							<td class="check">
								<section class="checkbox verify vendas-adicionais">
									<ul><li><label class="item"><input type="checkbox" name="adicionaiscod[]" value="<? echo $vendas_adicionais_cod; ?>" /></label></li></ul>
								</section>
							</td>
							<td class="nome"><? echo $vendas_adicionais_label; ?></td>
							<td class="incluso">
								<section class="checkbox infield vendas-adicionais">
									<ul><li><label class="item"><input type="checkbox" name="adicionaisincluso[<? echo $vendas_adicionais_cod; ?>]" value="true" />Incluso</label></li></ul>
								</section>
							</td>
							<td class="valor">
								<p class="vendas-adicionais">
									<input type="text" name="adicionaisvalor[<? echo $vendas_adicionais_cod; ?>]" class="input money visible disabled" disabled="disabled" id="ingresso-adicioais-<? echo $vendas_adicionais_cod; ?>" value="R$ 0,00" />
								</p>
							</td>
						</tr>
							
						<?

						}
					?>
					</tbody>
				</table>
			</section>
		</section>
		<?
		}
		?>


		<footer class="controle">
			<input type="submit" class="submit coluna" value="Inserir" />
			<a href="#" class="cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>

	</form>

</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>