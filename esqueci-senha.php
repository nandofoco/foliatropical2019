<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//Canonical
$meta_canonical = SITE.$link_lang."login-cadastro-site/";

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------------------//

?>
<input type="hidden" id="page" value="password-reset">
<section id="conteudo">

	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; 
		<a href="<? echo SITE.$link_lang; ?>login-cadastro-site/"><? echo $lg['cadastro_login_titulo']; ?></a> &rsaquo; 
		<? echo $lg['login_esqueci']; ?>
	</div>

	<section id="principal">
		<section id="cadastro">
			<header>
				<h2><? echo $lg['login_esqueci']; ?></h2>
				<p><? echo $lg['login_esqueci_texto']; ?></p>
			</header>

			<form name="login" class="infield" method="post" action="<? echo SITE.$link_lang; ?>esqueci-senha/sucesso/">

				<p id="login-email-box">
					<label for="login-email"><? echo $lg['login_email']; ?>:</label>
					<input type="text" name="email" class="input" id="login-email" />
				</p>
				
				<p class="submit">
					<input type="submit" class="submit" value="<? echo $lg['cadastro_enviar']; ?>" />
				</p>
			</form>
		</section>
	</section>
	
	<aside>

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

	</aside>

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
					    <li <? if ($blog_first){ echo 'class="first"'; } ?>>
							<a href="<? echo SITE.$link_lang; ?>noticias-carnaval/<? echo $blog_categoria; ?>/<? echo $blog_titulo_url; ?>/<? echo $blog_cod; ?>/">
								<? if ($blog_first) { ?>
								<div class="thumb">
									<img src="<? echo SITE; ?>img/posts/thumb/<? echo $blog_thumb; ?>" alt="<? echo $blog_titulo; ?>" />
									<span></span>
								</div>
								<? } ?>

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

//-----------------------------------------------------------------//

include('include/footer.php');

//fechar conexao com o banco
include("conn/close.php");

?>