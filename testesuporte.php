<?php     
    $email_remetente = "contato@success.inf.br"; 
    $email_destinatario = "testeiseuemail@gmail.com";
    $email_assunto = "Testando Cron.";
 
     $mensagem = "Teste de cron efetuado.";
 
    $email_conteudo .=  "Mensagem = $mensagem \n";
 
    $email_headers = implode ( "\n",array ( "From: $email_remetente", "Reply-To: $email_reply", "Subject: $email_assunto","Return-Path:  $email_remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html; charset=UTF-8" ) );
 
    mail ($email_destinatario, $email_assunto, nl2br($email_conteudo), $email_headers);
?>