<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$cod = (int) $_GET['c'];

//-----------------------------------------------------------------//

$sql_usuarios = sqlsrv_query($conexao, "SELECT * FROM usuarios WHERE US_COD='$cod' AND US_BLOCK='0' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Editar <span>Usuário</span></h1>		
	</header>
	<?
	if(sqlsrv_num_rows($sql_usuarios) > 0) {

		$usuario = sqlsrv_fetch_array($sql_usuarios);
		$usuario_nome = utf8_encode($usuario['US_NOME']);
		$usuario_login = utf8_encode($usuario['US_LOGIN']);
		$usuario_email = $usuario['US_EMAIL'];
		$usuario_grupo = $usuario['US_GRUPO'];
		$usuario_filial = $usuario['US_FILIAL'];

		?>
		<form id="cadastro-usuario" class="cadastro" method="post" action="<? echo SITE; ?>usuarios/cadastro/post/">
			<input type="hidden" name="editar" value="true">
			<input type="hidden" name="cod" value="<? echo $cod; ?>">
			<section class="secao">						
						<p>
							<label for="usuario-nome">Nome:</label>
							<input type="text" name="nome" class="input" id="usuario-nome" value="<? echo $usuario_nome; ?>" />
						</p>
						<p>
							<label for="usuario-email">Email:</label>
							<input type="text" name="email" class="input" id="usuario-email" value="<? echo $usuario_email; ?>" />
						</p>
						<section class="selectbox coluna pequeno" id="usuario-grupo">
							<h3>Grupo:</h3>
							<a href="#" class="arrow"><strong>Grupo</strong><span></span></a>
							<ul class="drop">
			                    <li><label class="item"><input type="radio" name="grupo" alt="Administrador" value="ADM">Administrador</label></li>
			                    <li><label class="item"><input type="radio" name="grupo" alt="Vendedor Interno" value="VIN">Vendedor Interno</label></li>
			                    <li><label class="item"><input type="radio" name="grupo" alt="Atendente" value="ATE">Atendente</label></li>
							</ul>
							<div class="clear"></div>
						</section>
						<section class="selectbox coluna pequeno" id="usuario-filial">
							<h3>Filial:</h3>
							<a href="#" class="arrow"><strong>Filial</strong><span></span></a>
							<ul class="drop">
			                    <li><label class="item"><input type="radio" name="filial" alt="Pafícica Centro" value="1">Pafícica Centro</label></li>
			                    <li><label class="item"><input type="radio" name="filial" alt="Pacífica Ipanema" value="2">Pacífica Ipanema</label></li>
			                    <li><label class="item"><input type="radio" name="filial" alt="Pacífica Leblon" value="3">Pacífica Leblon</label></li>
							</ul>
							<div class="clear"></div>
						</section>
						<div class="clear"></div>			
					</section>
					<header class="titulo">
						<h1>Dados de<span> Acesso</span></h1>		
					</header>
					<section class="secao">
						<p>
							<label for="usuario-login">Login:</label>
							<input type="text" name="login" class="input pequeno" id="usuario-login" value="<? echo $usuario_login; ?>" />
						</p>
						<p>
							<label for="cliente-senha">Nova Senha:</label>
							<input type="password" name="senha" class="input pequeno" id="cliente-senha" />
						</p>
						<p>
							<label for="cliente-csenha">Confirmar Senha:</label>
							<input type="password" name="csenha" class="input pequeno" id="cliente-csenha" />
						</p>
					</section>
					<header class="titulo">
						<h1>Permissões</h1>		
					</header>
					<section class="secao">
					<?
					
					$sql_menu_permissoes = sqlsrv_query($conexao, "
						DECLARE @menu TABLE (codigo INT, codigo_menu INT, titulo VARCHAR(255), submenu TINYINT DEFAULT 0, menu_permissoes VARCHAR(255));
						DECLARE @submenu TABLE (codigo INT, codigo_menu INT, titulo VARCHAR(255), submenu TINYINT DEFAULT 1, menu_permissoes VARCHAR(255));

						INSERT INTO @menu (codigo, codigo_menu, titulo, menu_permissoes)
						SELECT 
						ME_COD,
						ME_COD,
						ME_MENU,
						(SELECT MP_MENU FROM menu_permissoes WHERE MP_USUARIO=$cod) 
						FROM menu WHERE D_E_L_E_T_=0;

						INSERT INTO @submenu (codigo, codigo_menu, titulo, menu_permissoes)
						SELECT 
						SM_COD,
						SM_MENU,
						SM_SUBMENU,
						(SELECT MP_SUBMENU FROM menu_permissoes WHERE MP_USUARIO=$cod) 
						FROM submenu WHERE D_E_L_E_T_=0;

						(SELECT *, CASE WHEN CHARINDEX(','+CAST(codigo AS varchar(50))+',', ','+menu_permissoes+',') > 0 THEN 1 ELSE 0 END AS permitido FROM @menu)
						UNION
						(SELECT *, CASE WHEN CHARINDEX(','+CAST(codigo AS varchar(50))+',', ','+menu_permissoes+',') > 0 THEN 1 ELSE 0 END AS permitido FROM @submenu);", $conexao_params, $conexao_options);
					
					if(sqlsrv_next_result($sql_menu_permissoes) && sqlsrv_next_result($sql_menu_permissoes))
					$n_menu_permissoes = sqlsrv_num_rows($sql_menu_permissoes);

					if($n_menu_permissoes !== false) { ?>
					<table class="lista">
						<tbody>
						<?
							$lista_menus = array();
							$i=0;
							while ($menu_permissoes = sqlsrv_fetch_array($sql_menu_permissoes)) {
								$submenu = (bool) $menu_permissoes['submenu'];
								if(!$submenu) { 
									$lista_menus[$menu_permissoes['codigo']]['menu_titulo'] = utf8_encode($menu_permissoes['titulo']);
									$lista_menus[$menu_permissoes['codigo']]['menu_permissao'] = $menu_permissoes['permitido'];
								} elseif($submenu) {
									$lista_menus[$menu_permissoes['codigo_menu']]['submenu'][$menu_permissoes['codigo']]['cod'] = utf8_encode($menu_permissoes['codigo']);								
									$lista_menus[$menu_permissoes['codigo_menu']]['submenu'][$menu_permissoes['codigo']]['titulo'] = utf8_encode($menu_permissoes['titulo']);								
									$lista_menus[$menu_permissoes['codigo_menu']]['submenu'][$menu_permissoes['codigo']]['permissao'] = $menu_permissoes['permitido'];								
								}
								$i++;
							}
						}

						foreach ($lista_menus as $key => $menu) {
							$menu_cod = $key;
							$menu_titulo = $menu['menu_titulo'];
							$menu_permissao = (bool) $menu['menu_permissao'];
							$n_submenu = count($menu['submenu']);
							?>							
							<tr class="principal <? if ($menu_permissao){ echo 'checked'; } ?>">
								<td class="check">
									<section class="checkbox verify">
										<ul><li><label class="item <? if ($menu_permissao){ echo 'checked'; } ?>"><input type="checkbox" name="menuscod[]" value="<? echo $menu_cod; ?>" <? if ($menu_permissao){ echo 'checked="checked"'; } ?> /></label></li></ul>
									</section>
								</td>
								<td class="nome" colspan="2"><? echo $menu_titulo; ?></td>
							</tr>
							<?
							if($n_submenu > 0) {
								foreach ($menu['submenu'] as $key => $submenu) {
									$submenu_titulo = $submenu['titulo'];
									$submenu_permissao = (bool) $submenu['permissao'];
									?>
									<tr <? if ($submenu_permissao){ echo 'class="checked"'; } ?>>	
										<td class="first"></td>						
										<td class="check submenu">
											<section class="checkbox verify">
												<ul><li><label class="item <? if ($submenu_permissao){ echo 'checked'; } ?>"><input type="checkbox" rel="<? echo $menu_cod; ?>" name="submenuscod[]" value="<? echo $key; ?>" <? if ($submenu_permissao){ echo 'checked="checked"'; } ?> /></label></li></ul>
											</section>
										</td>
										<td class="nome"><? echo $submenu_titulo; ?></td>
									</tr>
									<?
								}
							}			
						}					
						?>	
						</tbody>
					</table>
					</section>
					<footer class="controle">
						<input type="submit" class="submit coluna" value="Alterar" />
						<a href="#" class="cancel coluna">Cancelar</a>
						<div class="clear"></div>
					</footer>
				</form>
	<?
	}
	?>
</section>
<script type="text/javascript">
$(document).ready(function(){
	$("form#cadastro-usuario").find("input[name='grupo']").radioSel('<? echo $usuario_grupo; ?>');
	$("form#cadastro-usuario").find("input[name='filial']").radioSel('<? echo $usuario_filial; ?>');
});
</script>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>