<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require "../includes/include.php";
##########################################################################################
// unset($_SESSION['roteiro-itens']);
//-----------------------------------------------------------------//
//arquivos de layout
include("include/head.php");
include("include/header.php");


error_reporting(E_ALL);
ini_set('display_errors', 1);


//-----------------------------------------------------------------//
$evento = (int) isset($_SESSION['usuario-carnaval'])?$_SESSION['usuario-carnaval']:setcarnaval();
$cod = (int) $_POST['cod'];
$cod_cliente = (int) $_POST['cliente'];
$cod_endereco = (int) $_POST['endereco'];
$cpfcnpj = format($_POST['cpfcnpj']);
$fromcliente = isset($_GET['fromcliente']);
$documento = format($_POST['documento']);
if(isset($_SESSION['usuario-cod'])){
    $tipo="cliente";
}

//busca das informações do cliente
$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 TIPPESSOA,CGC_CPF,AD_IDENTIFICACAO,PAIS_SIGLA FROM TGFPAR WHERE CODPARC='$cod_cliente'", $conexao_params, $conexao_options);
$ar_cliente = sqlsrv_fetch_array($sql_cliente);

//informação do tipo da pessoa cadastrada no banco
$cliente_pessoa = utf8_encode(trim($ar_cliente['TIPPESSOA']));
$cliente_cpf_cnpj = preg_replace( "@[./-]@", "", trim($ar_cliente['CGC_CPF']));
$cliente_passaporte = trim($ar_cliente['AD_IDENTIFICACAO']);
$cliente_pais = $ar_cliente['PAIS_SIGLA'];

// if(!empty($cliente_passaporte)) $session_language = 'US';
$session_language =  ($cliente_pais!="BR") ? 'US' : 'BR';



