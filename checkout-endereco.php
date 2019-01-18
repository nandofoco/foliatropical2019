<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

header("Content-type: application/json; charset=utf-8");

$tipo = format($_GET['t']);
$sucesso=false;
$msg="";

$cliente = (int) $_POST['cliente'];
$pais = format($_POST['pais']);
$cep = format($_POST['cep']);
$zipcode = format($_POST['zipcode']);
$endereco = format($_POST['endereco']);
$numero = $_POST['numero'];
$complemento = format($_POST['complemento']);
$bairro = format($_POST['bairro']);
$cidade = format($_POST['cidade']);
$estado = format($_POST['estado']);
$tipo_endereco = format($_POST['tipo_endereco']);
// $referencia = format($_POST['referencia']);
$cod= (int) $_POST['cod'];

if($pais!="BR"){
	$cep=$zipcode;
}

switch ($tipo) {

	case 'editar':

		if(!empty($pais) && (($pais=="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado))||($pais!="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($estado) && !empty($cidade)))) {
			$sql_cadastro = sqlsrv_query($conexao_sankhya, "UPDATE clientes_enderecos SET CE_CEP='$cep', CE_ENDERECO='$endereco', CE_NUMERO='$numero', CE_COMPLEMENTO='$complemento', CE_BAIRRO='$bairro', CE_CIDADE='$cidade', CE_ESTADO='$estado', CE_TIPO_ENDERECO='$tipo_endereco',CE_PAIS='$pais' WHERE CE_COD=$cod", $conexao_params, $conexao_options);

			if($sql_cadastro){
				$sucesso=true;
				// $msg="Endereço alterado com sucesso!";
				$_SESSION['ALERT'] = array('sucesso',$lg['endereco_alterado']);
			}else{
				// $msg="Não foi possível alterar o endereço!";
				$_SESSION['ALERT'] = array('erro',$lg['endereco_nao_alterado']);
			}
		}else{
			// $msg="Preencha todos os campos";
			$_SESSION['ALERT'] = array('erro',$lg['endereco_preencha_todos']);
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

		if(!empty($pais) && (($pais=="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado))||($pais!="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($estado) && !empty($cidade)))) {
			$sql_cadastro = sqlsrv_query($conexao_sankhya, "INSERT INTO clientes_enderecos (CE_CLIENTE, CE_CEP, CE_ENDERECO, CE_NUMERO, CE_COMPLEMENTO, CE_BAIRRO, CE_CIDADE, CE_ESTADO, CE_TIPO_ENDERECO,CE_PAIS) VALUES ('$cliente', '$cep', '$endereco', '$numero', '$complemento', '$bairro', '$cidade', '$estado', '$tipo_endereco','$pais')", $conexao_params, $conexao_options);
			if($sql_cadastro){
				$sucesso=true;
				// $msg="Endereço cadastrado com sucesso!";
				$_SESSION['ALERT'] = array('sucesso',$lg['endereco_cadastrado']);
			}else{
				// $msg="Não foi possível cadastrar o endereço!";
				$_SESSION['ALERT'] = array('erro',$lg['endereco_nao_alterado']);
			}
		}else{
			$_SESSION['ALERT'] = array('erro',$lg['endereco_preencha_todos']);
		}
		echo json_encode(array('sucesso'=>$sucesso,'msg'=>$msg));
	break;	
}
header("Location: ".$_SERVER['HTTP_REFERER']);
