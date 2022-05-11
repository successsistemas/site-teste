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
$tfi_listdownloads1 = new TFI_TableFilter($conn_conexao, "tfi_listdownloads1");
$tfi_listdownloads1->addColumn("downloads.id_download", "NUMERIC_TYPE", "id_download", "=");
$tfi_listdownloads1->addColumn("downloads.label", "STRING_TYPE", "label", "%");
$tfi_listdownloads1->addColumn("downloads.tamanho", "STRING_TYPE", "tamanho", "%");
$tfi_listdownloads1->Execute();

// Sorter
$tso_listdownloads1 = new TSO_TableSorter("rsdownloads1", "tso_listdownloads1");
$tso_listdownloads1->addColumn("downloads.id_download");
$tso_listdownloads1->addColumn("downloads.label");
$tso_listdownloads1->addColumn("downloads.tamanho");
$tso_listdownloads1->setDefault("downloads.label");
$tso_listdownloads1->Execute();

// Navigation
$nav_listdownloads1 = new NAV_Regular("nav_listdownloads1", "rsdownloads1", "", $_SERVER['PHP_SELF'], 50);

$currentPage = $_SERVER["PHP_SELF"];

// grupo
$colname_categoria = "-1";
if (isset($_GET['id_download_grupo'])) {
  $colname_categoria = $_GET['id_download_grupo'];
}
mysql_select_db($database_conexao, $conexao);
$query_categoria = sprintf("SELECT * FROM downloads_grupos WHERE id_download_grupo = %s", GetSQLValueString($colname_categoria, "int"));
$categoria = mysql_query($query_categoria, $conexao) or die(mysql_error());
$row_categoria = mysql_fetch_assoc($categoria);
$totalRows_categoria = mysql_num_rows($categoria);
// fim - grupo

// subgrupo
$colname_subcategoria = "-1";
if (isset($_GET['id_download_subgrupo'])) {
  $colname_subcategoria = $_GET['id_download_subgrupo'];
}
mysql_select_db($database_conexao, $conexao);
$query_subcategoria = sprintf("SELECT * FROM downloads_subgrupos WHERE id_download_subgrupo = %s", GetSQLValueString($colname_subcategoria, "int"));
$subcategoria = mysql_query($query_subcategoria, $conexao) or die(mysql_error());
$row_subcategoria = mysql_fetch_assoc($subcategoria);
$totalRows_subcategoria = mysql_num_rows($subcategoria);
// fim - subgrupo

//NeXTenesio3 Special List Recordset
$maxRows_rsdownloads1 = $_SESSION['max_rows_nav_listdownloads1'];
$pageNum_rsdownloads1 = 0;
if (isset($_GET['pageNum_rsdownloads1'])) {
  $pageNum_rsdownloads1 = $_GET['pageNum_rsdownloads1'];
}
$startRow_rsdownloads1 = $pageNum_rsdownloads1 * $maxRows_rsdownloads1;

// Defining List Recordset variable
$NXTFilter_rsdownloads1 = "1=1";
if (isset($_SESSION['filter_tfi_listdownloads1'])) {
  $NXTFilter_rsdownloads1 = $_SESSION['filter_tfi_listdownloads1'];
}
// Defining List Recordset variable
$NXTSort_rsdownloads1 = "downloads.label";
if (isset($_SESSION['sorter_tso_listdownloads1'])) {
  $NXTSort_rsdownloads1 = $_SESSION['sorter_tso_listdownloads1'];
}
mysql_select_db($database_conexao, $conexao);

$colname_rsdownloads1 = "-1";
if (isset($_GET['id_download_subgrupo'])) {
  $colname_rsdownloads1 = $_GET['id_download_subgrupo'];
}

$query_rsdownloads1 = sprintf("SELECT downloads.id_download, downloads.label, downloads.tamanho, downloads.link FROM downloads WHERE {$NXTFilter_rsdownloads1} and id_download_subgrupo=%s ORDER BY {$NXTSort_rsdownloads1}", GetSQLValueString($colname_rsdownloads1, "text"));
$query_limit_rsdownloads1 = sprintf("%s LIMIT %d, %d", $query_rsdownloads1, $startRow_rsdownloads1, $maxRows_rsdownloads1);
$rsdownloads1 = mysql_query($query_limit_rsdownloads1, $conexao) or die(mysql_error());
$row_rsdownloads1 = mysql_fetch_assoc($rsdownloads1);

