<?php
/**
 * Sendinblue mail helper
 * @package sendinblue
 * @author Sandip Roy
 * Author URL: https://binaryitlab.com
 * @version 1.0.1
 */

namespace SendinBlueHelper\Mail;
class Mail
{	

	/**
	* API Key V3
	* Get your api key from sendinblue dashboard
	* Required
	*/
	public $api_key = 'YOUR_API_KEY';
	
	/**
	* Sendinblue API URL
	* Default set to smtp transactional email
	* See details: https://developers.sendinblue.com/reference
	*/
	public $url = 'https://api.sendinblue.com/v3/smtp/email';

	/**
	* CURL Request Method - POST/GET
	* @param $string e.g "POST"
	* Default: POST
	*/
	public $method = "POST";

	/**
	* Sender Email, name optional
	* @param e.g. array('email' => 'admin@domain.com')
	* Mandatory if template id is not set
	* Not Required if Template ID is set, sender will be set from template
	*/
	public $sender = array('name' => 'Sender Name', "email" => 'sender@domain.com');

	/**
	* Email Subject
	* @param string e.g. 'Confirmation Mail'
	* Mandatory if template id is not set
	* Not Required if Template ID is set, subject will be set from template
	*/
	public $subject;

	/**
	* Email body in  HTML format
	* @param string e.g. '<b>Hello World</b>'
	* Mandatory is template is not set
	* Not Required if template id is set
	*/
	public $htmlContent;

	/**
	* Plain Text body of the email
	* @param  string 'Hello World'
	* Mandatory if 'templateId' is not passed, ignored if 'templateId' is passed
	*/
	public $textContent;

	/**
	* All the recepients array. 
	* @param array of arrays e.g array(['name' => 'Joe', 'email' => 'joe@domain.com'], ['email' => 'admin@domain.com'])
	* Required Field
	*/
	public $to = array(); 

	/**
	* All the recepients array
	* @param array of arrays
	* Same format as $to
	* Optional
	**/
	public $bcc = array();
	public $cc = array();



	/**
	* Reply To email
	* @param associative array e.g. array('name' => 'Admin', 'email' => 'support@email.com')
	* Same format as $sender
	* Optional, if not set sender email will be set by default
	*/
	public $replyTo = array();

	/**
	* Pass the absolute URL (no local file) or the base64 content of the attachment along with the attachment name 
	* @param array of arrays e.g. array(['name' => 'My Picture', 'url' => 'https://...'], 
	*									[ 'name' => 'Base64 content' 'content' => base64() ])
	* Optional
	*/
	public $attachment = array();


	/**
	* Template ID to use, get your template id from sendinblue dashboard
	* @param integer e.g. 2
	* Optional
	*/
	public $templateId;
	

	/**
	* Transactional parametes to send for template
	* @param associate array or array of arrays, according to the paramenter set in template
	* Optional
	*/
	public $params = array();

	public $data;
	public $header = array(
				    "accept: application/json",
				    "content-type: application/json"
				  );



	private $curl;
	public $isHTTPS = true;
	public $response;
	public $error;


	
	function __construct($method = 'POST'){
		//Set Request Method
		//Default POST
		$this->method = "POST";

		//Initiate CURL
		$this->curl = curl_init();

	}

		/**
	* Send Mail to the receiver
	*/
	public function send($email = null, $name = null){
		//check if receiver is set here
		if(isset($email)){
			$this->addTo($email, $name);
		}

		//Process sending mail
		$this->init();
		$this->response = curl_exec($this->curl);
		$this->error = curl_error($this->curl);
		curl_close($this->curl);
		//return result
    return $this->response;
	}



	/**
	* Initiate CURL Request
	*/
	private function init(){
			//Set API KEY
			$this->header("api-key: ".$this->api_key);
			$this->post();
			
	}

