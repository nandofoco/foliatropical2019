<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

$cliente = $_SESSION['usuario-cod'];


// // Selecionar dados do cliente
$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 *, CONVERT(CHAR, DTNASC, 103) AS DATA FROM TGFPAR WHERE CODPARC='$cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);

if(sqlsrv_num_rows($sql_cliente) > 0) {
	
	$ar_cliente = sqlsrv_fetch_array($sql_cliente);

	$cliente_cod = utf8_encode(trim($ar_cliente['CODPARC']));
	$cliente_data_nascimento = utf8_encode(trim($ar_cliente['DATA']));
	$cliente_cpf_cnpj = trim($ar_cliente['CGC_CPF']);
	$cliente_passaporte = trim($ar_cliente['AD_IDENTIFICACAO']);
	$cliente_pais = trim($ar_cliente['PAIS_SIGLA']);

}
// 	//Verifica se o CPF ou o Passaporte estão vazios

// 	$emptyCPF = false;
// 	$emptyPassaport = false;

// 	if ($cliente_pais == 'BR') 
// 	{
// 		$session_language = ('BR');

// 		if (empty($cliente_cpf_cnpj))
// 		{
// 			$emptyCPF = true;
// 		}
// 	} 
// 	else
// 	{
// 		$session_language = ('US');

// 		if (empty($cliente_passaporte))
// 		{
// 			$emptyPassaport = true;
// 		}
// 	}

	setcookie('ftropsitelang', $session_language, time()+(3600*24*30*12*5), '/');
	$lg = $lang[$session_language];
	
	//-----------------------------------------------------------------------------//

	//Canonical
	$meta_canonical = SITE.$link_lang."atualiza-dados/";

	//arquivos de layout
	include("include/head_atualiza_dados.php");
	include("include/header.php");



	//-----------------------------------------------------------------------------//


	// if (empty($cliente_data_nascimento) || $emptyCPF || $emptyPassaport):

	// 	$_SESSION['atualizacao_dados'] = false;

	?>
		<input type="hidden" id="page" value="edit-account">
		<section id="conteudo">

			<div id="breadcrumb" itemprop="breadcrumb"> 
				<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; <? echo $lg['menu_meus_dados']; ?>
			</div>

			<section id="principal">
				<section id="cadastro">			
				
					<header>
						<h1><? echo $lg['atualizacao_dados']; ?></h1>
						<p><? echo $lg['atualizacao_dados_desc']; ?>:</p>
					</header>
					
					<!-- <form name="cadastro" class="editar" method="post" action="<? echo SITE.$link_lang; ?>meus-dados/alterar/"> -->
						<form name="cadastro" class="editar" method="post" action="<? echo SITE ?>e-atualiza-dados.php">
						<input type="hidden" name="cod" value="<? echo $cliente_cod; ?>" />
						<section id="cadastro-pessoa" class="radio infield big" style="display: none;">
							<ul>
								<li><label class="item"><input type="radio" name="pessoa" value="F" /><? echo $lg['cadastro_pessoa_fisica']; ?></label></li>
								<li><label class="item"><input type="radio" name="pessoa" value="J" /><? echo $lg['cadastro_pessoa_juridica']; ?></label></li>
							</ul>
							<div class="clear"></div>
						</section>

						<p id="cadastro-cpfcnpj-box">
							<label for="<? echo ($cliente_pais == 'BR') ? 'cadastro-cpfcnpj' : 'cadastro-passaporte'; ?>"><? echo ($cliente_pais == 'BR') ? $lg['cadastro_cpf'] : $lg['cadastro_passaporte']; ?></label>
							
							<input type="text" name="<? echo ($cliente_pais == 'BR') ? 'cpfcnpj' : 'passaporte'; ?>" class="input" id="cadastro-<? echo ($cliente_pais == 'BR') ? 'cpfcnpj' : 'passaporte'; ?>">
						</p>


						<div class="coluna">
							<p class="first" id="cadastro-data-nascimento-box">
								<label for="cadastro-data-nascimento"><? echo $lg['cadastro_data_nascimento']; ?>:</label>
								<input type="text" name="data-nascimento" class="input pequeno" id="cadastro-data-nascimento" />
							</p>
							<div class="clear"></div>
						</div>

						<p class="submit"><input type="submit" class="submit" value="<? echo $lg['atualizacao_btn_submit']; ?>" /></p>
						<!-- <input type="submit" value="Enviar"></button> -->
					</form>
					
				</section>
			</section>
			
			<aside>

				<section id="aside-atendimento">
					<!-- <section class="chat-online">
						<h2><? echo $lg['atendimento_chat_online']; ?></h2>
						<a href="javascript:void(window.open('<? echo SITE.$link_lang; ?>chat/chat.php','','width=590,height=610,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))" class="chat"><? echo $lg['atendimento_acesse_chat']; ?></a>
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
		//-----------------------------------------------------------------//

		include('include/footer.php');



		// if ($_SESSION['plataforma'] == 'antiga')
		// {
		// 	echo '<script type="text/javascript">
		// 	alert("Atualizado com sucesso!");
		// 	location.href="https://ingressos.foliatropical.com.br/'.$lg.'/ingressos/";
		// 	</script>';
		// }
		// else if ($_SESSION['plataforma'] == 'atual')
		// {
		// 	echo '<script type="text/javascript">
		// 	alert("Atualizado com sucesso!");
		// 	location.href="https://ingressos.foliatropical.com.br/compras/";
		// 	</script>';
		// }


//fechar conexao com o banco
include("conn/close.php");

?>