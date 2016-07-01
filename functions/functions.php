<?php 

require_once 'handlers.php';

date_default_timezone_set('EUROPE/ROME');

define('USED_TABLE_LEZIONE', 'Lezione');

//-------------------- FUNZIONI SECONDARIE -----------------------------------------------------

function checkSelection($param){
	$chat_id = $param;

	$connessione = mysql_connect("***");
	mysql_select_db("***");
	$query = mysql_query("SELECT * FROM user WHERE chatid IN ( '". $chat_id ."' );");// AND ( scuola='' OR corso='' OR anno='');");

	if(!query) // VERIFICA ERRORE QUERY
		return false;
	else {
		$scuola = mysql_result($query, 0, 5);
		$corso = mysql_result($query, 0, 6);
		$anno = mysql_result($query, 0, 7);
		
		//IF PER FAR VISUALIZZARE LE LEZIONI DELLA MAGISTRALE SIA AL PRIMO CHE AL SECONDO ANNO
		if($scuola == "/Scuola di Scienze e Tecnologie" && $corso == "/Computer Science"){
			$anno = 1;
			mysql_query("UPDATE user SET anno = '1' WHERE user.chatid = $chat_id ;");
		}

		if($scuola === "" || $corso === "" || $anno === "") // VERIFICA SCELTA NON EFFETTUATA
			return false;
		else { // VERIFICA CAMPI CONGRUENTI
			$query = mysql_query("
				SELECT Esame.Nome
				FROM Scuola, Corso, Esame, user
				WHERE user.chatid IN ('". $chat_id ."') AND ". $anno ." IN (
					SELECT Esame.Anno
					FROM Esame
					WHERE Esame.id_corso IN (
						SELECT Corso.id_corso
						FROM Corso
						WHERE Corso.Nome IN ('". $corso ."') AND Corso.id_scuola IN (
							SELECT Scuola.id_scuola
							FROM Scuola
							WHERE Scuola.Nome IN ('". $scuola ."')
						)
					)
				);");
			if(mysql_num_rows($query) === 0)
				return false;
			}
	}

	return true;
}

function printSelection($param){
	$chat_id = $param;
	
	$connessione = mysql_connect("***");
	mysql_select_db("***");
	$query = mysql_query("SELECT user.scuola, user.corso, user.anno FROM user WHERE chatid = '". $chat_id ."' ;");
	
	if(!query)
		$text = "Errore con la query\n";
	else {
		$text = "";
		switch (mysql_result($query, 0, 2)){
			case 1:
				$anno = "Primo anno";
				break;
			case 2:
				$anno = "Secondo anno";
				break;
			case 3:
				$anno = "Terzo anno";
				break;
			case 4:
				$anno = "Quarto anno";
				break;
			case 5:
				$anno = "Quinto anno";
				break;
			default:
				break;
		}
		$text .=  mb_strcut( mysql_result($query, 0, 0), 1). "\n". mb_strcut( mysql_result($query, 0, 1), 1) ." - ". $anno;
		
	}
	
	apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
					
}

function printHelp($param){
	$chat_id = $param;
	$texthelp = "aiuto";

	if(!checkSelection($chat_id))
		$texthelp = "Selezione non effettuata correttamente\n\n/start per riprovare";
		else {
			$texthelp =
			"\n*Comandi rapidi*\n".

			"/start - Reinizializza il bot\n".
			"/info - Mostra le impostazioni ed i comandi supportati\n".
			"/cancellami - Cancella la tua selezione\n".
			"/show - Mostra la tua selezione corrente\n".
			"/notificheon - Attiva notifiche giornaliere\n".
			"/notificheoff - Disattiva notifiche giornaliere\n".
			"/oggi - Mostra lezioni odierne\n".
			"/domani - Mostra le lezioni di domani\n".
			"/professori - Mostra la lista dei professori e loro informazioni\n".
			
			"\n*Comandi testuali*\n".

			"Che lezioni ho oggi? - Mostra le lezioni odierne\n".
			"Che lezioni ho domani? - Mostra le lezioni di domani\n".
			"Che lezioni ho il martedì? - Mostra le lezioni per un giorno specifico\n".
			"Mostra tutte le mie lezioni - Lista delle lezioni in settimana\n".
			"Mostra tutti i miei esami - Lista degli esami e relativi professori\n".
			"Info professori - Mostra la lista dei professori e loro informazioni\n".
			
			//"\n IN COSTRUZIONE \n".
			
			"\n";
		}

		apiRequestJson("sendMessage", array('chat_id' => $param, 'text' => $texthelp, 'parse_mode' => 'markdown', 'reply_markup' => array(
				'hide_keyboard' => true)));
}

function checkUser($param){
	//RETURN 0 SE NON HA MAI SCRITTO
	//RETURN 1 SE HA SCRITTO MA NON E' TESTER
	//RETURN 2 SE UTENTE TESTER
	//RETURN 2 SE HA SCRITTO ED E' TESTER
	$connessione = mysql_connect("***");
	mysql_select_db("***");
	$result = 0;

	$query = mysql_query("SELECT * FROM user WHERE chatid IN ( '". $param ."' ) ;");
	if(mysql_num_rows($query) === 1)
		$result = 1;

	$query = mysql_query("SELECT * FROM user WHERE chatid IN ( '". $param ."' ) AND tester = 1;");
	if(mysql_num_rows($query) === 1)
		$result = 2;

	return $result;
}

function printDev($param){
	apiRequest("sendMessage", array('chat_id' => $param, 'text' => "Funzionalita' in via di sviluppo."));
}

function printDone($param){
	if(checkSelection($param) === true)
		apiRequestJson("sendMessage", array('chat_id' => $param, 'text' => 'Selezione effettuata', 'reply_markup' => array(
				'hide_keyboard' => true)));
	printHelp($param);
}

function printRetry($param){
	apiRequestJson("sendMessage", array('chat_id' => $param, 'text' => "Selezione non effettuata correttamente\n\n/start per riprovare", 'reply_markup' => array(
				'hide_keyboard' => true)));
}

function printDay($param, $param2, $param3){
	$chat_id = $param;
	$day = $param2;
	$date;

	if($day === "oggi" || $day === "domani"){
		$date = date('N', time());
		if($day === "domani")
			$date += 1;
		switch($date){
			case "1":
				$day = "Lunedì";
				break;
			case "2":
				$day = "Martedì";
				break;
			case "3":
				$day = "Mercoledì";
				break;
			case "4":
				$day = "Giovedì";
				break;
			case "5":
				$day = "Venerdì";
				break;
			case "6":
				$day = "Sabato";
				break;
			case "7":
				$day = "Domenica";
				break;
			default:
				$day = "Lunedì";
				break;
		}
	}

	$connessione = mysql_connect("***");
	mysql_select_db("***");

	//CONTROLLO SE PROVENIENTE DALLA PAGINA ESEGUITA AUTOMATICAMENTE
	if ($param3 == "1"){
		$test = $day;
		$text = "Notifica giornaliera\n";
	}
	else 
		$test = utf8_encode($day);
	
	//AGGIUNGERE CONTROLLI
	$a = mysql_query("SELECT user.anno, user.corso FROM user WHERE chatid IN ('". $chat_id ."')");
	$anno = mysql_result($a, 0, 0);
	$corso = mysql_result($a, 0, 1);
	
	//SELEZIONARE OGNI LEZIONE DI OGNI ESAME PER IL CORSO SELEZIONATO
	$query = mysql_query("
		SELECT Esame.Nome, ".USED_TABLE_LEZIONE.".ini, ".USED_TABLE_LEZIONE.".fin, Luogo.Nome, Luogo.Maps
		FROM Corso, Esame, ".USED_TABLE_LEZIONE.", Luogo
		WHERE Corso.id_corso = Esame.id_corso AND ".USED_TABLE_LEZIONE.".id_esame = Esame.id_esame AND ".USED_TABLE_LEZIONE.".id_luogo = Luogo.id_luogo
		AND Corso.Nome = '". $corso ."' AND ".USED_TABLE_LEZIONE.".Giorno = '". $test ."' AND Esame.Anno = '". $anno ."'
		ORDER BY ".USED_TABLE_LEZIONE.".ini ASC
		;");
	
	$n = mysql_num_rows($query);
	
	if(!$query || !$a)
		$text = "Errore con la query\n";
	else if($n === 0){
		$text .= "\nIl ". $day ." non hai lezioni da seguire\n";
		if( ($date == 6 || $date == 7) && $param3 == 1)
			$text = "";
	}
	else {
		$text .= "Lezioni: " . $n ."\n\n";
		for($i = 0; $i < $n ; $i++){
			$text .= "- ". mysql_result($query, $i, 0) ."\n". mysql_result($query, $i, 1) ." - ". mysql_result($query, $i, 2) ." | [". mysql_result($query, $i, 3) ."](". mysql_result($query, $i, 4) .")\n";
		}
	}
	
	// DETTAGLI
	if($text != ""){
		if($corso == "/Informatica" && $anno == "3")
			$text .= "\nINFO: Per visualizzare le lezioni degli esami a scelta dovrai selezionare Computer Science come corso";
		if($corso == "/Computer Science" && $anno == "2")
			$text .= "\nINFO: La maggior parte degli esami li della magistrale li trovi raggruppati al primo anno";
	}
	
	if( $param3 == 1 && $text != "")
		$text .= "\n/notificheoff per disattivare le notifiche";
	if(! ($param3 == 1 && $text == ""))
		apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'markdown'));
}

