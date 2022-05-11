<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php require_once('Connections/conexao.php'); ?>
<?php
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

$colname_parceiro_dados = "-1";
$colname_parceiro_dados = $_POST["IdUsuario"]; // parceiro (IdUsuario) selecionado

// parceiro dados
mysql_select_db($database_conexao, $conexao);
$query_parceiro_dados = sprintf("
SELECT IdUsuario, nome, praca
FROM usuarios 
WHERE IdUsuario = %s", GetSQLValueString($colname_parceiro_dados, "text"));
$parceiro_dados = mysql_query($query_parceiro_dados, $conexao) or die(mysql_error());
$row_parceiro_dados = mysql_fetch_assoc($parceiro_dados);
$totalRows_parceiro_dados = mysql_num_rows($parceiro_dados);
// fim - parceiro dados
?>
<style>
.linha1{
	border-top: 1px solid #E5E5E5;
	border-bottom: 1px solid #E5E5E5;
	background-color: #F6F6F6;
	margin-top: 3px;
	margin-bottom: 3px;
	padding-top: 3px;
	padding-bottom: 3px;
	padding-left: 5px;
	padding-right: 5px;
}
.linha2{
	border-top: 1px solid #E5E5E5;
	border-bottom: 1px solid #E5E5E5;
	background-color: #FFFFFF;
	margin-top: 3px;
	margin-bottom: 3px;
	padding-top: 3px;
	padding-bottom: 3px;
	padding-left: 5px;
	padding-right: 5px;
}
</style>
<div>
    <strong>Dados do parceiro selecionado:</strong>
    
    <div class="linha1">
    <strong><?php echo utf8_encode($row_parceiro_dados['nome']); ?></strong> | 
    Praça: <?php echo $row_parceiro_dados['praca']; ?>
    </div>
    
    <input name="empresa" type="hidden" value="<?php echo $row_parceiro_dados['nome']; ?>">
    <input name="tipo_suporte" type="hidden" value="p">
    <input name="inloco" type="hidden" value="nao">
        
    <div style="padding-top: 10px; margin-left: auto; margin-right: auto; width: 140px; height: 40px;">
    <input type="submit"  name="botao_inferior" value="Gerar novo suporte" class="botao_geral2" />
    </div>
</div>
<? mysql_free_result($parceiro_dados); ?>