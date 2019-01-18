<?

//Banco de dados
include '../../conn/conn.php';
include '../../conn/conn-sankhya.php';
include BASE.'inc/funcoes.php';
include BASE.'inc/checklogado.php';

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

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
$cod= (int) $_POST['cod'];

if($pais!="BR") $cep=$zipcode;

switch ($tipo) {

	case 'editar':

		if(!empty($pais) && (($pais=="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado))||($pais!="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($estado) && !empty($cidade)))) {
			$sql_cadastro = sqlsrv_query($conexao_sankhya, "UPDATE 
                    clientes_enderecos
                SET
                    CE_CEP='$cep',
                    CE_ENDERECO='$endereco',
                    CE_NUMERO='$numero',
                    CE_COMPLEMENTO='$complemento',
                    CE_BAIRRO='$bairro',
                    CE_CIDADE='$cidade',
                    CE_ESTADO='$estado',
                    CE_TIPO_ENDERECO='$tipo_endereco',
                    CE_PAIS='$pais'
                WHERE
                    CE_COD=$cod",
            $conexao_params, $conexao_options);

			if($sql_cadastro){
				$sucesso=true;
				$_SESSION['ALERT'] = array('sucesso', 'Endereço alterado com sucesso!');
			}else{
				$_SESSION['ALERT'] = array('erro', 'Não foi possível alterar o endereço!');
            }
            
		}else{
			$_SESSION['ALERT'] = array('erro', 'Preencha todos os campos');
		}
		echo json_encode(array('sucesso'=>$sucesso,'msg'=>$msg));

	break;

	case 'consultar':
		$cliente = (int) $_POST['cliente'];
		$sql_enderecos = sqlsrv_query($conexao_sankhya, "SELECT * 
            FROM
                clientes_enderecos
            WHERE
                CE_CLIENTE=$cliente
                AND CE_BLOCK=0
                AND D_E_L_E_T_=0
            ORDER BY
                CE_ULTIMA_ENTREGA DESC",
        $conexao_params, $conexao_options);

		$enderecos=array();
		$sucesso=true;
		while ($linha = sqlsrv_fetch_array($sql_enderecos)) {
			array_push($enderecos, $linha);
		}
		utf8_encode_deep($enderecos);
        echo json_encode(array('sucesso'=>$sucesso,'msg'=>$msg,'enderecos'=>$enderecos));
        
	break;
	
	case 'cadastrar':

		if(!empty($pais) && (($pais=="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($bairro) && !empty($cidade) && !empty($estado))||($pais!="BR" && !empty($cep) && !empty($endereco) && !empty($numero) && !empty($estado) && !empty($cidade)))) {
			$sql_cadastro = sqlsrv_query($conexao_sankhya, "INSERT INTO
                    clientes_enderecos
                (
                    CE_CLIENTE,
                    CE_CEP,
                    CE_ENDERECO,
                    CE_NUMERO,
                    CE_COMPLEMENTO,
                    CE_BAIRRO,
                    CE_CIDADE,
                    CE_ESTADO,
                    CE_TIPO_ENDERECO,
                    CE_PAIS
                ) VALUES (
                    '$cliente',
                    '$cep',
                    '$endereco',
                    '$numero',
                    '$complemento',
                    '$bairro',
                    '$cidade',
                    '$estado',
                    '$tipo_endereco',
                    '$pais')",
            $conexao_params, $conexao_options);

			if($sql_cadastro){
				$sucesso=true;
				$_SESSION['ALERT'] = array('sucesso', 'Endereço cadastrado com sucesso!');
			}else{
				$_SESSION['ALERT'] = array('erro', 'Não foi possível cadastrar o endereço!');
			}
		}else{
			$_SESSION['ALERT'] = array('erro', 'Preencha todos os campos');
		}
		echo json_encode(array('sucesso'=>$sucesso,'msg'=>$msg));
	break;	
}
header("Location: ".$_SERVER['HTTP_REFERER']);
