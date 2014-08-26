<?php
/**
 * @category Plugin
 * @copyright  2014 Juan Andrés Carmena
 * @author Juan Andrés Carmena <juan14nob@gmail.com>
 * @link https://github.com/jcdesignweb/CropJC
 * @version 1.0
 *
 */
class cropjc {
	
	/*
	 * Slash const 
	 * */
	const SLASH = "/";
	
	private $image;
	private $error = false;
	private $message;
	
	private $img;
	
	private $imagesize; // array
	
	/**
	 * save imagetype // gif, png, jpg
	 * @var String
	 */
	private $imagetype; 
	
	
	private $extension;
	
	private $canvas;
	
	
	private $newImage;
	
	
	/**
	 * Image dimentions
	 * 
	 * @var array
	 */
	private $dimensions;
	
	
	/**
	 * 
	 * @var array - defaults params
	 */
	public $default = array("quality" => 100);
	
	protected $createNewName;
	
	public function __construct($defaults = null) {
		
		$this->verifyModules();
		
		if(is_array($defaults)) {
			
			$this->setDefaultParams($defaults);

			$this->setFile();
		}
	}
	
	/**
	 * This method make crop and resize from the image .
	 */
	public function Crop() {
	
		$this->startConvertion();
	
		$this->canvas = imagecreatetruecolor(
				$this->dimensions["cropW"],
				$this->dimensions["cropH"]
		);
	
		$this->Imagecreate($this->dimensions["xPos"], $this->dimensions["yPos"], $this->dimensions["cropW"], $this->dimensions["cropH"] , $this->dimensions["cropW"], $this->dimensions["cropH"]);
		
		//print($this->dimensions["square"]);exit;
		
		if(array_key_exists("square", $this->dimensions) && $this->dimensions["square"] !== false) {
			$this->Imageresize($this->dimensions["square"],$this->dimensions["square"]);
		}
	
		return json_encode(
			array(
				"cropped" => $this->createNewName,
				"error"=> $this->error,
				"message" => $this->message
			)
		);
	}
	
	/**
	 * set values from $_FILES
	 * @param $_FILE $file
	 */
	private function File($file) {
		$this->img = $file["imagen"];
	}
	
	private function copyFile() {
		return move_uploaded_file($this->img, $this->getNewPath());
	}
	
	/**
	 * Return if GD modules are enabled
	 * 
	 * @return boolean
	 */
	private function getGD() {
		return (extension_loaded('gd') && function_exists('gd_info'))? true : false;
	}
	
	/**
	 * get the destination path
	 */
	private function getNewPath() {
		
		$path = $this->getRealDir() . self::SLASH . $this->default["destination_path"];
		
		if(is_dir($path)) {
			return $path. self::SLASH . $this->createNewName;
		}else{
			$this->error = true;
			$this->message = "'{$path}' not such file or directory";
			exit();
		}
	}

	/**
	 * return the realpath from this directory
	 */
	private function getRealDir() {
		return realpath(__DIR__);
	}

	/**
	 * return image width
	 */
	private function getWidth()	{
		return imagesx($this->canvas);
	}
	
	/**
	 *  return the image height
	 */
	private function getHeight() {
		return imagesy($this->canvas);
	}
	
	/**
	 * This create the temporal image
	 * 
	 * @param int $xPos
	 * @param int $yPos
	 * @param int $cropH
	 * @param int $cropW
	 * @param int $new_height
	 * @param int $new_width
	 */
	private function Imagecreate($xPos, $yPos, $cropH, $cropW, $new_height, $new_width) {
		$this->setNewImage();
	
		$x_dest = $this->imagesize["width"] + $this->dimensions["xPos"];
		$y_dest = $this->imagesize["height"] + $this->dimensions["yPos"];
	
		imagecopyresampled($this->canvas, $this->newImage, 0, 0, $this->dimensions["xPos"], $this->dimensions["yPos"], $this->dimensions["cropW"], $this->dimensions["cropH"] , $this->dimensions["cropW"], $this->dimensions["cropH"]);
	
	
		switch($this->imagetype) {
			case "jpg":
				imagejpeg($this->canvas, $this->getNewPath(), $this->default["quality"]);
				break;
					
			case "png":
				imagepng($this->canvas, $this->getNewPath(), $this->default["quality"] / 100);
				break;
					
			case "gif":
				imagegif($this->canvas, $this->getNewPath(), $this->default["quality"]);
				break;
		}
	}
	
	/**
	 * Resize the image 
	 * 
	 * @param int $width
	 * @param int $height
	 */
	
