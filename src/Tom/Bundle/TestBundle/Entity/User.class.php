<?php
	class User {
		private $mId;
		private $mCompanyId;
		private $mEmail;
		private $mPassword;
		private $mUserLevel;
		private $mFirstname;
		private $mLastname;
		private $mDatabase;
		private $mEncryptKey;
		
		/*===MD5_SALT===*/
		/*
		Use an encryption key to make passwords more secure. 
		DO NOT CHANGE THIS!!! If change this all old passwords in the database will be lost, no one will be able to login and you will have a shitty day!
		*/
		const MD5_SALT = "#!3nCrYpt3d!#";

		
		function __construct($core)
		{
			//Set this object to connect the virtual database
			$this->mDatabase = $core->mDb;

			if ($this->isLoggedIn()) 
			{
				$id = $this->getUserIdFromSession();
			
				$sql = "SELECT * FROM users WHERE id='$id'"; 		
				$stmt = $this->mDatabase->prepare($sql);
				$stmt->execute();

				$User = $stmt->fetch(PDO::FETCH_OBJ);
				
				$this->mId = $User->id;
				$this->mCompanyId = $User->companyId;
				$this->mEmail = $User->email;
				$this->mPassword = $User->password;
				$this->mUserLevel = $User->userlevel;
				$this->mFirstname = $User->firstname;
				$this->mLastname = $User->lastname;
			}
		}
		public function displayGeneralSettings()
		{
			echo '
			<nav class="settings settingsEditor">
				<ul>
					<li class="settingsLi">
						<a id="nameSettings" class="settingsLink">
							<span class="settingsHeading">Name</span><span class="info">'.$this->getFullname().'</span><span class="edit">Edit</span>
						</a>
						<div class="content"></div>
					</li>
					<li class="settingsLi">
						<a id="usernameSettings" class="settingsLink">
							<span class="settingsHeading">Username</span><span class="info">'.$this->mUsername.'</span><span class="edit">Edit</span>
						</a>
						<div class="content"></div>

					</li>
					<li class="settingsLi">
						<a id="emailSettings" class="settingsLink">
							<span class="settingsHeading">Email</span><span class="info">'.$this->mEmail.'</span><span class="edit">Edit</span>
						</a>
						<div class="content"></div>
					</li>
					<li class="settingsLi">
						<a id="passwordSettings" class="settingsLink">
							<span class="settingsHeading">Password</span><span class="info"><i>Hidden</i></span><span class="edit">Edit</span>
						</a>
						<div class="content"></div>
					</li>					
				</ul>
			</nav>';
		}
		public function checkPassword($password)
		{
			if ($this->convertToMd5($password) == $this->mPassword)
				return true;
			return false;
		}
		public function isUser($id)
		{
			$sql = "SELECT id FROM users WHERE id=:id"; 
			$stmt = $this->mDatabase->prepare($sql);
			$stmt->execute(array(':id'=>$id));
			if ($stmt->rowCount() > 0)
				return true;
			return false;
		}
		public function getUserIdFromSession()
		{

			if (isset($_SESSION["User"]))
			{
				return $_SESSION["User"];
			}
			else if (isset($_COOKIE["User"]))
			{
				$cookie = explode('&hash=', $_COOKIE['User']);
				list ($userId, $password) = $cookie;

				if($this->isUser($userId))
				{
					$sql = "SELECT password FROM users WHERE id=:id"; 
					$result = $this->mDatabase->prepare($sql);
					$result->execute(array(':id'=>$userId));
					$User = $result->fetch(PDO::FETCH_OBJ);

					if ($User->password == $password)
						return $userId;
				}
			}
		}
		public function isLoggedIn()
		{
			if ($this->isUser($this->getUserIdFromSession()))
				return true;
			return false;
		}
		public function auth()
		{
			//Check if user is logged in
			if ($_SERVER['PHP_SELF'] == '/publisher/login.php')
			{
				//If on the login page and logged in - go to index
				if ($this->isLoggedIn())
					header('Location: /publisher/index.php');
			}
			else
			{
				//If on any other page and not logged in - go to the login page
				if (!$this->isLoggedIn())
					header('Location: /publisher/login.php');
			}
		}
		public function logIn($email, $password, $remember) {
			$sql = "SELECT * FROM users WHERE email = :email"; 		
			$result = $this->mDatabase->prepare($sql);
			$result->execute(array(':email'=>$email));

			$User = $result->fetch(PDO::FETCH_OBJ);
			
			if ($User)
			{
				if ($User->password == $this->convertToMd5($password))
				{
					$this->mId = $User->id;
					$this->mCompanyId = $User->companyId;
					$this->mEmail = $User->email;
					$this->mPassword = $User->password;
					$this->mUserLevel = $User->userlevel;
					$this->mFirstname = $User->firstname;
					$this->mLastname = $User->lastname;
					$_SESSION["User"] = $this->mId;	
					$cookie = $this->mId.'&hash='.$this->convertToMd5($password);			
					
					if ($remember)
						setcookie("User", $cookie, time()+60*60*24*30);  //Expires in 1 month
					
					return true;	
				}
			}
			return false;
		}
		public function convertToMd5($string)
		{
			return md5($string . self::MD5_SALT);
		}
		public function updateName($firstname_, $lastname_)
		{
			$this->mDatabase->setAttribute(PDO::ATTR_EMULATE_PREPARES,false); 
			$sql = "UPDATE users SET firstname = :firstname, lastname = :lastname WHERE id = :user_id";
			$stmt = $this->mDatabase->prepare($sql);
			
			$stmt->bindParam(":firstname", $firstname_);
			$stmt->bindParam(":lastname", $lastname_);
			$stmt->bindParam(":user_id", $this->mId);
			$stmt->execute();	
			if (!$stmt) { 
				echo "\nPDO::errorInfo():\n"; 
				print_r($this->mDatabase->errorInfo()); 
			}
			else
			{
				$this->mFirstname = $firstname_;
				$this->mLastname = $lastname_;
				echo $this->getFullname();
			}

		}
		public function updateUsername($username_)
		{
			$this->mDatabase->setAttribute(PDO::ATTR_EMULATE_PREPARES,false); 
			$sql = "UPDATE users SET username = :username WHERE id = :user_id";
			$stmt = $this->mDatabase->prepare($sql);
			
			$stmt->bindParam(":username", $username_);
			$stmt->bindParam(":user_id", $this->mId);
			$stmt->execute();	
			if (!$stmt) { 
				echo "\nPDO::errorInfo():\n"; 
				print_r($this->mDatabase->errorInfo()); 
			}
			else
			{
				$this->mUsername = $username_;
				echo $this->mUsername;
			}

		}
		public function updateEmail($email_)
		{
			$this->mDatabase->setAttribute(PDO::ATTR_EMULATE_PREPARES,false); 
			$sql = "UPDATE users SET email = :email WHERE id = :user_id";
			$stmt = $this->mDatabase->prepare($sql);
			
			$stmt->bindParam(":email", $email_);
			$stmt->bindParam(":user_id", $this->mId);
			$stmt->execute();	
			if (!$stmt) { 
				echo "\nPDO::errorInfo():\n"; 
				print_r($this->mDatabase->errorInfo()); 
			}
			else
			{
				$this->mEmail = $email_;
				echo $this->mEmail;
			}
		}
		public function updatePassword($password_)
		{
			$this->mDatabase->setAttribute(PDO::ATTR_EMULATE_PREPARES,false); 
			$sql = "UPDATE users SET password = :password WHERE id = :user_id";
			$stmt = $this->mDatabase->prepare($sql);
			
			$stmt->bindParam(":password", $password_);
			$stmt->bindParam(":user_id", $this->mId);
			$stmt->execute();	
			if (!$stmt) { 
				echo "\nPDO::errorInfo():\n"; 
				print_r($this->mDatabase->errorInfo()); 
			}
			else
			{
				echo '<i>Hidden</i>';
			}
		}
		public function destroySession()
		{	
			$past = time()-(60*60*24);
			if (isset($_COOKIE['User']))
				setcookie("User", " ", $past, "/");  //Expires in 1 month //Use MD5 to make secure*/
			session_destroy();
			header('Location: /publisher/login.php');
		}
		public function getId()
		{
			return $this->mId;	
		}
		public function getPassword()
		{
			return $this->mPassword;
		}
		public function getFirstname() 
		{
			return $this->mFirstname;	
		}
		public function getLastname() 
		{
			return $this->mLastname;	
		}
		public function getFullname() 
		{
			return $this->mFirstname . ' ' . $this->mLastname;	
		}
		public function getUsername()
		{
			return $this->mUsername;	
		}
		public function getEmail()
		{
			return $this->mEmail;
		}
		public function getCompanyId()
		{
			return $this->mCompanyId;	
		}
		private function encrypt($string)
		{
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$block = mcrypt_get_block_size('des', 'ecb');
			$pad = $block - (strlen($string) % $block);
			$string .= str_repeat(chr($pad), $pad);
			return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->mEncryptKey, $string, MCRYPT_MODE_ECB, $iv);
		}		
		private function decrypt($string)
		{   
			$string = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->mEncryptKey, $string, MCRYPT_MODE_ECB);
		
			$block = mcrypt_get_block_size('des', 'ecb');
			$pad = ord($string[($len = strlen($string)) - 1]);
			return substr($string, 0, strlen($string) - $pad);
		}
	}
?>