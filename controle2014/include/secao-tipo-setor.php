<?

// precisamos das variáveis $tipo_ingresso e $evento para exibir corretamente a página
if (basename($_SERVER["PHP_SELF"]) == "secao-tipo-setor.php") die("Este arquivo não pode ser acessado diretamente.");

?>
<section id="tipo-setor" class="secao label-top">
	<section id="tipo-ingresso" class="coluna">
		<h3>Selecione o tipo de ingresso</h3>
		<ul>
		<?

		$sql_tipo_ingresso = sqlsrv_query($conexao, "SELECT * FROM tipos WHERE D_E_L_E_T_=0 ORDER BY TI_ORDEM ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_tipo_ingresso) !== false){

			while ($ar_tipo_ingresso = sqlsrv_fetch_array($sql_tipo_ingresso)) {
				
				$tipo_ingresso_cod = $ar_tipo_ingresso['TI_COD'];
				$tipo_ingresso_nome = utf8_encode($ar_tipo_ingresso['TI_NOME']);
				$tipo_ingresso_tag = $ar_tipo_ingresso['TI_TAG'];
				$tipo_ingresso_ordem = $ar_tipo_ingresso['TI_ORDEM'];
				
				if(($tipo_ingresso == $tipo_ingresso_tag) || (empty($tipo_ingresso) && ($tipo_ingresso_ordem==1))) {
					$tipo_ingresso = $tipo_ingresso_cod;
					$tipo = $tipo_ingresso_tag;
				}

				if(defined('PGCOMPRA')) {

					$disabled = false;

					/*"SELECT * FROM(SELECT VE_SETOR, VE_DIA, @ingresso:=VE_COD AS COD,
					@ingressos:=(SELECT COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.LI_INGRESSO=@ingresso AND li.D_E_L_E_T_=0) AS QTDE,
					@total := CAST((VE_ESTOQUE - @ingressos) AS SIGNED) AS TOTAL
					FROM vendas
					WHERE VE_EVENTO='$evento' AND VE_TIPO='$tipo_ingresso_cod' AND VE_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY VE_SETOR, VE_DIA) S
					HAVING TOTAL > 0";*/

					//Buscar disponibilidade do tipo e setores
					$sql_disponibilidade = sqlsrv_query($conexao, "
						DECLARE @vendas TABLE (VE_COD INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT);
						DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);

						INSERT INTO @vendas (VE_COD, VE_ESTOQUE, VE_SETOR, VE_DIA)
						SELECT VE_COD, VE_ESTOQUE, VE_SETOR, VE_DIA FROM vendas WHERE VE_EVENTO='$evento' AND VE_TIPO='$tipo_ingresso_cod' AND VE_BLOCK=0 AND D_E_L_E_T_=0;

						INSERT INTO @qtde (COD, QTDE)
						SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;

						SELECT * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL FROM @vendas v 
						LEFT JOIN @qtde q ON v.VE_COD = q.COD) S WHERE TOTAL > 0 ORDER BY VE_SETOR, VE_DIA", $conexao_params, $conexao_options);

					if(sqlsrv_next_result($sql_disponibilidade) && sqlsrv_next_result($sql_disponibilidade))
					if(sqlsrv_num_rows($sql_disponibilidade) !== false) {
						$ardisp = $ardispdias = array();
						while ($ar = sqlsrv_fetch_array($sql_disponibilidade)) {
							if(!in_array($ar['VE_SETOR'],$ardisp)) array_push($ardisp, $ar['VE_SETOR']);

							//Adicionamos o dia
							$ardispdias[$ar['VE_SETOR']][count($ardispdias[$ar['VE_SETOR']])] = (string) $ar['VE_DIA'];

						}
						$disponibilidade[$tipo_ingresso_cod] = array('setores' => $ardisp, 'dias' => $ardispdias);
					} else {
						$disabled = true;
					}
				}
				
			?>
			<li><a href="<? echo (defined('PGATUAL') && !$disabled) ? SITE.PGATUAL.$tipo_ingresso_tag.'/' : '#' ; ?>" class="item <? if ($disabled){ echo 'disabled '; }  if ($tipo_ingresso == $tipo_ingresso_cod) echo 'checked'; ?>"><? echo $tipo_ingresso_nome; ?></a></li>
			<?
				unset($ar, $ardisp, $ardispdias);
			}
		}

		?>
		</ul>
		<input type="hidden" name="tipo" value="<? echo $tipo_ingresso ?>" />
	</section>

	<section id="setor-ingresso" class="radio coluna">
		<h3>Selecione o setor</h3>
		<ul>
		<?

		// Verificar se temos ingresso para o setor e dia selecionados
		$sql_setor_ingresso = sqlsrv_query($conexao, "SELECT ES_COD, ES_NOME FROM eventos_setores WHERE ES_EVENTO='$evento' AND ES_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY LEN(ES_NOME) ASC, ES_NOME ASC", $conexao_params, $conexao_options);
		if(sqlsrv_num_rows($sql_setor_ingresso) !== false){

			while ($ar_setor_ingresso = sqlsrv_fetch_array($sql_setor_ingresso)) {
				
				$setor_ingresso_cod = $ar_setor_ingresso['ES_COD'];
				$setor_ingresso_nome = utf8_encode($ar_setor_ingresso['ES_NOME']);

				if(defined('PGCOMPRA')) {
					$disabled = false;
					if(!in_array($setor_ingresso_cod, $disponibilidade[$tipo_ingresso]['setores'])) $disabled = true;
					else {
						if(count($disponibilidade[$tipo_ingresso]['dias'][$setor_ingresso_cod]) > 0){
							$dias = json_encode($disponibilidade[$tipo_ingresso]['dias'][$setor_ingresso_cod]);
							$dias = "rel='".$dias."'";
						}
					}
				}
				
			?>
			<li><label class="item <? if($setor_ingresso == $setor_ingresso_cod) { echo 'checked'; } if($disabled) { echo 'disabled'; } ?>"><input type="radio" name="setor" value="<? echo $setor_ingresso_cod; ?>" <? if($setor_ingresso == $setor_ingresso_cod) { echo 'checked="checked"'; } ?> <? if($disabled) { echo 'class="disabled"'; } echo $dias; ?> /><? echo $setor_ingresso_nome; ?></label></li>
			<?
				unset($disabled,$dias);
			}
		}

		?>
		</ul>
	</section>
	<div class="clear"></div>
</section>