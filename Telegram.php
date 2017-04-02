<?php

/* ===============================================================================
   UDF:
	  Author		->	Luca aka LinkOut
	  Description	->	Control Telegram Bot with PHP
	  Language		->	English
	  Status		->	Fully functional.
   Documentation:
	  Telegram API	->	https://core.telegram.org/bots/api
	  GitHub Page	->	https://github.com/xLinkOut/telegram-udf-php
  =============================================================================== */

$BOT_TOKEN 	= '';
$API_URL 	= 'https://api.telegram.org/bot';
$Offset 	= '0';

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
   Return Value(s):		Return an array with some informations about messages
  =============================================================================== */
function _Polling(){
	global $Offset;
	while (true){
		// sleep(1); 
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
   Description....:     Send simple text message without any other parameters
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Text: Text of the message
   Return Value(s):  	If success return Message ID, else return false
  =============================================================================== */
function _SendMsg($ChatID,$Text){
	global $API_URL;
	$query = $API_URL . "/sendMessage?chat_id=" . $ChatID . "&text=" . $Text;
	$response = file_get_contents($query);
	$response = json_decode($response);
	if(!($response->ok == 1))
		return _CurlSendMsg($ChatID,$Text);
	elseif($response->ok == 1)
		return $response->result->message_id;
	else
		return false;
}

/* ===============================================================================
   Function Name..:		_CurlSendMsg()
   Description....:     Send simple text message when HTTP method failed
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Text: Text of the message
						$Param: array with optional parameters
							reply_markup: https://core.telegram.org/bots/api#replykeyboardmarkup
							parse_mode: markdown/html https://core.telegram.org/bots/api#sendmessage
							resize_keyboard: true/false requests clients to resize the keyboard vertically for optimal fit
							one_time_keyboard: true/false requests clients to hide the keyboard as soon as it's been used
							disable_web_page_preview: true/false disables link previews for links in this message
							disable_notification: true/false send message silently
   Return Value(s):  	If success return Message ID, else return false
  =============================================================================== */
function _CurlSendMsg($ChatID, $Text, $Param = array()){
	global $API_URL;
	$query = $API_URL . "/sendMessage";
	$post_fields = array('chat_id' => $ChatID,'text' => $Text);
	
	if(array_key_exists('reply_markup',$Param)) $post_fields['reply_markup'] = $Param['reply_markup'];
	if(array_key_exists('parse_mode',$Param)) $post_fields['parse_mode'] = $Param['parse_mode'];
	if(array_key_exists('resize_keyboard',$Param)) $post_fields['resize_keyboard'] = $Param['resize_keyboard'];
	if(array_key_exists('disable_notification',$Param)) $post_fields['disable_notification'] = $Param['disable_notification'];
	if(array_key_exists('one_time_keyboard',$Param)) $post_fields['one_time_keyboard'] = $Param['one_time_keyboard'];
	if(array_key_exists('disable_web_page_preview',$Param)) $post_fields['disable_web_page_preview'] = $Param['disable_web_page_preview'];
	
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	$output = json_decode($output);
	if(!($output->ok == 1))
		return false;
	else
		return $output->result->message_id;
}
/* ===============================================================================
   Function Name..:		_ForwardMsg()
   Description....:     Forward message from a chat to another
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$OriginalChatID: Unique identifier for the chat where the original message was sent
						$MsgID: Message identifier in the chat specified in from_chat_id
   Return Value(s):  	If success return true, else false
  =============================================================================== */
function _ForwardMsg($ChatID, $OriginalChatID, $MsgID){
	global $API_URL;
	$query = $API_URL . "/forwardMessage?chat_id=" . $ChatID . "&from_chat_id=" . $OriginalChatID . "&message_id=" . $MsgID;
	$response = file_get_contents($query);
	$response = json_decode($response);
	if($response->ok == 1)
		return true;
	else
		return false;
}

/* ===============================================================================
   Function Name..:		_SendPhoto()
   Description....:     Send a photo
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with photo (optional)
						$DisableNotification: True to send silently message
						$ReplyMarkup: Send custom keyboard markup (See documentation)
						$ReplyTo: If it's a reply, this must be the message id of the original message
   Return Value(s):  	If success return the File ID, else false
  =============================================================================== */
function _SendPhoto($ChatID,$Path,$Caption = '',$DisableNotification = false,$ReplyMarkup = '',$ReplyTo = ''){
	global $API_URL;
	$query = $API_URL . "/sendPhoto";
	$post_fields = array('chat_id' => $ChatID,'photo' => new CURLFile(realpath($Path)));
	if($Caption != '') $post_fields['caption'] = $Caption;
	if($DisableNotification == true) $post_fields['disable_notification'] = true;
	if($ReplyMarkup != '') $post_fields['reply_markup'] = $ReplyMarkup;
	if($ReplyTo != '') $post_fields['reply_to_message_id'] = $ReplyTo;
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response != '' and $response->ok == 1)
		return _GetFileID($output,'photo');
	else
		// return "Failed to send {$Path} to {$ChatID}";
		return false;
}

/* ===============================================================================
   Function Name..:		_SendVideo()
   Description....:     Send a video
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with video (optional)
						$DisableNotification: True to send silently message
						$ReplyMarkup: Send custom keyboard markup (See documentation)
						$ReplyTo: If it's a reply, this must be the message id of the original message
   Return Value(s):  	If success return the File ID, else false
  =============================================================================== */
function _SendVideo($ChatID,$Path,$Caption = '',$DisableNotification = false,$ReplyMarkup = '',$ReplyTo = ''){
	global $API_URL;
	$query = $API_URL . "/sendVideo";
	$post_fields = array('chat_id' => $ChatID,'video' => new CURLFile(realpath($Path)));
	if($Caption != '') $post_fields['caption'] = $Caption;
	if($DisableNotification == true) $post_fields['disable_notification'] = true;
	if($ReplyMarkup != '') $post_fields['reply_markup'] = $ReplyMarkup;
	if($ReplyTo != '') $post_fields['reply_to_message_id'] = $ReplyTo;
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response != '' and $response->ok == 1)
		return _GetFileID($output,'video');	
	else
		// return "Failed to send {$Path} to {$ChatID}";
		return false;
}

