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

// Load the required classes
require_once('../../includes/tfi/TFI.php');
require_once('../../includes/tso/TSO.php');
require_once('../../includes/nav/NAV.php');

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Filter
$tfi_listusuario_listar1 = new TFI_TableFilter($conn_conexao, "tfi_listusuario_listar1");
$tfi_listusuario_listar1->addColumn("IdUsuario", "NUMERIC_TYPE", "IdUsuario", "=");
$tfi_listusuario_listar1->addColumn("nome", "STRING_TYPE", "nome", "%");
$tfi_listusuario_listar1->addColumn("usuario", "STRING_TYPE", "usuario", "%");
$tfi_listusuario_listar1->addColumn("praca", "STRING_TYPE", "praca", "%");
$tfi_listusuario_listar1->Execute();

// Sorter
$tso_listusuario_listar1 = new TSO_TableSorter("usuario_listar", "tso_listusuario_listar1");
$tso_listusuario_listar1->addColumn("IdUsuario");
$tso_listusuario_listar1->addColumn("nome");
$tso_listusuario_listar1->addColumn("usuario");
$tso_listusuario_listar1->addColumn("praca");
$tso_listusuario_listar1->setDefault("status DESC, nome ASC");
$tso_listusuario_listar1->Execute();

// Navigation
$nav_listusuario_listar1 = new NAV_Regular("nav_listusuario_listar1", "usuario_listar", "", $_SERVER['PHP_SELF'], 100);

$currentPage = $_SERVER["PHP_SELF"];

//NeXTenesio3 Special List Recordset
$maxRows_usuario_listar = $_SESSION['max_rows_nav_listusuario_listar1'];
$pageNum_usuario_listar = 0;
if (isset($_GET['pageNum_usuario_listar'])) {
  $pageNum_usuario_listar = $_GET['pageNum_usuario_listar'];
}
$startRow_usuario_listar = $pageNum_usuario_listar * $maxRows_usuario_listar;

// Defining List Recordset variable
$NXTFilter_usuario_listar = "1=1";
if (isset($_SESSION['filter_tfi_listusuario_listar1'])) {
  $NXTFilter_usuario_listar = $_SESSION['filter_tfi_listusuario_listar1'];
}
// Defining List Recordset variable
$NXTSort_usuario_listar = "status DESC, nome ASC";
if (isset($_SESSION['sorter_tso_listusuario_listar1'])) {
  $NXTSort_usuario_listar = $_SESSION['sorter_tso_listusuario_listar1'];
}
mysql_select_db($database_conexao, $conexao);

$query_usuario_listar = "SELECT * FROM usuarios WHERE  {$NXTFilter_usuario_listar}  ORDER BY  {$NXTSort_usuario_listar} ";
$query_limit_usuario_listar = sprintf("%s LIMIT %d, %d", $query_usuario_listar, $startRow_usuario_listar, $maxRows_usuario_listar);
$usuario_listar = mysql_query($query_limit_usuario_listar, $conexao) or die(mysql_error());
$row_usuario_listar = mysql_fetch_assoc($usuario_listar);

if (isset($_GET['totalRows_usuario_listar'])) {
  $totalRows_usuario_listar = $_GET['totalRows_usuario_listar'];
} else {
  $all_usuario_listar = mysql_query($query_usuario_listar);
  $totalRows_usuario_listar = mysql_num_rows($all_usuario_listar);
}
$totalPages_usuario_listar = ceil($totalRows_usuario_listar/$maxRows_usuario_listar)-1;
//End NeXTenesio3 Special List Recordset

$nav_listusuario_listar1->checkBoundries();

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
<script src="../../js/jquery.js"></script>

<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_LIST_SETTINGS = {
  duplicate_buttons: true,
  duplicate_navigation: false,
  row_effects: false,
  show_as_buttons: false,
  record_counter: true
}
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_IdUsuario {width:50px; overflow:hidden;}
  .KT_col_nome {width:230px; overflow:hidden;}
  .KT_col_usuario {width:150px; overflow:hidden;}
  .KT_col_praca {width:150px; overflow:hidden;}
</style>
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
                        <td align="left">Usuário <?php
  $nav_listusuario_listar1->Prepare();
  require("../../includes/nav/NAV_Text_Statistics.inc.php");
?></td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Usuário</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<div class="KT_tng" id="listusuario_listar1">
  <div class="KT_tnglist">
    <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
      <div class="KT_options"> <a href="<?php echo $nav_listusuario_listar1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
            <?php 
  // Show IF Conditional region1
  if (@$_GET['show_all_nav_listusuario_listar1'] == 1) {
?>
              <?php echo $_SESSION['default_max_rows_nav_listusuario_listar1']; ?>
              <?php 
  // else Conditional region1
  } else { ?>
              <?php echo NXT_getResource("all"); ?>
              <?php } 
  // endif Conditional region1
