$(document).ready(function(){

    var $escolhaDia = $('section#escolha-dia');
    var $escolhaTipo = $('section#escolha-tipo');
    var $carrinho = $('section#carrinho');
    var $form = $('form.padrao');
    var $cadastro = $('section#cadastro');

    
    $form.find('.selectbox').selectbox();
    $form.find('.radio').radio();
    $form.find('.checkbox').checkbox();
    
    $form.find('.select2 select').select2().trigger('change');

    $("section#cadastro").hide();
    $("section#esqueci").hide();

    $("form.infield label, label.infield").inFieldLabels({ fadeOpacity:0.3 });

    $('a.confirm').click(function(){
        var $this=$(this);
        var msg = $(this).attr('title');
        swal({
            title: "",
            text: $this.attr('title'),
            showCancelButton: true,
            confirmButtonText: $this.data('sim'),
            cancelButtonText: $this.data('cancelar'),
        },function(){
            return location.href = $this.prop('href');
        });
        return false;
    });
    
    
    $escolhaDia.find('#check-dia.checkbox ul input[name="dia"]').change(function() {
        
        var dia = $(this).val();
        
        $.post(site + "carrinho/selecionar/", {
            dia: dia
        }, function(resposta) {
            if(resposta.sucesso) {
                
                //adicionar classe fade nos outros dias
                $escolhaDia.find('#check-dia.checkbox ul input[name="dia"]').each(function(){
                    if($(this).val() != dia) {
                        $(this).closest(".item").addClass("faded");
                    } else {
                        $(this).closest(".item").removeClass("faded");
                    }
                });

                $escolhaTipo.find('#check-tipo.checkbox > ul').html(resposta.itens);

            } else {
                swal({
                    title: "Ingressos esgotados",
                    text: "No momento não temos nenhum ingresso para este dia.",
                    html: true,
                    type: "error"
                });
            }
        }, 'json');
    });

    // $carrinho.find('td.qtde select[name^="quantidade"]').change(function(){
    //     var $qtde = $(this);
    //     var qtd = parseInt($qtde.val());
        
    //     //Total 
    //     var total = 0.00;
    //     var valor = parseFloat($qtde.closest('td.qtde').find('input[name="valor"]').val());
        
    //     total = parseFloat(valor*qtd);
    //     total = total.toFixed(2);

    //     $qtde.closest('td.qtde').find('input[name="valortotal"]').val(total);

    //     total = total.replace(".",",");
    //     if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

    //     $qtde.closest('tr').find('td.valor span.valor').html('R$ '+ total);


    //     //Geral
    //     var geral = 0.00;
    //     $carrinho.find('td.qtde input[name="valortotal"]').each(function() {
    //         var valor = parseFloat($(this).val());
    //         geral += valor;
    //     });
        
    //     geral = geral.toFixed(2);
    //     var final = geral;
    //     var reload = false;

    //     geral = geral.replace(".",",");
    //     if(geral.length > 6) geral = geral.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

    //     $carrinho.find('tr.total td.valor').html('R$ '+ geral);

    //     if($carrinho.find('td.total-final span.valor')[0]) {

    //         // Valor final com desconto
    //         var desconto = parseFloat($carrinho.find('tr.desconto input[name="desconto"]').val());
    //         desconto.toFixed(2);
    //         if(desconto > 0) {
    //             final = final - desconto;
    //             reload = true;
    //         } else {

    //             final = final.toFixed(2);
    //             final = final.replace(".",",");
    //             if(final.length > 6) final = final.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
        
    //             $carrinho.find('td.total-final span.valor').html('R$ '+ final);
                
    //         }    
    //     }

    //     //Quantidade
    //     var key = $qtde.attr('rel');

    //     //Enviar a quantidade para sessao
    //     $.get(site + "carrinho/adicionar/", {
    //         quantidade: qtd,
    //         a: 'quantidade',
    //         c: key
    //     }, function() {
    //         console.log(reload);
    //         if(!(qtd > 0) || reload) window.location.reload();
    //     }, 'json');
    // });

    $("[type='number']").keypress(function (evt) {
        evt.preventDefault();
    });

    $carrinho.find('td.qtde input[name^="quantidade"]').change(function(){
        var $qtde = $(this);
        var qtd = parseInt($qtde.val());
        
        //Total 
        var total = 0.00;
        var valor = parseFloat($qtde.closest('td.qtde').find('input[name="valor"]').val());
        
        total = parseFloat(valor*qtd);
        total = total.toFixed(2);

        $qtde.closest('td.qtde').find('input[name="valortotal"]').val(total);

        total = total.replace(".",",");
        if(total.length > 6) total = total.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

        $qtde.closest('tr').find('td.valor span.valor').html('R$ '+ total);

        //Geral
        var geral = 0.00;
        $carrinho.find('td.qtde input[name="valortotal"]').each(function() {
            var valor = parseFloat($(this).val());
            geral += valor;
        });
        
        geral = geral.toFixed(2);
        var final = geral;
        var reload = false;

        geral = geral.replace(".",",");
        if(geral.length > 6) geral = geral.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

        $carrinho.find('tr.total td.valor').html('R$ '+ geral);

        if($carrinho.find('td.total-final span.valor')[0]) {

            // Valor final com desconto
            var desconto = parseFloat($carrinho.find('tr.desconto input[name="desconto"]').val());
            desconto.toFixed(2);
            if(desconto > 0) {
                final = final - desconto;
                reload = true;
            } else {

                final = final.toFixed(2);
                final = final.replace(".",",");
                if(final.length > 6) final = final.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
        
                $carrinho.find('td.total-final span.valor').html('R$ '+ final);
                
            }    
        }

        //Quantidade
        var key = $qtde.attr('rel');

        //Enviar a quantidade para sessao
        $.get(site + "carrinho/adicionar/", {
            quantidade: qtd,
            a: 'quantidade',
            c: key
        }, function() {
            console.log(reload);
            if(!(qtd > 0) || reload) window.location.reload();
        }, 'json');
    });


    // Cupom
    $carrinho.find('a.adicionar-cupom').click(function(){
        var cupom = $('input#compra-parceiro').val();

        $.post(site + "carrinho/cupom/", {
            cupom: cupom
        }, function(resposta) {

            console.log(resposta);
            
            swal({
                title: "Cupom Desconto/Parceiro",
                text: resposta.mensagem,
                html: true,
                type: resposta.sucesso ? "success":"error"
            }, function() {
                window.location.reload();
            });
        
        }, 'json');
    });

    $('a.cadastro').click(function(){

        $('#cadastro').slideDown('fast', function(){
            $('html, body').animate({
                scrollTop: $(this).offset().top - 200
            }, 200);
        });

        return false;
    });

    $('a.esqueci').click(function(){
        
        $('#esqueci').slideDown('fast', function(){
            $('html, body').animate({
                scrollTop: $(this).offset().top - 200
            }, 200);
        });

        return false;
    });

    //mudar formulario ao trocar pais
    $('#cadastro-endereco').on('change','select[name="pais"]',function(event) {
        var pais = $(this).val();
        if(pais=="BR"){
            $(this).closest('form').find('input[name="bairro"]').attr('data-validate', 'true').closest('.form-group').show();
            $(this).closest('form').find('input[name="cep"]').attr('data-validate', 'true').closest('.form-group').show();
            $(this).closest('form').find('input[name="zipcode"]').attr('data-validate', 'false').val('').closest('.form-group').hide();
            $('#cadastro-endereco').validator('update');
        }else{
            $(this).closest('form').find('input[name="bairro"]').attr('data-validate', 'false').val('').closest('.form-group').hide();
            $(this).closest('form').find('input[name="cep"]').attr('data-validate', 'false').val('').closest('.form-group').hide();
            $(this).closest('form').find('input[name="zipcode"]').attr('data-validate', 'true').closest('.form-group').show();
            $('#cadastro-endereco').validator('update');
        }
    });

    if($cadastro[0]) {

        $cadastro.find('input[name^="data"]:text').mask('99/99/9999');

        var cadastrocpfcnpj = $cadastro.find('input[name="cpfcnpj"]:text').val();
        var legendas = jQuery.parseJSON($cadastro.find('input[name="legendas"]').val());
    
        $cadastro.find('input[name="cpfcnpj"]:text').mask('999.999.999-99').val(cadastrocpfcnpj);
        
        $cadastro.find('input[name="pessoa"]:radio').change(function(){
            
            var pessoa = $(this).val();
            var $cpfcnpj = $('#cadastro-cpfcnpj-box');
            var $nome = $('#cadastro-nome-box');
            if($('#cadastro-sobrenome-box')[0]) var $sobrenome = $('#cadastro-sobrenome-box');
            var $razao = $('#cadastro-razao-box');
            var $datanasc = $('#cadastro-data-nascimento-box');
    
            var cadastrocpfcnpj = $cadastro.find('input[name="cpfcnpj"]:text').val();
    
            switch(pessoa) {
                case 'F':
                    $cpfcnpj.find('label').html(legendas.cpf+':');
                    $('label[for="cadastro-passaporte"]').html(legendas.passaporte);
                    $('label[for="cadastro-cpfcnpj"]').html(legendas.cpf);
    
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('999.999.999-99').val(cadastrocpfcnpj);
                    $nome.find('label').html(legendas.nome+':');
                    if($('#cadastro-sobrenome-box')[0]) $sobrenome.slideDown('fast').find('input[name="sobrenome"]:text').removeAttr('disabled');
                    $datanasc.find('label').html(legendas.datanascimento+':');
                    $razao.slideUp('fast').find('input[name="razao"]:text').attr('disabled', true);
                break;
    
                case 'J':
                    $cpfcnpj.find('label').html(legendas.cnpj+':');
                    $('label[for="cadastro-cpfcnpj"]').html(legendas.cnpj);
                    $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('99.999.999/9999-99').val(cadastrocpfcnpj);
                    
                    $nome.find('label').html(legendas.nomefantasia+':');
                    if($('#cadastro-sobrenome-box')[0]) $sobrenome.slideUp('fast').find('input[name="sobrenome"]:text').attr('disabled', true);
                    $datanasc.find('label').html(legendas.datafundacao+':');
                    $razao.slideDown('fast').find('input[name="razao"]:text').removeAttr('disabled');
                break;
            }
        });
    
        $cadastro.find('select[name="pais"]').change(function(event) {
            var pais = $(this).val();
            if(pais=="BR"){
                $('#cadastro-cpfcnpj-box').find('input').attr('name','cpfcnpj').mask('999.999.999-99');
                $('#cadastro-cpfcnpj-box').find('label').html(legendas.cpf+':');
                $('#cadastro-pessoa').show();
            }else{
                $('#pessoa_fisica').closest('.item').trigger('click');
                $('#cadastro-cpfcnpj-box').find('label').html(legendas.passaporte+':');
                $('#cadastro-cpfcnpj-box').find('input').attr('name','passaporte').unmask();
                $('#cadastro-pessoa').hide();
            }
        });

        $form.find('input.input').focus(function(){
            $(this).css("background-color","#f2f2f2");
        });

        $form.find('input.input').blur(function(){
            $(this).css("background-color","#e1e1e1");
        });
    }

});


