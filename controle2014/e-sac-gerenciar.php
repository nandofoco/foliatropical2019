<?

//Verificamos o dominio
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

header('Content-Type: text/html; charset=utf-8');

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];

switch ($acao) {

	case 'deletar':
		// $sql_update = sqlsrv_query($conexao_sankhya, "UPDATE TOP (1) TGFPAR SET D_E_L_E_T_='1' WHERE CL_COD='$cod'", $conexao_params, $conexao_options);
		$sql_delete = mysql_query("UPDATE contato SET D_E_L_E_T_='*' WHERE CO_COD='$cod'");
		$mensagem = 'Contato excluído com sucesso';
	break;
}

?>
<script type="text/javascript">
	alert('<? echo $mensagem; ?>');
	history.go(-1);
</script>
<?

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

exit();

//-----------------------------------------------------------------------------//

?>