<?
if($_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0"){
	die("Acesso nao Autorizado");
}

error_reporting (E_ALL & ~ E_NOTICE & ~ E_DEPRECATED);

$hostname_conexao = "localhost";
$database_conexao = "success03";
$username_conexao = "root";
$password_conexao = "";
$conexao = mysql_pconnect($hostname_conexao, $username_conexao, $password_conexao) or trigger_error(mysql_error(),E_USER_ERROR); 

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

// o nome do arquivo deverá ser sempre 'dump.zip' ou 'dump.sql'
// recomendável trabalhar com a extensão 'zip', pois caso o arquivo esteja sendo atualizado no momento e/ou esteja corrompido, não é realizada a atualização do BD.

$marcador_inicial = microtime(1); // para calcular tempo de processamento do script
set_time_limit(600); // altera o tempo máximo de execução de 30 segundos para 10 minutos

// recordset data da última atualização
mysql_select_db($database_conexao, $conexao);
$query_dump_data_atual = "SELECT data FROM dump WHERE IdDump = '1'";
$dump_data_atual = mysql_query($query_dump_data_atual, $conexao) or die(mysql_error());
$row_dump_data_atual = mysql_fetch_assoc($dump_data_atual);
// dim - recordset data da última atualização

// mostra data da última atualização
echo "Data da ultima atualizacao: ";
echo date("d-m-Y H:i:s", strtotime($row_dump_data_atual['data']));
echo "<br>";
// fim - mostra data da última atualização

