<?php 

require_once 'bot.php';

define('BOT_TOKEN', '***');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

$date = date('N', time());

$connessione = mysql_connect("***");
mysql_select_db("***");

$query = mysql_query("SELECT user.chatid FROM user WHERE user.reminder = '1'");
$n = mysql_num_rows($query);

if(!query)
	apiRequest("sendMessage", array('chat_id' => MY_CHAT_ID, 'text' => "Errore con la query: Servizio di notifica giornaliero"));
else {
	for($i = 0; $i < $n ; $i++){
		$chat_id = mysql_result($query, $i, 0);
		printDay($chat_id, "oggi", "1");
	}
}
?>