/* ===============================================================================
   Function Name..:		_SendAudio()
   Description....:     Send an audio
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with audio (optional)
						$DisableNotification: True to send silently message
						$ReplyMarkup: Send custom keyboard markup (See documentation)
						$ReplyTo: If it's a reply, this must be the message id of the original message
   Return Value(s):  	If success return the File ID, else false
  =============================================================================== */
function _SendAudio($ChatID,$Path,$Caption = '',$DisableNotification = false,$ReplyMarkup = '',$ReplyTo = ''){
	global $API_URL;
	$query = $API_URL . "/sendAudio";
	$post_fields = array('chat_id' => $ChatID,'audio' => new CURLFile(realpath($Path)));
	if($Caption != '') $post_fields['caption'] = $Caption;
	if($DisableNotification == true) $post_fields['disable_notification'] = true;
	if($ReplyMarkup != '') $post_fields['reply_markup'] = $ReplyMarkup;
	if($ReplyTo != '') $post_fields['reply_to_message_id'] = $ReplyTo;
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response != '' and $response->ok == 1)
		return _GetFileID($output,'audio');	
	else
		// return "Failed to send {$Path} to {$ChatID}";
		return false;
}

/* ===============================================================================
   Function Name..:		_SendDocument()
   Description....:     Send a document
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file
						$Caption: Caption to send with document (optional)
						$DisableNotification: True to send silently message
						$ReplyMarkup: Send custom keyboard markup (See documentation)
						$ReplyTo: If it's a reply, this must be the message id of the original message
   Return Value(s):  	If success return the File ID, else false
  =============================================================================== */
