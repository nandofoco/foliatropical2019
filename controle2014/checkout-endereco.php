<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

header("Content-type: application/json; charset=utf-8");

$tipo = format($_GET['t']);
$sucesso=false;
$msg="";

switch ($tipo) {

	case 'editar':
		$cod = (int) $_POST['cod'];
		$cliente = (int) $_POST['cliente'];
		$cep = format($_POST['cep']);
		$endereco = format($_POST['endereco']);
		$numero = $_POST['numero'];
		if(empty($numero)) $numero = 0;
		$complemento = format($_POST['complemento']);
		$bairro = format($_POST['bairro']);
		$cidade = format($_POST['cidade']);
		$estado = format($_POST['estado']);
		$tipo_endereco = format($_POST['tipo_endereco']);
		$referencia = format($_POST['referencia']);

		if(!empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($tipo_endereco)) {
			$sql_cadastro = sqlsrv_query($conexao_sankhya, "UPDATE clientes_enderecos SET CE_CEP='$cep', CE_ENDERECO='$endereco', CE_NUMERO='$numero', CE_COMPLEMENTO='$complemento', CE_BAIRRO='$bairro', CE_CIDADE='$cidade', CE_ESTADO='$estado', CE_TIPO_ENDERECO='$tipo_endereco', CE_PONTO_REFERENCIA='$referencia' WHERE CE_COD=$cod", $conexao_params, $conexao_options);
			if($sql_cadastro){
				$sucesso=true;
				$msg="Endereço alterado com sucesso!";
			}else{
				$msg="Não foi possível alterar o endereço!";
			}
		}
		echo json_encode(array('sucesso'=>$sucesso,'msg'=>$msg));
	break;

	case 'consultar':
		$cliente = (int) $_POST['cliente'];
		$sql_enderecos = sqlsrv_query($conexao_sankhya, "SELECT * FROM clientes_enderecos WHERE CE_CLIENTE=$cliente AND CE_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY CE_ULTIMA_ENTREGA DESC", $conexao_params, $conexao_options);
		$enderecos=array();
		$sucesso=true;
		while ($linha = sqlsrv_fetch_array($sql_enderecos)) {
			array_push($enderecos, $linha);
		}
		utf8_encode_deep($enderecos);
		//echo var_dump($enderecos);
		echo json_encode(array('sucesso'=>$sucesso,'msg'=>$msg,'enderecos'=>$enderecos));
	break;
	
	case 'cadastrar':
		// $edit = (bool) $_POST['edit'];
		// $cod = (int) $_POST['cod'];
		$cliente = (int) $_POST['cliente'];
		// $destinatario = format($_POST['destinatario']);
		$cep = format($_POST['cep']);
		$endereco = format($_POST['endereco']);
		$numero = $_POST['numero'];
		$complemento = format($_POST['complemento']);
		$bairro = format($_POST['bairro']);
		$cidade = format($_POST['cidade']);
		$estado = format($_POST['estado']);
		$tipo_endereco = format($_POST['tipo_endereco']);
		$referencia = format($_POST['referencia']);

		if(!empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($tipo_endereco)) {
			// if(empty($cod) && !$edit) {
			// $sql_cadastro = mysql_query("INSERT INTO clientes_enderecos (CE_CLIENTE, CE_NOME_DESTINATARIO, CE_CEP, CE_ENDERECO, CE_NUMERO, CE_COMPLEMENTO, CE_BAIRRO, CE_CIDADE, CE_ESTADO, CE_TIPO_ENDERECO, CE_PONTO_REFERENCIA) VALUES ('$cliente', '$destinatario', '$cep', '$endereco', '$numero', '$complemento', '$bairro', '$cidade', '$estado', '$tipo_endereco', '$referencia')");
			$sql_cadastro = sqlsrv_query($conexao_sankhya, "INSERT INTO clientes_enderecos (CE_CLIENTE, CE_CEP, CE_ENDERECO, CE_NUMERO, CE_COMPLEMENTO, CE_BAIRRO, CE_CIDADE, CE_ESTADO, CE_TIPO_ENDERECO, CE_PONTO_REFERENCIA) VALUES ('$cliente', '$cep', '$endereco', '$numero', '$complemento', '$bairro', '$cidade', '$estado', '$tipo_endereco', '$referencia')", $conexao_params, $conexao_options);
			if($sql_cadastro){
				$sucesso=true;
				$msg="Endereço cadastrado com sucesso!";
			}else{
				$msg="Não foi possível cadastrar o endereço!";
			}
			// } 
			// elseif(!empty($cod) && $edit) {
			// 	$sql_update = mysql_query("UPDATE clientes_enderecos SET CE_NOME_DESTINATARIO='$destinatario', CE_CEP='$cep', CE_ENDERECO='$endereco', CE_NUMERO='$numero', CE_COMPLEMENTO='$complemento', CE_BAIRRO='$bairro', CE_CIDADE='$cidade', CE_ESTADO='$estado', CE_TIPO_ENDERECO='$tipo_endereco', CE_PONTO_REFERENCIA='$referencia' WHERE CE_COD='$cod' LIMIT 1");
			// 	$_SESSION['endereco-entrega'] = $cod;
			// }
		}
		echo json_encode(array('sucesso'=>$sucesso,'msg'=>$msg));
	break;	
}
