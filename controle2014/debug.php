<?
//Incluir funções básicas
include("include/includes.php");

$usuario = $_SESSION['us-cod'];

$sql_permissoes = sqlsrv_query($conexao, "SELECT TOP 1 MP_MENU, MP_SUBMENU FROM menu_permissoes WHERE MP_USUARIO='$usuario' AND D_E_L_E_T_='0'", $conexao_params, $conexao_options);
$n_permissoes = sqlsrv_num_rows($sql_permissoes);

echo $usuario;

if($n_permissoes > 0) {
	$ar_permissoes = sqlsrv_fetch_array($sql_permissoes);
	$menu_permissoes = $ar_permissoes['MP_MENU'];
	$submenu_permissoes = $ar_permissoes['MP_SUBMENU'];

	$menu_permissoes = (!empty($menu_permissoes)) ? 'ME_COD IN('.$menu_permissoes.') AND' : "";
	$submenu_permissoes = (!empty($submenu_permissoes)) ? 'AND SM_COD IN('.$submenu_permissoes.')' : "";

}
?>