<?php

$config_path = "config.php";
if(file_exists($config_path))
{
   require_once($config_path);
}else{
   while(true){
      $config_path = "../".$config_path;
      if(file_exists($config_path)) break;
   }
   require_once($config_path);
}

   Class Conexao 
   {
      private $linhas;
      private $array_dados;
      public $pdo;
      public $banco;
      public $rows;
      public $vetordados;
      public $affected;

      public function __construct()
      {
         try {
               $this->pdo = new PDO("mysql:host=".DB_HOST. ';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
               $this->pdo->exec("set names utf8");
               $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              
            }catch(PDOException $e) {
              
               echo"Não conectado ao banco de dados:".$e->getMessage();
            }
      }

      public function query($sql)
      {
         $query = $this->pdo->query($sql);
         $this->linhas = $query->rowCount();
      }
      
      public function querydados($sql){
         //Realiza a pesquisa
         $dados = $this->pdo->query($sql);

         //Atribui o número de resultados
         $this->rows = $dados->rowCount();

         //Verifica o número de resultados para escolher o modo de retorno adequado
         if($this->rows > 1){
            $this->array_dados = $dados->fetchAll();
         }else{
            $this->array_dados = $dados->fetch();
         }
      }
      
      public function rows()
      {
         return $this->rows;
      }

      public function linhas()
      {
         return $this->linhas;
      }

      public function result()
      {
          return $this->array_dados;
      }
      public function arraydados()
      {
         return $this->array_dados;
      }
   }

?>



      
   




