<?

//Verificamos o dominio
include("include/includes.php");

//-----------------------------------------------------------------------------//

$cod = (int) $_POST['cod'];
$nome = format($_POST['nome']);
$tipo = format($_POST['tipo']);
$valor = format($_POST['valor']);
$data_validade = todate($_POST['data-validade'], "ddmmaaaa");
$prefixo = format($_POST['prefixo']);
$quantidade = format($_POST['quantidade']);

$valor = str_replace(',', '.', str_replace('.', '', $valor));

//-----------------------------------------------------------------------------//

$resposta = "Ocorreu um erro, tente novamente.";
$erro = false;

//-----------------------------------------------------------------------------//

if(!$erro && !$editar && !empty($nome) && !empty($tipo) && !empty($valor) && !empty($data_validade) && !empty($prefixo) && !empty($quantidade)) {
	for($q=1; $q<=$quantidade; $q++) {

		$caracteres_aceitos = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$max = strlen($caracteres_aceitos)-1;
		$codigo_aleatorio = null;
		
		for($i=0; $i < 3; $i++) { $codigo_aleatorio .= $caracteres_aceitos{mt_rand(0, $max)}; }	
		
		$codigo_cupom = strtoupper($prefixo.$codigo_aleatorio);

		$sql_insert = sqlsrv_query($conexao, "INSERT INTO cupom (CP_NOME, CP_TIPO, CP_DATA_VALIDADE, CP_DESCONTO, CP_CUPOM) VALUES ('$nome','$tipo','$data_validade','$valor','$codigo_cupom')", $conexao_params, $conexao_options);
		$resposta = "Cupom cadastrado com sucesso.";

	}	
	?>
	<script type="text/javascript">
		alert('<? echo $resposta; ?>');
		location.href='<? echo SITE; ?>cupons/';
	</script>
	<?

	//Fechar conexoes
	include("conn/close.php");
	
	exit();

}

?>
<script type="text/javascript">
	alert('<? echo $resposta; ?>');
	history.go(-1);
</script>