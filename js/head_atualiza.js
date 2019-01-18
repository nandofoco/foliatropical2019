
$(document).ready(function(){   
            var $cpfcnpj = $('#cadastro-cpfcnpj-box');
            var $datanasc = $('#cadastro-data-nascimento-box');


            // $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('99.999.999/9999-99').val(cadastrocpfcnpj);
            $cpfcnpj.find('input[name="cpfcnpj"]:text').unmask().mask('999.999.999-99');
            $datanasc.find('input[name="data-nascimento"]:text').unmask().mask('99/99/9999');


});    