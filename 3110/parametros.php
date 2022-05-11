<?php
if(!isset($_SESSION)){session_start();}

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// geral_tipo_praca
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_praca = sprintf("SELECT * FROM geral_tipo_praca WHERE praca=%s", GetSQLValueString(@$_SESSION['MM_praca'], "text"));;
$geral_tipo_praca = mysql_query($query_geral_tipo_praca, $conexao) or die(mysql_error());
$row_geral_tipo_praca = mysql_fetch_assoc($geral_tipo_praca);
$totalRows_geral_tipo_praca = mysql_num_rows($geral_tipo_praca);
// fim - geral_tipo_praca

$praca_status = $row_geral_tipo_praca['status'];

// tamanho janela solicitação
$solicitacao_editar_tabela_width = 770;
$solicitacao_editar_tabela_height = 500;

// tamanho janela suporte
$suporte_editar_tabela_width = 770;
$suporte_editar_tabela_height = 500;

// tamanho janela prospeccao
$prospeccao_editar_tabela_width = 770;
$prospeccao_editar_tabela_height = 500;

// tamanho janela venda
$venda_editar_tabela_width = 770;
$venda_editar_tabela_height = 500;

// situacao padrao de solicitacao
$situacao_padrao = "situacao[]=criada&situacao[]=recebida&situacao[]=em análise&situacao[]=analisada&situacao[]=em orçamento&situacao[]=aprovada&situacao[]=em execução&situacao[]=executada&situacao[]=em testes&situacao[]=em validação&tipo[]=Correção&tipo[]=Fiscal&tipo[]=Implementação";

// situacao padrao de suporte
$suporte_padrao = "situacao[]=criada&situacao[]=analisada&situacao[]=em execução&situacao[]=em validação&situacao[]=solicitado suporte&situacao[]=solicitado visita&situacao[]=encaminhado para solicitação";

// situacao padrao de prospeccao
$prospeccao_padrao = "situacao[]=analisada&situacao[]=em negociação&situacao[]=solicitado agendamento&prospeccao_agenda_status[]=a";

// situacao padrao de venda
$venda_padrao = "situacao[]=criada&situacao[]=analisada&situacao[]=documentação pendente&situacao[]=em execução&situacao[]=em validação&venda_agenda_treinamento_status[]=a&venda_agenda_implantacao_status[]=a";

// situacao padrao de agenda
$agenda_padrao = NULL;

$geral_credito_acumulo_qtde = "6"; // em meses

mysql_free_result($parametros);
mysql_free_result($geral_tipo_praca);
?>