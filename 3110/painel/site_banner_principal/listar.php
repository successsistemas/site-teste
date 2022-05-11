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
$tfi_listsite_banner_principal1 = new TFI_TableFilter($conn_conexao, "tfi_listsite_banner_principal1");
$tfi_listsite_banner_principal1->addColumn("site_banner_principal.titulo", "STRING_TYPE", "titulo", "%");
$tfi_listsite_banner_principal1->addColumn("site_banner_principal.IdBanner", "NUMERIC_TYPE", "IdBanner", "=");
$tfi_listsite_banner_principal1->Execute();

// Sorter
$tso_listsite_banner_principal1 = new TSO_TableSorter("rssite_banner_principal1", "tso_listsite_banner_principal1");
$tso_listsite_banner_principal1->addColumn("site_banner_principal.titulo");
$tso_listsite_banner_principal1->addColumn("site_banner_principal.IdBanner");
$tso_listsite_banner_principal1->setDefault("site_banner_principal.IdBanner DESC");
$tso_listsite_banner_principal1->Execute();

// Navigation
$nav_listsite_banner_principal1 = new NAV_Regular("nav_listsite_banner_principal1", "rssite_banner_principal1", "../../", $_SERVER['PHP_SELF'], 50);

//NeXTenesio3 Special List Recordset
$maxRows_rssite_banner_principal1 = $_SESSION['max_rows_nav_listsite_banner_principal1'];
$pageNum_rssite_banner_principal1 = 0;
if (isset($_GET['pageNum_rssite_banner_principal1'])) {
  $pageNum_rssite_banner_principal1 = $_GET['pageNum_rssite_banner_principal1'];
}
$startRow_rssite_banner_principal1 = $pageNum_rssite_banner_principal1 * $maxRows_rssite_banner_principal1;

// Defining List Recordset variable
$NXTFilter_rssite_banner_principal1 = "1=1";
if (isset($_SESSION['filter_tfi_listsite_banner_principal1'])) {
  $NXTFilter_rssite_banner_principal1 = $_SESSION['filter_tfi_listsite_banner_principal1'];
}
// Defining List Recordset variable
$NXTSort_rssite_banner_principal1 = "site_banner_principal.IdBanner DESC";
if (isset($_SESSION['sorter_tso_listsite_banner_principal1'])) {
  $NXTSort_rssite_banner_principal1 = $_SESSION['sorter_tso_listsite_banner_principal1'];
}
mysql_select_db($database_conexao, $conexao);

$query_rssite_banner_principal1 = "SELECT site_banner_principal.titulo, site_banner_principal.IdBanner, site_banner_principal.codigo FROM site_banner_principal WHERE {$NXTFilter_rssite_banner_principal1} ORDER BY {$NXTSort_rssite_banner_principal1}";
$query_limit_rssite_banner_principal1 = sprintf("%s LIMIT %d, %d", $query_rssite_banner_principal1, $startRow_rssite_banner_principal1, $maxRows_rssite_banner_principal1);
$rssite_banner_principal1 = mysql_query($query_limit_rssite_banner_principal1, $conexao) or die(mysql_error());
$row_rssite_banner_principal1 = mysql_fetch_assoc($rssite_banner_principal1);

if (isset($_GET['totalRows_rssite_banner_principal1'])) {
  $totalRows_rssite_banner_principal1 = $_GET['totalRows_rssite_banner_principal1'];
} else {
  $all_rssite_banner_principal1 = mysql_query($query_rssite_banner_principal1);
  $totalRows_rssite_banner_principal1 = mysql_num_rows($all_rssite_banner_principal1);
}
$totalPages_rssite_banner_principal1 = ceil($totalRows_rssite_banner_principal1/$maxRows_rssite_banner_principal1)-1;
//End NeXTenesio3 Special List Recordset

$nav_listsite_banner_principal1->checkBoundries();
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
<title>Área de Parceiro - Success Sistemas</title>
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
  record_counter: true
}
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_titulo {width:350px; overflow:hidden;}
  .KT_col_IdBanner {width:140px; overflow:hidden;}
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
                        <td align="left">Banner principal <?php
  $nav_listsite_banner_principal1->Prepare();
  require("../../includes/nav/NAV_Text_Statistics.inc.php");
?></td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Banner principal</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                  <div class="KT_tng" id="listsite_banner_principal1">
                    <div class="KT_tnglist">
                      <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
                        <div class="KT_options"> <a href="<?php echo $nav_listsite_banner_principal1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
                          <?php 
  // Show IF Conditional region1
  if (@$_GET['show_all_nav_listsite_banner_principal1'] == 1) {
?>
                            <?php echo $_SESSION['default_max_rows_nav_listsite_banner_principal1']; ?>
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
                              <th id="titulo" class="KT_sorter KT_col_titulo <?php echo $tso_listsite_banner_principal1->getSortIcon('site_banner_principal.titulo'); ?>"> <a href="<?php echo $tso_listsite_banner_principal1->getSortLink('site_banner_principal.titulo'); ?>">Título</a> </th>
                              <th>&nbsp;</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if ($totalRows_rssite_banner_principal1 == 0) { // Show if recordset empty ?>
                              <tr>
                                <td colspan="3"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
                              </tr>
                              <?php } // Show if recordset empty ?>
                            <?php if ($totalRows_rssite_banner_principal1 > 0) { // Show if recordset not empty ?>
                              <?php do { ?>
                                <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                                  <td><input type="checkbox" name="kt_pk_site_banner_principal" class="id_checkbox" value="<?php echo $row_rssite_banner_principal1['IdBanner']; ?>" />
                                    <input type="hidden" name="IdBanner" class="id_field" value="<?php echo $row_rssite_banner_principal1['IdBanner']; ?>" /></td>
                                  <td>
                                  <div class="KT_col_titulo">
                                  <a href="../../imagens/site_banner_principal/<?php echo $row_rssite_banner_principal1['codigo']; ?>" target="_blank"><?php echo KT_FormatForList($row_rssite_banner_principal1['titulo'], 50); ?></a>
                                  </div>
                                  </td>
                                  <td><a class="KT_edit_link" href="tabela.php?IdBanner=<?php echo $row_rssite_banner_principal1['IdBanner']; ?>&amp;KT_back=1"><?php echo NXT_getResource("edit_one"); ?></a> <a class="KT_delete_link" href="#delete"><?php echo NXT_getResource("delete_one"); ?></a></td>
                                </tr>
                                <?php } while ($row_rssite_banner_principal1 = mysql_fetch_assoc($rssite_banner_principal1)); ?>
                              <?php } // Show if recordset not empty ?>
                          </tbody>
                        </table>
                        <div class="KT_bottomnav">
                          <div>
                            <?php
            $nav_listsite_banner_principal1->Prepare();
            require("../../includes/nav/NAV_Text_Navigation.inc.php");
          ?>
                          </div>
                        </div>
                        <div class="KT_bottombuttons">
                          <div class="KT_operations"> <a class="KT_edit_op_link" href="#" onclick="nxt_list_edit_link_form(this); return false;"><?php echo NXT_getResource(""); ?></a> <a class="KT_delete_op_link" href="#" onclick="nxt_list_delete_link_form(this); return false;"><?php echo NXT_getResource("delete_all"); ?></a> </div>
                          <span>&nbsp;</span>
                          <input type="hidden" name="no_new" id="no_new" value="1">
                          <a class="KT_additem_op_link" href="tabela.php?KT_back=1" onclick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a> </div>
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
mysql_free_result($rssite_banner_principal1);
 
mysql_free_result($usuario); ?>
