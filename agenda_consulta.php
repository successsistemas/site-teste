<?php 
require_once('restrito.php');
require_once('Connections/conexao.php');

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

// entrada
$id_agenda = "-1";
if (isset($_POST['id_agenda'])) {
  $id_agenda = $_POST['id_agenda'];
}
// fim - entrada

// se existe filtro de id_agenda (tira da verificação o agendamento que está sendo Finalizado/Reagendado)
$where = "and 1=1";
if($id_agenda > 0) {
	$colname_id_agenda = GetSQLValueString($id_agenda, "int");
	$where = " and id_agenda <> '$colname_id_agenda' ";
}
// fim - se existe filtro de id_agenda (tira da verificação o agendamento que está sendo Finalizado/Reagendado)

$usuario_responsavel = "-1";
if (isset($_POST['usuario_responsavel'])) {

	// busca usuario_responsavel selecionado
	mysql_select_db($database_conexao, $conexao);
	$query_usuario_responsavel_selecionado = sprintf("
											 SELECT IdUsuario, nome 
											 FROM usuarios 
											 WHERE nome = %s", 
											 GetSQLValueString($_POST['usuario_responsavel'], "text"));
	$usuario_responsavel_selecionado = mysql_query($query_usuario_responsavel_selecionado, $conexao) or die(mysql_error());
	$row_usuario_responsavel_selecionado = mysql_fetch_assoc($usuario_responsavel_selecionado);
	$totalRows_usuario_responsavel_selecionado = mysql_num_rows($usuario_responsavel_selecionado);
	
	$IdUsuario = $row_usuario_responsavel_selecionado['IdUsuario'];
	
	mysql_free_result($usuario_responsavel_selecionado);
	// fim - busca usuario_responsavel selecionado
	
} else if (isset($_POST['id_usuario_responsavel'])) {
	
	$IdUsuario = $_POST['id_usuario_responsavel'];
	
}

$data_inicio = "-1";
if (isset($_POST['data_inicio'])) {
  $data_inicio = $_POST['data_inicio'];
}

$data_fim = "-1";
if (isset($_POST['data_fim'])) {
  $data_fim = $_POST['data_fim'];
}
// fim - entrada

// agenda_consulta
mysql_select_db($database_conexao, $conexao);
$query_agenda_consulta = sprintf("
											SELECT data_inicio, data
											FROM agenda 
											WHERE (status = 'a' or status = 'g') and id_usuario_responsavel = %s $where 
											ORDER BY id_agenda ASC", 
											GetSQLValueString($IdUsuario, "int"));
$agenda_consulta = mysql_query($query_agenda_consulta, $conexao) or die(mysql_error());
$row_agenda_consulta = mysql_fetch_assoc($agenda_consulta);
$totalRows_agenda_consulta = mysql_num_rows($agenda_consulta);
// fim - agenda_consulta

$conflita = 0;

// se encontrado algum agendamento
if($totalRows_agenda_consulta > 0){
	do {
	
		// data_inicio
		if(
			  (
			   (strtotime($row_agenda_consulta['data_inicio']) >= strtotime($data_inicio)) and 
			   (strtotime($row_agenda_consulta['data_inicio']) <= strtotime($data_fim))
			  )
		){
			$conflita = 1;
		}
		// fim - data_inicio
	
	
		// data_fim    
		if(
			  (
			   (strtotime($row_agenda_consulta['data']) >= strtotime($data_inicio)) and 
			   (strtotime($row_agenda_consulta['data']) <= strtotime($data_fim))
			  )
		){
			$conflita = 1;
		}
		// fim - data_fim
	
	
		// data_inicio e data_fim
		if(
			  (
			   (strtotime($row_agenda_consulta['data_inicio']) <= strtotime($data_inicio)) and 
			   (strtotime($row_agenda_consulta['data']) >= strtotime($data_fim))
			  )
		){
			$conflita = 1;
		}
		// fim - data_inicio e data_fim
	
	} while ($row_agenda_consulta = mysql_fetch_assoc($agenda_consulta));
}
// fim - se encontrado algum agendamento

mysql_free_result($agenda_consulta);

echo $conflita;
?>