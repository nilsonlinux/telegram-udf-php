<?php

/* ===============================================================================
   UDF:
	  Author		->	Luca aka LinkOut
	  Description	->	Control Telegram Bot with PHP
	  Language		->	English
	  Status		->	Fully functional, but some functions are missing (like group function)
   Documentation:
	  Telegram API	->	https://core.telegram.org/bots/api
	  GitHub Page	->	https://github.com/xLinkOut/telegram-udf-autoit/ <<<<<<<<<<<<<<<<<<<<<<<
   Author Information:
	  GitHub	->	https://github.com/xLinkOut
	  Telegram	->	https://t.me/LinkOut
	  Instagram	->	https://instagram.com/lucacirillo.jpg
	  Email		->	mailto:luca.cirillo5@gmail.com
  =============================================================================== */

$BOT_TOKEN = '';
$API_URL = 'https://api.telegram.org/bot';
$Offset = '0';

/* ===============================================================================
   Function Name..:    	_InitBot()
   Description....:	   	Initialize your Bot with Token
   Parameter(s)...:    	$BotToken - Your Bot Token (12345678:AbCdEf...)
   Return Value(s):	   	Return True
  =============================================================================== */
function _InitBot($BotToken){
	global $BOT_TOKEN, $API_URL;
	$BOT_TOKEN = $BotToken;
	$API_URL = $API_URL . $BOT_TOKEN;
	return true;
}

/* ===============================================================================
   Function Name..:    	_Polling()
   Description....:     Wait for incoming messages from user
   Parameter(s)...:     None
   Return Value(s):		Return an array with information about messages:
						   $msgData[0] = Offset of the current update (used to 'switch' to next update)
						   $msgData[1] = Message ID of the current message
						   $msgData[2] = ChatID used to interact with the user
						   $msgData[3] = First name of the user
						   $msgData[4] = Username of the user
						   $msgData[5] = Text of the message
  =============================================================================== */
function _Polling(){
	global $Offset;
	while (true){
		sleep(1);
		$newUpdates = _GetUpdates();
		if(!strpos($newUpdates,"update_id")){continue;}
		$msgData = _JSONDecode($newUpdates);
		$Offset = $msgData['offset'] + 1;
		return $msgData;
	}
}

/* ===============================================================================
   Function Name..:    	_GetUpdates()
   Description....:     Used by _Polling() to get new messages
   Parameter(s)...:     None
   Return Value(s): 	Return string with information encoded in JSON format
  =============================================================================== */
function _GetUpdates(){
	global $Offset, $API_URL;
	return file_get_contents($API_URL . "/getUpdates?offset=" . $Offset);;
}

/* ===============================================================================
   Function Name..:    	_GetMe()
   Description....:     Get information about the bot (like name, @botname...)
   Parameter(s)...:     None
   Return Value(s):		Return string with information encoded in JSON format
  =============================================================================== */
function _GetMe(){
	global $API_URL;
	return file_get_contents($API_URL . "/getMe");
}

/* ===============================================================================
   Function Name..:		_SendMsg()
   Description....:     Send simple text message
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Text: Text of the message
						$ParseMode: Markdown/HTML (optional)- https://core.telegram.org/bots/api#sendmessage
						$KeyboardMarkup: Custom Keyboards (optional) - https://core.telegram.org/bots/api#replykeyboardmarkup
						$ResizeKeyboard: True/False (optional) - Requests clients to resize the keyboard vertically for optimal fit
						$OneTimeKeyboard: True/False (optional) - Requests clients to hide the keyboard as soon as it's been used
						$DisableWebPreview: True/False (optional) - Disables link previews for links in this message
						$DisableNotification: True/False (optional) - Send message silently
   Return Value(s):  	Return True (to debug, uncomment 'Return $Response')
  =============================================================================== */
function _SendMsg($ChatID,$Text,$ParseMode = '',$KeyboardMarkup = 'default',$ResizeKeyboard = false, $OneTimeKeyboard = false, $DisableWebPreview = false, $DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendMessage?chat_id=" . $ChatID . "&text=" . $Text;
	if($ParseMode == "Markdown"){$query &= "&parse_mode=markdown";}
	if($ParseMode == "HTML"){$query &= "&parse_mode=html";}
	if($KeyboardMarkup != 'default'){$query &= "&reply_markup=" . $KeyboardMarkup;}
	if($ResizeKeyboard == true){$query &= "&resize_keyboard=True";}
    if($OneTimeKeyboard == true){$query &= "&one_time_keyboard=True";}
    if($DisableWebPreview == true){$query &= "&disable_web_page_preview=True";}
    if($DisableNotification == true){$query &= "&disable_notification=True";}
	$response = file_get_contents($query);
	return true;
}