	/**
	* Validate email address
	* @param string $email
	* @return boolean
	*/
	private function isEmail($email){
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	/**
	* Build CURL Post Request
	**/
	private function post(){
		//set data if not set
		if(!isset($this->data)){
			$this->setData();
		}

			curl_setopt_array($this->curl, array(
				  CURLOPT_URL => $this->url,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_SSL_VERIFYPEER => $this->isHTTPS,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => $this->method,
				  CURLOPT_POSTFIELDS => $this->data,
				  CURLOPT_HTTPHEADER => $this->header,
			));

	  return $this->curl;
	}



	/**
	* Prepare all data for sending with curl
	*/
	private function setData(){
		$data = array();
		//Set Sender
		$this->setValue($data, 'sender', $this->sender);
		//set Receiver
		$this->setValue($data, 'to', $this->to);
		//set cc
		$this->setValue($data, 'cc', $this->cc);
		//set bcc
		$this->setValue($data, 'bcc', $this->bcc);
		//Set content
		$this->setValue($data, 'htmlContent', $this->htmlContent);
		//set plain text content
		$this->setValue($data, 'textContent', $this->textContent);
		//set subject
		$this->setValue($data, 'subject', $this->subject);
		//set reply to 
		$this->setValue($data, 'replyTo', $this->replyTo);
		//set templateid
		$this->setValue($data, 'templateId', $this->templateId);
		//set template params
		$this->setValue($data, 'params', $this->params);

		return $this->data = json_encode($data);
	}

	/**
	* Set if value exist
	*/
	private function setValue(&$data, $key, $value){
		if(!empty($value)){
			$data[$key] = $value;
		}
	}
	/**
	* Get all data
	*/
	public function getData($json_decode = false){
		return ($json_decode) ? json_decode($this->data) : $this->data;
	}

	/**
	* Read Response
	* @param boolean $decode
	* @return string/object - Will return object if $decode is set to true, else it will return json string
	*/
	public function getResponse($decode = false){
		return ($decode) ? json_decode($this->response) : $this->response;
	}

	/**
	* Read Last Sent Email ID
	* @return string/false
	*/
	public function messageID(){
		$response = $this->getResponse(true);
		return ($response && isset($response->messageId)) ? $response->messageId : false;
	}

	/**
	* Check for errors
	* @return boolean - if any erro found return true
	*					if there is no erro, return false
	*/
	public function hasError(){
		return ($this->error || $this->messageID() == false) ? true : false;
	}

	/**
	* Read Error
	* @return string - error description
	*/
	public function readError(){
		$err = array();
		//Check for CURL request erro
		if(!empty($this->error)){
			$err[] = $this->error;
		}
		//Check for server-side error
		if($this->hasError()){
			//read response
			$r = $this->getResponse(true);
			$err[] = $r->message;
		}

		return join(', ', $err);
	}


	/**
	* Add Header parameter
	**/
	public function header($param){
		$this->header[] = $param;
	}



	/**
	* Format Sender address
	*/
	private function addSender($email, $name = null){
		$s_arr = array();
		if($this->isEmail($email)){
			$s_arr['email'] = $email;
			if(isset($name)){
				$s_arr['name'] = $name;
			}
		}
		return $s_arr;
	}

	/**
	* Format Receiver address
	*/
	private function addReceiver($email, $name = null)
	{	
		$r_list = array();
		//Check if set single email
		if(!is_array($email) && $this->isEmail($email) ){
			$r = array();
			$r['email'] = $email;
			if(isset($name)){
				$r['name'] = $name;
			}
			$r_list[] = $r;
		}

		//Process multiple receiver
		if(is_array($email))
		{
			foreach ($email as $single) {
					$r = array();	
					
					//Only add email if it is valid
					if(isset($single['email']) && @$this->isEmail($single['email']) ){
						$r['email'] = $single['email'];
						//Add Name if set
						if(isset($single['name'])){
							$r['name'] = $single['name'];
						}
						//add recepient to the list
						$r_list[] = $r;
					}
				}

				
		}

			return $r_list;
			
	}

	/**
	* Add Receiver to the list
	* @param string/array e.g. 'info@email.com'
	*							array(['name' =>  'User Name', 'email' => 'user@domain.com'], ['email' => 'user2@domain.com'])
	* @return array all the receiver list
	*/
	public function addTo($email, $name = null){
		$r_list = $this->addReceiver($email, $name);
		return $this->to = array_merge($this->to, $r_list);
	}
	//Alias of addTo() method
	public function to($email, $name = null)
	{
		return $this->addTo($email, $name);
	}

	/**
	* Add CC recepients to the list
	* @param string/array e.g. 'info@email.com'
	*							array(['name' =>  'User Name', 'email' => 'user@domain.com'], ['email' => 'user2@domain.com'])
	* @return array all the receiver list
	*/
	public function addCC($email, $name = null){
		$r_list = $this->addReceiver($email, $name);
		return $this->cc = array_merge($this->cc, $r_list);
	}
	//alias of addCC
	public function cc($email, $name = null)	{
		return $this->addCC($email, $name);
	}


	/**
	* Add BCC recepients to the list
	* @param string/array e.g. 'info@email.com'
	*							array(['name' =>  'User Name', 'email' => 'user@domain.com'], ['email' => 'user2@domain.com'])
	* @return array all the receiver list
	*/
	public function addBCC($email, $name = null){
		$r_list = $this->addReceiver($email, $name);
		return $this->bcc = array_merge($this->bcc, $r_list);
	}
	//Alias of addBCC
	public function bcc($email, $name = null){
		return $this->addBCC($email, $name);
	}
	
	/**
	* Set Template ID
	* @param int $templateId
	*/
	public function template($id){
		return $this->templateId = $id;
	}

	/**
	* Add params for the template
	*/
	public function param($param, $name = null){
		$p_list = array();
		if(!is_array($param) && isset($name)){
			$p_list[$name] = $param;
		}
		if(is_array($param) && !empty($param)){
			$p_list = $param;
		}
		return $this->params = array_merge($this->params, $p_list);
	}
	//Alias of param
	public function params($param, $name=null){
		return $this->param($param, $name);
	}
	//Alias of param
	public function setParam($param, $name){
		return $this->param($param, $name);
	}


	/**
	* Add Sender
	* @param string, $email - Required
	*				 $name - Optional
	* @return array
	*/
	public function sender($email, $name = null){
		return $this->sender = $this->addSender($email, $name);
	}
	//alias of sender
	public function from($email, $name = null){
		return $this->sender($email, $name);
	}


	/**
	* Add Replyto
	* @param string, $email - Required
	*				 $name - Optional
	* @return array
	*/
	public function replyTo($email, $name = null){
		return $this->replyTo = $this->addSender($email, $name);
	}


	/**
	* Set Mail Subject
	* @param string
	*/
	public function subject($string){
		return $this->subject = $string;
	}

	/**
	* Set Mail Body
	* @param string HTML content of the mail body
	* @return string
	*/
	public function html($message){
		return $this->htmlContent = $message;
	}
	//alis of html
	public function content($message){
		return $this->html($message);
	}
	//alis of html
	public function body($message){
		return $this->html($message);
	}


	/**
	* Mail Body in plain text
	* @param string 
	*/
	public function text($message){
		$this->textContent = $message;
	}

}//End of the class
