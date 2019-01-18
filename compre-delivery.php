<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");


unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

if(!checklogado()){
?>
<script type="text/javascript">
	location.href='<? echo SITE.$link_lang; ?>';
</script>
<?
	exit();
}

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = setcarnaval();
$cod = (int) $_GET['c'];
$transfer = (bool) $_GET['transfer'];
$detalhes = (bool) $_GET['detalhes'];

$usuario_cod = $_SESSION['usuario-cod'];

//-----------------------------------------------------------------//

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, CONVERT(CHAR, l.LO_DATA_COMPRA, 103) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_CLIENTE='$usuario_cod' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<div id="breadcrumb" itemprop="breadcrumb"> 
		<a href="<? echo SITE.$link_lang; ?>"><? echo $lg['menu_inicio']; ?></a> &rsaquo; <? echo $lg['menu_loja']; ?>
	</div>
	<section id="compre-aqui">

		<header class="titulo">
			<h1><? echo $lg['compre_delivery_titulo']; ?></h1>
		</header>
		<section class="padding">
		<?
		if(sqlsrv_num_rows($sql_loja) > 0) {

			$loja = sqlsrv_fetch_array($sql_loja);

			$loja_cod = $loja['LO_COD'];
			$loja_cliente = $loja['LO_CLIENTE'];

			//Informações do delivery
			$loja_endereco = utf8_encode($loja['LO_CLI_ENDERECO']);
			$loja_numero = utf8_encode($loja['LO_CLI_NUMERO']);
			$loja_complemento = utf8_encode($loja['LO_CLI_COMPLEMENTO']);
			$loja_bairro = utf8_encode($loja['LO_CLI_BAIRRO']);
			$loja_cidade = utf8_encode($loja['LO_CLI_CIDADE']);
			$loja_estado = utf8_encode($loja['LO_CLI_ESTADO']);
			$loja_cep = utf8_encode($loja['LO_CLI_CEP']);

			// $loja_cliente = utf8_encode($loja['CL_NOME']);
			$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);

			$loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
			$loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
			$loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));
			
			?>
			<form id="compras-delivery" class="cadastro controle" method="post" action="<? echo SITE.$link_lang; ?>ingressos/delivery/post/">
				
				<input type="hidden" name="cod" value="<? echo $cod; ?>" />
				<input type="hidden" name="transfer" value="<? echo $transfer; ?>" />
				<input type="hidden" name="detalhes" value="<? echo $detalhes; ?>" />

				<section class="secao" id="compra-dados">
					<aside><? echo $loja_cod; ?></aside>
					<section>
						<h3><? echo $loja_nome; ?></h3>
						<p><? echo $loja_email; ?></p>
						<p><? echo $loja_telefone; ?></p>
					</section>

					<div class="clear"></div>
				</section>		
				
				<section class="secao">
					<p>
						<label for="parceiro-cep">CEP:</label>
						<input type="text" name="cep" class="input pequeno" id="parceiro-cep" value="<? echo $loja_cep; ?>" />
						<a href="http://www.correios.com.br/servicos/cep/" class="esqueci" target="_blank">Esqueci meu CEP</a>
					</p>
					<p>
						<label for="parceiro-endereco">Endereço:</label>
						<input type="text" name="endereco" class="input" id="parceiro-endereco" value="<? echo $loja_endereco; ?>" readonly="readonly" />
					</p>
					<p>
						<label for="parceiro-numero">Número:</label>
						<input type="text" name="numero" class="input pequeno" id="parceiro-numero" value="<? echo $loja_numero; ?>" />
					</p>
					<p>
						<label for="parceiro-complemento">Complemento:</label>
						<input type="text" name="complemento" class="input pequeno" id="parceiro-complemento" value="<? echo $loja_complemento; ?>" />
					</p>
					<p>
						<label for="parceiro-bairro">Bairro:</label>
						<input type="text" name="bairro" class="input" id="parceiro-bairro" value="<? echo $loja_bairro; ?>" readonly="readonly" />
					</p>
					<p>
						<label for="parceiro-cidade">Cidade:</label>
						<input type="text" name="cidade" class="input" id="parceiro-cidade" value="<? echo $loja_cidade; ?>" readonly="readonly" />
					</p>
					
					<section class="selectbox coluna" id="parceiros-estado">
						<h3>Estado:</h3>
						<a href="#" class="arrow"><strong>SELECIONE</strong><span></span></a>
						<ul class="drop">
		                    <li><label class="item"><input type="radio" name="estado" alt="Acre" value="AC">Acre</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Alagoas" value="AL">Alagoas</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Amapá" value="AP">Amapá</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Amazonas" value="AM">Amazonas</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Bahia" value="BA">Bahia</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Ceará" value="CE">Ceará</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Distrito Federal" value="DF">Distrito Federal</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Espírito Santo" value="ES">Espírito Santo</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Goiás" value="GO">Goiás</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Maranhão" value="MA">Maranhão</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Mato Grosso" value="MT">Mato Grosso</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Mato Grosso do Sul" value="MS">Mato Grosso do Sul</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Minas Gerais" value="MG">Minas Gerais</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Pará" value="PA">Pará</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Paraíba" value="PB">Paraíba</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Paraná" value="PR">Paraná</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Pernambuco" value="PE">Pernambuco</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Piauí" value="PI">Piauí</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Rio de Janeiro" value="RJ">Rio de Janeiro</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Rio Grande do Norte" value="RN">Rio Grande do Norte</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Rio Grande do Sul" value="RS">Rio Grande do Sul</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Rondônia" value="RO">Rondônia</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Roraima" value="RR">Roraima</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Santa Catarina" value="SC">Santa Catarina</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="São Paulo" value="SP">São Paulo</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Sergipe" value="SE">Sergipe</label></li>
		                    <li><label class="item"><input type="radio" name="estado" alt="Tocantins" value="TO">Tocantins</label></li>                        
						</ul>
						<div class="clear"></div>
					</section>

					<div class="clear"></div>
				</section>
				<?

				$link_proximo = SITE.$link_lang.'ingressos/pagamento/'.$cod.'/';
				if($transfer) $link_proximo = SITE.$link_lang.'ingressos/agendamento/'.$cod.'/';
				if($detalhes) $link_proximo = SITE.$link_lang.'minhas-compras/detalhes/'.$cod.'/';

				?>
				<footer class="controle">
					<input type="submit" class="submit coluna" value="Cadastrar" />
					<a href="<? echo $link_proximo; ?>" class="cancel coluna">Pular etapa</a>
					<p class="aviso-delivery"><? echo $lg['produtos_servicos_delivery_ecommerce']; ?></p>
					<!--<a href="<? echo strpos($_SERVER['HTTP_REFERER'], 'financeiro') ? $_SERVER['HTTP_REFERER'] : SITE.$link_lang.'financeiro/'; ?>" class="cancel coluna">Voltar</a>-->
					<div class="clear"></div>
				</footer>
			</form>

			<? if (!empty($loja_estado)){ ?>
		<script type="text/javascript">
		$(document).ready(function(){
			$("form#compras-delivery input[name='estado']").radioSel('<? echo $loja_estado; ?>');
		});
		</script>
		<? } ?>
		<?
		}
		?>	
		</section>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//fechar conexao com o banco
include("conn/close.php");
include("conn/close-mssql.php");
include("conn/close-sankhya.php");

?>