function _SendDocument($ChatID,$Path,$Caption = '',$DisableNotification = false,$ReplyMarkup = '',$ReplyTo = ''){
	global $API_URL;
	$query = $API_URL . "/sendDocument";
	$post_fields = array('chat_id' => $ChatID,'document' => new CURLFile(realpath($Path)));
	if($Caption != '') $post_fields['caption'] = $Caption;
	if($DisableNotification == true) $post_fields['disable_notification'] = true;
	if($ReplyMarkup != '') $post_fields['reply_markup'] = $ReplyMarkup;
	if($ReplyTo != '') $post_fields['reply_to_message_id'] = $ReplyTo;
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response != '' and $response->ok == 1)
		return _GetFileID($output,'document');	
	else
		// return "Failed to send {$Path} to {$ChatID}";
		return false;
}

/* ===============================================================================
   Function Name..:		_SendVoice()
   Description....:     Send a voice file
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file (format: .ogg)
						$Caption: Caption to send with voice (optional)
						$DisableNotification: True to send silently message
						$ReplyMarkup: Send custom keyboard markup (See documentation)
						$ReplyTo: If it's a reply, this must be the message id of the original message
   Return Value(s):  	If success return the File ID, else false
  =============================================================================== */
function _SendVoice($ChatID,$Path,$Caption = '',$DisableNotification = false,$ReplyMarkup = '',$ReplyTo = ''){
	global $API_URL;
	$query = $API_URL . "/sendVoice";
	$post_fields = array('chat_id' => $ChatID,'voice' => new CURLFile(realpath($Path)));
	if($Caption != '') $post_fields['caption'] = $Caption;
	if($DisableNotification == true) $post_fields['disable_notification'] = true;
	if($ReplyMarkup != '') $post_fields['reply_markup'] = $ReplyMarkup;
	if($ReplyTo != '') $post_fields['reply_to_message_id'] = $ReplyTo;
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response != '' and $response->ok == 1)
		return _GetFileID($output,'voice');	
	else
		// return "Failed to send {$Path} to {$ChatID}";
		return false;
}

/* ===============================================================================
   Function Name..:		_SendSticker()
   Description....:     Send a sticker
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Path: Path to local file (format: .webp)
						$DisableNotification: True to send silently message
						$ReplyMarkup: Send custom keyboard markup (See documentation)
						$ReplyTo: If it's a reply, this must be the message id of the original message
   Return Value(s):  	If success return the File ID, else false
  =============================================================================== */
function _SendSticker($ChatID,$Path,$DisableNotification = false,$ReplyMarkup = '',$ReplyTo = ''){
	global $API_URL;
	$query = $API_URL . "/sendSticker";
	$post_fields = array('chat_id' => $ChatID,'sticker' => new CURLFile(realpath($Path)));
	if($Caption != '') $post_fields['caption'] = $Caption;
	if($DisableNotification == true) $post_fields['disable_notification'] = true;
	if($ReplyMarkup != '') $post_fields['reply_markup'] = $ReplyMarkup;
	if($ReplyTo != '') $post_fields['reply_to_message_id'] = $ReplyTo;
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response != '' and $response->ok == 1)
		return _GetFileID($output,'sticker');	
	else
		// return "Failed to send {$Path} to {$ChatID}";
		return false;
}

/* ===============================================================================
   Function Name..:		_SendChatAction()
   Description....:     Display 'chat action' on specific chat (like Typing...)
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Action: Type of the action, can be: 
						'typing', 'upload_photo', 'upload_video', 'upload_audio', 'upload_document', 'find_location'
   Return Value(s):  	If success return true, else false
  =============================================================================== */
function _SendChatAction($ChatID,$Action){
	global $API_URL;
	$query = $API_URL . "/sendChatAction?chat_id=" . $ChatID . "&action=" . $Action;
	$response = file_get_contents($query);
	$response = json_decode($response);
	if($response->ok == 1)
		return true;
	else
		return false;
}

