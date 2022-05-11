<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

require_once('../parametros.php');

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

// update
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "usuario_atual")) {

	if ( isset($_POST['aniversario']) and $_POST['aniversario'] != "" ) {
		$data_data = substr($_POST['aniversario'],0,10);
		$_POST['aniversario'] = implode("-",array_reverse(explode("-",$data_data)));
	} else {
		$_POST['aniversario'] = NULL;
	}	
		
	// upload
	$updateSQL = sprintf("
					   UPDATE usuarios 
					   SET aniversario= %s, telefone=%s 
					   WHERE IdUsuario=%s", 
					   GetSQLValueString($_POST['aniversario'], "date"),
					   GetSQLValueString($_POST['telefone'], "text"),
					   GetSQLValueString($row_usuario['IdUsuario'], "int"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result1 = mysql_query($updateSQL, $conexao) or die(mysql_error());
	// fim - upload
	
	// redireciona
	$updateGoTo = "../index.php?acao=sucesso";
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo); 
	exit;
	// fim - redireciona
  
}
// fim - update


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright © Success Sistemas - Todos os direitos reservados." />
<title>Área do Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<style>
label.error { color: red; display: none; font-size: 11px; margin-top: 0px; padding-top: 0px; }	
</style>
<script src="../../js/jquery.js"></script>
<script type="text/javascript" src="../../funcoes.js"></script>

<script src="../../js/jquery.metadata.js" type="text/javascript"></script>
<script type="text/javascript" src="../../js/jquery.validate.js"></script>

<script type="text/javascript" src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript">
jQuery.validator.addMethod("dateBR", function(value, element) {            
     //contando chars 
    if(value.length!=10) return false;
    // verificando data
    var data        = value;
    var dia         = data.substr(0,2);
    var barra1      = data.substr(2,1);
    var mes         = data.substr(3,2);         
    var barra2      = data.substr(5,1);
    var ano         = data.substr(6,4);         
    if(data.length!=10||barra1!="-"||barra2!="-"||isNaN(dia)||isNaN(mes)||isNaN(ano)||dia>31||mes>12)return false; 
    if((mes==4||mes==6||mes==9||mes==11)&&dia==31)return false;
    if(mes==2 && (dia>29||(dia==29&&ano%4!=0)))return false;
    if(ano < 1900)return false;
    return true;        
}, "Informe uma data válida");  // Mensagem padrão 

$.metadata.setType("attr", "validate");
$(document).ready(function() {
	
	// validação
	$("#usuario_atual").validate({
		rules: {
			aniversario: {
			  required: true,
			  dateBR: true
			},
			telefone: {
			  required: true
			}
		},
		messages: {
			aniversario: " Informe uma data válida",
			telefone: " Informe um telefone"
		},
		onkeyup: false, 
		submitHandler: function(form) {
			form.submit();
		}
	});
	// fim - validação	
	
	// mascara
	$('#aniversario').mask('99-99-9999',{placeholder:" "});
	// fim - mascara
	
});
</script>

</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Área do parceiro - Dados</td>
                        <td align="right"></td>
                    </tr>
                </table>
                </div>
                <!-- fim - titulo -->
                
                <div style="margin-top: 10px; font-weight: bold;">Informe a data de anivesário e telefone do usuário atual para continuar:</div>               
                
          		<div style="border: 1px solid #CCC; padding: 5px; margin-top: 10px;">
                <form id="usuario_atual" name="usuario_atual" method="POST" action="<?php echo $editFormAction; ?>" class="cmxform">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="left" valign="middle" width="100" height="40">Nascimento: </td>
                
                	<td align="left" valign="middle">
                    <input type="text" name="aniversario" id="aniversario" size="20" maxlength="10" value="<? if($row_usuario['aniversario'] <> NULL){echo date('d-m-Y', strtotime($row_usuario['aniversario']));} ?>" />
                    </td>
                    
                	<td align="right" valign="middle">
					<input type="submit" name="salvar" value="Salvar" />
					<input type="hidden" name="MM_update" value="usuario_atual" />
                    </td>
                    
                </tr>
                <tr>
                  <td align="left" valign="middle" height="40">Telefone:</td>
                  <td align="left" valign="middle">
                  <input type="text" name="telefone" id="telefone" size="20" maxlength="30" value="<? echo $row_usuario['telefone']; ?>" />
                  </td>
                  <td align="right" valign="middle">&nbsp;</td>
                </tr>
                </table>  
                </form>
                </div>
                 
              </td>
                
            </tr>
        </table>
  	</div>
</div>
<!-- fim - corpo -->

<div class="rodape"><? require_once('../padrao_rodape.php'); ?></div>

</body>
</html>
<?php
mysql_free_result($usuario);
 ?>
