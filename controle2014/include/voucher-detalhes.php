<?

define('PGINCLUDE', 'true');

//Verificamos o dominio
include("checkwww.php");

//Banco de dados
include("../conn/conn.php");

//Conexão com o banco de dados da Sankhya
include("../conn/conn-sankhya.php");

// Checar usuario logado
include("checklogado.php");

//Incluir funções básicas
include("funcoes.php");

//-----------------------------------------------------------------//

$evento = (int) $_SESSION['usuario-carnaval'];
$cod = (int) $_POST['cod'];
$cancelado = $_POST['cancelado'];

//-----------------------------------------------------------------//

$sucesso = false;
$dados = null;

if(!empty($cod)) {

	if($cancelado == 'false') {
		$loja_search = " AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' ";
		$item_search = " AND D_E_L_E_T_='0' ";
	}

	$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA, ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA, CONVERT(VARCHAR, l.LO_DEADLINE, 103) AS DATA_DEADLINE, CONVERT(CHAR, l.LO_CLI_DATA_ENTREGA, 103) AS DATA_PARA_ENTREGA FROM loja l WHERE l.LO_EVENTO='$evento' $loja_search AND l.LO_COD='$cod'", $conexao_params, $conexao_options);

	if(sqlsrv_num_rows($sql_loja) > 0) {

		$loja_qtde_folia = 0;
		$loja_qtde_frisa = 0;
		$loja_enable_frisa = false;

		$loja = sqlsrv_fetch_array($sql_loja);

		$loja_cod = $loja['LO_COD'];
		$loja_deadline = $loja['DATA_DEADLINE'];

		if(!empty($loja_deadline)) {
			$loja_deadline_n = $loja['LO_DEADLINE'];
			$loja_deadline_n = date('Y-m-d', strtotime($loja_deadline_n->format('Y-m-d')));

			$loja_deadline_class = ($loja_deadline_n < date('Y-m-d')) ? ' class="vencido"' : '';
			$loja_deadline_texto =  '<span'.$loja_deadline_class.'>Deadline: '.$loja_deadline.'</span>';

			#$loja_deadline_texto = '<span>Deadline: '.$loja_deadline.'</span>';
		}

		//Inserimos no html
		$dados = '<h3>Vch '.$loja_cod.$loja_deadline_texto.'</h3>';

		//Itens
		$sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_VALOR_TABELA, LI_INGRESSO, LI_VALOR_TRANSFER, LI_VALOR_ADICIONAIS, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' $item_search GROUP BY LI_INGRESSO, LI_VALOR, LI_VALOR_TABELA, LI_VALOR_TRANSFER, LI_VALOR_ADICIONAIS, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO", $conexao_params, $conexao_options);

		if(sqlsrv_num_rows($sql_item) > 0) {
			$i = 1;
			$item_count = 1;

			while ($item = sqlsrv_fetch_array($sql_item)) {

				$item_cod = $item['COD'];
				$item_qtde = $item['QTDE'];
				$item_ingresso = $item['LI_INGRESSO'];
				
				//Informações adicionais do item
				$sql_info_item = sqlsrv_query($conexao, "
				SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, CONVERT(VARCHAR, ed.ED_DATA, 103) AS DIA, DATEPART(WEEKDAY, ed.ED_DATA) AS SEMANA, tp.TI_NOME, tp.TI_TAG  
				FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
				WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);

				if(sqlsrv_num_rows($sql_info_item) > 0) {
					$info_item = sqlsrv_fetch_array($sql_info_item);
				
					$item_setor = utf8_encode($info_item['ES_NOME']);
					$item_dia = utf8_encode($info_item['ED_NOME']);
					$item_data = utf8_encode($info_item['DIA']);
					$item_data_n = $info_item['ED_DATA'];
					$item_semana = $semana[($info_item['SEMANA']-1)];
					$item_tipo = utf8_encode($info_item['TI_NOME']);
					$item_tipo_tag = $info_item['TI_TAG'];
					
					$item_fila = utf8_encode($info_item['VE_FILA']);
					$item_vaga = utf8_encode($info_item['VE_VAGAS']);
					$item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);

					$item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;

				}

				// loja_itens_adicionais
				if($item_fechado) $item_qtde = $item_qtde / $item_vaga;
				
				$item_tipo_texto = '';
				if (!$item_fechado){ 
					switch ($item_tipo_especifico) {
						case 'vaga':
							$item_tipo_texto .= $item_tipo_especifico;
							if ($item_qtde > 1){ $item_tipo_texto .= 's'; }
						break;
						case 'lugar':
							$item_tipo_texto .= $item_tipo_especifico;
							if ($item_qtde > 1){  $item_tipo_texto .= 'es'; }
						break;									
						case 'fechado':
							$item_tipo_texto .= $item_tipo_especifico;
						break;
						default:
							$item_tipo_texto .= 'vaga';
							if ($item_qtde > 1){  $item_tipo_texto .= 's'; }
						break;

					}							
				}
				
				$item_tipo_especifico_texto = '';
				if(!empty($item_tipo_especifico)) { $item_tipo_especifico_texto .= " ".$item_tipo_especifico; }
				if($item_fechado) { $item_tipo_especifico_texto .= " (".$item_vaga." vagas)"; }
			
				$item_fila_texto = !empty($item_fila) ? " ".$item_fila : ''; 
				
				//Inserimos no html
				$dados .= '<div class="detalhe-voucher-item">';
				$dados .= '<p>'.$item_qtde.' '.$item_tipo_texto.' - '.$item_tipo.$item_tipo_especifico_texto.' - Setor: '.$item_setor.$item_fila_texto.'</p>';
				$dados .= '<p>'.$item_dia.' dia - '.$item_semana.' - '.$item_data.'</p>';
				$dados .= '</div>';
					
			}		

		}

		$sucesso = true;

	}
}

echo json_encode(array('sucesso'=>$sucesso, 'deadline'=>$loja_deadline, 'dados'=>$dados));

//-----------------------------------------------------------------//

// include('include/footer.php');

//Fechar conexoes
include("../conn/close.php");
include("../conn/close-sankhya.php");

?>