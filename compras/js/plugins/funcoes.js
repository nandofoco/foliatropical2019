$.fn.selectbox = function(){
    return this.each(function(){
        var $selectbox = $(this);

        /*Drop Down*/
        $selectbox.on('click', '.arrow', function(){
            
            if($selectbox.find(".drop").css("display") == 'none')
                $selectbox.addClass("ativo").find(".drop").slideDown("fast");
            else
                $selectbox.removeClass("ativo").find(".drop").slideUp("fast");
            
            return false;
        });
        
        //Sumir Drop
        $(document).click(function(event) {
            if($(event.target) != $selectbox) {
                 $selectbox.find(".drop").slideUp('fast');
                 $selectbox.removeClass("ativo");
            }
        });
        
        $selectbox.on('change', 'input[type="radio"]', function(){

            $selectbox.find("label.item.checked").removeClass("checked");
            
            $selectbox.find("label.item").each(function(){
                if($(this).find("input[type='radio']").attr("checked")) $(this).addClass("checked");
            });
            
            var selected = false;
            $selectbox.find("input[type='radio']").each(function(){
                if($(this).is(":checked")) selected = true; 
            });
            
            if(selected == true) {
                var value = $selectbox.find("input[type='radio']:checked").attr("alt");
                $selectbox.find("a.arrow strong").html(value);
            } else {
                $selectbox.find("a.arrow strong").html("Selecione");
            }
        });
    });
};

$.fn.radio = function(){
    return this.each(function(){
        var $radio = $(this);
        $radio.on('change', 'input[type="radio"]', function(){
            if($(this).hasClass('disabled')) {
                $(this).removeAttr('checked');
            } else {
                $radio.find("label.item.checked").removeClass("checked");
                $radio.find("label.item").each(function(){
                    if($(this).find("input[type='radio']").is(":checked")) $(this).addClass("checked");
                });
            }
        });
    });
};

$.fn.checkbox = function(){
    return this.each(function(){
        var $checkbox = $(this);
        $checkbox.on('change', 'input[type="checkbox"]', function(){
            if ($(this).is(":checked")) $(this).closest('label.item').addClass("checked");
            else $(this).closest('label.item').removeClass("checked");            
        });
    });
};

