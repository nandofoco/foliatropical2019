<?

//Incluir funções básicas
include("include/includes.php");

//Conexão com o banco de dados do sqlserver
include("conn/conn-mssql.php");

//Conexão com o banco de dados da Sankhya
include("conn/conn-sankhya.php");

//Definir o carnaval ativo
include("include/setcarnaval.php");

unset($_SESSION['roteiro-itens']);

//-----------------------------------------------------------------//
// echo var_dump($_POST);
//arquivos de layout
include("include/head.php");
include("include/header.php");

//-----------------------------------------------------------------//

$evento = setcarnaval();
$cod = (int) $_GET['c'];
$usuario_cod = $_SESSION['usuario-cod'];

//busca paises
$sql_paises= sqlsrv_query($conexao_sankhya, "SELECT * FROM pais", $conexao_params, $conexao_options);
$paises=array();
while($linha = sqlsrv_fetch_array($sql_paises)){
	array_push($paises, $linha);
}

//busca das informações do cliente
$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 TIPPESSOA,AD_IDENTIFICACAO,CGC_CPF,PAIS_SIGLA,DTNASC,DDI,DDD,TELEFONE FROM TGFPAR WHERE CODPARC='$usuario_cod'", $conexao_params, $conexao_options);
$ar_cliente = sqlsrv_fetch_array($sql_cliente);

//verifica se tem as informações básicas para continuar (telefone,data de nascimento, cpf/cnpj, passaporte)

$cliente_data_nascimento = $ar_cliente['DTNASC'];
$cliente_ddi = trim($ar_cliente['DDI']);
$cliente_ddd = trim($ar_cliente['DDD']);
$cliente_telefone = trim($ar_cliente['TELEFONE']);
$cliente_pessoa = utf8_encode(trim($ar_cliente['TIPPESSOA']));
$cliente_cpf_cnpj = trim($ar_cliente['CGC_CPF']);
$cliente_passaporte = trim($ar_cliente['AD_IDENTIFICACAO']);
$cliente_pais = trim($ar_cliente['PAIS_SIGLA']);

/*echo var_dump(empty($cliente_data_nascimento))."</br>";
echo $cliente_ddd."</br>";
echo $cliente_telefone."</br>";
echo $cliente_pessoa."</br>";
echo $cliente_cpf_cnpj."</br>";
echo $cliente_passaporte."</br>";
echo $cliente_pais."</br>";
echo "</br>";
echo "</br>";
echo "</br>";

echo var_dump(!empty($cliente_data_nascimento)&&!empty($cliente_ddd)&&!empty($cliente_telefone)&&($cliente_pais == 'BR'&&(!empty($cliente_cpf_cnpj)&&($cliente_pessoa=="F"&&validaCPF($cliente_cpf_cnpj))||($cliente_pessoa=="J"&&validaCNPJ($cliente_cpf_cnpj))))||($cliente_pais != 'BR' && !empty($cliente_passaporte)));
exit();*/

if(!empty($cliente_data_nascimento)&&!empty($cliente_ddi)&&!empty($cliente_ddd)&&!empty($cliente_telefone)&&($cliente_pais == 'BR'&&(!empty($cliente_cpf_cnpj)&&($cliente_pessoa=="F"&&validaCPF($cliente_cpf_cnpj))||($cliente_pessoa=="J"&&validaCNPJ($cliente_cpf_cnpj))))||($cliente_pais != 'BR' && !empty($cliente_passaporte))){
		//continuar
}else{
	$_SESSION['ALERT'] = array('aviso','Sua informação de Data de Nascimento,CPF,CNPJ,(DDD)Telefone ou Passaporte está vazia ou é inválida. Complete suas informações!');
	header("Location: ".$_SERVER['HTTP_REFERER']);
	exit();
}

// if(!empty($cliente_passaporte)) $session_language = 'US';
$session_language = ($cliente_pais!="BR") ? 'US' : 'BR';

//-----------------------------------------------------------------//
$parcelas = array(3);
// $sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA,ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod' AND l.LO_CLIENTE='$usuario_cod' AND l.LO_PAGO=0", $conexao_params, $conexao_options);

