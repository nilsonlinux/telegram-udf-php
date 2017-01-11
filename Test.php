<?php

require('Telegram.php');
$ChatID = 'Your_Chat_ID';

print("Test _InitBot \t->\t" . _InitBot('Your_Bot_Token') . "\r\n");

print("Test _GetUpdates \t->\t" . _GetUpdates() . "\r\n");
print("Test _GetMe \t->\t" . _GetMe() . "\r\n");

print("Test _SendMsg \t->\t" . _SendMsg($ChatID,"Test _SendMsg") . "\r\n");
print("Test _ForwardMsg \t->\t" . _ForwardMsg($ChatID,$ChatID,"Your_Msg_ID") . "\r\n");

print("Test _SendPhoto \t->\t" . _SendPhoto($ChatID,"image.png") . "\r\n");
print("Test _SendAudio \t->\t" . _SendAudio($ChatID,"audio.mp3") . "\r\n");
print("Test _SendVideo \t->\t" . _SendVideo($ChatID,"video.mp4") . "\r\n");
print("Test _SendDocument \t->\t" . _SendDocument($ChatID,"document.txt") . "\r\n");
print("Test _SendVoice \t->\t" . _SendVoice($ChatID,"voice.ogg") . "\r\n");
print("Test _SendSticker \t->\t" . _SendSticker($ChatID,"sticker.webp") . "\r\n");
print("Test _SendLocation \t->\t" . _SendLocation($ChatID,"74.808889","-42.275391") . "\r\n");
print("Test _SendContact \t->\t" . _SendContact($ChatID,"0123456789","Josh") . "\r\n");
print("Test _SendChatAction \t->\t" . _SendChatAction($ChatID,"typing") . "\r\n");

print("Test _GetUserProfilePhotos \t->\t"); print_r(_GetUserProfilePhotos($ChatID)); print("\r\n");
print("Test _GetChat \t->\t" . _GetChat($ChatID) . "\r\n");

while (true){	
	$msgData = _Polling();
	_SendMsg($msgData['chat_id'],$msgData['text']);
}
?>