/* ===============================================================================
   Function Name..:		_SendLocation()
   Description....:     Send a location
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Latitude: Latitute of location
						$Longitude: Longitude of location
   Return Value(s):  	If success return true, else false
  =============================================================================== */
function _SendLocation($ChatID,$Latitude,$Longitude,$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendLocation?chat_id=" . $ChatID . "&latitude=" . $Latitude . "&longitude=" . $Longitude;
	if($DisableNotification == true){$query &= "&disable_notification=True";}
	$response = file_get_contents($query);
	$response = json_decode($response);
	if($response->ok == 1)
		return true;
	else
		return false;
}

/* ===============================================================================
   Function Name..:		_SendContact()
   Description....:     Send contact
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$Phone: Phone number of the contact
						$Name: Name of the contact
   Return Value(s):  	If success return true, else false
  =============================================================================== */
function _SendContact($ChatID,$Phone,$Name,$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendContact?chat_id=" . $ChatID . "&phone_number=" . $Phone . "&first_name=" . $Name;
	if($DisableNotification == true){$query &= "&disable_notification=True";}
	$response = file_get_contents($query);
	$response = json_decode($response);
	if($response->ok == 1)
		return true;
	else
		return false;
}

/* ===============================================================================
   Function Name..:		_GetUserProfilePhotos()
   Description....:     Get all the profile pictures of an user
   Parameter(s)...:     $UserID: Unique identifier for the target chat
						$Offset (optional): offset to use if you want to get a specific photo
   Return Value(s):  	Return an array with count and fileIDs of the photos
							$photos_file_id[0] = Integer, photo's count
							$photos_file_id[1,2...] = FileID of the profile picture (use _DownloadFile to download file)
  =============================================================================== */
