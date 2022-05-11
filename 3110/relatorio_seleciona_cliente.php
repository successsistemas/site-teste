<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');
mysql_select_db($database_conexao, $conexao);

$where = "1 = 1";

// filtro_geral_praca
if( (isset($_POST["filtro_geral_praca"])) && ($_POST['filtro_geral_praca'] !="") ) {
	$colname_filtro_geral_praca = $_POST["filtro_geral_praca"];
}
// fim - se existe filtro de filtro_geral_praca

// cliente_atual
$cliente_atual_atual = NULL;
if( (isset($_POST["cliente_atual"])) && ($_POST['cliente_atual'] !="") ) {
	$cliente_atual_atual = $_POST["cliente_atual"];
} 
// fim - se existe filtro de cliente_atual

// seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query --------------------------------------------
$query_usuarios_geral_tipo_praca_executor = " 
SELECT * 
FROM geral_tipo_praca_executor 
WHERE geral_tipo_praca_executor.praca = '$colname_filtro_geral_praca'";
$usuarios_geral_tipo_praca_executor = mysql_query($query_usuarios_geral_tipo_praca_executor, $conexao) or die(mysql_error());	
$sql_clientes_vendedor17 = ""; 

		// lista os EXECUTORES - em cada EXECUTOR com acesso, é adicionado seu codigo a uma string
		while ($row_usuarios_geral_tipo_praca_executor = mysql_fetch_assoc($usuarios_geral_tipo_praca_executor)){
			$sql_clientes_vendedor17 .= "vendedor17 = '".$row_usuarios_geral_tipo_praca_executor['IdExecutor']."' or ";
		}
		// fim - lista os EXECUTORES - em cada EXECUTOR com acesso, é adicionado seu codigo a uma string

$sql_clientes_vendedor17 = substr($sql_clientes_vendedor17, 0, -4);
// fim - seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query --------------------------------------

$query_cliente_atual = "
SELECT 
da01.nome1, da01.codigo1 
FROM da37 
INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
WHERE $sql_clientes_vendedor17 and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
ORDER BY da01.nome1 ASC
";
$cliente_atual = mysql_query($query_cliente_atual) or die(mysql_error());
$row_cliente_atual = mysql_fetch_assoc($cliente_atual);
$totalRows_cliente_atual = mysql_num_rows($cliente_atual);
?>

<? if($totalRows_cliente_atual == 0){ ?>

	<option value="">Nenhum usuário encontrado</option>
   
<? }else{ ?>
    
    <option value=""
    <?php if (!(strcmp("", isset($cliente_atual_atual)))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
    <?php do {  ?>
    <option value="<?php echo $row_cliente_atual['codigo1']; ?>"
    <?php if ( (isset($cliente_atual_atual)) and (!(strcmp($row_cliente_atual['codigo1'], $cliente_atual_atual))) ) {echo "selected=\"selected\"";} ?>
    >
    <?php echo  utf8_encode($row_cliente_atual['nome1']); ?>
    </option>
    <?php
    } while ($row_cliente_atual = mysql_fetch_assoc($cliente_atual));
    $rows = mysql_num_rows($cliente_atual);
    if($rows > 0) {
    mysql_data_seek($cliente_atual, 0);
    $row_cliente_atual = mysql_fetch_assoc($cliente_atual);
    }
    ?>
    
<? } ?>

<? mysql_free_result($usuarios_geral_tipo_praca_executor); ?>
<? mysql_free_result($cliente_atual); ?>
