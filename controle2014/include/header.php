<?

$usuario = $_SESSION['us-cod'];

$sql_permissoes = sqlsrv_query($conexao, "SELECT TOP 1 MP_MENU, MP_SUBMENU FROM menu_permissoes WHERE MP_USUARIO='$usuario' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
$n_permissoes = sqlsrv_num_rows($sql_permissoes);

if($n_permissoes > 0) {
	$ar_permissoes = sqlsrv_fetch_array($sql_permissoes);
	$menu_permissoes = $ar_permissoes['MP_MENU'];
	$submenu_permissoes = $ar_permissoes['MP_SUBMENU'];

	$menu_permissoes = (!empty($menu_permissoes)) ? 'ME_COD IN('.$menu_permissoes.') AND' : "";
	$submenu_permissoes = (!empty($submenu_permissoes)) ? 'AND SM_COD IN('.$submenu_permissoes.')' : "";

}
?>
<header id="topo">
	<section id="logado">
		<div class="wrapper">
			<section id="busca-voucher">
				<a href="#" class="show"></a>
				<form name="busca-voucher" method="get" action="<? echo SITE; ?>financeiro/busca/">
					<label for="input-busca-voucher" class="infield">Buscar voucher</label>
					<input type="text" name="q" class="input" id="input-busca-voucher" />
					<input type="submit" class="submit" value="" />
				</form>
			</section>

			<? echo utf8_encode($_SESSION['us-nome']); ?>
			<a href="<? echo SITE; ?>logout/" class="logout" title="Sair"></a>

			<section id="header-muda-carnaval">
				<?

				//busca nome do evento
				$munda_carnaval_id = (int) $_SESSION['usuario-carnaval'];
				$sql_muda_carnaval_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_NOME FROM eventos WHERE EV_COD='$munda_carnaval_id'", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_muda_carnaval_evento) > 0) {
					$ar_muda_carnaval_evento = sqlsrv_fetch_array($sql_muda_carnaval_evento);
					$muda_carnaval_evento_nome = $ar_muda_carnaval_evento['EV_NOME'];
				}


				?>
				<a href="#" class="arrow"><? echo $muda_carnaval_evento_nome; ?></a>

				<ul class="drop">
				<?
					// Lista de carnavais
					$sql_muda_carnaval = sqlsrv_query($conexao, "SELECT EV_COD, EV_NOME FROM eventos WHERE EV_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY EV_ANO DESC", $conexao_params, $conexao_options);
					if(sqlsrv_num_rows($sql_muda_carnaval) > 0) {
						while($ar_muda_carnaval = sqlsrv_fetch_array($sql_muda_carnaval)){
							$muda_carnaval_cod = $ar_muda_carnaval['EV_COD'];							
							$muda_carnaval_nome = $ar_muda_carnaval['EV_NOME'];

						?>
						<li><a href="<? echo SITE; ?>e-carnaval-gerenciar.php?c=<? echo $muda_carnaval_cod; ?>&a=ativar&r=true"><? echo $muda_carnaval_nome; ?></a></li>
						<?						
						}
					}

				?>
				</ul>
			</section>
		</div>
	</section>

	<nav>
		<a href="<? echo SITE; ?>" id="logo"></a>
		<ul>
		<?
		$sql_menu = sqlsrv_query($conexao, "SELECT * FROM menu WHERE $menu_permissoes D_E_L_E_T_='0' ORDER BY ME_ORDEM ASC", $conexao_params, $conexao_options);
		$n_menu = sqlsrv_num_rows($sql_menu);
		if($n_menu > 0) {
			$im = 0;
			while ($menu = sqlsrv_fetch_array($sql_menu)) {
				$menu_cod = $menu['ME_COD'];
				$menu_titulo = utf8_encode($menu['ME_MENU']);
				$menu_link = $menu['ME_LINK'];
				$menu_drop = (bool) $menu['ME_DROP'];
				$menu_left = (bool) $menu['ME_LEFT'];
				$menu_blank = (bool) $menu['ME_BLANK'];
				$menu_link = ($menu_blank) ? $menu_link.'" target="_blank"' : SITE.$menu_link;
				if($menu_drop) {
					$order = ($menu_left) ? "ASC" : "DESC";
					$sql_total_submenu = sqlsrv_query($conexao, "SELECT * FROM submenu WHERE SM_MENU='$menu_cod' AND D_E_L_E_T_='0' ORDER BY SM_ORDEM $order", $conexao_params, $conexao_options);
					$n_total_submenu = sqlsrv_num_rows($sql_total_submenu);

					$sql_submenu = sqlsrv_query($conexao, "SELECT * FROM submenu WHERE SM_MENU='$menu_cod' $submenu_permissoes AND D_E_L_E_T_='0' ORDER BY SM_ORDEM $order", $conexao_params, $conexao_options);
					$n_submenu = sqlsrv_num_rows($sql_submenu);
					if($n_submenu > 0) $class_drop = "drop"; if(!$menu_left) $class_drop.= " right";
				}
				?>
				<li <? if($menu_drop) echo 'class="'.$class_drop.'"' ?>>
					<a href="<? echo $menu_link; ?>"><? echo $menu_titulo; ?></a>
					<?
					if($menu_drop && ($n_submenu > 0)) {
						if($menu_left) {
							$style = "style = left:".(($n_total_submenu*($im*25))-($n_submenu*($im*25)))."px;";
						} else {
							$right = (($n_total_submenu*($im*17))-($n_submenu*($im*17)));
							if($right <= 150) $right = 0;
							$style = "style = right:".$right."px;";
						}
					?> 
					<ul <? echo $style; ?>> <?
						$i = 1;
						while ($submenu = sqlsrv_fetch_array($sql_submenu)) {
							$submenu_titulo = utf8_encode($submenu['SM_SUBMENU']);
							$submenu_link = $submenu['SM_LINK'];
							$submenu_blank = (bool) $submenu['SM_BLANK'];
							$submenu_link = ($submenu_blank) ? $submenu_link.'" target="_blank"' : SITE.$submenu_link;
						?>
							<li><a href="<? echo $submenu_link; ?>" <? if(($menu_left && ($i == 1)) || (!$menu_left && ($i == $n_submenu))) echo 'class="first-child"'; ?>><? echo $submenu_titulo; ?></a></li>
						<?
							$i++;
						} //end while submenu ?> 
					</ul> <?
					} // end if submenu
					?>
				</li>
				<? 
					unset($style);
					$im++;
			} //end whjle menu
		} //end if menu
		?>
		</ul>
	</nav>
</header>