if (isset($_GET['totalRows_rsdownloads1'])) {
  $totalRows_rsdownloads1 = $_GET['totalRows_rsdownloads1'];
} else {
  $all_rsdownloads1 = mysql_query($query_rsdownloads1);
  $totalRows_rsdownloads1 = mysql_num_rows($all_rsdownloads1);
}
$totalPages_rsdownloads1 = ceil($totalRows_rsdownloads1/$maxRows_rsdownloads1)-1;
//End NeXTenesio3 Special List Recordset

$nav_listdownloads1->checkBoundries();
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

<? if($row_usuario['administrador_site']=="Y") { // se é administrador de site ?>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_label {width:300px; overflow:hidden;}
  .KT_col_data {width:150px; overflow:hidden;}
  .KT_col_tamanho {width:120px; overflow:hidden;}
</style>
<? } else { // fim - se é administrador de site | - se não é ... ?>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_label {width:300px; overflow:hidden;}
  .KT_col_data {width:150px; overflow:hidden;}
  .KT_col_tamanho {width:120px; overflow:hidden;}
</style>
<? } // fim - se não é... ?>

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
                        <td align="left">Downloads <?php $nav_listdownloads1->Prepare(); require("../../includes/nav/NAV_Text_Statistics.inc.php"); ?></td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Downloads</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">

