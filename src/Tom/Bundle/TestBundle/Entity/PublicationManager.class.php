<?php
	include_once('Publication.class.php');
	include_once('Magazine.class.php');
	include_once('MessageManager.class.php');
	
	class PublicationManager {
		private $mCore;
		private $mDatabase;
		private $mId;
		private $mPublications;
		private $mCurrentMagazine;
		private $mUser;
	
		public function __construct($core)
		{
			//Set this object to connect the virtual database
			$this->mCore = $core;
			$this->mDatabase = $core->mDb;
			//Create User
			$this->mUser = new User($core);

			$sql = "SELECT a.id, a.name
			FROM `publications` a
			INNER JOIN `publications_x_users` b 
			ON a.id = b.publicationId
			INNER JOIN `users` c
			ON b.userId = c.id
			WHERE c.id = :id";

			//$sql = "SELECT * FROM publications";  
			$stmt = $this->mDatabase->prepare($sql);
			$stmt->execute(array(':id'=>$this->mUser->getId()));
			
			$i = 0;
			while ($Publication = $stmt->fetch(PDO::FETCH_OBJ))
			{
				$this->mPublications[$i] = new Publication($core, $Publication->id);
				$i++;
			}
						
			if (!empty($_SESSION['magazineId']))
				$this->mCurrentMagazine = $_SESSION['magazineId'];

		}
		public function displayPublications()
		{
			echo '<h2 class="sub-header">Publications</h2>';
			echo '<div class="row">';
			$i = 0;
			
			if (!empty($this->mPublications))
			{
				foreach ($this->mPublications as $Pub)
				{
	  				echo '<div class="publication col-sm-6 col-md-4">
	    				<div class="thumbnail">
	      					<img src="'.$Pub->getLogo().'" width="300" alt="Power Electronics World" />
	     					<div class="caption">
	        					<h3>'.$Pub->getName().'</h3>
	        					<p>
									<a href="/publisher/publication/'.$Pub->getId().'/" class="btn btn-primary" role="button">View Issues</a>
									<a href="#" class="btn btn-success" role="button">Settings</a>
								</p>
	      					</div>
	    				</div>
					</div>';
					if ($i == 2)
						echo '
						</div>
						<div class="row">
						';
					$i++;
				}
			}
			else
				MessageManager::displayMessage('No Publications Found.', 1);
			echo '</div>';
		}
		public function displayDropdownMenu()
		{
			//If user has publications linked display them in a dropdown menu
			if (!empty($this->mPublications))
			{
				echo '<div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    	Publications <span class="caret"></span>
                    </button>
	                <ul class="dropdown-menu" role="menu">
					';
					foreach ($this->mPublications as $Pub)
		            	echo '	<li><a href="/publisher/publication/'.$Pub->getId().'/">'.$Pub->getName().'</a></li>';
		            echo '
					</ul>  
				</div>
				';
			}
		}
		public function displaySideMenu()
		{
			echo '
			<ul class="nav nav-sidebar">
			';
            	echo  '<li><a href="/publisher/">Dashboard</a></li>
              	<li class="divider"></li>';

              	//If user has publications linked display them
				if (!empty($this->mPublications))
				{
					echo '<li class="heading">Publications</li>';

					foreach ($this->mPublications as $Pub)
               			echo '<li><a href="/publisher/publication/'.$Pub->getId().'/">'.$Pub->getName().'</a></li>';
               		echo '<li class="divider"></li>';  

               	}
               	//If the user is editing a particular magazine display a quick link
				if (!empty($this->mCurrentMagazine))
				{
					$Magazine = new Magazine($this->mCore, $this->mCurrentMagazine);
					if (is_numeric($Magazine->getName()))
						$name = 'Issue '.$Magazine->getName();
					else
						$name = $Magazine->getName();
					echo '<li class="heading">'.$name.'</li>';
					echo '<li><a href="/publisher/issue/'.$this->mCurrentMagazine.'/">Pages</a></li>';
				}
           	echo '
		   	</ul>
			';	
		}
		public function auth($id)
		{
			
			if (is_object($this->getPublication($id)))
			{
				return true;
			}
			else if (is_object($this->getMagazine($id)))
			{
				return true;
			}
			else
			{
				echo '<h2>You are not authorised to visit this page.</h2>
				<p>If you think this an error please contact the administrator.</p>';
			}
			return false;
		}
		public function getPublication($id)
		{
			foreach ($this->mPublications as $Pub)
			{
				if ($Pub->getId() == $id)
					return $Pub;	
			}
		}
		public function getMagazine($id)
		{
			foreach ($this->mPublications as $Pub)
			{
				$Magazine = $Pub->getMagazine($id);
				if (is_object($Magazine))
					return $Magazine;
			}
		}
		public function getLogo()
		{
			return '<img src="/publisher/img/logo-publishingninja.png" class="img-responsive" />';
		}
		public function getName()
		{
			return '<img src="/publisher/img/icon-ninja.png" /> Publishing Ninja';	
		}
		public function getUser()
		{
			return $this->mUser;
		}
		public function displayMessage($message, $type = '0')
		{
			if ($type == 9)
			{
				echo '<div class="alert alert-info">';
					echo '<pre>';
					print_r($message);
					echo '</pre>';
				echo '</div>';
			}
			else if ($type == 1)
			{
				echo '<div class="alert alert-info">';
				echo $message;
				echo '</div>';
			}
			else if ($type == 2)
			{
				echo '<div class="alert alert-danger">';
				echo 'Error: '.$message;
				echo '</div>';
			}
			else 
			{
				echo '<div class="alert alert-success">';
				echo $message;
				echo '</div>';
			}
		}
		private function cleanUpURL($url)
		{
			$url = htmlentities($url, ENT_QUOTES);
			$url = str_ireplace(" ", "-", trim($url));
			$url = str_ireplace("%", "", $url); 
			$url = str_ireplace("?", "", $url);
			$url = str_ireplace('/', '', $url);
			$url = str_ireplace('&amp;', '-and-', $url);
			$url = preg_replace('/[\W]+/', '-', $url);
			$url = trim($url, "-");
			$url = strtolower($url);
			return $url;	
		}
	}
?>