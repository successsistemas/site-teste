<? session_start(); ?>
<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
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


// verifica se existe o $_GET['id'] (id da solicitação)
$id_atual = "-1"; 
if (isset($_POST["id"])) { 
	$id_atual = $_POST["id"];
}
// fim - verifica se existe o $_GET['id'] (id da solicitação)

// suporte
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
							 SELECT data, id_usuario_responsavel, questionado, descricao, 
							 (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte_descricoes.id_usuario_responsavel) as usuario_responsavel 
							 FROM suporte_descricoes 
							 WHERE id_suporte = %s and tipo_postagem = 'Questionamento'
							 ORDER BY id DESC LIMIT 1", GetSQLValueString($id_atual, "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte
?>

<div class="div_suporte_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Último questionamento (Suporte núm: <? echo $_POST["id"]; ?>)
		</td>
	</tr>
</table>
</div>

<div class="div_suporte_linhas4">
Data: <? echo date('d-m-Y  H:i', strtotime($row_suporte['data'])); ?>
<br>
Usuário responsável: <? echo $row_suporte['usuario_responsavel']; ?>
<br>
Para: <? echo $row_suporte['questionado']; ?>
<br><br>
<? echo $row_suporte['descricao']; ?>
</div>
<?php mysql_free_result($suporte); ?>