<?
//Incluir funções básicas
include("include/includes.php");
//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");
//Definir o carnaval ativo
include("include/setcarnaval.php");
$d=(int)$_GET['d'];

//-----------------------------------------------------------------//
/*if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>login-cadastro-site/ingressos/';
</script>
<?
	exit();
}*/
//-----------------------------------------------------------------//
$evento = setcarnaval();
if($_GET['cancelar']) unset($_SESSION['compra-site']);
//Tipo de ingresso selecionado
$tipo_ingresso = format($_GET['t']);
//Canonical
$meta_canonical = SITE.$link_lang.$lg['link_compre'];
//-----------------------------------------------------------------//
if($session_language == 'US') {
	
	switch ($tipo_ingresso) {
		case 'vip-boxes':
			$tipo_ingresso = 'camarote';
		break;
		case 'corporate-vip-boxes':
		 	$tipo_ingresso = 'camarote-corporativo';
		break;
		case 'grand-stands':
		 	$tipo_ingresso = 'arquibancada';
		break;
		case 'vip-seats':
		 	$tipo_ingresso = 'frisa';
		break;
	}	
}
if(!empty($tipo_ingresso)) $scroll = true;
$tipo_ingresso = !empty($tipo_ingresso) ? $tipo_ingresso : 'folia-tropical';
switch ($tipo_ingresso) {
	case 'folia-tropical':
		$tipo_ingresso_selected = 'lounge';
	break;
	case 'camarote-corporativo':
	 	$tipo_ingresso_selected = 'camarote';
	break;
	case 'super-folia':
	 	$tipo_ingresso_selected = 'super';
	break;
	/*case 'camarote':
	case 'camarote-corporativo':
	case 'arquibancada':
	case 'frisa':
		?>
		<script type="text/javascript">
		location.href='<? echo SITE.$link_lang; ?>ingressos/';
		</script>
		<?
		exit();
	break;*/
	default:
		$tipo_ingresso_selected = $tipo_ingresso;
	break;
}
if($tipo_ingresso) {
	$meta_title = $lg['meta_title_compre_'.$tipo_ingresso];	
	$meta_description = $lg['meta_description_compre_'.$tipo_ingresso];
	$compretitle = $lg['compre_titulo_'.$tipo_ingresso];
} else {
	$meta_title = $lg['meta_title_compre'];
	$meta_description = $lg['meta_description_compre'];
	$compretitle = $lg['compre_titulo'];
}
//arquivos de layout
include("include/head.php");
include("include/header.php");
//-----------------------------------------------------------------------------//
$tipo_ingresso_folia = ($tipo_ingresso == 'folia-tropical') ? true : false;
$tipo_ingresso_superfolia = ($tipo_ingresso == 'super-folia') ? true : false;
?>