<div class="KT_tng" id="listdownloads1">

    <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; color:#C00; font-size: 16px;">
    Categoria: <span style="color: #000"><?php echo $row_categoria['label']; ?></span>
    <br>
    Subcategoria: <span style="color: #000"><?php echo $row_subcategoria['label']; ?></span>
    </div>
    
	<div class="KT_tnglist">
    <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
          <div class="KT_options"> <a href="<?php echo $nav_listdownloads1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
            <?php 
      // Show IF Conditional region1
      if (@$_GET['show_all_nav_listdownloads1'] == 1) {
    ?>
              <?php echo $_SESSION['default_max_rows_nav_listdownloads1']; ?>
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
      if (@$_SESSION['has_filter_tfi_listdownloads1'] == 1) {
    ?>
                                  <a href="<?php echo $tfi_listdownloads1->getResetFilterLink(); ?>"><?php echo NXT_getResource("Reset filter"); ?></a>
                                  <?php 
      // else Conditional region2
      } else { ?>
                                  <a href="<?php echo $tfi_listdownloads1->getShowFilterLink(); ?>"><?php echo NXT_getResource("Show filter"); ?></a>
                                  <?php } 
      // endif Conditional region2
    ?>
          </div>
          <table cellpadding="2" cellspacing="0" class="KT_tngtable">
            <thead>
              <tr class="KT_row_order">
                <th>
                <? if($row_usuario['administrador_site']=="Y") { // se é administrador de site ?>
                <input type="checkbox" name="KT_selAll" id="KT_selAll"/>
                <? } // fim - se é administrador de site ?>
                </th>
                <th id="label" class="KT_sorter KT_col_label <?php echo $tso_listdownloads1->getSortIcon('downloads.label'); ?>"> <a href="<?php echo $tso_listdownloads1->getSortLink('downloads.label'); ?>">Título</a> </th>
                <th id="tamanho" class="KT_sorter KT_col_data <?php echo $tso_listdownloads1->getSortIcon('downloads.data'); ?>"> <a href="#">Data de modificação</a></th>
                <th id="tamanho" class="KT_sorter KT_col_tamanho <?php echo $tso_listdownloads1->getSortIcon('downloads.tamanho'); ?>"> <a href="<?php echo $tso_listdownloads1->getSortLink('downloads.tamanho'); ?>">Tamanho</a> </th>
                <th>&nbsp;</th>
              </tr>
              <?php 
      // Show IF Conditional region3
      if (@$_SESSION['has_filter_tfi_listdownloads1'] == 1) {
    ?>
                <tr class="KT_row_filter">
                  <td>&nbsp;</td>
                  <td><input type="text" name="tfi_listdownloads1_label" id="tfi_listdownloads1_label" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listdownloads1_label']); ?>" size="70" maxlength="100" /></td>
                  <td>&nbsp;</td>
                  <td><input type="text" name="tfi_listdownloads1_tamanho" id="tfi_listdownloads1_tamanho" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listdownloads1_tamanho']); ?>" size="30" maxlength="50" /></td>
                  <td><input type="submit" name="tfi_listdownloads1" value="<?php echo NXT_getResource("Filter"); ?>" /></td>
                </tr>
                <?php } 
      // endif Conditional region3
    ?>
            </thead>
            <tbody>
              <?php if ($totalRows_rsdownloads1 == 0) { // Show if recordset empty ?>
                <tr>
                  <td colspan="5"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
                </tr>
                <?php } // Show if recordset empty ?>
              <?php if ($totalRows_rsdownloads1 > 0) { // Show if recordset not empty ?>
                <?php do { ?>
                  <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                    <td>
                    <? if($row_usuario['administrador_site']=="Y") { // se é administrador de site ?>
                    <input type="checkbox" name="kt_pk_downloads" class="id_checkbox" value="<?php echo $row_rsdownloads1['id_download']; ?>" />
                    <? } // fim - se é administrador de site ?>
    
                    <input type="hidden" name="id_download" class="id_field" value="<?php echo $row_rsdownloads1['id_download']; ?>" />
                    </td>
                    <td>
                    <div class="KT_col_label"><?php echo $row_rsdownloads1['label']; ?></div>
                    </td>
                    <td>
                    <div class="KT_col_data">
                    <? if(file_exists("../../".$row_rsdownloads1['link'])){ ?>
                    <?php echo $data_upload = date("d-m-Y", filemtime("../../".$row_rsdownloads1['link'])); ?>
                    <? } ?>
                    </div>
                    </td>
                    <td><div class="KT_col_tamanho"><?php echo KT_FormatForList($row_rsdownloads1['tamanho'], 30); ?></div></td>
                    <td>
    
    <? if($row_usuario['administrador_site']=="Y") { // se é administrador de site ?>
    <a class="KT_edit_link" href="tabela.php?id_download_grupo=<?php echo $_GET['id_download_grupo']; ?>&id_download_subgrupo=<?php echo $_GET['id_download_subgrupo']; ?>&id_download=<?php echo $row_rsdownloads1['id_download']; ?>&amp;KT_back=1">
    <?php echo NXT_getResource("edit_one"); ?>
    </a> 
    <a class="KT_delete_link" href="#delete">
    <?php echo NXT_getResource("delete_one"); ?>
    </a>
    <? } // fim - se é administrador de site ?>
    
                    <a class="KT_edit_link" style="font-weight:bold;" href="../../<?php echo $row_rsdownloads1['link']; ?>">
                    <?php echo NXT_getResource("Baixar"); ?>
                    </a>
    
                    </td>
                  </tr>
                  <?php } while ($row_rsdownloads1 = mysql_fetch_assoc($rsdownloads1)); ?>
                <?php } // Show if recordset not empty ?>
            </tbody>
          </table>
          <div class="KT_bottomnav">
            <div>
              <?php
                $nav_listdownloads1->Prepare();
                require("../../includes/nav/NAV_Text_Navigation.inc.php");
              ?>
            </div>
          </div>
    
    <? if($row_usuario['administrador_site']=="Y") { // se é administrador de site ?>
    <div class="KT_bottombuttons">
    <div class="KT_operations"> <a class="KT_edit_op_link" href="#" onclick="nxt_list_edit_link_form(this); return false;"><?php echo NXT_getResource(""); ?></a> <a class="KT_delete_op_link" href="#" onclick="nxt_list_delete_link_form(this); return false;"><?php echo NXT_getResource("delete_all"); ?></a> </div>
    <input name="no_new" type="hidden" id="no_new" value="1" />
    <a class="KT_additem_op_link" href="tabela.php?id_download_grupo=<?php echo $_GET['id_download_grupo']; ?>&id_download_subgrupo=<?php echo $_GET['id_download_subgrupo']; ?>&KT_back=1" onclick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a> </div>
    <? } // fim - se é administrador de site ?>
    
        </form>
    </div>
	<br class="clearfixplain" />
    
    <div style="border: 2px solid #CCC; padding: 5px; margin-top: 10px;">
    <a href="listar_grupo.php">Listar Grupos</a> | 
    <a href="listar_subgrupo.php?id_download_grupo=<?php echo $row_categoria['id_download_grupo']; ?>">Listar Subgrupos</a>
    </div>
    
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
mysql_free_result($usuario);

mysql_free_result($categoria);
mysql_free_result($subcategoria);

mysql_free_result($rsdownloads1);
?>
