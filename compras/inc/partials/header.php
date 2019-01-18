<div class="language">
	<ul>
		<?
		$server_name = $_SERVER['SERVER_NAME'];
		$server_uri = $_SERVER ['REQUEST_URI'];
		$server_site = str_replace('http://', '', SITE);
		$server_site = str_replace('https://', '', $server_site);
		$server_site = str_replace('www.', '', $server_site);
		$server_site = str_replace($server_name, '', $server_site);

		$server_uri = str_replace($server_site, '', $server_uri);
		$server_uri = str_replace('br/', '', $server_uri);
		$server_uri = str_replace('us/', '', $server_uri);

		$total_lang = count($lang) - 1;
		$ilang = 1;

		foreach ($lang as $lang_key => $v) {
			
		?>		
		<li><a href="<? echo SITE.strtolower($lang_key); ?>/<? echo $server_uri; ?>" class="<? echo strtolower($lang_key); ?>"><img src="<? echo SITE ?>img/flag-<? echo strtolower($lang_key); ?>.png"></a></li>					
		<?
				$ilang++;
		}
		?>
	</ul>
</div>


<header id="topo">

	<nav <? if($tipo == 'candybox') { echo "class='candy'"; }?>>
		<div>
			<a href="<? echo SITE.$link_lang; ?>" id="logo"><span>Folia Tropical</span></a>
			<ul class="menu">
				<!-- <li><a href="<? echo RAIZ; ?>">Home</a></li>
				<li><a href="<? echo RAIZ; ?>#o-camarote">Camarote</a></li>
				<li><a href="<? echo RAIZ; ?>galeria-2/">Galeria</a></li>
				<li><a href="<? echo RAIZ; ?>faq/">Dúvidas</a></li>
				<li><a href="<? echo RAIZ; ?>contato/">Contato</a></li>
				<li><a href="<? echo SITE; ?>" class="comprar">Comprar</a></li> -->
				<? if($tipo == 'candybox') { ?>
					<li><a href="http://www.candybox.com.br/">Home</a></li>
				<? } else { ?>
					<li><a href="http://www.foliatropical.com.br/">Home</a></li>
				<? } ?>
				<? if($tipo != 'candybox') { ?>
				<li class="drop">
					<a href="#"><?=$lg['camarote'];?></a>
					<ul class="submenu">
						<li><a href="http://www.foliatropical.com.br/folia/"><?=$lg['menu_folia_titulo'];?></a></li>
						<li><a href="http://www.foliatropical.com.br/super/"><?=$lg['sp_vip'];?></a></li>
					</ul>
				</li>
				<? } ?>
				<? if($tipo == 'candybox') { ?>
					<li><a href="https://www.candybox.com.br/edicoes-anteriores/"><?=$lg['galeria'];?></a></li>
					<li><a href="https://www.candybox.com.br/faq/"><?=$lg['duvidas'];?></a></li>
					<li><a href="https://www.facebook.com/CandyBoxOficial/" target="_blank"><?=$lg['contato'];?></a></li>
					<li><a href="<? echo SITE.$link_lang."candybox/"; ?>" class="comprar"><?=$lg['compre_ingressos_comprar'];?></a></li>
				<? } else { ?>
					<li><a href="http://www.foliatropical.com.br/galeria/"><?=$lg['galeria'];?></a></li>
					<li><a href="http://www.foliatropical.com.br/duvidas/"><?=$lg['duvidas'];?></a></li>
					<li><a href="http://www.foliatropical.com.br/contato/"><?=$lg['contato'];?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>" class="comprar"><?=$lg['compre_ingressos_comprar'];?></a></li>
				<? } ?>

				<?
				if(checklogado()) {
					$usuario_nome = explode(" ", $_SESSION['usuario-nome']);
					$usuario_nome = $usuario_nome[0];
				?>
				<li><a href="<? echo RAIZ.$link_lang; ?>minhas-compras/" class="info"><?=$lg['menu_minhas_compras'];?></a></li>
				<li><a href="<? echo RAIZ.$link_lang; ?>meus-dados/" class="info"><?=$lg['menu_meus_dados'];?></a></li>
				<li class="carrinho">

					

					<? if (!empty($_SESSION['compra-site'])): ?>

						<a href="<? echo SITE.$link_lang; ?>ingressos/" class="carrinho"><? echo count($_SESSION['compra-site']); ?></a>
					<? endif; ?>
				</li>
				<li><a href="<? echo SITE.$link_lang; ?>logout/" class="info"><?=$lg['menu_sair'];?></a></li>
				<li class="nome"><?=$lg['menu_ola'];?>, <? echo utf8_encode($usuario_nome); ?></li>
				<?
				} else {
					if($tipo == 'candybox') {
				?>
						<li class="login"><a href="<? echo SITE.$link_lang."candybox/"; ?>#login" ><?=$lg['menu_login_ou_cadastro'];?></a></li>
				<? 
					} else {
					?>
						<li class="login"><a href="<? echo SITE.$link_lang; ?>#login" ><?=$lg['menu_login_ou_cadastro'];?></a></li>
					<?
					}
				}
				?>
			</ul>
		</div>
	</nav>
</header>