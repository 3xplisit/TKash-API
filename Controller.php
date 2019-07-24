<?php

date_default_timezone_set('Africa/Nairobi');

/*
***
------------------------------------------------------------------------------------------------------------------
Author: Jesse Kariuki
Email:  jessy3g@gmail.com
Tel:    0700099269

Description: This File Contains all the classes to be Passed to the various endpoints during 
the testing stage. To God be the Glory.

------------
14 April 2019

- Initial testing of the API Endpoints and Documentation

-------------
24 July 2019

-
-------------------------------------------------------------------------------------------------------------------
*
***/


class TKASHController{


function __construct(){


  $this->environment            = '';//The environment can either be {preprod,uat,dev,prod}

  $this->consumer_secret        = '';//The Applications Consumer Secret
  $this->consumer_key           = '';//The Applications Consumer Key

  $this->grant_username         = '';//Grant Username is required to Authorize generation of the access Token
  $this->grant_password         = '';//Grant Password is required to Authorize generation of the access Token

  $this->paybill_user           = '';//This Account is used to authorize B2B Payments
  $this->paybill_password       = '';//This Password is encrypted with the Public Key cert to be sent as a request for B2C or Bank Transfer..

  $this->b2c_username           = ''; //Used when initiating Transactional API's (B2C, Disbursements)
  $this->b2c_password           = '';

  $this->consumer_id            = '';//Consumer ID is Sent during production.

  $this->call_baseURL           = 'https://'.$this->environment.'.gw.mfs-tkl.com/';

  $this->responseType           = strtoupper('REST'); //Depending on the system, the response can either be in XML or JSON.

  $this->acceptHeaders          = ( $this->responseType=='REST' ? 'Accept: application/json' : 'Accept: application/xml' );

  /*--------------------------------------------------------------------------------------------------*/

  $this->security_credentials = '';


}


  public function getToken(){

    (empty($this->consumer_key)     ? die ('Error. The Consumer Key has not been set') : $this->consumer_key );
    (empty($this->consumer_secret)  ? die ('Error. The Consumer Secret has not been set') : $this->consumer_secret );

    //We encrypt the key and secret now.
    
    $cs_key         = $this->consumer_key;
    $cs_secret      = $this->consumer_secret;
    $encrypted_key  = base64_encode($cs_key.':'.$cs_secret);
    
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->call_baseURL.'token?grant_type=client_credentials');
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(
    	[
    	'username'=>$this->grant_username,
    	'password'=>$this->grant_password
    ]));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array($this->acceptHeaders,'Authorization:Basic'.' '.$encrypted_key ));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,  0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  0);
    $exec    = curl_exec($curl);

    curl_close($curl);
    
    //Capture the access token and save it in a log file and pass it for any other request...
    file_put_contents('Token_Logs.log', date('Y-m-d H:i:s').' '.json_encode($exec).PHP_EOL,FILE_APPEND);

    $results =  json_decode($exec);

    if(isset($results->access_token )){

    	//Return the access token received from the request.
    	return $results->access_token;

    }else{

    	//Here an error occured and we need to capture and display it.
    	die(json_encode(['errorMessage'=>$results->error,'errorDescription'=>$results->error_description]));
    }


 }


function _initRequest($requestURL =null, $post_data,$verb = null, $responseHeader = null){

	(empty($requestURL)      ? die ('Request URL was not Set. Cannot send Request to Null URL') : $verb );
    (empty($verb)            ? die ('Please HTTP Request Verb') : $verb );
    (empty($responseHeader)  ? die ('Reponse Header is not set use either 0 for Requests with no HTTP Code or 1 for Requests with HTTP Code') : $responseHeader );


    /*-------------------------------------------------------------*/
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL,             $this->call_baseURL.$requestURL);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json',$this->acceptHeaders,'Authorization:Bearer'.' '.Self::getToken() ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,  true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $verb);
    ( $responseHeader == 100 ? curl_setopt($curl, CURLOPT_HEADER, true) : curl_setopt($curl, CURLOPT_HEADER, false));
    ( $responseHeader == 100 ? curl_setopt($curl, CURLOPT_NOBODY, true) : curl_setopt($curl, CURLOPT_NOBODY, false) );
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,  0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  0);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data ) ;

     $res = curl_exec($curl);
     //Get the headers for the request that only recieve headers.
     $headers = curl_getinfo($curl);
   	 
     curl_close($curl);

    switch($responseHeader){


    case(100):

    	switch($headers['http_code']){

    		case(200):
    		$result = json_encode(['responseMessage'=>'Success','responseDesc'=>'Request was Sucessful.']);
    		break;

    		default:
    		$result = json_encode(['errorMessage'=>'Failed','errorDescription'=>'Failed with HTTP Code '.$headers['http_code']]);
    		break;

    	}

    return $result;
    	
	break;


	case(200):

	return $res;

	break;

	default:

	return json_encode(['errorMessage'=>'Failed','errorDescription'=>'ResponseHeader '.$responseHeader.' is Uknown']);
	break;

  	   
  	}


 }


