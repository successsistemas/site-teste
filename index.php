<? if (!isset($_SESSION)) { session_start(); } ?>

<?
if (isset($_SESSION['MM_Username'])) {
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "painel/index.php");
	exit;
}
?>

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

// *** Acessar área do parceiro
$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['usuario'])) {
	$loginUsername=$_POST['usuario'];
	$password=$_POST['senha'];
	$MM_fldUserAuthorization = "";
	$MM_redirectLoginSuccess = "painel/index.php?acao=sucesso";
	$MM_redirectLoginFailed = "index.php?acao=erro";
	$MM_redirecttoReferrer = false;
	
	mysql_select_db($database_conexao, $conexao);  

	// primeiro vamos verificar apenas se o campo usuário existe ou não E status
	$TestaUsuario__query=sprintf("SELECT usuario, senha, status FROM usuarios WHERE usuario=%s", 
										GetSQLValueString($loginUsername, "text")); 
	$TestaUsuario = mysql_query($TestaUsuario__query, $conexao) or die(mysql_error());
	$row_TestaUsuario = mysql_fetch_assoc($TestaUsuario);
	$TestaUsuarioEncontrados = mysql_num_rows($TestaUsuario);

		if($TestaUsuarioEncontrados) {
			$usuario_existe = "S";
			$usuario_status = $row_TestaUsuario['status'];
		} else {
			$usuario_existe = "N";
		}
	// fim - primeiro vamos verificar apenas se o campo usuário existe ou não E status

	$LoginRS__query=sprintf("SELECT usuario, senha, IdUsuario, praca FROM usuarios WHERE usuario=%s AND senha=%s and status = '1' ",
	GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
	
	$LoginRS = mysql_query($LoginRS__query, $conexao) or die(mysql_error());
	$row_LoginRS = mysql_fetch_assoc($LoginRS);
	$loginFoundUser = mysql_num_rows($LoginRS);

	
	// se usuário e senha existe  
	if ($loginFoundUser) {
		 $loginStrGroup = "";
		
		//declare two session variables and assign them
		$_SESSION['MM_Username'] = $loginUsername;
		$_SESSION['MM_praca'] = $row_LoginRS['praca'];
		$_SESSION['MM_UserGroup'] = $loginStrGroup;	 

		$_SESSION['MM_primeiro_acesso_controle'] = 1;
	
		if (isset($_SESSION['PrevUrl']) && false) {
		  $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
		}
		$link = $MM_redirectLoginSuccess;
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $link);
		exit;
	  }
	// se usuário e senha NÃO existem
	else {
			if($usuario_existe=="S"){
					$link1 = $MM_redirectLoginFailed ."&usuario=".$loginUsername."&status=".$usuario_status;
					echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $link1);
					exit;
			}
			if($usuario_existe=="N"){
					$link2 = $MM_redirectLoginFailed;
					echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $link2);
					exit;
			}
		}
}
// *** FIM - Acessar área do parceiro

// site_banner_principal
mysql_select_db($database_conexao, $conexao);
$query_site_banner_principal = "SELECT * FROM site_banner_principal ORDER BY IdBanner ASC";
$site_banner_principal = mysql_query($query_site_banner_principal, $conexao) or die(mysql_error());
$row_site_banner_principal = mysql_fetch_assoc($site_banner_principal);
$totalRows_site_banner_principal = mysql_num_rows($site_banner_principal);
// fim - site_banner_principal

// site_banner_inferior
mysql_select_db($database_conexao, $conexao);
$query_site_banner_inferior = "SELECT * FROM site_banner_inferior ORDER BY IdBanner ASC";
$site_banner_inferior = mysql_query($query_site_banner_inferior, $conexao) or die(mysql_error());
$row_site_banner_inferior = mysql_fetch_assoc($site_banner_inferior);
$totalRows_site_banner_inferior = mysql_num_rows($site_banner_inferior);
// fim - site_banner_inferior

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="js/jquery.js"></script>

