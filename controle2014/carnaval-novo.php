<?

//Incluir funções básicas
include("include/includes.php");

unset($_SESSION['carnaval-dias'], $_SESSION['carnaval-setores']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$ano_proximo = date('Y', strtotime('+1year'));

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Carnaval <span>Novo</span></h1>
	</header>
	<section class="padding">
		<form id="carnaval-novo" method="post" action="#">
			<p class="coluna">
				<label for="carnaval-dia">Adicione os dias</label>
				<input type="text" name="dia" class="input" id="carnaval-dia" />
				<input type="hidden" name="ano" class="input" id="carnaval-ano" value="<? echo $ano_proximo; ?>" />
			</p>
			<input type="submit" class="submit adicionar coluna" value="+" />
		</form>
		<form id="carnaval-novo-escolas" method="post" action="<? echo SITE; ?>carnaval/novo/post/">
			<section class="secao label-top" id="carnaval-dados">
				<section class="selectbox coluna" id="carnaval-ano">
					<h3>Insira o Ano</h3>

					<a href="#" class="arrow"><strong><? echo $ano_proximo; ?></strong><span></span></a>
					<ul class="drop">
						<? for ($ano=$ano_proximo; $ano<=(date('Y', strtotime('+3 years'))) ; $ano++) { ?>
						<li><label class="item <? if($ano == $ano_proximo) echo 'checked'; ?>"><input type="radio" name="ano" value="<? echo $ano; ?>" alt="<? echo $ano; ?>" <? if($ano == $ano_proximo) echo 'checked="checked"'; ?> /><? echo $ano; ?></label></li>
						<? } ?>
					</ul>
				</section>

				<p class="coluna">
					<label for="carnaval-nome">Insira um nome</label>
					<input type="text" name="nome" class="input" id="carnaval-nome" value="Carnaval <? echo $ano_proximo; ?>" />
				</p>

				<div class="clear"></div>
			</section>		
			<section id="carnaval-dias-selecionados" class="secao">
				<ul></ul>
			</section>
			<section id="carnaval-dias-atracoes" class="secao">
				<h3>Adicione as escolas de samba por dia <span>(Separadas por vírgula)</span></h3>
				<ul></ul>
				<footer class="controle">
					<input type="submit" class="submit coluna" value="Inserir" />
					<a href="#" class="cancel coluna">Cancelar</a>
					<div class="clear"></div>
				</footer>
			</section>
		</form>
		<section class="secao" id="carnaval-setores">
			<form id="carnaval-novo-setores" method="post" action="#">
				<h3>Adicionar Setores</h3>
				<p>
					<label for="carnaval-setor" class="infield"></label>
					<input type="text" name="setor" class="input" id="carnaval-setor" />
					<small>Adicione o número ou a sigla dos setores disponíveis (Ex. "1", "FT")</small>
				</p>
				<input type="submit" class="submit adicionar coluna" value="+" />
			</form>
			<ul>
			</ul>
			<div class="clear"></div>
		</section>
	</section>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>