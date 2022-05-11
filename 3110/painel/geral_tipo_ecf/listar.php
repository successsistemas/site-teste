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
$tfi_listgeral_tipo_ecf1 = new TFI_TableFilter($conn_conexao, "tfi_listgeral_tipo_ecf1");
$tfi_listgeral_tipo_ecf1->addColumn("geral_tipo_ecf.IdTipoEcf", "NUMERIC_TYPE", "IdTipoEcf", "=");
$tfi_listgeral_tipo_ecf1->addColumn("geral_tipo_ecf.titulo", "STRING_TYPE", "titulo", "%");
$tfi_listgeral_tipo_ecf1->Execute();

// Sorter
$tso_listgeral_tipo_ecf1 = new TSO_TableSorter("rsgeral_tipo_ecf1", "tso_listgeral_tipo_ecf1");
$tso_listgeral_tipo_ecf1->addColumn("geral_tipo_ecf.IdTipoEcf");
$tso_listgeral_tipo_ecf1->addColumn("geral_tipo_ecf.titulo");
$tso_listgeral_tipo_ecf1->setDefault("geral_tipo_ecf.IdTipoEcf");
$tso_listgeral_tipo_ecf1->Execute();

// Navigation
$nav_listgeral_tipo_ecf1 = new NAV_Regular("nav_listgeral_tipo_ecf1", "rsgeral_tipo_ecf1", "../../", $_SERVER['PHP_SELF'], 50);

//NeXTenesio3 Special List Recordset
$maxRows_rsgeral_tipo_ecf1 = $_SESSION['max_rows_nav_listgeral_tipo_ecf1'];
$pageNum_rsgeral_tipo_ecf1 = 0;
if (isset($_GET['pageNum_rsgeral_tipo_ecf1'])) {
  $pageNum_rsgeral_tipo_ecf1 = $_GET['pageNum_rsgeral_tipo_ecf1'];
}
$startRow_rsgeral_tipo_ecf1 = $pageNum_rsgeral_tipo_ecf1 * $maxRows_rsgeral_tipo_ecf1;

// Defining List Recordset variable
$NXTFilter_rsgeral_tipo_ecf1 = "1=1";
if (isset($_SESSION['filter_tfi_listgeral_tipo_ecf1'])) {
  $NXTFilter_rsgeral_tipo_ecf1 = $_SESSION['filter_tfi_listgeral_tipo_ecf1'];
}
// Defining List Recordset variable
$NXTSort_rsgeral_tipo_ecf1 = "geral_tipo_ecf.IdTipoEcf";
if (isset($_SESSION['sorter_tso_listgeral_tipo_ecf1'])) {
  $NXTSort_rsgeral_tipo_ecf1 = $_SESSION['sorter_tso_listgeral_tipo_ecf1'];
}
mysql_select_db($database_conexao, $conexao);

$query_rsgeral_tipo_ecf1 = "SELECT geral_tipo_ecf.IdTipoEcf, geral_tipo_ecf.titulo FROM geral_tipo_ecf WHERE {$NXTFilter_rsgeral_tipo_ecf1} ORDER BY {$NXTSort_rsgeral_tipo_ecf1}";
$query_limit_rsgeral_tipo_ecf1 = sprintf("%s LIMIT %d, %d", $query_rsgeral_tipo_ecf1, $startRow_rsgeral_tipo_ecf1, $maxRows_rsgeral_tipo_ecf1);
$rsgeral_tipo_ecf1 = mysql_query($query_limit_rsgeral_tipo_ecf1, $conexao) or die(mysql_error());
$row_rsgeral_tipo_ecf1 = mysql_fetch_assoc($rsgeral_tipo_ecf1);

if (isset($_GET['totalRows_rsgeral_tipo_ecf1'])) {
  $totalRows_rsgeral_tipo_ecf1 = $_GET['totalRows_rsgeral_tipo_ecf1'];
} else {
  $all_rsgeral_tipo_ecf1 = mysql_query($query_rsgeral_tipo_ecf1);
  $totalRows_rsgeral_tipo_ecf1 = mysql_num_rows($all_rsgeral_tipo_ecf1);
}
$totalPages_rsgeral_tipo_ecf1 = ceil($totalRows_rsgeral_tipo_ecf1/$maxRows_rsgeral_tipo_ecf1)-1;
//End NeXTenesio3 Special List Recordset

$nav_listgeral_tipo_ecf1->checkBoundries();

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
  duplicate_navigation: true,
  row_effects: false,
  show_as_buttons: false,
  record_counter: false
}
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_IdTipoEcf {width: 80px; overflow:hidden;}
  .KT_col_titulo {width: 470px; overflow:hidden;}
  
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
                        <td align="left">Tipo de ECF <?php
  $nav_listgeral_tipo_ecf1->Prepare();
  require("../../includes/nav/NAV_Text_Statistics.inc.php");
