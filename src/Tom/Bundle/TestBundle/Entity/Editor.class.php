<?php
	include_once('Magazine.class.php');
	include_once('Layout.class.php');
	include_once('Section.class.php');
	include_once('Media.class.php');


	class Editor {
		private $mCore;
		private $mDatabase;
		private $mMagazine;
		private $mLayouts;
		private $mSections;
		private $mMedia;
		
		public function __construct($core_)
		{
			//Get magazineId from session
			$magazineId_ = $this->getMagazineId();
			$this->mCore = $core_;
			$this->mDatabase = $core_->mDb;
			
			$this->mMagazine = new Magazine($core_, $magazineId_);
			
			//Get Layouts from Database
			$i = 0;	
			$sql = "SELECT * FROM layouts";  
			$stmt = $this->mDatabase->prepare($sql);
			$stmt->execute();
			
			while ($Layout = $stmt->fetch(PDO::FETCH_OBJ))
			{
				//Add layouts to list				
				$this->mLayouts[$i] = new Layout($this->mDatabase, $Layout->id);
				$i++;
			}
			
			//Get Sections from Database
			$i = 0;	
			$sql = "SELECT * FROM sections WHERE magazineId = '0' OR magazineId = '$magazineId_' ORDER BY magazineId,sectionOrder ASC";  
			$stmt = $this->mDatabase->prepare($sql);
			$stmt->execute();
			
			while ($Section = $stmt->fetch(PDO::FETCH_OBJ))
			{
				//Add sections to list				
				$this->mSections[$i] = new Section($this->mDatabase, $Section->id);
				$i++;
			}
			
			//Get Media from Database
			$i = 0;	
			$sql = "SELECT * FROM media ORDER BY id ASC";  
			$stmt = $this->mDatabase->prepare($sql);
			$stmt->execute();
			
			while ($Media = $stmt->fetch(PDO::FETCH_OBJ))
			{
				//Add sections to list				
				$this->mMedia[$i] = new Media($this->mDatabase, $Media->id);
				$i++;
			}
		}
		public function init()
		{
			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				if ($_POST['action'] == 'Add')
				{
					$this->createPage();
				}
				else if ($_POST['action'] == 'Edit')
				{
					$this->savePage();
				}
				else if ($_POST['action'] == 'Delete')
				{
					$this->deletePage();
				}
				else if ($_POST['action'] == 'DisplayPages')
				{
					$this->displayPages();
				}
				else if ($_POST['action'] == 'DisplayImages')
				{
					echo $this->displayImages();
				}
				else if ($_POST['action'] == 'EditImages')
				{
					$this->editImages();
				}
				else if ($_POST['action'] == 'DisplayImageCrop')
				{
					$this->displayImageCrop();
				}
				else if ($_POST['action'] == 'CropImage')
				{
					$this->cropImage();
				}
				else if ($_POST['action'] == 'SetMediaPosition')
				{
					$this->setMediaPosition();
				}
				else if ($_POST['action'] == 'ActivateMedia')
				{
					$this->activateMedia();
				}
				else if ($_POST['action'] == 'DeactivateMedia')
				{
					$this->deactivateMedia();
				}
				//else if (!empty($_FILES) && empty($_POST['action']))
				else if ($_POST['action'] == 'UploadImages')
				{
					$this->uploadMedia($this->getPageId(), 0, 1, $this->uploadImage());
				}
				else if ($_POST['action'] == 'SaveImages')
				{
					$this->SaveImages();
				}
				else if ($_POST['action'] == 'Reorder')
				{
					$this->reorderPages($_POST['pages']);
				}
				else if ($_POST['action'] == 'Reorder')
				{
					$this->reorderPages($_POST['pages']);
				}
				else if ($_POST['action'] == 'Remove Image')
				{
					$this->deleteImage($_POST['image']);
					$this->displayEditor(1);
				}
				else 
				{
					MessageManager::displayMessage('You posted a form but I couldn\'t understand what you wanted me to do.');
					MessageManager::displayMessage('Action:' . $_POST['action']);	
				}
			}
			else if ($_SERVER['REQUEST_METHOD'] == 'GET')
			{
				if ($_GET['action'] == 'add')
					$this->displayEditor(0);
				else if ($_GET['action'] == 'edit')
					$this->displayEditor(1);
				else if ($_GET['action'] == 'preview')
					$this->previewPage();
				else if ($_GET['action'] == 'delete')
					$this->deletePage();
				else if ($_GET['action'] == 'DisplayImageCrop')
					$this->displayImageCrop();
				else 
				{
					MessageManager::displayMessage('You gave me an action but I couldn\'t understand what you wanted me to do.');
					MessageManager::displayMessage('Action:' . $_GET['action']);		
				}
			}
		}
		public function displayPages()
		{
			$this->displayOptions();
			$Pages = $this->mMagazine->getPages();
			echo '
			<div class="row issues">
			';
			if ($Pages)
			{
				$i = 0;
				foreach ($Pages as $Page)
				{
					$Layout = $Page->getLayout();
				
					echo '
					  <div class="col-sm-2" id="'.$Page->getId().'">
						<div class="thumbnail">
							<h3 class="'.$Page->getSectionClass().'-bg"><span class="page-num">'.$i.'</span>'.$Page->getSectionName().'</h3>
							<div class="preview">';
						$image = $this->mMagazine->getImageFilepath().'thumbnails/thumb-'.$this->mMagazine->getId().'-'.$Page->getId().'.png';
						if (file_exists($image))
							echo '<img src="/publisher/content/img/thumbnails/thumb-'.$this->mMagazine->getId().'-'.$Page->getId().'.png?t='.mktime().'" />';
						echo '</div>
						<div class="caption">
							<h5><a href="/publisher/editor/'.$this->mMagazine->getId().'/edit/'.$Page->getId().'/">'.$Page->getTitle().'</a></h5>
							<div class="btn-group">
								<a href="/publisher/editor/'.$this->mMagazine->getId().'/edit/'.$Page->getId().'/" class="btn btn-xs btn-primary" role="button"><i class="fa fa-edit"></i></a>
								<a href="/publisher/'.$this->mMagazine->getId().'/image/'.$Page->getId().'/" class="btn btn-xs btn-primary" role="button"><i class="fa fa-picture-o"></i></a>
								<a href="/publisher/editor/'.$this->mMagazine->getId().'/preview/'.$Page->getId().'/" class="btn btn-xs btn-primary" role="button"><i class="fa fa-eye"></i></a>
								<a class="btn btn-xs btn-danger btn-delete" role="button" data-id="'.$Page->getId().'"><i class="fa fa-trash-o"></i></a>
							</div>
						  </div>
						</div>
						</div>
					';
					$i++;
				}
			}
			else
				echo '
				<div class="col-sm-2">
					<div class="thumbnail">
						<p>No pages found.</p>
					</div>
				</div>
				';
			
			echo '
				<div class="col-sm-2">
					<div class="thumbnail">
						<h3></h3>
						<div class="preview">
							<a href="/publisher/editor/'.$this->mMagazine->getId().'/add/"><i class="fa fa-plus-square fa-5x"></i></a>
						</div>
						<p><a href="/publisher/editor/'.$this->mMagazine->getId().'/add/" class="btn btn-success" role="button">Add Page</a></p>
					</div>
				</div>
			</div>
			';
		}
		public function reorderPages($pages)
		{	
			$i = 0;
			foreach ($pages as $page)
			{
				$sql = "UPDATE pages SET pageNum = :pageNum WHERE id = :id";  
				$stmt = $this->mDatabase->prepare($sql);
				$stmt->execute(array(':pageNum'=>$i, ':id'=>$page));
				$i++;
			}
			$this->mMagazine->loadPagesFromDb();
			$this->displayPages();
		}
		public function displayLayouts($layout=0)
		{
			$output = '<div class="btn-group" data-toggle="buttons">';
			foreach ($this->mLayouts as $Layout)
			{
				$output .= '
					<label class="btn btn-layout btn-primary'; if ($layout == $Layout->getId()) $output .= ' active'; $output .= '">
    					<input type="radio" name="layout" id="layout'.$Layout->getId().'" value="'.$Layout->getId().'"'; if ($layout == $Layout->getId()) $output .= ' checked '; $output .= '/>
						<img src="'.$Layout->getImage().'" />
						<div>'.$Layout->getName().'</div>
					 </label>';
			}
			$output .= '
  			</div>';
			return $output;
		}
		public function displayImageCrop()
		{
			//Get image
			$mediaId = explode("img-", $_POST['mediaId']);
			$mediaId = $mediaId[1];
			$Media = $this->getMediaById($mediaId);
			$image = $Media->getSource();
			if ($Media->getPositionId() == 1)
				$ratio = 1000/400;
			if ($Media->getPositionId() == 4)
				$ratio = 667/1000;
			else
				$ratio = 1;

			$height = 420/$ratio;
	
			//If image is found display cropping tool
			if (isset($image) && $image != '' && $image != 'Error')
			{
				echo '<h4>Select Image Position</h4>';
					echo '<div class="btn-group">
						<a class="btn btn-primary btn-cropinline">FrontCover</a>
						<a class="btn btn-primary btn-cropmaster">Master</a>
						<a class="btn btn-primary btn-cropthird">Third</a>
						<a class="btn btn-primary btn-crophalf">Half</a>
						<a class="btn btn-primary btn-cropinline">Inline</a>
					</div>';
				echo '<hr />';
				echo '<div class="container-crop">';
				echo '<div id="previewbox" style="width:420px;height:'.$height.'px; overflow:hidden;float:right;">';
					
							echo '<img src="'.$image.'?t='.time().'" width="420" style="max-width:none;" id="preview" />';
							
						echo '</div>';
		
				echo '<div id="croppingContainer">';
					echo '<form>';
						echo '<input type="hidden" id="oW" name="oW" />';
						echo '<input type="hidden" id="oH" name="oH" />';
						echo '<input type="hidden" id="x" name="x" />';
						echo '<input type="hidden" id="y" name="y" />';
						echo '<input type="hidden" id="w" name="w" />';
						echo '<input type="hidden" id="h" name="h" />';
						echo '<input type="hidden" id="positionId" name="positionId" value="1" />';
						echo '<input type="hidden" id="mediaId" name="mediaId" value="'.$_POST['mediaId'].'" />';
						echo '<input type="hidden" name="image" value="'.$image.'?t='.mktime().'" />';
						echo '<img src="'.$image.'?t='.mktime().'" id="target" width="420" />';
						echo '</div>';
					echo '</form>';
				echo '</div>
				</div>';
				echo '<link rel="stylesheet" type="text/css" href="/publisher/css/jquery.Jcrop.css" /> <script src="/publisher/js/jquery.Jcrop.js"></script>';
				
				echo "<script type=\"text/javascript\">
					$(function() {
						$('#previewbox').prepend('<div class=\"coords\"></div>');
						$('.btn-cropinline').on('click', changeCropPreview);
						$('.btn-cropmaster').on('click', changeCropPreview);
						$('.btn-cropthird').on('click', changeCropPreview);
						$('.btn-crophalf').on('click', changeCropPreview);
						var jcrop_api, boundx, boundy;
						
						$('#target').on('load', function() {
							setTimeout(function() {	
							$('#target').Jcrop({";
							if ($ratio != 1)
								echo "aspectRatio : $ratio,";
							echo "
								onChange: updatePreview,
								onSelect: updatePreview,
								minSize: [150, 140],
								setSelect: [ 0, 0, 9999, 9999 ],
							  },function(){
								$('#target').attr('width', '420');
								// Use the API to get the real image size
								// Store the API in the jcrop_api variable
								jcrop_api = this;
								
								 var bounds = this.getBounds();
								boundx = bounds[0];
								boundy = bounds[1];
								$('#w').val(boundx);
								$('#h').val(boundy);
								var img = new Image();
								img.onload = function() {
									$('#oW').val(this.width);
									$('#oH').val(this.height);
								}
								img.src = $(\"#target\").attr('src');
							  });
					}, 500);
					  
					});
							function updateCoords(c)
							{
								$('#x').val(c.x);
								$('#y').val(c.y);
								$('#w').val(c.w);
								$('#h').val(c.h);
							};
				
							function checkCoords()
							{
								if (parseInt($('#w').val())) return true;
								alert('Please select a crop region then press submit.');
								return false;
							};
					  function updatePreview(c)
					  {
						
						//if (parseInt(c.w) > 0 && parseInt(c.x) <= boundx)
						//{
							 updateCoords(c);
							  var rx = 420 / c.w;
							  var rw = rx;
							  $('#preview').css({
								width: Math.round(rx * boundx) + 'px',
								marginLeft: '-' + Math.round(rx * c.x) + 'px',
								marginTop: '-' + Math.round(rx * c.y) + 'px'
							  });
								if($(this).hasClass('btn-cropinline'))
									$('#previewbox').height($('.jcrop-tracker').height());
							   
						 // }
					  };
					  function changeCropPreview() {
						if ($(this).hasClass('btn-cropinline')) {
								var ratio = '';
								$('#positionId').val('1');
							} else if ($(this).hasClass('btn-cropmaster')) {
								var ratio = 1000/400;
								$('#positionId').val('3');
							} else if ($(this).hasClass('btn-cropthird')) {
								var ratio = 667/1000;
								$('#positionId').val('4');
							} else if ($(this).hasClass('btn-crophalf')) {
								var ratio = 1;
								$('#positionId').val('5');
							}
						if	($(this).hasClass('btn-cropinline'))
							var height = $('.jcrop-tracker').height();
						else
							var height = 420/ratio;	
							$('#previewbox').height(height);
							jcrop_api.setOptions({aspectRatio : ratio});  
					  	}
					});
				</script>
				";
				
			}
			else
				MessageManager::displayMessage('Could not find image.', 2);
		}
		public function cropImage()
		{
	
			$data = $this->unserializePOST($_POST['data']);
			
			
			$tempImage = $data['image'];			
			
			
			$filenamePlusTime = explode($this->mMagazine->getImageURL(), $tempImage);
			$filename = explode('?', $filenamePlusTime[1]);
			MessageManager::displayMessage($filename, 9);
			$filepath = $this->mMagazine->getImageFilepath() . $filename[0];
			
		
			
			$image_info = getimagesize($tempImage);
			
			if ($data['x'] != '')
				$x = $data['x'];
			else
				$x = 0;
			
			if ($data['y'] != '')
				$y = $data['y'];
			else
				$y = 0;
			
				$w = $data['w'];
		
				$h = $data['h'];
			
			if ($data['positionId'] > 3)
				$newWidth = 667;
			else
				$newWidth = 1000;
				
			$rw = ($image_info[0] / 420);
			$w = floor($rw * $w);
			$h = floor($rw * $h);
			$nW = $image_info[0];
			$nH = ($h/$w)*$nW;
			$nH = floor($nH);
			$nX = $rw * $x;
			$nY = $rw * $y;

			//display output for debugging
			$output = '$positionId: '.$data['positionId'].'<br>';
			$output .= '$x: '.$x.'<br>';
			$output .= '$y: '.$y.'<br>';
			$output .= '$w: '.$w.'<br>';
			$output .= '$h: '.$h.'<br>';
			$output .= '$imageW: '.$image_info[0].'<br>';
			$output .= '$imageH: '.$image_info[1].'<br>';
			$output .= '$filepath: '.$filepath.'<br>';
			$output .= '$filename: '.$filename[0];
			
			$output .= '<p>Now we work out how that translates to 420 width</p>';
			
			$output .= '$nW: '.$nW.'<br>';
			$output .= '$nH: '.$nH.'<br>';
			$output .= '$nX: '.$nX.'<br>';
			$output .= '$nY: '.$nY.'<br>';
			$output .= '<img src="'.$this->mMagazine->getImageURL().$filename[0].'?t='.mktime().'" style="max-width:100%" />';
			MessageManager::displayMessage($output);
			
			//crop image		
			ini_set('memory_limit', '128M');
			$image = imagecreatefromjpeg($tempImage);
			if (!$image)
				MessageManager::displayMessage('Error creating image.', 'error');
			
			
			$newHeight = ($h/$w)*$newWidth;
			$newHeight = floor($newHeight);
							
			$newImage = imagecreatetruecolor($newWidth, $newHeight);
			
			
			imagecopyresampled($newImage, $image, 0, 0, $nX, $nY, $newWidth, $newHeight, $w, $h);
			imagejpeg($newImage, $filepath, 47);
			$tempImage = $newImage;
			
			
		}
		public function displayImages()
		{
			$id = $this->getPageId();
			$MediaList = $this->getMediaFromPage($id);
			if (count($MediaList)>0)
			{
				$output = '';
				foreach ($MediaList as $Media)
				{
					if ($Media->getPositionId() == 1 && !$Media->isActive())
					{
						$output .= '
						<div class="col-sm-2" id="'.$Media->getPageId().'">
							<div class="thumbnail">
							  <div class="preview"><img src="'.$Media->getSource().'" /></div>
							</div>
						</div>
						';
					}
				}
				return $output;
			}
		}
		public function displayTypesOfContent($Media)
		{
			$output = '';
			if (is_object($Media))
			{
				//$output .= $this->previewMedia($Media);
				$output .= '<img src="'.$Media->getSource().'" style="max-width:300px; max-height:300px" />';
				$output .= '
				<div class="editor-file">
					<input type="hidden" name="image" value="'.$Media->getId().'" />
					<input type="submit" name="action" value="Remove Image" />
					<input type="hidden" name="image-position" class="image-position" value="'.$Media->getPositionId().'" />
				</div>
				';
			}
			else
			{
				$output = '<div class="btn-group" data-toggle="buttons">';
				$output .= '
					<label class="btn btn-secondary btn-primary">
						<input type="radio" name="media" id="" value="Image" />
						Image
					</label>
					<label class="btn btn-secondary btn-primary">
						<input type="radio" name="media" id="" value="HTML" />
						HTML
					</label>
					';
				$output .= '
				</div>
				<div class="editor-file">
					<input type="file" name="file" class="content-file" />
					<input type="hidden" name="image-position" class="image-position" value="" />
				</div>
				';
			}
			//return $output;
			return '';
		}
		public function displaySections($section=0)
		{
			$output = '<div class="btn-group" data-toggle="buttons">';
			$i = 1;
			foreach ($this->mSections as $Section)
			{
				$id = $Section->getId();

				$style = 'style="background:#'.$Section->getColor().';';
				if (hexdec($Section->getColor()) < 10066329)
					$style .= 'color:#fff;"';
				else
					$style .= 'color:#121212;"';
				$output .= '
					<label class="btn btn-section'; if ($section == $id) $output .= ' active'; $output .= '" '.$style.' >
    					<input type="radio" name="section" id="section'.$id.'" value="'.$id.'"'; if ($section == $id) $output .= ' checked '; $output .= '/>
						'.$Section->getName().'
					 </label>';
				$i++;
			}
			$output .= '
  			</div>
  			<a class="btn btn-success btn-addsection">Add Section</a>
  			<input type="hidden" id="publicationId" name="publicationId" value="'.$this->mMagazine->getPublicationId().'" />';
			return $output;
		}
		public function editImages()
		{
			$this->displayOptions();
			$id = $this->getPageId();
			$pageNum = $this->mMagazine->getPageNum($id);
			$Page = $this->mMagazine->getPage($pageNum);
			$title = $Page->getTitle();	
			$standfirst = $Page->getStandfirst();
			$section = $Page->getSectionId();
			$layout = $Page->getLayoutId();				
			$MainImage = $Page->getMediaOfPosition($layout);
			$Media = $Page->getMedia();
			$content = $Page->loadPage($this->mMagazine->getFilepath());
			echo '<div class="row">';
				
			echo '</div>';
			echo '<div class="row">';
				echo '<div id="droppable">
			<div class="fixed-bar">
					<!-- Elastislide Carousel -->
					<ul id="carousel" class="elastislide-list">
						
					';
				if (count($Media))
				{
					foreach ($Media as $Item)
					{
						if ($Item->getPositionId() == 1 && !$Item->isActive())
						{
							//echo '<div class="img-default" id="img-'.$Item->getId().'" style="width:200px"><img src="'.$Item->getSource().'" /></div>';
							echo '
									<li id="li-'.$Item->getId().'"><div class="img-default" id="img-'.$Item->getId().'" style="width:200px"><img src="'.$Item->getSource().'" /></div></li>
								';
						}
							
					}
				}
				else
				{
					MessageManager::displayMessage('There are no images to edit.', 1);	
				}
				echo '</ul>
					<!-- End Elastislide Carousel -->
				</div>
				
		';
					$this->mMagazine->createImagePreviewPage($pageNum);
				echo '</div>';
			echo '</div>';
			echo '<div class="row">
				<a class="btn btn-success btn-saveimg" data-pageId="'.$id.'">Finish Editing</a>
			</div>';
		}
		public function saveImages()
		{
			$content = stripslashes($_POST['content']);
			
			if (empty($content))
				$content = ' ';
			
			$file = $this->mMagazine->getFilepath().'page'.$this->getPageId().'.html';
			if (!$file_handle = fopen($file,"w"))
				MessageManager::displayMessage('Cannot open file.', 2);
			if (!fwrite($file_handle, $content))
				MessageManager::displayMessage('Cannot write to file.', 2);
			fclose($file_handle);
			MessageManager::displayMessage('Images Saved');
			$this->redirect('/publisher/editor/'.$this->mMagazine->getId().'/preview/'.$this->getPageId().'/');
		}
		public function setMediaPosition()
		{
			$id = explode("-", $_POST['mediaId']);
			$Media = $this->getMediaById($id[1]);
			$Media->setPosition($_POST['positionId']);
			$this->editImages();
			MessageManager::displayMessage('Updated media.');
		}
		public function activateMedia()
		{
			$id = explode("-", $_POST['mediaId']);
			$Media = $this->getMediaById($id[1]);
			$Media->activate();
		}
		public function deactivateMedia()
		{
			$id = explode("-", $_POST['mediaId']);
			$Media = $this->getMediaById($id[1]);
			$pageId = $Media->getPageId();
			$pageNum = $this->mMagazine->getPageNum($pageId);
			$Page = $this->mMagazine->getPage($pageNum);
			$Media->deactivate();
			echo $Page->getImageDropbox();

		}
		public function displayEditor($mode)
		{
			if ($mode)
			{
				$id = $this->getPageId();
				$pageNum = $this->mMagazine->getPageNum($id);
				$Page = $this->mMagazine->getPage($pageNum);
				$title = $Page->getTitle();	
				$standfirst = $Page->getStandfirst();
				$section = $Page->getSectionId();
				$layout = $Page->getLayoutId();	
				$Media = $Page->getMediaOfPosition($layout);
				$content = $Page->loadPage($this->mMagazine->getFilepath());
			}
			else
				$this->clearPageId();
				
			$this->displayOptions();
			MessageManager::displayMessage('The current Page Id is: '.$this->getPageId());
			echo '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data" class="editor-form" id="editor-form" onsubmit="return validateForm();">
				<div class="row editor-section">
					<h4>Section</h4>
					<p class="help-text">Which section of the magazine do you want to put this page in?</p>
					'.@$this->displaySections($section).'
				</div>
				<div class="row editor-title">
					<h4>Title</h4>
					<p class="help-text">Please give the title of the page.</p>
					<input type="text" class="title" name="title" value="'.@$title.'" />
				</div>
				<div class="row editor-layout">
					<h4>Layout</h4>
					<p class="help-text">Which layout do you want to choose?</p>
					'.@$this->displayLayouts($layout).'
				</div>
				
				<div class="row editor-images">
					<h4>Images</h4>
					<p class="help-text">Upload all your images at once. You will be able to edit and move them in the next stage of creation.</p>
					<div class="image-preview-container row">'.
						@$this->displayImages().'
					</div>
					<a class="btn btn-primary btn-uploadimage">Upload Images</a>
				</div>
				<div class="row editor-standfirst">
					<h4>Standfirst</h4>
					<p class="help-text">Please write a small synopsis of the page.</p>
					<textarea name="standfirst" id="standfirst">';
						echo @$standfirst;
					echo '</textarea>
				</div>
				<div class="row editor-content">
					<h4>Content</h4>
					<p class="help-text">Please put the main content of the page here. Don\'t worry about images, they will be added in the next step.</p>
					<textarea name="content" id="content">';
						echo @$content;
					echo '</textarea>
				</div>
				<input type="hidden" name="pageId" value="'.@$id.'" />
				<div class="row editor-submit">';
				if ($mode == 0)
					echo '<h4>Add Page &amp; Edit Images</h4><input class="btn btn-success" type="submit" name="action" id="submitForm" value="Add" />';
				else
					echo '<h4>Save &amp; Edit Images</h4><input class="btn btn-success" type="submit" name="action" id="submitForm" value="Edit" />';
				echo '
			</div>
			</form>
			';
			/*<div class="row editor-secondary">
					<h4>Secondary Content</h4>
					'.@$this->displayTypesOfContent($Media).'
				</div>
			*/
		}
		public function previewMedia($Media)
		{
			$position = $Media->getPositionId();
			$output = '<div style="width:300px; font-size:7px">';
			
				$output .= '
				<div style="width:100%">
					<img src="'.$Media->getSource().'" style="width:100%" />
				</div>
				';
			$output .= '</div>';
			return $output;
		}
		public function createPage()
		{
			$pageNum = $this->mMagazine->getPagesCount();
			$title = $this->formatText($_POST['title']);
			$standfirst = $this->formatText($_POST['standfirst']);
			$content = $this->formatText($_POST['content']);
			if (empty($content))
				$content = ' ';
			$layout = $_POST['layout'];
			$section = $_POST['section'];
			$header = 1;
			$footer = 1;
			$sql = "INSERT INTO pages (magazineId, sectionId, layoutId, pageNum, title, standfirst, header, footer) VALUES (:magazineId, :sectionId, :layoutId, :pageNum, :title, :standfirst, :header, :footer)";  
			$stmt = $this->mDatabase->prepare($sql);
			$stmt->execute(array(':magazineId'=>$this->mMagazine->getId(),
				':sectionId'=>$section,
				':layoutId'=>$layout,
				':pageNum'=>$pageNum,
				':title'=>$title,
				':standfirst'=>$standfirst,
				':header'=>$header,
				':footer'=>$footer));
			
			$_SESSION['pageId'] = $this->mDatabase->lastInsertId();
			$file = $this->mMagazine->getFilepath().'page'.$this->getPageId().'.html';    
			if (!$file_handle = fopen($file,"w"))
				MessageManager::displayMessage('Cannot open file.', 2);
			if (!fwrite($file_handle, $content))
				MessageManager::displayMessage('Cannot write to file.', 2);
			fclose($file_handle);
			
			$this->mMagazine->insertPage($this->getPageId());
					
			$typeId = 0; //Images only atm
			$id = $this->getPageId();
			
			MessageManager::displayMessage('Creating page...');
			$this->redirect('/publisher/'.$this->mMagazine->getId().'/image/'.$id.'/');
		}
		public function savePage()
		{
			$section = $_POST['section'];
			$layout = $_POST['layout'];
			$title = $this->formatText($_POST['title']);
			$standfirst = $this->formatText($_POST['standfirst']);
			$content = $this->formatText($_POST['content']);
			if (empty($content))
				$content = ' ';
			$id = $this->getPageId();
			$sql = "UPDATE pages SET sectionId = :sectionId, layoutId = :layoutId, title = :title, standfirst = :standfirst WHERE id = :id";  
			$stmt = $this->mDatabase->prepare($sql);
			$stmt->execute(array(':sectionId'=>$section,
				':layoutId'=>$layout,
				':title'=>$title,
				':standfirst'=>$standfirst,
				':id'=>$id
				));
				
			$file = $this->mMagazine->getFilepath().'page'.$this->getPageId().'.html';
			if (!$file_handle = fopen($file,"w"))
				MessageManager::displayMessage('Cannot open file.', 2);
			if (!fwrite($file_handle, $content))
				MessageManager::displayMessage('Cannot write to file.', 2);
			fclose($file_handle);	
			
			$typeId = 0; //Images only atm

			MessageManager::displayMessage('Saving page...');
			/*$positionId = $_POST['image-position'];
			if (is_uploaded_file($_FILES['file']['tmp_name']))
				$this->uploadMedia($this->getPageId(), $typeId, $positionId, $this->uploadImage());*/
			$this->redirect('/publisher/'.$this->mMagazine->getId().'/image/'.$id.'/');
			$this->displayEditor(1);
		}
		public function deletePage()
		{
			$id = $this->getPageId();
			$this->mMagazine->deletePage($id);
			
			MessageManager::displayMessage('You deleted the page.',1);
			$this->displayPages();
		}
		public function displayOptions()
		{
			echo '
			<div class="row options">
				<div class="btn-group">
			';
			if (substr_count($_SERVER['REQUEST_URI'], '/publisher/editor/') > 0 || $_POST['action'] == 'EditImages')
			{
				echo '
					<a class="btn btn-default" href="/publisher/issue/'.$this->mMagazine->getId().'/">Back to Issue</a>
					<a class="btn btn-success" href="/publisher/editor/'.$this->mMagazine->getId().'/add/">Add Another Page</a>
					<a class="btn btn-warning" href="/publisher/editor/'.$this->mMagazine->getId().'/edit/'.$this->getPageId().'/">Edit Page</a>
					<a class="btn btn-warning" href="/publisher/'.$this->mMagazine->getId().'/image/'.$this->getPageId().'/">Edit Images</a>
					<a class="btn btn-danger" href="/publisher/editor/'.$this->mMagazine->getId().'/delete/'.$this->getPageId().'/">Delete Page</a>
				';
			}
			else
				echo '
					<a class="btn btn-success" href="/publisher/editor/'.$this->mMagazine->getId().'/add/">Add Page</a>
				';

			echo '
					<a class="btn btn-primary" href="/publisher/editor/'.$this->mMagazine->getId().'/preview/'.$this->getPageId().'/">Preview Page</a>
					<a class="btn btn-primary" href="/publisher/magazine/'.$this->mMagazine->getId().'/';
					$pageNum = $this->mMagazine->getPageNum($this->getPageId());
					if (!empty($pageNum))
						echo '#'.$pageNum;
					echo '" target="_blank">Preview Issue <i class="fa fa-external-link"></i></a>

				</div>
			</div>
			';
		}
		public function previewPage()
		{

			$pageNum = $this->mMagazine->getPageNum($this->getPageId());
			$this->displayOptions();
			MessageManager::displayMessage('Previewing Page');
			echo '<script>
   				document.domain = "http://www.angel-test.net";
    function setIframeHeight( iframeId )
    {
     var ifDoc, ifRef = document.getElementById( iframeId );
     try
     {   
      ifDoc = ifRef.contentWindow.document.documentElement; 
     }
     catch( e )
     { 
      try
      { 
       ifDoc = ifRef.contentDocument.documentElement;  
      }
      catch(ee)
      {   
      }  
     }
     if( ifDoc )
     {
      ifRef.height = 1;  
      ifRef.height = ifDoc.scrollHeight;  
     }
	}
 </script>';
			echo '<iframe width="100%" height="100%" scrolling="no" onload="setIframeHeight( this.id )" id="iframe" frameborder="0" src="http://www.angel-test.net/publisher/magazine/preview.php?magazineId='.$this->mMagazine->getId().'&pageNum='.$pageNum.'" />';
		}
		public function uploadMedia($pageId, $typeId, $positionId, $source)
		{
			if ($source != 'Error' && $source != '')
			{
				$sql = "INSERT INTO media (pageId, typeId, positionId, source) VALUES (:pageId, :typeId, :positionId, :source)";  
				$stmt = $this->mDatabase->prepare($sql);
				$stmt->execute(array(':pageId'=>$pageId,
					':typeId'=>$typeId,
					':positionId'=>$positionId,
					':source'=>$source));
			}
		}
		//Uploads the image to the server
		public function uploadImage()
		{
			
			
			$maxFileSize = 8 * 1024 * 1024; //8MB
			
			if ($_FILES["file"]["size"]	<= $maxFileSize)
			{			
				//Check it is actually an image
				if (($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/png"))
				{
					if ($_FILES["file"]["error"] > 0)
					{
						MessageManager::displayMessage('Error uploading image - Error Code: ' . $_FILES["file"]["error"], 2);
					}
					else
					{
						//Rename file
						$filename = 'image-'.$this->mMagazine->getId(). '-' . $this->getMediaId() .'-'.date("Y-m-d").'.jpg';
						$filepath = $this->mMagazine->getImageFilepath() . $filename;
						$actualpath = $this->mMagazine->getImageURL() . $filename;
						$tempImage = $_FILES["file"]["tmp_name"];
						$imageType = $_FILES["file"]["type"];
						$width = 0;
						$height = 0;
							//Check the size of the image
							$this->getSizeOfImage($tempImage, $width, $height);
							//Different banners with different size constraints
							
							//Activate when all sites are switched over
							/*if ($width < 500)
							{
								return 'Error';
								MessageManager::displayMessage('Image was too small.'.$filepath, 2);
							}*/
							$oldWidth=$width;
							$oldHeight=$height;
							if ($_POST['image-position'] == 4)
								$newWidth = 667;
							else
								$newWidth = 1000;
							$this->resizeImage($tempImage, $filepath, $width, $height, $newWidth);
							
							
							@move_uploaded_file($tempImage, $filepath);
							MessageManager::displayMessage('Image successfully uploaded.'.$filepath);
							return $actualpath;
					}
				}
				else
				{
					MessageManager::displayMessage('Error uploading image - Invalid file type. File type used: <i>'. $_FILES["file"]["type"] .'</i>. please use jpeg, png and gif.', 2);				
					return 'Error';
				}
			}
			else
			{
				MessageManager::displayMessage('Error uploading image - File too large. Size: <i>'. number_format(($_FILES["file"]["size"] / 1024 / 1024),2). 'MB.</i> Max filesize is 8MB.', 2);
				return 'Error';
			}	
		}
		public function getSizeOfImage($tempImage, &$width, &$height)
		{
			$image_info = getimagesize($tempImage);
			$imageType = $image_info[2];
			if( $imageType == IMAGETYPE_JPEG ) {
				$image = imagecreatefromjpeg($tempImage);
			} elseif( $imageType == IMAGETYPE_GIF ) {
				$image = imagecreatefromgif($tempImage);
			} elseif( $imageType == IMAGETYPE_PNG ) {
				$image = imagecreatefrompng($tempImage);
			}
			$width = imagesx($image);
			$height = imagesy($image);	
		}
		public function deleteImage($mediaId)
		{
			$id = $this->getPageId();
			$pageNum = $this->mMagazine->getPageNum($id);
			$Page = $this->mMagazine->getPage($pageNum);
			
			$Page->deleteMedia($mediaId, $this->mMagazine->getImageURL(), $this->mMagazine->getImageFilepath());
			
			MessageManager::displayMessage('Image successfully deleted.');
			$image = '';	
		}
		//Function to resize an image Here are some examples of parameters
		//$tempImage = $_FILES["file"]["tmp_name"];
		//$filepath = '/physicaldirectory/filename.jpg'
		//$width and $height are returned variables so you can store the images new width and height if you need to (you can easily remove those if you don't need them)
		public function resizeImage(&$tempImage, $filepath, &$width, &$height, $newWidth)
		{
			//Set the php.ini so large images can be processed
			ini_set('memory_limit', '256M');
			
			//create a physical image from the tempImage
			$image_info = getimagesize($tempImage);
			$imageType = $image_info[2];
			if( $imageType == IMAGETYPE_JPEG ) {
				$image = imagecreatefromjpeg($tempImage);
			} elseif( $imageType == IMAGETYPE_GIF ) {
				$image = imagecreatefromgif($tempImage);
			} elseif( $imageType == IMAGETYPE_PNG ) {
				$image = imagecreatefrompng($tempImage);
			}
			$width = imagesx($image);
			$height = imagesy($image);
			
			/*//Set the new width you would like
			if ($_POST['image-position'] == 4)
				$newWidth = 667;
			else
				$newWidth = 1000;*/
			
			//Calculate the new height
			$newHeight = ($height/$width)*$newWidth;
			$newHeight = floor($newHeight);
			
			//Resize the image
			$newImage = imagecreatetruecolor($newWidth, $newHeight);
			$white = imagecolorallocate($newImage,  255, 255, 255);
			imagefilledrectangle($newImage, 0, 0, $newWidth, $newWidth, $white);
			imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagejpeg($newImage, $filepath, 40);



/*$newImage = imagecreatetruecolor($width, $height);
$white = imagecolorallocate($newImage,  255, 255, 255);
imagefilledrectangle($newImage, 0, 0, $width, $height, $white);
imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
imagejpeg($output, $output_file);*/
			
			//Update the variables
			$tempImage = $newImage;		
			$width = $newWidth;
			$height = $newHeight;
		}
		public function createThumbnail($filename, $newWidth, $maxHeight)
		{
			//Set the php.ini so large images can be processed
			ini_set('memory_limit', '128M');
			
			$source = imagecreatefrompng($filename);

			$width = imagesx($source);
			$height = imagesy($source);
			
			//Calculate the new height
			$newHeight = ($height/$width)*$newWidth;
			$newHeight = floor($newHeight);
			
			if ($newHeight < $maxHeight)
				$maxHeight = $newHeight;
			//Create two images - one to resize the other to crop
			$thumb = imagecreatetruecolor($newWidth, $maxHeight);
			$temp = imagecreatetruecolor($newWidth, $newHeight);
			
			MessageManager::displayMessage('Width: '.$width);
			MessageManager::displayMessage('Height: '.$height);
			MessageManager::displayMessage('New Width: '.$newWidth);
			MessageManager::displayMessage('New Height: '.$newHeight);
			
			// Resize
			imagecopyresampled($temp, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagecopy($thumb, $temp, 0, 0, 0, 0, $newWidth, $newHeight);
			imagepng($thumb, $filename, 9);
			
			return $thumb;
				
		}
		public function formatText(&$content)
		{
			//Change any h tags to h2
			$result = str_replace(array("<h1>",'<h3>','<h4>'), "<h2>", $content);
			$result = str_replace(array("</h1>",'</h3>','</h4>'), "</h2>", $content);

			//Change divs to ps
			//$result = str_replace("<div>", "<p>", $content);
			//$result = str_replace("</div>", "</p>", $content);

			//Remove any styling that may have crept into the text		
			$allow = '<h2><p><a><div><strong><u><li><ul><i><table><tbody><tbody><tr><td><b><img><code><del><sub><sup>';
			$result = strip_tags($result,$allow);
			$result = $this->clean_inside_tags($result,$allow);

			//Remove any other unwanted characters
			$result = str_replace(array("\r","\n",'\r','\n'), " ", $result);
			if (get_magic_quotes_gpc())
				$result = stripslashes($result);
			$result = preg_replace('/\s\s+/', ' ', $result);		
	
			return $result;
		}
		public function clean_inside_tags($str,$tags){
		
			preg_match_all("/<([^>]+)>/i",$tags,$allTags,PREG_PATTERN_ORDER);
			
			foreach ($allTags[1] as $tag){
				if ($tag != 'img' && $tag != 'div' && $tag != 'a' && $tag != 'td')
					$str = preg_replace("/<\b".$tag."\b[^>]*>/i","<".$tag.">",$str);
					
				if ($tag == 'table')
					$str = preg_replace("/<\b".$tag."\b[^>]*>/i","<".$tag." width=\"485\" border=\"1\">",$str);
			}
			return $str;
		}
		public function no_magic_quotes($query) 
		{
			$nlRemoved = str_replace(array("\\r", "\\n"), '', $query);
			$excessP = str_replace("<p></p>", '', $nlRemoved);
			$data = explode("\\",$excessP);
			$cleaned = implode("",$data);
			return $cleaned;
		}
		public function redirect($url)
		{
		
			if(headers_sent())
			{
				$string = '<script type="text/javascript">';
				$string .= 'window.location = "' . $url . '"';
				$string .= '</script>';
		
				echo $string;
			}
			else
			{
			if (isset($_SERVER['HTTP_REFERER']) AND ($url == $_SERVER['HTTP_REFERER']))
				header('Location: '.$_SERVER['HTTP_REFERER']);
			else
				header('Location: '.$url);
		
			}
			exit;
		}
		public function clearPageId()
		{
			@$pageId = $_SESSION['pageId'];
			unset( $_SESSION['pageId'], $pageId );
		}
		public function getMagazineId()
		{
			if (!empty($_GET['magazineId']))
				return $_GET['magazineId'];
			else if (!empty($_POST['magazineId']))
				return $_POST['magazineId'];
			else if (!empty($_SESSION['magazineId']))
				return $_SESSION['magazineId'];
			return false;
		}
		public function getLayout($id)
		{
			foreach ($this->mLayouts as $Layout)
			{
				if ($Layout->getId() == $id)
					return $Layout;	
			}
		}
		public function getMediaFromPage($id)
		{
			$list = array();
			foreach ($this->mMedia as $Media)
			{
				if ($Media->getPageId() == $id)
					array_push($list, $Media);	
			}
			return $list;
		}
		public function getPageId()
		{
			if (!empty($_GET['pageId']))
				return $_GET['pageId'];
			else if (!empty($_POST['pageId']))
				return $_POST['pageId'];
			else if (!empty($_SESSION['pageId']))
				return $_SESSION['pageId'];
			else 
			{
				$magazineId = $this->mMagazine->getId();
				$sql = "SELECT id FROM pages ORDER BY id DESC";  
				$stmt = $this->mDatabase->prepare($sql);
				$stmt->execute();
				$Page = $stmt->fetch(PDO::FETCH_OBJ);
				return $Page->id+1;
			}
		}
		public function getMediaId() 
		{
			if (!empty($_GET['mediaId']))
				return $_GET['mediaId'];
			else if (!empty($_POST['mediaId']))
				return $_POST['mediaId'];
			else if (!empty($_SESSION['mediaId']))
				return $_SESSION['mediaId'];
			else 
			{
				$Media = end($this->mMedia);
				return $Media->getId()+1;
			}
		}
		public function getMediaById($id)
		{
			foreach ($this->mMedia as $Media)
			{
				if ($Media->getId() == $id)
					return $Media;	
			}
		}
		public function unserializePOST($array)
		{
			$values = array();
			$i = 0;
			foreach ($array as $value)
			{
				$name = $value['name'];
				$values[$name] = $value['value'];
			}
			return $values;
		}
	}
?>