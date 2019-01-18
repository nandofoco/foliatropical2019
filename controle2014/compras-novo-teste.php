<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

if($_GET['cancelar']) unset($_SESSION['compra-interna']);

//Pagina atual
define('PGATUAL', 'compras/novo/');
define('PGCOMPRA', 'true');

$tipo_ingresso = format($_GET['t']);
$evento = (int) $_SESSION['usuario-carnaval'];

?>
<section id="conteudo">
	<form id="compras-novo" method="post" action="<? echo SITE; ?>compras/novo/adicionar/">
		<header class="titulo">
			<h1>Vendas <span>Nova</span></h1>
		</header>
		
		<? //////////////////// BEM AQUI //////////////////// ?>

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
                                SELECT VE_COD, VE_ESTOQUE, VE_SETOR, VE_DIA FROM vendas WHERE VE_EVENTO='$evento' AND VE_TIPO='$tipo_ingresso_cod' AND VE_BLOCK=0 AND D_E_L_E_T_=0 /*AND ((VE_LOTE IS NOT NULL AND VE_LOTE_ATIVO = 1) OR VE_LOTE IS NULL)*/;

                                INSERT INTO @qtde (COD, QTDE)
                                SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;

                                SELECT * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL FROM @vendas v 
                                LEFT JOIN @qtde q ON v.VE_COD = q.COD) S WHERE TOTAL > 0 ORDER BY VE_SETOR, VE_DIA", $conexao_params, $conexao_options);

                            if(sqlsrv_next_result($sql_disponibilidade) && sqlsrv_next_result($sql_disponibilidade))
                            if(sqlsrv_num_rows($sql_disponibilidade) !== false) {

                                echo 'aqui';
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

                print_r($disponibilidade);

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



		<section class="secao label-top">
			<section id="compra-dias" class="radio infield dias coluna">
				<h3>Selecione o dia</h3>
				<ul>
				<?

				$sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, SUBSTRING(CONVERT(CHAR(8), ED_DATA, 103), 1, 5) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC;", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_eventos_dias) !== false){

					while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
						
						$eventos_dias_cod = $ar_eventos_dias['ED_COD'];
						$eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);
						$eventos_dias_data = $ar_eventos_dias['DATA'];
						$eventos_dias_semana = $semana_min[($ar_eventos_dias['SEMANA']-1)];
						
					?>
					<li>
						<label class="item disabled"><input type="radio" name="dia" value="<? echo $eventos_dias_cod; ?>" class="disabled" />
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

				<div class="clear"></div>

				<section class="aviso-descontos">Desconto e over devem ser lançados como valores unitários.</section>

			</section>

			<section id="compras-itens"></section>

			<div class="clear"></div>
		</section>

		<footer class="controle">
			<input type="submit" class="submit coluna" value="Adicionar" />
			<!-- <a href="#" class="cancel coluna">Cancelar</a> -->
			<div class="clear"></div>
		</footer>
	</form>

	<?

	if($_GET['teste']) print_r($_SESSION['compra-interna']);

	if(count($_SESSION['compra-interna']) > 0) {

	?>	
	<form id="compras-carrinho" method="post" action="<? echo SITE; ?>compras/novo/adicionais/">
		<header class="titulo">
			<h1>Ingressos <span>Carrinho</span></h1>
		</header>
		
		<section class="secao bottom">
			<table class="lista">
				<thead>
					<tr>
						<th class="setor"><strong>Setor</strong></th>
						<th><strong>Data Ref.</strong></th>
						<th><strong>Tipo do Ingresso</strong></th>
						<th><strong>Qtde.</strong></th>
						<th class="right"><strong>Valor</strong></th>
						<th>&nbsp;</th>
					</tr>
					<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
				</thead>
				<tbody>
				<?

				$idisponivel = 0;

				foreach ($_SESSION['compra-interna'] as $key => $carrinho) {

					/*"SELECT v.*, t.TI_NOME, d.ED_NOME, s.ES_NOME,
						@ingresso:=v.VE_COD AS COD,
						@ingressos:=(SELECT COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.LI_INGRESSO=@ingresso AND li.D_E_L_E_T_=0) AS QTDE,
						@total := CAST((v.VE_ESTOQUE - @ingressos) AS SIGNED), IF(@total < 0,0, @total) AS TOTAL
						FROM vendas v, tipos t, eventos_dias d, eventos_setores s WHERE v.VE_COD='".$carrinho['item']."' AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 AND d.ED_COD=v.VE_DIA AND t.TI_COD=v.VE_TIPO AND s.ES_COD=v.VE_SETOR AND d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0 LIMIT 1";*/
					
					$sql_ingressos = sqlsrv_query($conexao, "
						DECLARE @ingresso INT='".$carrinho['item']."';
						DECLARE @vendas TABLE (VE_COD INT, VE_TIPO INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT, VE_FILA VARCHAR(255), VE_VAGAS INT, VE_TIPO_ESPECIFICO VARCHAR(255));
						DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);

						INSERT INTO @vendas (VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO)
                SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM vendas WHERE VE_COD=@ingresso AND VE_BLOCK=0 AND D_E_L_E_T_=0 /*AND ((VE_LOTE IS NOT NULL AND VE_LOTE_ATIVO = 1) OR VE_LOTE IS NULL)*/;

						INSERT INTO @qtde (COD, QTDE)
						SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE li.LI_INGRESSO=@ingresso AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;

						SELECT TOP 1 * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL, t.TI_NOME, d.ED_NOME, s.ES_NOME FROM @vendas v 
						LEFT JOIN @qtde q ON v.VE_COD = q.COD
						LEFT JOIN tipos t ON t.TI_COD=v.VE_TIPO
						LEFT JOIN eventos_dias d ON d.ED_COD=v.VE_DIA
						LEFT JOIN eventos_setores s ON s.ES_COD=v.VE_SETOR
						WHERE d.D_E_L_E_T_=0 AND t.D_E_L_E_T_=0 AND s.D_E_L_E_T_=0) S", $conexao_params, $conexao_options);
					
					if(sqlsrv_next_result($sql_ingressos) && sqlsrv_next_result($sql_ingressos))
					if(sqlsrv_num_rows($sql_ingressos) !== false) {

					$i=1;
					$ingressos = sqlsrv_fetch_array($sql_ingressos);

					$ingressos_cod = $ingressos['VE_COD'];
					$ingressos_setor = utf8_encode($ingressos['ES_NOME']);
					$ingressos_dia = utf8_encode($ingressos['ED_NOME']);
					$ingressos_tipo = utf8_encode($ingressos['TI_NOME']);
					$ingressos_valor = $carrinho['valor'];
					$ingressos_valor_total = number_format(($ingressos_valor * $carrinho['qtde']),2,",",".");
					
					$ingressos_fila = utf8_encode($ingressos['VE_FILA']);
					$ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
					$ingressos_estoque = (int) $ingressos['TOTAL'];
					$ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);

					//Calculo de estoque
					if(($ingressos_tipo_especifico == 'fechado') && ($ingressos_vaga > 0)) {
						$ingressos_estoque = $ingressos['VE_ESTOQUE'] / $ingressos_vaga;
						$ingressos_estoque = $ingressos_estoque - ($ingressos['QTDE'] / $ingressos_vaga);
					}

					$ingressos_block = ($ingressos_estoque == 0);
					$ingresso_indisponivel = ($ingressos_estoque < $carrinho['qtde']);
					$_SESSION['compra-interna'][$key]['disabled'] = ($ingressos_block) ? true : false;

					if(!$ingressos_block) $idisponivel++;

					?>
					<tr <? if ($ingressos_block || $ingresso_indisponivel) { echo 'class="block"'; } ?>>
						<td class="setor"><? echo $ingressos_setor; ?></td>
						<td class="data"><? echo $ingressos_dia; ?> dia</td>
						<td class="tipo">
						<?
							echo $ingressos_tipo;
							if(!empty($ingressos_fila)) { echo " ".$ingressos_fila; }
							if(!empty($ingressos_tipo_especifico)) { echo " ".$ingressos_tipo_especifico; }
							if(($ingressos_vaga > 0) && ($ingressos_tipo_especifico == 'fechado')) { echo " (".$ingressos_vaga." vagas)"; }
						?>
						</td>
						<td class="qtde">
							<p>
								<input type="text" name="quantidade[<? echo $key; ?>]" class="input qtde <? if ($ingressos_block){ echo 'block'; } ?>" value="<? echo $carrinho['qtde']; ?>" rel="<? echo $key; ?>" <? if ($ingressos_block){ echo 'disabled="disabled"'; } ?> />
								<? if ($ingresso_indisponivel){ ?>
									<span class="aviso"><? echo $ingressos_estoque; ?> disponíve<? echo ($ingressos_estoque==1) ? 'l' : 'is' ; ?></span>
								<? } ?>
							</p>
							<input type="hidden" name="estoque" value="<? echo $ingressos_estoque; ?>" />
							<input type="hidden" name="valor" value="<? echo $ingressos_valor; ?>" />
						</td>
						<td class="valor">R$ <? echo $ingressos_valor_total; ?></td>
						<td class="ctrl small">
							<a href="<? echo SITE; ?>e-compras-adicionar.php?c=<? echo $key; ?>&a=excluir" class="excluir confirm" title="Tem certeza que deseja excluir o ingresso?"></a>
						</td>
					</tr>
					<?
					}
				}

				?>
				</tbody>
			</table>
		</section>
		
		<footer class="controle">
			<? if ($idisponivel > 0){ ?><input type="submit" class="submit coluna" value="Comprar" /><? } ?>
			<a href="<? echo SITE; ?>compras/novo/?cancelar=true" class="cancel no-cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>

	</form>
	<?

	}

	?>

</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>