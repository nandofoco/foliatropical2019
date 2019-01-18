	<footer id="rodape">

		<section id="sobre">
			<section class="wrapper">
				<section class="coluna sobre">
					<h3><? echo $lg['footer_grupo_pacifica']; ?></h3>
					<p><? echo $lg['footer_sobre']; ?></p>
				</section>

				<section class="coluna">
					
					<section class="unidade first">
						<h4><? echo $lg['footer_sede_centro']; ?></h4>
						<p><? echo $lg['footer_sede_centro_endereco']; ?></p>
					</section>

					
				</section>

				<section class="coluna">
					
					<!-- <section class="unidade">
						<h4><? echo $lg['footer_filial_ipanema']; ?></h4>
						<p><? echo $lg['footer_filial_ipanema_endereco']; ?></p>
					</section> -->
					
					<!-- <section class="unidade first">
						<h4><? echo $lg['footer_filial_copacabana']; ?></h4>
						<p><? echo $lg['footer_filial_copacabana_endereco']; ?></p>
					</section> -->

					<!-- <section class="unidade">
						<h4><? echo $lg['footer_filial_leblon']; ?></h4>
						<p><? echo $lg['footer_filial_leblon_endereco']; ?></p>
					</section> -->

				</section>
			</section>
		</section>

		<section id="pagamento-seguro">
			<section class="wrapper">
				<div class="formas-pagamento">
					<h4><? echo $lg['footer_formas_pagamento']; ?></h4>
					<ul class="cartoes">
						<li class="visa"></li>
						<li class="mastercard"></li>
						<li class="diners"></li>
						<li class="discover"></li>
						<li class="elo"></li>
						<li class="paypal"></li>
						<!-- <li class="amex"></li> -->
					</ul>
				</div>

				<div class="cielo">
					<h4><? echo $lg['footer_site_seguro']; ?></h4>
				</div>
			</section>
		</section>

		<section id="patrocinadores">
			<section class="wrapper">
				<div class="abav">ABAV: 417</div>
				<div class="iata">IATA: 57-628196</div>
				<div class="embratur">Embratur: 045900417</div>
			</section>
		</section>

		<section id="foco">
			<a href="http://www.fococomunicacao.com" target="_blank">Desenvolvido por <span>Foco</span></a>
		</section>

	</footer>
	<input type="hidden" id="base-site" value="<? echo SITE; ?>" />
</section>

<!--<section id="pop-up-atendimento">
	<a href="#" class="fechar">&times;</a>
	<section class="chat">
		<h2><? echo $lg['atendimento_chat_online']; ?></h2>
		<a href="javascript:void(window.open('<? echo SITE; ?>chat/chat.php','','width=590,height=610,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))" class="chat"><? echo $lg['atendimento_acesse_chat']; ?></a>
	</section>
	<section class="atendimento">
		<h2><? echo $lg['atendimento_televendas']; ?></h2>
		<p><? echo $lg['atendimento_televendas_tel']; ?></p>
	</section>
	<!-- <section class="revista">
		<a href="http://www.foliatropical.com.br/revista/Main.php?MagID=10&MagNo=67" target="_blank">
			<img src="<? echo SITE; ?>img/bg-capa-revista-mini-2015.png" alt="<? echo $lg['revista_titulo']; ?>" />
			<h2><? echo $lg['revista_titulo']; ?></h2>
		</a>
	</section> -->
<!--</section>-->
<?php 
switch ($_SESSION['ALERT'][0]) {
case 'sucesso':
    echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "success")'.'</script>';
    break;
case 'erro':
    echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "error")'.'</script>';
    break;
case 'aviso':
    echo '<script>'.'swal("", "'.$_SESSION['ALERT'][1].'", "warning")'.'</script>';
    break;
}
//limpando todos os aviso
unset($_SESSION['ALERT']);
?>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700"/>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700"/>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Londrina+Solid"/>
<script>
	(function (a, b, c, d, e, f, g) {
	 a['CsdmObject'] = e; a[e] = a[e] || function () {
	 (a[e].q = a[e].q || []).push(arguments)
	 }, a[e].l = 1 * new Date(); f = b.createElement(c),
	 g = b.getElementsByTagName(c)[0]; f.async = 1; f.src = d; g.parentNode.insertBefore(f, g)
	})(window, document, 'script', '//device.clearsale.com.br/m/cs.js', 'csdm');
	csdm('app', '<?php echo CLEARSALE_APP; ?>');
	csdm('mode', 'manual');

	if($('#page').length && $('#page_key').length){
		csdm('send', $('#page').val(),$('#page_key').val());
	}else if($('#page').length){
		csdm('send', $('#page').val());
	}
</script>
<? 
// Adicionar tags de rastreamento
include("include/analytics.php");
?>
</body>
</html>