function printAllWeek($param){
	$chat_id = $param;
	
	apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Un attimo che controllo...\n"));
	sleep(1);
	apiRequest("sendChatAction", array('chat_id' => $chat_id, 'action' => 'typing'));
	
	$connessione = mysql_connect("***");
	mysql_select_db("******");
	
	$a = mysql_query("SELECT user.anno, user.corso FROM user WHERE chatid = (". $chat_id .")");
	$anno = mysql_result($a, 0, 0);
	$corso = mysql_result($a, 0, 1);
	
	$query = mysql_query("
		SELECT Esame.Nome, ".USED_TABLE_LEZIONE.".Giorno, ".USED_TABLE_LEZIONE.".ini, ".USED_TABLE_LEZIONE.".fin, Luogo.Nome, Luogo.Maps
		FROM Corso, Esame, ".USED_TABLE_LEZIONE.", Luogo
		WHERE Corso.id_corso = Esame.id_corso AND ".USED_TABLE_LEZIONE.".id_esame = Esame.id_esame AND ".USED_TABLE_LEZIONE.".id_luogo = Luogo.id_luogo
		AND Esame.Anno = '". $anno ."' AND Corso.Nome = '". $corso ."'
		ORDER BY
			CASE
				WHEN Giorno = '". utf8_encode("Lunedì") ."' THEN 1
		  		WHEN Giorno = '". utf8_encode("Martedì") ."' THEN 2
		   		WHEN Giorno = '". utf8_encode("Mercoledì") ."' THEN 3
		   		WHEN Giorno = '". utf8_encode("Giovedì") ."' THEN 4
		   		WHEN Giorno = '". utf8_encode("Venerdì") ."' THEN 5
		   	 	WHEN Giorno = '". utf8_encode("Sabato") ."' THEN 6
		   		WHEN Giorno = '". utf8_encode("Domenica") ."' THEN 7
			END 
		ASC, ".USED_TABLE_LEZIONE.".ini ASC;");
	
	$n = mysql_num_rows($query);
	
	if(!$query || !$a)
		$text = "Errore con la query";
	else {
		$text .= "Lezioni: " . $n ."\n";
		$day = "";
		for($i = 0; $i < $n ; $i++){
			if($day != mysql_result($query, $i, 1)){
				$day = mysql_result($query, $i, 1);
				$text .= "\n". utf8_decode($day) .":\n";
			}
			$text .= "- ". mysql_result($query, $i, 0) ."\n". mysql_result($query, $i, 2) ." - ". mysql_result($query, $i, 3) ." | [". mysql_result($query, $i, 4) ."](". mysql_result($query, $i, 5) .")\n";
		}
	}
	
	// DETTAGLI
	if($corso == "/Informatica" && $anno == "3")
		$text .= "\nINFO: Per visualizzare le lezioni degli esami a scelta dovrai selezionare Computer Science come corso";
	if($corso == "/Computer Science" && $anno == "2")
		$text .= "\nINFO: La maggior parte degli esami li della magistrale li trovi raggruppati al primo anno";
	
	sleep(1);
	apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'markdown'));
}

