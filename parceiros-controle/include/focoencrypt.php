<?

class FocoEncrypt {

   private $ascii = array();
   private $revascii = array();
   public $usuario;
   public $senha;
   
   function iniascii(){
      for($i=32; $i <= 126; $i++) {
         $ia = ((126 + 32) - $i);
         $this->ascii[chr($i)] = chr($ia);
         $this->revascii[chr($ia)] = chr($i);
      }
   }

   function criptografar($usuario, $senha){

      $usuario = strval($usuario);
      $senha = strval($senha);

      $this->iniascii();
      
      //Criptografar
      $sizeusuario = strlen($usuario);
      $sizesenha = strlen($senha);

      $tamanhosenha = max($sizeusuario,$sizesenha);
      $encrypt = '';

      for ($i=0; $i<$tamanhosenha ; $i++) { 
         $encrypt .= ($i<$sizeusuario) ? $this->ascii[$usuario[$i]] : '{%}';
         $encrypt .= ($i<$sizesenha) ? $this->ascii[$senha[$i]] : '{#}';
      }

      return base64_encode($encrypt);
   }
   
   function descriptografar($encrypt){
      
      $this->iniascii();

      $encrypt = base64_decode($encrypt);

      //Decript
      $d_usuario = '';
      $d_senha = '';

      for ($i=0; $i<strlen($encrypt) ; $i++) {
         $var = (($i%2) ==0) ? 'd_usuario' : 'd_senha';
         
         //hash next
         if(substr($encrypt,$i,3) == '{%}') {
            $i += 3;
            $var = 'd_senha';
         }
         if(substr($encrypt,$i,3) == '{#}') {
            $i += 3;
            $var = 'd_usuario';
         }

         $$var .= $this->revascii[$encrypt[$i]];
      }

      $this->usuario = $d_usuario;
      $this->senha = $d_senha;

   }
}

?>