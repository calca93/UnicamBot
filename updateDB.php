<?php 
   
	require_once 'handlers.php';
	
	//$myChatId = "***";
	
    $key = "***";
    $gid = array("0", "670695282", "937351042", "1685980731", "1216365949");
    
    $connessione = mysql_connect("***");
    mysql_select_db("***");
    
    $delete = mysql_query("DELETE FROM LezioneAuto");
    
    //_------------------------------ TRIENNALE------------------------------------------
    //CICLO FOR CHE SCORRE I FOGLI DELLO SPREADSHEET (TRIENNALE)
    for($k = 0; $k < 3 ; $k++){
    	
	    $url = "https://docs.google.com/spreadsheets/u/2/d/$key/gviz/tq?&tq=&tqx=out:json&gid=$gid[$k]";
	    $ch = curl_init();
	
	    // set URL and other appropriate options
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
	    // grab URL and pass it to the browser
	    $google_sheet = curl_exec($ch);
	
	    // close cURL resource, and free up system resources
	    curl_close($ch);
	    
	    //TAGLIO PRIMI 47 E ULTIMI 2 CARATTERI PERCHE' NON JSON
		$str = mb_strcut($google_sheet, 47);
		$str = mb_strcut($str, 0, strlen($str)-2);
		$update = json_decode($str, true);
		//echo $str;
		//echo 'Corso '. ($k+1) .'<br>';
		
		//SCANNERIZZA LA TABELLA DEGLI ORARI	
		// FOR CHE SCORRE I GIORNI DELLA SETTIMANA
		for($j = 1; $j < 6; $j++){
			$day = ($j==1?"Lunedì":($j==2?"Martedì":($j==3?"Mercoledì":($j==4?"Giovedì":($j==5?"Venerdì":"")))));
			$durata = 0;
			$esame = "";
			
			// FOR CHE SCORRE LE ORE DELLA GIORNATA
			for($i = 4; $i < 12 ; $i++){
				$ora_ini = ($i==4?9:($i==5?10:($i==6?11:($i==7?12:($i==8?15:($i==9?16:($i==10?17:($i==11?18:0))))))));
				
				$text = utf8_decode( $update["table"]["rows"][$i][c][$j]["v"] . " ");
				
				if($esame != $text){
					// CONTROLLO PRIMISSIMA LEZIONE, NON FARE QUERY
					if(  ($esame != "")  ){
						//echo "query: $esame $ini - ". ($ini+$durata) ." | $day " . '<br>'; // !!! QUERY UPDATE !!!
						$query = query($ini, $ini+$durata, $day, $esame, "");
					}
					
					// RESET PARAMETRI SE RIGA VUOTA E SALTO
					if(!isset($update["table"]["rows"][$i][c][$j]["v"])){
						$durata = 0;
						$esame = "";
						continue;
					}
					$esame = $text;
					$ini = $ora_ini;
					$durata = 1;
					
				}
				else {
					$durata++;
					if($i == 11){
						//echo "query: $esame $ini - ". ($ini+$durata) ." | $day". '<br>'; // !!! QUERY UPDATE !!!
						$query = query($ini, $ini+$durata, $day, $esame, "");
					}
				}
			}
			echo '<br>';
		}
		echo '----------------------------------------------------------<br>';
    }
    //---------------------------------------------------------------------------------------------------------------
    
    
    //------------------------------------ MAGRISTRALE -------------------------------------------------------------
    $url = "https://docs.google.com/spreadsheets/u/2/d/$key/gviz/tq?&tq=&tqx=out:json&gid=$gid[3]&range=A1:F30";
    $ch = curl_init();

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
    // grab URL and pass it to the browser
    $google_sheet = curl_exec($ch);

    // close cURL resource, and free up system resources
    curl_close($ch);
	    
    
    //TAGLIO PRIMI 47 E ULTIMI 2 CARATTERI PERCHE' NON JSON
	$str = mb_strcut($google_sheet, 47);
	$str = mb_strcut($str, 0, strlen($str)-2);
	$update = json_decode($str, true);
	//echo $str;
	//echo 'Magistrale <br>';
		
	//SCANNERIZZA LA TABELLA DEGLI ORARI	
	// FOR CHE SCORRE I GIORNI DELLA SETTIMANA
	for($j = 1; $j < 6; $j++){
		$day = ($j==1?"Lunedì":($j==2?"Martedì":($j==3?"Mercoledì":($j==4?"Giovedì":($j==5?"Venerdì":"")))));
		$durata = 0;
		$esame = "";
			
		// FOR CHE SCORRE LE ORE DELLA GIORNATA
		for($i = 3; $i < 12 ; $i++){
			$ora_ini = ($i==3?9:($i==4?10:($i==5?11:($i==6?12:($i==7?13:($i==8?15:($i==9?16:($i==10?17:($i==11?18:0)))))))));
				
			$text = utf8_decode( $update["table"]["rows"][$i][c][$j]["v"] . " ");
				
			if($esame != $text){
				// CONTROLLO PRIMISSIMA LEZIONE, NON FARE QUERY
				if(  ($esame != "")  ){
					//echo "query: $esame $ini - ". ($ini+$durata) ." | $day ". '<br>'; // !!! QUERY UPDATE !!!
					$query = query($ini, $ini+$durata, $day, $esame, "");
				}
					
				// RESET PARAMETRI SE RIGA VUOTA E SALTO
				if(!isset($update["table"]["rows"][$i][c][$j]["v"])){
					$durata = 0;
					$esame = "";
					continue;
				}
				$esame = $text;
				$ini = $ora_ini;
				$durata = 1;
					
			}
			else {
				$durata++;
				if($i == 11){
					//echo "query: $esame $ini - ". ($ini+$durata) ." | $day ". '<br>'; // !!! QUERY UPDATE !!!
					$query = query($ini, $ini+$durata, $day, $esame, "");
				}
			}
		}
		echo '<br>';
	}
	
	$date = Date('D, d M Y H:i:s');
	$message = "$date\nStato aggiornamento: ";
	if($query)
		$message .= "eseguito correttamente";
	else 
		$message .= "errore";//print error
	
	//apiRequest("sendMessage", array('chat_id' => $myChatId, "text" => $message));
	
	
	
	
	
	
	
