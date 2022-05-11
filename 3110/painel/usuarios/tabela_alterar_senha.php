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

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the tNG classes
require_once('../../includes/tng/tNG.inc.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("../../");

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Make an update transaction instance
$upd_usuarios = new tNG_update($conn_conexao);
$tNGs->addTransaction($upd_usuarios);
// Register triggers
$upd_usuarios->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_usuarios->registerTrigger("END", "Trigger_Default_Redirect", 99, "../index.php");

// Add columns
$upd_usuarios->setTable("usuarios");
$upd_usuarios->addColumn("senha", "STRING_TYPE", "POST", "senha");
$upd_usuarios->setPrimaryKey("usuario", "STRING_TYPE", "SESSION", "MM_Username");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsusuarios = $tNGs->getRecordset("usuarios");
$row_rsusuarios = mysql_fetch_assoc($rsusuarios);
$totalRows_rsusuarios = mysql_num_rows($rsusuarios);

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

<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../js/jquery.js"></script>
<script type="text/javascript" src="../../js/jquery.rsv.js"></script> 

<script>
$(document).ready(function() {
function myOnComplete() { return true; }

	// validação
	var rules = [];	
	// http://www.benjaminkeen.com/software/rsv/jquery/demo.php?page=3
		rules.push("required,senha_atual,Informe a sua senha atual.");
		rules.push("required,senha,Informe a nova senha.");
		rules.push("function,senhaConfirma");		

	$("#form1").RSV({
			onCompleteHandler: myOnComplete,
			rules: rules
	});			
	// fim - validação
	
	// confere senha antiga
	$("#senha_atual").bind('keyup',function(event){
		if($("#senha_atual").val() != ""){
			$.post("alterar_senha_confere.php", 
				  {senha_atual:$("#senha_atual").val()}, // entrada de valor
					  // retorno
					  function(valor){
						  if(valor == "1"){
							$("#senha_atual_mensagem").css("color","#0C0");
						 	$("#senha_atual_mensagem").html("Senha atual correta");
							$("#KT_Update1").removeAttr("disabled");
						  }
						  if(valor == "0"){
							$("#senha_atual_mensagem").css("color","#C00");
						 	$("#senha_atual_mensagem").html("Senha atual incorreta");
							$("#KT_Update1").attr('disabled', 'true');
						  }
					  }
					  // fim - retorno
			)
		} else {$("#senha_atual_mensagem").html("");}
    });
	// fim - confere senha antiga

});

// função para validação da confirmação de senha
function senhaConfirma()
{
	var senha = document.getElementById("senha").value;
	var re_senha = document.getElementById("re_senha").value;
	
	if(re_senha == senha){
		return true;
	}
	if(re_senha != senha){
		var field = document.getElementById("re_senha");
		return [[field, "Senhas não conferem"]];
	}
}
// fim - função para validação da confirmação de senha
</script>

</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_esquerda"><? require_once('../padrao_esquerda.php'); ?></td>
                                
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Usuário (alterar senha)</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Alterar senha</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
                A senha deverá ter no máximo 20 caracteres.
                <br><br>
                
                <form method="post" name="form1" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>">
                  <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                    <tr>
                      <td class="KT_th"><label for="senha">Senha atual:</label></td>
                      <td>
                      <input name="senha_atual" type="password" id="senha_atual" size="32" maxlength="20">
                      <div id="senha_atual_mensagem" style="font-weight:bold;"></div>
                      </td>
                    </tr>
                    <tr>
                      <td class="KT_th"><label for="senha">Nova senha:</label></td>
                      <td>
                      <input name="senha" type="password" id="senha" value="" size="32" maxlength="20" />
                      <div id="senha_mensagem" style="font-weight:bold;"></div>
                      </td>
                    </tr>
                    <tr>
                      <td class="KT_th"><label for="re_senha">Repita a nova senha:</label></td>
                      <td>
                      <input name="re_senha" type="password" id="re_senha" value="" size="32" maxlength="20" />
                            <div id="re_senha_mensagem" style="font-weight:bold;"></div>
                      </td>
                    </tr>
                    <tr class="KT_buttons">
                      <td colspan="2"><input type="submit" name="KT_Update1" id="KT_Update1" value="Alterar senha" /></td>
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
<?php mysql_free_result($usuario); ?>
