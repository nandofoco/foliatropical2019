<?

// error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
// ini_set('display_errors', 1);

include 'conn/conn.php';
include 'conn/conn-sankhya.php';
include 'inc/funcoes.php';
include 'inc/checklogado.php';
include 'inc/checkwww.php';
include 'inc/language.php';

$evento = setcarnaval();
$logado = checklogado();

$cod = (int) $_GET['c'];
$usuario_cod = $_SESSION['usuario-cod'];


//busca paises
$sql_paises= sqlsrv_query($conexao_sankhya, "SELECT * FROM pais", $conexao_params, $conexao_options);
$paises=array(); while($linha = sqlsrv_fetch_array($sql_paises)){ array_push($paises, $linha); }

//busca das informações do cliente
$sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1
        TIPPESSOA,
        AD_IDENTIFICACAO,
        CGC_CPF,
        PAIS_SIGLA,
        DTNASC,
        DDI,
        DDD,
        TELEFONE
    FROM 
        TGFPAR
    WHERE
        CODPARC='$usuario_cod'",
$conexao_params, $conexao_options);

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

/*if(!empty($cliente_data_nascimento) && !empty($cliente_ddi) && !empty($cliente_ddd) && !empty($cliente_telefone) && ($cliente_pais == 'BR' && (!empty($cliente_cpf_cnpj) && ($cliente_pessoa=="F" && validaCPF($cliente_cpf_cnpj))||($cliente_pessoa=="J" && validaCNPJ($cliente_cpf_cnpj))))||($cliente_pais != 'BR' && !empty($cliente_passaporte))){
		//continuar
}else{
	$_SESSION['ALERT'] = array('aviso','Sua informação de Data de Nascimento,CPF,CNPJ,(DDD)Telefone ou Passaporte está vazia ou é inválida. Complete suas informações!');
	header("Location: ".$_SERVER['HTTP_REFERER']);
	exit();
}*/

$tipo = $_GET['tipo'];

include 'inc/partials/head.php';
include 'inc/partials/header.php';

// if(!empty($cliente_passaporte)) $session_language = 'US';
$session_language = ($cliente_pais!="BR") ? 'US' : 'BR';


//-----------------------------------------------------------------//
$parcelas = array(3);


$sql_loja = sqlsrv_query($conexao, "SELECT TOP 1 
        l.*,
        (CONVERT(VARCHAR, l.LO_DATA_COMPRA, 103)+' '+SUBSTRING(CONVERT(VARCHAR, l.LO_DATA_COMPRA, 108),1,5)) AS DATA,
        ISNULL(DATEDIFF (DAY, LO_DATA_PAGAMENTO, GETDATE()), 6) AS DIFERENCA
    FROM
        loja l
    WHERE
        l.LO_EVENTO='$evento'
        AND l.LO_BLOCK='0'
        AND l.D_E_L_E_T_='0'
        AND l.LO_COD='$cod'
        AND l.LO_CLIENTE='$usuario_cod'
        AND l.LO_PAGO=0",
$conexao_params, $conexao_options);