function printProf($param){
	$chat_id = $param;
	
	apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Un attimo che controllo...\n"));
	sleep(1);
	apiRequest("sendChatAction", array('chat_id' => $chat_id, 'action' => 'typing'));
	
	$connessione = mysql_connect("***");
	mysql_select_db("***");
	
	$a = mysql_query("SELECT user.anno FROM user WHERE chatid IN ('". $chat_id ."')");
	$anno = mysql_result($a, 0, 0);
	
	$query = mysql_query("
		SELECT DISTINCT Professore.Nome, Professore.Cognome, Professore.Mail, Professore.Ricevimento, Professore.Ora
		FROM Professore, mid, Esame
		WHERE Professore.id_prof = mid.id_prof AND Esame.id_esame = mid.id_esame
		AND Esame.anno IN ('". $anno ."');" );
	
	$n = mysql_num_rows($query);
	
	if(!$query || !$a)
		$text = "Errore con la query: ". mysql_error();
	else {
		$text .= "Professori: " . $n ."\n\n";
		for($i = 0; $i < $n ; $i++){
			$text .= "- ". mysql_result($query, $i, 1) ." ". mysql_result($query, $i, 0) ." - ". mysql_result($query, $i, 2) ."\nRicevimento ". utf8_decode(mysql_result($query, $i, 3)) ."  ". mysql_result($query, $i, 4) ."\n";
		}
	}
	
	
	sleep(1);
	apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'markdown'));
}

