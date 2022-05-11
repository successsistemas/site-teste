<?
// totalizar_solicitacao_orcamento
if($row_relatorio['totalizar_solicitacao_orcamento'] > 0){ 
	$totalizar_solicitacao_orcamento = 0;
}
// fim - totalizar_solicitacao_orcamento

// totalizar_solicitacao_praca_orcamento *****************
if($row_relatorio['totalizar_solicitacao_praca_orcamento'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_solicitacao_praca_orcamento = NULL;
	do {
		$totalizar_solicitacao_praca_orcamento[$row_praca_array['praca']] = array('orcamento' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_orcamento ***********

// totalizar_solicitacao_tipo
if($row_relatorio['totalizar_solicitacao_tipo'] > 0){ 
	// solicitacao_tipo_solicitacao_totalizar
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_tipo_solicitacao_totalizar = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY IdTipoSolicitacao ASC";
	$solicitacao_tipo_solicitacao_totalizar = mysql_query($query_solicitacao_tipo_solicitacao_totalizar, $conexao) or die(mysql_error());
	$row_solicitacao_tipo_solicitacao_totalizar = mysql_fetch_assoc($solicitacao_tipo_solicitacao_totalizar);
	$totalRows_solicitacao_tipo_solicitacao_totalizar = mysql_num_rows($solicitacao_tipo_solicitacao_totalizar);
	// fim - solicitacao_tipo_solicitacao_totalizar
	$totalizar_solicitacao_tipo = NULL;
	do {
		$totalizar_solicitacao_tipo[] = array(
										'IdTipoSolicitacao' => $row_solicitacao_tipo_solicitacao_totalizar['IdTipoSolicitacao'], 
										'titulo'			=> $row_solicitacao_tipo_solicitacao_totalizar['titulo'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_tipo_solicitacao_totalizar = mysql_fetch_assoc($solicitacao_tipo_solicitacao_totalizar));
	mysql_free_result($solicitacao_tipo_solicitacao_totalizar);
}
// fim - totalizar_solicitacao_tipo

// totalizar_solicitacao_praca_tipo *****************
if($row_relatorio['totalizar_solicitacao_praca_tipo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// solicitacao_tipo_array
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_tipo_array = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY IdTipoSolicitacao ASC";
	$solicitacao_tipo_array = mysql_query($query_solicitacao_tipo_array, $conexao) or die(mysql_error());
	$row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array);
	$totalRows_solicitacao_tipo_array = mysql_num_rows($solicitacao_tipo_array);
	$solicitacao_tipo_array_atual = NULL;
	do {
		$solicitacao_tipo_array_atual[] = array(
			'IdTipoSolicitacao' => $row_solicitacao_tipo_array['IdTipoSolicitacao'], 
			'titulo'			=> $row_solicitacao_tipo_array['titulo'], 
			'contador'			=> 0
		);
	} while ($row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array));
	mysql_free_result($solicitacao_tipo_array);
	// fim - solicitacao_tipo_array
	
	$totalizar_solicitacao_praca_tipo = NULL;
	do {
		$totalizar_solicitacao_praca_tipo[$row_praca_array['praca']] = $solicitacao_tipo_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tipo ***********

// totalizar_solicitacao_tempo
if($row_relatorio['totalizar_solicitacao_tempo'] > 0){ 
	$totalizar_solicitacao_tempo = 0;
}
// fim - totalizar_solicitacao_tempo

// totalizar_solicitacao_praca_tempo *****************
if($row_relatorio['totalizar_solicitacao_praca_tempo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_solicitacao_praca_tempo = NULL;
	do {
		$totalizar_solicitacao_praca_tempo[$row_praca_array['praca']] = array('tempo_gasto' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tempo ***********


// totalizar_solicitacao_tempo_testador_geral
if($row_relatorio['totalizar_solicitacao_tempo_testador_geral'] > 0){ 
	$totalizar_solicitacao_tempo_testador_geral = 0;
}
// fim - totalizar_solicitacao_tempo_testador_geral

// totalizar_solicitacao_praca_tempo_testador_geral *****************
if($row_relatorio['totalizar_solicitacao_praca_tempo_testador_geral'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_solicitacao_praca_tempo_testador_geral = NULL;
	do {
		$totalizar_solicitacao_praca_tempo_testador_geral[$row_praca_array['praca']] = array('tempo_testador_geral_gasto' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tempo_testador_geral ***********

// totalizar_solicitacao_tempo_usuario_responsavel
if($row_relatorio['totalizar_solicitacao_tempo_usuario_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_solicitacao_tempo_usuario_responsavel = NULL;
	do {
		$totalizar_solicitacao_tempo_usuario_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_solicitacao_tempo_usuario_responsavel
		
// totalizar_solicitacao_praca_tempo_usuario_responsavel *****************
if($row_relatorio['totalizar_solicitacao_praca_tempo_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_tempo_usuario_responsavel = NULL;
	do {
		$totalizar_solicitacao_praca_tempo_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tempo_usuario_responsavel ***********

// totalizar_solicitacao_tempo_operador
if($row_relatorio['totalizar_solicitacao_tempo_operador'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_solicitacao_tempo_operador = NULL;
	do {
		$totalizar_solicitacao_tempo_operador[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_solicitacao_tempo_operador
		
// totalizar_solicitacao_praca_tempo_operador *****************
if($row_relatorio['totalizar_solicitacao_praca_tempo_operador'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_tempo_operador = NULL;
	do {
		$totalizar_solicitacao_praca_tempo_operador[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tempo_operador ***********

// totalizar_solicitacao_tempo_executante
if($row_relatorio['totalizar_solicitacao_tempo_executante'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_solicitacao_tempo_executante = NULL;
	do {
		$totalizar_solicitacao_tempo_executante[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_solicitacao_tempo_executante
		
// totalizar_solicitacao_praca_tempo_executante *****************
if($row_relatorio['totalizar_solicitacao_praca_tempo_executante'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_tempo_executante = NULL;
	do {
		$totalizar_solicitacao_praca_tempo_executante[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tempo_executante ***********

// totalizar_solicitacao_tempo_testador
if($row_relatorio['totalizar_solicitacao_tempo_testador'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_solicitacao_tempo_testador = NULL;
	do {
		$totalizar_solicitacao_tempo_testador[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_solicitacao_tempo_testador
		
// totalizar_solicitacao_praca_tempo_testador *****************
if($row_relatorio['totalizar_solicitacao_praca_tempo_testador'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_tempo_testador = NULL;
	do {
		$totalizar_solicitacao_praca_tempo_testador[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tempo_testador ***********

// totalizar_solicitacao_situacao
if($row_relatorio['totalizar_solicitacao_situacao'] > 0){ 
	
	$totalizar_solicitacao_situacao = NULL;
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 1, 
									'situacao'			=> 'criada', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 2, 
									'situacao'			=> 'recebida', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 3, 
									'situacao'			=> 'em análise', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 4, 
									'situacao'			=> 'em execução', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 5, 
									'situacao'			=> 'executada', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 6, 
									'situacao'			=> 'em testes', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 7, 
									'situacao'			=> 'testada', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 8, 
									'situacao'			=> 'em validação', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 9, 
									'situacao'			=> 'validada', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 10, 
									'situacao'			=> 'solucionada', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_situacao[] = array(
									'IdTipoSituacao' => 11, 
									'situacao'			=> 'reprovada', 
									'contador'			=> 0
									);
}
// fim - totalizar_solicitacao_situacao

// totalizar_solicitacao_praca_situacao *****************
if($row_relatorio['totalizar_solicitacao_praca_situacao'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_solicitacao_praca_situacao = NULL;
	do {
		$totalizar_solicitacao_praca_situacao[$row_praca_array['praca']] = array(
											array(
											'IdTipoSituacao' => 1, 
											'situacao'			=> 'criada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 2, 
											'situacao'			=> 'recebida', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 3, 
											'situacao'			=> 'em análise', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 4, 
											'situacao'			=> 'em execução', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 5, 
											'situacao'			=> 'executada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 6, 
											'situacao'			=> 'em testes', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 7, 
											'situacao'			=> 'testada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 8, 
											'situacao'			=> 'em validação', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 9, 
											'situacao'			=> 'validada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 10, 
											'situacao'			=> 'solucionada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 11, 
											'situacao'			=> 'reprovada', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_situacao ***********

// totalizar_solicitacao_status
if($row_relatorio['totalizar_solicitacao_status'] > 0){ 
	
	$totalizar_solicitacao_status = NULL;
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 1, 
									'status'			=> 'pendente solicitante', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 2, 
									'status'			=> 'pendente operador', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 3, 
									'status'			=> 'pendente executante', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 4, 
									'status'			=> 'pendente testador', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 5, 
									'status'			=> 'encaminhada para solicitante', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 6, 
									'status'			=> 'encaminhada para operador', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 7, 
									'status'			=> 'encaminhada para executante', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 8, 
									'status'			=> 'encaminhada para testador', 
									'contador'			=> 0
									);
	$totalizar_solicitacao_status[] = array(
									'IdTipoStatus' => 9, 
									'status'			=> 'encaminhada para analista', 
									'contador'			=> 0
									);
}
// fim - totalizar_solicitacao_status

// totalizar_solicitacao_praca_status *****************
if($row_relatorio['totalizar_solicitacao_praca_status'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_solicitacao_praca_status = NULL;
	do {
		$totalizar_solicitacao_praca_status[$row_praca_array['praca']] = array(
											array(
											'IdTipoStatus' => 1, 
											'status'			=> 'pendente solicitante', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 2, 
											'status'			=> 'pendente operador', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 3, 
											'status'			=> 'pendente executante', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 4, 
											'status'			=> 'pendente testador', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 5, 
											'status'			=> 'encaminhada para solicitante', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 6, 
											'status'			=> 'encaminhada para operador', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 7, 
											'status'			=> 'encaminhada para executante', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 8, 
											'status'			=> 'encaminhada para testador', 
											'contador'			=> 0
											),
											array(
											'IdTipoStatus' => 9, 
											'status'			=> 'encaminhada para analista', 
											'contador'			=> 0
											)					
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_status ***********

// totalizar_solicitacao_envolvido
if($row_relatorio['totalizar_solicitacao_envolvido'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_solicitacao_envolvido = NULL;
	do {
		$totalizar_solicitacao_envolvido[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_solicitacao_envolvido

// totalizar_solicitacao_praca_envolvido *****************
if($row_relatorio['totalizar_solicitacao_praca_envolvido'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_envolvido = NULL;
	do {
		$totalizar_solicitacao_praca_envolvido[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_envolvido ***********

// totalizar_solicitacao_tipo_envolvido
if($row_relatorio['totalizar_solicitacao_tipo_envolvido'] > 0){ 

	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array

	// solicitacao_tipo_array
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_tipo_array = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY IdTipoSolicitacao ASC";
	$solicitacao_tipo_array = mysql_query($query_solicitacao_tipo_array, $conexao) or die(mysql_error());
	$row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array);
	$totalRows_solicitacao_tipo_array = mysql_num_rows($solicitacao_tipo_array);
	$solicitacao_tipo_array_atual = NULL;
	do {
		$solicitacao_tipo_array_atual[] = array(
			'IdTipoSolicitacao' => $row_solicitacao_tipo_array['IdTipoSolicitacao'], 
			'titulo'			=> $row_solicitacao_tipo_array['titulo'], 
			'contador'			=> 0
		);
	} while ($row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array));
	mysql_free_result($solicitacao_tipo_array);
	// fim - solicitacao_tipo_array
	
	$totalizar_solicitacao_tipo_envolvido = NULL;
	do {

		$totalizar_solicitacao_tipo_envolvido[$row_usuarios_array['nome']] = $solicitacao_tipo_array_atual;

	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	
}
// fim - totalizar_solicitacao_tipo_envolvido

// totalizar_solicitacao_praca_tipo_envolvido ***********
if($row_relatorio['totalizar_solicitacao_praca_tipo_envolvido'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array

	// solicitacao_tipo_array
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_tipo_array = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY IdTipoSolicitacao ASC";
	$solicitacao_tipo_array = mysql_query($query_solicitacao_tipo_array, $conexao) or die(mysql_error());
	$row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array);
	$totalRows_solicitacao_tipo_array = mysql_num_rows($solicitacao_tipo_array);
	$solicitacao_tipo_array_atual = NULL;
	do {
		$solicitacao_tipo_array_atual[] = array(
			'IdTipoSolicitacao' => $row_solicitacao_tipo_array['IdTipoSolicitacao'], 
			'titulo'			=> $row_solicitacao_tipo_array['titulo'], 
			'contador'			=> 0
		);
	} while ($row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array));
	mysql_free_result($solicitacao_tipo_array);
	// fim - solicitacao_tipo_array
					
	$totalizar_solicitacao_praca_tipo_envolvido = NULL;
	do {

		// usuarios_array
		mysql_select_db($database_conexao, $conexao);
		$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
		$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
		$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
		$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
		// fim - usuarios_array
		
		do {
			
			$totalizar_solicitacao_praca_tipo_envolvido[$row_praca_array['praca']][$row_usuarios_array['nome']] = $solicitacao_tipo_array_atual;
			
		} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
		mysql_free_result($usuarios_array);

	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tipo_envolvido ***********

// totalizar_solicitacao_situacao_envolvido
if($row_relatorio['totalizar_solicitacao_situacao_envolvido'] > 0){ 

	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array

	// solicitacao_situacao_array	
	$solicitacao_situacao_array_atual = NULL;
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 1, 
									'situacao'			=> 'criada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 2, 
									'situacao'			=> 'recebida', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 3, 
									'situacao'			=> 'em análise', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 4, 
									'situacao'			=> 'analisada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 5, 
									'situacao'			=> 'aprovada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 6, 
									'situacao'			=> 'em orçamento', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 7, 
									'situacao'			=> 'em execução', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 8, 
									'situacao'			=> 'executada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 9, 
									'situacao'			=> 'em testes', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 10, 
									'situacao'			=> 'testada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 11, 
									'situacao'			=> 'em validação', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 12, 
									'situacao'			=> 'validada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 13, 
									'situacao'			=> 'solucionada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 14, 
									'situacao'			=> 'reprovada', 
									'contador'			=> 0
									);
	// fim - solicitacao_situacao_array
	
	$totalizar_solicitacao_situacao_envolvido = NULL;
	do {

		$totalizar_solicitacao_situacao_envolvido[$row_usuarios_array['nome']] = $solicitacao_situacao_array_atual;

	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	
}
// fim - totalizar_solicitacao_situacao_envolvido

// totalizar_solicitacao_praca_situacao_envolvido ***********
if($row_relatorio['totalizar_solicitacao_praca_situacao_envolvido'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array

	// solicitacao_situacao_array	
	$solicitacao_situacao_array_atual = NULL;
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 1, 
									'situacao'			=> 'criada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 2, 
									'situacao'			=> 'recebida', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 3, 
									'situacao'			=> 'em análise', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 4, 
									'situacao'			=> 'analisada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 5, 
									'situacao'			=> 'aprovada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 6, 
									'situacao'			=> 'em orçamento', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 7, 
									'situacao'			=> 'em execução', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 8, 
									'situacao'			=> 'executada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 9, 
									'situacao'			=> 'em testes', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 10, 
									'situacao'			=> 'testada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 11, 
									'situacao'			=> 'em validação', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 12, 
									'situacao'			=> 'validada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 13, 
									'situacao'			=> 'solucionada', 
									'contador'			=> 0
									);
	$solicitacao_situacao_array_atual[] = array(
									'IdTipoSituacao' => 14, 
									'situacao'			=> 'reprovada', 
									'contador'			=> 0
									);
	// fim - solicitacao_situacao_array
				
	$totalizar_solicitacao_praca_situacao_envolvido = NULL;
	do {

		// usuarios_array
		mysql_select_db($database_conexao, $conexao);
		$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
		$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
		$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
		$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
		// fim - usuarios_array
		
		do {
			
			$totalizar_solicitacao_praca_situacao_envolvido[$row_praca_array['praca']][$row_usuarios_array['nome']] = $solicitacao_situacao_array_atual;
			
		} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
		mysql_free_result($usuarios_array);

	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_situacao_envolvido ***********

// totalizar_solicitacao_tempo_envolvido
if($row_relatorio['totalizar_solicitacao_tempo_envolvido'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_solicitacao_tempo_envolvido = NULL;
	do {
		$totalizar_solicitacao_tempo_envolvido[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_solicitacao_tempo_envolvido
		
// totalizar_solicitacao_praca_tempo_envolvido *****************
if($row_relatorio['totalizar_solicitacao_praca_tempo_envolvido'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_tempo_envolvido = NULL;
	do {
		$totalizar_solicitacao_praca_tempo_envolvido[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_tempo_envolvido ***********

// totalizar_solicitacao_usuario_responsavel
if($row_relatorio['totalizar_solicitacao_usuario_responsavel'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_solicitacao_usuario_responsavel = NULL;
	do {
		$totalizar_solicitacao_usuario_responsavel[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_solicitacao_usuario_responsavel

// totalizar_solicitacao_praca_usuario_responsavel *****************
if($row_relatorio['totalizar_solicitacao_praca_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_usuario_responsavel = NULL;
	do {
		$totalizar_solicitacao_praca_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_usuario_responsavel ***********

// totalizar_solicitacao_operador
if($row_relatorio['totalizar_solicitacao_operador'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_solicitacao_operador = NULL;
	do {
		$totalizar_solicitacao_operador[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_solicitacao_operador

// totalizar_solicitacao_praca_operador *****************
if($row_relatorio['totalizar_solicitacao_praca_operador'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_operador = NULL;
	do {
		$totalizar_solicitacao_praca_operador[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_operador ***********

// totalizar_solicitacao_executante
if($row_relatorio['totalizar_solicitacao_executante'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_solicitacao_executante = NULL;
	do {
		$totalizar_solicitacao_executante[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_solicitacao_executante

// totalizar_solicitacao_praca_executante *****************
if($row_relatorio['totalizar_solicitacao_praca_executante'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_executante = NULL;
	do {
		$totalizar_solicitacao_praca_executante[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_executante ***********

// totalizar_solicitacao_testador
if($row_relatorio['totalizar_solicitacao_testador'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_solicitacao_testador = NULL;
	do {
		$totalizar_solicitacao_testador[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_solicitacao_testador

// totalizar_solicitacao_praca_testador *****************
if($row_relatorio['totalizar_solicitacao_praca_testador'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_testador = NULL;
	do {
		$totalizar_solicitacao_praca_testador[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_testador ***********

// totalizar_solicitacao_orcamento_usuario_responsavel
if($row_relatorio['totalizar_solicitacao_orcamento_usuario_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_solicitacao_orcamento_usuario_responsavel = NULL;
	do {
		$totalizar_solicitacao_orcamento_usuario_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_solicitacao_orcamento_usuario_responsavel
			
// totalizar_solicitacao_praca_orcamento_usuario_responsavel *****************
if($row_relatorio['totalizar_solicitacao_praca_orcamento_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_solicitacao_praca_orcamento_usuario_responsavel = NULL;
	do {
		$totalizar_solicitacao_praca_orcamento_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_solicitacao_praca_orcamento_usuario_responsavel ***********

// totalizar_solicitacao_tipo_responsavel
if($row_relatorio['totalizar_solicitacao_tipo_responsavel'] > 0){ 

	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$usuarios_array_atual = NULL;

	// solicitacao_tipo_array
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_tipo_array = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY IdTipoSolicitacao ASC";
	$solicitacao_tipo_array = mysql_query($query_solicitacao_tipo_array, $conexao) or die(mysql_error());
	$row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array);
	$totalRows_solicitacao_tipo_array = mysql_num_rows($solicitacao_tipo_array);
	$solicitacao_tipo_array_atual = NULL;
	do {
		$solicitacao_tipo_array_atual[] = array(
			'IdTipoSolicitacao' => $row_solicitacao_tipo_array['IdTipoSolicitacao'], 
			'titulo'			=> $row_solicitacao_tipo_array['titulo'], 
			'contador'			=> 0
		);
	} while ($row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array));
	mysql_free_result($solicitacao_tipo_array);
	// fim - solicitacao_tipo_array
	
	$totalizar_solicitacao_tipo_responsavel = NULL;
	do {

		$totalizar_solicitacao_tipo_responsavel[$row_usuarios_array['IdUsuario']] = $solicitacao_tipo_array_atual;
		
		// nome do usuario
		if(count($usuarios_array_atual) < $totalRows_usuarios_array) { 
			$usuarios_array_atual[$row_usuarios_array['IdUsuario']] = $row_usuarios_array['nome'];
		}
		// fim - nome do usuario

	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	
}
// fim - totalizar_solicitacao_tipo_responsavel

// totalizar_solicitacao_praca_tipo_responsavel ***********
if($row_relatorio['totalizar_solicitacao_praca_tipo_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array

	// solicitacao_tipo_array
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_tipo_array = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY IdTipoSolicitacao ASC";
	$solicitacao_tipo_array = mysql_query($query_solicitacao_tipo_array, $conexao) or die(mysql_error());
	$row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array);
	$totalRows_solicitacao_tipo_array = mysql_num_rows($solicitacao_tipo_array);
	$solicitacao_tipo_array_atual = NULL;
	do {
		$solicitacao_tipo_array_atual[] = array(
			'IdTipoSolicitacao' => $row_solicitacao_tipo_array['IdTipoSolicitacao'], 
			'titulo'			=> $row_solicitacao_tipo_array['titulo'], 
			'contador'			=> 0
		);
	} while ($row_solicitacao_tipo_array = mysql_fetch_assoc($solicitacao_tipo_array));
	mysql_free_result($solicitacao_tipo_array);
	// fim - solicitacao_tipo_array
					
	$totalizar_solicitacao_praca_tipo_responsavel = NULL;
	do {

		// usuarios_array
		mysql_select_db($database_conexao, $conexao);
		$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
		$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
		$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
		$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
		// fim - usuarios_array
		$usuarios_array_atual = NULL;
			
		do {
			
			$totalizar_solicitacao_praca_tipo_responsavel[$row_praca_array['praca']][$row_usuarios_array['IdUsuario']] = $solicitacao_tipo_array_atual;
			
			// nome do usuario
			if(count($usuarios_array_atual) < $totalRows_usuarios_array) { 
				$usuarios_array_atual[$row_usuarios_array['IdUsuario']] = $row_usuarios_array['nome'];
			}
			// fim - nome do usuario
			
		} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
		mysql_free_result($usuarios_array);

	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);

}
// fim - totalizar_solicitacao_praca_tipo_responsavel ***********



// totalizar_suporte_avaliacao_atendimento
if($row_relatorio['totalizar_suporte_avaliacao_atendimento'] > 0){ 
	
	$totalizar_suporte_avaliacao_atendimento = NULL;
	$totalizar_suporte_avaliacao_atendimento[] = array(
									'IdTipoSolicitacao' => 1, 
									'titulo'			=> 'Excelente', 
									'contador'			=> 0
									);
	$totalizar_suporte_avaliacao_atendimento[] = array(
									'IdTipoSolicitacao' => 2, 
									'titulo'			=> 'Bom', 
									'contador'			=> 0
									);
	$totalizar_suporte_avaliacao_atendimento[] = array(
									'IdTipoSolicitacao' => 3, 
									'titulo'			=> 'Regular', 
									'contador'			=> 0
									);
	$totalizar_suporte_avaliacao_atendimento[] = array(
									'IdTipoSolicitacao' => 4, 
									'titulo'			=> 'Ruim', 
									'contador'			=> 0
									);
	$totalizar_suporte_avaliacao_atendimento[] = array(
									'IdTipoSolicitacao' => 5, 
									'titulo'			=> 'Péssimo', 
									'contador'			=> 0
									);
}
// fim - totalizar_suporte_avaliacao_atendimento

// totalizar_suporte_praca_avaliacao_atendimento *****************
if($row_relatorio['totalizar_suporte_praca_avaliacao_atendimento'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_suporte_praca_avaliacao_atendimento = NULL;
	do {
		$totalizar_suporte_praca_avaliacao_atendimento[$row_praca_array['praca']] = array( 
											array('IdTipoSolicitacao' => 1, 
											'titulo'			=> 'Excelente', 
											'contador'			=> 0
											), 
											array('IdTipoSolicitacao' => 2, 
											'titulo'			=> 'Bom', 
											'contador'			=> 0
											), 
											array('IdTipoSolicitacao' => 3, 
											'titulo'			=> 'Regular', 
											'contador'			=> 0
											), 
											array('IdTipoSolicitacao' => 4, 
											'titulo'			=> 'Ruim', 
											'contador'			=> 0
											), 
											array('IdTipoSolicitacao' => 5, 
											'titulo'			=> 'Péssimo', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_suporte_praca_avaliacao_atendimento);
	
}
// fim - totalizar_suporte_praca_avaliacao_atendimento ***********

// totalizar_suporte_tipo_atendimento
if($row_relatorio['totalizar_suporte_tipo_atendimento'] > 0){ 
	// suporte_tipo_atendimento_suporte_totalizar
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_atendimento_suporte_totalizar = "SELECT * FROM suporte_tipo_atendimento ORDER BY IdTipoAtendimento ASC";
	$suporte_tipo_atendimento_suporte_totalizar = mysql_query($query_suporte_tipo_atendimento_suporte_totalizar, $conexao) or die(mysql_error());
	$row_suporte_tipo_atendimento_suporte_totalizar = mysql_fetch_assoc($suporte_tipo_atendimento_suporte_totalizar);
	$totalRows_suporte_tipo_atendimento_suporte_totalizar = mysql_num_rows($suporte_tipo_atendimento_suporte_totalizar);
	// fim - suporte_tipo_atendimento_suporte_totalizar
	$totalizar_suporte_tipo_atendimento = NULL;
	do {
		$totalizar_suporte_tipo_atendimento[] = array(
										'IdTipoAtendimento' => $row_suporte_tipo_atendimento_suporte_totalizar['IdTipoAtendimento'], 
										'descricao'			=> $row_suporte_tipo_atendimento_suporte_totalizar['descricao'], 
										'contador'			=> 0
										);
	} while ($row_suporte_tipo_atendimento_suporte_totalizar = mysql_fetch_assoc($suporte_tipo_atendimento_suporte_totalizar));
	mysql_free_result($suporte_tipo_atendimento_suporte_totalizar);
}
// fim - totalizar_suporte_tipo_atendimento

// totalizar_suporte_praca_tipo_atendimento *****************
if($row_relatorio['totalizar_suporte_praca_tipo_atendimento'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// suporte_tipo_atendimento_array
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_atendimento_array = "SELECT * FROM suporte_tipo_atendimento ORDER BY IdTipoAtendimento ASC";
	$suporte_tipo_atendimento_array = mysql_query($query_suporte_tipo_atendimento_array, $conexao) or die(mysql_error());
	$row_suporte_tipo_atendimento_array = mysql_fetch_assoc($suporte_tipo_atendimento_array);
	$totalRows_suporte_tipo_atendimento_array = mysql_num_rows($suporte_tipo_atendimento_array);
	$suporte_tipo_atendimento_array_atual = NULL;
	do {
		$suporte_tipo_atendimento_array_atual[] = array(
			'IdTipoAtendimento' => $row_suporte_tipo_atendimento_array['IdTipoAtendimento'], 
			'descricao'			=> $row_suporte_tipo_atendimento_array['descricao'], 
			'contador'			=> 0
		);
	} while ($row_suporte_tipo_atendimento_array = mysql_fetch_assoc($suporte_tipo_atendimento_array));
	mysql_free_result($suporte_tipo_atendimento_array);
	// fim - suporte_tipo_atendimento_array
	
	$totalizar_suporte_praca_tipo_atendimento = NULL;
	do {
		$totalizar_suporte_praca_tipo_atendimento[$row_praca_array['praca']] = $suporte_tipo_atendimento_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_tipo_atendimento ***********

// totalizar_suporte_tipo_recomendacao
if($row_relatorio['totalizar_suporte_tipo_recomendacao'] > 0){ 
	// suporte_tipo_recomendacao_suporte_totalizar
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_recomendacao_suporte_totalizar = "SELECT * FROM suporte_tipo_recomendacao ORDER BY IdTipoRecomendacao ASC";
	$suporte_tipo_recomendacao_suporte_totalizar = mysql_query($query_suporte_tipo_recomendacao_suporte_totalizar, $conexao) or die(mysql_error());
	$row_suporte_tipo_recomendacao_suporte_totalizar = mysql_fetch_assoc($suporte_tipo_recomendacao_suporte_totalizar);
	$totalRows_suporte_tipo_recomendacao_suporte_totalizar = mysql_num_rows($suporte_tipo_recomendacao_suporte_totalizar);
	// fim - suporte_tipo_recomendacao_suporte_totalizar
	$totalizar_suporte_tipo_recomendacao = NULL;
	do {
		$totalizar_suporte_tipo_recomendacao[] = array(
										'IdTipoRecomendacao' => $row_suporte_tipo_recomendacao_suporte_totalizar['IdTipoRecomendacao'], 
										'titulo'			=> $row_suporte_tipo_recomendacao_suporte_totalizar['titulo'], 
										'contador'			=> 0
										);
	} while ($row_suporte_tipo_recomendacao_suporte_totalizar = mysql_fetch_assoc($suporte_tipo_recomendacao_suporte_totalizar));
	mysql_free_result($suporte_tipo_recomendacao_suporte_totalizar);
}
// fim - totalizar_suporte_tipo_recomendacao

// totalizar_suporte_praca_tipo_recomendacao *****************
if($row_relatorio['totalizar_suporte_praca_tipo_recomendacao'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// suporte_tipo_recomendacao_array
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_recomendacao_array = "SELECT * FROM suporte_tipo_recomendacao ORDER BY IdTipoRecomendacao ASC";
	$suporte_tipo_recomendacao_array = mysql_query($query_suporte_tipo_recomendacao_array, $conexao) or die(mysql_error());
	$row_suporte_tipo_recomendacao_array = mysql_fetch_assoc($suporte_tipo_recomendacao_array);
	$totalRows_suporte_tipo_recomendacao_array = mysql_num_rows($suporte_tipo_recomendacao_array);
	$suporte_tipo_recomendacao_array_atual = NULL;
	do {
		$suporte_tipo_recomendacao_array_atual[] = array(
			'IdTipoRecomendacao' => $row_suporte_tipo_recomendacao_array['IdTipoRecomendacao'], 
			'titulo'			=> $row_suporte_tipo_recomendacao_array['titulo'], 
			'contador'			=> 0
		);
	} while ($row_suporte_tipo_recomendacao_array = mysql_fetch_assoc($suporte_tipo_recomendacao_array));
	mysql_free_result($suporte_tipo_recomendacao_array);
	// fim - suporte_tipo_recomendacao_array
	
	$totalizar_suporte_praca_tipo_recomendacao = NULL;
	do {
		$totalizar_suporte_praca_tipo_recomendacao[$row_praca_array['praca']] = $suporte_tipo_recomendacao_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_tipo_recomendacao ***********

// totalizar_suporte_tipo_parecer
if($row_relatorio['totalizar_suporte_tipo_parecer'] > 0){ 
	// suporte_tipo_parecer_suporte_totalizar
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_parecer_suporte_totalizar = "SELECT * FROM suporte_tipo_parecer ORDER BY IdTipoParecer ASC";
	$suporte_tipo_parecer_suporte_totalizar = mysql_query($query_suporte_tipo_parecer_suporte_totalizar, $conexao) or die(mysql_error());
	$row_suporte_tipo_parecer_suporte_totalizar = mysql_fetch_assoc($suporte_tipo_parecer_suporte_totalizar);
	$totalRows_suporte_tipo_parecer_suporte_totalizar = mysql_num_rows($suporte_tipo_parecer_suporte_totalizar);
	// fim - suporte_tipo_parecer_suporte_totalizar
	$totalizar_suporte_tipo_parecer = NULL;
	do {
		$totalizar_suporte_tipo_parecer[] = array(
										'IdTipoParecer' => $row_suporte_tipo_parecer_suporte_totalizar['IdTipoParecer'], 
										'descricao'			=> $row_suporte_tipo_parecer_suporte_totalizar['descricao'], 
										'contador'			=> 0
										);
	} while ($row_suporte_tipo_parecer_suporte_totalizar = mysql_fetch_assoc($suporte_tipo_parecer_suporte_totalizar));
	mysql_free_result($suporte_tipo_parecer_suporte_totalizar);
}
// fim - totalizar_suporte_tipo_parecer

// totalizar_suporte_praca_tipo_parecer *****************
if($row_relatorio['totalizar_suporte_praca_tipo_parecer'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// suporte_tipo_parecer_array
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_parecer_array = "SELECT * FROM suporte_tipo_parecer ORDER BY IdTipoParecer ASC";
	$suporte_tipo_parecer_array = mysql_query($query_suporte_tipo_parecer_array, $conexao) or die(mysql_error());
	$row_suporte_tipo_parecer_array = mysql_fetch_assoc($suporte_tipo_parecer_array);
	$totalRows_suporte_tipo_parecer_array = mysql_num_rows($suporte_tipo_parecer_array);
	$suporte_tipo_parecer_array_atual = NULL;
	do {
		$suporte_tipo_parecer_array_atual[] = array(
			'IdTipoParecer' => $row_suporte_tipo_parecer_array['IdTipoParecer'], 
			'descricao'			=> $row_suporte_tipo_parecer_array['descricao'], 
			'contador'			=> 0
		);
	} while ($row_suporte_tipo_parecer_array = mysql_fetch_assoc($suporte_tipo_parecer_array));
	mysql_free_result($suporte_tipo_parecer_array);
	// fim - suporte_tipo_parecer_array
	
	$totalizar_suporte_praca_tipo_parecer = NULL;
	do {
		$totalizar_suporte_praca_tipo_parecer[$row_praca_array['praca']] = $suporte_tipo_parecer_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_tipo_parecer ***********
						
// totalizar_suporte_tempo
if($row_relatorio['totalizar_suporte_tempo'] > 0){ 
	$totalizar_suporte_tempo = 0;
}
// fim - totalizar_suporte_tempo

// totalizar_suporte_praca_tempo *****************
if($row_relatorio['totalizar_suporte_praca_tempo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_suporte_praca_tempo = NULL;
	do {
		$totalizar_suporte_praca_tempo[$row_praca_array['praca']] = array('tempo_gasto' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_tempo ***********

// totalizar_suporte_valor
if($row_relatorio['totalizar_suporte_valor'] > 0){ 
	$totalizar_suporte_valor = 0;
}
// fim - totalizar_suporte_valor

// totalizar_suporte_praca_valor *****************
if($row_relatorio['totalizar_suporte_praca_valor'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_suporte_praca_valor = NULL;
	do {
		$totalizar_suporte_praca_valor[$row_praca_array['praca']] = array('valor' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_valor ***********

// totalizar_suporte_valor_usuario_responsavel
if($row_relatorio['totalizar_suporte_valor_usuario_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_suporte_valor_usuario_responsavel = NULL;
	do {
		$totalizar_suporte_valor_usuario_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_suporte_valor_usuario_responsavel
			
// totalizar_suporte_praca_valor_usuario_responsavel *****************
if($row_relatorio['totalizar_suporte_praca_valor_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_suporte_praca_valor_usuario_responsavel = NULL;
	do {
		$totalizar_suporte_praca_valor_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_valor_usuario_responsavel ***********

// totalizar_suporte_situacao
if($row_relatorio['totalizar_suporte_situacao'] > 0){ 
	
	$totalizar_suporte_situacao = NULL;
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 1, 
									'situacao'			=> 'criada', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 2, 
									'situacao'			=> 'analisada', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 3, 
									'situacao'			=> 'em execução', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 4, 
									'situacao'			=> 'em validação', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 5, 
									'situacao'			=> 'solicitado suporte', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 6, 
									'situacao'			=> 'solicitado visita', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 7, 
									'situacao'			=> 'encaminhado para solicitação', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 8, 
									'situacao'			=> 'solucionada', 
									'contador'			=> 0
									);
	$totalizar_suporte_situacao[] = array(
									'IdTipoSituacao' => 9, 
									'situacao'			=> 'cancelada', 
									'contador'			=> 0
									);
}
// fim - totalizar_suporte_situacao

// totalizar_suporte_praca_situacao *****************
if($row_relatorio['totalizar_suporte_praca_situacao'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_suporte_praca_situacao = NULL;
	do {
		$totalizar_suporte_praca_situacao[$row_praca_array['praca']] = array(
											array(
											'IdTipoSituacao' => 1, 
											'situacao'			=> 'criada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 2, 
											'situacao'			=> 'analisada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 3, 
											'situacao'			=> 'em execução', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 4, 
											'situacao'			=> 'em validação', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 5, 
											'situacao'			=> 'solicitado suporte', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 6, 
											'situacao'			=> 'solicitado visita', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 7, 
											'situacao'			=> 'encaminhado para solicitação', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 8, 
											'situacao'			=> 'solucionada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 9, 
											'situacao'			=> 'cancelada', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_situacao ***********

// totalizar_suporte_usuario_responsavel
if($row_relatorio['totalizar_suporte_usuario_responsavel'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_suporte_usuario_responsavel = NULL;
	do {
		$totalizar_suporte_usuario_responsavel[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_suporte_usuario_responsavel

// totalizar_suporte_praca_usuario_responsavel *****************
if($row_relatorio['totalizar_suporte_praca_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_suporte_praca_usuario_responsavel = NULL;
	do {
		$totalizar_suporte_praca_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_usuario_responsavel ***********

// totalizar_suporte_tempo_usuario_responsavel
if($row_relatorio['totalizar_suporte_tempo_usuario_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_suporte_tempo_usuario_responsavel = NULL;
	do {
		$totalizar_suporte_tempo_usuario_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_suporte_tempo_usuario_responsavel
			
// totalizar_suporte_praca_tempo_usuario_responsavel *****************
if($row_relatorio['totalizar_suporte_praca_tempo_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_suporte_praca_tempo_usuario_responsavel = NULL;
	do {
		$totalizar_suporte_praca_tempo_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_tempo_usuario_responsavel ***********

// totalizar_suporte_usuario_envolvido
if($row_relatorio['totalizar_suporte_usuario_envolvido'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_suporte_usuario_envolvido = NULL;
	do {
		$totalizar_suporte_usuario_envolvido[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_suporte_usuario_envolvido

// totalizar_suporte_praca_usuario_envolvido *****************
if($row_relatorio['totalizar_suporte_praca_usuario_envolvido'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_suporte_praca_usuario_envolvido = NULL;
	do {
		$totalizar_suporte_praca_usuario_envolvido[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_usuario_envolvido ***********

// totalizar_suporte_tempo_usuario_envolvido
if($row_relatorio['totalizar_suporte_tempo_usuario_envolvido'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_suporte_tempo_usuario_envolvido = NULL;
	do {
		$totalizar_suporte_tempo_usuario_envolvido[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_suporte_tempo_usuario_envolvido
			
// totalizar_suporte_praca_tempo_usuario_envolvido *****************
if($row_relatorio['totalizar_suporte_praca_tempo_usuario_envolvido'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	

	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_suporte_praca_tempo_usuario_envolvido = NULL;
	do {
		$totalizar_suporte_praca_tempo_usuario_envolvido[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_tempo_usuario_envolvido ***********

// totalizar_suporte_tipo_visita
if($row_relatorio['totalizar_suporte_tipo_visita'] > 0){ 
	
	$totalizar_suporte_tipo_visita = NULL;
	$totalizar_suporte_tipo_visita[] = array(
									'visita17' => 1, 
									'titulo'			=> 'Nenhum', 
									'contador'			=> 0
									);
	$totalizar_suporte_tipo_visita[] = array(
									'visita17' => 2, 
									'titulo'			=> 'Sem Limite', 
									'contador'			=> 0
									);
	$totalizar_suporte_tipo_visita[] = array(
									'visita17' => 3, 
									'titulo'			=> 'Mensal', 
									'contador'			=> 0
									);
	$totalizar_suporte_tipo_visita[] = array(
									'visita17' => 4, 
									'titulo'			=> 'Trismestral', 
									'contador'			=> 0
									);
	$totalizar_suporte_tipo_visita[] = array(
									'visita17' => 5, 
									'titulo'			=> 'Sem visita', 
									'contador'			=> 0
									);
}
// fim - totalizar_suporte_tipo_visita

// totalizar_suporte_praca_tipo_visita *****************
if($row_relatorio['totalizar_suporte_praca_tipo_visita'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_suporte_praca_tipo_visita = NULL;
	do {
		
		
		$totalizar_suporte_praca_tipo_visita[$row_praca_array['praca']] = array( 
											array('visita17' => 1, 
											'titulo'			=> 'Nenhum', 
											'contador'			=> 0
											), 
											array('visita17' => 2, 
											'titulo'			=> 'Sem Limite', 
											'contador'			=> 0
											), 
											array('visita17' => 3, 
											'titulo'			=> 'Mensal', 
											'contador'			=> 0
											), 
											array('visita17' => 4, 
											'titulo'			=> 'Trimestral', 
											'contador'			=> 0
											), 
											array('visita17' => 5, 
											'titulo'			=> 'Sem visita', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_suporte_praca_tipo_visita);
	
}
// fim - totalizar_suporte_praca_tipo_visita ***********

// totalizar_suporte_optante_acumulo
if($row_relatorio['totalizar_suporte_optante_acumulo'] > 0){ 
	
	$totalizar_suporte_optante_acumulo = NULL;
	$totalizar_suporte_optante_acumulo[] = array(
									'optacuv17' => 'N', 
									'titulo'			=> 'Não', 
									'contador'			=> 0
									);
	$totalizar_suporte_optante_acumulo[] = array(
									'optacuv17' => 'S', 
									'titulo'			=> 'Sim', 
									'contador'			=> 0
									);
}
// fim - totalizar_suporte_optante_acumulo

// totalizar_suporte_praca_optante_acumulo *****************
if($row_relatorio['totalizar_suporte_praca_optante_acumulo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_suporte_praca_optante_acumulo = NULL;
	do {
		
		
		$totalizar_suporte_praca_optante_acumulo[$row_praca_array['praca']] = array( 
											array('optacuv17' => 'N', 
											'titulo'			=> 'Não', 
											'contador'			=> 0
											), 
											array('optacuv17' => 'S', 
											'titulo'			=> 'Sim', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_suporte_praca_optante_acumulo);
	
}
// fim - totalizar_suporte_praca_optante_acumulo ***********

// totalizar_suporte_avaliacao_atendimento_responsavel
if($row_relatorio['totalizar_suporte_avaliacao_atendimento_responsavel'] > 0){ 

	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$usuarios_array_atual = NULL;
	
	$totalizar_suporte_avaliacao_atendimento_responsavel = NULL;
	do {
		
		$totalizar_suporte_avaliacao_atendimento_responsavel[$row_usuarios_array['IdUsuario']] = array( 
		array('IdTipoAvaliacao' => 1, 
		'titulo'			=> 'Excelente', 
		'contador'			=> 0
		), 
		array('IdTipoAvaliacao' => 2, 
		'titulo'			=> 'Bom', 
		'contador'			=> 0
		), 
		array('IdTipoAvaliacao' => 3, 
		'titulo'			=> 'Regular', 
		'contador'			=> 0
		), 
		array('IdTipoAvaliacao' => 4, 
		'titulo'			=> 'Ruim', 
		'contador'			=> 0
		), 
		array('IdTipoAvaliacao' => 5, 
		'titulo'			=> 'Péssimo', 
		'contador'			=> 0
		)
		);
		
		// nome do usuario
		if(count($usuarios_array_atual) < $totalRows_usuarios_array) { 
			$usuarios_array_atual[$row_usuarios_array['IdUsuario']] = $row_usuarios_array['nome'];
		}
		// fim - nome do usuario
		
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	
}
// fim - totalizar_suporte_avaliacao_atendimento_responsavel

// totalizar_suporte_praca_avaliacao_atendimento_responsavel ***********
if($row_relatorio['totalizar_suporte_praca_avaliacao_atendimento_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_suporte_praca_avaliacao_atendimento_responsavel = NULL;
	do {

		// usuarios_array
		mysql_select_db($database_conexao, $conexao);
		$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
		$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
		$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
		$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
		// fim - usuarios_array
		$usuarios_array_atual = NULL;
		
		do {
			
			$totalizar_suporte_praca_avaliacao_atendimento_responsavel[$row_praca_array['praca']][$row_usuarios_array['IdUsuario']] = array( 
			array('IdTipoAvaliacao' => 1, 
			'titulo'			=> 'Excelente', 
			'contador'			=> 0
			), 
			array('IdTipoAvaliacao' => 2, 
			'titulo'			=> 'Bom', 
			'contador'			=> 0
			), 
			array('IdTipoAvaliacao' => 3, 
			'titulo'			=> 'Regular', 
			'contador'			=> 0
			), 
			array('IdTipoAvaliacao' => 4, 
			'titulo'			=> 'Ruim', 
			'contador'			=> 0
			), 
			array('IdTipoAvaliacao' => 5, 
			'titulo'			=> 'Péssimo', 
			'contador'			=> 0
			)
			);
			
			// nome do usuario
			if(count($usuarios_array_atual) < $totalRows_usuarios_array) { 
				$usuarios_array_atual[$row_usuarios_array['IdUsuario']] = $row_usuarios_array['nome'];
			}
			// fim - nome do usuario
			
		} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
		mysql_free_result($usuarios_array);

	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_avaliacao_atendimento_responsavel ***********

// totalizar_suporte_tipo_atendimento_envolvido
if($row_relatorio['totalizar_suporte_tipo_atendimento_envolvido'] > 0){ 

	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$usuarios_array_atual = NULL;
	
	// suporte_tipo_atendimento_array
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_atendimento_array = "SELECT * FROM suporte_tipo_atendimento ORDER BY IdTipoAtendimento ASC";
	$suporte_tipo_atendimento_array = mysql_query($query_suporte_tipo_atendimento_array, $conexao) or die(mysql_error());
	$row_suporte_tipo_atendimento_array = mysql_fetch_assoc($suporte_tipo_atendimento_array);
	$totalRows_suporte_tipo_atendimento_array = mysql_num_rows($suporte_tipo_atendimento_array);
	$suporte_tipo_atendimento_array_atual = NULL;
	do {
		$suporte_tipo_atendimento_array_atual[] = array(
			'IdTipoAtendimento' => $row_suporte_tipo_atendimento_array['IdTipoAtendimento'], 
			'descricao'			=> $row_suporte_tipo_atendimento_array['descricao'], 
			'contador'			=> 0
		);
	} while ($row_suporte_tipo_atendimento_array = mysql_fetch_assoc($suporte_tipo_atendimento_array));
	mysql_free_result($suporte_tipo_atendimento_array);
	// fim - suporte_tipo_atendimento_array
	
	$totalizar_suporte_tipo_atendimento_envolvido = NULL;
	do {

		$totalizar_suporte_tipo_atendimento_envolvido[$row_usuarios_array['IdUsuario']] = $suporte_tipo_atendimento_array_atual;
		
		// nome do usuario
		if(count($usuarios_array_atual) < $totalRows_usuarios_array) { 
			$usuarios_array_atual[$row_usuarios_array['IdUsuario']] = $row_usuarios_array['nome'];
		}
		// fim - nome do usuario

	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	
}
// fim - totalizar_suporte_tipo_atendimento_envolvido

// totalizar_suporte_praca_tipo_atendimento_envolvido ***********
if($row_relatorio['totalizar_suporte_praca_tipo_atendimento_envolvido'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// suporte_tipo_atendimento_array
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_atendimento_array = "SELECT * FROM suporte_tipo_atendimento ORDER BY IdTipoAtendimento ASC";
	$suporte_tipo_atendimento_array = mysql_query($query_suporte_tipo_atendimento_array, $conexao) or die(mysql_error());
	$row_suporte_tipo_atendimento_array = mysql_fetch_assoc($suporte_tipo_atendimento_array);
	$totalRows_suporte_tipo_atendimento_array = mysql_num_rows($suporte_tipo_atendimento_array);
	$suporte_tipo_atendimento_array_atual = NULL;
	do {
		$suporte_tipo_atendimento_array_atual[] = array(
			'IdTipoAtendimento' => $row_suporte_tipo_atendimento_array['IdTipoAtendimento'], 
			'descricao'			=> $row_suporte_tipo_atendimento_array['descricao'], 
			'contador'			=> 0
		);
	} while ($row_suporte_tipo_atendimento_array = mysql_fetch_assoc($suporte_tipo_atendimento_array));
	mysql_free_result($suporte_tipo_atendimento_array);
	// fim - suporte_tipo_atendimento_array
					
	$totalizar_suporte_praca_tipo_atendimento_envolvido = NULL;
	do {

		// usuarios_array
		mysql_select_db($database_conexao, $conexao);
		$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
		$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
		$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
		$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
		// fim - usuarios_array
		$usuarios_array_atual = NULL;
		
		do {
			
			$totalizar_suporte_praca_tipo_atendimento_envolvido[$row_praca_array['praca']][$row_usuarios_array['IdUsuario']] = $suporte_tipo_atendimento_array_atual;
			
			// nome do usuario
			if(count($usuarios_array_atual) < $totalRows_usuarios_array) { 
				$usuarios_array_atual[$row_usuarios_array['IdUsuario']] = $row_usuarios_array['nome'];
			}
			// fim - nome do usuario
			
		} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
		mysql_free_result($usuarios_array);

	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_suporte_praca_tipo_atendimento_envolvido ***********





// totalizar_prospeccao_situacao
if($row_relatorio['totalizar_prospeccao_situacao'] > 0){ 
	
	$totalizar_prospeccao_situacao = NULL;
	$totalizar_prospeccao_situacao[] = array(
									'IdTipoSituacao' => 1, 
									'situacao'			=> 'analisada', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_situacao[] = array(
									'IdTipoSituacao' => 2, 
									'situacao'			=> 'em negociação', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_situacao[] = array(
									'IdTipoSituacao' => 3, 
									'situacao'			=> 'solicitado agendamento', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_situacao[] = array(
									'IdTipoSituacao' => 4, 
									'situacao'			=> 'venda realizada', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_situacao[] = array(
									'IdTipoSituacao' => 5, 
									'situacao'			=> 'venda perdida', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_situacao[] = array(
									'IdTipoSituacao' => 6, 
									'situacao'			=> 'cancelada', 
									'contador'			=> 0
									);
}
// fim - totalizar_prospeccao_situacao

// totalizar_prospeccao_praca_situacao *****************
if($row_relatorio['totalizar_prospeccao_praca_situacao'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_prospeccao_praca_situacao = NULL;
	do {
		$totalizar_prospeccao_praca_situacao[$row_praca_array['praca']] = array(
											array(
											'IdTipoSituacao' => 1, 
											'situacao'			=> 'analisada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 2, 
											'situacao'			=> 'em negociação', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 3, 
											'situacao'			=> 'solicitado agendamento', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 4, 
											'situacao'			=> 'venda realizada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 5, 
											'situacao'			=> 'venda perdida', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 6, 
											'situacao'			=> 'cancelada', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_prospeccao_praca_situacao ***********

// totalizar_prospeccao_status
if($row_relatorio['totalizar_prospeccao_status'] > 0){ 
	
	$totalizar_prospeccao_status = NULL;
	$totalizar_prospeccao_status[] = array(
									'IdTipoSituacao' => 1, 
									'status'			=> 'aguardando retorno do cliente', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_status[] = array(
									'IdTipoSituacao' => 2, 
									'status'			=> 'encaminhada para usuario responsavel', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_status[] = array(
									'IdTipoSituacao' => 3, 
									'status'			=> 'aguardando atendente', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_status[] = array(
									'IdTipoSituacao' => 4, 
									'status'			=> 'pendente usuario responsavel', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_status[] = array(
									'IdTipoSituacao' => 5, 
									'status'			=> 'aguardando agendamento', 
									'contador'			=> 0
									);
}
// fim - totalizar_prospeccao_status

// totalizar_prospeccao_praca_status *****************
if($row_relatorio['totalizar_prospeccao_praca_status'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_prospeccao_praca_status = NULL;
	do {
		$totalizar_prospeccao_praca_status[$row_praca_array['praca']] = array(
											array(
											'IdTipoSituacao' => 1, 
											'status'			=> 'aguardando retorno do cliente', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 2, 
											'status'			=> 'encaminhada para usuario responsavel', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 3, 
											'status'			=> 'aguardando atendente', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 4, 
											'status'			=> 'pendente usuario responsavel', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 5, 
											'status'			=> 'aguardando agendamento', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_prospeccao_praca_status ***********

// totalizar_prospeccao_baixa_perda_motivo
if($row_relatorio['totalizar_prospeccao_baixa_perda_motivo'] > 0){ 
	$totalizar_prospeccao_baixa_perda_motivo = NULL;
	$totalizar_prospeccao_baixa_perda_motivo[] = array(
									'IdTipoSolicitacao' => 1, 
									'titulo'			=> 'falta de recurso', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_baixa_perda_motivo[] = array(
									'IdTipoSolicitacao' => 2, 
									'titulo'			=> 'concorrência', 
									'contador'			=> 0
									);
}
// fim - totalizar_prospeccao_baixa_perda_motivo

// totalizar_prospeccao_praca_baixa_perda_motivo *****************
if($row_relatorio['totalizar_prospeccao_praca_baixa_perda_motivo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_prospeccao_praca_baixa_perda_motivo = NULL;
	do {
		$totalizar_prospeccao_praca_baixa_perda_motivo[$row_praca_array['praca']] = array( 
											array('
											IdTipoSolicitacao'  => 1, 
											'titulo'			=> 'falta de recurso', 
											'contador'			=> 0
											), 
											array('IdTipoSolicitacao' => 2, 
											'titulo'			=> 'concorrência', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_prospeccao_praca_baixa_perda_motivo);
	
}
// fim - totalizar_prospeccao_praca_baixa_perda_motivo ***********

// totalizar_prospeccao_tipo_cliente
if($row_relatorio['totalizar_prospeccao_tipo_cliente'] > 0){ 
	$totalizar_prospeccao_tipo_cliente = NULL;
	$totalizar_prospeccao_tipo_cliente[] = array(
									'IdTipoCliente' => 1, 
									'titulo'			=> 'a', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_tipo_cliente[] = array(
									'IdTipoCliente' => 2, 
									'titulo'			=> 'n', 
									'contador'			=> 0
									);
}
// fim - totalizar_prospeccao_tipo_cliente

// totalizar_prospeccao_praca_tipo_cliente *****************
if($row_relatorio['totalizar_prospeccao_praca_tipo_cliente'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_prospeccao_praca_tipo_cliente = NULL;
	do {
		$totalizar_prospeccao_praca_tipo_cliente[$row_praca_array['praca']] = array( 
											array('
											IdTipoCliente'  => 1, 
											'titulo'			=> 'a', 
											'contador'			=> 0
											), 
											array('IdTipoCliente' => 2, 
											'titulo'			=> 'n', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_prospeccao_praca_tipo_cliente);
	
}
// fim - totalizar_prospeccao_praca_tipo_cliente ***********

// totalizar_prospeccao_ativo_passivo
if($row_relatorio['totalizar_prospeccao_ativo_passivo'] > 0){ 
	$totalizar_prospeccao_ativo_passivo = NULL;
	$totalizar_prospeccao_ativo_passivo[] = array(
									'IdTipoCliente' => 1, 
									'titulo'			=> 'a', 
									'contador'			=> 0
									);
	$totalizar_prospeccao_ativo_passivo[] = array(
									'IdTipoCliente' => 2, 
									'titulo'			=> 'p', 
									'contador'			=> 0
									);
}
// fim - totalizar_prospeccao_ativo_passivo

// totalizar_prospeccao_praca_ativo_passivo *****************
if($row_relatorio['totalizar_prospeccao_praca_ativo_passivo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_prospeccao_praca_ativo_passivo = NULL;
	do {
		$totalizar_prospeccao_praca_ativo_passivo[$row_praca_array['praca']] = array( 
											array('
											IdTipoCliente'  => 1, 
											'titulo'			=> 'a', 
											'contador'			=> 0
											), 
											array('IdTipoCliente' => 2, 
											'titulo'			=> 'p', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_prospeccao_praca_ativo_passivo);
	
}
// fim - totalizar_prospeccao_praca_ativo_passivo ***********

// totalizar_prospeccao_usuario_responsavel
if($row_relatorio['totalizar_prospeccao_usuario_responsavel'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_prospeccao_usuario_responsavel = NULL;
	do {
		$totalizar_prospeccao_usuario_responsavel[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_prospeccao_usuario_responsavel

// totalizar_prospeccao_praca_usuario_responsavel *****************
if($row_relatorio['totalizar_prospeccao_praca_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_prospeccao_praca_usuario_responsavel = NULL;
	do {
		$totalizar_prospeccao_praca_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_prospeccao_praca_usuario_responsavel ***********




			
// totalizar_venda_situacao
if($row_relatorio['totalizar_venda_situacao'] > 0){ 
	
	$totalizar_venda_situacao = NULL;
	$totalizar_venda_situacao[] = array(
									'IdTipoSituacao' => 1, 
									'situacao'			=> 'analisada', 
									'contador'			=> 0
									);
	$totalizar_venda_situacao[] = array(
									'IdTipoSituacao' => 2, 
									'situacao'			=> 'em execução', 
									'contador'			=> 0
									);
	$totalizar_venda_situacao[] = array(
									'IdTipoSituacao' => 3, 
									'situacao'			=> 'solucionada', 
									'contador'			=> 0
									);
	$totalizar_venda_situacao[] = array(
									'IdTipoSituacao' => 4, 
									'situacao'			=> 'cancelada', 
									'contador'			=> 0
									);
}
// fim - totalizar_venda_situacao

// totalizar_venda_praca_situacao *****************
if($row_relatorio['totalizar_venda_praca_situacao'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_venda_praca_situacao = NULL;
	do {
		$totalizar_venda_praca_situacao[$row_praca_array['praca']] = array(
											array(
											'IdTipoSituacao' => 1, 
											'situacao'			=> 'analisada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 2, 
											'situacao'			=> 'em execução', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 3, 
											'situacao'			=> 'solucionada', 
											'contador'			=> 0
											),
											array(
											'IdTipoSituacao' => 4, 
											'situacao'			=> 'cancelada', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_situacao ***********

// totalizar_venda_usuario_responsavel
if($row_relatorio['totalizar_venda_usuario_responsavel'] > 0){ 
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	// fim - usuarios_array
	$totalizar_venda_usuario_responsavel = NULL;
	do {
		$totalizar_venda_usuario_responsavel[] = array(
										'IdUsuario' => $row_usuarios_array['IdUsuario'], 
										'nome'			=> $row_usuarios_array['nome'], 
										'contador'			=> 0
										);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
}
// fim - totalizar_venda_usuario_responsavel

// totalizar_venda_praca_usuario_responsavel *****************
if($row_relatorio['totalizar_venda_praca_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_venda_praca_usuario_responsavel = NULL;
	do {
		$totalizar_venda_praca_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_usuario_responsavel ***********
												
// totalizar_venda_valor_venda
if($row_relatorio['totalizar_venda_valor_venda'] > 0){ 
	$totalizar_venda_valor_venda = 0;
}
// fim - totalizar_venda_valor_venda

// totalizar_venda_praca_valor_venda *****************
if($row_relatorio['totalizar_venda_praca_valor_venda'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_venda_praca_valor_venda = NULL;
	do {
		$totalizar_venda_praca_valor_venda[$row_praca_array['praca']] = array('valor_venda' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_valor_venda ***********

// totalizar_venda_valor_treinamento
if($row_relatorio['totalizar_venda_valor_treinamento'] > 0){ 
	$totalizar_venda_valor_treinamento = 0;
}
// fim - totalizar_venda_valor_treinamento

// totalizar_venda_praca_valor_treinamento *****************
if($row_relatorio['totalizar_venda_praca_valor_treinamento'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_venda_praca_valor_treinamento = NULL;
	do {
		$totalizar_venda_praca_valor_treinamento[$row_praca_array['praca']] = array('valor_treinamento' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_valor_treinamento ***********
			
// totalizar_venda_modulos
if($row_relatorio['totalizar_venda_modulos'] > 0){ 
	// venda_modulos_venda_totalizar
	mysql_select_db($database_conexao, $conexao);
	$query_venda_modulos_venda_totalizar = "SELECT * FROM geral_tipo_modulo ORDER BY ordem ASC, IdTipoModulo ASC";
	$venda_modulos_venda_totalizar = mysql_query($query_venda_modulos_venda_totalizar, $conexao) or die(mysql_error());
	$row_venda_modulos_venda_totalizar = mysql_fetch_assoc($venda_modulos_venda_totalizar);
	$totalRows_venda_modulos_venda_totalizar = mysql_num_rows($venda_modulos_venda_totalizar);
	// fim - venda_modulos_venda_totalizar
	$totalizar_venda_modulos = NULL;
	do {
		$totalizar_venda_modulos[] = array(
										'IdTipoModulo' => $row_venda_modulos_venda_totalizar['IdTipoModulo'], 
										'descricao'			=> $row_venda_modulos_venda_totalizar['descricao'], 
										'contador'			=> 0
										);
	} while ($row_venda_modulos_venda_totalizar = mysql_fetch_assoc($venda_modulos_venda_totalizar));
	mysql_free_result($venda_modulos_venda_totalizar);
}
// fim - totalizar_venda_modulos

// totalizar_venda_praca_modulos *****************
if($row_relatorio['totalizar_venda_praca_modulos'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// venda_modulos_array
	mysql_select_db($database_conexao, $conexao);
	$query_venda_modulos_array = "SELECT * FROM geral_tipo_modulo ORDER BY IdTipoModulo ASC";
	$venda_modulos_array = mysql_query($query_venda_modulos_array, $conexao) or die(mysql_error());
	$row_venda_modulos_array = mysql_fetch_assoc($venda_modulos_array);
	$totalRows_venda_modulos_array = mysql_num_rows($venda_modulos_array);
	$venda_modulos_array_atual = NULL;
	do {
		$venda_modulos_array_atual[] = array(
			'IdTipoModulo' => $row_venda_modulos_array['IdTipoModulo'], 
			'descricao'			=> $row_venda_modulos_array['descricao'], 
			'contador'			=> 0
		);
	} while ($row_venda_modulos_array = mysql_fetch_assoc($venda_modulos_array));
	mysql_free_result($venda_modulos_array);
	// fim - venda_modulos_array
	
	$totalizar_venda_praca_modulos = NULL;
	do {
		$totalizar_venda_praca_modulos[$row_praca_array['praca']] = $venda_modulos_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_modulos ***********

// totalizar_venda_tempo
if($row_relatorio['totalizar_venda_tempo'] > 0){ 
	$totalizar_venda_tempo = 0;
}
// fim - totalizar_venda_tempo

// totalizar_venda_praca_tempo *****************
if($row_relatorio['totalizar_venda_praca_tempo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_venda_praca_tempo = NULL;
	do {
		$totalizar_venda_praca_tempo[$row_praca_array['praca']] = array('tempo' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_tempo ***********

// totalizar_venda_tempo_gasto
if($row_relatorio['totalizar_venda_tempo_gasto'] > 0){ 
	$totalizar_venda_tempo_gasto = 0;
}
// fim - totalizar_venda_tempo_gasto

// totalizar_venda_praca_tempo_gasto *****************
if($row_relatorio['totalizar_venda_praca_tempo_gasto'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_venda_praca_tempo_gasto = NULL;
	do {
		$totalizar_venda_praca_tempo_gasto[$row_praca_array['praca']] = array('tempo_gasto' => 0);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_tempo_gasto ***********

// totalizar_venda_tempo_gasto_usuario_responsavel
if($row_relatorio['totalizar_venda_tempo_gasto_usuario_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_venda_tempo_gasto_usuario_responsavel = NULL;
	do {
		$totalizar_venda_tempo_gasto_usuario_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_venda_tempo_gasto_usuario_responsavel
			
// totalizar_venda_praca_tempo_gasto_usuario_responsavel *****************
if($row_relatorio['totalizar_venda_praca_tempo_gasto_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_venda_praca_tempo_gasto_usuario_responsavel = NULL;
	do {
		$totalizar_venda_praca_tempo_gasto_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_tempo_gasto_usuario_responsavel ***********

// totalizar_venda_valor_venda_usuario_responsavel
if($row_relatorio['totalizar_venda_valor_venda_usuario_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_venda_valor_venda_usuario_responsavel = NULL;
	do {
		$totalizar_venda_valor_venda_usuario_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_venda_valor_venda_usuario_responsavel
			
// totalizar_venda_praca_valor_venda_usuario_responsavel *****************
if($row_relatorio['totalizar_venda_praca_valor_venda_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_venda_praca_valor_venda_usuario_responsavel = NULL;
	do {
		$totalizar_venda_praca_valor_venda_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_valor_venda_usuario_responsavel ***********

// totalizar_venda_valor_treinamento_responsavel
if($row_relatorio['totalizar_venda_valor_treinamento_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_venda_valor_treinamento_responsavel = NULL;
	do {
		$totalizar_venda_valor_treinamento_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_venda_valor_treinamento_responsavel
			
// totalizar_venda_praca_valor_treinamento_responsavel *****************
if($row_relatorio['totalizar_venda_praca_valor_treinamento_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_venda_praca_valor_treinamento_responsavel = NULL;
	do {
		$totalizar_venda_praca_valor_treinamento_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_valor_treinamento_responsavel ***********

// totalizar_venda_tempo_usuario_responsavel
if($row_relatorio['totalizar_venda_tempo_usuario_responsavel'] > 0){ 
	// solicitacao_usuarios
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_usuarios = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$solicitacao_usuarios = mysql_query($query_solicitacao_usuarios, $conexao) or die(mysql_error());
	$row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios);
	$totalRows_solicitacao_usuarios = mysql_num_rows($solicitacao_usuarios);
	// fim - solicitacao_usuarios
	$totalizar_venda_tempo_usuario_responsavel = NULL;
	do {
		$totalizar_venda_tempo_usuario_responsavel[] = array(
										'IdUsuario' => $row_solicitacao_usuarios['IdUsuario'], 
										'nome'			=> $row_solicitacao_usuarios['nome'], 
										'contador'			=> 0
										);
	} while ($row_solicitacao_usuarios = mysql_fetch_assoc($solicitacao_usuarios));
	mysql_free_result($solicitacao_usuarios);
}
// fim - totalizar_venda_tempo_usuario_responsavel
			
// totalizar_venda_praca_tempo_usuario_responsavel *****************
if($row_relatorio['totalizar_venda_praca_tempo_usuario_responsavel'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	// usuarios_array
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_array = "SELECT * FROM usuarios ORDER BY IdUsuario ASC";
	$usuarios_array = mysql_query($query_usuarios_array, $conexao) or die(mysql_error());
	$row_usuarios_array = mysql_fetch_assoc($usuarios_array);
	$totalRows_usuarios_array = mysql_num_rows($usuarios_array);
	$usuarios_array_atual = NULL;
	do {
		$usuarios_array_atual[] = array(
			'IdUsuario' => $row_usuarios_array['IdUsuario'], 
			'nome'			=> $row_usuarios_array['nome'], 
			'contador'			=> 0
		);
	} while ($row_usuarios_array = mysql_fetch_assoc($usuarios_array));
	mysql_free_result($usuarios_array);
	// fim - usuarios_array
	
	$totalizar_venda_praca_tempo_usuario_responsavel = NULL;
	do {
		$totalizar_venda_praca_tempo_usuario_responsavel[$row_praca_array['praca']] = $usuarios_array_atual;
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	
}
// fim - totalizar_venda_praca_tempo_usuario_responsavel ***********

// totalizar_venda_tipo_cliente
if($row_relatorio['totalizar_venda_tipo_cliente'] > 0){ 
	$totalizar_venda_tipo_cliente = NULL;
	$totalizar_venda_tipo_cliente[] = array(
									'IdTipoCliente' => 1, 
									'titulo'			=> 'a', 
									'contador'			=> 0
									);
	$totalizar_venda_tipo_cliente[] = array(
									'IdTipoCliente' => 2, 
									'titulo'			=> 'n', 
									'contador'			=> 0
									);
}
// fim - totalizar_venda_tipo_cliente

// totalizar_venda_praca_tipo_cliente *****************
if($row_relatorio['totalizar_venda_praca_tipo_cliente'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_venda_praca_tipo_cliente = NULL;
	do {
		$totalizar_venda_praca_tipo_cliente[$row_praca_array['praca']] = array( 
											array('
											IdTipoCliente'  => 1, 
											'titulo'			=> 'a', 
											'contador'			=> 0
											), 
											array('IdTipoCliente' => 2, 
											'titulo'			=> 'n', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_venda_praca_tipo_cliente);
	
}
// fim - totalizar_venda_praca_tipo_cliente ***********

// totalizar_venda_dilacao_prazo
if($row_relatorio['totalizar_venda_dilacao_prazo'] > 0){ 
	
	$totalizar_venda_dilacao_prazo = NULL;
	$totalizar_venda_dilacao_prazo[] = array(
									'titulo'			=> 'Não', 
									'contador'			=> 0
									);
	$totalizar_venda_dilacao_prazo[] = array(
									'titulo'			=> 'Sim', 
									'contador'			=> 0
									);
}
// fim - totalizar_venda_dilacao_prazo

// totalizar_venda_praca_dilacao_prazo *****************
if($row_relatorio['totalizar_venda_praca_dilacao_prazo'] > 0){ 

	// praca_array
	mysql_select_db($database_conexao, $conexao);
	$query_praca_array = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
	$praca_array = mysql_query($query_praca_array, $conexao) or die(mysql_error());
	$row_praca_array = mysql_fetch_assoc($praca_array);
	$totalRows_praca_array = mysql_num_rows($praca_array);
	// fim - praca_array
	
	$totalizar_venda_praca_dilacao_prazo = NULL;
	do {
		
		
		$totalizar_venda_praca_dilacao_prazo[$row_praca_array['praca']] = array( 
											array(
											'titulo'			=> 'Não', 
											'contador'			=> 0
											), 
											array(
											'titulo'			=> 'Sim', 
											'contador'			=> 0
											)
										);
	} while ($row_praca_array = mysql_fetch_assoc($praca_array));
	mysql_free_result($praca_array);
	//print_r($totalizar_venda_praca_dilacao_prazo);
	
}
// fim - totalizar_venda_praca_dilacao_prazo ***********
?>