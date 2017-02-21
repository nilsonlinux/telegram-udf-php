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

This library doesn't use WebHook, but GetUpdates method: you can host your script anywhere, on any remote or local server because you don't need SSL Certificate (required instead to use WebHook method).
To wait for incoming messages you have to put the bot in Polling State, as this

```php
While 1(true){ //Create a While to restart Polling after processing a message
	$msgData = _Polling() //_Polling function return an array with info about message
	_SendMsg($msgData['chat_id'],$msgData['text']); //Send a message to the same user with the same text
}
```

The array returned by _Polling function contain:
* $msgData[0] = Offset of the current update (used to 'switch' to next update)
* $msgData[1] = Message ID of the current message
* $msgData[2] = ChatID used to interact with the user
* $msgData[3] = First name of the user
* $msgData[4] = Username of the user
* $msgData[5] = Text of the message

## Functions:
* **_InitBot:** _Initialize bot (require BotID and BotTOKEN);_
* **_Polling:** _Wait for incoming messages;_
* **_GetUpdates:** _Get new messages from Telegram Server (Return a string);_
* **_GetMe:** _Get information about the bot (Return a string);_
* **_SendMsg:** _Send simple text message (support Markdown/HTML, Keyboard ecc...)(Return True);_
* **_ForwardMsg:** _Forward a message from a chat to another(Return True);_
* **_SendPhoto:** _Send a photo to a specific chat (Return file ID);_
* **_SendVideo:** _Send a video to a specific chat (Return file ID);_
* **_SendAudio:** _Send an audio to a specific chat (Return file ID);_
* **_SendDocument:** _Send a document to a specific chat (Return file ID);_
* **_SendVoice:** _Send a voice to a specific chat (Return file ID);_
* **_SendSticker:** _Send a sticker to a specific chat (Return file ID);_
* **_SendChatAction:** _Set the 'Chat Action' for 5 seconds (Typing, Sending photo...)(Return True);_
* **_SendLocation:** _Send a location (Return True);_
* **_SendContact:** _Send a contact with Phone and First Name (Return True);_
* **_GetUserProfilePhotos:** _Get the user profile pictures (Return an Array with the FileID of each photo);_
* **_GetChat:** _Get information about specific chat (Return a string with info);_
* **_GetChatAdmin:** _Get information about all chat administrators (Return an array);_
* **_GetChatMemberCount:** _Get number of chat members (Return an integer);_
* **_GetChatMember:** _GetInformation about a specific user (Return an array);_
* **_LeaveChat:** _Leave the current chat (Return true if success, false otherwise);_
* **_KickChatMember:** _Kick an user from a Group chat (Return true if success, false otherwise);_
* **_UnbanChatMember:** _Unban an user previously kicked from a Group chat (Return true if success, false otherwise);_
* **_GetFileID:** _Get FileID of a the file uploaded(Return a string);_
* **_GetFilePath:** _Get the path of a specific file, require file ID (Return a string);_
* **_DownloadFile:** _Download a file from the server, require file path (Return True);_
* **_JSONDecode:** _Decode incoming message (Return an array with some information like Chat ID ecc);_

### Changelog:
**21/02/2017** - v1.2 - • _DownloadFile function now return file name; • JSONDecode function now can manage callback query, incoming photos and text messages in the groups;

**29/01/2017** - v1.1 - • _Changed order of arguments in SendMsg function; • Added DisableNotification to all send media function; • Updated JSONDecode: now can distinguish from private or group chat, left or new member event and also inline query; • Added functions: GetChatAdmin, GetChatMemberCount, GetChatMember, LeaveChat, KickChatMember and UnbanChatMember; • Minor bug fixes;_

**11/01/2016** - v1.0 - _First Release._

### Legal:
**License: GPL v3.0 ©** : Feel free to use this code and adapt it to your software; just mention this page if you share your software (free or paid).  
This code is in no way affiliated with, authorized, maintained, sponsored or endorsed by Telegram or any of its affiliates or subsidiaries. This is independent and unofficial. Use at your own risk.

### About:
If you want to donate for support my (future) works, use this: https://www.paypal.me/LCirillo  
I'll appreciate. Also, names of those who donated will be written in an **'Awesome list'** (if you agree).

For support, just contact me! Enjoy 🎉
