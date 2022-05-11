<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
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

require_once('../parametros.php');
require_once('../funcao.php');

$janela = NULL;
$janela_url = NULL;
if (isset($_GET['janela'])) {
  $janela = $_GET['janela'];
  if($janela == "index"){$janela_url = "&janela=index";}
}

// usuarios
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuarios

// função que limita caracteres
function limita_caracteres($string, $tamanho, $encode = 'UTF-8') {
    if( strlen($string) > $tamanho ){
        $string = mb_substr($string, 0, $tamanho, $encode);
	}
    return $string;
}
// fim - função que limita caracteres

// comunicado
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
SELECT 
comunicado.*, 
usuarios.nome AS remetente  
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE comunicado.IdComunicado = %s and 
EXISTS (
	SELECT 'x' 
	FROM comunicado_destinatario 
	WHERE comunicado_destinatario.IdUsuario = %s and comunicado_destinatario.IdComunicado = %s
)
", 
GetSQLValueString($_GET['IdComunicado'], "int"),
GetSQLValueString($row_usuario['IdUsuario'], "int"),
GetSQLValueString($_GET['IdComunicado'], "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

// comunicado_destinatario_listar
mysql_select_db($database_conexao, $conexao);
$query_comunicado_destinatario_listar = sprintf("
SELECT comunicado_destinatario.*, 
usuarios.nome AS usuarios_nome 
FROM comunicado_destinatario 
LEFT JOIN usuarios ON comunicado_destinatario.IdUsuario = usuarios.IdUsuario 
WHERE comunicado_destinatario.IdComunicado = %s 
ORDER BY comunicado_destinatario.IdComunicadoDestinatario ASC", 
GetSQLValueString($row_comunicado['IdComunicado'], "int"));
$comunicado_destinatario_listar = mysql_query($query_comunicado_destinatario_listar, $conexao) or die(mysql_error());
$row_comunicado_destinatario_listar = mysql_fetch_assoc($comunicado_destinatario_listar);
$totalRows_comunicado_destinatario_listar = mysql_num_rows($comunicado_destinatario_listar);
// fim - comunicado_destinatario_listar
?>
<? if($totalRows_comunicado_destinatario_listar > 0){ ?>
<?php do { ?>

	<div class="texto_responder">
		<hr style="border: 1px solid #DDD;"/>
		<span style="font-weight: bold;"><? echo $row_comunicado_destinatario_listar['usuarios_nome']; ?> 
		[<? echo date('d/m/Y H:i', strtotime($row_comunicado_destinatario_listar['data_criacao'])); ?>]: 
		</span>
		<? echo $row_comunicado_destinatario_listar['texto']; ?>
	</div>
	
<?php } while ($row_comunicado_destinatario_listar = mysql_fetch_assoc($comunicado_destinatario_listar)); ?>
<? } ?>
<? 
mysql_free_result($comunicado_destinatario_listar);
mysql_free_result($usuario); 
mysql_free_result($comunicado);
?>