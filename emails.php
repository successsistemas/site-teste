<?
// email_solicitacao() -----------------------------------------------------------------------------
// envia e-mail para todos do site utilizando o cadastro 'e-mails_aviso'
function email_solicitacao($id_solicitacao, $assunto, $conteudo){
	
	// conexão
	require('Connections/conexao.php');
	
	// solicitacao_email
	$colname_solicitacao_email = "-1";
	if (isset($id_solicitacao)) {
	  $colname_solicitacao_email = $id_solicitacao;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_email = sprintf("
									   SELECT situacao, id, titulo, empresa, contrato, praca, tipo, dt_solicitacao, id_usuario_responsavel, prioridade, 
									   (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel 
									   FROM solicitacao 
									   WHERE id = %s", 
									   GetSQLValueString($colname_solicitacao_email, "int"));
	$solicitacao_email = mysql_query($query_solicitacao_email, $conexao) or die(mysql_error());
	$row_solicitacao_email = mysql_fetch_assoc($solicitacao_email);
	$totalRows_solicitacao_email = mysql_num_rows($solicitacao_email);
	// fim - solicitacao_email
	
	// emails_aviso
	mysql_select_db($database_conexao, $conexao);
	$query_emails_aviso = "SELECT * FROM emails_aviso ORDER BY id_email_aviso ASC";
	$emails_aviso = mysql_query($query_emails_aviso, $conexao) or die(mysql_error());
	$row_emails_aviso = mysql_fetch_assoc($emails_aviso);
	$totalRows_emails_aviso = mysql_num_rows($emails_aviso);
	// fim - emails_aviso
	
	// $email_destinatario
	$email_destinatario ="";
	
	do {
		$email_destinatario .= $row_emails_aviso['email'].";";
	} while ($row_emails_aviso = mysql_fetch_assoc($emails_aviso));
	// fim - $email_destinatario
	
	// assunto - conteúdo
	if ($row_solicitacao_email['situacao']=="criada"){ // se é nova solicitação
	
		$email_assunto = "[ ".$row_solicitacao_email['praca']." ] "."[ ".$row_solicitacao_email['tipo']." ] ".$assunto;
		$email_conteudo = $conteudo;
			
	} else { // se não é nova solicitação
	
		$email_assunto = "[ ".$row_solicitacao_email['praca']." ] ".$assunto." - ".$row_solicitacao_email['id']." - ".$row_solicitacao_email['titulo']."";
		$email_conteudo = "
			Link: <a href='http://success.inf.br/solicitacao_editar.php?id_solicitacao=".$row_solicitacao_email['id']."&padrao=sim'>Clique aqui para acessar a solicitação</a>
			
			Título: ".$row_solicitacao_email['titulo']."
			N. Solicitação: ".$row_solicitacao_email['id']."
			
			Empresa: ".$row_solicitacao_email['empresa']."
			Contrato: ".$row_solicitacao_email['contrato']."
			
			Criação da solicitação: ".date('d-m-Y  H:i', strtotime($row_solicitacao_email['dt_solicitacao']))."
			Solicitante: ".$row_solicitacao_email['usuario_responsavel']."
			Tipo: ".$row_solicitacao_email['tipo']."
			Prioridade: ".$row_solicitacao_email['prioridade']."
			
			Data: ".date("d-m-Y H:i:s")." 
			Responsável: ".$row_solicitacao_email['usuario_responsavel']." 
					
			Descrição: ".$conteudo."
			";	
			
	}
	// fim - assunto - conteúdo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));

	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($solicitacao_email);
	mysql_free_result($emails_aviso);
	
}
// fim - email_solicitacao() -----------------------------------------------------------------------


// email_suporte() --------------------------------------------------------------------------------------------------
// envia e-mail somente para usuários ativos e (da praça referente ou controlador de suporte)
function email_suporte($id_suporte, $assunto, $conteudo){
	
	// conexão
	require('Connections/conexao.php');
	
	// suporte_email
	$colname_suporte_email = "-1";
	if (isset($id_suporte)) {
	  $colname_suporte_email = $id_suporte;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_email = sprintf("
								   SELECT id, titulo, empresa, contrato, praca, 
								   (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel
								   FROM suporte 
								   WHERE id = %s", GetSQLValueString($colname_suporte_email, "int"));
	$suporte_email = mysql_query($query_suporte_email, $conexao) or die(mysql_error());
	$row_suporte_email = mysql_fetch_assoc($suporte_email);
	$totalRows_suporte_email = mysql_num_rows($suporte_email);
	// fim - suporte_email

	// usuarios_email
	$colname_usuarios_email = "-1";
	if (isset($row_suporte_email['praca'])) {
	  $colname_usuarios_email = $row_suporte_email['praca'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("SELECT email 
								  FROM usuarios 
								  WHERE 
								  status = 1 and
								  (
								  praca = %s or
								  controle_suporte = 'Y'
								  )
								  ORDER BY IdUsuario ASC", GetSQLValueString($colname_usuarios_email, "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	// fim - $email_destinatario

	// assunto - conteúdo
	$email_assunto = "[ ".$row_suporte_email['praca']." ] ".$assunto." - ".$row_suporte_email['id']." - ".$row_suporte_email['titulo']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/suporte_editar.php?id_suporte=".$row_suporte_email['id']."&padrao=sim'>Clique aqui para acessar o suporte</a>
	
	Título: ".$row_suporte_email['titulo']."
	N. Suporte: ".$row_suporte_email['id']."
	
	Empresa: ".$row_suporte_email['empresa']."
	Contrato: ".$row_suporte_email['contrato']."
	
	Data: ".date("d-m-Y H:i:s")." 
	Responsável: ".$row_suporte_email['usuario_responsavel']." 
			
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteúdo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	//mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($suporte_email);
	mysql_free_result($usuarios_email);

}
// fim - email_suporte() --------------------------------------------------------------------------------------------


// email_solicitacao_sem_movimento() --------------------------------------------------------------------------------
// envia e-mail somente para usuário responsável pela ação
function email_solicitacao_sem_movimento($id_solicitacao, $assunto, $conteudo){
	
	// conexão
	require('Connections/conexao.php');
	
	// solicitacao_email
	$colname_solicitacao_email = "-1";
	if (isset($id_solicitacao)) {
	  $colname_solicitacao_email = $id_solicitacao;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_solicitacao_email = sprintf("
									   SELECT situacao, id, titulo, empresa, contrato, praca, tipo, dt_solicitacao, id_usuario_responsavel, prioridade, 
									   (SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel 
									   FROM solicitacao 
									   WHERE id = %s", 
									   GetSQLValueString($colname_solicitacao_email, "int"));
	$solicitacao_email = mysql_query($query_solicitacao_email, $conexao) or die(mysql_error());
	$row_solicitacao_email = mysql_fetch_assoc($solicitacao_email);
	$totalRows_solicitacao_email = mysql_num_rows($solicitacao_email);
	// fim - solicitacao_email
	
	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE IdUsuario = '".$row_solicitacao_email['id_usuario_responsavel']."'");
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	// fim - $email_destinatario
	
	// assunto - conteudo
	$email_assunto = "[ ".$row_solicitacao_email['praca']." ] ".$assunto." - ".$row_solicitacao_email['id']." - ".$row_solicitacao_email['titulo']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/solicitacao_editar.php?id_solicitacao=".$row_solicitacao_email['id']."&padrao=sim'>Clique aqui para acessar a solicitação</a>
	
	Título: ".$row_solicitacao_email['titulo']."
	N. Solicitação: ".$row_solicitacao_email['id']."
	
	Empresa: ".$row_solicitacao_email['empresa']."
	Contrato: ".$row_solicitacao_email['contrato']."
	
	Criação da solicitação: ".date('d-m-Y  H:i', strtotime($row_solicitacao_email['dt_solicitacao']))."
	Solicitante: ".$row_solicitacao_email['usuario_responsavel']."
	Tipo: ".$row_solicitacao_email['tipo']."
	Prioridade: ".$row_solicitacao_email['prioridade']."
	
	Data: ".date("d-m-Y H:i:s")." 
	Responsável: ".$row_solicitacao_email['usuario_responsavel']." 
			
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($solicitacao_email);
	mysql_free_result($usuarios_email);
	
}
// fim - email_solicitacao_sem_movimento() --------------------------------------------------------------------------



// email_prospeccao_agenda_atraso() --------------------------------------------------------------------------------
// envia e-mail para usuário responsável pela ação e para o controlador de praça
function email_prospeccao_agenda_atraso($id_prospeccao, $id_agenda, $assunto, $conteudo){

	// conexão
	require('Connections/conexao.php');
	
	// prospeccao_email
	$colname_prospeccao_email = "-1";
	if (isset($id_prospeccao)) {
	  $colname_prospeccao_email = $id_prospeccao;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_prospeccao_email = sprintf("
	SELECT 
	prospeccao.id, prospeccao.status, prospeccao.situacao, prospeccao.data_prospeccao, prospeccao.id_usuario_responsavel, prospeccao.praca, 
	prospeccao.ativo_passivo, prospeccao.uf, prospeccao.cidade, prospeccao.nome_razao_social, prospeccao.quantidade_agendado, prospeccao.status_flag, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel, 
		
	agenda.id_agenda AS prospeccao_agenda_id_agenda, 
	agenda.data AS prospeccao_agenda_data, 
	agenda.data_inicio AS prospeccao_agenda_data_inicio, 
	agenda.descricao AS prospeccao_agenda_descricao, 
	agenda.status AS prospeccao_agenda_status
	
	FROM agenda
	LEFT JOIN prospeccao ON agenda.id_prospeccao = prospeccao.id
	WHERE prospeccao.id = %s and agenda.id_agenda = %s and prospeccao.status_flag = 'a' and agenda.status = 'a'
	ORDER BY agenda.data ASC", 
	GetSQLValueString($colname_prospeccao_email, "int"),
	GetSQLValueString($id_agenda, "int"));
	$prospeccao_email = mysql_query($query_prospeccao_email, $conexao) or die(mysql_error());
	$row_prospeccao_email = mysql_fetch_assoc($prospeccao_email);
	$totalRows_prospeccao_email = mysql_num_rows($prospeccao_email);
	// fim - prospeccao_email

	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE 
								 (IdUsuario = '".$row_prospeccao_email['id_usuario_responsavel']."' or (praca = %s and (nivel_prospeccao=1 or nivel_prospeccao=2))) and status = 1", 
									   GetSQLValueString($row_prospeccao_email['praca'], "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_prospeccao_email['praca']." ] ".$assunto." - ".$row_prospeccao_email['id']." - ".$row_prospeccao_email['nome_razao_social']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/prospeccao_editar.php?id_prospeccao=".$row_prospeccao_email['id']."&padrao=sim'>Clique aqui para acessar a Prospecção</a>
	
	Empresa: ".$row_prospeccao_email['nome_razao_social']."
	N. Prospecção: ".$row_prospeccao_email['id']."
	Criação da Prospecção: ".date('d-m-Y  H:i', strtotime($row_prospeccao_email['data_prospeccao']))."
	Situação: ".$row_prospeccao_email['situacao']."
	Responsável: ".$row_prospeccao_email['usuario_responsavel']." 
	
	Agendamento N. ".$row_prospeccao_email['prospeccao_agenda_id_agenda']." 
	Início: ".date('d-m-Y  H:i', strtotime($row_prospeccao_email['prospeccao_agenda_data_inicio']))." 
	Fim: ".date('d-m-Y  H:i', strtotime($row_prospeccao_email['prospeccao_agenda_data']))." 
	Descrição do agendamento: ".$row_prospeccao_email['prospeccao_agenda_descricao']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($prospeccao_email);
	mysql_free_result($usuarios_email);

}
// fim - email_prospeccao_agenda_atraso() --------------------------------------------------------------------------



// email_prospeccao_sem_agendamento() --------------------------------------------------------------------------------
// envia e-mail para usuário responsável pela ação e para o controlador de praça
function email_prospeccao_sem_agendamento($id_prospeccao, $assunto, $conteudo){

	// conexão
	require('Connections/conexao.php');
	
	// prospeccao_email
	$colname_prospeccao_email = "-1";
	if (isset($id_prospeccao)) {
	  $colname_prospeccao_email = $id_prospeccao;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_prospeccao_email = sprintf("
	SELECT 
	prospeccao.id, prospeccao.status, prospeccao.situacao, prospeccao.data_prospeccao, prospeccao.id_usuario_responsavel, prospeccao.praca, 
	prospeccao.ativo_passivo, prospeccao.uf, prospeccao.cidade, prospeccao.nome_razao_social, prospeccao.quantidade_agendado, prospeccao.status_flag, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel 
	
	FROM prospeccao 
	WHERE id = %s", 
	GetSQLValueString($colname_prospeccao_email, "int"));
	$prospeccao_email = mysql_query($query_prospeccao_email, $conexao) or die(mysql_error());
	$row_prospeccao_email = mysql_fetch_assoc($prospeccao_email);
	$totalRows_prospeccao_email = mysql_num_rows($prospeccao_email);
	// fim - prospeccao_email

	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE 
								 (IdUsuario = '".$row_prospeccao_email['id_usuario_responsavel']."' or (praca = %s and (nivel_prospeccao=1 or nivel_prospeccao=2))) and status = 1",  
									   GetSQLValueString($row_prospeccao_email['praca'], "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	//echo $email_destinatario;
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_prospeccao_email['praca']." ] ".$assunto." - ".$row_prospeccao_email['id']." - ".$row_prospeccao_email['nome_razao_social']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/prospeccao_editar.php?id_prospeccao=".$row_prospeccao_email['id']."&padrao=sim'>Clique aqui para acessar a Prospecção</a>
	
	Empresa: ".$row_prospeccao_email['nome_razao_social']."
	N. Prospecção: ".$row_prospeccao_email['id']."
	Criação da Prospecção: ".date('d-m-Y  H:i', strtotime($row_prospeccao_email['data_prospeccao']))."
	Situação: ".$row_prospeccao_email['situacao']."
	Responsável: ".$row_prospeccao_email['usuario_responsavel']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	//mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($prospeccao_email);
	mysql_free_result($usuarios_email);

}
// fim - email_prospeccao_sem_agendamento() --------------------------------------------------------------------------



// email_prospeccao_sem_movimento() --------------------------------------------------------------------------------
// envia e-mail para usuário responsável pela ação e para o controlador de praça
function email_prospeccao_sem_movimento($id_prospeccao, $assunto, $conteudo){

	// conexão
	require('Connections/conexao.php');
	
	// prospeccao_email
	$colname_prospeccao_email = "-1";
	if (isset($id_prospeccao)) {
	  $colname_prospeccao_email = $id_prospeccao;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_prospeccao_email = sprintf("
	SELECT 
	prospeccao.id, prospeccao.status, prospeccao.situacao, prospeccao.data_prospeccao, prospeccao.id_usuario_responsavel, prospeccao.praca, 
	prospeccao.ativo_passivo, prospeccao.uf, prospeccao.cidade, prospeccao.nome_razao_social, prospeccao.quantidade_agendado, prospeccao.status_flag, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel 

	FROM prospeccao 
	WHERE id = %s", 
	GetSQLValueString($colname_prospeccao_email, "int"));
	$prospeccao_email = mysql_query($query_prospeccao_email, $conexao) or die(mysql_error());
	$row_prospeccao_email = mysql_fetch_assoc($prospeccao_email);
	$totalRows_prospeccao_email = mysql_num_rows($prospeccao_email);
	// fim - prospeccao_email

	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE 
								 (IdUsuario = '".$row_prospeccao_email['id_usuario_responsavel']."' or (praca = %s and (nivel_prospeccao=1 or nivel_prospeccao=2))) and status = 1", 
									   GetSQLValueString($row_prospeccao_email['praca'], "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	//echo $email_destinatario;
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_prospeccao_email['praca']." ] ".$assunto." - ".$row_prospeccao_email['id']." - ".$row_prospeccao_email['nome_razao_social']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/prospeccao_editar.php?id_prospeccao=".$row_prospeccao_email['id']."&padrao=sim'>Clique aqui para acessar a Prospecção</a>
	
	Empresa: ".$row_prospeccao_email['nome_razao_social']."
	N. Prospecção: ".$row_prospeccao_email['id']."
	Criação da Prospecção: ".date('d-m-Y  H:i', strtotime($row_prospeccao_email['data_prospeccao']))."
	Situação: ".$row_prospeccao_email['situacao']."
	Responsável: ".$row_prospeccao_email['usuario_responsavel']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($prospeccao_email);
	mysql_free_result($usuarios_email);

}
// fim - email_prospeccao_sem_movimento() --------------------------------------------------------------------------



// email_venda() --------------------------------------------------------------------------------------------------
// envia e-mail somente para usuários ativos e (da praça referente ou controlador de venda)
function email_venda($id_venda, $assunto, $conteudo){
	
	// conexão
	require('Connections/conexao.php');
	
	// venda_email
	$colname_venda_email = "-1";
	if (isset($id_venda)) {
	  $colname_venda_email = $id_venda;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_venda_email = sprintf("
	SELECT 
	venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
	venda.empresa, venda.quantidade_agendado_implantacao, venda.quantidade_agendado_implantacao, venda.status_flag, 
	venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = venda.id_usuario_responsavel) as usuario_responsavel

	FROM venda 
	WHERE id = %s", 
	GetSQLValueString($colname_venda_email, "int"));
	$venda_email = mysql_query($query_venda_email, $conexao) or die(mysql_error());
	$row_venda_email = mysql_fetch_assoc($venda_email);
	$totalRows_venda_email = mysql_num_rows($venda_email);
	// fim - venda_email

	// usuarios_email
	$colname_usuarios_email = "-1";
	if (isset($row_venda_email['praca'])) {
	  $colname_usuarios_email = $row_venda_email['praca'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("SELECT email 
								  FROM usuarios 
								  WHERE 
								  status = 1 and
								  (
								  praca = %s or
								  controle_venda = 'Y'
								  )
								  ORDER BY IdUsuario ASC", GetSQLValueString($colname_usuarios_email, "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	// echo $email_destinatario;
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_venda_email['praca']." ] ".$assunto." - ".$row_venda_email['id']." - ".$row_venda_email['empresa']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/venda_editar.php?id_venda=".$row_venda_email['id']."&padrao=sim'>Clique aqui para acessar a Venda</a>
	
	Empresa: ".$row_venda_email['empresa']."
	N. Venda: ".$row_venda_email['id']."
	Criação da Venda: ".date('d-m-Y  H:i', strtotime($row_venda_email['data_venda']))."
	Situação: ".$row_venda_email['situacao']."
	Responsável: ".$row_venda_email['usuario_responsavel']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($venda_email);
	mysql_free_result($usuarios_email);

}
// fim - email_venda() --------------------------------------------------------------------------------------------



// email_venda_agenda_treinamento_atraso() --------------------------------------------------------------------------------
// envia e-mail para usuário responsável pela ação e para o controlador de praça
function email_venda_agenda_treinamento_atraso($id_venda, $id_agenda, $assunto, $conteudo){

	// conexão
	require('Connections/conexao.php');
	
	// venda_email
	$colname_venda_email = "-1";
	if (isset($id_venda)) {
	  $colname_venda_email = $id_venda;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_venda_email = sprintf("
	SELECT 
	venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
	venda.empresa, venda.quantidade_agendado_treinamento, venda.quantidade_agendado_implantacao, venda.status_flag, 
	venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = venda.id_usuario_responsavel) as usuario_responsavel, 
	
	agenda.id_agenda AS venda_agenda_treinamento_id_agenda, 
	agenda.data AS venda_agenda_treinamento_data, 
	agenda.data_inicio AS venda_agenda_treinamento_data_inicio, 
	agenda.descricao AS venda_agenda_treinamento_descricao, 
	agenda.status AS venda_agenda_treinamento_status
	
	FROM agenda 
	LEFT JOIN venda ON agenda.id_venda_treinamento = venda.id
	WHERE venda.id = %s and agenda.id_agenda = %s and venda.status_flag = 'a' and agenda.status = 'a'
	ORDER BY agenda.data ASC", 
	GetSQLValueString($colname_venda_email, "int"),
	GetSQLValueString($id_agenda, "int"));
	$venda_email = mysql_query($query_venda_email, $conexao) or die(mysql_error());
	$row_venda_email = mysql_fetch_assoc($venda_email);
	$totalRows_venda_email = mysql_num_rows($venda_email);
	// fim - venda_email

	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE 
								 (IdUsuario = '".$row_venda_email['id_usuario_responsavel']."' or (praca = %s and (nivel_venda=1 or nivel_venda=2))) and status = 1", 
									   GetSQLValueString($row_venda_email['praca'], "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	//echo $email_destinatario;
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_venda_email['praca']." ] ".$assunto." - ".$row_venda_email['id']." - ".$row_venda_email['empresa']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/venda_editar.php?id_venda=".$row_venda_email['id']."&padrao=sim'>Clique aqui para acessar a Venda</a>
	
	Empresa: ".$row_venda_email['empresa']."
	N. Venda: ".$row_venda_email['id']."
	Criação da Venda: ".date('d-m-Y  H:i', strtotime($row_venda_email['data_venda']))."
	Situação: ".$row_venda_email['situacao']."
	Responsável: ".$row_venda_email['usuario_responsavel']." 
	
	Agendamento N. ".$row_venda_email['venda_agenda_treinamento_id_agenda']." 
	Início: ".date('d-m-Y  H:i', strtotime($row_venda_email['venda_agenda_treinamento_data_inicio']))." 
	Fim: ".date('d-m-Y  H:i', strtotime($row_venda_email['venda_agenda_treinamento_data']))." 
	Descrição do agendamento: ".$row_venda_email['venda_agenda_treinamento_descricao']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($venda_email);
	mysql_free_result($usuarios_email);

}
// fim - email_venda_agenda_treinamento_atraso() --------------------------------------------------------------------------



// email_venda_agenda_implantacao_atraso() --------------------------------------------------------------------------------
// envia e-mail para usuário responsável pela ação e para o controlador de praça
function email_venda_agenda_implantacao_atraso($id_venda, $id_agenda, $assunto, $conteudo){

	// conexão
	require('Connections/conexao.php');
	
	// venda_email
	$colname_venda_email = "-1";
	if (isset($id_venda)) {
	  $colname_venda_email = $id_venda;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_venda_email = sprintf("
	SELECT 
	venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
	venda.empresa, venda.quantidade_agendado_implantacao, venda.quantidade_agendado_implantacao, venda.status_flag, 
	venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = venda.id_usuario_responsavel) as usuario_responsavel, 
	
	agenda.id_agenda AS venda_agenda_implantacao_id_agenda, 
	agenda.data AS venda_agenda_implantacao_data, 
	agenda.data_inicio AS venda_agenda_implantacao_data_inicio, 
	agenda.descricao AS venda_agenda_implantacao_descricao, 
	agenda.status AS venda_agenda_implantacao_status
	
	FROM agenda 
	LEFT JOIN venda ON agenda.id_venda_implantacao = venda.id
	WHERE venda.id = %s and agenda.id_agenda = %s and venda.status_flag = 'a' and agenda.status = 'a'
	ORDER BY agenda.data ASC", 
	GetSQLValueString($colname_venda_email, "int"),
	GetSQLValueString($id_agenda, "int"));
	$venda_email = mysql_query($query_venda_email, $conexao) or die(mysql_error());
	$row_venda_email = mysql_fetch_assoc($venda_email);
	$totalRows_venda_email = mysql_num_rows($venda_email);
	// fim - venda_email

	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE 
								 (IdUsuario = '".$row_venda_email['id_usuario_responsavel']."' or (praca = %s and (nivel_venda=1 or nivel_venda=2))) and status = 1", 
									   GetSQLValueString($row_venda_email['praca'], "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	//echo $email_destinatario;
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_venda_email['praca']." ] ".$assunto." - ".$row_venda_email['id']." - ".$row_venda_email['empresa']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/venda_editar.php?id_venda=".$row_venda_email['id']."&padrao=sim'>Clique aqui para acessar a Venda</a>
	
	Empresa: ".$row_venda_email['empresa']."
	N. Venda: ".$row_venda_email['id']."
	Criação da Venda: ".date('d-m-Y  H:i', strtotime($row_venda_email['data_venda']))."
	Situação: ".$row_venda_email['situacao']."
	Responsável: ".$row_venda_email['usuario_responsavel']." 
	
	Agendamento N. ".$row_venda_email['venda_agenda_implantacao_id_agenda']." 
	Início: ".date('d-m-Y  H:i', strtotime($row_venda_email['venda_agenda_implantacao_data_inicio']))." 
	Fim: ".date('d-m-Y  H:i', strtotime($row_venda_email['venda_agenda_implantacao_data']))." 
	Descrição do agendamento: ".$row_venda_email['venda_agenda_implantacao_descricao']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($venda_email);
	mysql_free_result($usuarios_email);

}
// fim - email_venda_agenda_implantacao_atraso() --------------------------------------------------------------------------



// email_venda_sem_movimento() --------------------------------------------------------------------------------
// envia e-mail para usuário responsável pela ação e para o controlador de praça
function email_venda_sem_movimento($id_venda, $assunto, $conteudo){

	// conexão
	require('Connections/conexao.php');
	
	// venda_email
	$colname_venda_email = "-1";
	if (isset($id_venda)) {
	  $colname_venda_email = $id_venda;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_venda_email = sprintf("
	SELECT 
	venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
	venda.empresa, venda.quantidade_agendado_implantacao, venda.quantidade_agendado_implantacao, venda.status_flag, 
	venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = venda.id_usuario_responsavel) as usuario_responsavel

	FROM venda 
	WHERE id = %s", 
	GetSQLValueString($colname_venda_email, "int"));
	$venda_email = mysql_query($query_venda_email, $conexao) or die(mysql_error());
	$row_venda_email = mysql_fetch_assoc($venda_email);
	$totalRows_venda_email = mysql_num_rows($venda_email);
	// fim - venda_email

	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE 
								 (IdUsuario = '".$row_venda_email['id_usuario_responsavel']."' or (praca = %s and (nivel_venda=1 or nivel_venda=2))) and status = 1", 
									   GetSQLValueString($row_venda_email['praca'], "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	// echo $email_destinatario;
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_venda_email['praca']." ] ".$assunto." - ".$row_venda_email['id']." - ".$row_venda_email['empresa']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/venda_editar.php?id_venda=".$row_venda_email['id']."&padrao=sim'>Clique aqui para acessar a Venda</a>
	
	Empresa: ".$row_venda_email['empresa']."
	N. Venda: ".$row_venda_email['id']."
	Criação da Venda: ".date('d-m-Y  H:i', strtotime($row_venda_email['data_venda']))."
	Situação: ".$row_venda_email['situacao']."
	Responsável: ".$row_venda_email['usuario_responsavel']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($venda_email);
	mysql_free_result($usuarios_email);

}
// fim - email_venda_sem_movimento() --------------------------------------------------------------------------



// email_suporte_sem_movimento() --------------------------------------------------------------------------------
// envia e-mail para usuário responsável pela ação e para o controlador de praça
function email_suporte_sem_movimento($id_suporte, $assunto, $conteudo){

	// conexão
	require('Connections/conexao.php');
	
	// suporte_email
	$colname_suporte_email = "-1";
	if (isset($id_suporte)) {
	  $colname_suporte_email = $id_suporte;
	}
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_email = sprintf("
	SELECT id, titulo, empresa, contrato, praca, data_suporte, situacao 
	FROM suporte 
	WHERE id = %s", 
	GetSQLValueString($colname_suporte_email, "int"));
	$suporte_email = mysql_query($query_suporte_email, $conexao) or die(mysql_error());
	$row_suporte_email = mysql_fetch_assoc($suporte_email);
	$totalRows_suporte_email = mysql_num_rows($suporte_email);
	// fim - suporte_email

	// usuarios_email
	mysql_select_db($database_conexao, $conexao);
	$query_usuarios_email = sprintf("
								 SELECT nome, email, email2 
								 FROM usuarios
								 WHERE 
								 (IdUsuario = '".$row_suporte_email['id_usuario_responsavel']."' or (praca = %s and (controle_suporte = 'Y' or suporte_operador_parceiro = 'Y'))) and status = 1", 
									   GetSQLValueString($row_suporte_email['praca'], "text"));
	$usuarios_email = mysql_query($query_usuarios_email, $conexao) or die(mysql_error());
	$row_usuarios_email = mysql_fetch_assoc($usuarios_email);
	$totalRows_usuarios_email = mysql_num_rows($usuarios_email);
	// fim - usuarios_email
	
	// $email_destinatario
	$email_destinatario ="";
	do {
		$email_destinatario .= $row_usuarios_email['email'].";";
	} while ($row_usuarios_email = mysql_fetch_assoc($usuarios_email));
	// echo $email_destinatario;
	// fim - $email_destinatario

	// assunto - conteudo
	$email_assunto = "[ ".$row_suporte_email['praca']." ] ".$assunto." - ".$row_suporte_email['id']." - ".$row_suporte_email['empresa']."";
	$email_conteudo = "
	Link: <a href='http://success.inf.br/suporte_editar.php?id_suporte=".$row_suporte_email['id']."&padrao=sim'>Clique aqui para acessar o Suporte</a>
	
	Empresa: ".$row_suporte_email['empresa']."
	N. Suporte: ".$row_suporte_email['id']."
	Criação do Suporte: ".date('d-m-Y  H:i', strtotime($row_suporte_email['data_suporte']))."
	Situação: ".$row_suporte_email['situacao']."
	Responsável: ".$row_suporte_email['usuario_responsavel']." 
	
	Descrição: ".$conteudo."
	";
	// fim - assunto - conteudo
	
	$email_remetente = "automatico@success.inf.br";
	$email_headers = implode ("\n",array( "From: $email_remetente", "Reply-To: $email_remetente", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8"));
	
	mail($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
	
	mysql_free_result($suporte_email);
	mysql_free_result($usuarios_email);

}
// fim - email_suporte_sem_movimento() --------------------------------------------------------------------------
?>