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

// usuario
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("
SELECT * 
FROM usuarios 
WHERE usuario = %s", 
GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuario

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the required classes
require_once('../../includes/tfi/TFI.php');
require_once('../../includes/tso/TSO.php');
require_once('../../includes/nav/NAV.php');

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Filter
$tfi_listmala_direta1 = new TFI_TableFilter($conn_conexao, "tfi_listmala_direta1");
$tfi_listmala_direta1->addColumn("mala_direta.IdMalaDireta", "NUMERIC_TYPE", "IdMalaDireta", "=");
$tfi_listmala_direta1->addColumn("mala_direta.data_criacao", "DATE_TYPE", "data_criacao", "=");
$tfi_listmala_direta1->addColumn("usuarios.nome", "STRING_TYPE", "usuario_nome", "%");
$tfi_listmala_direta1->addColumn("mala_direta.titulo", "STRING_TYPE", "titulo", "%");
$tfi_listmala_direta1->addColumn("mala_direta.perfil", "NUMERIC_TYPE", "perfil", "=");
$tfi_listmala_direta1->addColumn("mala_direta.tipo", "NUMERIC_TYPE", "tipo", "=");
$tfi_listmala_direta1->Execute();

// Sorter
$tso_listmala_direta1 = new TSO_TableSorter("rsmala_direta1", "tso_listmala_direta1");
$tso_listmala_direta1->addColumn("mala_direta.IdMalaDireta");
$tso_listmala_direta1->addColumn("mala_direta.data_criacao");
$tso_listmala_direta1->addColumn("usuarios.nome");
$tso_listmala_direta1->addColumn("mala_direta.titulo");
$tso_listmala_direta1->addColumn("mala_direta.perfil");
$tso_listmala_direta1->addColumn("mala_direta.tipo");
$tso_listmala_direta1->setDefault("mala_direta.IdMalaDireta DESC");
$tso_listmala_direta1->Execute();

// Navigation
$nav_listmala_direta1 = new NAV_Regular("nav_listmala_direta1", "rsmala_direta1", "../../", $_SERVER['PHP_SELF'], 50);

//NeXTenesio3 Special List Recordset
$maxRows_rsmala_direta1 = $_SESSION['max_rows_nav_listmala_direta1'];
$pageNum_rsmala_direta1 = 0;
if (isset($_GET['pageNum_rsmala_direta1'])) {
  $pageNum_rsmala_direta1 = $_GET['pageNum_rsmala_direta1'];
}
$startRow_rsmala_direta1 = $pageNum_rsmala_direta1 * $maxRows_rsmala_direta1;

// Defining List Recordset variable
$NXTFilter_rsmala_direta1 = "1=1";
if (isset($_SESSION['filter_tfi_listmala_direta1'])) {
  $NXTFilter_rsmala_direta1 = $_SESSION['filter_tfi_listmala_direta1'];
}
// Defining List Recordset variable
$NXTSort_rsmala_direta1 = "mala_direta.data_reenvio IS NULL ASC, mala_direta.data_criacao DESC, mala_direta.IdMalaDireta DESC";
if (isset($_SESSION['sorter_tso_listmala_direta1'])) {
  $NXTSort_rsmala_direta1 = $_SESSION['sorter_tso_listmala_direta1'];
}

mysql_select_db($database_conexao, $conexao);
$query_rsmala_direta1 = "
SELECT 
mala_direta.IdMalaDireta, mala_direta.data_criacao, mala_direta.IdUsuario, mala_direta.titulo, mala_direta.perfil, mala_direta.tipo, 
0 AS lido,
(SELECT COUNT(mala_direta_destinatario.IdMalaDiretaDestinatario) FROM mala_direta_destinatario WHERE mala_direta_destinatario.IdMalaDireta = mala_direta.IdMalaDireta) AS mala_direta_destinatario_contador, 
usuarios.nome AS usuario_nome 
FROM mala_direta 
LEFT JOIN usuarios ON usuarios.IdUsuario = mala_direta.IdUsuario 
WHERE mala_direta.IdUsuario = ".$row_usuario['IdUsuario']." and {$NXTFilter_rsmala_direta1} 
ORDER BY {$NXTSort_rsmala_direta1}";

$query_limit_rsmala_direta1 = sprintf("%s LIMIT %d, %d", $query_rsmala_direta1, $startRow_rsmala_direta1, $maxRows_rsmala_direta1);
$rsmala_direta1 = mysql_query($query_limit_rsmala_direta1, $conexao) or die(mysql_error());
$row_rsmala_direta1 = mysql_fetch_assoc($rsmala_direta1);

if (isset($_GET['totalRows_rsmala_direta1'])) {
  $totalRows_rsmala_direta1 = $_GET['totalRows_rsmala_direta1'];
} else {
  $all_rsmala_direta1 = mysql_query($query_rsmala_direta1);
  $totalRows_rsmala_direta1 = mysql_num_rows($all_rsmala_direta1);
}
$totalPages_rsmala_direta1 = ceil($totalRows_rsmala_direta1/$maxRows_rsmala_direta1)-1;
//End NeXTenesio3 Special List Recordset


$nav_listmala_direta1->checkBoundries();
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

<link rel="stylesheet" href="../../css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="../../js/thickbox.js"></script>

<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_LIST_SETTINGS = {
  duplicate_buttons: false,
  duplicate_navigation: false,
  row_effects: false,
  show_as_buttons: false,
  record_counter: false
}
</script>
<script type="text/javascript">
$(document).ready(function() {
	
	// acao - deletar
	$('.KT_delete_link').click(function(){
		var codigo_atual = $(this).attr('title');
		return confirm("Você tem certeza que deseja deletar o registro de código: " + codigo_atual + " ?");
	})
	// fim - acao - deletar
	
});
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_IdMalaDireta {width:50px; overflow:hidden;}
  .KT_col_data_criacao {width:100px; overflow:hidden;}
  .KT_col_usuario_nome {width:150px; overflow:hidden;}
  .KT_col_titulo {width:200px; overflow:hidden;}
  .KT_col_distribuicao {width:80px; overflow:hidden;}
  .KT_col_perfil {width: 70px; overflow:hidden;}
  .KT_col_tipo {width: 80px; overflow:hidden;}
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
                        <td align="left">Mala Direta
                          <?php
$nav_listmala_direta1->Prepare();
require("../../includes/nav/NAV_Text_Statistics.inc.php");
?>
</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Mala Direta</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">

                <div class="KT_tng">
                                
                    <div class="KT_tnglist">
                    <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
                      <div class="KT_options"> <a href="<?php echo $nav_listmala_direta1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
                        <?php 
                    // Show IF Conditional region1
                    if (@$_GET['show_all_nav_listmala_direta1'] == 1) {
                    ?>
                          <?php echo $_SESSION['default_max_rows_nav_listmala_direta1']; ?>
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
                    if (@$_SESSION['has_filter_tfi_listmala_direta1'] == 1) {
                    ?>
                          <a href="<?php echo $tfi_listmala_direta1->getResetFilterLink(); ?>"><?php echo NXT_getResource("Reset filter"); ?></a>
                          <?php 
                    // else Conditional region2
                    } else { ?>
                          <a href="<?php echo $tfi_listmala_direta1->getShowFilterLink(); ?>"><?php echo NXT_getResource("Show filter"); ?></a>
                          <?php } 
                    // endif Conditional region2
                    ?>
                      </div>
                      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                        <thead>
                          <tr class="KT_row_order">
                            <th>&nbsp;</th>
                            
                            <th id="IdMalaDireta" class="KT_sorter KT_col_IdMalaDireta <?php echo $tso_listmala_direta1->getSortIcon('mala_direta.IdMalaDireta'); ?>">
                            <a href="<?php echo $tso_listmala_direta1->getSortLink('mala_direta.IdMalaDireta'); ?>">Núm</a>
                            </th>
                            
                            <th id="data_criacao" class="KT_sorter KT_col_data_criacao <?php echo $tso_listmala_direta1->getSortIcon('mala_direta.data_criacao'); ?>">
                            <a href="<?php echo $tso_listmala_direta1->getSortLink('mala_direta.data_criacao'); ?>">Criação</a>
                            </th>
							
                            <th id="perfil" class="KT_sorter KT_col_perfil <?php echo $tso_listmala_direta1->getSortIcon('mala_direta.perfil'); ?>">
                            	<a href="<?php echo $tso_listmala_direta1->getSortLink('mala_direta.perfil'); ?>">Perfil</a>
                            </th>
                            
                            <th id="usuario_nome" class="KT_sorter KT_col_usuario_nome <?php echo $tso_listmala_direta1->getSortIcon('usuarios.nome'); ?>">
                            <a href="<?php echo $tso_listmala_direta1->getSortLink('usuarios.nome'); ?>">
                            Responsável
                            </a>
                            </th>
                            
                            <th id="titulo" class="KT_sorter KT_col_titulo <?php echo $tso_listmala_direta1->getSortIcon('mala_direta.titulo'); ?>">
                            	<a href="<?php echo $tso_listmala_direta1->getSortLink('mala_direta.titulo'); ?>">Título</a>
                            </th>
													
                            <th id="tipo" class="KT_sorter KT_col_tipo <?php echo $tso_listmala_direta1->getSortIcon('mala_direta.tipo'); ?>">
                            	<a href="<?php echo $tso_listmala_direta1->getSortLink('mala_direta.tipo'); ?>">Tipo</a>
                            </th>
                            
                            <th>&nbsp;</th>
                          </tr>
                          <?php 
                    // Show IF Conditional region3
                    if (@$_SESSION['has_filter_tfi_listmala_direta1'] == 1) {
                    ?>
                            <tr class="KT_row_filter">
                              <td>&nbsp;</td>
                               <td>&nbsp;</td>
                               <td><input type="text" name="tfi_listmala_direta1_usuario_nome" id="tfi_listmala_direta1_usuario_nome" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listmala_direta1_usuario_nome']); ?>" size="15" maxlength="50" /></td>
                              <td><input type="text" name="tfi_listmala_direta1_titulo" id="tfi_listmala_direta1_titulo" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listmala_direta1_titulo']); ?>" size="15" maxlength="50" /></td>
                              <td>&nbsp;</td>
                              </tr>
                            <?php } 
                    // endif Conditional region3
                    ?>
                        </thead>
                        <tbody>
                          <?php if ($totalRows_rsmala_direta1 == 0) { // Show if recordset empty ?>
                            <tr>
                              <td colspan="5"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
                            </tr>
                            <?php } // Show if recordset empty ?>
                          <?php if ($totalRows_rsmala_direta1 > 0) { // Show if recordset not empty ?>
                            <?php do { ?>
                              <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>" id="KT_even_<?php echo $row_rsmala_direta1['IdMalaDireta']; ?>" <?php if($row_rsmala_direta1['lido'] == 0){ ?> style="font-weight: bold;"<? } ?> >
                                <td>
                                <input type="hidden" name="IdMalaDireta" class="id_field" value="<?php echo $row_rsmala_direta1['IdMalaDireta']; ?>" /> 
                                </td>
                                <td><div class="KT_col_IdMalaDireta"><?php echo $row_rsmala_direta1['IdMalaDireta']; ?></div></td>
                                <td><div class="KT_col_data_criacao"><?php echo date('d-m-Y H:i', strtotime($row_rsmala_direta1['data_criacao'])); ?></div></td>
								<td>
								<div class="KT_col_tipo">
								<?php 
								if($row_rsmala_direta1['perfil']=="c"){
									echo "Clientes";
								} else if($row_rsmala_direta1['perfil']=="p"){
									echo "Prospects";
								}
								?>
								</div>
								</td>
                                <td><div class="KT_col_IdUsuario"><?php echo KT_FormatForList($row_rsmala_direta1['usuario_nome'], 20); ?></div></td>
                                <td><div class="KT_col_titulo" title="<?php echo $row_rsmala_direta1['titulo']; ?>"><?php echo KT_FormatForList($row_rsmala_direta1['titulo'], 30); ?></div></td>
								
								<td>
								<div class="KT_col_tipo">
                                	<?php if($row_rsmala_direta1['tipo']=="em"){ ?>
										<a class="KT_edit_link" href="email.php?IdMalaDireta=<?php echo $row_rsmala_direta1['IdMalaDireta']; ?>" target="_blank"><?php echo NXT_getResource("E-mail"); ?></a> 									
									<? } else if($row_rsmala_direta1['tipo']=="et"){ ?>
                                		<a class="KT_edit_link" href="etiqueta.php?IdMalaDireta=<?php echo $row_rsmala_direta1['IdMalaDireta']; ?>" target="_blank"><?php echo NXT_getResource("Etiquetas"); ?></a> 									
									<? } else if($row_rsmala_direta1['tipo']=="po"){ ?>
										<a class="KT_edit_link" href="postal.php?IdMalaDireta=<?php echo $row_rsmala_direta1['IdMalaDireta']; ?>" target="_blank"><?php echo NXT_getResource("M.D. Postal"); ?></a> 
									<? } ?>
								</div>
								</td>
								
                                <td>
									<a class="KT_edit_link" href="tabela.php?IdMalaDireta=<?php echo $row_rsmala_direta1['IdMalaDireta']; ?>"><?php echo NXT_getResource("Acessar"); ?></a>                                 	
                                </td>
                              </tr>
                              <?php } while ($row_rsmala_direta1 = mysql_fetch_assoc($rsmala_direta1)); ?>
                            <?php } // Show if recordset not empty ?>
                        </tbody>
                      </table>
                      <div class="KT_bottomnav">
                        <div>
                          <?php
                            $nav_listmala_direta1->Prepare();
                            require("../../includes/nav/NAV_Text_Navigation.inc.php");
                          ?>
                        </div>
                      </div>
                      
                      <div class="KT_bottombuttons">
                        <span>&nbsp;</span>
                        <a class="KT_additem_op_link" href="tabela.php" onClick="return nxt_list_additem(this)"><strong><?php echo NXT_getResource("Inserir novo - CLIENTES"); ?></strong></a>  
						&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp; 
						<a class="KT_additem_op_link" href="tabela.php?perfil=p" onClick="return nxt_list_additem(this)"><strong><?php echo NXT_getResource("Inserir novo - PROSPECTS"); ?></strong></a> 
                      </div>
                        
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
mysql_free_result($usuario);
mysql_free_result($rsmala_direta1);
?>
