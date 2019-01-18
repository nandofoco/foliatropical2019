<?

define('PGINCLUDE', 'true');


//Verificamos o dominio
include("checkwww.php");

//Banco de dados
include("../conn/conn.php");
include("../conn/conn-mssql.php");

// Checar usuario logado
include("checklogado.php");

// Checar usuario logado
include("language.php");

//Incluir funções básicas
include("funcoes.php");

//Incluir função para url amigável
include("toascii.php");

//Definir o carnaval ativo
include("setcarnaval.php");


header('Content-Type: text/html; charset=utf-8'); 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Expires: Fri, 1 Jan 2010 08:00:00 GMT"); // Date in the past 

$sucesso = false;

$tipo = (int) $_POST['tipo'];
$tipo_especial = format($_POST['tipo-especial']);
$setor = (int) $_POST['setor'];
$dia = (int) $_POST['dia'];

$evento = setcarnaval();

// $alterar = (bool) $_POST['alterar'];

//if(($dia != 1) && ($tipo == 4) && ($tipo_especial == 'folia-tropical')) unset($tipo, $setor, $dia, $evento);

if(!empty($tipo) && !empty($setor) && !empty($dia) && !empty($evento)) {

	switch ($tipo_especial) {
		case 'camarote':
			$search = " AND v.VE_TIPO_ESPECIFICO<>'fechado' ";
		break;
		case 'camarote-corporativo':
			$search = " AND v.VE_TIPO_ESPECIFICO='fechado' ";
		break;
		default:
			unset($search);
		break;
	}

	$top = ($tipo == 4) || ($tipo == 6) ? 'TOP(1)' : '';

	$sql_ingressos = sqlsrv_query($conexao, "SELECT $top
		v.*, t.TI_NOME 
		FROM vendas v, tipos t 
		WHERE v.VE_EVENTO='$evento' 
		AND v.VE_TIPO='$tipo' 
		AND v.VE_SETOR='$setor' 
		AND v.VE_DIA='$dia' 
		AND v.VE_TIPO=t.TI_COD 
		AND v.VE_BLOCK=0 
		AND v.VE_VALOR>0
		AND v.D_E_L_E_T_=0 
		AND t.D_E_L_E_T_=0
		/*AND ((v.VE_LOTE IS NOT NULL AND v.VE_LOTE_ATIVO = 1) OR v.VE_LOTE IS NULL)*/
		$search ORDER BY v.VE_COD DESC", $conexao_params, $conexao_options);

	$n_ingressos = sqlsrv_num_rows($sql_ingressos);


	if($n_ingressos > 0) {

		$sucesso = true;
		$ingressos_html;

		if($n_ingressos == 1) {
			$checked_class = 'checked';
			$checked_check = 'checked="checked"';
		}

		// Alterado
		$ingressos_html .= '<section class="radio verify">';

		while($ingressos = sqlsrv_fetch_array($sql_ingressos)) {


			$ingressos_cod = $ingressos['VE_COD'];
			$ingressos_estoque = $ingressos['VE_ESTOQUE'];

			$ingressos_tipo = ($ingressos['TI_NOME'] == 'Lounge') ? 'Folia Tropical' : utf8_encode($ingressos['TI_NOME']);
			//Inserir porcentagem parceiro
			$ingressos_valor = number_format($ingressos['VE_VALOR'],2,",",".");
			
			$ingressos_fila = utf8_encode($ingressos['VE_FILA']);
			$ingressos_vaga = utf8_encode($ingressos['VE_VAGAS']);
			$ingressos_tipo_especifico = utf8_encode($ingressos['VE_TIPO_ESPECIFICO']);
			
			$ingressos_lote = utf8_encode($ingressos['VE_LOTE']);

			$ingressos_extra="";

			if(!empty($ingressos_fila)) $ingressos_extra .= " ".$ingressos_fila;
			if(!empty($ingressos_tipo_especifico)) $ingressos_extra .= " ".$ingressos_tipo_especifico;
			
			// GAMBIARRA PRA QUE OS IMBECIS NAO COMPREM A FRISA ERRADA
			if(($ingressos_tipo == 'Frisa') && ($ingressos_tipo_especifico == 'vaga')) $ingressos_extra .= " por pessoa ";
			
			if(($ingressos_tipo == 'Camarote') && ($ingressos_tipo_especifico == 'vaga')) $ingressos_extra .= " - valor individual ";
			if(!empty($ingressos_vaga) && ($ingressos_tipo_especifico == 'fechado')) $ingressos_extra .= " (".$ingressos_vaga." vagas)";

			if(!empty($ingressos_lote)) $ingressos_extra .= "<br />(".$ingressos_lote."º Lote)";

			
			$disponibilidade = true;

			//Buscar estoque
			$sql_comprados = sqlsrv_query($conexao, "SELECT li.LI_INGRESSO, COUNT(li.LI_COD) as QTDE FROM loja_itens li, loja l WHERE li.LI_INGRESSO='$ingressos_cod' AND l.LO_COD=li.LI_COMPRA AND l.D_E_L_E_T_=0 AND li.D_E_L_E_T_=0 GROUP BY li.LI_INGRESSO", $conexao_params, $conexao_options);
			if(sqlsrv_num_rows($sql_comprados)) {
				$comprados = sqlsrv_fetch_array($sql_comprados);
				$ingressos_comprados = (int) $comprados['QTDE'];

				$disponibilidade = (($ingressos_estoque - $ingressos_comprados) > 0) ? true : false;
			}



			if($disponibilidade) {

				if($n_ingressos == 1) {
					$checked_class = 'checked';
					$checked_check = 'checked="checked"';
				}

				$ingressos_html .= 
				'<section class="item-compra '.$checked_class.'">
					<p>'.$ingressos_tipo.$ingressos_extra.'</p>
					<section class="checkbox verify preco">
					<p>R$ '.$ingressos_valor.'</p>
					<ul><li><label class="item '.$checked_class.'"><input type="checkbox" name="item[]" '.$checked_check.' value="'.$ingressos_cod.'" /></label></li></ul>
					</section>
				</section>';			
				
			} else {
				$n_ingressos--;
			}
			
			
		}

		$ingressos_html .= '</section>';
	}
}

echo json_encode(array('sucesso' => $sucesso, 'itens'=> $ingressos_html));

?>