	private function Imageresize($width,$height) {
	
		$new_image = imagecreatetruecolor($width, $height);
		imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
	
		$this->setNewImage();
	
		imagecopyresampled($new_image, $this->newImage, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
	
		switch($this->imagetype) {
			case "jpg":
				imagejpeg($new_image, $this->getNewPath(), $this->default["quality"]);
				break;
					
			case "png":
				imagepng($new_image, $this->getNewPath(), $this->default["quality"] / 100);
				break;
					
			case "gif":
				imagegif($new_image, $this->getNewPath(), $this->default["quality"]);
				break;
		}
	}
	
	/**
	 * Set the defaults params, this values are setting in the client php.
	 * 
	 * @param Array $defaults
	 */
	private function setDefaultParams($defaults) {
		$this->File($_FILES);
		$this->default=array_replace($this->default, $defaults);
		$this->verifyParams();
		
		if(isset($_POST["dimensions"])) {
			$this->dimensions = get_object_vars(json_decode($_POST["dimensions"]));
		}
	}
	
	/**
	 * set global $imagesize to get the image dimentions
	 */
	private function setDimension() {
		list($this->imagesize["width"], $this->imagesize["height"]) = getimagesize($this->getNewPath());
	}
	
	/**
	 * this method is a setters. Set permissions to new image, next set imageType and his dimensions 
	 */
	public function setFile() {
	
		$this->image = $_FILES["imagen"]["name"];
		$this->setExtension();
		move_uploaded_file($_FILES["imagen"]['tmp_name'], $this->getNewPath());
	
		$this->setPermission();
	
		$this->setImageType();
	
		$this->setDimension();
	}
	
	
	/**
	 * set global $imageyype
	 */
	private function setImageType() {
		
		switch(exif_imagetype($this->getNewPath())) {
			
			case IMAGETYPE_GIF:
				$this->imagetype = "gif";
			break;
			
			case IMAGETYPE_JPEG:
				$this->imagetype = "jpg";
				break;
					
			case IMAGETYPE_PNG:
				$this->imagetype = "png";
				break;
		}
	}
	
	/**
	 * set in global $newImage the new image
	 */
	private function setNewImage() {
		switch($this->imagetype) {
			case "jpg":
				$this->newImage = imagecreatefromjpeg($this->getNewPath());
				break;
					
			case "png":
				$this->newImage = imagecreatefrompng($this->getNewPath());
				break;
					
			case "gif":
				$this->newImage = imagecreatefromgif($this->getNewPath());
				break;
		}
	}
	
	
	private function setPermission() {
		chmod($this->getNewPath(), 0777);
	}

	
	private function setExtension() {
		$this->extension = strrchr($this->image, ".");
		$this->createNewName = time() . $this->extension;
	}
	
	/**
	 * Change array dimensions from % to px
	 */
	private function startConvertion() {
		$this->dimensions['cropW'] = ($this->imagesize["width"]*$this->dimensions['cropW']/100);
		$this->dimensions['cropH'] = ($this->imagesize["height"]*$this->dimensions['cropH']/100);
	
		$this->dimensions['yPos'] = ($this->imagesize["height"]*$this->dimensions['yPos']/100);
		$this->dimensions['xPos'] = ($this->imagesize["width"]*$this->dimensions['xPos']/100);
	}
	
	/**
	 * Verify if the $file is valid
	 * @return boolean
	 */
	private function verifyExtensionFile() {
		$validExt = array('.jpg', '.jpeg', '.gif', '.png');
		return in_array( $this->extension, $validExt );
	}
	
	/**
	 * Verify params
	 */
	private function verifyParams() {
		
		$stricts = array('destination_path');
		
		foreach($stricts as $strict) {
			if(!array_key_exists($strict, $this->default)) {
				
				$this->error = true;
				$this->message = "Falta parametro";
					
				exit();
			}
		}
	}
	
	/**
	 * Verify if GD modules is enabled on the server
	 */	
	private function verifyModules() {
		
		if(!$this->getGD()) {
			$this->error = true;
			$this->message = "GD is disabled";
			exit();
		}
	}
	
	/**
	 * Verify the file to upload, should be a image
	 * @param File $file
	 * @return boolean|string
	 */
	private function verifyFile($file)	{
		$fh = fopen($file,'rb');
		if ($fh) {
			$bytes = fread($fh, 6); // read 6 bytes
			fclose($fh);            // close file
	
			if ($bytes === false) { // bytes there?
				return false;
			}
			if (substr($bytes,0,3) == "\xff\xd8\xff") {
				return 'image/jpeg';
			}
			if ($bytes == "\x89PNG\x0d\x0a") {
				return 'image/png';
			}
			if ($bytes == "GIF87a" or $bytes == "GIF89a") {
				return 'image/gif';
			}
	
			return 'application/octet-stream';
		}
		return false;
	}

	public function __destruct() {
		if($this->error){
			die($this->message);
		}
	}
}