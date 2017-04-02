# Telegram UDF for PHP <img src="https://s30.postimg.org/h95ulyoap/telegram_icon.png" width="28"> <img src="https://s27.postimg.org/42x1gujn7/icon_php.png" width="28">

<p align="center">
  <img src="https://s27.postimg.org/p28idh5wz/Banner.png"><br>
</p>
<p align="center">
  <b>If you want to control your Telegram Bot with PHP, this library is for you!</b><br>
</p>

## How it work:

1. Download "Telegram.php";
2. Include it in your page: `require('Telegram.php');`
3. Initialize your bot **before** use other function: `_InitBot(12345678:AbCdEfGh....)`
4. Now you can use all the functions provided in the file.

### How to wait for incoming messages:

This library doesn't use WebHook, but GetUpdates method aka Long Polling: you can host your script anywhere, on any remote or local server because you don't need SSL Certificate (required instead to use WebHook method).
To wait for incoming messages you have to put the bot in Polling State, as this

```php
While (true){ //Create a While to restart Polling after processing a messag
	$msgData = _Polling() //_Polling function return an array with info about message
	_SendMsg($msgData['chat_id'],$msgData['text']); //Send a message to the same user with the same text
}
```
Polling function return an array with information about the message, like Chat ID, Username, Text or File ID if it contain a photo, video, document... Check JSONDecode function for further information.


## Functions:
* **InitBot:** _Initialize bot (require BotID and BotTOKEN);_
* **Polling:** _Wait for incoming messages;_
* **GetUpdates:** _Get new messages from Telegram Server;_
* **GetMe:** _Get information about the bot;_
* **SendMsg:** _Send simple text message without any parameters;_
* **CurlSendMsg:** _Send text message, support custom parse mode, reply markup and other param;_
* **ForwardMsg:** _Forward a message from a chat to another;_
* **SendPhoto:** _Send a photo to a specific chat;_
* **SendVideo:** _Send a video to a specific chat;_
* **SendAudio:** _Send an audio to a specific chat;_
* **SendDocument:** _Send a document to a specific chat;_
* **SendVoice:** _Send a voice to a specific chat;_
* **SendSticker:** _Send a sticker to a specific chat;_
* **SendChatAction:** _Set the 'Chat Action' for 5 seconds (Typing, Sending photo...);_
* **SendLocation:** _Send a location;_
* **SendContact:** _Send a contact with Phone and First Name;_
* **GetUserProfilePhotos:** _Get the user profile pictures;_
* **GetChat:** _Get information about specific chat;_
* **GetChatAdmin:** _Get information about all chat administrators;_
* **GetChatMemberCount:** _Get number of chat members;_
* **GetChatMember:** _GetInformation about a specific user;_
* **LeaveChat:** _Leave the current chat;_
* **KickChatMember:** _Kick an user from a Group chat;_
* **UnbanChatMember:** _Unban an user previously kicked from a Group chat;_
* **AnswerInlineQuery:** _Answer to an inline query with an array of result;_
* **AnswerCallbackQuery:** _Answer to a callback generted from inline button;_
* **EditMessageText:** _Edit text of a message;_
* **EditMessageCaption:** _Edit caption of a photo;_
* **EditMessageReplyMarkup:** _Edit the keyboard of a message;_
* **GetFileID:** _Get FileID of a the file uploaded;_
* **GetFilePath:** _Get the path of a specific file, require file ID;_
* **DownloadFile:** _Download a file from the server, require file path;_
* **JSONDecode:** _Decode incoming message;_

### Changelog:
**02/04/2017** - v1.3 - • _Added: AnswerInlineQuery, AnswerCallbackQuery, EditMessageText, EditMessageCaption, EditMessageReplyMarkup, CurlSendMsg; • Now, SendMsg take only two arguments: ChatID and Text. Also, if the http request fail (maybe for illegal charaters) it automatically use CurlSendMsg to send the message and return true if success. • CurlSendMsg take a third arguments: an array that contain optional parameter like parse mode, reply markup, disable notification... • All Send* function now return true only if success, false otherwise. • JSONDecode now fully support Channel Post, Inline Query, Callback Query and return additional information about photo, video, audio, voice and document;_ 

**21/02/2017** - v1.2 - • _DownloadFile function now return file name; • JSONDecode function now can manage callback query, incoming photos and text messages in the groups;_

**29/01/2017** - v1.1 - • _Changed order of arguments in SendMsg function; • Added DisableNotification to all send media function; • Updated JSONDecode: now can distinguish from private or group chat, left or new member event and also inline query; • Added functions: GetChatAdmin, GetChatMemberCount, GetChatMember, LeaveChat, KickChatMember and UnbanChatMember; • Minor bug fixes;_

**11/01/2016** - v1.0 - _First Release._

### Legal:
**License: GPL v3.0 ©** : Feel free to use this code and adapt it to your software; just mention this page if you share your software (free or paid).  
This code is in no way affiliated with, authorized, maintained, sponsored or endorsed by Telegram or any of its affiliates or subsidiaries. This is independent and unofficial. Use at your own risk.

### About:
If you want to donate for support my (future) works, use this: https://www.paypal.me/LCirillo  
I'll appreciate. Also, names of those who donated will be written in an **'Awesome list'** (if you agree).

For support, just contact me! Enjoy 🎉