<script src="js/jquery.validate.min.js"></script>

        <script type="text/javascript"> 
            $(document).ready( function() {
                $("#logar").validate({
                    // Define as regras
                    rules:{
                        usuario:{
                            // usuario será obrigatorio (required) e terá tamanho minimo (minLength)
                            required: true, minlength: 1
                        },
                        senha:{
                            // usuario será obrigatorio (required) e terá tamanho minimo (minLength)
                            required: true, minlength: 1
                        }
                    },
                    // Define as mensagens de erro para cada regra
                    messages:{
                        usuario:{
                            required: "Digite o usuário",
                            minlength: "O usuário deve conter, no mínimo, 1 caracteres"
                        },
                        senha:{
                            required: "Digite a senha",
                            minlength: "A senha deve conter, no mínimo, 1 caracteres"
                        }
                    }
                });
            });
        </script>
        <style type="text/css"> 
            label.error { float: none; color: red; vertical-align: top;}
        </style> 

<script type="text/javascript" src="js/thickbox.js"></script>

	<link href="css/screen.css" rel="stylesheet" type="text/css" media="screen" />	
	<script type="text/javascript" src="js/easySlider1.7.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){	
								   
			$("#slider").easySlider({
				auto: true, 
				continuous: true,
				numeric: true
			});
			
			$("#slider2").easySlider({
				auto: true, 
				continuous: true,
				controlsShow:	false,
				speed: 			400,
				pause:			4000
			});
			
		});	
	</script>
    
    
<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex,nofollow" />
<title>Success Sistemas</title>
<link href="css/guia.css" rel="stylesheet" type="text/css">
<!--[if IE 6]><script type="text/javascript" src="js/unitpngfix.js"></script><![endif]-->
</head>

<body>
<?php include('topo.php'); ?>
<table class="tabela_geral" cellpadding="0" cellspacing="0">

<tr>
	<td class="tabela_geral_acima"></td>
</tr>

<tr>
	<td class="tabela_geral_centro">

<!-- menu/conteúdo - início -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td class="menu"><?php include('menu.php'); ?></td>
<td class="conteudo">

<!-- slider -->
<? if($totalRows_site_banner_principal>1){ ?>
<div id="content">
    <div id="slider">
        <ul>	
			<?php do { ?>		
            <li><a href="<?php echo $row_site_banner_principal['site_link']; ?>"><img src="imagens/site_banner_principal/<?php echo $row_site_banner_principal['codigo']; ?>" border="0" /></a>
			<?php } while ($row_site_banner_principal = mysql_fetch_assoc($site_banner_principal)); ?>
            </li>	
        </ul>
    </div>
</div>
<? } else if ($totalRows_site_banner_principal==1){ ?>
<a href="<?php echo $row_site_banner_principal['site_link']; ?>"><img src="imagens/site_banner_principal/<?php echo $row_site_banner_principal['codigo']; ?>" border="0" /></a>
<? } ?>
<!-- fim - slider -->

<!-- conteúdo da página - início -->
<div class="conteudo_div">


  <table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td valign="top">
<div style="border:1px solid #CCCCCC; background-color:#F1F1F1; padding: 5px; margin-right: 5px; font-weight:bold; ">
Outros destaques
</div>

<div style="text-align:justify; padding-right: 5px;">
    <br>
    A Success sempre procurou melhorar os seus produtos, prova disto, está nos desafios que já vencemos. Para completar, já iniciamos a venda da versão for Windows de nosso programa e com banco de dados local sem gerenciamento ou com o banco de dados MySql.
    <br>
    <br>
    <strong>APLICAÇÃO DOS SOFTWARES SUCCESS:</strong>
    <br><br>
    Supermercados, loja de roupas, móveis, elétricas, construção, auto peças, oficinas, informática, padarias, papelarias, açougue, conveniência, eletro-eletrônicas, assistência técnica, tele-vendas/Disk-Entregas e outras aplicações comerciais compatíveis com os segmentos acima.
    <br><br>
    <strong>CONTATOS:</strong>
    <br><br>
    Todos os contatos nas diversas cidades onde a Success Sistemas atua.