// se arquivo existe
if(file_exists('imp/dump.sql') or file_exists('imp/dump.zip')){

	// para arquivos 'zip'
	if(file_exists('imp/dump.zip')){
		
		$zip = new ZipArchive;
		if ($zip->open('imp/dump.zip') === TRUE) {
			
			$zip->extractTo('imp/');
			$zip->close();
				
		} else {
			
			// echo 'failed';
			
		}
		
		unlink('imp/dump.zip'); // apaga o arquivo
		$arquivo = 'imp/dump.sql'; // define o diretorio/arquivo
		
	}
	// fim - para arquivos 'zip'
	
	// para arquivos 'sql'
	if(file_exists('imp/dump.sql')){
		$arquivo = 'imp/dump.sql'; // define o diretorio/arquivo
	}
	// fim - para arquivos 'sql'	
	
	// mostra data do arquivo
	echo "Data do arquivo: ";
	echo date("d-m-Y H:i:s", filemtime($arquivo));
	echo "<br>";
	// fim - mostra data do arquivo
	
	// se arquivo ainda NÃO foi atualizado (Datas diferentes)
	if($row_dump_data_atual['data'] != date("Y-m-d H:i:s", filemtime($arquivo))){		

		// importa o arquivo para o banco de dados --------------------
		// Connect to the Database server
		$driver = 'mysql';
		$host = $hostname_conexao;
		$port = 3306;
		$socket = ''; // Optional
		$username = $username_conexao;
		$password = $password_conexao;
		$database = $database_conexao;
		$options = array(
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_EMULATE_PREPARES   => true,
			\PDO::ATTR_CURSOR             => \PDO::CURSOR_FWDONLY
		);
		try {
			// Get the Connexion's DSN
			if (empty($socket)) {
				$dsn = $driver . ':host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=utf8';
			} else {
				$dsn = $driver . ':unix_socket=' . $socket . ';dbname=' . $database . ';charset=utf8';
			}
			// Connect to the Database Server
			$pdo = new \PDO($dsn, $username, $password, $options);
			
		} catch (\PDOException $e) {
			die("Can't connect to the database server. ERROR: " . $e->getMessage());
		} catch (\Exception $e) {
			die("The database connection failed. ERROR: " . $e->getMessage());
		}
		// fim - Connect to the Database server

		function importSqlFile($pdo, $sqlFile, $tablePrefix = null, $InFilePath = null){
			try {
				
				// Enable LOAD LOCAL INFILE
				//$pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
				
				$errorDetect = false;
				
				// Temporary variable, used to store current query
				$tmpLine = '';
				
				// Read in entire file
				$lines = file($sqlFile);
				
				// Loop through each line
				foreach ($lines as $line) {
					// Skip it if it's a comment
					if (substr($line, 0, 2) == '--' || trim($line) == '') {
						continue;
					}
					
					// Read & replace prefix
					$line = str_replace(array('<<prefix>>', '<<InFilePath>>'), array($tablePrefix, $InFilePath), $line);
					
					// Add this line to the current segment
					$tmpLine .= $line;
					
					// If it has a semicolon at the end, it's the end of the query
					if (substr(trim($line), -1, 1) == ';') {
						try {
							// Perform the Query
							$pdo->exec($tmpLine);
						} catch (\PDOException $e) {
							echo "<br><pre>Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage() . "</pre>\n";
							$errorDetect = true;
						}
						
						// Reset temp variable to empty
						$tmpLine = '';
					}
				}
				
				// Check if error is detected
				if ($errorDetect) {
					return false;
				}
				
			} catch (\Exception $e) {
				echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
				return false;
			}
			
			return true;
		}

		// Import the SQL file
		$res = importSqlFile($pdo, $arquivo);
		if ($res === false) {
			die('ERROR');
		}
		// fim - importa o arquivo para o banco de dados --------------
		
		$dsn = NULL;
		$res = NULL;
		
		echo "Datas diferentes.";
		echo "<br>";
		echo "Importação realizada.";
		echo "<br>";
		
		// grava data de modificação
		mysql_query("UPDATE dump SET data = '".date("Y-m-d H:i:s", filemtime($arquivo))."' WHERE IdDump='1'") or die(mysql_error());
		echo "Datas alterada.";
		echo "<br>";
		// fim - grava data de modificação
		
		clearstatcache(); // limpa o cache do arquivo

		copy($arquivo, "imp2/dump.sql"); // faz um backup do arquivo na pasta imp2
		unlink($arquivo); // apaga o arquivo
		
		echo "Arquivo apagado do FTP.";
		echo "<br>";
		
	}
	// fim - se arquivo ainda NÃO foi atualizado (Datas diferentes)
	
	// se arquivo JÁ foi atualizado (Datas iguais)
	else {
		
		unlink($arquivo); // apaga o arquivo
		
		echo "Banco de dados já atualizado com o arquivo atual.";
		echo "<br>";
		echo "Arquivo apagado do FTP.";
		echo "<br>";
	}
	// FIM - se arquivo JÁ foi atualizado (Datas iguais)
		
}
// fim - se arquivo existe

// fim - se arquivo NÃO existe
else {
	echo "Aquivo não localizado no ftp.";
	echo "<br>";
}
// fim - se arquivo NÃO existe

mysql_free_result($dump_data_atual);

// mostra o tempo de execução do script
$marcador_final= microtime(1);
$tempo_execucao = $marcador_final - $marcador_inicial;
echo "Tempo para execução: <b>" .sprintf ( "%02.3f", $tempo_execucao ). "</b> segundos.";
echo "<br>";
// fim - mostra o tempo de execução do script

// insert - auto *****************************************************************
$insertSQL_auto = sprintf("
INSERT INTO auto (titulo, data, ip) 
VALUES (%s, %s, %s)", 
GetSQLValueString(basename($_SERVER['PHP_SELF']), "text"), 
GetSQLValueString(date('Y-m-d H:i:s'), "date"),
GetSQLValueString(basename($_SERVER["REMOTE_ADDR"]), "text"));
mysql_select_db($database_conexao, $conexao);
$Result_auto = mysql_query($insertSQL_auto, $conexao) or die(mysql_error());
// fim - insert - auto ***********************************************************

mysql_close($conexao);
?>