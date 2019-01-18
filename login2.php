<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

$meta_title = $lg['meta_title_cadastro'];
$meta_description = $lg['meta_description_cadastro'];

//Canonical
$meta_canonical = SITE.$link_lang."login-cadastro-site/";

//arquivos de layout
include("include/head.php");
include("include/header.php");

//busca paises
$sql_paises= sqlsrv_query($conexao_sankhya, "SELECT * FROM pais", $conexao_params, $conexao_options);
$paises=array();
while($linha = sqlsrv_fetch_array($sql_paises)){
	array_push($paises, $linha);
}

//-----------------------------------------------------------------------------//

$imprimir = (int) $_GET['imprimir'];

?>
<section id="conteudo">

	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; <? echo $lg['login_titulo']; ?>
	</div>

	<section id="principal">
		<section id="cadastro">
			<header>
				<h2><? echo $lg['login_titulo']; ?></h2>
			</header>

			<form name="login" class="infield login" method="post" action="<? echo SITE.$link_lang; ?>login/sucesso/2/">
				<input type="hidden" id="SessionID" name="SessionID">
				<? if ($_GET['compre']){ ?><input type="hidden" name="compre" value="true" /><? } ?>
				<? if ($imprimir > 0){ ?><input type="hidden" name="imprimir" value="<? echo $imprimir; ?>" /><? } ?>

				<p id="login-email-box">
					<label for="login-email"><? echo $lg['login_email']; ?>:</label>
					<input type="text" name="email" class="input" id="login-email" />
				</p>
				
				<p>
					<label for="login-senha"><? echo $lg['login_senha']; ?>:</label>
					<input type="password" name="senha" class="input" id="login-senha" />
				</p>
				<? echo $lg['login_aviso']; ?>
				<p class="submit">
					<a href="<? echo SITE.$link_lang; ?>esqueci-senha/" class="esqueci"><? echo $lg['login_esqueci']; ?></a>
					<input type="submit" class="submit" value="<? echo $lg['cadastro_enviar']; ?>" />
					<a class="cadastro" href="<? echo SITE.$link_lang; ?>cadastro/"><? echo $lg['cadastro_criar_conta']; ?></a>
				</p>
				
			</form>
		
		</section>
	</section>
	
	<aside>

		<section class="whatsapp">
			<h2><? echo $lg['atendimento_whatsapp']; ?></h2>
			<p><? echo $lg['atendimento_whatsapp_texto']; ?></p>
		</section>
		
		<section id="aside-horario">
			<section class="horario-atendimento">
				<h2><? echo $lg['atendimento_horario_atendimento']; ?></h2>
				<p><? echo $lg['atendimento_horario_atendimento_texto']; ?></p>
			</section>

		</section>

		<section id="aside-televendas">
			<h2><? echo $lg['atendimento_televendas']; ?></h2>
			<p><? echo $lg['atendimento_televendas_texto']; ?></p>
			<strong><? echo $lg['atendimento_televendas_tel']; ?></strong>
		</section>

		<section id="facebook" class="atendimento">				
			<div class="wrap"><div class="fb-like-box" data-href="https://www.facebook.com/CamaroteFoliaTropical" data-width="302" data-height="552" data-border-color="#ffffff" data-show-faces="true" data-stream="false" data-header="false"></div></div>
		</section>

	</aside>

	<? /*<aside>

		<section id="aside-atendimento">
			<!-- <section class="chat-online">
				<h2><? echo $lg['atendimento_chat_online']; ?></h2>
				<a href="javascript:void(window.open('<? echo SITE; ?>chat/chat.php','','width=590,height=610,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))" class="chat"><? echo $lg['atendimento_acesse_chat']; ?></a>
			</section> -->

			<section class="horario-atendimento">
				<h2><? echo $lg['atendimento_horario_atendimento']; ?></h2>
				<p><? echo $lg['atendimento_horario_atendimento_texto']; ?></p>
			</section>

		</section>

		<section id="aside-televendas">
			<h2><? echo $lg['atendimento_televendas']; ?></h2>
			<p><? echo $lg['atendimento_televendas_texto']; ?></p>
			<strong><? echo $lg['atendimento_televendas_tel']; ?></strong>
		</section>

		<section id="facebook" class="atendimento">				
			<div class="wrap"><div class="fb-like-box" data-href="https://www.facebook.com/CamaroteFoliaTropical" data-width="302" data-height="552" data-border-color="#ffffff" data-show-faces="true" data-stream="false" data-header="false"></div></div>
		</section>

	</aside>*/ ?>

	<div class="clear"></div>
</section>
<?

include('include/escolha-lugar.php');


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
					<h3><? echo $lg['blog_titulo']; ?></h3>
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
								
								<h4><? echo $blog_titulo; ?></h4>
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
		$('section#conteudo section#cadastro form select[name="pais"]').select2().trigger('change');
	});
</script>
<script>
	 (function(a, b, c, d, e, f, g) {
	 a['CsdpObject'] = e; a[e] = a[e] || function() {
	 (a[e].q = a[e].q || []).push(arguments)
	 }, a[e].l = 1 * new Date(); f = b.createElement(c),
	g = b.getElementsByTagName(c)[0]; f.async = 1; f.src = d;
	g.parentNode.insertBefore(f, g)
	 })(window, document, 'script',
	'//device.clearsale.com.br/p/fp.js', 'csdp');
	 csdp('app', '<?php echo CLEARSALE_APP; ?>');
	 csdp('outputsessionid', 'SessionID');
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//fechar conexao com o banco
include("conn/close.php");

?>