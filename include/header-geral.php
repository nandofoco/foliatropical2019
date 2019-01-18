<header id="topo-novo">

	<nav id="principal" class="geral">
		<div>
			<!-- <a href="<? echo SITE.$link_lang; ?>" id="logo"><span>Folia Tropical</span></a> -->
			<!-- <a href="https://ingressos.foliatropical.com.br/compras/<?= $link_lang; ?>" id="logo"><span>Folia Tropical</span></a> -->
			<ul class="menu">
                <li><a href="http://www.foliatropical.com.br/">Folia Tropical</a></li>
                <li><a href="http://www.candybox.com.br/">Candybox</a></li>
                <li><a href="http://www.grupopacifica.com.br/">Grupo Pacífica</a></li>
                <li><a href="<? echo SITE.$link_lang; ?>minhas-compras/" class="info"><? echo $lg['menu_minhas_compras']; ?></a></li>
                <li><a href="<? echo SITE.$link_lang; ?>meus-dados/" class="info"><? echo $lg['menu_meus_dados']; ?></a></li>

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