function RegisterURL($validationURL = null,$confirmationURL = null, $responseHeader = null){


	(empty($validationURL)   ? die('Please Supply a Validation URL') : $validationURL);
	(empty($confirmationURL) ? die('Please Supply a Confirmation URL') : $confirmationURL);
	(empty($responseHeader)  ? die('Please Configure Response Header') : $responseHeader);

	$urlResponseType = ['REST','SOAP'];

	if(!in_array($this->responseType, $urlResponseType)){

		//The Response From the API Requires either (SOAP or REST) to be configured.
		die(json_encode(['errorMessage'=>'Error on ResponseType','errorDescription'=>'Callback Response Type: '.$this->responseType.' is Unknown.']));

	}else{


    $jsonify = [
    		
    		'registerUrlRequest'=>
    		[

    			'consumerId'           => $this->consumer_id,
                'notificationUrl'      => $confirmationURL,
                'notificationUrlType'  => $this->responseType,
                'validationUrl'        => $validationURL,
                'validationUrlType'    => $this->responseType,
                'creationDate'         => date('d-M-y\TH:i:s')
              ]
          			
          	];


   		$post_data = json_encode($jsonify);
   		$response  = $this->_initRequest('consumer/v3/registerurl',$post_data,"POST",$responseHeader);
 
  		return json_decode($response,true);
  
  	 }


    }


  function UpdateURL($validationURL = null,$confirmationURL = null, $responseHeader = null){

	(empty($validationURL)   ? die('You need to Supply a Update Validation URL') : $validationURL);
	(empty($confirmationURL) ? die('You need to Supply a Update Confirmation URL') : $confirmationURL);
	(empty($responseHeader)  ? die('You need to Configure Response Header') : $responseHeader);

    $jsonify = [
    		
    		'updateUrlRequest'=>

    		[
    			'consumerId'           => $this->consumer_id,
                'notificationUrl'      => $confirmationURL,
                'notificationUrlType'  => $this->responseType,
                'validationUrl'        => $validationURL,
                'validationUrlType'    => $this->responseType,
                'creationDate'         => date('d-M-y\TH:i:s')
              ]
          			
          	];

     	$post_data = json_encode($jsonify,JSON_UNESCAPED_SLASHES);//Using JSON_UNESCAPED_SLASHES Rids of the error of wrong URL Formatting.
   		$response  = $this->_initRequest('update-consumer/v3/updateUrl',$post_data,"PUT",$responseHeader);

  		return $response;
  

    }


    function C2BSimulate(){

	(empty($this->consumer_id)   ? die('Consuer ID not Configured') : $this->consumer_id);
	
	$response  = $this->_initRequest('simulate/v3/c2b/'.$this->consumer_id.'','',"GET",200);

    return $response;
  

   }

   function replayNotification($notificationType =null,$limit =null){

   	(empty($notificationType)    ? die('Please Configure API notification Type For Replay Action') : $notificationType);
   	(empty($limit)               ? die('Error. Limit for Replay Notification not Set')  : $limit);
   	(empty($this->consumer_id)   ? die('Error. Consumer ID not Configured') : $this->consumer_id);


   	//Define the NotificationValues in a variable. This to ensure the correct value is used.
   	$notificationTypeValues = ['ATP','B2C','C2B','B2B'];

   	//Check whether the correct NotificationTypeValues have been set.
   	if(!in_array(strtoupper($notificationType), $notificationTypeValues)){


   		die(json_encode(['errorMessage'=>'Error on Request Channel','errorDescription'=>'Notification Type: '.$notificationType.' is Unknown.']));


   	}else{


   	$params    = http_build_query(
   		[
   			'notificationType'=> $notificationType,
   			'id'              => $this->consumer_id,
   			'limit'           => $limit
   		]
   	);

   	$response  = $this->_initRequest('notificationReplay/v3/replayNotification?'.$params,'',"GET",200);

    return $response;
    
    }


   }


}

$call = new TKASHController;

//var_dump($call->RegisterURL("","",100));
//var_dump($call->UpdateURL("","",200));
//var_dump($call->C2BSimulate());
//var_dump($call->replayNotification("",""));


