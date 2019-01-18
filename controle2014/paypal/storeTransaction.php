<?php
/**
 * Armazena os dados da transação recebidos em uma notificação IPN.
 *
 * @param PDO $pdo Objeto de conexão com a base.
 * @param array $message Mensagem IPN
 *
 * @return boolean
 */


function storeTransaction($conexao, $conexao_params, $conexao_options, array $message)
{

    // $texto = '';
    // foreach ($message as $key => $value) {
    //     $texto .= $key.': '.$value.PHP_EOL;
    // }

    // escreveLog('logs/log.txt', $texto);

/*
    $stm = $pdo->prepare('
        INSERT INTO `transaction` (
            `invoice`,
            `custom`,
            `txn_type`,
            `txn_id`,
            `payer_id`,
            `currency`,
            `gross`,
            `fee`,
            `handling`,
            `shipping`,
            `tax`,
            `payment_status`,
            `pending_reason`,
            `reason_code`
        ) VALUES (
            :invoice,
            :custom,
            :txn_type,
            :txn_id,
            :payer_id,
            :currency,
            :gross,
            :fee,
            :handling,
            :shipping,
            :tax,
            :payment_status,
            :pending_reason,
            :reason_code
        );');
  
    $transaction = array_merge(array(
        'invoice' => null,
        'custom' => null,
        'txn_type' => null,
        'txn_id' => null,
        'payer_id' => null,
        'mc_currency' => null,
        'mc_gross' => null,
        'mc_fee' => null,
        'mc_handling' => null,
        'mc_shipping' => null,
        'tax' => null,
        'payment_status' => null,
        'pending_reason' => null,
        'reason_code' => null,
    ), $message);
  
    $stm->bindValue(':invoice', $transaction['invoice']);
    $stm->bindValue(':custom', $transaction['custom']);
    $stm->bindValue(':txn_type', $transaction['txn_type']);
    $stm->bindValue(':txn_id', $transaction['txn_id']);
    $stm->bindValue(':payer_id', $transaction['payer_id']);
    $stm->bindValue(':currency', $transaction['mc_currency']);
    $stm->bindValue(':gross', $transaction['mc_gross']);
    $stm->bindValue(':fee', $transaction['mc_fee']);
    $stm->bindValue(':handling', $transaction['mc_handling']);
    $stm->bindValue(':shipping', $transaction['mc_shipping']);
    $stm->bindValue(':tax', $transaction['tax']);
    $stm->bindValue(':payment_status', $transaction['payment_status']);
    $stm->bindValue(':pending_reason', $transaction['pending_reason']);
    $stm->bindValue(':reason_code', $transaction['reason_code']);
*/


    $invoice          = $message['invoice']; // número da compra
    $custom           = $message['custom'];
    $txn_type         = $message['txn_type'];
    $txn_id           = $message['txn_id'];
    $payer_id         = $message['payer_id'];
    $mc_currency      = $message['mc_currency'];
    $mc_gross         = $message['mc_gross'];
    $mc_fee           = $message['mc_fee'];
    $mc_handling      = $message['mc_handling'];
    $mc_shipping      = $message['mc_shipping'];
    $tax              = $message['tax'];
    $payment_status   = $message['payment_status'];
    $pending_reason   = $message['pending_reason'];
    $reason_code      = $message['reason_code'];


    $order_number = $invoice;

    // ob_start();
    // var_dump($conexao);
    // $output = ob_get_clean();

    // escreveLog('logs/log.txt', $output);

    

    $sql_exist_cod = sqlsrv_query($conexao, "SELECT * FROM loja_modalidade WHERE LM_COD='$order_number' AND D_E_L_E_T_=0", $conexao_params, $conexao_options);


    // escreveLog('logs/log.txt', sqlsrv_num_rows($sql_exist_cod) );
    if(sqlsrv_num_rows($sql_exist_cod) > 0) {
        $exist_cod = sqlsrv_fetch_array($sql_exist_cod);
        
        $cod = $exist_cod['LM_COMPRA'];
        $modalidade = $exist_cod['LM_MODALIDADE'];

        switch ($modalidade) {
            case 'carnaval': $tabela = 'foliatropical2014'; break;
            case 'rockinrio': $tabela = 'rockinrio'; break;
        }

        //escreveLog('logs/log.txt', $modalidade);
    }


  
    //return $stm->execute();


    /*The status of the payment:

    Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.

    *** Completed: The payment has been completed, and the funds have been added successfully to your account balance.

    Created: A German ELV payment is made using Express Checkout.

    Denied: The payment was denied. This happens only if the payment was previously pending because of one of the reasons listed for the pending_reason variable or the Fraud_Management_Filters_x variable.

    Expired: This authorization has expired and cannot be captured.

    Failed: The payment has failed. This happens only if the payment was made from your customer's bank account.

    Pending: The payment is pending. See pending_reason for more information.

    Refunded: You refunded the payment.

    Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.

    Processed: A payment has been accepted.

    Voided: This authorization has been voided.*/

    // $sql_logon_cookie = sqlsrv_query($conexao_sankhya, "SELECT * FROM TGFPAR WHERE EMAIL='$cookie_usuario' AND AD_SENHA='$cookie_senha'", $conexao_params, $conexao_options);



    if(!empty($cod) && !empty($tabela)) {
    
        switch ($payment_status) {      
            case 'Pending': $status = '0'; break; // Pendente (Para todos os meios de pagamento)
            case 'Completed': $status = '6'; break; // Pago (Para todos os meios de pagamento)
            case 'Denied': $status = '3'; break; // Negado (Somente para Cartão Crédito)
            case 'Canceled_Reversal':            // Cancelado (Para cartões de crédito)
            case 'Failed': $status = '9'; break; // Cancelado (Para cartões de crédito)
            case 'Created': $status = '1'; break; // Não Finalizado (Todos os meios de pagamento)
            case 'Processed': $status = '4'; break; // Autorizado (somente para Cartão de Crédito)
        }

        if($status == 6) $update_pagamento = " LO_PAGO=1, LO_DATA_PAGAMENTO=GETDATE(), ";
        if($status == 4) $update_pagamento = " LO_PAGO=0, LO_DATA_PAGAMENTO=GETDATE(), ";

        $sql_itens = sqlsrv_query($conexao, "UPDATE [$tabela].[dbo].[loja] SET
            $update_pagamento
            LO_STATUS_TRANSACAO='$status',
            LO_CARTAO_V2=1
        WHERE LO_COD='$cod'", $conexao_params, $conexao_options);

        if($status == 6) {

            //Banco de dados
            include("../conn/conn-sankhya.php");

            //Buscar informações do cliente

            $sql_co = sqlsrv_query($conexao, "SELECT TOP (1) LO_CLIENTE FROM [$tabela].[dbo].[loja] WHERE LO_COD=$cod", $conexao_params, $conexao_options);
            if(sqlsrv_num_rows($sql_co) > 0) {
                $co = sqlsrv_fetch_array($sql_co);
                $co_cliente = $co['LO_CLIENTE'];
            }
            

            $sql_cliente = sqlsrv_query($conexao_sankhya, "SELECT TOP 1 NOMEPARC, EMAIL, TELEFONE FROM TGFPAR WHERE CODPARC='$co_cliente'", $conexao_params, $conexao_options);


            if(sqlsrv_num_rows($sql_cliente) > 0) {

                $cliente = sqlsrv_fetch_array($sql_cliente);
                $nome = $cliente['NOMEPARC'];
                $email = $cliente['EMAIL'];
                $telefone = $cliente['TELEFONE'];

                //-----------------------------------------------------------------//

                //Envio de SMS
                $sms = "O pagamento do pedido n. $cod foi confirmado. Central de Atendimento Folia Tropical: 21 3202 6000";

                require("../../include/directcall-envio-sms.php");
                directcall($telefone, $sms);

                //-----------------------------------------------------------------//

                include("../../include/class.phpmailer.php");

                $msg = "<body>
                        <table width='350' border='0' align='center' cellpadding='0' cellspacing='0'>
                          <tr>
                            <td height='150' align='center' valign='top'><img src='".SITE."img/logo-email.png' width='200'height='150'></td>
                          </tr>
                          <tr>
                            <td align='left' valign='top'>&nbsp;</td>
                          </tr>
                          <tr>
                            <td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>Prezado (a),</font></td>
                          </tr>
                          <tr>
                            <td align='left' valign='top'>&nbsp;</td>
                          </tr>
                          <tr>
                            <td align='left' valign='top'><font face='Arial, Helvetica, sans-serif' color='#666666' size='2'>
                            Seu pagamento (pedido nº.$cod) foi confirmado, agora é só cair na Folia!<br /><br />
                            Clique <a href='http://www.foliatropical.com.br/minhas-compras/imprimir/$cod/'>aqui</a> para imprimir o seu voucher ou acesse o menu Minhas Compras no www.foliatropical.com.br<br /><br />
                            Central de Atendimento: 21 3202 6000</font></td>
                          </tr>
                          <tr>
                            <td align='left' valign='top'>&nbsp;</td>
                          </tr>
                          <tr>
                            <td align='center' height='30'><a href='http://www.foliatropical.com.br' target='_blank' style='text-decoration: none; color: #999;'><font face='Arial, Helvetica, sans-serif' color='#999' size='1'><strong>www.foliatropical.com.br</strong></font></a></td>
                          </tr>
                        </table>
                        </body>";

                
                //-----------------------------------------------------------//

                $remetente_nome = utf8_decode("Grupo Pacífica");
                $remetente_email = 'central@grupopacifica.com.br';
                $destinatario_nome = utf8_decode($nome);
                $destinatario_email = $email;
                $assunto = utf8_decode("Confirmação de pagamento do pedido $cod");
                $mensagem = utf8_decode($msg);

                enviarEmail($remetente_nome, $remetente_email, $destinatario_nome, $destinatario_email, $assunto, $mensagem);

                include("../conn/close-sankhya.php");
            }


        } // end if linha 168

    } // end if linha 147

    


    return true;
}