$.fn.validation = function(settings){
    var config = {};
    if (settings){$.extend(config, settings);}

    return this.each(function(n){

        var $validation = $(this);

        //-----------------------------------------------------------------------//

        $validation.submit(function(e){

            $validation.addClass('return-false');

            //Criando os requires
            $validation.find('input, textarea').not(':disabled, [type="submit"], [type="hidden"]').each(function(){ $(this).addClass('required'); });
            
            // Regras
            for(key in config.rules){
                if(config.rules[key].tipo !== undefined) $validation.find('input[name="'+key+'"],input[name^="'+key+'["],textarea[name="'+key+'"],textarea[name^="'+key+'["]').data('tipo', config.rules[key].tipo);
                if((config.rules[key].required !== undefined) && (config.rules[key].required === false)) $validation.find('input[name="'+key+'"],input[name^="'+key+'["],textarea[name="'+key+'"],textarea[name^="'+key+'["]').removeClass('required');
            }
            
            //-----------------------------------------------------------------------//

            var retorno = true;

            $validation.find('input.required').each(function(){
                
                switch($(this).attr('type')){
                    case 'text':

                        var valor = $(this).val();

                        if($(this).data('tipo') !== undefined) {

                            var regexp = new Array();
                            var tipo = $(this).data('tipo');

                            // Data
                            regexp['data'] = /(^\d{1,2}\/\d{1,2}\/\d{4}$)|(^\d{1,2}\/\d{1,2}$)/;
                            regexp['hora'] = /^([0-1][0-9]|[2][0-3]):[0-5][0-9]$/;
                            // regexp['email'] = /^[^0-9][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/;
                            regexp['email'] = /^[a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[@][a-zA-Z0-9_-]+([.][a-zA-Z0-9_-]+)*[.][a-zA-Z]{2,4}$/;
                            regexp['cpf'] = /^[\d]{3}\.[\d]{3}\.[\d]{3}\-[\d]{2}$/;
                            regexp['cnpj'] = /^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$/;
                            regexp['cpfcnpj'] = /(^\d{3}\.\d{3}\.\d{3}\-\d{2}$)|(^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$)/;
                            regexp['money'] = /^([0-9]{1,3}.){1,}[0-9]{2}$/;
                            regexp['cep']= /^[0-9]{5}-[0-9]{3}$/;
                            regexp['tel']= /^\(\d{2}\)\ \d{4,5}-\d{4}$/;
                            regexp['int']= /^[0-9]+$/;
                            
                            if(!regexp[tipo].test(valor)) {
                                retorno = false;
                                $(this).addClass('invalid');
                            } else {
                                $(this).removeClass('invalid');
                            }
                        }

                        if(!(valor.length > 0)){
                            retorno = false;
                            $(this).addClass('empty');
                        } else {
                            $(this).removeClass('empty');
                        }

                    break;
                    case 'password':

                        var valor = $(this).val();
                        if(!(valor.length > 0)){
                            retorno = false;
                            $(this).addClass('empty');
                        } else {
                            $(this).removeClass('empty');
                        }

                    break;
                    case 'radio':
                        
                        if($(this).data('validate') != true) {
                            var radioname = $(this).attr('name');
                            $validation.find('input[name="'+radioname+'"]').data('validate', true);
                            if($validation.find('input[name="'+radioname+'"]:checked').size() == 0){
                                retorno = false;
                                $(this).closest('.radio, .selectbox').addClass('empty');
                            } else {
                                $(this).closest('.radio, .selectbox').removeClass('empty');
                            }
                        }

                    break;
                    case 'checkbox':

                        if($(this).data('validate') != true) {
                            var checkboxname = $(this).attr('name');
                            $validation.find('input[name="'+checkboxname+'"]').data('validate', true);
                            if($validation.find('input[name="'+checkboxname+'"]:checked').size() == 0){
                                retorno = false;
                                $(this).closest('.checkbox').addClass('empty');
                            } else {
                                $(this).closest('.checkbox').removeClass('empty');
                            }
                        }

                    break;
                }
            });

            $validation.find('textarea.required').each(function(){                
                var valor = $(this).val();
                if(!(valor.length > 0)){
                    retorno = false;
                    $(this).addClass('empty');
                } else {
                    $(this).removeClass('empty');
                }
            });
            
            //-----------------------------------------------------------------------//
            
            // Retirar o informativo que o campo ja foi validado
            $validation.find('input[type="radio"].required').removeData('validate');
            $validation.find('input[type="checkbox"].required').removeData('validate');
            
            var $voltar = $validation.find('.empty, .invalid').first();

            if($voltar.closest('.item-carrinho.extra')) $voltar = $('.item-carrinho.extra');
            
            if(retorno) $validation.removeClass('return-false');
            else $voltar.ScrollTo(300);            

            return retorno;
        });

    });
};

$.fn.radioSel = function(valueToSel){

    if(arguments.length>0){
        return this.each(function(){ // itera sobre cada elemento encontrado
            if($(this).val()==valueToSel) this.click();
        })        
    }else{
        valorSelecionado = false;
        this.each(function(){ // itera sobre cada elemento encontrado
            if(this.checked){
                valorSelecionado = $(this).val();
                return valorSelecionado;
            }
        });
        return valorSelecionado;
    }
};

$.fn.cursorpos = function(){
    var input = this;
    // Internet Explorer Caret Position (TextArea)
    if (document.selection && document.selection.createRange) {
        var range = document.selection.createRange();
        var bookmark = range.getBookmark();
        var posicao = bookmark.charCodeAt(2) - 2;
    } else {
        // Firefox Caret Position (TextArea)
        if (input.setSelectionRange)
            var posicao = input.selectionStart;
    }

    return posicao;
};

$.fn.setCursorToTextEnd = function() {
    var initialVal = this.val();
    this.focus().val("").val(initialVal);    
};

function serverTime() { 
    var site = $("#base-site").val();
    var time = null; 
    $.ajax({url: site + 'include/servertime.php', 
        async: false, dataType: 'text', 
        success: function(text) { 
            time = new Date(text); 
        }, error: function(http, message, exc) { 
            time = new Date(); 
    }}); 
    return time; 
}