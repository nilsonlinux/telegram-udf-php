<?php

/* ===============================================================================
   UDF:
	  Author		->	Luca aka LinkOut
	  Description	->	Control Telegram Bot with PHP
	  Language		->	English
	  Status		->	Fully functional, missing some json deconding (like incoming video, document...)
   Documentation:
	  Telegram API	->	https://core.telegram.org/bots/api
	  GitHub Page	->	https://github.com/xLinkOut/telegram-udf-php
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
function _SendMsg($ChatID,$Text,$KeyboardMarkup = 'default',$DisableNotification = false,$ParseMode = '',$ResizeKeyboard = false, $OneTimeKeyboard = false, $DisableWebPreview = false){
	global $API_URL;
	$query = $API_URL . "/sendMessage?chat_id=" . $ChatID . "&text=" . $Text;
	if($DisableNotification == true){$query &= "&disable_notification=True";}
	if($ParseMode == "Markdown"){$query &= "&parse_mode=markdown";}
	if($ParseMode == "HTML"){$query &= "&parse_mode=html";}
	if($KeyboardMarkup != 'default'){$query &= "&reply_markup=" . $KeyboardMarkup;}
	if($ResizeKeyboard == true){$query &= "&resize_keyboard=True";}
    if($OneTimeKeyboard == true){$query &= "&one_time_keyboard=True";}
    if($DisableWebPreview == true){$query &= "&disable_web_page_preview=True";}
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
function _SendPhoto($ChatID,$Path,$Caption = '',$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendPhoto";
	$post_fields = array('chat_id' => $ChatID,'photo' => new CURLFile(realpath($Path)),'caption' => $Caption,'disable_notification' => $DisableNotification);
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
function _SendAudio($ChatID,$Path,$Caption = '',$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendAudio";
	$post_fields = array('chat_id' => $ChatID,'audio' => new CURLFile(realpath($Path)),'caption' => $Caption,'disable_notification' => $DisableNotification);
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
function _SendVideo($ChatID,$Path,$Caption = '',$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendVideo";
	$post_fields = array('chat_id' => $ChatID,'video' => new CURLFile(realpath($Path)),'caption' => $Caption,'disable_notification' => $DisableNotification);
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
function _SendDocument($ChatID,$Path,$Caption = '',$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendDocument";
	$post_fields = array('chat_id' => $ChatID,'document' => new CURLFile(realpath($Path)),'caption' => $Caption,'disable_notification' => $DisableNotification);
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
function _SendVoice($ChatID,$Path,$Caption = '',$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendVoice";
	$post_fields = array('chat_id' => $ChatID,'voice' => new CURLFile(realpath($Path)),'caption' => $Caption,'disable_notification' => $DisableNotification);
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
function _SendSticker($ChatID,$Path,$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendSticker";
	$post_fields = array('chat_id' => $ChatID,'sticker' => new CURLFile(realpath($Path)),'disable_notification' => $DisableNotification);
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
function _SendLocation($ChatID,$Latitude,$Longitude,$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendLocation?chat_id=" . $ChatID . "&latitude=" . $Latitude . "&longitude=" . $Longitude;
	if($DisableNotification == true){$query &= "&disable_notification=True";}
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
function _SendContact($ChatID,$Phone,$Name,$DisableNotification = false){
	global $API_URL;
	$query = $API_URL . "/sendContact?chat_id=" . $ChatID . "&phone_number=" . $Phone . "&first_name=" . $Name;
	if($DisableNotification == true){$query &= "&disable_notification=True";}
	$response = file_get_contents($query);
	return true;
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
	if($response->result == 1){
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
	if($response->result == 1){
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
	if($response->result == 1){
		return true;
	}else{
		return false;
	}
}

/* ===============================================================================
   Function Name..:		_SendInlineQuery()
   Description....:     POC of inline query answer, not work yes
   Parameter(s)...:     $QueryID: Unique identifier for the current query
   						$Response: Array of answer to send
   Return Value(s):  	Return true if success, false otherwise
  =============================================================================== */
// function _SendInlineQuery($queryid,$response){
	// global $API_URL;
	// $query = $API_URL . "/answerInlineQuery?inline_query_id=" . $queryid . "&results=" . json_encode($response);
	// $hCurl = curl_init(); 
	// curl_setopt($hCurl, CURLOPT_URL, $query); 
	// curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, 1); 
	// $output = curl_exec($hCurl);
// }
  

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
	//print_r($update);
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
	}elseif($update['result']['0']['message']['chat']['type'] == 'private'){//Incoming message from a private chat
		$message = $update['result']['0'];
		$update_id = $message['update_id'];
		$data = $message['message'];
			$message_id = $data['message_id'];
			$from = $data['from'];
				$chat_id = $from['id'];
				$first_name = $from['first_name'];
				$username = $from['username'];
			if(array_key_exists('text',$data))
				$text = $data['text'];
			if(array_key_exists('reply_to_message',$data)){
				$reply = $data['reply_to_message'];
					$originalMsgID     = $data['reply_to_message']['message_id'];
					$originalUserID    = $data['reply_to_message']['from']['id'];
					$originalFirstName = $data['reply_to_message']['from']['first_name'];
					$originalUsername  = $data['reply_to_message']['from']['username'];
					$originalChatID    = $data['reply_to_message']['chat']['id'];
					$originalChatTitle = $data['reply_to_message']['from']['title'];
					$originalMsgText   = $data['reply_to_message']['text'];
			}
			if(isset($data['forward_from'])){
				$forwardID		  = $data['forward_from']['id'];
				$forwardFirstName = $data['forward_from']['first_name'];
				$forwardUsername  = $data['forward_from']['username'];
			}
			if(array_key_exists('photo',$data)){
				$counter  = count($data['photo']) - 1;
				$photo_id = $data['photo'][$counter]['file_id'];
			}
			if(array_key_exists('caption',$data))
				$caption = $data['caption'];
			
		$msgData = array(
			"type"       => "private",
			"offset"     => $update_id,
			"message_id" => $message_id,
			"chat_id"    => $chat_id,
			"first_name" => $first_name,
			"username"   => $username,
		);
		
		if(isset($text))
			$msgData['text'] = $text;
		
		if(isset($originalMsgID)){
			$msgData['reply'] = 'yes';
			$msgData['original_message_id'] = $originalMsgID;
			$msgData['original_user_id']    = $originalUserID;
			$msgData['original_first_name'] = $originalFirstName;
			$msgData['original_username']   = $originalUsername;
			$msgData['original_chat_id']    = $originalChatID;
			$msgData['original_chat_title'] = $originalChatTitle;
			$msgData['original_text']       = $originalMsgText;
		}
		
		if(isset($forwardFromID)){
			$msgData['forward'] = 'yes';
			$msgData['forward_id'] = $forwardID;
			$msgData['forward_first_name'] = $forwardFirstName;
			$msgData['forward_username'] = $forwardUsername;
		}
		
		if(isset($photo_id)){
			$msgData['photo_id'] = $photo_id;
		}		
		if(isset($caption))
			$msgData['caption'] = $caption;
		
		return $msgData;
	}
}
?>