function query($ini, $fin, $giorno, $esame, $luogo){
	$connessione = mysql_connect("***");
	mysql_select_db("***");
	
	
	if(stripos($esame, "*") || stripos($esame, "(+)")){
		$esame = str_ireplace('*', '', $esame);
		$esame = str_ireplace('(+)', '', $esame);
	}
	
	
	
	if(stripos($esame, ' - ')){
		$array = explode(' - ', $esame);
	}
	else if(stripos($esame, '-')){
		$n = stripos($esame, '-');
		$array = array(mb_strcut($esame, 0, $n));
	}
	else if(stripos($esame, ' ')){
		$array = array(str_ireplace(' ', '', $esame));
	}
	else
		$array = array($esame);
	
	for($i = 0; $i < count($array); $i++){
		$esame = $array[$i];
		echo $esame;
		
		$query = mysql_query("SELECT id_esame FROM Esame WHERE Esame.Acronimo = '$esame' ;");
		$result = mysql_result($query, 0);
		
		//CERCO ESAME SU TABELLA ESAME
		if(!query){
			//apiRequest("sendMessage", array('chat_id' => $myChatId, "text" => "Errormessage: ". $mysqli->error ));
			$id_esame = 0;
		}
		else 
			$id_esame = $result;
		
		echo ' - idesame: '. $id_esame ."  (". $ini ."-". $fin . ") ";
		
		
		
		//CERCO LUOGO SU TABELLA LUOGO-----------------
		$id_luogo = 15;//LUOGO VUOTO
		//-----------------------------------------------
		echo " - idluogo: ". $id_luogo .'<br>';
		
		
		$mysqli = new mysqli("***");
		
		/*if (!$mysqli->query("INSERT INTO LezioneAuto (id_lezionep, ini, fin, Giorno, id_esame, id_luogo) VALUES (NULL, '$ini', '$fin', '". mysql_real_escape_string($giorno) ."', '$id_esame', '$id_luogo');")) {
			apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, "text" => "Errormessage: ". $mysqli->error ));
		}*/
	}
} 
?>