function printExam($param){
	$chat_id = $param;
	
	apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Un attimo che controllo...\n"));
	sleep(1);
	apiRequest("sendChatAction", array('chat_id' => $chat_id, 'action' => 'typing'));
	
	$connessione = mysql_connect("***");
	mysql_select_db("***");
	
	$a = mysql_query("SELECT user.anno, user.corso FROM user WHERE chatid IN ('". $chat_id ."')");
	$anno = mysql_result($a, 0, 0);
	$corso = mysql_result($a, 0, 1);
	
	$query = mysql_query("
		SELECT Esame.Nome, Esame.Crediti, Professore.Cognome, mid.Tipo
		FROM Esame, Corso, mid, Professore
		WHERE Corso.id_corso = Esame.id_corso AND Esame.id_esame = mid.id_esame AND mid.id_prof = Professore.id_prof
		AND Esame.Anno IN ('". $anno ."') AND Corso.Nome IN ('". $corso ."') ;");
	$n = mysql_num_rows($query);
	
	$text = "";

	if(!$query || !$a)
		$text = "Errore con la query\n";
	else {
		if($n == 0)
			$text = "Nessun esame da visualizzare";
		else{
			$esame = "";
			$text .= "Esami: ";
			for($i = 0; $i < $n ; $i++){
				if($esame != mysql_result($query, $i, 0)){
					$esame = mysql_result($query, $i, 0);
					$text .= "\n\n". mysql_result($query, $i, 0) ." - Crediti: ". mysql_result($query, $i, 1) ."\n| ";
				}
				$text .= mysql_result($query, $i, 2);
				if(mysql_result($query, $i, 3) != "")
					$text .= " (". mysql_result($query, $i, 3) .")";
				$text .= " | ";
			}
		}
	}
	
	
	sleep(1);
	apiRequestJson("sendMessage", array('chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'markdown'));
			
}

function sendUpdates($param){
	$query = mysql_query("SELECT user.chatid FROM user WHERE user.tester = '1'");
	$n = mysql_num_rows($query);
	apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, 'text' => $n));
	
	if(!query)
		apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, 'text' => "Errore con la query\n"));
	else {
			for($i = 0; $i < $n ; $i++){
				$chat_id = mysql_result($query, $i, 0);
				apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => $param));
			}
	}
}

?>