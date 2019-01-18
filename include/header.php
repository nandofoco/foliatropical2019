<? 

$tipo_candy = $_GET['tipo'];

?>

<header id="topo-novo" <? if (defined('PGINDEX')){ echo 'class="index parallaxouter"'; } ?>>

	<nav id="principal">
		<div>
			<!-- <a href="<? echo SITE.$link_lang; ?>" id="logo"><span>Folia Tropical</span></a> -->
			<a href="https://ingressos.foliatropical.com.br/compras/<?= $link_lang; ?>" id="logo"><span>Folia Tropical</span></a>
			<ul class="menu">
				<? if($tipo_candy == 'candybox') { ?>
					
				<? } else { ?>
					<li><a href="http://www.grupopacifica.com.br/">Home</a></li>
					<li><a href="http://www.grupopacifica.com.br/frisa/">Frisa</a></li>
					<li><a href="http://www.grupopacifica.com.br/camarote/">Camarote</a></li>
					<li><a href="https://www.grupopacifica.com.br/contato/"><?=$lg['contato'];?></a></li>
					<li class="drop"><a href="<? echo SITE.$link_lang; ?>" class="comprar"><?=$lg['compre_ingressos_comprar'];?></a></li>
					<li><a href="<? echo SITE.$link_lang; ?>minhas-compras/" class="info"><? echo $lg['menu_minhas_compras']; ?></a></li>
				<li><a href="<? echo SITE.$link_lang; ?>meus-dados/" class="info"><? echo $lg['menu_meus_dados']; ?></a></li>
				<? } ?>
				
				
				
				<!-- <li><a href="<? echo SITE; ?>compras/" class="comprar"><?=$lg['compre_ingressos_comprar'];?></a></li> -->
				<!-- <li class="drop"><a href="#" class="comprar"><?=$lg['compre_ingressos_comprar'];?></a>
					<ul class="submenu">
						<li><a href="https://ingressos.foliatropical.com.br/compras/<? echo $link_lang; ?>"><?=$lg['super_folia'];?></a></li>
						<li><a href="<? echo SITE.$link_lang; ?>"><?=$lg['camarote_frisa'];?></a></li>
					</ul>
				</li> -->

				<?
				if(checklogado()) {
					$usuario_nome = explode(" ", $_SESSION['usuario-nome']);
					$usuario_nome = $usuario_nome[0];
				?>
				
				<li class="carrinho"><a href="<? echo SITE.$link_lang.$lg['link_compre']; ?>" class="carrinho"><? echo count($_SESSION['compra-site']); ?></a></li>
				<li><a href="<? echo SITE.$link_lang; ?>logout/" class="info"><? echo $lg['menu_sair']; ?></a></li>
				<li class="nome"><?=$lg['menu_ola'];?>, <? echo utf8_encode($usuario_nome); ?></li>
				<?
				} else {
				?>
				<li class="login"><a href="<? echo SITE.$link_lang; ?>login/" ><? echo $lg['menu_login_ou_cadastro']; ?></a></li>
				<? 
				}
				?>
			</ul>
		</div>
	</nav>
</header>