</div>

	</td>
		<td width="230px" valign="top">
		  <div style="border: 1px solid #CCCCCC; width: 228px; background-image:url(imagens/logar_fundo.jpg)">
          
			<div style="text-align: right; padding: 5px;"><img src="imagens/logar_topo.png" width="201" height="46" /></div>
			
			<!-- Formulário logar -->
			<div style="width:203px; margin-left: auto; margin-right: auto;">
			<form action="<?php echo $loginFormAction; ?>" method="POST" name="logar" id="logar">
			Usuário:
			<br>
						<input type="text" name="usuario" id="usuario" value="<? if ((isset($_GET['acao']) and $_GET['acao']=='erro') and (isset($_GET['usuario']))) { echo $_GET['usuario'];} ?>" style="width: 200px;">
			<label></label>
			<br>
			Senha:
			<br>
			<input id="senha" name="senha" type="password" style="width: 200px;" />
			<label></label>
			<br>
			<div style="padding-top: 5px;"><input type="submit" value="Entrar" class="submit"></div>
			</form>
			</div>
			<!-- fim - Formulário logar -->

			<!-- Erro logar -->
            <div style="padding: 5px; text-align: center">
            <?
            // erro ao logar
            $erro = "-1";
            if (isset($_GET['acao'])) {
                  $erro = $_GET['acao'];
            }
            
            if ($erro == 'erro') { ?>
              <div style="color:#FF0000">
                    <?
                    if (isset($_GET['usuario'])) {
								if (isset($_GET['status']) and $_GET['status']=="0") {
									echo "Usuário bloqueado.";
								} else if (isset($_GET['status']) and $_GET['status']=="1") {
									echo "Senha incorreta, verifique.";
								}
                    } else {
                            echo "Usuário não existe.";
                    }
                    ?>
                    <br><br>
              </div>
            <? }
            // fim - erro ao logar
            ?>
            </div>
			<!-- fim - Erro logar -->
            
		</div>

<!-- slider2 -->
<? if($totalRows_site_banner_inferior>1){ ?>
<div id="content2" style="margin-top: 10px;">
    <div id="slider2">
        <ul>	
			<?php do { ?>		
            <li>
				<?php if($row_site_banner_inferior['site_link']!=""){ ?>
                    <a href="<?php echo $row_site_banner_inferior['site_link']; ?>" target="_blank">
                    <img src="imagens/site_banner_inferior/<?php echo $row_site_banner_inferior['codigo']; ?>" border="0" />
                    </a>
                <? } else { ?>
                    <img src="imagens/site_banner_inferior/<?php echo $row_site_banner_inferior['codigo']; ?>" border="0" />
                <? } ?>
			<?php } while ($row_site_banner_inferior = mysql_fetch_assoc($site_banner_inferior)); ?>
            </li>	
        </ul>
    </div>
</div>
<? } else if ($totalRows_site_banner_inferior==1){ ?>
	<?php if($row_site_banner_inferior['site_link']!=""){ ?>
        <a href="<?php echo $row_site_banner_inferior['site_link']; ?>" target="_blank">
        <img src="imagens/site_banner_inferior/<?php echo $row_site_banner_inferior['codigo']; ?>" border="0" />
        </a>
    <? } else { ?>
        <img src="imagens/site_banner_inferior/<?php echo $row_site_banner_inferior['codigo']; ?>" border="0" />
    <? } ?>
<? } ?>
<!-- fim - slider2 -->

		</td>
    </tr>
  </table>
</div>
<!-- conteúdo da página - fim -->
    
</td>
</tr>
</table>
<!-- menu/conteúdo - fim -->

	</td>
</tr>

<tr>
	<td class="tabela_geral_abaixo"></td>
</tr>

</table>
<?php include('creditos.php'); ?>
</body>
</html>
<?php

mysql_free_result($site_banner_principal);

mysql_free_result($site_banner_inferior);
?>
