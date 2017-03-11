<?php
/**
 *	Klass som hanterar bilder för ett recept.
 *	Omge med try-catch block.
 *
 *	@author Axel Larsson <axl.larsson@gmail.com>
 *
 */
class Image
{
	/**
	 *	Privata variabler
	 *
	 *	@var string $_caption	 - bildtext
	 *	@var string	$_path		 - filsökväg för filen
	 */
	private $_caption,
		$_path;

	/**
	 *	Array som definierar tillåtna MIME-typer
	 */
	private static $allowedMIMEs = array("image/jpg", "image/png", "image/gif", "image/jpeg");

	/**
	 *	Konstant som definierar uppladdningsmapp
	 *
	 *	@var const string $UPLOAD_DIR
	 */
	const UPLOAD_DIR = '../uploaded_images/';	// ska vara '../uploaded_images/'

	/**
	 *	Konstruktör - tar emot bilden i base64 eller som filsökväg
	 *	utför diverse valideringar och lagrar sedan
	 *	bilden med ett slumpmässigt vald namn
	 *	Tar dessutom emot en captionsträng.
	 *
	 *	Om valideringen misslyckas kastas fel.
	 *
	 *	@param string $image - bilden kodad i base64 eller en filsökväg
	 *	@param string $caption
	 */
	public function __construct($image, $caption)
	{
		// Kolla att vi har fått rätt antal argument
		if (func_num_args() != 2) {
			throw new Exception('Image.class.php->__construct: WRONG NUMBER OF ARGUMENTS');
		}
		// lagra caption
		$this->_caption = $caption;

		// Om $image är en filsökväg - lagra värdet och vi är klara
		if (strpos($image, 'uploaded_images') !== false) {
			$this->_path = $image;
			return;
		}


		// kolla MIME
		$MIME = self::getMimeFromBase64($image);
		if (!in_array($MIME, self::$allowedMIMEs)) {
			throw new Exception("Image.class.php->__construct: INVALID MIME-TYPE.");
		}

		// konvertera från base64 till riktig data
		$image = self::convertBase64ToImage($image);

		// kolla så att konverteringen lyckades
		if ($image == false) {
			throw new Exception("Image.class.php->__construct: CONVERSION FAILED.");
		}

		// spara bilden, lagra filsökväg
		$extension = self::getExtensionFromMIME($MIME);
		$this->_path = self::UPLOAD_DIR . uniqid() . $extension;

		// Spara filen
		if (!file_put_contents($this->_path, $image)) {
			throw new Exception("Image.class.php->__construct: COULDN'T SAVE IMAGE.");
			// Ta bort filen
			unlink($file);
		}
	}

	/**
	 *	Tar reda på MIME-typ av en base64-kodad bild
	 *
	 *	@param base64 $base64	- base64-kodad bild
	 *	@return string 			- MIME-typen
	 */
	private function getMimeFromBase64($base64)
	{
		// tokeniza base64
		$MIME = strtok($base64, ';');
		$MIME = strtok($MIME, ':');
		return strtok(':');
	}

	/**
	 *	Konverterar base64 till vanlige en vanlig bild
	 *
	 *	@param string base64 - base64-fil
	 *
	 *	@return data - returnerar som vanlig bild
	 */
	private function convertBase64ToImage($base64)
	{
		// Strippa bort MIME-typen i början, genom att hitta kommat
		$comma = strpos($base64, ',');
		$base64 = substr($base64, $comma+1);
		// Konvertera till "vanlig" bildfil
		return base64_decode($base64, 0);
	}

	/**
	 *	Beräknar filändelse utifrån MIME-typ
	 *
	 *	@param string $MIME - MIME-typ, ex: image/jpg
	 *
	 *	@return string - returnerar filändelse, ex: .jpg
	 */
	private function getExtensionFromMIME($MIME)
	{
		$MIME = strtok($MIME, '/');
		return '.' . strtok('/');
	}

	/**
	 *	Get-funktion för filsökväg
	 *
	 *	@return string - filsökvägen
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 *	Get-funktion för bildtext
	 *
	 *	@return string - bildtexten
	 */
	public function getCaption()
	{
		return $this->_caption;
	}

	/**
	 *	Get-funktion som returnerar bilden i base64
	 *
	 *	@return string base64 - en base64-kod representerandes bilden
	 */
	function getBase64()
	{
		// @ innebär att eventuella fel ignoreras - såsom att filen inte finns
		// File handler för att öppna filen
		$path = $_ENV["IMAGE_UPLOAD_PATH"] . substr($this->_path, 3);
		$handle = @fopen($path, 'r');
		// Själva bilden
		$image = @fread($handle, filesize($path));
		@fclose($handle);

		// Filändelse från filsökvägen
		$ext = substr(strrchr($path,'.'),1);
		// Fixa base64 med lite MIME-prefix:
		return 'data:image/' . $ext . ';base64,' . base64_encode($image);
	}

	/**
	 *	Set-funktion för bildtext
	 *
	 *	@param string $newCaption - bildtexten
	 */
	public function setCaption($newCaption)
	{
		$this->_caption = $newCaption;
	}
}
?>