if(sqlsrv_num_rows($sql_loja) > 0) {

    $loja = sqlsrv_fetch_array($sql_loja);
    $loja_cod = $loja['LO_COD'];
    $loja_cliente = $loja['LO_CLIENTE'];
    $loja_parceiro = $loja['LO_PARCEIRO'];
    $loja_desconto = (bool) $loja['LO_DESCONTO'];
    $desconto_fp = $loja['LO_VALOR_DESCONTO_FT'];
    $loja_data_compra = $loja['DATA'];
    
    $sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 
            NOMEPARC,
            TELEFONE,
            EMAIL,
            CGC_CPF
        FROM
            TGFPAR
        WHERE
            CODPARC='$loja_cliente'
            AND CLIENTE='S'
            AND BLOQUEAR='N'
        ORDER BY
            NOMEPARC ASC",
    $conexao_params, $conexao_options);

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
    
    $sql_itens = sqlsrv_query($conexao, "SELECT 
            li.*,
            v.VE_DIA,
            v.VE_SETOR,
            v.VE_FILA,
            v.VE_VAGAS,
            v.VE_TIPO_ESPECIFICO,
            es.ES_NOME,
            ed.ED_DATA,
            SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia,
            tp.TI_NOME,
            tp.TI_TAG
        FROM
            loja_itens li,
            vendas v,
            eventos_setores es,
            eventos_dias ed,
            tipos tp
        WHERE
            li.LI_COMPRA='$loja_cod'
            AND li.LI_INGRESSO=v.VE_COD
            AND es.ES_COD=v.VE_SETOR
            AND ed.ED_COD=v.VE_DIA
            AND v.VE_TIPO=tp.TI_COD
            AND li.D_E_L_E_T_='0'
        ORDER BY
            LI_COD ASC",
    $conexao_params, $conexao_options);

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
                $cupom_valor_desconto = (($cupom_valor * $loja_valor_ingressos) / 100);
            break;
            
            case 2:
                if($loja_valor_ingressos >= $cupom_valor) $cupom_valor_desconto = $cupom_valor;
                else unset($_SESSION['compra-cupom'], $cupom_cod);
            break;
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
    $sql_item = sqlsrv_query($conexao, " SELECT 
            COUNT(LI_COD) AS QTDE,
            LI_VALOR,
            LI_INGRESSO,
            LI_DESCONTO,
            LI_OVER_INTERNO,
            LI_OVER_EXTERNO,
            MIN(LI_COD) AS COD,
            MAX(LI_EXCLUSIVIDADE) AS EXCLUSIVIDADE,
            MAX(LI_EXCLUSIVIDADE_VAL) AS EXCLUSIVIDADE_VAL
        FROM
            loja_itens
        WHERE
            LI_COMPRA='$loja_cod'
            AND D_E_L_E_T_='0'
        GROUP BY
            LI_INGRESSO,
            LI_VALOR,
            LI_DESCONTO,
            LI_OVER_INTERNO,
            LI_OVER_EXTERNO",
    $conexao_params, $conexao_options);


    $itens = array();
    
    if(sqlsrv_num_rows($sql_item) > 0) {
        
        $cont = 0;
        $i = 1;
        $item_count = 1;
        while ($item = sqlsrv_fetch_array($sql_item)) {

            $itens[$cont]['cod'] = $item['COD'];
            $itens[$cont]['qtd'] = $item['QTDE'];
            $itens[$cont]['valor'] =  $item['LI_VALOR'];


            
            $item_cod = $item['COD'];
            $item_qtde = $item['QTDE'];
            $item_ingresso = $item['LI_INGRESSO'];
            $item_valor =  $item['LI_VALOR'];
            $item_desconto =  $item['LI_DESCONTO'];
            $item_overinterno =  $item['LI_OVER_INTERNO'];
            $item_overexterno =  $item['LI_OVER_EXTERNO'];
            $item_exclusividade = (bool) $item['EXCLUSIVIDADE'];
            $item_exclusividade_val = $item['EXCLUSIVIDADE_VAL'];
        
            
            //Procurar o overpricing
            $item_valor_tabela = 0.00;
            $item_valor_adicionais = 0.00;
            $item_valor_transfer = 0.00;
            $item_vagas = 1;

            //Informações adicionais do item
            $sql_info_item = sqlsrv_query($conexao, "SELECT
                    v.VE_DIA,
                    v.VE_SETOR,
                    v.VE_FILA,
                    v.VE_VAGAS,
                    v.VE_TIPO_ESPECIFICO,
                    v.VE_VALOR_EXCLUSIVIDADE,
                    es.ES_NOME,
                    ed.ED_NOME,
                    ed.ED_DATA,
                    SUBSTRING(CONVERT(VARCHAR, ed.ED_DATA, 103), 1, 5) AS dia,
                    tp.TI_NOME,
                    tp.TI_TAG 
                FROM
                    vendas v,
                    eventos_setores es,
                    eventos_dias ed,
                    tipos tp 
                WHERE
                    v.VE_COD='$item_ingresso'
                    AND es.ES_COD=v.VE_SETOR
                    AND ed.ED_COD=v.VE_DIA
                    AND v.VE_TIPO=tp.TI_COD",
            $conexao_params, $conexao_options);

            if(sqlsrv_num_rows($sql_info_item) > 0) {               

                $info_item = sqlsrv_fetch_array($sql_info_item);

                $itens[$cont]['dia'] = utf8_encode($info_item['ED_NOME']);
                $itens[$cont]['tipo'] = utf8_encode($info_item['TI_NOME']);


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

                $item_valores = $item_valor * $item_qtde;
                $valor_ingressos += $item_valores;

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
                
                $sql_adicionais = sqlsrv_query($conexao, "SELECT
                        lia.LIA_COD,
                        v.*,
                        vv.*
                    FROM
                        loja_itens_adicionais lia,
                        vendas_adicionais v,
                        vendas_adicionais_valores vv 
                    WHERE
                        v.VA_COD=lia.LIA_ADICIONAL
                        AND lia.LIA_COMPRA='$loja_cod'
                        AND vv.VAV_ADICIONAL=v.VA_COD 
                        AND lia.LIA_ITEM IN (SELECT LI_COD FROM loja_itens WHERE LI_COMPRA='$loja_cod' AND LI_INGRESSO='$item_ingresso' AND D_E_L_E_T_='0')
                        AND lia.D_E_L_E_T_='0'
                        AND vv.VAV_BLOCK=0
                        AND vv.D_E_L_E_T_=0
                        AND v.VA_BLOCK=0
                        AND v.D_E_L_E_T_=0
                    ORDER BY vv.VAV_INCLUSO DESC",
                $conexao_params, $conexao_options);

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
                
                //Atualizar
                $item_anterior = $item_ingresso;
                
            } //for iitemvaga
            //-----------------------------------------------------------------------------//
            
            $item_total_valores = $item_valores + $item_valor_adicionais;
            $valor_final += ($item_total_valores);
            $produto_valor_unitario = number_format(($item_total_valores / $item_qtde), 2, '', '');
            $i++;
            $cont++;
        }
    }
    
    if($delivery) {
        if(!$vendas_adicionais_delivery['incluso']) $valor_adicionais += $vendas_adicionais_delivery['valor'];
    }               
    
    //$valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;
    $valor = $valor_parcial = $valor_ingressos + $valor_adicionais + $delivery_valor;
    $desconto = 0;      
    if($cupom_valor_desconto > 0) $desconto = 1;
    
    /*$loja_combo_desconto = 0;
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
    }*/
    if($desconto) {
        $desconto_valores = $valor_desconto + $desconto_especial_folia + $desconto_especial_frisa + $cupom_valor_desconto;
        $valor_final -= $desconto_valores;
        $valor_desconto = number_format($desconto_valores, 2, '', '');
    }

    if($desconto_fp > 0) {
        $valor_final -= $desconto_fp;
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
        // $valor_final += $delivery_valor;
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
					<h2><?=$lg['cadastrar_endereco'];?></h2>
					<a href="#" class="fechar-modal">&times;</a>
				</header>
				<section id="conteudo">
					<form name="endereco" class="cadastro padrao" method="post" id="cadastro-endereco" action="<? echo SITE.$link_lang; ?>carrinho/endereco/?t=cadastrar" data-toggle="validator" role="form">
						<input type="hidden" id="total" value="<? echo $valor_final; ?>">
						<input type="hidden" name="cod" value="<? echo $cod; ?>" />
						<input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
						
						<p class="form-group">
							<label><?=$lg['cadastro_pais'];?></label>
							<select name="pais" class="drop" style="width: 340px;">
							<? foreach ($paises as $key => $pais) { ?>
								<option value="<? echo $pais['PAIS_SIGLA'] ?>" <? echo $pais['PAIS_SIGLA']=="BR"?"selected":"" ?>><? echo $pais['PAIS_NOME'] ?></option>
							<? } ?>
							</select>
						</p>

						<p class="cep form-group">
							<label for="cep">Cep</label>
							<input type="text" name="cep" class="input pequeno" id="cep" value="" required>
							<a class="busca-cep" href="http://www.buscacep.correios.com.br/" target="_blank"><?=$lg['nao_sei_cep'];?></a>
						</p>
						<p class="zipcode form-group" style="display: none;">
							<label for="cep">Zipcode</label>
							<input type="text" name="zipcode" class="input pequeno" id="zipzode" value="" required>
						</p>

						<div class="coluna">
							<p class="cidade form-group">
								<label for="cidade" class="control-label"><?=$lg['pagamento_cielo_cidade'];?></label>
								<input type="text" name="cidade" class="input" id="cidade" value="<? echo $endereco_cidade; ?>" required>
							</p>
							<p class="estado form-group">
								<label for="estado" class="control-label"><?=$lg['pagamento_cielo_estado'];?></label>
								<input type="text" name="estado" class="input" id="estado" value="<? echo $endereco_estado; ?>" required>
							</p>
							<div class="clear"></div>
						</div>

						<p class="form-group">
							<label for="bairro"><?=$lg['pagamento_cielo_bairro'];?></label>
							<input type="text" name="bairro" class="input" id="bairro" value="<? echo $endereco_bairro; ?>" required>
						</p>

						<p class="form-group">
							<label for="endereco"><?=$lg['pagamento_cielo_endereco'];?></label>
							<input type="text" name="endereco" class="input" id="endereco" value="<? echo $endereco_logradouro; ?>" required>
						</p>

						<p class="numero form-group">
							<label for="numero" class="control-label"><?=$lg['pagamento_cielo_numero'];?></label>
							<input type="numero" name="numero" class="input" id="numero" value="<? echo $endereco_numero; ?>" required>
						</p>
						<p class="complemento form-group">
							<label for="complemento"><?=$lg['pagamento_cielo_complemento'];?></label>
							<input type="text" name="complemento" class="input complemento" id="complemento" value="<? echo $endereco_complemento; ?>" />
						</p>
					
						<div class="selectbox coluna pequeno form-group" id="usuario-filial">
							<h3><?=$lg['pagamento_cielo_tipo_endereco'];?></h3>
							<a href="#" class="arrow"><strong></strong><span><i class="fa fa-caret-down" aria-hidden="true"></i></span></a>
							<ul class="drop">
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="Comercial" value="Comercial" required><?=$lg['pagamento_cielo_tipo_comercial'];?></label></li>
								<li><label class="item"><input type="radio" name="tipo_endereco" alt="Residencial"  value="Residencial" required><?=$lg['pagamento_cielo_tipo_residencial'];?></label></li>
							</ul>
							<div class="clear"></div>
						</div>						
						<footer>
							<input type="submit" class="input submit" value="<?=$lg['salvar'];?>" />
							<a href="#" class="cancel no-cancel coluna fechar-modal"><?=$lg['compre_ingressos_cancelar'];?></a>
						</footer>
						<div class="clear"></div>
					</form>		
				</section>
			</section>
		</section>
	</section>
</section>

<main>
	<?
	if(sqlsrv_num_rows($sql_loja) > 0) {
	?>
	
    <form method="post" id="pagamento-cartao" class="padrao infield" name="pagamento-cartao" action="<? echo SITE.$link_lang; ?>pagamento/cielo/confirmacao/">
        <!-- <section class="secao" id="compra-dados">
            <section>
                <h2><? echo $loja_nome; ?></h2>
                <p><? echo $loja_email; ?></p>
                <p><? echo formatTelefone($loja_telefone); ?></p>
            </section>
            <div class="clear"></div>
        </section> -->
        
        <input type="hidden" name="cod" value="<? echo $cod; ?>"/>
        <input type="hidden" name="cliente" value="<? echo $loja_cliente; ?>" />
        
        <section class="secao voucher">
            <section>
                <header class="titulo">
                    <h2>Voucher</h2>
                </header>

                <section id="voucher">
                    <h2>#<? echo $cod; ?></h2>
                    <span><? echo $loja_data_compra; ?></span>
                </section>
            </section>
        </section>

        <section class="secao" id="carrinho">
            <header>
                <h2><?=$lg['resumo_compra'];?></h2>
            </header>
            
            <table class="lista">
                <thead>
                    <tr>
                        <th class="dia"><?=$lg['resumo_dia'];?></th>
                        <th><?=$lg['resumo_tipo_ingresso'];?></th>
                        <th><?=$lg['resumo_quantidade'];?></th>
                        <th class="valor"><?=$lg['resumo_valor'];?></th>
                    </tr>
                </thead>
                <tbody>
                    
                    <? foreach ($itens as $key => $item): ?>
                    <tr>
                    <td class="dia"><? echo $item['dia']; ?></td>
                    <td class="tipo"><? echo $item['tipo']; ?></td>
                    <td class="qtde">
                        <? echo $item['qtd']; ?>
                    </td>
                    <td class="valor_individual">R$ <? echo $item['valor']; ?></td>
                    </tr>
                    <? endforeach; ?> 



                    <? if($desconto_fp > 0): ?>
                    <tr class="desconto">
                        <td colspan="4" class="desconto">
                            <span class="cupom">
                                Folia Tropical • <? echo ($_SESSION['language'] == 'BR' ? 'Desconto de '. $_SESSION['desconto_ft_porc'] .'%' : $_SESSION['desconto_ft_porc'] .'% discount' ) ?> 
                            </span>
                            <span class="valor">- R$ <? echo number_format($desconto_fp,2,',','.'); ?></span>
                        </td>
                    </tr>  
                    <? endif; ?>

                    <? if($cupom_cod > 0 && $desconto_fp == 0): ?>
                    <tr class="desconto">
                        <td colspan="4" class="desconto">
                            <span class="cupom">
                                <? echo $cupom_nome; ?> 

                                <? if ((0 === strpos($cupom_codigo, 'FOLIA'))) { ?>
                                • Desconto de  <? echo ($cupom_tipo == 1) ? round($cupom_valor)."%" : 'R$ '.number_format($cupom_valor, 2, ',', '.'); } ?>

                                <? if ($cupom_delete){ ?>

                                <a href="<? echo SITE ?>carrinho/cupom/?c=<? echo $cupom_cod; ?>" class="excluir confirm" title="<? echo $lg['desconto_remover_cupom']; ?> &rdquo;<? echo $cupom_nome; ?>&ldquo;">&times;</a>
                                
                                <? } ?>
                            </span>
                            <span class="valor">- R$ <? echo number_format($cupom_valor_desconto,2,',','.'); ?></span>
                            <input type="hidden" name="desconto" value="<? echo $cupom_valor_desconto; ?>" />
                        </td>
                    </tr>
                    <? endif; ?>

                   <tr class="total">
                    <td colspan="4" class="valor">
                        <strong>Total</strong>
                        <span class="valor">R$ <? echo number_format($loja_valor_total, 2, ",", "."); ?></span>
                    </td>
                    
                </tr>
            </tbody>
        </table>

        <section class="secao enderecos">
            <section>
                <header class="titulo">
                    <h2><?=$lg['pagamento_cielo_endereco_cobranca'];?></h2>
                </header>
                
                <ul class="enderecos">
                    <? 
                    $sql_enderecos =sqlsrv_query($conexao_sankhya, "SELECT
                            *
                        FROM
                            clientes_enderecos
                        WHERE
                            CE_CLIENTE=$loja_cliente
                            AND CE_BLOCK='0'
                            AND D_E_L_E_T_='0'
                        ORDER BY
                            CE_ULTIMA_ENTREGA DESC",
                    $conexao_params, $conexao_options);

                    $numRows = sqlsrv_num_rows($sql_enderecos);

                    if($numRows > 0) {

                        $i = 2;

                        $count = 1;

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

                            
                            <li class="<? if ($count == $numRows){ echo "checked"; } ?>"> 

                                <a href="#" title="<?=$lg['alterar_endereco'];?>" class="open-modal modal editar" data-width="650" data-cod="<? echo $endereco_cod ?>" data-cep="<? echo $endereco_cep ?>" data-endereco="<? echo $endereco_logradouro ?>" data-numero="<? echo $endereco_numero ?>" data-complemento="<? echo $endereco_complemento ?>" data-bairro="<? echo $endereco_bairro ?>" data-cidade="<? echo $endereco_cidade ?>" data-estado="<? echo $endereco_estado ?>" data-tipo-endereco="<? echo $endereco_tipo_endereco ?>" data-referencia="<? echo $endereco_ponto_referencia ?>" data-pais="<? echo $endereco_pais ?>"></a>
                                <? if($endereco_pais == "BR"){ ?>
                                    <p><? echo $endereco_logradouro.', '.$endereco_numero; ?><br/>
                                    <? echo $endereco_bairro; ?><br/>
                                    CEP <? echo $endereco_cep; ?><br/>
                                    <? echo $endereco_cidade.', '.$endereco_estado.' - '.$endereco_pais; ?>
                                    </p>
                                <? } else { ?>
                                    <p><? echo $endereco_logradouro.', '.$endereco_numero; ?><br/>
                                    ZipCode <? echo $endereco_cep; ?><br/>
                                    <? echo $endereco_cidade.', '.$endereco_estado.' - '.$endereco_pais; ?></p>
                                <? } ?>
                                <!-- <label type="button" class="utilizar"><input type="radio" name="endereco" value="<? echo $endereco_cod; ?>" <? echo $_SESSION['FORM-PAGAMENTO-CARTAO']['endereco']==$endereco_cod?"checked":""; ?>>Utilizar endereço</label> -->

                                <label type="button" class="utilizar"><input type="radio" name="endereco" value="<? echo $endereco_cod; ?>" <? if ($count == $numRows){ echo "checked"; } ?>><?=$lg['utilizar_endereco'];?></label>
                            </li>
                            <?

                            $count ++;
                        }
                    }
                    
                    ?>
                    <li class="novo">
                        <a href="#" class="open-modal novo" data-width="650"><?=$lg['novo_endereco'];?></a>
                    </li>
                </ul>
            </section>
            <div class="clear"></div>
        </section>
        
        <section class="secao" id="pagamento">
            
            <header class="titulo">
                <h2><?=$lg['compre_agendamento_pagamento'];?></h2>
            </header>
            
            <section id="cartao-credito">
                <div class="modelo-cartao">
                    <div class="front face">
                        <div id="list-cards"></div>

                        <p class="numero">•••• •••• •••• ••••</p>
                        <p class="nome"><?=$lg['nome_completo'];?></p>
                        <p class="validade">
                            <span><?=$lg['mes_ano'];?></span>
                            <span>Val <strong>••/••</strong>
                        </p>

                    </div>
                    <div class="back face">
                        <span class="tarja"></span>
                        <span class="assinatura"></span>
                        <p class="codigo">•••</p>
                        <p class="folia"><?
                        
                        $sql_ano_evento = sqlsrv_query($conexao, "SELECT TOP 1 EV_ANO FROM eventos WHERE EV_COD='$evento' AND EV_BLOCK=0 AND D_E_L_E_T_=0 ORDER BY EV_ANO DESC", $conexao_params, $conexao_options);
                        if(sqlsrv_num_rows($sql_ano_evento) > 0) {
                            $evento_ano = sqlsrv_fetch_array($sql_ano_evento);
                            $evento_ano = $evento_ano['EV_ANO'];

                            echo 'Folia Tropical '.$evento_ano;
                        }
                        
                        ?></folia>
                    </div>
                    <div class="info" style="position: absolute; top: 270px; color: #333;">
                        <? if($tipo == "candybox") { ?>
                        <p>Para pagamento à vista, via boleto bancário sem juros, favor enviar email para atendimento@foliatropical.com.br</p>
                        <? } else { ?>
                        <p>Para pagamento a vista ou dividido via boleto bancário, sem juros, favor enviar email para atendimento@foliatropical.com.br</p>
                        <? } ?>
                        <p>Somente o Titular do cartão estara autorizado para retirar os ingressos em caso de duvida favor entrar em contato: (21) 3404-6000 ou atendimento@grupopacifica.com.br</p>
                    </div>
                </div>
            </section>
            
            <section class="form">
                <input type="hidden" name="codigoBandeira" id="bandeira">
                <p style="color: #f3901e;"><strong><? echo $lg['compre_pagamento_titular']; ?></strong></p>
                <section id="documento" class="radio">
                    <ul>
                        <? if($cliente_pais=="BR"){ ?>                            
                        <li>
                            <label class="item checked">
                                <span><i class="fa fa-check" aria-hidden="true"></i></span>
                                <input type="radio" name="documento" value="cpf" checked="checked"/>
                                <?=$lg['cadastro_pessoa_fisica'];?>
                            </label>
                        </li>
                        <li>
                            <label class="item">
                                <span><i class="fa fa-check" aria-hidden="true"></i></span>
                                <input type="radio" name="documento" value="cnpj" />
                                <?=$lg['cadastro_pessoa_juridica'];?>
                            </label>
                        </li>
                        <? }else{ ?>
                        <li>
                            <label class="item">
                                <span><i class="fa fa-check" aria-hidden="true"></i></span>
                                <input type="radio" name="documento" value="passaporte" />
                                <?=$lg['cadastro_passaporte'];?>
                            </label>
                        </li>
                        <? } ?>
                    </ul>
                    <div class="clear"></div>
                </section>

                <p>
                    <label for="cpfcnpj"><?=$lg['pagamento_cielo_numero_documento'];?></label>
                    <input autocomplete="off" type="text" id="cpfcnpj" name="cpfcnpj" class="input" value="<? 
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
                
                <p>
                    <label id="ncard" for="numero_cartao"><?=$lg['pagamento_cielo_numero_cartao'];?></label>
                    <input autocomplete="off" type="text" id="numero_cartao" name="cartaoNumero" class="input" maxlength="16" value="<? echo $_SESSION['FORM-PAGAMENTO-CARTAO']['cartaoNumero']; ?>">
                </p>

                <p>
                    <label for="nome_titular"><?=$lg['pagamento_cielo_nome_titular'];?></label>
                    <input autocomplete="off" type="text" id="nome_titular" name="nomeTitular" class="input" value="<? echo $_SESSION['FORM-PAGAMENTO-CARTAO']['nomeTitular']; ?>">
                </p>
                
                <p class="validade coluna col-1-2">
                    <label for="validade"><?=$lg['validade'];?></label>
                    <input autocomplete="off" type="tel" id="validade" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" name="validade" maxlength="5"  class="input" value="<? echo $_SESSION['FORM-PAGAMENTO-CARTAO']['validade']; ?>">

                    <input autocomplete="off" type="hidden" id="mes_validade" name="mesValidade" maxlength=""  class="input" value="<? echo $_SESSION['FORM-PAGAMENTO-CARTAO']['mesValidade']; ?>">
                    <input autocomplete="off" type="hidden" id="ano_validade" name="anoValidade" maxlength=""  class="input" value="<? echo $_SESSION['FORM-PAGAMENTO-CARTAO']['anoValidade']; ?>">
                </p>

                <p class="codigo coluna col-1-2 last">
                    <label for="codigo_seguranca"><?=$lg['pagamento_cielo_codigo_seguranca'];?></label>
                    <input autocomplete="off" type="tel" id="codigo_seguranca" name="cartaoCodigoSeguranca" maxlength="4" class="input min" value="<? echo $_SESSION['FORM-PAGAMENTO-CARTAO']['cartaoCodigoSeguranca']; ?>">
                </p>
                
                <div class="clear"></div>
           
                <section class="selectbox parcelas">
                    <a href="#" class="arrow"><strong><?=$lg['selecione_parcelas'];?></strong><span><i class="fa fa-caret-down" aria-hidden="true"></i></span></a>
                    <ul class="drop"> 
                        <li><label class="item"><input type="radio" name="formaPagamento" value="" alt="Informe o número do cartão"/>Informe o número do cartão</label></li>
                    </ul>
                </section>
                
            </section>

            <div class="clear"></div>
            
            <section id="compra-pagamento">
                <!-- CIELO -->    
                <input type="submit" class="submit cielo" value="<?=$lg['compre_ingressos_comprar'];?>"/>    

            </section>
        </section>
        
        <div class="clear"></div>
        

    </form>
	<?
	}
	?>	
