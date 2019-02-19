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
<input type="hidden" id="page" value="create-account">
<section id="conteudo">

	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; <? echo $lg['cadastro_login_titulo']; ?>
	</div>

	<section id="principal">
		<section id="cadastro">
			<header>
				<h1><? echo $lg['cadastro_titulo']; ?></h1>
				<!-- <p><? echo $lg['cadastro_descricao']; ?></p> -->
			</header>

			<form name="cadastro" class="infield" method="post" action="<? echo SITE.$link_lang; ?>cadastro/sucesso/">
				<input type="hidden" id="SessionID" name="SessionID">
				<? if ($_GET['compre']){ ?><input type="hidden" name="compre" value="true" /><? } ?>
				<p class="aviso-pais"><? echo $lg['cadastro_pais_reside']; ?></p>
				<section class="selectbox coluna pequeno" id="pais">
					<select name="pais" class="drop" style="width: 100%;">
						<?php foreach ($paises as $key => $pais) { ?>
							<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><?php echo $pais['PAIS_NOME'] ?></option>
						<? } ?>
					</select>
				</section>

				<section id="cadastro-pessoa" class="radio infield big">
					<ul>
						<li><label class="item checked"><input type="radio" name="pessoa" value="F" checked="checked" id="pessoa_fisica"/><? echo $lg['cadastro_pessoa_fisica']; ?></label></li>
						<li><label class="item"><input type="radio" name="pessoa" value="J"  id="pessoa_juridica"/><? echo $lg['cadastro_pessoa_juridica']; ?></label></li>
					</ul>
					<div class="clear"></div>
				</section>

				<input type="hidden" name="legendas" value='<? echo json_encode(array('nome' => $lg['cadastro_nome'], 'nomefantasia' => $lg['cadastro_nome_fantasia'], 'cpf' => $lg['cadastro_cpf'],'passaporte' => $lg['cadastro_passaporte'], 'cnpj' => $lg['cadastro_cnpj'], 'datanascimento' => $lg['cadastro_data_nascimento'], 'datafundacao' => $lg['cadastro_data_fundacao'])); ?>' />

				<div class="clear"></div>
				<p id="cadastro-nome-box">
					<label for="cadastro-nome"><? echo $lg['cadastro_nome']; ?>:</label>
					<input type="text" name="nome" class="input" id="cadastro-nome" />
				</p>
				<p id="cadastro-sobrenome-box">
					<label for="cadastro-sobrenome"><? echo $lg['cadastro_sobrenome']; ?>:</label>
					<input type="text" name="sobrenome" class="input" id="cadastro-sobrenome" />
				</p>
				<p id="cadastro-razao-box">
					<label for="cadastro-razao"><? echo $lg['cadastro_razao_social']; ?>:</label>
					<input type="text" name="razao" class="input" id="cadastro-razao" disabled="disabled" />
				</p>
				<p>
					<label for="cadastro-email"><? echo $lg['cadastro_email']; ?>:</label>
					<input type="text" name="email" class="input" id="cadastro-email" />
				</p>

				<p id="cadastro-cpfcnpj-box">
					<label for="cadastro-cpfcnpj"><? echo $lg['cadastro_cpf']; ?>:</label>
					<input type="text" name="cpfcnpj" class="input" id="cadastro-cpfcnpj" />
				</p> 
				<p class="aviso-pais">Como você conheceu o Folia Tropical?</p>
				<section class="selectbox coluna pequeno" id="origem">
					<select name="origem" style="width: 100%;">
						<option value="Sites de Busca">Sites de busca</option>
						<option value="Redes Socias">Redes Sociais</option>
						<option value="Indicação de amigo">Indicação de amigo</option>
						<option value="Rádio">Rádio</option>
						<option value="TV">TV</option>
						<option value="Agência">Agência</option>
						<option value="Hotel">Hotel</option>
						<option value="Outros">Outros</option>
					</select>
				</section>
				<section id="cadastro-sexo" class="radio infield big">
					<ul>
						<li><label class="item checked"><input type="radio" name="sexo" value="M" checked="checked" /><? echo $lg['cadastro_masculino']; ?></label></li>
						<li><label class="item"><input type="radio" name="sexo" value="F" /><? echo $lg['cadastro_feminino']; ?></label></li>
					</ul>
					<div class="clear"></div>
				</section>

				<div class="coluna">
					<p class="first" id="cadastro-data-nascimento-box">
						<label for="cadastro-data-nascimento"><? echo $lg['cadastro_data_nascimento']; ?>:</label>
						<input type="text" name="data-nascimento" class="input pequeno" id="cadastro-data-nascimento" />
					</p>

					<div class="clear"></div>
				</div>
				<p class="aviso-pais"><? echo $lg['cadastro_telefone_info']; ?></p>
				<div class="coluna">
					<section class="selectbox ddi pequeno" id="ddi">
						<select name="ddi" class="drop" style="width: 190px;">
							<?php foreach ($paises as $key => $pais) { ?>
								<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><?php echo $pais['PAIS_NOME']." +".$pais['PAIS_PHONECODE'] ?></option>
							<? } ?>
						</select>
					</section>
					<p class="first">
						<label for="cadastro-ddd">DDD:</label>
						<input type="text" name="ddd" min="0" maxlength="2" class="input pequeno" id="cadastro-ddd" />
					</p>
					<p>
						<label for="cadastro-telefone"><? echo $lg['cadastro_telefone']; ?>:</label>
						<input type="text" name="telefone" class="input pequeno" id="cadastro-telefone" />
					</p>
					<div class="clear"></div>
				</div>
				<p>
					<label for="cadastro-senha"><? echo $lg['cadastro_senha']; ?>:</label>
					<input type="password" name="senha" class="input" id="cadastro-senha" />
				</p>
				<p>
					<label for="cadastro-csenha"><? echo $lg['cadastro_csenha']; ?>:</label>
					<input type="password" name="csenha" class="input" id="cadastro-csenha" />
				</p>
				<? echo $lg['login_aviso']; ?>
				<p class="submit"><input type="submit" class="submit" value="<? echo $lg['cadastro_enviar']; ?>" /></p>
				
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

		<section id="facebook" class="atendimento">				
			<div class="wrap"><div class="fb-like-box" data-href="https://www.facebook.com/CamaroteFoliaTropical" data-width="302" data-height="552" data-border-color="#ffffff" data-show-faces="true" data-stream="false" data-header="false"></div></div>
		</section>

	</aside>

	<div class="clear"></div>
</section>
<?
/*include('include/escolha-lugar.php');
// Blog
$sql_blog = mysql_query("SELECT a.*, DATE_FORMAT(a.AR_DATA, '%d') AS DIA, DATE_FORMAT(a.AR_DATA, '%m') AS MES, DATE_FORMAT(a.AR_DATA, '%Y') AS ANO, c.* FROM artigos a, blog_categorias c WHERE a.AR_TIPO='blog' AND a.AR_CAT=c.CA_COD AND c.CA_BLOCK<>'*' AND c.D_E_L_E_T_<>'*' AND a.AR_BLOCK<>'*' AND a.D_E_L_E_T_<>'*' ORDER BY a.AR_DATA DESC LIMIT 4");
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
}*/ ?>
<script>
	$(document).ready(function(){
		$('section#conteudo section#cadastro form select[name="pais"],section#conteudo section#cadastro form select[name="ddi"]').select2().trigger('change');
		$('section#conteudo section#cadastro form select[name="origem"]').select2({ minimumResultsForSearch: -1 }).trigger('change');
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