//guardando os valores do formulario
$_SESSION['FORM-PAGAMENTO-CARTAO']=array(
    'endereco'=>$_POST['endereco'],
    'cartaoNumero'=>$_POST["cartaoNumero"],
    'cpfcnpj'=>$_POST["cpfcnpj"],
    'anoValidade'=>$_POST["anoValidade"],
    'mesValidade'=>$_POST["mesValidade"],
    'cartaoCodigoSeguranca'=>$_POST["cartaoCodigoSeguranca"],
    'formaPagamento'=>$_POST["formaPagamento"],
    'nomeTitular'=>$_POST["nomeTitular"],
    'documento'=>$_POST["documento"]
);
//validação dos valores do formulario
if(empty($_POST['endereco'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o endereço.');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($_POST['cartaoNumero'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o número do cartão.');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($_POST['cpfcnpj'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o CPF / CNPJ');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($_POST['anoValidade'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o ano de validade do cartão');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($_POST['mesValidade'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o mês de validade do cartão');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($_POST['cartaoCodigoSeguranca'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o código de segurança do cartão');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($_POST['formaPagamento'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha a bandeira do cartão');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($_POST['nomeTitular'])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o nome do titular');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}else if(empty($documento)){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha o tipo do documento do titular');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}
// if(empty($_POST['endereco'])||empty($_POST['cartaoNumero'])||empty($_POST['cpfcnpj'])||empty($_POST['anoValidade'])||empty($_POST['mesValidade'])||empty($_POST['cartaoCodigoSeguranca'])||empty($_POST['formaPagamento'])||empty($_POST['nomeTitular'])||empty($documento)){
//     $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Preencha todos os campos');
//     header("Location: ".$_SERVER['HTTP_REFERER']."");   
//     exit();
// }
/*else if(!cardIsValid($_POST["cartaoNumero"])){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Cartão Inválido');
    header("Location: ".$_SERVER['HTTP_REFERER']."");   
    exit();
}*/
else if($documento=="cpf"&&!validaCPF($cpfcnpj)){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','CPF Inválido');
    header("Location: ".$_SERVER['HTTP_REFERER']."");
    exit();
}else if($documento=="cnpj"&&!validaCNPJ($cpfcnpj)){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','CNPJ Inválido');
    header("Location: ".$_SERVER['HTTP_REFERER']."");
    exit();
}else if($cliente_pais=="BR"&&$cliente_cpf_cnpj!=preg_replace( "@[./-]@", "", $cpfcnpj)){
    $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','O CPF/CNPJ do cartão é diferente do seu, utilize um cartão no seu nome');
    header("Location: ".$_SERVER['HTTP_REFERER']."");
    exit();
}
// else if (validaCPF($cpfcnpj)&&validaCNPJ($cpfcnpj)&&false){
//     exit();
// }
//-----------------------------------------------------------------//
$parcelas = array(3);
//compra(loja)
$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 l.*, (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA FROM loja l WHERE l.LO_EVENTO='$evento' AND l.LO_BLOCK='0' AND l.D_E_L_E_T_='0' AND l.LO_COD='$cod' AND l.LO_CLIENTE=$cod_cliente", $conexao_params, $conexao_options);
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
        // print_r($_SESSION['compra-cupom']);
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

                //marcar como cupom utilizado
                $sql_cupom_usado = sqlsrv_query($conexao, "UPDATE TOP (1) cupom SET CP_UTILIZADO=1, CP_COMPRA='$cod', CP_DATA_UTILIZACAO=GETDATE() WHERE CP_COD='$cupom_cod'", $conexao_params, $conexao_options);

                //Total formatado
                $loja_valor_total_f = number_format($loja_valor_total, 2, ',','.');
            }
        }
        // echo "aaaaa";
    }

    // exit();
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
        
        //atualizar valor da compra se tiver cupom
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
    } else { 
        // apaguei <input type="hidden" name="Shipping_Type" value="4" />
    }

    $desconto_ft = $_SESSION['desconto_ft'];

    if ($desconto_ft > 0) {
        $valor_final = $valor_final - $desconto_ft;

    }

    $sql_compra_up = sqlsrv_query($conexao, "UPDATE TOP (1) loja SET LO_VALOR_TOTAL='$valor_final' WHERE LO_COD='$cod'", $conexao_params, $conexao_options);    

    
    //Validar CPF ou CNPJ
    $loja_cpf_cnpj_teste = formatCPFCNPJ($loja_cpf_cnpj);
    switch (strlen($loja_cpf_cnpj_teste)) {
        case '14':
            if(!validaCPF($loja_cpf_cnpj_teste)) unset($loja_cpf_cnpj);
        break;
        case '18':
            if(!validaCNPJ($loja_cpf_cnpj_teste)) unset($loja_cpf_cnpj);
        break;
        default: 
            unset($loja_cpf_cnpj);
        break;                  
    }
    $loja_telefone = str_replace(' ', '', $loja_telefone);
    $loja_telefone = str_replace('+', '', $loja_telefone);
    if(strlen($loja_telefone) < 10) unset($loja_telefone);  
    $sql_endereco = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 * FROM clientes_enderecos WHERE CE_COD=$cod_endereco", $conexao_params, $conexao_options);
    $endereco=sqlsrv_fetch_array($sql_endereco);

    #################SessionID ClearSale#####################
    $sessionID=$_SESSION['SessionID'];

    #################Pedido##############################
    $Pedido = new Pedido();
        
    // Lê dados do $_POST
    $Pedido->formaPagamentoBandeira = format($_POST["codigoBandeira"]); 
    if($_POST["formaPagamento"] != "A" && $_POST["formaPagamento"] != "1")
    {
        // $Pedido->formaPagamentoProduto = $_POST["tipoParcelamento"];
        $Pedido->formaPagamentoProduto = 2;
        $Pedido->formaPagamentoParcelas = (int) $_POST["formaPagamento"];
    } 
    else 
    {
        $Pedido->formaPagamentoProduto = (int) $_POST["formaPagamento"];
        $Pedido->formaPagamentoParcelas = 1;
    }
    $Pedido->dadosEcNumero = LOJA;
    $Pedido->dadosEcChave = LOJA_CHAVE;
    //não capturar
    $Pedido->capturar = "false"; 
    //Autorizar direto
    $Pedido->autorizar = 3;
    $Pedido->dadosPortadorNumero = format($_POST["cartaoNumero"]);

    $Pedido->bin = substr(format($_POST["cartaoNumero"]), 0,6);

    $Pedido->token = 'false';
    
    $Pedido->dadosPortadorVal = format($_POST["anoValidade"]).str_pad(format($_POST["mesValidade"]), 2, "0", STR_PAD_LEFT);
    $Pedido->dadosPortadorNome = format($_POST["nomeTitular"]);
    // Verifica se Código de Segurança foi informado e ajusta o indicador corretamente
    if ($_POST["cartaoCodigoSeguranca"] == null || $_POST["cartaoCodigoSeguranca"] == "") {
        $Pedido->dadosPortadorInd = "0";
    } else if ($Pedido->formaPagamentoBandeira == "mastercard") {
        $Pedido->dadosPortadorInd = "1";
    } else {
        $Pedido->dadosPortadorInd = "1";
    }
    $Pedido->dadosPortadorCodSeg = format($_POST["cartaoCodigoSeguranca"]);
    //codigo do banco com a compra(tabela loja)
    $Pedido->dadosPedidoNumero = $loja_cod; 
    $Pedido->dadosPedidoValor = $valor_final*100;
    $Pedido->urlRetorno = ReturnURL($cod,$tipo);
    $Pedido->clienteEndereco = $endereco['CE_ENDERECO'];
    $Pedido->clienteComplemento = empty($endereco['CE_COMPLEMENTO'])?"NULL":$endereco['CE_COMPLEMENTO'];
    $Pedido->clienteNumero = $endereco['CE_NUMERO'];
    $Pedido->clienteBairro = $endereco['CE_BAIRRO'];
    $Pedido->clienteCep = $endereco['CE_CEP'];
    // ENVIA REQUISIÇÃO SITE CIELO
   // if($_POST["tentarAutenticar"] == "sim") // TRANSAÇÃO
    //tentar autenticar
    if(false) {
        $objResposta = $Pedido->RequisicaoTransacao(true);
    } else // AUTORIZAÇÃO DIRETA 
    {
        $objResposta = $Pedido->RequisicaoTid();
        $Pedido->tid = $objResposta->tid;
        $Pedido->pan = $objResposta->pan;
        $Pedido->status = $objResposta->status;
        $objResposta = $Pedido->RequisicaoAutorizacaoPortador();
    }

    $Pedido->tid = $objResposta->tid;
    $Pedido->pan = $objResposta->pan;
    $Pedido->status = $objResposta->status;

    //pegando url para autenticação caso exista
    $urlAutenticacao = "url-autenticacao";
    $Pedido->urlAutenticacao = $objResposta->$urlAutenticacao;

    //endereco
    $endereco_complemento = $endereco['CE_COMPLEMENTO'];
    $endereco_numero = $endereco['CE_NUMERO'];
    $endereco_endereco = $endereco['CE_ENDERECO'];
    $endereco_bairro = $endereco['CE_BAIRRO'];
    $endereco_cidade = $endereco['CE_CIDADE'];
    $endereco_estado = $endereco['CE_ESTADO'];
    $endereco_cep = $endereco['CE_CEP'];
    $endereco_pais = $endereco['CE_PAIS'];

    //validade cartao
    $validade_ano = format($_POST["anoValidade"]);
    $validade_mes = str_pad($_POST["mesValidade"], 2, "0", STR_PAD_LEFT);
   
    if($objResposta->status == 6) $update_pagamento =  " LO_PAGO=1, LO_DATA_PAGAMENTO=GETDATE(), ";
    if($objResposta->status == 4) $update_pagamento =  " LO_PAGO=0, LO_DATA_PAGAMENTO=GETDATE(), ";
    $stringxml = $Pedido->toString();

    $sql_loja = sqlsrv_query($conexao, "UPDATE loja SET 
        $update_pagamento
        
        LO_CLI_COMPLEMENTO = '$endereco_complemento',
        LO_CLI_NUMERO = '$endereco_numero',
        LO_CLI_ENDERECO = '$endereco_endereco',
        LO_CLI_BAIRRO = '$endereco_bairro',
        LO_CLI_CIDADE = '$endereco_cidade',
        LO_CLI_ESTADO = '$endereco_estado',
        LO_CLI_CEP = '$endereco_cep',
        LO_CLI_PAIS = '$endereco_pais',
        LO_TID ='$objResposta->tid',
        LO_STATUS_TRANSACAO='$objResposta->status',
        LO_CARTAO = '$Pedido->formaPagamentoBandeira',
        LO_XML = '$stringxml',
        LO_CARTAO_BANDEIRA='$Pedido->dadosPortadorNumero',
        LO_CARTAO_NOME = '$Pedido->dadosPortadorNome',
        LO_CARTAO_CPF = '$cpfcnpj',
        LO_PARCELAS = '$Pedido->formaPagamentoParcelas',
        LO_ANTIFRAUDE_SESSION_ID = '$sessionID',
        LO_CARTAO_VALIDADE_MES='$validade_mes',
        LO_CARTAO_VALIDADE_ANO='$validade_ano'

        WHERE LO_COD = $loja_cod", $conexao_params, $conexao_options);

    // $link_retorno = $fromcliente ? str_replace('controle2014/', '', SITE).$_POST['lang'].'ingressos-carnaval-2017/pagamento/cielo/'.$loja_cod : SITE.'compra/retorno/'.$loja_cod;
    // var_dump($Pedido->urlAutenticacao);
    // echo $Pedido->urlAutenticacao;

    if(is_object($objResposta)&&$objResposta->getName() == "erro"){
        $_SESSION['ALERT-PAGAMENTO-CARTAO'] = array('erro','Um erro ocorreu. Verifique todas as informações e tente outra vez!');
        header("Location: ".$_SERVER['HTTP_REFERER']."");
    }else{
        unset($_SESSION['FORM-PAGAMENTO-CARTAO']);
        if(!empty($Pedido->urlAutenticacao)){
            header("Location: ".$Pedido->urlAutenticacao);
        }else{
            header("Location: ".ReturnURL($cod,$tipo));
        }
    /* ?>
        <script type="text/javascript">
            window.location.href = "<?php echo ReturnURL($cod,$tipo) ?>";
        </script>
    <? &*/
        
        //apagar formulario
        
    }
}
//-----------------------------------------------------------------//

//Fechar conexoes
include("conn/close.php");
include("conn/close-sankhya.php");
//função pra validar número do cartão
?>