</main>
<?

//-----------------------------------------------------------------//

include 'inc/partials/footer.php';

?>
<script>
function data(v){
    v=v.replace(/D/g,"")                
    v=v.replace(/^(\d{2})(\d)/,"$1/$2")
    return v
}

$(document).ready(function(){
    
    var $cartaocredito = $("#cartao-credito");

	//select2
	$('select[name="pais"]').select2();

	$("form#pagamento-cartao input[name='documento']").radioSel('<? 
		if(empty($_SESSION['FORM-PAGAMENTO-CARTAO']['documento'])){
            
            if($cliente_pais!="BR") echo "passaporte";
			else if($cliente_pessoa=="F") echo "cpf";
			else echo "cnpj";
			
		}else{
			echo $_SESSION['FORM-PAGAMENTO-CARTAO']['documento'];
		} ?>');

	$('form input[name="cep"]:text').mask('99999-999');
    $(document).on('click','.open-modal',function(){
        $("body").addClass("modal-open");
        $("#modal header h1").html("Cadastrar endereço de cobrança​");
        $("#overlay,#modal").fadeIn("fast");
        $('form#cadastro-endereco')[0].reset();
        $('#modal #endereco-box').find('select[name="pais"]').val('BR').trigger('change');
        $('#modal #endereco-box').find('form').attr('action',site+'carrinho/endereco/?t=cadastrar');
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
        $("#modal header h1").html("Alterar endereço de cobrança​");
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
        $('#modal #endereco-box').find('form').attr('action',site+'carrinho/endereco/?t=editar');
    });

    //--------------controle dos enderecos-------------------------------------------
    $('section.enderecos ul.enderecos').on('change','li label.utilizar input[name="endereco"]',function(event) {
        $('section.enderecos ul.enderecos').find("li.checked").removeClass("checked");
        $('section.enderecos ul.enderecos').find("li").each(function(){
            if($(this).find("input[type='radio']").is(":checked")) $(this).addClass("checked");
        });
    });
    
    // $("#validade").mask("99/99");
    // $("#codigo_seguranca").mask("9?999");
    //$("#numero_cartao").mask("99999999999999?99");

    $("#nome_titular").keyup(function(e){
        var nome = $(this).val();  
        $cartaocredito.find('.nome').html(nome);
    });

    $("#codigo_seguranca").focus(function() {
        $cartaocredito.addClass('virar');
    });
    $("#codigo_seguranca").blur(function() {
       $cartaocredito.removeClass('virar');
    });

    $("#codigo_seguranca").keyup(function(e){
        var codigo = $(this).val();
        var bandeira = $('#bandeira').val();
        var exemplo = '';

        var limite = (bandeira == 'amex') ? 4 : 3;

        for (i = 0; i < limite; i++) { 
            if(codigo[i] !== undefined && codigo[i] != '_') exemplo = exemplo + codigo[i];
            else exemplo = exemplo + '•';
        }
        
        $cartaocredito.find('.codigo').html(exemplo);
    });

    $("#numero_cartao").keyup(function(e){
        var numero = $(this).val();  
        var bandeira = getCreditCardLabel(numero);
        var exemplo = '';

        //console.log(bandeira);
        $cartaocredito.find('#list-cards').removeAttr('class').addClass(bandeira);

        var limite = (bandeira == 'diners') ? 15 : 16;

        for (i = 0; i < limite; i++) { 
            if(numero[i] !== undefined) exemplo = exemplo + numero[i];
            else exemplo = exemplo + '•';

            if(i == 3) exemplo = exemplo + ' ';

            switch(bandeira) {
                case 'amex':
                case 'diners':
                    if(i == 9) exemplo = exemplo + ' ';
                break;
                
                default:
                    if(i == 7) exemplo = exemplo + ' ';
                    if(i == 11) exemplo = exemplo + ' ';
                break;
            }
        }

        $cartaocredito.find('.numero').html(exemplo);
    });

    if($('#numero_cartao').val()!=""){ $('#numero_cartao').trigger('keyup'); }

    $("#validade").keyup(function(e){
        var validade = $(this).val();  
        var mes, ano, exemplo;

        if(validade[0] !== undefined && validade[0] != '_') mes = validade[0];
        if(validade[1] !== undefined && validade[0] != '_') mes = mes + validade[1];

        if(validade[3] !== undefined && validade[3] != '_') ano = validade[3];
        if(validade[4] !== undefined && validade[4] != '_') ano = ano + validade[4];

        $("#ano_validade").val('20'+ano);
        $("#mes_validade").val(mes);

        for (i = 0; i < 5; i++) { 
            if(i == 0) {
                if(validade[i] !== undefined && validade[i] != '_') exemplo = validade[i];
                else exemplo = '•';
            } else if(i < 2) {
                if(validade[i] !== undefined && validade[i] != '_') exemplo = exemplo + validade[i];
                else exemplo = exemplo + '•';
            } if(i == 2) {
                exemplo = exemplo + '/';
            } else if(i > 2) {
                if(validade[i] !== undefined && validade[i] != '_') exemplo = exemplo + validade[i];
                else exemplo = exemplo + '•';
            }
        }

        $cartaocredito.find('.validade strong').html(exemplo);

        valor = $( "#validade" ).val();
        valorFinal = data(valor);
        $( "#validade" ).val(valorFinal);
    });

    
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
    var $cadastro = $("form.cadastro");

    $cadastro.find("input[name='cep']").blur(function(){
        //cep
        var cep = $(this).val().replace(/[^0-9\.]+/g, '');
        if (cep.length == 8) {
            $.ajax({
                    url: 'https://viacep.com.br/ws/' + cep + '/json/',
                    type: 'GET',
                    dataType: 'json'
                })
                .done(function (data) {
                    console.log(data);
                    // $('input[name="pais"]').val('Brasil');
                    $('input[name="estado"]').val(data.uf);
                    $('input[name="cidade"]').val(data.localidade);
                    $('input[name="bairro"]').val(data.bairro);
                    $('input[name="endereco"]').val(data.logradouro);
                    // if (data.complemento == "" && $('input[name="complemento"]').first().val()=="") {
                    // $('input[name="complemento"]').val(data.complemento);
                    // }
                    $('input[name="numero"]').focus();
                })
                .always(function (data) {});
        }
    });

    // $cadastro.find("input[name='cep']").blur(function(){
        
        
    //     // Pegamos o valor do input CEP
    //     var cep = $cadastro.find("input[name='cep']").val();
        
    //     // Se o CEP nÃ£o estiver em branco
    //     if(cep != '') {

    //         // Adiciona imagem de "Loading"
    //         $cadastro.find(".endereco input[name='cep']").addClass('loading');
            
    //         $.getJSON(site + "inc/busca-cep.php", {
    //             cep: cep
    //         }, function(resultado) {                
    //             $cadastro.find(".endereco input[name='cep']").removeClass('loading');

    //             //Valores
    //             $cadastro.find("input[name='endereco']").val(resultado.logradouro).blur();
    //             $cadastro.find("input[name='bairro']").val(resultado.bairro).blur();
    //             $cadastro.find("input[name='cidade']").val(resultado.cidade).blur();

    //             if($cadastro.find("input[name='estado']").data('uf') != null) $cadastro.find("input[name='estado'][data-uf='"+resultado.uf+"']").trigger('click');
    //             else $cadastro.find("input[name='estado']").radioSel(resultado.uf);

    //             //alteração para o cadastro de enderecos do pagamento
    //             $cadastro.find("input[id='estado']").val(resultado.uf);

    //             $cadastro.find("input[name='numero']").focus();
    //         });
    //     } else {
    //         // Se o campo CEP estiver em branco, apresenta mensagem de erro
    //         // alert('Para que o endereÃ§o seja completado automaticamente vocÃª deve preencher o campo CEP!');
    //     }
    //     return false;
    // });
    
    //-------------------------------------------------------------------//
    
    function getCreditCardLabel(cardNumber){

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

        $('#ncard').html('Número do cartão');
        var verifytotal = $('#total').val();
        var options = '';

        var parcelas = 1;
        switch(bandeira) {
            case 'mastercard':
            case 'diners':
            case 'visa':
            case 'elo':
                parcelas = 6;
            break;

            case 'amex':
            case 'aura':
                parcelas = 6;
            break;

            case 'discover':
                parcelas = 1;
            break;
        }

        for(i = 1; i <= parcelas; i++){
            var opcao = i+' x R$ '+ parseFloat( (verifytotal / i) ).toFixed(2).replace('.',',');
            var checked = "";
            // if(i == 1) {
            //     var checked = 'checked="checked"';
            // }
            options = options + '<li>\
                <label class="item">\
                    <input type="radio" name="formaPagamento" '+checked+' value="'+i+'" alt="'+opcao+'"/>'+opcao+'\
                </label>\
            </li>';
        }  

        $('.selectbox.parcelas').find('strong').html('Selecione o número de parcelas');
        $('.selectbox.parcelas').find('.drop').html(options);

        if(bandeira=='elo'){
            $('#bandeira').val('elo');
            return 'elo';
        }
        if(bandeira=='visa'){
            $('#bandeira').val('visa');
            return 'visa';
        }
        if(bandeira=='aura'){
            $('#bandeira').val('aura');
            return 'aura';
        }
        if(bandeira=='mastercard'){
            $('#bandeira').val('mastercard');
            return 'mastercard';
        }
        if(bandeira=='amex'){
            $('#bandeira').val('amex');
            return 'amex';
        }
        if(bandeira=='diners'){
            $('#bandeira').val('diners');
            return 'diners';
        }
        if(bandeira=='discover'){
            $('#bandeira').val('discover');
            return 'discover';
        }

        $('#ncard').html('Número do cartão <span style="color:red">(Bandeira inválida)</span>');
        $('.selectbox.parcelas').find('strong').html('Selecione o número de parcelas');
        $('.selectbox.parcelas').find('.drop').html('<li><label class="item"><input type="radio" name="formaPagamento" value="" alt="Informe o número do cartão"/>Informe o número do cartão</label></li>');
        $('#bandeira').val('');
        return '';

        return false;
	}
	//pagamento cartao
    $('form#pagamento-cartao').submit(function(event) {
        var $form=$(this);
        if(!$form.hasClass('return-false')){
            
        }else{
            if($('form#pagamento-cartao').find('input[name="endereco"]:checked').length!=1){
                swal("", "Selecione o endereço de pagamento", "warning");
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
include "conn/close.php";
include "conn/close-sankhya.php";

?>