$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA,ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod' AND l.LO_CLIENTE='$usuario_cod' AND l.LO_PAGO=0", $conexao_params, $conexao_options);
if(sqlsrv_num_rows($sql_loja) > 0) {
    $loja = sqlsrv_fetch_array($sql_loja);
    $loja_cod = $loja['LO_COD'];
    $loja_cliente = $loja['LO_CLIENTE'];
    $loja_parceiro = $loja['LO_PARCEIRO'];
    $loja_desconto = (bool) $loja['LO_DESCONTO'];
    $sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, TELEFONE, EMAIL, CGC_CPF FROM TGFPAR WHERE CODPARC='$loja_cliente' AND CLIENTE='S' AND BLOQUEAR='N' ORDER BY NOMEPARC ASC", $conexao_params, $conexao_options);
    if(sqlsrv_num_rows($sql_cliente) > 0) $loja_cliente_ar = sqlsrv_fetch_array($sql_cliente);
    $loja_nome = utf8_encode(trim($loja_cliente_ar['NOMEPARC']));
    $loja_telefone = utf8_encode(trim($loja_cliente_ar['TELEFONE']));
    $loja_email = utf8_encode(trim($loja_cliente_ar['EMAIL']));
    $loja_cpf_cnpj = utf8_encode(trim($loja_cliente_ar['CGC_CPF']));
    $loja_valor_total = $loja['LO_VALOR_TOTAL'];
    $loja_valor_ingressos = $loja['LO_VALOR_INGRESSOS'];
    $loja_valor_adicionais = $loja['LO_VALOR_ADICIONAIS'];
    $loja_valor_total_f = number_format($loja['LO_VALOR_TOTAL'], 2, ',','.');
    $loja_comissao_retida = (bool) $loja['LO_COMISSAO_RETIDA'];
    $loja_comissao_paga = (bool) $loja['LO_COMISSAO_PAGA'];
    $loja_vendedor = (empty($loja['LO_VENDEDOR']) || $loja['LO_VENDEDOR'] == 0) ? false : true;
    $loja_data = $loja['LO_DATA_COMPRA'];
    //delivery
    $del_ar = array();
    $vendas_adicionais_delivery['valor'] = '0.00';
    //Array de produtos da Cielo
    $produtos = array();
    $item_count = 1;
    
    $sql_itens = sqlsrv_query($conexao, "SELECT li.*, v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, es.ES_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG FROM loja_itens li, vendas v, eventos_setores es, eventos_dias ed, tipos tp WHERE li.LI_COMPRA='$loja_cod' AND li.LI_INGRESSO=v.VE_COD AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD AND li.D_E_L_E_T_='0' ORDER BY LI_COD ASC", $conexao_params, $conexao_options);
    if(sqlsrv_num_rows($sql_itens) > 0) {
        $i = 1;
        while ($item = sqlsrv_fetch_array($sql_itens)) {
            $item_cod = $item['LI_COD'];
            $item_id = $item['LI_ID'];
            $item_nome = utf8_encode($item['LI_NOME']);
            $item_tipo = utf8_encode($item['TI_NOME']);
            $item_tipo_tag = $item['TI_TAG'];
            $item_dia = utf8_encode($item['dia']);
            $item_setor = $item['ES_NOME'];
            $item_valor = number_format($item['LI_VALOR'], 2, ",", ".");
            $item_data_n = $item['ED_DATA'];
            $item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));
            $item_fila = utf8_encode($item['VE_FILA']);
            $item_vaga = utf8_encode($item['VE_VAGAS']);
            $item_tipo_especifico = utf8_encode($item['VE_TIPO_ESPECIFICO']);
            $item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;
            if(!$item_fechado) $item_count = 1;
            switch($item_tipo_tag) {
                case 'lounge':
                    if(in_array($item_data_n, $dias_candybox)) array_push($parcelas, 3);
                    else array_push($parcelas, 10);
                break;
                case 'arquibancada':
                    array_push($parcelas, 6);
                break;
                case 'frisa':
                    array_push($parcelas, 10);
                break;
                case 'camarote':
                    array_push($parcelas, 10);
                break;
            }
            $i++;
            if($item_fechado) $item_count++;
        }
    }
    //Verificar a existencia de cupom de desconto para essa compra
    $sql_exist_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$loja_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
    if(sqlsrv_num_rows($sql_exist_cupom) > 0) {
        $cupom_utilizado = true;
        $cupom = sqlsrv_fetch_array($sql_exist_cupom);
        $cupom_cod = $cupom['CP_COD'];
        $cupom_nome = utf8_encode($cupom['CP_NOME']);
        $cupom_codigo = $cupom['CP_CUPOM'];
        $cupom_valor = $cupom['CP_DESCONTO'];
        $cupom_tipo = $cupom['CP_TIPO'];

        switch ($cupom_tipo) {
            case 1:
                /*$loja_valor_ingressos = $loja_valor_ingressos - (($cupom_valor * $loja_valor_ingressos) / 100);*/
                $cupom_valor_desconto = (($cupom_valor * $loja_valor_ingressos) / 100);
            break;
            
            case 2:
                if($loja_valor_ingressos >= $cupom_valor) $cupom_valor_desconto = $cupom_valor; /*$loja_valor_ingressos = $loja_valor_ingressos - $cupom_valor;*/
                else unset($_SESSION['compra-cupom'], $cupom_cod);
            break;
        }
    } else {
        //Verificar a existencia de cupom de desconto
        if($_SESSION['compra-cupom']['usuario'] == $loja_cliente) {
            
            $cupom_cod = $_SESSION['compra-cupom']['cod'];
            $cupom_delete = true;
            $sql_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COD='$cupom_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='0' AND CP_DATA_VALIDADE >= GETDATE() ", $conexao_params, $conexao_options);
            $n_cupom = sqlsrv_num_rows($sql_cupom);
            if($n_cupom > 0) {
                $cupom = sqlsrv_fetch_array($sql_cupom);
                $cupom_cod = $cupom['CP_COD'];
                $cupom_nome = utf8_encode($cupom['CP_NOME']);
                $cupom_codigo = $cupom['CP_CUPOM'];
                $cupom_valor = $cupom['CP_DESCONTO'];
                $cupom_tipo = $cupom['CP_TIPO'];
                // 1 Porcentagem
                // 2 Valor
                $_SESSION['compra-cupom']['usuario'] = $loja_cliente;
                $_SESSION['compra-cupom']['cod'] = $cupom_cod;
                $_SESSION['compra-cupom']['compra'] = $cod;
                switch ($cupom_tipo) {
                    case 1:
                        /*$loja_valor_ingressos = $loja_valor_ingressos - (($cupom_valor * $loja_valor_ingressos) / 100);*/
                        $cupom_valor_desconto = (($cupom_valor * $loja_valor_ingressos) / 100);
                    break;
                    
                    case 2:
                        if($loja_valor_ingressos >= $cupom_valor) $cupom_valor_desconto = $cupom_valor; /*$loja_valor_ingressos = $loja_valor_ingressos - $cupom_valor;*/
                        else unset($_SESSION['compra-cupom'], $cupom_cod);
                    break;
                }
                $loja_valor_total = $loja_valor_ingressos + $loja_valor_adicionais;
                //Total formatado
                $loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');
            }
        }
    }
    //Criar um código unico de produto já que temos 2 tabelas e a Cielo não permite enviar uma observação
    //Verificar existencia
    $sql_exist_cod = sqlsrv_query($conexao, "SELECT * FROM loja_modalidade WHERE LM_COMPRA='$loja_cod' AND LM_MODALIDADE='carnaval' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);
    if(sqlsrv_num_rows($sql_exist_cod) > 0) {
        $ar_order_number = sqlsrv_fetch_array($sql_exist_cod);
        $order_number = $ar_order_number['LM_COD'];
    } else {
        //Inserir
        $sql_insert_cod = sqlsrv_query($conexao, "INSERT INTO loja_modalidade (LM_COMPRA, LM_MODALIDADE, LM_DATA) VALUES ('$loja_cod', 'carnaval', GETDATE())", $conexao_params, $conexao_options);
        $order_number = getLastId();
    }
    //$ordernumber carnaval ou rockinrio
    
    //Buscar nome do cliente
    $valor = 0.00;
    $valor_final = 0.00;
    $valor_ingressos = 0.00;
    $valor_ingressos_base = 0.00;
    $valor_adicionais = 0.00;
    $valor_desconto = 0.00;
    $valor_transfer = 0.00;
    $valor_over_interno = 0.00;
    $valor_over_externo = 0.00;
    $loja_qtde_folia = 0;
    $loja_qtde_frisa = 0;
    //Novos combos
    $loja_qtde_combo = array();
    //-----------------------------------------------------------------------------//
    $sql_item = sqlsrv_query($conexao, " SELECT COUNT(LI_COD) AS QTDE, LI_VALOR, LI_INGRESSO, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO, MIN(LI_COD) AS COD, MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE, MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND D_E_L_E_T_='0' GROUP BY LI_INGRESSO, LI_VALOR, LI_DESCONTO, LI_OVER_INTERNO, LI_OVER_EXTERNO", $conexao_params, $conexao_options);
    
    if(sqlsrv_num_rows($sql_item) > 0) {
        
        $i = 1;
        $item_count = 1;
        while ($item = sqlsrv_fetch_array($sql_item)) {
            // $item_id = $item['LI_ID'];
            // $item_nome = utf8_encode($item['LI_NOME']);
            $item_cod = $item['COD'];
            $item_qtde = $item['QTDE'];
            $item_ingresso = $item['LI_INGRESSO'];
            $item_valor =  $item['LI_VALOR'];
            $item_desconto =  $item['LI_DESCONTO'];
            $item_overinterno =  $item['LI_OVER_INTERNO'];
            $item_overexterno =  $item['LI_OVER_EXTERNO'];
            $item_exclusividade = (bool) $item['EXCLUSIVIDADE'];
            $item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];
            //for ($iitem=1; $iitem <=$carrinho['qtde'] ; $iitem++) {
        
            
            //Procurar o overpricing
            $item_valor_tabela = 0.00;
            $item_valor_adicionais = 0.00;
            $item_valor_transfer = 0.00;
            $item_vagas = 1;
            //Informações adicionais do item
            $sql_info_item = sqlsrv_query($conexao, "
            SELECT v.VE_DIA, v.VE_SETOR, v.VE_FILA, v.VE_VAGAS, v.VE_TIPO_ESPECIFICO, v.VE_VALOR_EXCLUSIVIDADE, es.ES_NOME, ed.ED_NOME, ed.ED_DATA, SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia, tp.TI_NOME, tp.TI_TAG 
            FROM vendas v, eventos_setores es, eventos_dias ed, tipos tp 
            WHERE v.VE_COD='$item_ingresso' AND es.ES_COD=v.VE_SETOR AND ed.ED_COD=v.VE_DIA AND v.VE_TIPO=tp.TI_COD", $conexao_params, $conexao_options);
            if(sqlsrv_num_rows($sql_info_item) > 0) {
                $info_item = sqlsrv_fetch_array($sql_info_item);
                $item_setor = utf8_encode($info_item['ES_NOME']);
                $item_dia = utf8_encode($info_item['ED_NOME']);
                $item_data = utf8_encode($info_item['dia']);
                $item_data_n = $info_item['ED_DATA'];
                $item_tipo = utf8_encode($info_item['TI_NOME']);
                $item_tipo_tag = $info_item['TI_TAG'];
                
                $item_fila = utf8_encode($info_item['VE_FILA']);
                $item_vaga = utf8_encode($info_item['VE_VAGAS']);
                $item_tipo_especifico = utf8_encode($info_item['VE_TIPO_ESPECIFICO']);
                $item_valor_exclusividade = $info_item['VE_VALOR_EXCLUSIVIDADE'];
                $item_fechado = (($item_vaga > 0) && ($item_tipo_especifico == 'fechado')) ? true : false;
				if($item_fechado) {
					$item_vagas = utf8_encode($info_item['VE_VAGAS']);
					$item_valor = $item_valor/$item_vagas;
				}
				
				//-----------------------------------------------------------------------------//
			
            	$item_valores = $item_valor * $item_qtde;
            	$valor_ingressos += $item_valores;
            
            //-----------------------------------------------------------------------------//

                $item_data_n = (string) date('Y-m-d', strtotime($item_data_n->format('Y-m-d')));
                if(($item_tipo_tag == 'lounge')) {
                    if($loja_cod <= $combo_dias_limite) {
                        if(in_array($item_data_n, $dias_principais)){
                            //Adicionamos na quantidade e excluimos do array
                            $loja_qtde_folia++;
                            foreach ($dias_principais as $key_dia => $item_dia_atual) {
                                if ($item_dia_atual == $item_data_n) unset($dias_principais[$key_dia]);
                            }
                        }
                        
                    } else {
                        //loja_qtde_combo
                        if(count($combo_dias) > 0) {
                            // Limite
                            $loja_data_limite = (string) date('Y-m-d', strtotime($loja_data->format('Y-m-d')));
                            foreach ($combo_dias as $k => $c) {
                                //Verificar cada ocorrencia
                                // if(in_array($item_data_n, $c['dias'])) {
                                // Modificacao por causa da data de compra
                                if(in_array($item_data_n, $c['dias']) && ($loja_data_limite >= $c['limite'][0]) && ($loja_data_limite <= $c['limite'][1])) {
                                    $loja_qtde_combo[$k] = 1 + ((int) $loja_qtde_combo[$k]);
                                    //Retiramos do combo o valor encontrado
                                    foreach ($c['dias'] as $kd => $ingressos_dia_atual) {
                                        if ($ingressos_dia_atual == $item_data_n) unset($combo_dias[$k]['dias'][$kd]);
                                    }
                                }                                   
                            }
                        }
                        
                    }
                }
                $produto_nome = $item_tipo;
                if(!empty($item_fila)) { $produto_nome .= " ".$item_fila; }
                if(!empty($item_tipo_especifico)) { $produto_nome.= " ".$item_tipo_especifico; }
                if($item_fechado) { $produto_nome .= " (".$item_vaga." vagas)"; }
                $produto_descricao = $produto_nome ." - ".$item_dia." dia - Setor: ".$item_setor;
            }
            $excl = ($item_exclusividade) ? true : false;
            for ($iitemvaga=1; $iitemvaga <= $item_vagas; $iitemvaga++) { 
                $item_id = ($item_vagas > 1) ? $iitemvaga : $iitem;
                //$valor_desconto += $item_desconto;
                $valor_over_interno += $item_overinterno;
                $valor_over_externo += $item_overexterno;
                $excl = ($excl == true) ? 1 : 0;
                
                //Adicionar valor exclusividade
                if(($iitemvaga == 1) && $excl) {
                    $valor_adicionais += $item_valor_exclusividade;
                    $item_valor_adicionais += $item_valor_exclusividade;
                }
                //-----------------------------------------------------------------------------//
                // $sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.* FROM loja_itens_adicionais lia, vendas_adicionais v WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$cod' AND lia.LIA_ITEM='$item_cod'", $conexao_params, $conexao_options);
                $sql_adicionais = sqlsrv_query($conexao, "SELECT lia.LIA_COD, v.*, vv.*
                    FROM loja_itens_adicionais lia, vendas_adicionais v, vendas_adicionais_valores vv 
                    WHERE v.VA_COD=lia.LIA_ADICIONAL AND lia.LIA_COMPRA='$loja_cod' AND vv.VAV_ADICIONAL=v.VA_COD 
                    AND lia.LIA_ITEM IN (SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO='$item_ingresso' AND D_E_L_E_T_='0')
                    AND lia.D_E_L_E_T_='0' AND vv.VAV_BLOCK=0 AND vv.D_E_L_E_T_=0 AND v.VA_BLOCK=0 AND v.D_E_L_E_T_=0
                    ORDER BY vv.VAV_INCLUSO DESC
                    ", $conexao_params, $conexao_options);
                if(sqlsrv_num_rows($sql_adicionais) !== false) {
                    while ($vendas_adicionais = sqlsrv_fetch_array($sql_adicionais)) {
                        $vendas_adicionais_cod = $vendas_adicionais['VA_COD'];
                        $vendas_adicionais_tipo = $vendas_adicionais['VA_TIPO'];
                        $vendas_adicionais_label = utf8_encode($vendas_adicionais['VA_LABEL']);
                        $vendas_adicionais_nome_exibicao = $vendas_adicionais['VA_NOME_EXIBICAO'];
                        $vendas_adicionais_nome_insercao = $vendas_adicionais['VA_NOME_INSERCAO'];
                        $vendas_adicionais_multi = (bool) $vendas_adicionais['VA_VALOR_MULTI'];
                        $vendas_adicionais_opcoes_cod = $vendas_adicionais['VAV_COD'];
                        $vendas_adicionais_opcoes_valor = $vendas_adicionais['VAV_VALOR'];
                        $vendas_adicionais_opcoes_incluso = (bool) $vendas_adicionais['VAV_INCLUSO'];
                        $vendas_adicionais_opcoes_incluso_int = $vendas_adicionais_opcoes_incluso ? 1 : 0;
                        if($vendas_adicionais_opcoes_incluso) $vendas_adicionais_opcoes_incluso_ar[$item_cod][$vendas_adicionais_nome_exibicao] = true;
                        if($vendas_adicionais_nome_exibicao == 'delivery'){
                            if(!$vendas_adicionais_delivery['incluso'] || $vendas_adicionais_opcoes_incluso || ($vendas_adicionais_opcoes_valor > $vendas_adicionais_delivery['valor'])){
                                $delivery = true;
                                $vendas_adicionais_delivery['incluso'] = ($vendas_adicionais_opcoes_incluso) ? 1 : 0;
                                $vendas_adicionais_delivery['cod'] = $vendas_adicionais_cod;
                                $vendas_adicionais_delivery['label'] = $vendas_adicionais_label;
                                $vendas_adicionais_delivery['valor'] = $vendas_adicionais_opcoes_valor;
                            }
                        } else {
                            $adicional_enable = true;
                            //Limitamos o transfer
                            if(!$vendas_adicionais_multi && ($item_ingresso == $item_anterior)) $adicional_enable = false;
                            
                            if(!$vendas_adicionais_opcoes_incluso_ar[$item_cod][$vendas_adicionais_nome_exibicao] && $adicional_enable) {
                                if($vendas_adicionais_nome_exibicao == 'transfer') $item_valor_adicionais = $vendas_adicionais_opcoes_valor;
                                else $item_valor_adicionais += $vendas_adicionais_opcoes_valor;
                                $valor_adicionais += $vendas_adicionais_opcoes_valor;
                                
                            }                                       
                        }
                    }
                }
                #$valor_transfer += $item_valor_transfer;
                //-----------------------------------------------------------------------------//
                
                //Atualizar
                $item_anterior = $item_ingresso;
            } //for iitemvaga
            //-----------------------------------------------------------------------------//
            
            $item_total_valores = $item_valores + $item_valor_adicionais;
            $valor_final += ($item_total_valores);
            $produto_valor_unitario = number_format(($item_total_valores / $item_qtde), 2, '', '');
            $i++;
        }
    }
    
    if($delivery) {
        if(!$vendas_adicionais_delivery['incluso']) $valor_adicionais += $vendas_adicionais_delivery['valor'];
    }               
    
    //$valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;
    $valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;
    $desconto = 0;      
    if($cupom_valor_desconto > 0) $desconto = 1;
    $loja_combo_desconto = 0;
    if($loja_parceiro == 54) {
        foreach ($loja_qtde_combo as $k => $r) {
            if(($r == $combo_dias[$k]['total']) && ($combo_dias[$k]['desconto'] > $loja_combo_desconto)) {
                $loja_combo_desconto = $combo_dias[$k]['desconto'];
                $loja_combo_nome = $combo_dias[$k]['nome'];
            }
        }
        if($loja_combo_desconto > 0) {
            $desconto = 1;
            $desconto_especial_folia = ($loja_combo_desconto * $valor) / 100;
            // $valor = $valor - $desconto_especial_folia;
        }
        
    }
    
    if($loja_qtde_frisa > 0) {
        $desconto = 1;
        // $valor = $valor - ($loja_qtde_frisa * 50);
        $desconto_especial_frisa = $loja_qtde_frisa * 50;
        // $valor = $valor - $desconto_especial_frisa;
    }
    if($desconto) {
        $desconto_valores = $valor_desconto + $desconto_especial_folia + $desconto_especial_frisa + $cupom_valor_desconto;
        $valor_final -= $desconto_valores;
        $valor_desconto = number_format($desconto_valores, 2, '', '');
    }
    
    if($delivery) {
        //$delivery_valor;
        $loja_endereco = utf8_encode($loja['LO_CLI_ENDERECO']);
        $loja_numero = utf8_encode($loja['LO_CLI_NUMERO']);
        $loja_complemento = utf8_encode($loja['LO_CLI_COMPLEMENTO']);
        $loja_bairro = utf8_encode($loja['LO_CLI_BAIRRO']);
        $loja_cidade = utf8_encode($loja['LO_CLI_CIDADE']);
        $loja_estado = utf8_encode($loja['LO_CLI_ESTADO']);
        $loja_cep = utf8_encode($loja['LO_CLI_CEP']);
        $loja_data_para_entrega = utf8_encode($loja['DATA_PARA_ENTREGA']);
        $loja_cuidados = utf8_encode($loja['LO_CLI_CUIDADOS']);
        $loja_celular = utf8_encode($loja['LO_CLI_CELULAR']);
        $loja_referencia = utf8_encode($loja['LO_CLI_PONTO_REFERENCIA']);
        $valor_final += $delivery_valor;
        //$delivery_valor valor do frete fixo
    } 
}

//AVISO DO PAGAMENTO NO CARTÃO
switch ($_SESSION['ALERT-PAGAMENTO-CARTAO'][0]) {
  case 'sucesso':
    echo '<script>'.'swal("Sucesso", "'.$_SESSION['ALERT-PAGAMENTO-CARTAO'][1].'", "success")'.'</script>';
    break;
  case 'erro':
    echo '<script>'.'swal("Erro", "'.$_SESSION['ALERT-PAGAMENTO-CARTAO'][1].'", "error")'.'</script>';
    break;
}
?>
<input type="hidden" id="page" value="checkout">
<section id="overlay" class="fechar-modal"><span class="loader"></span></section>
<section class="modal-box" id="modal">
	<section class="modal-dialog">
		<section class="modal-content">
			<section id="endereco-box">
				<header>
					<h1><? echo $lg['pagamento_cielo_cadastrar_endereco']; ?></h1>
					<a href="#" class="fechar-modal">&times;</a>
				</header>
				<section id="conteudo">
					<form name="endereco" class="cadastro controle" method="post" id="cadastro-endereco" action="<? echo SITE; ?>checkout-endereco.php?t=cadastrar" data-toggle="validator" role="form">
						<input type="hidden" id="total" value="<? echo $valor_final; ?>">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
						
						<p class="form-group">
							<label><? echo $lg['pagamento_cielo_pais']; ?></label>
							<select name="pais" class="drop" style="width: 340px;">
							<?php foreach ($paises as $key => $pais) { ?>
								<option value="<?php echo $pais['PAIS_SIGLA'] ?>" <?php echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><?php echo $pais['PAIS_NOME'] ?></option>
							<? } ?>
							</select>
						</p>

						<p class="cep form-group">
							<label for="cep"><? echo $lg['pagamento_cielo_cep']; ?></label>
							<input type="text" name="cep" class="input pequeno" id="cep" value="" required>
							<a class="busca-cep" href="http://www.buscacep.correios.com.br/" target="_blank">Não sei meu CEP</a>
						</p>
						<p class="zipcode form-group" style="display: none;">
							<label for="cep">Zipcode</label>
							<input type="text" name="zipcode" class="input pequeno" id="zipzode" value="" required>
						</p>

						<div class="coluna">
							<p class="cidade form-group">
								<label for="cidade" class="control-label"><? echo $lg['pagamento_cielo_cidade']; ?></label>
								<input type="text" name="cidade" class="input" id="cidade" value="<? echo $endereco_cidade; ?>" required>
							</p>
							<p class="estado form-group">
								<label for="estado" class="control-label"><? echo $lg['pagamento_cielo_estado']; ?></label>
								<input type="text" name="estado" class="input" id="estado" value="<? echo $endereco_estado; ?>" required>
							</p>
							<div class="clear"></div>
						</div>

						<p class="form-group">
							<label for="bairro"><? echo $lg['pagamento_cielo_bairro']; ?></label>
							<input type="text" name="bairro" class="input" id="bairro" value="<? echo $endereco_bairro; ?>" required>
						</p>

						<p class="form-group">
							<label for="endereco"><? echo $lg['pagamento_cielo_endereco']; ?></label>
							<input type="text" name="endereco" class="input" id="endereco" value="<? echo $endereco_logradouro; ?>" required>
						</p>

						<p class="numero form-group">
							<label for="numero" class="control-label"><? echo $lg['pagamento_cielo_numero']; ?></label>
							<input type="number" step="1" name="numero" class="input" id="numero" value="<? echo $endereco_numero; ?>" required>
						</p>
						<p class="complemento form-group">
							<label for="complemento"><? echo $lg['pagamento_cielo_complemento']; ?></label>
							<input type="text" name="complemento" class="input complemento" id="complemento" value="<? echo $endereco_complemento; ?>" />
						</p>
					
						<div class="selectbox coluna pequeno form-group" id="usuario-filial">
							<h3><? echo $lg['pagamento_cielo_tipo_endereco']; ?></h3>
							<a href="#" class="arrow"><strong></strong><span></span></a>
							<ul class="drop">
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="<? echo $lg['pagamento_cielo_tipo_comercial']; ?>" value="Comercial" required><? echo $lg['pagamento_cielo_tipo_comercial']; ?></label></li>
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="<? echo $lg['pagamento_cielo_tipo_residencial']; ?>"  value="Residencial" required><? echo $lg['pagamento_cielo_tipo_residencial']; ?></label></li>
							</ul>
							<div class="clear"></div>
						</div>						
						<footer>
							<input type="submit" class="input submit" value="<? echo $lg['pagamento_cielo_salvar_endereco']; ?>" />
							<a href="#" class="cancel no-cancel coluna fechar-modal"><? echo $lg['pagamento_cielo_cancelar']; ?></a>
						</footer>
						<div class="clear"></div>
					</form>		
				</section>
			</section>
		</section>
	</section>
</section>

<section id="conteudo">
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {

	?>

	<!-- <header class="titulo">
		<h1>Dados do <span>cartão</span></h1>

		<div class="valor-total">R$ <? echo $loja_valor_total_f; ?></div>
	</header> -->
	<section class="padding">		
		<section id="conteudo" class="label-top">
			<form method="POST" id="pagamento-cartao" class="controle" name="pagamento-cartao" action="<?echo SITE.$link_lang?>ingressos/pagamento/cielo/confirmacao/">
				<!-- <section class="secao" id="compra-dados">
					<section>
						<h1><? echo $loja_nome; ?></h1>
						<p><? echo $loja_email; ?></p>
						<p><? echo formatTelefone($loja_telefone); ?></p>
					</section>
					<div class="clear"></div>
				</section> -->
				<input type="hidden" name="cod" value="<? echo $cod; ?>"/>
				<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
				<input type="hidden" name="lang" value="<? echo $link_lang; ?>" />
				
				<section class="secao enderecos">
					<section>
						<header class="titulo">
							<h1><? echo $lg['pagamento_cielo_endereco_cobranca']; ?></h1>
						</header>
						
						<ul class="enderecos">
							<li class="novo">
								<a href="#" class="open-modal" data-width="650" data-url="<? echo SITE; ?>checkout-endereco-cadastro.php?t=cadastrar"><? echo $lg['pagamento_cielo_novo_endereco']; ?></a>
							</li>

							<?php 
							$sql_enderecos =sqlsrv_query($conexao_sankhya, "SELECT * FROM clientes_enderecos WHERE CE_CLIENTE=$loja_cliente AND CE_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY CE_ULTIMA_ENTREGA DESC", $conexao_params, $conexao_options);

							if(sqlsrv_num_rows($sql_enderecos) > 0) {

								$i = 2;
								while ($endereco = sqlsrv_fetch_array($sql_enderecos)) {
									
									$endereco_cod = $endereco['CE_COD'];
									$endereco_pais = $endereco['CE_PAIS'];
									$endereco_cep = $endereco['CE_CEP'];
									$endereco_logradouro = utf8_encode($endereco['CE_ENDERECO']);
									$endereco_numero = $endereco['CE_NUMERO'];
									$endereco_complemento = $endereco['CE_COMPLEMENTO'];
									$endereco_bairro = utf8_encode($endereco['CE_BAIRRO']);
									$endereco_cidade = utf8_encode($endereco['CE_CIDADE']);
									$endereco_estado = utf8_encode($endereco['CE_ESTADO']);
									$endereco_tipo_endereco = utf8_encode($endereco['CE_TIPO_ENDERECO']);
									$endereco_ponto_referencia = utf8_encode($endereco['CE_PONTO_REFERENCIA']);

									?>
									<li class="<? if($i%3 == 0) echo 'last'; ?><?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['endereco']==$endereco_cod?"checked":""; ?>">
										<a href="#" title="Alterar Endereço" class="open-modal modal editar" data-width="650" data-cod="<?php echo $endereco_cod ?>" data-cep="<?php echo $endereco_cep ?>" data-endereco="<?php echo $endereco_logradouro ?>" data-numero="<?php echo $endereco_numero ?>" data-complemento="<?php echo $endereco_complemento ?>" data-bairro="<?php echo $endereco_bairro ?>" data-cidade="<?php echo $endereco_cidade ?>" data-estado="<?php echo $endereco_estado ?>" data-tipo-endereco="<?php echo $endereco_tipo_endereco ?>" data-referencia="<?php echo $endereco_ponto_referencia ?>" data-pais="<?php echo $endereco_pais ?>"></a>
										<?php if($endereco_pais=="BR"){?>
											<p><? echo $endereco_logradouro.', '.$endereco_numero; ?><br/>
											<? echo $endereco_bairro; ?><br/>
											CEP <? echo $endereco_cep; ?><br/>
											<? echo $endereco_cidade.', '.$endereco_estado.' - '.$endereco_pais; ?>
											</p>
										<?php }else{ ?>
											<p><? echo $endereco_logradouro.', '.$endereco_numero; ?><br/>
											ZipCode <? echo $endereco_cep; ?><br/>
											<? echo $endereco_cidade.', '.$endereco_estado.' - '.$endereco_pais; ?></p>
											<?php } ?>
										<label type="button" class="utilizar"><input type="radio" name="endereco" value="<? echo $endereco_cod; ?>" <?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['endereco']==$endereco_cod?"checked":""; ?>><? echo $lg['pagamento_cielo_utilizar_endereco']; ?></label>
									</li>
							<?}
							} ?>
						</ul>
					</section>
					<div class="clear"></div>
				</section>
				<section class="secao">	
					<input type="hidden" name="codigoBandeira" id="bandeira">
					<ul class="list-cards">
						<li><label><img id="cardvisa" alt="visa" src="<?echo SITE?>img/card-visa.png" class="cartoes opacity"> </label>	</li>
						<li><label><img id="cardamex" alt="amex" src="<?echo SITE?>img/card-amex.png" class="cartoes opacity"> </label></li>
						<li><label><img id="cardmaster" alt="master" src="<?echo SITE?>img/card-master.png" class="cartoes opacity"> </label></li>
						
						<!--<li> <label> <img id="cardhiper" alt="hiper" src="<?echo SITE?>img/card-hiper.jpg" class="cartoes opacity"> </label></li>>-->

						<li> <label> <img id="carddiscover" alt="elo" src="<?echo SITE?>img/card-discover.png" class="cartoes opacity"> </label></li>

						<li> <label> <img id="carddiners" alt="diners" src="<?echo SITE?>img/card-diners.png" class="cartoes opacity"> </label></li>

						<li><label><img id="cardelo" alt="elo" src="<?echo SITE?>img/card-elo.png" class="cartoes opacity"> </label></li>										

						<!-- <input type="hidden" id="card" name="card" value="">
						<input type="hidden" id="total" name="total" value="<?echo $total;?>">
						<input type="hidden" id="tentarAutenticar" name="tentarAutenticar" value="nao">
						<input type="hidden" id="tipoParcelamento" name="tipoParcelamento" value="2">
						<input type="hidden" id="capturarAutomaticamente" name="capturarAutomaticamente" value="false">
						<input type="hidden" id="indicadorAutorizacao" name="indicadorAutorizacao" value="3"> -->
						
						<div class="clear"></div>
					</ul>
					<div class="metade">
						<p>
							<label id="ncard"><? echo $lg['pagamento_cielo_numero_cartao']; ?></label>
							<input autocomplete="off" type="text" id="numero_cartao" name="cartaoNumero" class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['cartaoNumero']; ?>">
						</p>
						
						<p class="validade">
							<label><? echo $lg['pagamento_cielo_mes_validade']; ?></label>
							<input autocomplete="off" type="text" id="mes_validade" name="mesValidade" maxlength=""  class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['mesValidade']; ?>">
						</p>

						<p class="validade" style="margin-left: 10px;">
							<label><? echo $lg['pagamento_cielo_ano_validade']; ?></label>
							<input autocomplete="off" type="text" id="ano_validade" name="anoValidade" maxlength=""  class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['anoValidade']; ?>">
						</p>

						<p class="codigo">
							<label><? echo $lg['pagamento_cielo_codigo_seguranca']; ?></label>
							<input autocomplete="off" type="text" id="codigo_seguranca" name="cartaoCodigoSeguranca" maxlength="4" class="input min" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['cartaoCodigoSeguranca']; ?>">
							<img class="imgcodseg" src="<?echo SITE?>img/numseg.png" >
						</p>
						
						<p>
							<label><? echo $lg['pagamento_cielo_nome_titular']; ?></label>
							<input autocomplete="off" type="text" id="nome_titular" name="nomeTitular" class="input" value="<?php echo $_SESSION['FORM-PAGAMENTO-CARTAO']['nomeTitular']; ?>">
						</p>
					</div>
					<div class="metade">
						<section class="selectbox coluna">
							<h3><? echo $lg['pagamento_cielo_documento_titular']; ?></h3>
							<a href="#" class="arrow"><strong>
								<?php 
								switch ($_SESSION['FORM-PAGAMENTO-CARTAO']['documento']) {
									case 'cpf':
										echo "CPF";
										break;
									case 'cnpj':
										echo "CNPJ";
										break;
									case 'passaporte':
										echo $lg['pagamento_cielo_passaporte'];
										break;
									default:
										echo "";
										break;
								}
								?>
							</strong><span></span></a>
							<ul class="drop">
							<?php if($cliente_pais=="BR"){ ?>
								<li><label class="item "><input type="radio" name="documento" value="cpf" alt="CPF">CPF</label></li>
								<li><label class="item "><input type="radio" name="documento" value="cnpj" alt="CNPJ">CNPJ</label></li>
							<?php }else{ ?>
								<li><label class="item"><input type="radio" name="documento" value="passaporte" alt="<? echo $lg['pagamento_cielo_passaporte']; ?>"><? echo $lg['pagamento_cielo_passaporte']; ?></label></li>
							<?php } ?>
							</ul>
						</section>
						<p>
							<label><? echo $lg['pagamento_cielo_numero_documento']; ?></label>
							<input autocomplete="off" type="text" id="cpfcnpj" name="cpfcnpj" class="input" value="<?php 
							if(empty($_SESSION['FORM-PAGAMENTO-CARTAO']['cpfcnpj'])){
								if($cliente_pais=="BR"){
									echo $cliente_cpf_cnpj;
								}else{
									echo $cliente_passaporte;
								}
							}else{
								echo $_SESSION['FORM-PAGAMENTO-CARTAO']['cpfcnpj'];
							} ?>">
						</p>
						<section class="selectbox coluna parcelas">
							<h3><? echo $lg['pagamento_cielo_numero_parcelas']; ?></h3>
							<a href="#" class="arrow"><strong><? echo $lg['pagamento_cielo_informe_numero']; ?></strong><span></span></a>
							<ul class="drop">
								<!-- <li><label class="item"><input type="radio" name="formaPagamento" value="" alt="Informe o número do cartão" disabled />Informe o número do cartão</label></li> -->
								<? /* for ($i=1; $i<=6 ; $i++) { ?>
									<li>
										<label class="item"><input type="radio" name="formaPagamento" value="<? echo $i; ?>" alt="<? echo $i; ?>" <?php echo $i==$_SESSION['FORM-PAGAMENTO-CARTAO']['formaPagamento']?"checked":"";?> /><? echo $i; ?>
										</label>
									</li>
								<? }*/ ?>
							</ul>
						</section>
						<!-- <section class="selectbox coluna endereco">
							<h3>Endereço de Cobrança</h3>
							<a href="#" class="arrow"><strong></strong><span></span></a>
							<ul class="drop">
								<?//busca enderecos desse cliente
								$sql_enderecos =sqlsrv_query($conexao_sankhya, "SELECT * FROM clientes_enderecos WHERE CE_CLIENTE=$loja_cliente AND CE_BLOCK='0' AND D_E_L_E_T_='0' ORDER BY CE_ULTIMA_ENTREGA DESC", $conexao_params, $conexao_options);

								if(sqlsrv_num_rows($sql_enderecos) > 0) {

									$i = 2;
									while ($endereco = sqlsrv_fetch_array($sql_enderecos)) {
										
										$endereco_cod = $endereco['CE_COD'];
										$endereco_nome_destinatario = utf8_encode($endereco['CE_NOME_DESTINATARIO']);
										$endereco_cep = $endereco['CE_CEP'];
										$endereco_logradouro = utf8_encode($endereco['CE_ENDERECO']);
										$endereco_numero = $endereco['CE_NUMERO'];
										$endereco_complemento = $endereco['CE_COMPLEMENTO'];
										$endereco_bairro = utf8_encode($endereco['CE_BAIRRO']);
										$endereco_cidade = utf8_encode($endereco['CE_CIDADE']);
										$endereco_estado = utf8_encode($endereco['CE_ESTADO']);
										$endereco_tipo_endereco = utf8_encode($endereco['CE_TIPO_ENDERECO']);
										$endereco_ponto_referencia = utf8_encode($endereco['CE_PONTO_REFERENCIA']);

										?>
										<li>
											<label class="item"><input type="radio" name="endereco-cobranca" value="<?echo $endereco_cod?>"/><?echo $endereco_nome_destinatario?> - <?echo $endereco_logradouro?>, <?echo $endereco_numero?> <?if(!empty($endereco_complemento)){echo $endereco_complemento;}?> <?echo $endereco_cidade?>
											</label>
										</li>
									<?}
								}else {	?>
									<li>
										<label class="item"><input type="radio" name="" value=""/>Não há endereços cadastrados
										</label>
									</li>
								<?php } ?>		
							</ul>
						</section> -->
						<!-- <input type="button" class="open-modal submit adicionar" value="+" autocomplete="off"> -->
					</div>
					<section id="compra-pagamento">
						<!-- CIELO -->

							<input type="submit" class="submit cielo" value="<? echo $lg['pagamento_cielo_finalizar']; ?>"/>

					    <div class="clear"></div>
					</section>
					<div class="clear"></div>
				</section>
			</form>
			<div class="clear"></div>
		</section>
		
		<?

		// //Verificar a existencia de cupom de desconto para essa compra
		// $sql_exist_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COMPRA='$loja_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='1' ", $conexao_params, $conexao_options);
		// if(sqlsrv_num_rows($sql_exist_cupom) > 0) {

		// 	$cupom_utilizado = true;
		// 	$cupom = sqlsrv_fetch_array($sql_exist_cupom);

		// 	$cupom_cod = $cupom['CP_COD'];
		// 	$cupom_nome = utf8_encode($cupom['CP_NOME']);
		// 	$cupom_codigo = $cupom['CP_CUPOM'];
		// 	$cupom_valor = $cupom['CP_DESCONTO'];
		// 	$cupom_tipo = $cupom['CP_TIPO'];

		// } else {

		// 	//Verificar a existencia de cupom de desconto
		// 	if($_SESSION['compra-cupom']['usuario'] == $loja_cliente) {
				
		// 		$cupom_cod = $_SESSION['compra-cupom']['cod'];
		// 		$cupom_delete = true;

		// 		$sql_cupom = sqlsrv_query($conexao, "SELECT TOP 1 * FROM cupom WHERE CP_COD='$cupom_cod' AND CP_BLOCK='0' AND D_E_L_E_T_='0' AND CP_UTILIZADO='0' AND CP_DATA_VALIDADE >= GETDATE() ", $conexao_params, $conexao_options);
		// 		$n_cupom = sqlsrv_num_rows($sql_cupom);

		// 		if($n_cupom > 0) {

		// 			$cupom = sqlsrv_fetch_array($sql_cupom);

		// 			$cupom_cod = $cupom['CP_COD'];
		// 			$cupom_nome = utf8_encode($cupom['CP_NOME']);
		// 			$cupom_codigo = $cupom['CP_CUPOM'];
		// 			$cupom_valor = $cupom['CP_DESCONTO'];
		// 			$cupom_tipo = $cupom['CP_TIPO'];

		// 			// 1 Porcentagem
		// 			// 2 Valor

		// 			$_SESSION['compra-cupom']['usuario'] = $loja_cliente;
		// 			$_SESSION['compra-cupom']['cod'] = $cupom_cod;
		// 			$_SESSION['compra-cupom']['compra'] = $cod;

		// 			switch ($cupom_tipo) {
		// 				case 1:
		// 					/*$loja_valor_ingressos = $loja_valor_ingressos - (($cupom_valor * $loja_valor_ingressos) / 100);*/
		// 					$cupom_valor_desconto = (($cupom_valor * $loja_valor_ingressos) / 100);
		// 				break;
						
		// 				case 2:
		// 					if($loja_valor_ingressos >= $cupom_valor) $cupom_valor_desconto = $cupom_valor; /*$loja_valor_ingressos = $loja_valor_ingressos - $cupom_valor;*/
		// 					else unset($_SESSION['compra-cupom'], $cupom_cod);
		// 				break;
		// 			}
					
		// 			$loja_valor_total = $loja_valor_ingressos + $loja_valor_adicionais;

		// 			//Total formatado
		// 			$loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');
		// 		}

		// 	}
		// }

		?>

	</section>
	<?
	}
	?>	
</section>
<?

//-----------------------------------------------------------------//

include('include/footer.php');

?>
<script>
$(document).ready(function(){
	var site = $("#base-site").val();

	//select2
	$('select[name="pais"]').select2();

	$("form#pagamento-cartao input[name='documento']").radioSel('<? 
		if(empty($_SESSION['FORM-PAGAMENTO-CARTAO']['documento'])){
			if($cliente_pais!="BR"){
				echo "passaporte";
			}else if($cliente_pessoa=="F"){
				echo "cpf";
			}else{
				echo "cnpj";
			}
		}else{
			echo $_SESSION['FORM-PAGAMENTO-CARTAO']['documento'];
		} ?>');

	$('form input[name="cep"]:text').mask('99999-999');
    $(document).on('click','.open-modal',function(){
        $("body").addClass("modal-open");
        $("#modal header h1").html("<? echo $lg['pagamento_cielo_cadastrar_endereco']; ?>");
        $("#overlay,#modal").fadeIn("fast");
        $('form#cadastro-endereco')[0].reset();
        $('#modal #endereco-box').find('select[name="pais"]').val('BR').trigger('change');
        $('#modal #endereco-box').find('form').attr('action',site+'checkout-endereco.php?t=cadastrar');
    });
    $(document).on('click', 'a.fechar-modal', function(){
        $("body").removeClass('modal-open');
        $("#overlay").fadeOut("fast");
        $("#modal").fadeOut("fast");
        return false;
    });
    $(document).on('click','#modal .modal-dialog',function(event){
       //event.stopPropagation();
    });
    $(document).on('click', '#modal', function(){
        //$("body").removeClass('modal-open');
        //$("#overlay").fadeOut("fast");
        //$("#modal").fadeOut("fast");
        //return false;
    });
    $(document).on('click','.open-modal.editar',function(){
        //preencher o formulário
        $form=$(this);
        $("#modal header h1").html("<? echo $lg['pagamento_cielo_alterar_endereco']; ?>");
        $('#modal #endereco-box').find('input[name="cod"]').val($form.data('cod')).blur();
        $('#modal #endereco-box').find('select[name="pais"]').val($form.data('pais')).trigger('change');
        $('#modal #endereco-box').find('input[name="zipcode"]').val($form.data('cep'))
        if($form.data('pais')!="BR"){
        	$('#modal #endereco-box').find('input[name="cep"]').val('');
        	$('#modal #endereco-box').find('input[name="zipcode"]').val($form.data('cep'));
        }else{
        	$('#modal #endereco-box').find('input[name="cep"]').val($form.data('cep'));
        	$('#modal #endereco-box').find('input[name="zipcode"]').val('')
        }
        
        $('#modal #endereco-box').find('input[name="endereco"]').val($form.data('endereco')).blur();
        $('#modal #endereco-box').find('input[name="numero"]').val($form.data('numero')).blur();
        $('#modal #endereco-box').find('input[name="complemento"]').val($form.data('complemento')).blur();
        $('#modal #endereco-box').find('input[name="bairro"]').val($form.data('bairro')).blur();
        $('#modal #endereco-box').find('input[name="cidade"]').val($form.data('cidade')).blur();
        $('#modal #endereco-box').find('input[name="estado"]').val($form.data('estado')).blur();
        $('#modal #endereco-box').find('input[name="tipo_endereco"][value="'+$form.data('tipo-endereco')+'"]').trigger('click');
        $('#modal #endereco-box').find('form').attr('action',site+'checkout-endereco.php?t=editar');
    });

    //--------------controle dos enderecos-------------------------------------------
    $('section.enderecos ul.enderecos').on('change','li label.utilizar input[name="endereco"]',function(event) {
        $('section.enderecos ul.enderecos').find("li.checked").removeClass("checked");
        $('section.enderecos ul.enderecos').find("li").each(function(){
            if($(this).find("input[type='radio']").is(":checked")) $(this).addClass("checked");
        });
    });
    // $('form#cadastro-endereco').submit(function(event) {
        // var $form=$(this);
        // if(!$form.hasClass('return-false')){
        //     $.ajax({
        //         url : $form.attr('action'),
        //         method : 'POST',
        //         data : $form.serialize(),
        //         success : function(resposta){
        //             //resposta=JSON.parse(resposta);
        //             if(resposta.sucesso){
        //                 // alert();
        //                 $form[0].reset();
        //                //$('a.fechar-modal').trigger('click');
        //                 getEnderecos($form.find('input[name="cliente"]').val());
        //                 $('a.fechar-modal').trigger('click');
        //             }
        //         }
        //     });
        // }

        // return false;
    // });

    //Criando os requires
    // $('form#cadastro-endereco').validation({
    //     rules: {
    //         cep: { tipo: 'cep' },
    //         endereco: { required: true },
    //         numero: { required: true },
    //         complemento: { required: false },
    //         bairro: { required: true },
    //         cidade: { required: true },
    //         estado: { required: true },
    //         tipo_endereco: { required: true },
    //         referencia: { required: false }
    //     }
    // });
    $("#ano_validade").mask("9999");
    $("#mes_validade").mask("99");
    $("#codigo_seguranca").mask("9?999");
    
    $('form#pagamento-cartao').validation({
        rules: {
            numero_cartao: { required: true},
            ano_validade: { required: true },
            mes_validade: { required: true },
            codigo_seguranca: { required: true },
            cpfcnpj: { required: true },
            parcelas: { required: true },
            endereco: { required: true }
        }
    });
    var $cadastro = $("form");

    $cadastro.find("input[name='cep']").blur(function(){
        
        var site = $("#base-site").val();
        
        // Pegamos o valor do input CEP
        var cep = $cadastro.find("input[name='cep']").val();
        
        // Se o CEP nÃ£o estiver em branco
        if(cep != '') {

            // Adiciona imagem de "Loading"
            $cadastro.find(".endereco input[name='cep']").addClass('loading');
            
            $.getJSON(site + "include/busca-cep.php", {
                cep: cep
            }, function(resultado) {                
                $cadastro.find(".endereco input[name='cep']").removeClass('loading');

                //Valores
                $cadastro.find("input[name='endereco']").val(resultado.logradouro).blur();
                $cadastro.find("input[name='bairro']").val(resultado.bairro).blur();
                $cadastro.find("input[name='cidade']").val(resultado.cidade).blur();

                if($cadastro.find("input[name='estado']").data('uf') != null) $cadastro.find("input[name='estado'][data-uf='"+resultado.uf+"']").trigger('click');
                else $cadastro.find("input[name='estado']").radioSel(resultado.uf);

                //alteração para o cadastro de enderecos do pagamento
                $cadastro.find("input[id='estado']").val(resultado.uf);

                $cadastro.find("input[name='numero']").focus();
            });
        } else {
            // Se o campo CEP estiver em branco, apresenta mensagem de erro
            // alert('Para que o endereÃ§o seja completado automaticamente vocÃª deve preencher o campo CEP!');
        }
        return false;
    });
    $('#numero_cartao').keyup(function(){
        var value = $(this).val();
        getCreditCardLabel(value);
    });
    if($('#numero_cartao').val()!=""){
        $('#numero_cartao').trigger('keyup');
    }
        //-------------------------------------------------------------------//
    // function getEnderecos(cliente){
    //     $.ajax({
    //         url : site + 'checkout-endereco.php?t=consultar',
    //         method : 'POST',
    //         data : {cliente: cliente},
    //         success : function(resposta){
    //             // resposta=JSON.parse(resposta);
    //             if(resposta.sucesso){
    //                 $('section.enderecos ul.enderecos li').not('.novo').remove();
    //                 resposta.enderecos.forEach(function(endereco){
    //                     $('section.enderecos ul.enderecos').append('<li class=""><a href="#" title="Alterar Endereço" class="open-modal modal editar" data-width="650" data-cod="'+endereco['CE_COD']+'" data-cep="'+endereco['CE_CEP']+'" data-endereco="'+endereco['CE_ENDERECO']+'" data-numero="'+endereco['CE_NUMERO']+'" data-complemento="'+endereco['CE_COMPLEMENTO']+'" data-bairro="'+endereco['CE_BAIRRO']+'" data-cidade="'+endereco['CE_CIDADE']+'" data-estado="'+endereco['CE_ESTADO']+'" data-tipo-endereco="'+endereco['CE_TIPO_ENDERECO']+'" data-referencia="'+endereco['CE_PONTO_REFERENCIA']+'"></a><h3></h3><p>'+endereco['CE_ENDERECO']+', '+endereco['CE_NUMERO']+'<br/>'+endereco['CE_BAIRRO']+'<br/>CEP '+endereco['CE_CEP']+'<br/>'+endereco['CE_CIDADE']+', '+endereco['CE_ESTADO']+'</p><label type="button" class="utilizar"><input type="radio" name="endereco" value="'+endereco['CE_COD']+'">Utilizar este endereço</label></li>');
    //                 });
    //             }
    //         }
    //     });
    // }
    function getCreditCardLabel(cardNumber){

	    var site = $("#base-site").val();

	    // $.ajax({
	    //   url : site + 'verifycard.php',
	    //   method : 'POST',
	    //   data : { card : cardNumber },
	    //   success : function(html){
	    var bandeira='';
	    switch(true){
	        case (/^(636368|438935|504175|451416|636297)/).test(cardNumber) :
	            bandeira = 'elo';  
	        break;
	     
	        case (/^(606282)/).test(cardNumber) :
	        bandeira = 'hipercard';    
	        break;
	     
	        case (/^(5067|5090|4576|4011)/).test(cardNumber) :
	        bandeira = 'elo';  
	        break;
	     
	        case (/^(3841)/).test(cardNumber) :
	        bandeira = 'hipercard';    
	        break;
	     
	        case (/^(6011)/).test(cardNumber) :
	        bandeira = 'discover'; 
	        break;
	     
	        case (/^(622)/).test(cardNumber) :
	        bandeira = 'discover'; 
	        break;
	     
	        case (/^(301|305)/).test(cardNumber) :
	        bandeira = 'diners';   
	        break;
	     
	        case (/^(34|37)/).test(cardNumber) :
	        bandeira = 'amex'; 
	        break;
	     
	        case (/^(36|38)/).test(cardNumber) :
	        bandeira = 'diners';   
	        break;
	     
	        case (/^(64|65)/).test(cardNumber) :
	        bandeira = 'discover'; 
	        break;
	     
	        case (/^(50)/).test(cardNumber) :
	        bandeira = 'aura'; 
	        break;
	     
	        case (/^(35)/).test(cardNumber) :
	        bandeira = 'jcb';  
	        break;
	     
	        case (/^(60)/).test(cardNumber) :
	        bandeira = 'hipercard';    
	        break;
	     
	        case (/^(4)/).test(cardNumber) :
	        bandeira = 'visa'; 
	        break;
	     
	        case (/^(5)/).test(cardNumber) :
	        bandeira = 'mastercard';   
	        break;
	    }
	        // console.log(bandeira);
	        // if(html!=''){

	            $('#ncard').html('Número do cartão');
	            var verifytotal = $('#total').val();
	            var options = '';

	            if( (bandeira=='mastercard') || (bandeira=='diners') || (bandeira=='visa') || (bandeira=='elo') ){
	                
	                for(i=1;i<=6;i++){
	                    options = options + '<li><label class="item"><input type="radio" name="formaPagamento" value="'+i+'" alt="'+i+'"/>'+i+' x R$ '+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</label></li>';
	                }

	            }
	            if( (bandeira=='amex') || (bandeira=='aura')){
	                
	                for(i=1;i<=3;i++){
	                    options = options + '<li><label class="item"><input type="radio" name="formaPagamento" value="'+i+'" alt="'+i+'"/>'+i+' x R$ '+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</label></li>';
	                }

	            }
	            if(bandeira=='discover'){
	                for(i=1;i<=1;i++){
	                    options = options + '<li><label class="item"><input type="radio" name="formaPagamento" value="'+i+'" alt="'+i+'"/>'+i+' x R$ '+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',') +'</label></li>';
	                }  
	            }

	            $('.selectbox.parcelas').find('strong').html('');
	            $('.selectbox.parcelas').find('.drop').html(options);
	            // $('#pnparcelas').html('<label>Número de parcelas</label><select id="formaPagamento" name="formaPagamento">'+options+'</select>');
	            
	            /*if( (bandeira=='discover') || (bandeira=='hiper') || (bandeira=='diners')  ){
	                $('#numero-cartao').val('');
	                $('#pnparcelas').html('<label>Número de parcelas</label><select id="formaPagamento" name="formaPagamento"><option value="">Informe o número do cartão</option></select>');
	            }*/

	            if(bandeira=='elo'){
	               $('#bandeira').val('elo');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardelo').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               return 'elo';
	              }

	              if(bandeira=='visa'){
	               $('#bandeira').val('visa');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardvisa').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               return 'visa';
	              }

	              if(bandeira=='aura'){
	               $('#bandeira').val('aura');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardaura').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               return 'aura';
	              }

	              if(bandeira=='mastercard'){
	                $('#bandeira').val('mastercard');
	               $('img.cartoes').removeClass('opacity');
	               $('img.cartoes').addClass('opacity');
	               $('img#cardmaster').removeClass('opacity');
	               //altera a imagem
	               $('.imgcodseg').attr('src',site+'img/numseg.png');  
	               return 'mastercard';
	              }
	              if(bandeira=='amex'){
		               $('#bandeira').val('amex');
		               $('img.cartoes').removeClass('opacity');
		               $('img.cartoes').addClass('opacity');
		               $('img#cardamex').removeClass('opacity');
		               //altera a imagem
		               $('.imgcodseg').attr('src',site+'img/numseg2.png');
	               return 'amex';
	              }
	              if(bandeira=='diners'){
		               $('#bandeira').val('diners');
		               $('img.cartoes').removeClass('opacity');
		               $('img.cartoes').addClass('opacity');
		               $('img#carddiners').removeClass('opacity');
		               //altera a imagem
		               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               	return 'diners';
	              }
	              if(bandeira=='discover'){
		               $('#bandeira').val('discover');
		               $('img.cartoes').removeClass('opacity');
		               $('img.cartoes').addClass('opacity');
		               $('img#carddiscover').removeClass('opacity');
		               //altera a imagem
		               $('.imgcodseg').attr('src',site+'img/numseg.png');
	               	return 'diners';
	              }
	              $('#ncard').html('Número do cartão <span style="color:red">(Bandeira inválida)</span>');
	              $('.selectbox.parcelas').find('strong').html('Informe o número do cartão');
	              $('.selectbox.parcelas').find('.drop').html('<li><label class="item"><input type="radio" name="formaPagamento" value="" alt="Informe o número do cartão"/>Informe o número do cartão</label></li>');
	              $('img.cartoes').removeClass('opacity');
	              $('img.cartoes').addClass('opacity');
	              $('#bandeira').val('');
	              return '';
		        // }

		  //   }
		  // });
		  
		return false;
	}
	//pagamento cartao
    $('form#pagamento-cartao').submit(function(event) {
        var $form=$(this);
        if(!$form.hasClass('return-false')){
        }else{
            if($('form#pagamento-cartao').find('input[name="endereco"]:checked').length!=1){
                swal("", "<?php echo $lg['pagamento_cielo_selecione_endereco'] ?>", "warning");
            }
            return false;
        }
    });
	
});
</script>
<?

//limpar sessao com aviso e formulario preenchido
unset($_SESSION['ALERT-PAGAMENTO-CARTAO'],$_SESSION['FORM-PAGAMENTO-CARTAO']);

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");

?>