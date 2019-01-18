<header id="topo" <? if (defined('PGINDEX')){ echo 'class="index parallaxouter"'; } ?>>

	<nav id="principal">
		<ul class="menu">
			<li class="logo"><a href="<? echo SITE.$link_lang; ?>" id="logo"><span>Folia Tropical</span></a></li>
			<!-- <li><a href="#"><? echo $lg['menu_folia']; ?></a></li> -->
			<li class="drop">
				<a href="#" class="drop"><? echo $lg['menu_produtos']; ?></a>
				<ul>
					<li><a href="<? echo SITE.$link_lang; ?>folia-tropical-carnaval-2016/"><? echo $lg['escolha_folia_tropical']; ?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>camarotes-carnaval-2016/"><? echo $lg['escolha_camarote']; ?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>camarote-corporativo-carnaval-2016/"><? echo $lg['escolha_camarote_corporativo']; ?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>arquibancadas-carnaval-2016/"><? echo $lg['escolha_arquibancadas']; ?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>frisas-carnaval-2016/"><? echo $lg['escolha_frisas']; ?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>marketing-incentivo/"><? echo $lg['menu_marketing_incentivo']; ?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>amigos-do-ton/"><? echo $lg['escolha_amigos_do_ton']; ?></a></li>
					<!-- <li><a href="<? echo SITE.$link_lang; ?>cadeiras-numeradas-carnaval-2016/"><? echo $lg['escolha_cadeiras']; ?></a></li> -->
				</ul>
			</li>
			<li><a href="<? echo SITE.$link_lang; ?>noticias-carnaval-rj/"><? echo $lg['menu_blog']; ?></a></li>
			<li><a href="<? echo SITE.$link_lang; ?>guia-do-carnaval-2016/"><? echo $lg['menu_guia_carnaval']; ?></a></li>
			<li><a href="<? echo SITE.$link_lang; ?>atendimento/"><? echo $lg['menu_atendimento']; ?></a></li>
			<li><a href="<? echo SITE.$link_lang; ?>comprar-ingressos-carnaval-2016-rj/"><? echo $lg['menu_loja']; ?></a></li>
			<?
			if(checklogado()) {

				$usuario_nome = explode(" ", $_SESSION['usuario-nome']);
				$usuario_nome = $usuario_nome[0];
			?>
			<li class="usuario drop <? echo strtolower($session_language); ?>">
				<span class="drop"><? echo $lg['menu_ola']; ?>, <? echo utf8_encode($usuario_nome); ?></span>
				<ul>
					<li><a href="<? echo SITE.$link_lang; ?>minhas-compras/"><? echo $lg['menu_minhas_compras']; ?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>meus-dados/"><? echo $lg['menu_meus_dados']; ?></a></li>
				</ul>
			</li>
			<li class="logout"><a href="<? echo SITE.$link_lang; ?>logout/" class="logout"><? echo $lg['menu_sair']; ?></a></li>
			<li class="botao first"><a href="<? echo SITE.$link_lang; ?>comprar-ingressos-carnaval-2016-rj/" class="carrinho"><? echo count($_SESSION['compra-site']); ?></a></li>
			<?
			} else {
			?>
			<li class="login"><a href="<? echo SITE.$link_lang; ?>cadastro/" ><? echo $lg['menu_ola']; ?>. <? echo $lg['menu_login_ou_cadastro']; ?></a></li>
			<? 
			}
			?>
			<li class="botao">
				<section id="lang-select" class="select">
					<a href="#" class="lang <? echo strtolower($session_language); ?> arrow"><span></span><? echo $session_language; ?></a>
					<section class="drop">
						<?

						$server_name = $_SERVER['SERVER_NAME'];
						$server_uri = $_SERVER ['REQUEST_URI'];
						$server_site = str_replace('http://', '', SITE);
						$server_site = str_replace('www.', '', $server_site);
						$server_site = str_replace($server_name, '', $server_site);
						
						$server_uri = str_replace($server_site, '', $server_uri);
						$server_uri = str_replace('br/', '', $server_uri);
						$server_uri = str_replace('us/', '', $server_uri);
						
						$total_lang = count($lang) - 1;
						$ilang = 1;

						foreach ($lang as $lang_key => $v) {
							if($lang_key != $session_language) {

						?>							
						<a href="<? echo SITE.strtolower($lang_key); ?>/<? echo $server_uri; ?>" class="lang <? if ($ilang = $total_lang){ echo 'last'; } ?> <? echo strtolower($lang_key); ?>"><span></span><? echo $lang_key; ?></a>
						<?
								$ilang++;
							}
						}
						?>
					</section>
				</section>
			</li>

			<li class="social">
				<a href="http://www.facebook.com/CamaroteFoliaTropical" target="_blank" class="facebook"><span></span></a>
				<a href="http://twitter.com/FoliaTropical" target="_blank" class="twitter"><span></span></a>
				<a href="http://www.youtube.com/user/FoliaTropical" target="_blank" class="youtube"><span></span></a>
				<a href="http://www.instagram.com/foliatropical" target="_blank" class="instagram"><span></span></a>
				<a href="javascript:void(window.open('<? echo SITE; ?>chat/chat.php','','width=590,height=610,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))" class="chat"><span></span></a>
			</li>
		</ul>
	</nav>

	<? if (defined('PGINDEX')){ ?>
	<section class="outer">
		<section class="wrapper">
			<a href="<? echo SITE; ?>" class="logo"><span>Folia Tropical</span></a>
			<p class="<? echo strtolower($session_language); ?>"><? echo $lg['header_maior_carnaval']; ?> <strong><? echo $lg['header_espera_por_voce']; ?></strong></p>

			<section class="parallax">
				<div class="splash-l"></div>
				<div class="splash-r"></div>
			</section>

			<section id="contador"></section>
			
			<span class="bottom-arrow"></span>
		</section>
	</section>
	<? } ?>

</header>