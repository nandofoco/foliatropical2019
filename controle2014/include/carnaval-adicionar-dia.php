<?
session_start();

include("../conn/conn.php");

header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$sucesso = false;

$dia = $_POST['dia'];
$ano = $_POST['ano'];

function ordena($a, $b) {
  return $a['data'] - $b['data'];
}

function monta_lista($dados) {

	global $conexao, $conexao_params, $conexao_options;
	
	$semana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');

	if(count($dados) > 0) {
		usort($dados, 'ordena');
		$lista = "";
		$escolas = "";
		foreach ($dados as $key => $value) {

			$classe = "Remover";
			//buscar atracoes
			$busca_cod = $value['cod'];
			$sql_atracoes = sqlsrv_query($conexao, "SELECT TOP 1 ED_ATRACOES FROM eventos_dias WHERE ED_COD='$busca_cod'", $conexao_params, $conexao_options);
			$nat = sqlsrv_num_rows($sql_atracoes);
			
			if($nat > 0) {
				$at = sqlsrv_fetch_array($sql_atracoes);
				$atracoes = utf8_encode($at['ED_ATRACOES']);
				$classe = "Editar";
			}

			$lista .= '<li><a href="'.$key.'" class="'.strtolower($classe).'" title="'.$classe.' Dia"><h1>'.($key+1).'&ordm;</h1><h2>'.$semana[date('w',$value['data'])].'</h2><small>'.date('d/m/Y',$value['data']).'</small><span></span></a><div id="form-edit-'.$key.'" class="form-edit"><input type="text" name="editar-data['.$key.']" class="input editar" value="'.date('d/m',$value['data']).'" /><a href="'.$key.'" class="edit-data"></a></div></li>';
			$escolas .= '<li><h4>'.($key+1).'&ordm; dia</h4><p class="coluna"><label for="carnaval-escolas-dia-'.$key.'" class="infield">Ex. Mangueira, Salgueiro, Beija-Flor, Mocidade</label><input type="text" name="escola-dia['.$key.']" class="input" value="'.$atracoes.'" id="carnaval-escolas-dia-'.$key.'" /></p><div class="clear"></div></li>';
			unset($atracoes);
		}
		usort($_SESSION['carnaval-dias'], 'ordena');
		$sucesso = true;
	} else {
		$sucesso = false;
	}
	echo json_encode(array("sucesso"=>$sucesso, "lista"=>$lista, "escolas"=>$escolas));
}

function edit_lista($dados) {

	global $conexao, $conexao_params, $conexao_options;

	$semana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');

	if(count($dados) > 0) {
		usort($dados, 'ordena');
		$lista = "";
		$escolas = "";
		foreach ($dados as $key => $value) {

			$classe = "Remover";
			//buscar atracoes
			$busca_cod = $value['cod'];
			$sql_atracoes = sqlsrv_query($conexao, "SELECT TOP 1 * FROM eventos_dias WHERE ED_COD='$busca_cod'", $conexao_params, $conexao_options);
			$nat = sqlsrv_num_rows($sql_atracoes);

			// if($nat == 0) {
			// 	$busca_dia = $datacod;
			// 	$sql_atracoes = sqlsrv_query($conexao, "SELECT TOP 1 * FROM eventos_dias WHERE ED_COD='$busca_dia'", $conexao_params, $conexao_options);
			// 	$nat = sqlsrv_num_rows($sql_atracoes);
			// }
			
			if($nat > 0) {
				$at = sqlsrv_fetch_array($sql_atracoes);
				$atracoes = utf8_encode($at['ED_ATRACOES']);
				$classe = "Editar";
			}

			$lista .= '<li><a href="'.$key.'" class="'.strtolower($classe).'" title="'.$classe.' Dia"><h1>'.($key+1).'&ordm;</h1><h2>'.$semana[date('w',$value['data'])].'</h2><small>'.date('d/m/Y',$value['data']).'</small><span></span></a><div id="form-edit-'.$key.'" class="form-edit"><input type="text" name="editar-data['.$key.']" class="input editar" value="'.date('d/m',$value['data']).'" /><a href="'.$key.'" class="edit-data"></a></div></li>';
			$escolas .= '<li><h4>'.($key+1).'&ordm; dia</h4><p class="coluna"><label for="carnaval-escolas-dia-'.$key.'" class="infield">Ex. Mangueira, Salgueiro, Beija-Flor, Mocidade</label><input type="text" name="escola-dia['.$key.']" class="input" value="'.$atracoes.'" id="carnaval-escolas-dia-'.$key.'" /></p><div class="clear"></div></li>';
			unset($atracoes);
		}
		usort($_SESSION['carnaval-dias'], 'ordena');
		$sucesso = true;
	} else {
		$sucesso = false;
	}
	echo json_encode(array("sucesso"=>$sucesso, "lista"=>$lista, "escolas"=>$escolas));
}

if(!empty($dia) && !empty($ano)) {

	$timesplit = explode('/', $dia);
	$timestamp = $ano.'-'.end($timesplit).'-'.reset($timesplit);
	$data = strtotime($timestamp);

	//Tratar array
	if(!empty($data)) {
		$i = count($_SESSION['carnaval-dias']);
		$insere = true;
		//Adicionar ao array se não existir a data cadastrada
		if(count($_SESSION['carnaval-dias']) > 0) {
			foreach ($_SESSION['carnaval-dias'] as $key => $value) {
				if ($value['data'] == $data) {
					$insere = false;
					break;
				}
		   }
		}
		
		if($insere) $_SESSION['carnaval-dias'][$i]['data'] = $data;
				
	}
	monta_lista($_SESSION['carnaval-dias']);
}

if($_POST['key'] != "") {
	unset($_SESSION['carnaval-dias'][(int)$_POST['key']]);
	usort($_SESSION['carnaval-dias'], 'ordena');

	monta_lista($_SESSION['carnaval-dias']);
}

if($_POST['limpar']) { 
	unset($_SESSION['carnaval-dias']);
	monta_lista($_SESSION['carnaval-dias']);
}

if($_POST['edit'] && ($_POST['editdata'] != "") && ($_POST['editkey'] != "") && !empty($_POST['editano'])) {

	$timesplit = explode('/', $_POST['editdata']);
	$timestamp = $_POST['editano'].'-'.end($timesplit).'-'.reset($timesplit);
	$data = strtotime($timestamp);

	//Tratar array
	if(!empty($data)) {
		$i = count($_SESSION['carnaval-dias']);
		$altera = true;		
		//Adicionar ao array se não existir a data cadastrada
		if(count($_SESSION['carnaval-dias']) > 0) {
			foreach ($_SESSION['carnaval-dias'] as $key => $value) {
				if ($value['data'] == $data) {
					$altera = false;
					break;
				}
		    }
		}
		
		if($altera) {
			$_SESSION['carnaval-dias'][$_POST['editkey']]['data'] = $data;

			edit_lista($_SESSION['carnaval-dias']);
		} else {
			echo json_encode(array("sucesso"=>false));
		}				
	}	
}

//Fechar conexoes
include("../conn/close.php");

?>