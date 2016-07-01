# UnicamBot
A Telegram bot that provides information about Unicam courses and exams


## Features

#### Comandi rapidi
Triggerable by type '/'

- /start - Reinizializza il bot
- /info - Mostra le impostazioni ed i comandi supportati
- /cancellami - Cancella la tua selezione
- /show - Mostra la tua selezione corrente
- /notificheon - Attiva notifiche giornaliere
- /notificheoff - Disattiva notifiche giornaliere
- /oggi - Mostra lezioni odierne
- /domani - Mostra le lezioni di domani
- /professori - Mostra la lista dei professori e loro informazioni
			
#### Comandi testuali
Triggerable by type the exact messagge into the chat

- Che lezioni ho oggi? - Mostra le lezioni odierne
- Che lezioni ho domani? - Mostra le lezioni di domani
- Che lezioni ho il martedì? - Mostra le lezioni per un giorno specifico
- Mostra tutte le mie lezioni - Lista delle lezioni in settimana
- Mostra tutti i miei esami - Lista degli esami e relativi professori
- Info professori - Mostra la lista dei professori e loro informazioni

## About
You can find this bot on Telegram searching @UnicamBot or following this link: 
[Unicam Bot](https://web.telegram.org/#/im?p=@UnicamBot)

At the beginning the bot asks your university class then it stores these choices
in the database. Every users requests will be elaborated according to the 
initial choices.

This is a quickly and friendly way to get this information for a student, 
instead of searching them in a cumbersome university site.

At the moment, only two courses "Informatica L-31" and "Computer Science LM-18" 
are supported by this bot.

I've made this bot as my bachelor thesis at the University of Camerino.

## To do

- The updateDB.php file should update automatically lessons and other information 
stored in the database from a Google Sheet provided by the university.


## Privacy
This code is not the production version of the application.

Some data are hidden due to keep secure the bot

