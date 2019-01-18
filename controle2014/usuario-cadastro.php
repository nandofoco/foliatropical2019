<?

//Incluir funções básicas
include("include/includes.php");

//-----------------------------------------------------------------//

//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

?>
<section id="conteudo">
	<header class="titulo">
		<h1>Cadastro de <span>Usuários</span></h1>		
	</header>
	<form id="cadastro-usuario" class="cadastro" method="post" action="<? echo SITE; ?>usuarios/cadastro/post/">
		<section class="secao">
			<p>
				<label for="usuario-login">Login:</label>
				<input type="text" name="login" class="input" id="usuario-login" />
			</p>
			<p>
				<label for="usuario-nome">Nome:</label>
				<input type="text" name="nome" class="input" id="usuario-nome" />
			</p>
			<p>
				<label for="usuario-email">Email:</label>
				<input type="text" name="email" class="input" id="usuario-email" />
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
			<h1>Permissões</h1>		
		</header>
		<section class="secao">
		<?
		// $sql_menu_permissoes = sqlsrv_query($conexao, "(SELECT @menu:=ME_COD as codigo, ME_COD as codigo_menu, ME_MENU as titulo,  @submenu:=0 as submenu FROM menu WHERE D_E_L_E_T_=0) UNION (SELECT @menu:=SM_COD as codigo, SM_MENU as codigo_menu, SM_SUBMENU as titulo,  @submenu:=1 as submenu FROM submenu WHERE D_E_L_E_T_=0)");
		$sql_menu_permissoes = sqlsrv_query($conexao, "
			DECLARE @menu TABLE (codigo INT, codigo_menu INT, titulo VARCHAR(255), submenu TINYINT DEFAULT 0);
			DECLARE @submenu TABLE (codigo INT, codigo_menu INT, titulo VARCHAR(255), submenu TINYINT DEFAULT 1);

			INSERT INTO @menu (codigo, codigo_menu, titulo)
			SELECT 
			ME_COD,
			ME_COD,
			ME_MENU
			FROM menu WHERE D_E_L_E_T_=0;

			INSERT INTO @submenu (codigo, codigo_menu, titulo)
			SELECT 
			SM_COD,
			SM_MENU,
			SM_SUBMENU
			FROM submenu WHERE D_E_L_E_T_=0;

			(SELECT * FROM @menu)
			UNION
			(SELECT * FROM @submenu);", $conexao_params, $conexao_options);

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
					} elseif($submenu) {
						$lista_menus[$menu_permissoes['codigo_menu']]['submenu'][$menu_permissoes['codigo']]['cod'] = utf8_encode($menu_permissoes['codigo']);								
						$lista_menus[$menu_permissoes['codigo_menu']]['submenu'][$menu_permissoes['codigo']]['titulo'] = utf8_encode($menu_permissoes['titulo']);													
					}
					$i++;
				}
			}

			
			foreach ($lista_menus as $key => $menu) {
				$menu_cod = $key;
				$menu_titulo = $menu['menu_titulo'];
				$n_submenu = count($menu['submenu']);
				?>							
				<tr class="principal checked">
					<td class="check">
						<section class="checkbox verify">
							<ul><li><label class="item checked"><input type="checkbox" name="menuscod[]" value="<? echo $menu_cod; ?>" checked="checked" /></label></li></ul>
						</section>
					</td>
					<td class="nome" colspan="2"><? echo $menu_titulo; ?></td>
				</tr>
				<?
				if($n_submenu > 0) {
					foreach ($menu['submenu'] as $key => $submenu) {
						$submenu_titulo = $submenu['titulo'];
						?>
						<tr class="checked">	
							<td class="first"></td>						
							<td class="check submenu">
								<section class="checkbox verify">
									<ul><li><label class="item checked"><input type="checkbox" rel="<? echo $menu_cod; ?>" name="submenuscod[]" value="<? echo $key; ?>" checked="checked" /></label></li></ul>
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
			<input type="submit" class="submit coluna" value="Inserir" />
			<a href="#" class="cancel coluna">Cancelar</a>
			<div class="clear"></div>
		</footer>
	</form>
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

//Fechar conexoes
include("conn/close.php");

?>