<section id="conteudo">
	
	<? /*if(date('Y-m-d H:i:s') >= '2017-03-03 22:00:00') { ?>
	<div id="pop">
		<img src="<? echo SITE; ?>img/pop2017.jpg" />
		<a href="#" class="closepop"></a>
	</div>
	<? }*/ ?>

	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; <? echo $lg['menu_loja']; ?>
	</div>

	<section id="compre-aqui" class="<? echo $tipo_ingresso; ?>">

		<form id="form-compre-aqui" method="post" action="<? echo SITE.$link_lang; ?>ingressos/adicionar/">
			<header>
				<h1><? echo $compretitle; ?></h1>
			</header>

			<section id="tipo-setor">
				<h2>1 • <? echo $lg['compre_selecione_ingresso']; ?></h2>
				<ul>
				<?
				// $tipos = array('folia-tropical'=>'Folia Tropical', 'camarote' => 'Camarote', 'camarote-corporativo' => 'Camarote Corporativo', 'arquibancada' => 'Arquibancada', 'frisa' => 'Frisa', 'cadeira' => 'Cadeira');
				// $tipos = array('folia-tropical'=>'Folia Tropical', 'camarote' => 'Camarote', 'camarote-corporativo' => 'Camarote Corporativo', 'arquibancada' => 'Arquibancada', 'frisa' => 'Frisa');
				//$tipos = array('folia-tropical'=>'Folia Tropical', 'super-folia'=>'Super Folia', 'camarote' => 'Camarote Corporativo', 'arquibancada' => 'Arquibancada', 'frisa' => 'Frisa');
				// $tipos = array('camarote' => 'Camarote Corporativo', 'arquibancada' => 'Arquibancada', 'frisa' => 'Frisa');
				$tipos = array('camarote' => 'Camarote Corporativo', 'frisa' => 'Frisa');
				foreach ($tipos as $tipo => $tipo_titulo) {
					$disable_tipo = false;
					switch ($tipo) {
						case 'folia-tropical':
							$search_tipo = 'lounge';
							//Setor folia
							$sql_setor_folia = sqlsrv_query($conexao, "SELECT TOP 1 ES_COD FROM eventos_setores WHERE ES_NOME='FT' AND ES_EVENTO='$evento' AND ES_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
							if(sqlsrv_num_rows($sql_setor_folia) > 0) $setor_folia = sqlsrv_fetch_array($sql_setor_folia);
							$setor_folia = $setor_folia['ES_COD'];
							$search = " AND VE_SETOR='$setor_folia' ";
							#Retirar
							$disable_tipo = true;
						break;
						case 'super-folia':
							$search_tipo = 'super';
							//Setor folia
							$sql_setor_folia = sqlsrv_query($conexao, "SELECT TOP 1 ES_COD FROM eventos_setores WHERE ES_NOME='FT' AND ES_EVENTO='$evento' AND ES_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
							if(sqlsrv_num_rows($sql_setor_folia) > 0) $setor_folia = sqlsrv_fetch_array($sql_setor_folia);
							$setor_folia = $setor_folia['ES_COD'];
							$search = " AND VE_SETOR='$setor_folia' ";
							#Retirar
							$disable_tipo = true;
						break;
						case 'camarote':
							$search_tipo = 'camarote';
							$search = " AND VE_TIPO_ESPECIFICO<>'fechado' ";
							#Retirar
							#$disable_tipo = true;
						break;
						case 'camarote-corporativo':
							$search_tipo = 'camarote';
							$search = " AND VE_TIPO_ESPECIFICO='fechado' ";
							#Retirar
							#$disable_tipo = true;
						break;
						case 'arquibancada':
							$sql_setor_nove = sqlsrv_query($conexao, "SELECT TOP 1 ES_COD FROM eventos_setores WHERE ES_NOME='9' AND ES_EVENTO='$evento' AND ES_BLOCK=0 AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
							if(sqlsrv_num_rows($sql_setor_nove) > 0) $setor_nove = sqlsrv_fetch_array($sql_setor_nove);
							$setor_nove = $setor_nove['ES_COD'];
							$search_tipo = $tipo;
							unset($search);
							#Retirar
							$disable_tipo = true;
						break;
						// case 'frisa':
						// 	#Retirar
						// 	// $disable_tipo = true;
						// break;
						default:
							$search_tipo = $tipo;
							#Retirar
							#$disable_tipo = true;
							unset($search);
						break;
					}
					
					$sql_tipo_ingresso = sqlsrv_query($conexao, "SELECT * FROM tipos WHERE TI_TAG='$search_tipo' AND D_E_L_E_T_=0 ORDER BY TI_ORDEM ASC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_tipo_ingresso) !== false){
						while ($ar_tipo_ingresso = sqlsrv_fetch_array($sql_tipo_ingresso)) {
							
							$tipo_ingresso_cod = $ar_tipo_ingresso['TI_COD'];
							$tipo_ingresso_nome = utf8_encode($ar_tipo_ingresso['TI_NOME']);
							$tipo_ingresso_tag = $ar_tipo_ingresso['TI_TAG'];
							$tipo_ingresso_ordem = $ar_tipo_ingresso['TI_ORDEM'];
							// if(($tipo_ingresso_selected == $tipo_ingresso_tag)) $tipo_ingresso_id = $tipo_ingresso_cod;
							if($tipo_ingresso_selected == $tipo_ingresso_tag) $tipo_ingresso_id = $tipo_ingresso_cod;
							if(($tipo_ingresso_selected == 'folia') && ($tipo_ingresso_tag == 'lounge')) $tipo_ingresso_id = $tipo_ingresso_cod;
							$disabled = true;
							
							if(!$disable_tipo) {
								// - Folia Tropical 1° Dia (candybox) - bloqueio dia 04/02 as 22:00
								// - Folia Tropical 2° Dia (terrasse) - bloqueio dia 05/02 as 22:00
								// - Folia Tropical 3° Dia (domingo) - bloqueio dia 06/02 as 22:00
								// - Folia Tropical 4° Dia (segunda) - bloqueio dia 07/02 as 22:00
								// - Folia Tropical 5° Dia (campeãs) - bloqueio dia 12/02 as 22:00
								// - Arquibancada setor 9 - bloqueio dia 12/02 as 22:00
								// - Frisas 3° e 4° Dia (Domingo e Segunda) - bloqueio dia 01/02 as 22:00
								// - Frisas 5° Dia (campeãs) - bloqueio dia 11/02 as 22:00
								// Bloqueio
								// $tipo_ingresso_selected


								/*$bloqueio = array(
									'lounge' => array(
										'2016-02-04' => '2016-02-05',
										'2016-02-05' => '2016-02-06',
										'2016-02-06' => '2016-02-07',
										'2016-02-07' => '2016-02-12',
										'2016-02-12' => '2016-02-13'
									),
									'super' => array(
										'2016-02-04' => '2016-02-05',
										'2016-02-05' => '2016-02-06',
										'2016-02-06' => '2016-02-07',
										'2016-02-07' => '2016-02-12',
										'2016-02-12' => '2016-02-13'
									),
									'arquibancada' => array(
										'2016-02-12' => '2016-02-13'
									),
									'frisa' => array(
										'2016-02-01' => '2016-02-08',
										'2016-02-11' => '2016-02-13'
									) 
								);

								$bloqueio_query = "";
								$bloqueio_hoje = date('Y-m-d', strtotime('-1 day +4 hours'));
								
								foreach ($bloqueio[$tipo_ingresso_selected] as $k => $r) {
									if($k <= $bloqueio_hoje) {
										$bloqueio_query .= " AND d.ED_DATA > '$r' ";
									}
								}
								*/

								// - Sexta dia 24/02 - Bloqueio dia 23/02 as 22:00
								// - Sábado 25/02 - Bloqueio dia 24/02 as 22:00
								// - Domingo 26/02 - Bloqueio dia 25/02 as 22:00
								// - Segunda 27/02 - Bloqueio dia 26/02 as 22:00
								// - Campeãs 04/03 - Bloqueio dia 03/03 as 22:00

								$bloqueio = array(									
									'2017-02-24' => '2017-02-23 22:00:00',															
									'2017-02-25' => '2017-02-24 22:00:00',
									'2017-02-26' => '2017-02-25 22:00:00',
									'2017-02-27' => '2017-02-26 22:00:00',
									'2017-03-03' => '2017-03-01 22:00:00',
									'2017-03-04' => '2017-03-03 22:00:00'
								);

								$bloqueio_query = "";
								$bloqueio_hoje = date('Y-m-d H:i:s');
								
								foreach ($bloqueio as $k => $r) {
									if($r <= $bloqueio_hoje) {
										$bloqueio_query .= " AND d.ED_DATA > '$k' ";
									}
								}

								//Buscar disponibilidade do tipo e setores
								$sql_disponibilidade = sqlsrv_query($conexao, "
									DECLARE @vendas TABLE (VE_COD INT, VE_ESTOQUE INT, VE_SETOR INT, VE_DIA INT);
									DECLARE @qtde TABLE (COD INT, QTDE INT DEFAULT 0);
									INSERT INTO @vendas (VE_COD, VE_ESTOQUE, VE_SETOR, VE_DIA)
									SELECT v.VE_COD, v.VE_ESTOQUE, v.VE_SETOR, v.VE_DIA FROM vendas v
									LEFT JOIN eventos_dias d ON v.VE_DIA = d.ED_COD
									WHERE v.VE_EVENTO='$evento' AND v.VE_TIPO='$tipo_ingresso_cod' AND v.VE_VALOR>0 AND v.VE_BLOCK=0 AND v.D_E_L_E_T_=0 ".str_replace('VE_', 'v.VE_', $search)." $bloqueio_query;
									INSERT INTO @qtde (COD, QTDE)
									SELECT li.LI_INGRESSO, COUNT(li.LI_COD) FROM loja_itens li, loja l WHERE l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO;
									SELECT * FROM (SELECT ISNULL(q.QTDE, 0) AS QTDE, v.*, CAST((v.VE_ESTOQUE - ISNULL(q.QTDE, 0)) AS INT) AS TOTAL FROM @vendas v 
									LEFT JOIN @qtde q ON v.VE_COD = q.COD) S WHERE TOTAL > 0
									ORDER BY VE_SETOR, VE_DIA", $conexao_params, $conexao_options);
								if(sqlsrv_next_result($sql_disponibilidade) && sqlsrv_next_result($sql_disponibilidade))
								if(sqlsrv_num_rows($sql_disponibilidade) !== false) {
									
									$ardisp = $ardispdias = array();
									while ($ar = sqlsrv_fetch_array($sql_disponibilidade)) {
										$disabled = false;
										$continue = true;
										//Só permitir setor 9 da arquibancada
										if(($tipo_ingresso_selected == 'arquibancada') && ($ar['VE_SETOR'] != $setor_nove)) $continue = false;
										if($continue && ($tipo_ingresso == $tipo)) {
											if(!in_array($ar['VE_SETOR'],$ardisp)) array_push($ardisp, $ar['VE_SETOR']);
											//Adicionamos o dia
											$ardispdias[$ar['VE_SETOR']][count($ardispdias[$ar['VE_SETOR']])] = (string) $ar['VE_DIA'];
										}
									}
									// Adicionamos somente se for o dia selecionado
									if($tipo_ingresso == $tipo) $disponibilidade = array('setores' => $ardisp, 'dias' => $ardispdias);
								}
							}
							#Retirar apos carnaval
							#unset($tipo_ingresso);
							//Link tipo e Tipo titulo
							if($session_language == 'US') {
								switch ($tipo) {
									case 'camarote':
										$link_tipo = 'vip-boxes';
										$tipo_titulo = 'Vip Boxes';
									break;
									case 'camarote-corporativo':
										$link_tipo = 'corporate-vip-boxes';
										$tipo_titulo = 'Corporate Vib Boxes';
									break;
									case 'arquibancada':
										$link_tipo = 'grand-stands';
										$tipo_titulo = 'Grand Stands';
									break;
									case 'frisa':
										$link_tipo = 'vip-seats';
										$tipo_titulo = 'Vip Seats';
									break;
									case 'super-folia':
										$link_tipo = 'super-folia';
										$tipo_titulo = 'Super Folia';
									break;
								}
							} else {
								$link_tipo = $tipo;
							}
							#Retirar
							#$disable_tipo = $disabled = true;
						?>
						<li>
							<? /*<a href="<? echo ($disabled) ? '#' : SITE.$link_lang.'ingressos/'.$tipo.'/'; ?>" class="tipo <? echo $tipo; if ($tipo_ingresso == $tipo) echo ' checked'; if($disabled) { echo ' disabled'; } ?>">*/ ?>
							<a href="<? echo ($disabled) ? '#' : SITE.$link_lang.$lg['link_compre'].$link_tipo.'/'; ?>" class="tipo <? echo $tipo; if ($tipo_ingresso == $tipo) echo ' checked'; if($disabled) { echo ' disabled'; } ?>">
								<span>
									<img src="<? echo SITE; ?>img/bg-produtos-mini-<? echo $tipo; ?>.png" alt="<? echo $tipo_titulo; ?>" />
								</span>
								<h3><? echo $tipo_titulo; ?></h3>
								
								<? //if ($disable_tipo){ ?>
								<? //<div class="consulta"><div></div><? echo /*$lg['compre_preco_consulta'];*/ $lg['menu_em_breve']; </div> ?>
								<? //} ?>
							</a>
						</li>
						<?
							unset($ar, $ardisp, $ardispdias);
						}
					}
				}
				?>
				</ul>
				<input type="hidden" name="tipo" value="<? echo $tipo_ingresso_id; ?>" />
				<input type="hidden" name="tipo-especial" value="<? echo $tipo_ingresso; ?>" />
				<div class="clear"></div>
			</section>

			<section id="setor-ingresso" class="radio <? if ($scroll){ echo 'scroll'; } ?>">
				<h2>2 • <? echo $lg['compre_selecione_setor']; ?></h2>
				<ul>
				<?
				// Verificar se temos ingresso para o setor e dia selecionados
				$sql_setor_ingresso = sqlsrv_query($conexao, "SELECT ES_COD, ES_NOME FROM eventos_setores WHERE ES_EVENTO='$evento' AND ES_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY LEN(ES_NOME) ASC, ES_NOME ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_setor_ingresso) !== false){
					while ($ar_setor_ingresso = sqlsrv_fetch_array($sql_setor_ingresso)) {
						
						$setor_ingresso_cod = $ar_setor_ingresso['ES_COD'];
						$setor_ingresso_nome = utf8_encode($ar_setor_ingresso['ES_NOME']);
						$disabled = false;
						
						if(!in_array($setor_ingresso_cod, $disponibilidade['setores'])) $disabled = true;
						else {
							if(count($disponibilidade['dias'][$setor_ingresso_cod]) > 0){
								$dias = json_encode($disponibilidade['dias'][$setor_ingresso_cod]);
								$dias = "rel='".$dias."'";
							}
						}
						
						$selected = (!$disabled && (count($disponibilidade['setores']) == 1)) ? true : false;
						
					?>
					<li><label class="item <? if(($setor_ingresso == $setor_ingresso_cod) || $selected) { echo 'checked'; } if($disabled) { echo ' disabled'; } ?>"><input type="radio" name="setor" value="<? echo $setor_ingresso_cod; ?>" <? if(($setor_ingresso == $setor_ingresso_cod) || $selected) { echo 'checked="checked"'; } ?> <? if($disabled) { echo 'class="disabled"'; } echo $dias; ?> /><? echo $setor_ingresso_nome; ?></label></li>
					<?
						unset($selected, $disabled,$dias);
					}
				}
				?>
				</ul>
				<div class="clear"></div>
			</section>

			<section id="compra-dias" class="radio">
				<h2>3 • <? echo $lg['compre_selecione_dia']; ?></h2>
				<ul>
				<?
				//ED_DATA NOT IN ('".implode("','", $dias_principais)."')
				// $sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, CONVERT(CHAR(10), ED_DATA, 103) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA, CASE WHEN 1=2 $bloqueiro_query THEN 1 ELSE 0 END AS DISABLED FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC", $conexao_params, $conexao_options);
				$sql_eventos_dias = sqlsrv_query($conexao, "SELECT ED_COD, ED_NOME, CONVERT(CHAR(10), ED_DATA, 103) AS DATA, DATEPART(WEEKDAY, ED_DATA) AS SEMANA FROM eventos_dias WHERE ED_EVENTO='$evento' AND D_E_L_E_T_=0 ORDER BY ED_DATA ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_eventos_dias) !== false){
					while ($ar_eventos_dias = sqlsrv_fetch_array($sql_eventos_dias)) {
						
						$eventos_dias_cod = $ar_eventos_dias['ED_COD'];
						$eventos_dias_nome = utf8_encode($ar_eventos_dias['ED_NOME']);
						$eventos_dias_data = $ar_eventos_dias['DATA'];
						$eventos_dias_semana = $semana[($ar_eventos_dias['SEMANA']-1)];
						$eventos_dias_disabled = true;
						//Caso exista apenas um setor selecionado
						if((count($disponibilidade['setores']) == 1) && in_array($eventos_dias_cod, $disponibilidade['dias'][$disponibilidade['setores'][0]])) $eventos_dias_disabled = false;
						
						// if($ar_eventos_dias['DISABLED'] && $tipo_ingresso_folia) $eventos_dias_disabled = true;

						if($eventos_dias_data != '03/03/2017') {
							
					?>
					<li>
						<label class="item <? if ($eventos_dias_disabled){ echo 'disabled'; } ?>">
							<input type="radio" name="dia" value="<? echo $eventos_dias_cod; ?>" <? if ($eventos_dias_disabled){ echo 'class="disabled"'; } ?> />
							<span class="big"><? echo $eventos_dias_nome; ?></span class="big">
							<p><? echo $eventos_dias_semana; ?></p>
							<span><? echo $eventos_dias_data; ?></span>
						</label>
					</li>
					<?
						}
					}
				}
				?>
				</ul>
				<div class="clear"></div>


			</section>
			
			<section id="compras-especiais" class="hidden">
				<section id="compras-candybox" class="compras-especiais hidden">
					<span class="arrow"></span>

					<? if ($tipo_ingresso_folia) echo $lg['compre_ingressos_folia_candybox']; ?>
					<? if ($tipo_ingresso_superfolia) echo $lg['compre_ingressos_superfolia_candybox']; ?>
					<? /*echo $lg['compre_ingressos_especial_sexta'];*/ ?>
					
				</section>

				<section id="compras-especial-sabado" class="compras-especiais hidden">
					<span class="arrow"></span>
					<span class="selo"></span>
					<? if ($tipo_ingresso_folia) echo $lg['compre_ingressos_folia_sabado']; ?>
					<? if ($tipo_ingresso_superfolia) echo $lg['compre_ingressos_superfolia_sabado']; ?>
					<? echo $lg['compre_ingressos_especial_sabado']; ?>
				</section>

				<section id="compras-especial-domingo" class="compras-especiais hidden">
					<span class="arrow"></span>
					<span class="selo"></span>
					<? if ($tipo_ingresso_folia) echo $lg['compre_ingressos_folia_domingo']; ?>
					<? if ($tipo_ingresso_superfolia) echo $lg['compre_ingressos_superfolia_domingo']; ?>
					<? echo $lg['compre_ingressos_especial_domingo']; ?>
				</section>

				<section id="compras-especial-segunda" class="compras-especiais hidden">
					<span class="arrow"></span>
					<span class="selo"></span>
					<? if ($tipo_ingresso_folia) echo $lg['compre_ingressos_folia_segunda']; ?>
					<? if ($tipo_ingresso_superfolia) echo $lg['compre_ingressos_superfolia_segunda']; ?>
					<? echo $lg['compre_ingressos_especial_segunda']; ?>
				</section>

				<section id="compras-especial-sexta" class="compras-especiais hidden">
					<span class="arrow"></span>
					<span class="selo"></span>
					<? if ($tipo_ingresso_folia) echo $lg['compre_ingressos_especial_sexta']; ?>
				</section>

				<section id="compras-especial-campeas" class="compras-especiais hidden">
					<span class="arrow"></span>
					<span class="selo"></span>
					<? if ($tipo_ingresso_folia) echo $lg['compre_ingressos_folia_campeas']; ?>
					<? if ($tipo_ingresso_superfolia) echo $lg['compre_ingressos_superfolia_campeas']; ?>
					<? echo $lg['compre_ingressos_especial_campeas']; ?>
				</section>
			</section>
			
			<section id="compras-itens">
				<h2>
					4 • <? echo $lg['compre_selecione_ingresso']; ?> 
					<? if($tipo_ingresso == 'folia-tropical') { ?>
						<span class="candybox lote"><? echo $lg['compre_selecione_ingresso']; ?></span>
						<!-- <span class="folia lote"><? echo $lg['compre_selecione_ingresso']; ?></span> -->
					<? } ?>
				</h2>
				<section class="target">
				</section>
				<div class="clear"></div>
			</section>
			
			<footer class="controle">
				<input type="submit" class="submit coluna" value="<? echo $lg['compre_ingressos_adicionar']; ?>" />
				<span class="camiseta"><? echo $lg['compre_camiseta']; ?></span>
				<div class="clear"></div>
			</footer>

		</form>

		<?
	if(count($_SESSION['compra-site']) > 0) {
	?>	
	<form id="compras-carrinho" method="post" action="<? echo SITE.$link_lang; ?>ingressos/adicionais/">
		<header class="titulo">
			<h2><? echo $lg['compre_ingressos_carrinho']; ?></h2>
		</header>
		
		<section class="secao bottom">
			<table class="lista">
				<thead>
					<tr>
						<th class="setor"><strong><? echo $lg['compre_ingressos_setor']; ?></strong></th>
						<th><strong><? echo $lg['compre_ingressos_data_ref']; ?></strong></th>
						<th><strong><? echo $lg['compre_ingressos_tipo_ingresso']; ?></strong></th>
						<th><strong><? echo $lg['compre_ingressos_quantiadde']; ?></strong></th>
						<th class="right"><strong><? echo $lg['compre_ingressos_valor']; ?></strong></th>
						<th>&nbsp;</th>
					</tr>
					<tr class="spacer"><td colspan="6">&nbsp;</td></tr>
				</thead>
				<tbody>
				<?
				$idisponivel = 0;
				foreach ($_SESSION['compra-site'] as $key => $carrinho) {
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
						SELECT VE_COD, VE_TIPO, VE_ESTOQUE, VE_SETOR, VE_DIA, VE_FILA, VE_VAGAS, VE_TIPO_ESPECIFICO FROM vendas WHERE VE_COD=@ingresso AND VE_VALOR>0 AND VE_BLOCK=0 AND D_E_L_E_T_=0;
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
					$ingressos_tipo = ($ingressos['TI_NOME'] == 'Lounge') ? 'Folia Tropical' : utf8_encode($ingressos['TI_NOME']);
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
					$_SESSION['compra-site'][$key]['disabled'] = ($ingressos_block) ? true : false;
					if(!$ingressos_block) $idisponivel++;
					?>
					<tr <? if ($ingressos_block || $ingresso_indisponivel) { echo 'class="block"'; } ?>>
						<td class="setor"><? echo $ingressos_setor; ?></td>
						<td class="data"><? echo $ingressos_dia; ?> <? echo $lg['compre_ingressos_dia']; ?></td>
						<td class="tipo">
						<?
							echo $ingressos_tipo;
							if(!empty($ingressos_fila)) { echo " ".$ingressos_fila; }
							if(!empty($ingressos_tipo_especifico)) { echo " ".$ingressos_tipo_especifico; }
							if(!empty($ingressos_vaga) && ($ingressos_tipo_especifico == 'fechado')) { echo " (".$ingressos_vaga." vagas)"; }
						?>
						</td>
						<td class="qtde">
							<p>
								<input type="text" name="quantidade[<? echo $key; ?>]" class="input qtde <? if ($ingressos_block){ echo 'block'; } ?>" value="<? echo $carrinho['qtde']; ?>" rel="<? echo $key; ?>" <? if ($ingressos_block){ echo 'disabled="disabled"'; } ?> />
								<? if ($ingresso_indisponivel){ ?>
									<span class="aviso"><? echo $ingressos_estoque; ?> <? echo ($ingressos_estoque==1) ? $lg['compre_ingressos_disponivel'] : $lg['compre_ingressos_disponiveis']; ?></span>
								<? } ?>
							</p>
							<input type="hidden" name="estoque" value="<? echo $ingressos_estoque; ?>" />
							<input type="hidden" name="valor" value="<? echo $ingressos_valor; ?>" />
						</td>
						<td class="valor">R$ <? echo $ingressos_valor_total; ?></td>
						<td class="ctrl small">
							<a href="<? echo SITE.$link_lang; ?>ingressos/adicionar/?c=<? echo $key; ?>&a=excluir" class="excluir confirm" title="Tem certeza que deseja excluir o ingresso?"></a>
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
			<? if ($idisponivel > 0){ ?><input type="submit" class="submit coluna" value="<? echo $lg['compre_ingressos_comprar']; ?>" /><? } ?>
			<a href="<? echo SITE.$link_lang; ?>ingressos/cancelar/" class="cancel no-cancel coluna"><? echo $lg['compre_ingressos_cancelar']; ?></a>

			<section class="termo">
				<section class="checkbox">
					<label class="item"><input type="checkbox" name="termo" value="true" /></label>
					<span><? echo $lg['compre_ingressos_concordo_mensagem']; ?></span>
				</section>
				<? echo $lg['compre_ingressos_concordo']; ?> <a href="<? echo SITE; ?>pdf/<? echo $lg['compre_ingressos_termos']; ?>" target="_blank"><? echo $lg['compre_ingressos_concordo_termos']; ?></a>
			</section>

			<div class="clear"></div>
		</footer>



	</form>
	<?
	}
	?>

	</section>

	<div class="clear"></div>
</section>
<?
// Blog
/*$sql_blog = mysql_query("SELECT a.*, DATE_FORMAT(a.AR_DATA, '%d') AS DIA, DATE_FORMAT(a.AR_DATA, '%m') AS MES, DATE_FORMAT(a.AR_DATA, '%Y') AS ANO, c.* FROM artigos a, blog_categorias c WHERE a.AR_TIPO='blog' AND a.AR_CAT=c.CA_COD AND c.CA_BLOCK<>'*' AND c.D_E_L_E_T_<>'*' AND a.AR_BLOCK<>'*' AND a.D_E_L_E_T_<>'*' ORDER BY a.AR_DATA DESC LIMIT 4");
$n_blog = mysql_num_rows($sql_blog);
if($n_blog > 0) {
?>
<section id="blog" class="interno parallaxouter">
	<section class="outer">
		<section class="wrapper">

			<section class="inside">
			
				<header>
					<h2><? echo $lg['blog_titulo']; ?></h2>
				</header>

				<ul>
					<?
					$iblog = 1;
					while ($blog = mysql_fetch_array($sql_blog)) {
						$blog_cod = $blog['AR_COD'];
				        $blog_titulo = utf8_encode($blog['AR_TITULO_BR']);
				        $blog_titulo_url = toAscii($blog_titulo);
				        $blog_thumb = utf8_encode($blog['AR_THUMB']);
				        $blog_categoria = utf8_encode($blog['CA_ABREV']);
				        $blog_first = ($iblog == 1);
					    ?>
					    <li>
							<a href="<? echo SITE.$link_lang; ?>noticias-carnaval/<? echo $blog_categoria; ?>/<? echo $blog_titulo_url; ?>/<? echo $blog_cod; ?>/">
								<div class="thumb">
									<img src="<? echo SITE; ?>img/posts/thumb/<? echo $blog_thumb; ?>" alt="<? echo $blog_titulo; ?>" />
									<span></span>
								</div>
								
								<h3><? echo $blog_titulo; ?></h3>
								<span class="ler"><? echo $lg['blog_ler']; ?></span>
							</a>
						</li>
					    <?
						
				        $iblog++;
					}
					?>
				</ul>
			</section>
			
			<section class="parallax">
				<div class="splash"></div>
			</section>

		</section>
	</section>
</section>

<?
}*/
?>
<script>
	$(document).ready(function(){
		$("section#conteudo section#compre-aqui section#compra-dias input[name='dia']").radioSel('<?php echo $d ?>');
	});
</script>
<?
//-----------------------------------------------------------------//
include('include/footer.php');
//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
?>