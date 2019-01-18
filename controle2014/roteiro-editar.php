<?

//Incluir funções básicas
include("include/includes.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$cod = (int) $_GET['c'];

$sql_roteiro = sqlsrv_query($conexao, "SELECT TOP 1 * FROM roteiros WHERE RO_COD='$cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
?>
<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Roteiro</span></h1>
	</header>	
	<section class="padding">
	<?
	if(sqlsrv_num_rows($sql_roteiro) > 0) {

		$roteiro = sqlsrv_fetch_array($sql_roteiro);

		$roteiro_cod = $roteiro['RO_COD'];
		$roteiro_nome = utf8_encode($roteiro['RO_NOME']);
		$roteiro_tipo = $roteiro['RO_TIPO'];
	?>
		<form id="roteiro-novo" method="post" action="<? echo SITE; ?>roteiros/cadastro/post/">
			<input type="hidden" name="cod" value="<? echo $cod; ?>" />
			<input type="hidden" name="editar" value="true" />
			<section class="secao label-top" id="roteiro-dados">
				<p class="coluna">
					<label for="roteiro-nome">Nome do Roteiro</label>
					<input type="text" name="titulo" class="input" id="roteiro-nome" value="<? echo $roteiro_nome; ?>" />
				</p>
				<section class="radio infield big coluna">
					<h3>Tipo de Setor</h3>
					<ul>						
						<li><label class="item"><input type="radio" name="tipo" value="1" />Pares</label></li>
						<li><label class="item"><input type="radio" name="tipo" value="2" />Ímpares</label></li>
						<li><label class="item"><input type="radio" name="tipo" value="3" />FT</label></li>
						<li><label class="item"><input type="radio" name="tipo" value="4" />Camarote</label></li>
					</ul>
				</section>
				<div class="clear"></div>
			</section>		
			<section id="roteiro-itens" class="secao">
				<h3>Itens do roteiro</h3>
				<ul>
				<?
				$sql_itens = sqlsrv_query($conexao, "SELECT * FROM transportes WHERE TR_ROTEIRO='$roteiro_cod' AND D_E_L_E_T_='0' ORDER BY TR_COD ASC", $conexao_params, $conexao_options);
				if(sqlsrv_num_rows($sql_itens) > 0) {
					$i = 1;
					while ($item = sqlsrv_fetch_array($sql_itens)) {
						$item_cod = $item['TR_COD'];
						$item_nome = utf8_encode($item['TR_NOME']);
						$item_endereco = utf8_encode($item['TR_ENDERECO']);
						$item_telefone = $item['TR_TELEFONE'];
				?>
						<li rel="<? echo $i; ?>">
							<a href="<? echo SITE; ?>e-roteiro-gerenciar.php?c=<? echo $item_cod; ?>&a=exc-item" class="remover confirm" title="Tem certeza que deseja remover esse item?"></a>
							<input type="hidden" name="editar-item[<? echo $i; ?>]" value="<? echo $item_cod; ?>" />
							<p class="coluna">
								<label for="roteiro-item-<? echo $i; ?>-nome" class="infield">Nome do Local</label>
								<input type="text" name="nome[<? echo $i; ?>]" class="input nome" id="roteiro-item-<? echo $i; ?>-nome" value="<? echo $item_nome; ?>" />
							</p>
							<p class="coluna">
								<label for="roteiro-item-<? echo $i; ?>-endereco" class="infield">Endereço</label>
								<input type="text" name="endereco[<? echo $i; ?>]" class="input horario" id="roteiro-item-<? echo $i; ?>-endereco" value="<? echo $item_endereco; ?>" />
							</p>
							<p class="coluna">
								<label for="roteiro-item-<? echo $i; ?>-telefone" class="infield">Telefone</label>
								<input type="text" name="telefone[<? echo $i; ?>]" class="input horario" id="roteiro-item-<? echo $i; ?>-telefone" value="<? echo $item_telefone; ?>" />
							</p>
							<?
							$sql_horarios = sqlsrv_query($conexao, "SELECT *, SUBSTRING(CONVERT(CHAR, TH_HORA, 8), 1, 5) AS hora FROM transportes_horarios WHERE TH_TRANSPORTE='$item_cod' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
							if(sqlsrv_num_rows($sql_horarios) > 0) {
								$h=1;
								while ($horario = sqlsrv_fetch_array($sql_horarios)) {
									$horario_cod = $horario['TH_COD'];		
									$horario_hora = $horario['hora'];		
							?>
									<input type="hidden" name="editar-horario[<? echo $i; ?>][<? echo $h; ?>]" value="<? echo $horario_cod; ?>" />
									<p class="coluna">
										<label for="roteiro-item-<? echo $i; ?>-horario-<? echo $h; ?>" class="infield">Horário 0<? echo $h; ?></label>
										<input type="text" name="horario[<? echo $i; ?>][<? echo $h; ?>]" class="input horario" id="roteiro-item-<? echo $i; ?>-horario-<? echo $h; ?>" value="<? echo $horario_hora; ?>" />
									</p>
							<?
									$h++;
								}
							}
							?>
							<div class="clear"></div>
						</li>
					<?
						$i++;
					}
				}
					?>
					<a href="#" class="adicionar">+</a>
					<div class="clear"></div>
				</ul>
				<footer class="controle">
					<input type="submit" class="submit coluna" value="Alterar" />
					<a href="#" class="cancel coluna">Cancelar</a>
					<div class="clear"></div>
				</footer>
			</section>
		</form>
	<?
	}
	?>	
	</section>
</section>
<script type="text/javascript">
$(document).ready(function(){
	$("form#roteiro-novo").find("input[name='tipo']").radioSel('<? echo $roteiro_tipo; ?>');
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");


?>