/* ===============================================================================
   Function Name..:		_ForwardMsg()
   Description....:     Forward message from a chat to another
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$OriginalChatID: Unique identifier for the chat where the original message was sent
						$MsgID: Message identifier in the chat specified in from_chat_id
   Return Value(s):  	Return True (to debug, uncomment 'return $response')
  =============================================================================== */
function _ForwardMsg($ChatID, $OriginalChatID, $MsgID){
	global $API_URL;
	$query = $API_URL . "/forwardMessage?chat_id=" . $ChatID . "&from_chat_id=" . $OriginalChatID . "&message_id=" . $MsgID;
	$response = file_get_contents($query);
	return true;
}

/* ===============================================================================
   Function Name..:		_SendPhoto()
   Description....:     Send a photo
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with photo (optional)
   Return Value(s):  	Return File ID of the photo as string
  =============================================================================== */
function _SendPhoto($ChatID,$Path,$Caption = ''){
	global $API_URL;
	$query = $API_URL . "/sendPhoto?chat_id=" . $ChatID;
	$post_fields = array('chat_id' => $ChatID,'photo' => new CURLFile(realpath($Path)),'caption' => $Caption);
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	return _GetFileID($output,'photo');
}

/* ===============================================================================
   Function Name..:		_SendAudio()
   Description....:     Send an audio
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with audio (optional)
   Return Value(s):  	Return File ID of the audio as string
  =============================================================================== */
function _SendAudio($ChatID,$Path,$Caption = ''){
	global $API_URL;
	$query = $API_URL . "/sendAudio?chat_id=" . $ChatID;
	$post_fields = array('chat_id' => $ChatID,'audio' => new CURLFile(realpath($Path)),'caption' => $Caption);
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	return _GetFileID($output,'audio');	
}

/* ===============================================================================
   Function Name..:		_SendVideo()
   Description....:     Send a video
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with video (optional)
   Return Value(s):  	Return File ID of the video as string
  =============================================================================== */
function _SendVideo($ChatID,$Path,$Caption = ''){
	global $API_URL;
	$query = $API_URL . "/sendVideo?chat_id=" . $ChatID;
	$post_fields = array('chat_id' => $ChatID,'video' => new CURLFile(realpath($Path)),'caption' => $Caption);
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	return _GetFileID($output,'video');
}

/* ===============================================================================
   Function Name..:		_SendDocument()
   Description....:     Send a document
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with document (optional)
   Return Value(s):  	Return File ID of the video as string
  =============================================================================== */
function _SendDocument($ChatID,$Path,$Caption = ''){
	global $API_URL;
	$query = $API_URL . "/sendDocument?chat_id=" . $ChatID;
	$post_fields = array('chat_id' => $ChatID,'document' => new CURLFile(realpath($Path)),'caption' => $Caption);
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	return _GetFileID($output,'document');
}

/* ===============================================================================
   Function Name..:		_SendVoice()
   Description....:     Send a voice file
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file (format: .ogg)
						$Caption: Caption to send with voice (optional)
   Return Value(s):  	Return File ID of the video as string
  =============================================================================== */
function _SendVoice($ChatID,$Path,$Caption = ''){
	global $API_URL;
	$query = $API_URL . "/sendVoice?chat_id=" . $ChatID;
	$post_fields = array('chat_id' => $ChatID,'voice' => new CURLFile(realpath($Path)),'caption' => $Caption);
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	return _GetFileID($output,'voice');
}

/* ===============================================================================
   Function Name..:		_SendSticker()
   Description....:     Send a sticker
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file (format: .webp)
   Return Value(s):  	Return File ID of the video as string
  =============================================================================== */
function _SendSticker($ChatID,$Path){
	global $API_URL;
	$query = $API_URL . "/sendSticker?chat_id=" . $ChatID;
	$post_fields = array('chat_id' => $ChatID,'sticker' => new CURLFile(realpath($Path)));
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	return _GetFileID($output,'sticker');
}

/* ===============================================================================
   Function Name..:		_SendChatAction()
   Description....:     Display 'chat action' on specific chat (like Typing...)
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Action: Type of the action, can be: 'typing','upload_photo','upload_video','upload_audio',upload_document','find_location'
   Return Value(s):  	Return True (to debug uncomment 'Return $Response')
  =============================================================================== */
function _SendChatAction($ChatID,$Action){
	global $API_URL;
	$query = $API_URL . "/sendChatAction?chat_id=" . $ChatID . "&action=" . $Action;
	$response = file_get_contents($query);
	return true;
}

/* ===============================================================================
   Function Name..:		_SendLocation()
   Description....:     Send a location
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Latitude: Latitute of location
						$Longitude: Longitude of location
   Return Value(s):  	Return True (to debug, uncomment 'Return $Response')
  =============================================================================== */
function _SendLocation($ChatID,$Latitude,$Longitude){
	global $API_URL;
	$query = $API_URL . "/sendLocation?chat_id=" . $ChatID . "&latitude=" . $Latitude . "&longitude=" . $Longitude;
	$response = file_get_contents($query);
	return true;
}