function _GetUserProfilePhotos($UserID,$offs = ''){
	global $API_URL;
	$query = $API_URL . "/getUserProfilePhotos?user_id=" . $UserID;
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
   Function Name..:		_GetChatAdmins()
   Description....:     Get information about chat administrator
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
   Return Value(s):  	Return array with ID, First name and Username of all chat admin
  =============================================================================== */
function _GetChatAdmins($ChatID){
	global $API_URL;
	$query = $API_URL. "/getChatAdministrators?chat_id=" . $ChatID;
	$json = file_get_contents($query);
	$response = json_decode($json);
	$update = $response->result;
	$counter = 0;
	$msgData = array();
	while(isset($update[$counter]->user)){
		$user = array('id' => $update[$counter]->user->id,
					  'first_name' => $update[$counter]->user->first_name,
					  'username' => $update[$counter]->user->username);
		array_push($msgData,$user);
		$counter++;
	}
	return $msgData;
}

/* ===============================================================================
   Function Name..:		_GetChatMembersCount()
   Description....:     Get number of chat members
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
   Return Value(s):  	Return an integer
  =============================================================================== */
function _GetChatMembersCount($ChatID){
	global $API_URL;
	$query = $API_URL. "/getChatMembersCount?chat_id=" . $ChatID;
	$json = file_get_contents($query);
	$response = json_decode($json);
	$count = $response->result;
	return $count;
}

/* ===============================================================================
   Function Name..:		_GetChatMember()
   Description....:     Get information about a specific user
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
						$UserID: Unique identifier for the target user
   Return Value(s):  	Return an array with ID,First name and Username
  =============================================================================== */
function _GetChatMember($ChatID, $UserID){
	global $API_URL;
	$query = $API_URL . "/getChatMember?chat_id=" . $ChatID . "&user_id=" . $UserID;
	$json = file_get_contents($query);
	$response = json_decode($json);
	$user = $response->result->user;
	$msgData = array('id' => $user->id,
					 'first_name' => $user->first_name,
					  'username' => $user->username);
	return($msgData);
}

/* ===============================================================================
   Function Name..:		_LeaveChat()
   Description....:     Leave the current chat
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
   Return Value(s):  	Return true if success, false otherwise
  =============================================================================== */
function _LeaveChat($ChatID){
	global $API_URL;
	$query = $API_URL . "/leaveChat?chat_id=" . $ChatID;
	$json = file_get_contents($query);
	$response = json_decode($json);
	if($response->ok == 1){
		return true;
	}else{
		return false;
	}
}

/* ===============================================================================
   Function Name..:		_KickChatMember()
   Description....:     Kick an user from a Group chat. Bot need to be Admin
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
   						$UserID: Unique identifier for the target user
   Return Value(s):  	Return true if success, false otherwise
  =============================================================================== */
function _KickChatMember($ChatID,$UserID){
	global $API_URL;
	$query = $API_URL . "/kickChatMember?chat_id=" . $ChatID . "&user_id=" . $UserID;;
	$json = file_get_contents($query);
	$response = json_decode($json);
	if($response->ok == 1){
		return true;
	}else{
		return false;
	}
}

/* ===============================================================================
   Function Name..:		_UnbanChatMember()
   Description....:     Unban an user previously kicked from a Group chat. Bot need to be Admin
   Parameter(s)...:     $ChatID: Unique identifier for the target chat
   						$UserID: Unique identifier for the target user
   Return Value(s):  	Return true if success, false otherwise
  =============================================================================== */
function _UnbanChatMember($ChatID,$UserID){
	global $API_URL;
	$query = $API_URL . "/unbanChatMember?chat_id=" . $ChatID . "&user_id=" . $UserID;;
	$json = file_get_contents($query);
	$response = json_decode($json);
	if($response->ok == 1){
		return true;
	}else{
		return false;
	}
}

/* ===============================================================================
   Function Name..:		_AnswerInlineQuery()
   Description....:     Answer to an inline query
   Parameter(s)...:     $QueryID: Unique identifier for the current query
   						$Results: Array of answer to send (see documentation)
						$CacheTime: Time, in seconds, to cache the results (default 300)
   Return Value(s):  	Return true
  =============================================================================== */
function _AnswerInlineQuery($QueryID,$Results,$CacheTime = '300'){
	global $API_URL;
	$query = $API_URL . "/answerInlineQuery";
	$post_fields = array('inline_query_id' => $QueryID,'results' => $Results,'cache_time' => $CacheTime); 
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	$output = curl_exec($hCurl);
	print($output);
}
/* ===============================================================================
   Function Name..:		_AnswerCallbackQuery()
   Description....:     Answer to a callback generated from inline button
   Parameter(s)...:     $QueryID: Unique identifier for the current query
   						$Response: Array of answer to send (see documentation)
						$CacheTime: Time, in seconds, to cache the results (default 300)
   Return Value(s):  	Return true
  =============================================================================== */
function _AnswerCallbackQuery($CallbackID,$Text){
	global $API_URL;
	$query = $API_URL . "/answerCallbackQuery";
	$post_fields = array('callback_query_id' => $CallbackID,'text' => $Text); 
	$hCurl = curl_init(); 
	curl_setopt($hCurl, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
	curl_setopt($hCurl, CURLOPT_URL, $query); 
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields); 
	curl_exec($hCurl);
	return true;
}

// EXAMPLE OF INLINE KEYBOARD BUTTON
// $postButton = array("inline_keyboard" => array(array(array("text" => "👥 {$linecount}", "callback_data" => "c-{$postCode}"), array("text" => "Partecipar", "callback_data" => "p-{$postCode}")), array(array("text" => "Compartir", "url" => "https://t.me/{$botUsername}?start={$postCode}"))));
// $postButton = json_encode($postButton, true);

/* ===============================================================================
   Function Name..:		_EditMessageText()
   Description....:     Edit text of a message
   Parameter(s)...:     $ChatID: Unique identifier for the current chat
   						$MessageID: Unique identifier for the message to edit
						$Text: New text to send
   Return Value(s):  	Return true if success, else false
  =============================================================================== */
function _EditMessageText($ChatID,$MessageID,$Text){
	global $API_URL;
	$URL = $API_URL . "/editMessageText";
	$post_fields = array('chat_id' => $ChatID, 'message_id' => $MessageID, 'text' => $Text);
	$hCurl = curl_init();
	curl_setopt($hCurl, CURLOPT_URL, $URL);
	curl_setopt($hCurl, CURLOPT_POST, 1);
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields);
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response->ok == 1){
		return true;
	}else{
		return false;
	}
}