?></td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Tipo de ECF</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                  <div class="KT_tng" id="listgeral_tipo_ecf1">
                    <div class="KT_tnglist">
                      <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
                        <div class="KT_options"> <a href="<?php echo $nav_listgeral_tipo_ecf1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
                          <?php 
  // Show IF Conditional region1
  if (@$_GET['show_all_nav_listgeral_tipo_ecf1'] == 1) {
?>
                            <?php echo $_SESSION['default_max_rows_nav_listgeral_tipo_ecf1']; ?>
                            <?php 
  // else Conditional region1
  } else { ?>
                            <?php echo NXT_getResource("all"); ?>
                            <?php } 
  // endif Conditional region1
?>
<?php echo NXT_getResource("records"); ?></a> &nbsp;
                          &nbsp; </div>
                        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                          <thead>
                            <tr class="KT_row_order">
                              <th> <input type="checkbox" name="KT_selAll" id="KT_selAll"/>
                              </th>
                              <th id="IdTipoEcf" class="KT_sorter KT_col_IdTipoEcf <?php echo $tso_listgeral_tipo_ecf1->getSortIcon('geral_tipo_ecf.IdTipoEcf'); ?>"> <a href="<?php echo $tso_listgeral_tipo_ecf1->getSortLink('geral_tipo_ecf.IdTipoEcf'); ?>">Código</a></th>
                              <th id="titulo" class="KT_sorter KT_col_titulo <?php echo $tso_listgeral_tipo_ecf1->getSortIcon('geral_tipo_ecf.titulo'); ?>"> <a href="<?php echo $tso_listgeral_tipo_ecf1->getSortLink('geral_tipo_ecf.titulo'); ?>">Título</a></th>
                              <th>&nbsp;</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if ($totalRows_rsgeral_tipo_ecf1 == 0) { // Show if recordset empty ?>
                              <tr>
                                <td colspan="4"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
                              </tr>
                              <?php } // Show if recordset empty ?>
                            <?php if ($totalRows_rsgeral_tipo_ecf1 > 0) { // Show if recordset not empty ?>
                              <?php do { ?>
                                <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                                  <td><input type="checkbox" name="kt_pk_geral_tipo_ecf" class="id_checkbox" value="<?php echo $row_rsgeral_tipo_ecf1['IdTipoEcf']; ?>" />
                                    <input type="hidden" name="IdTipoEcf" class="id_field" value="<?php echo $row_rsgeral_tipo_ecf1['IdTipoEcf']; ?>" /></td>
                                  <td><div class="KT_col_IdTipoEcf"><?php echo $row_rsgeral_tipo_ecf1['IdTipoEcf']; ?></div></td>
                                  <td><div class="KT_col_titulo"><?php echo KT_FormatForList($row_rsgeral_tipo_ecf1['titulo'], 70); ?></div></td>
                                  <td><a class="KT_edit_link" href="tabela.php?IdTipoEcf=<?php echo $row_rsgeral_tipo_ecf1['IdTipoEcf']; ?>&amp;KT_back=1"><?php echo NXT_getResource("edit_one"); ?></a> <a class="KT_delete_link" href="#delete"><?php echo NXT_getResource("delete_one"); ?></a></td>
                                </tr>
                                <?php } while ($row_rsgeral_tipo_ecf1 = mysql_fetch_assoc($rsgeral_tipo_ecf1)); ?>
                              <?php } // Show if recordset not empty ?>
                          </tbody>
                        </table>
                        <div class="KT_bottomnav">
                          <div>
                            <?php
            $nav_listgeral_tipo_ecf1->Prepare();
            require("../../includes/nav/NAV_Text_Navigation.inc.php");
          ?>
                          </div>
                        </div>
                        <div class="KT_bottombuttons">
                          <div class="KT_operations"> <a class="KT_edit_op_link" href="#" onclick="nxt_list_edit_link_form(this); return false;"><?php echo NXT_getResource("edit_all"); ?></a> <a class="KT_delete_op_link" href="#" onclick="nxt_list_delete_link_form(this); return false;"><?php echo NXT_getResource("delete_all"); ?></a></div>
                          <span>&nbsp;</span>
                          <select name="no_new" id="no_new">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                          </select>
                          <a class="KT_additem_op_link" href="tabela.php?KT_back=1" onclick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a></div>
                      </form>
                    </div>
                    <br class="clearfixplain" />
                  </div>
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
mysql_free_result($rsgeral_tipo_ecf1);
 
mysql_free_result($usuario); ?>