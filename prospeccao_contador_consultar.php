<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php

$where = " 1=1 ";

if (isset($_POST['uf_atual']) and $_POST['uf_atual'] <> "") { 
	$where .= " and prospeccao_contador.uf = '".$_POST['uf_atual']."'";
}

if (isset($_POST['cidade_atual']) and $_POST['cidade_atual'] <> "") { 
	$where .= " and prospeccao_contador.cidade = '".$_POST['cidade_atual']."'";
}

$id_contador_atual = @$_POST['id_contador_atual'];

// contador
mysql_select_db($database_conexao, $conexao);
$query_contador = "
SELECT prospeccao_contador.* 
FROM prospeccao_contador 
WHERE $where 
ORDER BY prospeccao_contador.razao ASC
";
$contador = mysql_query($query_contador, $conexao) or die(mysql_error());
$row_contador = mysql_fetch_assoc($contador);
$totalRows_contador = mysql_num_rows($contador);
// fim - contador
?>

<option value="">...</option>
<? if($totalRows_contador > 0){ ?>
	<?php do { ?>
	
		<option value="<?php echo $row_contador['id']?>" <?php if ((!(strcmp($row_contador['id'], $id_contador_atual)))) {echo "selected=\"selected\"";} ?>><?php echo $row_contador['razao']; ?></option>
	
	<?php } while ($row_contador = mysql_fetch_assoc($contador)); ?>
<? } ?>
<? mysql_free_result($contador); ?>