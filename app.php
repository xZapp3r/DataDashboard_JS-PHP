<?php

//classe dashboard para armazenamento dos dados

class Dashboard {
  private $data_inicio;
  private $data_fim;
  public $numeroVendas;
  public $totalVendas;
  public $totalDespesas;
  public $clientesOur;
  public $contato = array();

  public function __get($atributo) {
    return $this->$atributo;
  }
  public function __set($atributo, $valor) {
    if(is_array($this->$atributo)){
      array_push($this->$atributo, $valor);
    }else {
      $this->$atributo = $valor;
    }
    return $this;
  }
}

//Acessando banco de dados
class Conexao {
  private $host = 'localhost';
  private $dbname = 'dashboard';
  private $user = 'root';
  private $pass = '';


  public function conectar() {
    try {
      //sessao SQL mysql:host=;dbname=, user;pass
      $conexao = new PDO(
          "mysql:host=$this->host;dbname=$this->dbname",
          "$this->user",
          "$this->pass"
      );

      //configurando charset
      $conexao->exec('set charset utf8');

      //retornando instancia conectada
      return $conexao;

    } catch (PDOException $e) {
      //capturando erro
      echo "<p>" .$e->getMessage(). '</p>';
    }
  }
}


class Bd {
  private $conexao;
  private $dashboard;
  //contruct com tipo de dado definido mais seguranca
  public function __construct(Conexao $conexao, Dashboard $dashboard) {
    $this->conexao = $conexao->conectar();
    $this->dashboard = $dashboard;
  }
  public function getNumeroVendas() {
    $query = "
    select 
      count(*) as numero_vendas 
    from 
      tb_vendas 
    where data_venda between :data_inicio and :data_fim";

    $stmt = $this->conexao->prepare($query);
    
    $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));//$dashboard->__get($data_inicio));
    $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));//$dashboard->__get($data_fim));*/
    
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_OBJ)->numero_vendas;
  }
  public function getTotalVendas($dia, $table) {
    $query = "
    select
      SUM(total) as total_vendas
    from
      $table
    where $dia between :data_inicio and :data_fim";

    $stmt = $this->conexao->prepare($query);
    
    
    // $stmt->bindValue('data_venda', 'data_venda');
    $stmt->bindValue('data_inicio', $this->dashboard->__get('data_inicio'));
    $stmt->bindValue('data_fim', $this->dashboard->__get('data_fim'));

    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_OBJ)->total_vendas;
  }
  public function getClientes() {
    $query = '
    select
      sum(cliente_ativo) as act,
      max(id) as una
    from
      tb_clientes
    where cliente_ativo = :a

    ';
    $stmt = $this->conexao->prepare($query);

    $stmt->bindValue(':a', 1);

    $stmt->execute();
    $our = $stmt->fetch(PDO::FETCH_OBJ);
     $our->una = $our->una - $our->act;
     return $our;
    //return $stmt->fetch(PDO::FETCH_OBJ)->act;
  }
  function getContato($tipo) {
    $query = '
    select
    count(*)
     from
     tb_contatos
     where
    tipo_contato = :tipo
    ';

    $stmt = $this->conexao->prepare($query);
    $stmt->bindValue(':tipo', $tipo);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_NUM);
  }

}

//instancia dos dados
$dashboard = new Dashboard();
$conexao = new Conexao() ;
$bd = new Bd($conexao, $dashboard);
//Recebendo periodo pelo GET e tratando dado
$atualiza = $_GET['atualiza'];
$atualiza = explode('-', $atualiza);
$mes = $atualiza[1];
$ano = $atualiza[0];

//Requerindo quantidade de dias pra cada mes
$diasmes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

//Configurando periodo na consulta SQL colocando nas variaveis os seguintes valores
$dashboard->__set('data_inicio', $ano.'-'.$mes.'-01');
$dashboard->__set('data_fim', $ano.'-'.$mes.'-'. $diasmes);
//Pegando dados em tempo real do servirdor para exibir na tela
$dashboard->__set('numeroVendas', $bd->getNumeroVendas());
$dashboard->__set('totalVendas', $bd->getTotalVendas('data_venda', 'tb_vendas'));
$dashboard->__set('totalDespesas', $bd->getTotalVendas('data_despesa', 'tb_despesas'));
$dashboard->__set('clientesOur', $bd->getClientes());

for($i = 1 ; $i < 4 ; $i++){
  $dashboard->__set('contato', $bd->getContato($i));
}


//troubleshoot
//print_r($bd->getNumeroVendas());
//print_r($bd->getTotalVendas());
echo json_encode($dashboard);
// print_r($bd->getTotalVendas()) ;
//  print_r($bd->getContato(2)) ;


?>