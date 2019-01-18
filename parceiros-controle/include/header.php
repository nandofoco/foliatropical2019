<?

$usuario = $_SESSION['us-cod'];

?>

<header id="topo">
	<section id="logado">
		<div class="wrapper">
			<!-- <section id="busca-voucher">
				<a href="#" class="show"></a>
				<form name="busca-voucher" method="get" action="<? echo SITE; ?>financeiro/busca/">
					<label for="input-busca-voucher" class="infield">Buscar voucher</label>
					<input type="text" name="q" class="input" id="input-busca-voucher" />
					<input type="submit" class="submit" value="" />
				</form>
			</section> -->

			<? echo utf8_encode($_SESSION['us-par-nome']); ?>
			<a href="<? echo SITE; ?>logout/" class="logout" title="Sair"></a>
		</div>
	</section>

	<nav>
		<a href="<? echo SITE; ?>" id="logo"></a>
		<ul>		
			<!-- <li>
				<a href="<?=SITE;?>vendas/">Vendas</a>				
				<ul > 
				</ul> 
			</li> -->				
		</ul>
	</nav>
</header>