/* ===============================================================================
   Function Name..:		_SendContact()
   Description....:     Send contact
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Phone: Phone number of the contact
						$Name: Name of the contact
   Return Value(s):  	Return True (to debug, uncomment 'Return $Response')
  =============================================================================== */
function _SendContact($ChatID,$Phone,$Name){
	global $API_URL;
	$query = $API_URL . "/sendContact?chat_id=" . $ChatID . "&phone_number=" . $Phone . "&first_name=" . $Name;
	$response = file_get_contents($query);
	return true;
}

/* ===============================================================================
   Function Name..:		_GetUserProfilePhotos()
   Description....:     Get all the profile pictures of an user
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Offset (optional): offset to use if you want to get a specific photo
   Return Value(s):  	Return an array with count and fileIDs of the photos
							$photos_file_id[0] = Integer, photo's count
							$photos_file_id[1,2...] = FileID of the profile picture (use _DownloadFile to download file)
  =============================================================================== */
function _GetUserProfilePhotos($ChatID,$offs = ''){
	global $API_URL;
	$query = $API_URL . "/getUserProfilePhotos?user_id=" . $ChatID;
	if($offs != ''){$query = $query . "&offset=" . $offs;}
	$response = file_get_contents($query);
	$json = json_decode($response);
	$photos_file_id = array();
	$photos_file_id[0] = $json->result->total_count;
	for($i=0;$i<$json->result->total_count;$i++){
		$photos_file_id[$i+1] = $json->result->photos[$i][2]->file_id;
	}
	return $photos_file_id;
}

/* ===============================================================================
   Function Name..:		_GetChat()
   Description....:     Get basic information about chat, like username of the user, id of the user
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
   Return Value(s):  	Return string with information encoded in JSON format
  =============================================================================== */
function _GetChat($ChatID){
	global $API_URL;
	$query = $API_URL . "/getChat?chat_id=" . $ChatID;
	return file_get_contents($query);
}

/* ===============================================================================
   Function Name..:		_GetFilePath()
   Description....:     Get path of a specific file (specified by FileID) on Telegram Server
   Parameter(s)...:     $FileID: Unique identifie for the file
   Return Value(s):  	Return FilePath as String
  =============================================================================== */
function _GetFilePath($FileID){
	global $API_URL;
	$query = $API_URL . "/getFile?file_id=" . $FileID;
	$response = file_get_contents($query);
	$json = json_decode($response);
	return $json->result->file_path;
}

/* ===============================================================================
   Function Name..:		_GetFileID()
   Description....:     Get file ID of the last uploaded file
   Parameter(s)...:     $Output: Response from HTTP Request
   Return Value(s):  	Return FileID as String
  =============================================================================== */
function _GetFileID($output,$type){
	$outputArray = json_decode($output);	
	if($type == 'photo'){return $outputArray->result->photo['2']->file_id;}
	if($type == 'video'){return $outputArray->result->video->file_id;}
	if($type == 'audio'){return $outputArray->result->audio->file_id;}
	if($type == 'document'){return $outputArray->result->document->file_id;}
	if($type == 'voice'){return $outputArray->result->voice->file_id;}
	if($type == 'sticker'){return $outputArray->result->sticker->file_id;}
}

/* ===============================================================================
   Function Name..:		_DownloadFile()
   Description....:     Download and save locally a file from the Telegram Server by FilePath
   Parameter(s)...:     $FilePath: Path of the file on Telegram Server
   Return Value(s):  	Return True
  =============================================================================== */
function _DownloadFile($FilePath){
	global $BOT_TOKEN;
	$query = "https://api.telegram.org/file/bot" . $BOT_TOKEN . "/" . $FilePath;
	$tmp = explode('/',$FilePath);
	$fname = $tmp[1];
	file_put_contents($fname, file_get_contents($query));
	return true;
}

/* ===============================================================================
   Function Name..:		_JSONDecode()
   Description....:     Decode response from JSON format to array with information
   Parameter(s)...:     JSON Response from HTTP request
   Return Value(s):  	Return array with information about message
  =============================================================================== */
function _JSONDecode($newUpdates){
	$update = json_decode($newUpdates, true); 
	$message = $update['result']['0'];
		$update_id = $message['update_id'];
		$data = $message['message'];
			$message_id = $data['message_id'];
			$from = $data['from'];
				$chat_id = $from['id'];
				$first_name = $from['first_name'];
				$username = $from['username'];
			$text = $data['text'];
	$msgData = array(
		"offset" => $update_id,
		"message_id" => $message_id,
		"chat_id" => $chat_id,
		"first_name" => $first_name,
		"username" => $username,
		"text" => $text,
	);
	return $msgData;
};
?>