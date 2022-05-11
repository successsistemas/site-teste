<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php

$id_concorrente_atual = @$_POST['id_concorrente_atual'];

// concorrente
mysql_select_db($database_conexao, $conexao);
$query_concorrente = "
SELECT prospeccao_concorrente.* 
FROM prospeccao_concorrente 
ORDER BY prospeccao_concorrente.nome ASC
";
$concorrente = mysql_query($query_concorrente, $conexao) or die(mysql_error());
$row_concorrente = mysql_fetch_assoc($concorrente);
$totalRows_concorrente = mysql_num_rows($concorrente);
// fim - concorrente
?>

<option value="">...</option>
<?php do { ?>

	<option value="<?php echo $row_concorrente['id']?>" <?php if ((!(strcmp($row_concorrente['id'], $id_concorrente_atual)))) {echo "selected=\"selected\"";} ?>>
	<?php echo $row_concorrente['nome']; ?> [<?php echo $row_concorrente['empresa']; ?>]
	</option>

<?php } while ($row_concorrente = mysql_fetch_assoc($concorrente)); ?>

<? mysql_free_result($concorrente); ?>