/* ===============================================================================
   Function Name..:		_EditMessageCaption()
   Description....:     Edit caption of a photo
   Parameter(s)...:     $ChatID: Unique identifier for the current chat
   						$MessageID: Unique identifier for the message to edit
						$Text: New text to send
   Return Value(s):  	Return true if success, else false
  =============================================================================== */
function _EditMessageCaption($ChatID,$MessageID,$Caption){
	global $API_URL;
	$URL = $API_URL . "/editMessageCaption";
	$post_fields = array('chat_id' => $ChatID, 'message_id' => $MessageID, 'caption' => $Caption);
	$hCurl = curl_init();
	curl_setopt($hCurl, CURLOPT_URL, $URL);
	curl_setopt($hCurl, CURLOPT_POST, 1);
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields);
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response->ok == 1){
		return true;
	}else{
		return false;
	}
}

/* ===============================================================================
   Function Name..:		_EditMessageReplyMarkup()
   Description....:     Edit the keyboard of a message
   Parameter(s)...:     $ChatID: Unique identifier for the current chat
   						$MessageID: Unique identifier for the message to edit
						$Text: New text to send
   Return Value(s):  	Return true if success, else false
  =============================================================================== */
function _EditMessageReplyMarkup($ChatID,$MessageID,$Markup){
	global $API_URL;
	$URL = $API_URL . "/editMessageReplyMarkup";
	$post_fields = array('chat_id' => $ChatID, 'message_id' => $MessageID, 'reply_markup' => $Markup);
	$hCurl = curl_init();
	curl_setopt($hCurl, CURLOPT_URL, $URL);
	curl_setopt($hCurl, CURLOPT_POST, 1);
	curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($hCurl, CURLOPT_POSTFIELDS, $post_fields);
	$output = curl_exec($hCurl);
	$response = json_decode($output);
	if($response->ok == 1){
		return true;
	}else{
		return false;
	}
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
   Function Name..:		_DownloadFile()
   Description....:     Download and save locally a file from the Telegram Server by FilePath
   Parameter(s)...:     $FilePath: Path of the file on Telegram Server
   Return Value(s):  	Return filename
  =============================================================================== */
function _DownloadFile($FilePath){
	global $BOT_TOKEN;
	$query = "https://api.telegram.org/file/bot" . $BOT_TOKEN . "/" . $FilePath;
	$tmp = explode('/',$FilePath);
	$fname = $tmp[1];
	file_put_contents($fname, file_get_contents($query));
	return $fname;
}

/* ===============================================================================
   Function Name..:		_JSONDecode()
   Description....:     Decode response from JSON format to array with information
   Parameter(s)...:     JSON Response from HTTP request
   Return Value(s):  	Return array with information about message
  =============================================================================== */
function _JSONDecode($newUpdates){
	$update = json_decode($newUpdates, true);
	print_r($update);
	if(isset($update['result']['0']['channel_post'])){ //Incoming post from channel where bot is admin
		$offset = $update['result']['0']['update_id'];
		
		$data = $update['result']['0']['channel_post'];
			$message_id = $data['message_id'];
			$chat_id    = $data ['chat']['id'];
			$chat_title = $data['chat']['title'];
			$chat_username = $data['chat']['username'];
			
			if(isset($data['text']))
				$text = $data['text'];
			
			$msgData = array('type' => 'channel_post',
							 'offset' => $offset,
							 'message_id' => $message_id,
							 'chat_id' => $chat_id,
							 'chat_title' => $chat_title,
							 'chat_username' => $chat_username);
			if(isset($text))
				$msgData['text'] = $text;
			
			return $msgData;
			
	}elseif(isset($update['result']['0']['callback_query'])){ //Incoming callback query from inline keyboard
		$data = $update['result']['0'];
			$offset = $data['update_id'];
			$callback_query = $data['callback_query'];
			$callback_id = $callback_query['id'];
					$user_id    = $callback_query['from']['id'];
					$first_name = $callback_query['from']['first_name'];
					$username   = $callback_query['from']['username'];
				$message = $callback_query['message'];
					$message_id = $message['message_id'];
					$chat_id    = $message['chat']['id'];
					$chat_name  = $message['chat']['title'];
					$chat_username  = $message['chat']['username'];
					if(isset($message['text']))
						$text  = $message['text'];
				$chat_instance = $callback_query['chat_instance'];
				$callback_data = $callback_query['data'];
				
		$msgData = array('type'          => 'inline_keyboard_callback',
						 'offset'        => $offset,
						 'callback_id'   => $callback_id,
						 'callback_data' => $callback_data,
						 'user_id'       => $user_id,
						 'first_name'    => $first_name,
						 'username'      => $username,
						 'message_id'    => $message_id,
						 'chat_id'       => $chat_id,
						 'chat_name'     => $chat_name,
						 'chat_username' => $chat_username,
						 'text' 	     => $text,
						 'chat_instance' => $chat_instance); 
		return $msgData;
	}elseif(isset($update['result']['0']['inline_query'])){ //Incoming inline query from an inline command
		$message = $update['result']['0'];
		$update_id = $message['update_id'];
		$data = $message['inline_query'];
			$query_id = $data['id'];
			$from = $data['from'];
				$first_name = $from['first_name'];
				$username   = $from['username'];
			$text = $data['query'];
		$msgData = array(
			"type"       => "inline",
			"offset"     => $update_id,
			"query_id"   => $query_id,
			"first_name" => $first_name,
			"username"   => $username,
			"text"       => $text,
		);
		return $msgData;
	}elseif(($update['result']['0']['message']['chat']['type'] == 'group') or ($update['result']['0']['message']['chat']['type'] == 'supergroup')){ //Incoming message from group (include left and new member)
		$message = $update['result']['0'];
		$update_id = $message['update_id'];
		$data = $message['message'];
			$message_id = $data['message_id'];
			$from = $data['from'];
				$chat_id    = $from['id'];
				$first_name = $from['first_name'];
				$username   = $from['username'];
			$chat = $data['chat'];
				$groupid   = $chat['id'];
				$groupname = $chat['title'];
			if(array_key_exists('left_chat_member',$data)){ //Left Chat Member Event
				$Event = "left";
				$MemberID = $data['left_chat_member']['id'];
				$MemberFirstName = $data['left_chat_member']['first_name'];
				$MemberUsername = $data['left_chat_member']['username'];
			}elseif(array_key_exists('new_chat_member',$data)){ //New Chat Member Event
				$Event = "new";
				$MemberID = $data['new_chat_member']['id'];
				$MemberFirstName = $data['new_chat_member']['first_name'];
				$MemberUsername  = $data['new_chat_member']['username'];
			}else{
				$text = $data['text'];
			}
			
		$msgData = array(
			"type" 	 	 => "group",
			"offset" 	 => $update_id,
			"message_id" => $message_id,
			"chat_id" 	 => $chat_id,
			"first_name" => $first_name,
			"username" 	 => $username,
			"group_id" 	 => $groupid,
			"group_name" => $groupname,			
		);
		
		if(isset($MemberID)){
			$msgData['event'] = $Event;
			$msgData['member_id'] = $MemberID;
			$msgData['member_first_name'] = $MemberFirstName;
			$msgData['member_username'] = $MemberUsername;
		}else{
			$msgData['text'] = $text;
		}
		return $msgData;
	}elseif($update['result']['0']['message']['chat']['type'] == 'private'){
		//Incoming message from a private chat
		$message = $update['result']['0'];
		$data = $message['message'];

		$msgData = array(
			"type"       => "private",
			"offset"     => $message['update_id'],
			"message_id" => $data['message_id'],
			"chat_id"    => $data['from']['id'],
			"username"   => $data['from']['username'],
			"first_name" => $data['from']['first_name'],
		);
		
		if(array_key_exists('text',$data))
			$msgData['text'] = $data['text'];
			
		if(array_key_exists('photo',$data)){
			$counter = count($data['photo']) - 1;
			$photo   = $data['photo'][$counter];
			$msgData['photo_id']     = $photo['file_id'];
			$msgData['photo_size']   = $photo['file_size']; 
			$msgData['photo_width']  = $photo['width'];
			$msgData['photo_height'] = $photo['height'];
		}
		
		if(array_key_exists('video',$data)){
			$video = $data['video'];
			$msgData['video_id'] 		   = $video['file_id'];
			$msgData['video_size'] 		   = $video['file_size'];
			$msgData['video_duration'] 	   = $video['duration'];
			$msgData['video_width'] 	   = $video['width'];
			$msgData['video_height'] 	   = $video['height'];
			$msgData['video_thumb_id'] 	   = $video['thumb']['file_id'];
			$msgData['video_thumb_size']   = $video['thumb']['file_size'];
			$msgData['video_thumb_width']  = $video['thumb']['width'];
			$msgData['video_thumb_height'] = $video['thumb']['height'];
		}
		
		if(array_key_exists('audio',$data)){
			$audio = $data['audio'];
			$msgData['audio_id'] 		= $audio['file_id'];
			$msgData['audio_name']		= $audio['title'];
			$msgData['audio_performer'] = $audio['performer'];
			$msgData['audio_size'] 		= $audio['file_size'];
			$msgData['audio_duration'] 	= $audio['duration'];
			$msgData['audio_mime'] 		= $audio['mime_type'];
		}
		
		if(array_key_exists('document',$data)){
			$document = $data['document'];
			$msgData['document_id']   = $document['file_id'];
			$msgData['document_size'] = $document['file_size'];
			$msgData['document_name'] = $document['file_name'];
			$msgData['document_mime'] = $document['mime_type'];
		}
		
		if(array_key_exists('voice',$data)){
			$voice = $data['voice'];
			$msgData['voice_id'] 	   = $voice['file_id'];
			$msgData['voice_size'] 	   = $voice['file_size'];
			$msgData['voice_mime']     = $voice['mime_type'];
			$msgData['voice_duration'] = $voice['duration'];
		}
		
		if(array_key_exists('caption',$data))
			$msgData['caption'] = $data['caption'];
			
		if(array_key_exists('reply_to_message',$data)){
			$reply = $data['reply_to_message'];
			$msgData['reply'] 		  = 'yes';
			$msgData['from_chat_id']  = $reply['from']['id'];
			$msgData['from_username'] = $reply['from']['username'];
			$msgData['from_first_name'] = $reply['from']['first_name'];
			$msgData['original_chat_id'] 	  = $reply['chat']['id'];
			$msgData['original_username'] 	  = $reply['chat']['username'];
			$msgData['original_first_name']    = $reply['chat']['first_name'];
			$msgData['original_message_id']	= $reply['message_id'];
			$msgData['original_text'] = $reply['text'];
		}
			
		if(array_key_exists('forward_from',$data)){
			$msgData['forward'] = 'yes';
			$msgData['forward_id'] = $data['forward_from']['id'];
			$msgData['forward_first_name'] = $data['forward_from']['first_name'];
			$msgData['forward_username'] = $data['forward_from']['username'];
			$msgData['forward_text'] = $data['text'];
		}
		
		return $msgData;
	}	
}
?>