?>
            <?php echo NXT_getResource("records"); ?></a> &nbsp;
        &nbsp;
                <?php 
  // Show IF Conditional region2
  if (@$_SESSION['has_filter_tfi_listusuario_listar1'] == 1) {
?>
                  <a href="<?php echo $tfi_listusuario_listar1->getResetFilterLink(); ?>"><?php echo NXT_getResource("Reset filter"); ?></a>
                  <?php 
  // else Conditional region2
  } else { ?>
                  <a href="<?php echo $tfi_listusuario_listar1->getShowFilterLink(); ?>"><?php echo NXT_getResource("Show filter"); ?></a>
                  <?php } 
  // endif Conditional region2
?>
      </div>
      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
        <thead>
          <tr class="KT_row_order">
            <th> <input type="checkbox" name="KT_selAll" id="KT_selAll"/>
            </th>
            <th id="IdUsuario" class="KT_sorter KT_col_IdUsuario <?php echo $tso_listusuario_listar1->getSortIcon('IdUsuario'); ?>">Código</th>
            <th id="nome" class="KT_sorter KT_col_nome <?php echo $tso_listusuario_listar1->getSortIcon('nome'); ?>"> <a href="<?php echo $tso_listusuario_listar1->getSortLink('nome'); ?>">Nome</a> </th>
            <th id="usuario" class="KT_sorter KT_col_usuario <?php echo $tso_listusuario_listar1->getSortIcon('usuario'); ?>"> <a href="<?php echo $tso_listusuario_listar1->getSortLink('usuario'); ?>">Usuário</a> </th>
            <th id="praca" class="KT_sorter KT_col_praca <?php echo $tso_listusuario_listar1->getSortIcon('praca'); ?>"> <a href="<?php echo $tso_listusuario_listar1->getSortLink('praca'); ?>">Praça</a> </th>
            <th>&nbsp;</th>
          </tr>
          <?php 
  // Show IF Conditional region3
  if (@$_SESSION['has_filter_tfi_listusuario_listar1'] == 1) {
?>
            <tr class="KT_row_filter">
              <td>&nbsp;</td>
              <td><input type="text" name="tfi_listusuario_listar1_IdUsuario" id="tfi_listusuario_listar1_IdUsuario" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listusuario_listar1_IdUsuario']); ?>" size="40" maxlength="20" /></td>
              <td><input type="text" name="tfi_listusuario_listar1_nome" id="tfi_listusuario_listar1_nome" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listusuario_listar1_nome']); ?>" size="40" maxlength="20" /></td>
              <td><input type="text" name="tfi_listusuario_listar1_usuario" id="tfi_listusuario_listar1_usuario" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listusuario_listar1_usuario']); ?>" size="30" maxlength="20" /></td>
              <td>&nbsp;</td>
              <td><input type="submit" name="tfi_listusuario_listar1" value="<?php echo NXT_getResource("Filter"); ?>" /></td>
            </tr>
            <?php } 
  // endif Conditional region3
?>
        </thead>
        <tbody>
          <?php if ($totalRows_usuario_listar == 0) { // Show if recordset empty ?>
            <tr>
              <td colspan="6"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
            </tr>
            <?php } // Show if recordset empty ?>
          <?php if ($totalRows_usuario_listar > 0) { // Show if recordset not empty ?>
            <?php do { ?>
              <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                <td><input type="checkbox" name="kt_pk_usuarios" class="id_checkbox" value="<?php echo $row_usuario_listar['IdUsuario']; ?>" />
                    <input type="hidden" name="IdUsuario" class="id_field" value="<?php echo $row_usuario_listar['IdUsuario']; ?>" />
                </td>
                <td><div class="KT_col_IdUsuario"><?php echo $row_usuario_listar['IdUsuario']; ?></div></td>
                <td><div class="KT_col_nome" style="color: <? if($row_usuario_listar['status']=="1"){ echo "#000"; } else { echo "#F00"; } ?>">
				<?php echo KT_FormatForList($row_usuario_listar['nome'], 40); ?></div></td>
                <td><div class="KT_col_usuario"><?php echo KT_FormatForList($row_usuario_listar['usuario'], 30); ?></div></td>
                <td><div class="KT_col_praca"><?php echo KT_FormatForList($row_usuario_listar['praca'], 30); ?></div></td>
                <td><a class="KT_edit_link" href="tabela.php?IdUsuario=<?php echo $row_usuario_listar['IdUsuario']; ?>&amp;KT_back=1"><?php echo NXT_getResource("edit_one"); ?></a> <a class="KT_delete_link" href="#delete"><?php echo NXT_getResource(""); ?></a> </td>
              </tr>
              <?php } while ($row_usuario_listar = mysql_fetch_assoc($usuario_listar)); ?>
            <?php } // Show if recordset not empty ?>
        </tbody>
      </table>
      <div class="KT_bottomnav">
        <div>
          <?php
            $nav_listusuario_listar1->Prepare();
            require("../../includes/nav/NAV_Text_Navigation.inc.php");
          ?>
        </div>
      </div>
      <div class="KT_bottombuttons">       

		<input name="no_new" id="no_new" type="hidden" value="1" />
        <a class="KT_additem_op_site_link" href="tabela.php?KT_back=1" onclick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a> </div>
    </form>
  </div>
  <br class="clearfixplain" />
</div>
<font color="#FF0000">vermelho:</font> bloqueado
                
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
mysql_free_result($usuario_listar);
?>
