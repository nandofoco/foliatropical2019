<?

//Banco de dados
include '../../conn/conn.php';
include '../../conn/conn-sankhya.php';
include BASE.'inc/funcoes.php';
include BASE.'inc/checklogado.php';

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

//-----------------------------------------------------------------------------//

$item = $_POST['item'];
$quantidade = $_POST['quantidade'];

//-----------------------------------------------------------------------------//

if(count($item) > 0) {

	//Valor dos itens
	foreach ($item as $k => $value) {

		// Valor do item
		$sql_ingressos = sqlsrv_query($conexao, "SELECT TOP 1
                VE_VALOR
            FROM
                vendas
            WHERE
                VE_COD='$value'
                -- AND VE_BLOCK=0
                AND D_E_L_E_T_=0",
            $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_ingressos) !== false) {
			$ingresso = sqlsrv_fetch_array($sql_ingressos);
			$valor[$value] = $ingresso['VE_VALOR'];
		}
	}
	
	//Adicionar à sessao
	$i = count($_SESSION['compra-site']);

	//Procuramos se ja existe cadastrada
	if($i > 0) {
		
		foreach ($item as $k => $value) {
			
			foreach ($_SESSION['compra-site'] as $key => $compra) {
				
				if(($compra['item'] == $value) && ($compra['valor'] == $valor[$value])) {

					$qtde = ((int) $quantidade[$key]) > 0 ? (int) $quantidade[$key] : 1;
					$_SESSION['compra-site'][$key]['qtde'] += $qtde;

					unset($item[$k], $valor[$value], $quantidade[$value]);
				}
			}
		}
    }
    
    //Se não existe adicionamos o item ao array
	if(count($item) > 0) {
		foreach ($item as $value) {

            $_SESSION['compra-site'][$i]['item'] = $value;
			$_SESSION['compra-site'][$i]['valor'] = $valor[$value];
			$_SESSION['compra-site'][$i]['qtde'] = 1;
			$i++;
		}
    }
    
	ksort($_SESSION['compra-site']);

	$_SESSION['ingresso-add'] = true;
	
    header("Location: ".$_SERVER['HTTP_REFERER']."");

	exit();
}

//-----------------------------------------------------------------------------//

$cod = (int) $_GET['c'];
$acao = $_GET['a'];

if(is_numeric($cod) && !empty($acao)) {

	switch ($acao) {
		case 'excluir':			
			unset($_SESSION['compra-site'][$cod]);
			sort($_SESSION['compra-site']);

            header("Location: ".$_SERVER['HTTP_REFERER']."");
            
			exit();

		break;

		case 'quantidade':
			$quantidade = (int) $_GET['quantidade'];
			if($quantidade > 0) {
				$_SESSION['compra-site'][$cod]['qtde'] = $quantidade;
			} else {
				unset($_SESSION['compra-site'][$cod]);
				sort($_SESSION['compra-site']);
			}
			echo json_encode(array('sucesso' => true));
			exit();
		break;
		
	}

}

//fechar conexao com o banco
include BASE."conn/close.php";
include BASE."conn/close-sankhya.php";

include BASE."inc/partials/head.php";

?>
<script type="text/javascript">
    swal({
        title: "Atenção",
        text: "Ocorreu um erro, tente novamente.",
        html: true,
        type: "error"
    }, function() {
        history.go(-1);
    });
</script>