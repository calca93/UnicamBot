<?php 


require_once 'functions.php';

//----------------------- FUNZIONE PRINCIPALE CONTROLLO DEL TESTO --------------------------------

function processMessage($message) {
	// process incoming message
	$message_id = $message['message_id'];
	$chat_id = $message['chat']['id'];
	
	
	//CONNESIONE AL DATABASE
	$connessione = mysql_connect("***");
	mysql_select_db("***");
	
	mysql_set_charset('utf8', $connessione);
	
	$date = date('Y-m-d', time());
	
	$myChatId = "***";
	
	//SALVATAGGIO MESSAGGI
	mysql_query("INSERT INTO messaggi (id_message, firstname, lastname, chatname, chatid, testo, data, ora) VALUES ('". $message_id ."', '". $message['from']['first_name'] ."', '". $message['from']['last_name'] ."','". $message['chat']['type'] ."' ,'". $chat_id ."', '". $message['text'] ."', '". date("Y-m-d", $message['date']) ."', '". date("H:i:s", $message['date']) ."');");
	
	
	// CONTROLLO UTENTI RIMOSSO, SALVATAGGIO NUOVI UTENTI RIMASTO INVARIATO
	//CONTROLLO SE UTENTE CON PERMESSI
	/*if(checkUser($chat_id) === 0){
		apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => 'Bot in via di sviluppo.'));
		mysql_query("INSERT INTO user (id, firstname, lastname, chatid) VALUES ('', '". $message['from']['first_name'] ."', '". $message['from']['last_name'] ."', '". $chat_id ."');");
		apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, 'text' => "Nuovo utente: ". $message['from']['first_name'] ." ". $message['from']['last_name']));
	}
	else if(checkUser($chat_id) === 1){
		apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => 'Bot in via di sviluppo.'));
		apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, 'text' => "Un utente ha cercato di interagire con il bot: \n". $message['from']['first_name'] ." ". $message['from']['last_name'] ." ". $chat_id ."\nTesto: ". $message['text']));
	}
	else{*/
	if(checkUser($chat_id) === 0){
		mysql_query("INSERT INTO user (id, firstname, lastname, chatid) VALUES ('', '". $message['from']['first_name'] ."', '". $message['from']['last_name'] ."', '". $chat_id ."');");
		apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, 'text' => "Nuovo utente: ". $message['from']['first_name'] ." ". $message['from']['last_name']));
	}
	
		if (isset($message['text'])) {
			// incoming text message
			$text = $message['text'];
			
			
			if(  (stripos($text, 'sendupdate:') !== FALSE)  &&  ($chat_id == MY_CHAT_ID)  ){
				sendUpdates(mb_strcut($text, 11));
			}
	
			//ANALISI DEL TESTO INVIATO DAGLI UTENTI
			switch (strtolower($text)){
				
				case "/send":
					apiRequest("sendMessage", array('chat_id' => "", "text" => "Abilitato come tester\n\n/start per iniziare"));
					apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, "text" => "ok"));
					break;
					
				case "unicambot ringrazia":
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Grazie per l'attenzione"));
					break;
					
				//----------- START AND DELETE ------------------------------
				case "/start":
					apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => "Per iniziare seleziona il dipartimento", 'parse_mode' => 'markdown', 'reply_markup' => array(
						'keyboard' => array(array('/Scuola di Scienze e Tecnologie', '/Scuola di Architettura e Design'), array( '/Scuola di Bioscienze e Medicina Veterinaria','/Scuola di Giurisprudenza'), array('/Scuola di Scienze del Farmaco e dei Prodotti della Salute')),
						'one_time_keyboard' => false,
						'resize_keyboard' => false)));
					break;
				case "/start@UnicamBot":
					apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => "Per iniziare seleziona il dipartimento", 'parse_mode' => 'markdown', 'reply_markup' => array(
							'keyboard' => array(array('/Scuola di Scienze e Tecnologie', '/Scuola di Architettura e Design'), array( '/Scuola di Bioscienze e Medicina Veterinaria','/Scuola di Giurisprudenza'), array('/Scuola di Scienze del Farmaco e dei Prodotti della Salute')),
							'one_time_keyboard' => false,
							'resize_keyboard' => false)));
					break;
					
				case "/info":
					printHelp($chat_id);
					break;
				case "/info@UnicamBot":
					printHelp($chat_id);
					break;
					
				case "/cancellami":
					mysql_query("UPDATE user SET scuola = '', corso = '', anno = '' WHERE chatid = '". $chat_id ."' ;");
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Le tue informazioni sono state cancellate\nUsare /start per reinizializzare"));
					break;
					
				case "/show":
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					printSelection($chat_id);
					break;
					
				case "/notificheon":
					mysql_query("UPDATE user SET reminder = '1' WHERE chatid = '". $chat_id ."' ;");
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Notifiche giornaliere attivate dal lunedì al venerdì\nIl messaggio arriverà intorno alle 8:00"));
					break;
					
				case "/notificheoff":
					mysql_query("UPDATE user SET reminder = '0' WHERE chatid = '". $chat_id ."' ;");
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Notifiche giornaliere disattivate"));
					break;
					
				case "/oggi" :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					printDay($chat_id, "oggi");
					break;
					
				case "/domani" :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					printDay($chat_id, "domani");
					break;
					
				case "/professori" :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					printProf($chat_id);
					break;
				//---------------------------------------------------------------------------------
				//----------------------------- COMANDI TESTUALI ----------------------------------
				case "mostra tutte le mie lezioni" :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}

					printAllWeek($chat_id);
						
					break;
					
				case "mostra tutti i miei esami" :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					
					printExam($chat_id);
						
					break;
					
				//----------------------------- GIORNI --------------------------------------------
				case "che lezioni ho oggi?" :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					printDay($chat_id, "oggi");
					break;
					
				case "che lezioni ho domani?" :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					printDay($chat_id, "domani");
					break;
				
				case utf8_encode("che lezioni ho il lunedì?") :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					
					printDay($chat_id, "Lunedì");
					
					break;
				
				case utf8_encode("che lezioni ho il martedì?") :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
						
					printDay($chat_id, "Martedì");
					
					break;
				

				case utf8_encode("che lezioni ho il mercoledì?") :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
			
					printDay($chat_id, "Mercoledì");
					
					break;
						

				case utf8_encode("che lezioni ho il giovedì?") :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
				
					printDay($chat_id, "Giovedì");
					
					break;
							

				case utf8_encode("che lezioni ho il venerdì?") :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
				
					printDay($chat_id, "Venerdì");
					
					break;
					
				case utf8_encode("che lezioni ho il sabato?") :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
				
					printDay($chat_id, "Sabato");
						
					break;
						
				case utf8_encode("che lezioni ho la domenica?") :
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
				
					printDay($chat_id, "Domenica");
						
					break;
						
					
				//-------------------------------- ALTRI COMANDI -----------------------------------
				
				case "info professori":
					if(checkSelection($chat_id) === false){
						printRetry($chat_id);
						break;
					}
					printProf($chat_id);
					break;
			
				case "hi":
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you'));
					break;
				
				case "hello":
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you'));
					break;
					
				case "ciao":
					apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Ciao, come va?'));
					break;
				
					
				//----------------------------------------------------------------------------------------------
				//------------------------------- SELEZIONI ----------------------------------------------------
				//----------------------------------------------------------------------------------------------
				
				//----------- SELEZIONE CORSO -------------------------------
				case '/scuola di scienze e tecnologie':
					mysql_query("UPDATE user SET scuola = '/Scuola di Scienze e Tecnologie' WHERE chatid = '". $chat_id ."' ;");
					apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => 'Seleziona il corso', 'reply_markup' => array(
						'keyboard' => array(array('/Chimica L-27', '/Chemistry LM-54'), array('/Restauro L-43'), array('/Geologia L-32 & L-34', '/Geoenvironmental LM-74'), array( '/Fisica L-30', '/Physics LM-17'), array('/Matematica L-35', '/Matematica LM-40'), array('/Informatica L-31', '/Computer Science LM-18')),
						'one_time_keyboard' => false,
						'resize_keyboard' => true)));
					break;
					
				case '/scuola di architettura e design':
					mysql_query("UPDATE user SET scuola = '/Scuola di Architettura e Design' WHERE chatid = '". $chat_id ."' ;");
					printDev($chat_id);
					break;
					
				case '/scuola di bioscienze e medicina veterinaria':
					printDev($chat_id);
					break;
					
				case '/scuola di giurisprudenza':
					printDev($chat_id);
					break;
					
				case '/scuola di scienze del farmaco e dei prodotti della salute':
					printDev($chat_id);
					break;
					
				
				//----------- SELEZIONE ANNO --------------------------------
				
				//SCIENZE E TECNOLOGIE
				case '/chimica l-27':
					printDev($chat_id);
					break;
				case '/chimica lm-54':
					printDev($chat_id);
					break;
				case '/restauro l-43':
					printDev($chat_id);
					break;
				case '/geologia l-32 & l-34':
					printDev($chat_id);
					break;
				case '/geoenvironmental lm-74':
					printDev($chat_id);
					break;
				case '/fisica l-30':
					printDev($chat_id);
					break;
				case '/physics lm-17':
					printDev($chat_id);
					break;
				case '/matematica l-35':
					printDev($chat_id);
					break;
				case '/matematica lm-40':
					printDev($chat_id);
					break;
				
				case '/informatica l-31':
					mysql_query("UPDATE user SET corso = '/Informatica' WHERE chatid = '". $chat_id ."' ;");
					apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => 'Seleziona l\'anno', 'reply_markup' => array(
						'keyboard' => array(array('/Primo anno', '/Secondo anno', '/Terzo anno')),
						'one_time_keyboard' => false,
						'resize_keyboard' => true)));
					break;
				
				case '/computer science lm-18':
					mysql_query("UPDATE user SET corso = '/Computer Science' WHERE chatid = '". $chat_id ."' ;");
					apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => 'Seleziona l\'anno', 'reply_markup' => array(
						'keyboard' => array(array('/Primo anno', '/Secondo anno'),),
						'one_time_keyboard' => false,
						'resize_keyboard' => true)));
					break;
				
				//----------- CONFERMA SELEZIONE -----------------------------
				//ANNO - NUMERO CORRISPONDENTE NELLA TABELLA
				//	1°   			- 1
				//	2°   			- 2
				//	3°   			- 3
				//	4°   			- 4
				//	5°   			- 5
				//	Fuori corso   	- 99
				
				case '/primo anno':
					mysql_query("UPDATE user SET anno = '1' WHERE chatid = '". $chat_id ."' ;");
					printDone($chat_id);
					break;
				case '/secondo anno':
					mysql_query("UPDATE user SET anno = '2' WHERE chatid = '". $chat_id ."' ;");
					printDone($chat_id);
					break;
				case '/terzo anno':
					mysql_query("UPDATE user SET anno = '3' WHERE chatid = '". $chat_id ."' ;");
					printDone($chat_id);
					
					//SE CORSO INFORMATICA E SE TERZO ANNO, SPECIFICARE CHE GLI ESAMI A SCELTA DELLA MAGISTRALE 
					//SI VISUALIZZANO SOLO SE SI SELEZIONA "1° ANNO MAGISRTALE"
					break;
					
				//-------- SELEZIONI PER MAGISTRALI DA 5 ANNI ------------
				case '/quarto anno':
					mysql_query("UPDATE user SET anno = '4' WHERE chatid = '". $chat_id ."' ;");
					printDone($chat_id);
					break;
				case '/quinto anno':
					mysql_query("UPDATE user SET anno = '5' WHERE chatid = '". $chat_id ."' ;");
					printDone($chat_id);
					break;
				//--------------------------------------------------------
				
				case '/fuori corso':
					mysql_query("UPDATE user SET anno = '99' WHERE chatid = '". $chat_id ."' ;");
					printDone($chat_id);
					break;
	
				default :
					break;
			}
		}

	/* Telegram example
	if (strpos($text, "/start") === 0) {
    apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Hello', 'reply_markup' => array(
        'keyboard' => array(array('Hello', 'Hi')),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
    
    } else if ($text === "Hello" || $text === "Hi") {
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you'));
    } else if (strpos($text, "/stop") === 0) {
        // stop now
    } else {
        apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Cool'));
    }
    } else {
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'I understand only text messages'));
    }*/
}

?>