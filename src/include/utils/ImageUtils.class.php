<?php
	/**
	 * Class ImageUtils
	 *
	 * Esta clase permite manejar las propiedades de la imagenes.
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 * NOTA: PHP Mess Detector reporta 9 hallazgos de propiedades y métodos públicos, pero resulta imposible reducirlos de momento, pues hasta el momento
	 * se consideran todas necesarias
	 */
	class ImageUtils {
		/**
		 * PHP extension/library to use for image manipulation
		 * Can be: imagemagick, netpbm, gd, gd2
		 *
		 * @var string
		 */
		public $imageLibrary = 'gd2';

		/**
		 * Path to the graphic library (if applicable)
		 *
		 * @var string
		 */
		public $libraryPath = '';

		/**
		 * Whether to send to browser or write to disk
		 *
		 * @var boolean
		 */
		public $dynamicOutput = false;

		/**
		 * Path to original image
		 *
		 * @var string
		 */
		public $sourceImage	= '';

		/**
		 * Path to the modified image
		 *
		 * @var string
		 */
		public $newImage = '';

		/**
		 * Image width
		 *
		 * @var integer
		 */
		public $width = '';

		/**
		 * Image height
		 *
		 * @var integer
		 */
		public $height = '';

		/**
		 * Quality percentage of new image
		 *
		 * @var integer
		 */
		public $quality	= 90;

		/**
		 * Whether to create a thumbnail
		 *
		 * @var boolean
		 */
		public $createThumb	= false;

		/**
		 * String to add to thumbnail version of image
		 *
		 * @var string
		 */
		public $thumbMarker = '_thumb';

		/**
		 * Whether to maintain aspect ratio when resizing or use hard values
		 *
		 * @var boolean
		 */
		public $maintainRatio = true;

		/**
		 * Auto, height, or width.  Determines what to use as the master dimension
		 *
		 * @var string
		 */
		public $masterDim = 'auto';

		/**
		 * Angle at to rotate image
		 *
		 * @var string
		 */
		public $rotationAngle = '';

		/**
		 * X Coordinate for manipulation of the current image
		 *
		 * @var integer
		 */
		public $xAxis = '';

		/**
		 * Y Coordinate for manipulation of the current image
		 *
		 * @var integer
		 */
		public $yAxis = '';

		/**
		 * Watermark text if graphic is not used
		 *
		 * @var string
		 */
		public $wmText	= '';

		/**
		 * Type of watermarking.  Options:  text/overlay
		 *
		 * @var string
		 */
		public $wmType = 'text';

		/**
		 * Default transparency for watermark
		 *
		 * @var integer
		 */
		public $wmXTransp = 4;

		/**
		 * Default transparency for watermark
		 *
		 * @var integer
		 */
		public $wmYTransp = 4;

		/**
		 * Watermark image path
		 *
		 * @var string
		 */
		public $wmOverlayPath = '';

		/**
		 * TT font
		 *
		 * @var string
		 */
		public $wmFontPath = '';

		/**
		 * Font size (different versions of GD will either use points or pixels)
		 *
		 * @var integer
		 */
		public $wmFontSize	= 17;

		/**
		 * Vertical alignment:   T M B
		 *
		 * @var string
		 */
		public $wmVrtAlignment = 'B';

		/**
		 * Horizontal alignment: L R C
		 *
		 * @var string
		 */
		public $wmHorAlignment	= 'C';

		/**
		 * Padding around text
		 *
		 * @var integer
		 */
		public $wmPadding = 0;

		/**
		 * Lets you push text to the right
		 *
		 * @var integer
		 */
		public $wmHorOffset	= 0;

		/**
		 * Lets you push text down
		 *
		 * @var integer
		 */
		public $wmVrtOffset	= 0;

		/**
		 * Text color
		 *
		 * @var string
		 */
		protected $wmFontColor	= '#ffffff';

		/**
		 * Dropshadow color
		 *
		 * @var string
		 */
		protected $wmShadowColor = '';

		/**
		 * Dropshadow distance
		 *
		 * @var integer
		 */
		public $wmShadowDistance = 2;

		/**
		 * Image opacity: 1 - 100  Only works with image
		 *
		 * @var integer
		 */
		public $wmPpacity = 50;

		/**
		 * Source image folder
		 *
		 * @var string
		 */
		public $sourceRolder = '';

		/**
		 * Destination image folder
		 *
		 * @var string
		 */
		public $destFolder = '';

		/**
		 * Image mime-type
		 *
		 * @var string
		 */
		public $mimeType = '';

		/**
		 * Original image width
		 *
		 * @var integer
		 */
		public $origWidth = '';

		/**
		 * Original image height
		 *
		 * @var integer
		 */
		public $origHeight = '';

		/**
		 * Image format
		 *
		 * @var string
		 */
		public $imageType = '';

		/**
		 * Size of current image
		 *
		 * @var string
		 */
		public $sizeStr	= '';

		/**
		 * Full path to source image
		 *
		 * @var string
		 */
		public $fullSrcPath = '';

		/**
		 * Full path to destination image
		 *
		 * @var string
		 */
		public $fullDstPath = '';

		/**
		 * File permissions
		 *
		 * @var	integer
		 */
		public $filePermissions = 0644;

		/**
		 * Name of function to create image
		 *
		 * @var string
		 */
		public $createFnc = 'imagecreatetruecolor';

		/**
		 * Name of function to copy image
		 *
		 * @var string
		 */
		public $copyFnc = 'imagecopyresampled';

		/**
		 * Error messages
		 *
		 * @var array
		 */
		public $errorMsg = array();

		/**
		 * Whether to have a drop shadow on watermark
		 *
		 * @var boolean
		 */
		protected $wmUseDropShadow = false;

		/**
		 * Whether to use truetype fonts
		 *
		 * @var boolean
		 */
		public $wmUseTruetype	= false;

		/**
		 * Initialize Image Library
		 *
		 * @param	array	$props
		 *
		 * @return	void
		 */
		public function __construct ($props = array()) {
			if (count ($props) > 0) {
				$this->initialize ($props);
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Initialize image properties
		 *
		 * Resets values in case this class is used in a loop
		 *
		 * @return	void
		 */
		public function clear () {
			$props = array(
				'thumbMarker',
				'libraryPath',
				'sourceImage',
				'newImage',
				'width',
				'height',
				'rotationAngle',
				'xAxis',
				'yAxis',
				'wmText',
				'wmOverlayPath',
				'wmFontPath',
				'wmShadowColor',
				'sourceRolder',
				'destFolder',
				'mimeType',
				'origWidth',
				'origHeight',
				'imageType',
				'sizeStr',
				'fullSrcPath',
				'fullDstPath',
			);

			foreach ($props as $val) {
				$this->$val = '';
			}

			$this->imageLibrary 	= 'gd2';
			$this->dynamicOutput 	= false;
			$this->quality 			= 90;
			$this->createThumb 		= false;
			$this->thumbMarker 		= '_thumb';
			$this->maintainRatio 	= true;
			$this->masterDim 		= 'auto';
			$this->wmType 			= 'text';
			$this->wmXTransp 		= 4;
			$this->wmYTransp 		= 4;
			$this->wmFontSize 		= 17;
			$this->wmVrtAlignment 	= 'B';
			$this->wmHorAlignment 	= 'C';
			$this->wmPadding 		= 0;
			$this->wmHorOffset 		= 0;
			$this->wmVrtOffset 		= 0;
			$this->wmFontColor		= '#ffffff';
			$this->wmShadowDistance = 2;
			$this->wmPpacity 		= 50;
			$this->createFnc 		= 'imagecreatetruecolor';
			$this->copyFnc 			= 'imagecopyresampled';
			$this->errorMsg 		= array ();
			$this->wmUseDropShadow 	= false;
			$this->wmUseTruetype 	= false;
		}

		/**
		 * Initialize image preferences
		 *
		 * @param	array
		 *
		 * @return	boolean
		 */
		public function initialize ($props = array()) {
			// Convert array elements into class variables
			if (count ($props) > 0) {
				foreach ($props as $key => $val) {
					if (property_exists ($this, $key)) {
						if (in_array ($key, array ('wmFontColor', 'wmShadowColor'))) {
							if (preg_match ('/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', $val, $matches)) {
								$val = (strlen ($matches[1]) === 6) ? '#'.$matches [1] : '#'.$matches [1][0] . $matches [1][0] . $matches [1][1] . $matches [1][1] . $matches [1][2] . $matches [1][2];
							} else {
								continue;
							}
						}

						$this->$key = $val;
					}
				}
			}

			if (empty ($this->sourceImage)) {
				$this->setError ('imglib_source_image_required');
				return false;
			}

			/* Is getimagesize() available?
			 *
			 * We use it to determine the image properties (width/height).
			 * Note: We need to figure out how to determine image
			 * properties using ImageMagick and NetPBM
			 */
			if (!function_exists ('getimagesize')) {
				$this->setError ('imglib_gd_required_for_props');
				return false;
			}

			$this->imageLibrary = strtolower ($this->imageLibrary);

			/* Set the full server path
			 *
			 * The source image may or may not contain a path.
			 * Either way, we'll try use realpath to generate the
			 * full server path in order to more reliably read it.
			 */
			$fullSourcePath = realpath ($this->sourceImage);
			if ($fullSourcePath !== false) {
				$fullSourcePath = str_replace ('\\', '/', $fullSourcePath);
			} else {
				$fullSourcePath = $this->sourceImage;
			}

			$x = explode ('/', $fullSourcePath);
			$this->sourceImage  = end ($x);
			$this->sourceRolder = str_replace ($this->sourceImage, '', $fullSourcePath);

			// Set the Image Properties
			if (!$this->getImageProperties ($this->sourceRolder.$this->sourceImage)) {
				return false;
			}

			/*
			 * Assign the "new" image name/path
			 *
			 * If the user has set a "newImage" name it means
			 * we are making a copy of the source image. If not
			 * it means we are altering the original. We'll
			 * set the destination filename and path accordingly.
			 */

			if (empty ($this->newImage)) {
				$this->destImage  = $this->sourceImage;
				$this->destFolder = $this->sourceRolder;
			} else if (strpos ($this->newImage, '/') === false) {
				$this->destFolder = $this->sourceRolder;
				$this->destImage  = $this->newImage;
			} else {
				if (strpos ($this->newImage, '/') === false && strpos ($this->newImage, '\\') === false) {
					$fullDestPath = str_replace ('\\', '/', realpath($this->newImage));
				} else {
					$fullDestPath = $this->newImage;
				}

				// Is there a file name?
				if (!preg_match ('#\.(jpg|jpeg|gif|png)$#i', $fullDestPath)) {
					$this->destFolder = $fullDestPath.'/';
					$this->destImage  = $this->sourceImage;
				} else {
					$x = explode ('/', $fullDestPath);
					$this->destImage = end ($x);
					$this->destFolder = str_replace ($this->destImage, '', $fullDestPath);
				}
			}

			/* Compile the finalized filenames/paths
			 *
			 * We'll create two master strings containing the
			 * full server path to the source image and the
			 * full server path to the destination image.
			 * We'll also split the destination image name
			 * so we can insert the thumbnail marker if needed.
			 */
			if ($this->createThumb === false || empty ($this->thumbMarker)) {
				$this->thumbMarker = '';
			}

			$xp = $this->explodeName ($this->destImage);

			$filename = $xp ['name'];
			$file_ext = $xp ['ext'];

			$this->fullSrcPath = $this->sourceRolder . $this->sourceImage;
			$this->fullDstPath = $this->destFolder . $filename . $this->thumbMarker . $file_ext;

			/* Should we maintain image proportions?
			 *
			 * When creating thumbs or copies, the target width/height
			 * might not be in correct proportion with the source
			 * image's width/height. We'll recalculate it here.
			 */
			if ($this->maintainRatio === true && ($this->width !== 0 || $this->height !== 0)) {
				$this->imageReproportion ();
			}

			/* Was a width and height specified?
			 *
			 * If the destination width/height was not submitted we
			 * will use the values from the actual file
			 */
			if (empty ($this->width)) {
				$this->width = $this->origWidth;
			}

			if (empty ($this->height)) {
				$this->height = $this->origHeight;
			}

			// Set the quality
			$this->quality = trim (str_replace('%', '', $this->quality));

			if (empty ($this->quality) || $this->quality === 0 || ! ctype_digit($this->quality)) {
				$this->quality = 90;
			}

			// Set the x/y coordinates
			$this->xAxis = (is_numeric ($this->xAxis)) ? $this->xAxis : 0;
			$this->yAxis = (is_numeric ($this->yAxis)) ? $this->yAxis : 0;


			// Watermark-related Stuff...
			if (!empty ($this->wmOverlayPath)) {
				$this->wmOverlayPath = str_replace('\\', '/', realpath($this->wmOverlayPath));
			}

			if (!empty ($this->wmShadowColor)) {
				$this->wmUseDropShadow = true;
			} else if ($this->wmUseDropShadow === true && empty ($this->wmShadowColor)) {
				$this->wmUseDropShadow = false;
			}

			if (!empty ($this->wmFontPath)) {
				$this->wmUseTruetype = true;
			}

			return true;
		}

		/**
		 * Image Resize
		 *
		 * This is a wrapper function that chooses the proper
		 * resize function based on the protocol specified
		 *
		 * @return	boolean
		 */
		public function resize () {
			$protocol = ($this->imageLibrary === 'gd2') ? 'imageProcessGD' : 'image_process_' . $this->imageLibrary;
			return $this->$protocol ('resize');
		}

		// --------------------------------------------------------------------

		/**
		 * Image Crop
		 *
		 * This is a wrapper function that chooses the proper
		 * cropping function based on the protocol specified
		 *
		 * @return	boolean
		 */
		public function crop () {
			$protocol = ($this->imageLibrary === 'gd2') ? 'imageProcessGD' : 'image_process_' . $this->imageLibrary;
			return $this->$protocol ('crop');
		}

		// --------------------------------------------------------------------

		/**
		 * Image Rotate
		 *
		 * This is a wrapper function that chooses the proper
		 * rotation function based on the protocol specified
		 *
		 * @return	boolean
		 */
		public function rotate () {
			// Allowed rotation values
			$degs = array (90, 180, 270, 'vrt', 'hor');

			if (empty ($this->rotationAngle) || ! in_array($this->rotationAngle, $degs)) {
				$this->setError ('imglib_rotation_angle_required');
				return false;
			}

			// Reassign the width and height
			if ($this->rotationAngle === 90 || $this->rotationAngle === 270) {
				$this->width	= $this->origHeight;
				$this->height	= $this->origWidth;
			} else {
				$this->width	= $this->origWidth;
				$this->height	= $this->origHeight;
			}

			// Choose resizing function
			if ($this->imageLibrary === 'imagemagick' || $this->imageLibrary === 'netpbm') {
				$protocol = 'image_process_' . $this->imageLibrary;
				return $this->$protocol ('rotate');
			}

			return ($this->rotationAngle === 'hor' || $this->rotationAngle === 'vrt') ? $this->imageMirrorGD () : $this->imageRotateGD ();
		}

		// --------------------------------------------------------------------

		/**
		 * Image Process Using GD/GD2
		 *
		 * This function will resize or crop
		 *
		 * @param string $action
		 *
		 * @return boolean
		 */
		public function imageProcessGD ($action = 'resize') {
			$v2_override = false;

			// If the target width/height match the source, AND if the new file name is not equal to the old file name
			// we'll simply make a copy of the original with the new name... assuming dynamic rendering is off.
			if ($this->dynamicOutput === false && $this->origWidth === $this->width && $this->origHeight === $this->height) {
				if ($this->sourceImage !== $this->newImage && @copy($this->fullSrcPath, $this->fullDstPath)) {
					chmod ($this->fullDstPath, $this->filePermissions);
				}
				return true;
			}

			// Let's set up our values based on the action
			if ($action === 'crop') {
				// Reassign the source width/height if cropping
				$this->origWidth  = $this->width;
				$this->origHeight = $this->height;

				// GD 2.0 has a cropping bug so we'll test for it
				if ($this->gdVersion () !== false) {
					$gd_version = str_replace ('0', '', $this->gdVersion());
					$v2_override = ($gd_version == 2);
				}
			} else {
				// If resizing the x/y axis must be zero
				$this->xAxis = 0;
				$this->yAxis = 0;
			}

			// Create the image handle
			$srcImg = $this->imageCreateGD ();
			if (!$srcImg) {
				return false;
			}

			/* Create the image
			 *
			 * Old conditional which users report cause problems with shared GD libs who report themselves as "2.0 or greater"
			 * it appears that this is no longer the issue that it was in 2004, so we've removed it, retaining it in the comment
			 * below should that ever prove inaccurate.
			 *
			 * if ($this->imageLibrary === 'gd2' && function_exists('imagecreatetruecolor') && $v2_override === false)
			 */
			if ($this->imageLibrary === 'gd2' && function_exists ('imagecreatetruecolor')) {
				$create	= 'imagecreatetruecolor';
				$copy	= 'imagecopyresampled';
			} else {
				$create	= 'imagecreate';
				$copy	= 'imagecopyresized';
			}

			$dstImg = $create ($this->width, $this->height);
			// png we can actually preserve transparency
			if ($this->imageType === 3) {
				imagealphablending ($dstImg, false);
				imagesavealpha ($dstImg, true);
			}

			$copy ($dstImg, $srcImg, 0, 0, $this->xAxis, $this->yAxis, $this->width, $this->height, $this->origWidth, $this->origHeight);

			// Show the image
			if ($this->dynamicOutput === true) {
				$this->imageDisplayGD($dstImg);
			} else if (!$this->imageSaveGD ($dstImg)) { // Or save it
				return false;
			}

			// Kill the file handles
			imagedestroy ($dstImg);
			imagedestroy ($srcImg);

			chmod ($this->fullDstPath, $this->filePermissions);

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Process Using ImageMagick
		 *
		 * This function will resize, crop or rotate
		 *
		 * @param	string
		 *
		 * @return	boolean
		 */
		public function imageProcessImagemagick ($action = 'resize') {
			// Do we have a vaild library path?
			if (empty ($this->libraryPath)) {
				$this->setError ('imglib_libpath_invalid');
				return false;
			}

			if (!preg_match ('/convert$/i', $this->libraryPath)) {
				$this->libraryPath = rtrim ($this->libraryPath, '/').'/convert';
			}

			// Execute the command
			$cmd = $this->libraryPath . ' -quality '.$this->quality;

			if ($action === 'crop') {
				$cmd .= ' -crop '.$this->width . 'x'.$this->height . '+' . $this->xAxis . '+' . $this->yAxis . ' "'. $this->fullSrcPath . '" "' . $this->fullDstPath . '" 2>&1';
			} else if ($action === 'rotate') {
				$angle = ($this->rotationAngle === 'hor' || $this->rotationAngle === 'vrt') ? '-flop' : '-rotate '.$this->rotationAngle;
				$cmd  .= ' '. $angle . ' "' . $this->fullSrcPath . '" "' . $this->fullDstPath . '" 2>&1';
			} else { // Resize
				if($this->maintainRatio === true) {
					$cmd .= ' -resize ' . $this->width . 'x' . $this->height . ' "' . $this->fullSrcPath . '" "' . $this->fullDstPath . '" 2>&1';
				} else 	{
					$cmd .= ' -resize ' . $this->width . 'x' . $this->height . '\! "' . $this->fullSrcPath . '" "' . $this->fullDstPath . '" 2>&1';
				}
			}

			$retval = 1;
			// exec() might be disabled
			if (function_usable('exec')) {
				@exec($cmd, $output, $retval);
			}

			// Did it work?
			if ($retval > 0) {
				$this->setError('imglib_image_process_failed');
				return false;
			}

			chmod ($this->fullDstPath, $this->filePermissions);

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Process Using NetPBM
		 *
		 * This function will resize, crop or rotate
		 *
		 * @param	string
		 *
		 * @return	boolean
		 */
		public function imageProcessNetpbm ($action = 'resize') {
			if (empty ($this->libraryPath)) {
				$this->setError('imglib_libpath_invalid');
				return false;
			}

			// Build the resizing command
			switch ($this->imageType) {
				case 1:
					$cmd_in  = 'giftopnm';
					$cmd_out = 'ppmtogif';
					break;
				case 2:
					$cmd_in	 = 'jpegtopnm';
					$cmd_out = 'ppmtojpeg';
					break;
				case 3:
					$cmd_in	= 'pngtopnm';
					$cmd_out = 'ppmtopng';
					break;
				default:
					continue;
			}

			if ($action === 'crop') {
				$cmd_inner = 'pnmcut -left '.$this->xAxis.' -top '.$this->yAxis.' -width '.$this->width.' -height '.$this->height;
			} else if ($action === 'rotate') {
				switch ($this->rotationAngle) {
					case 90:
						$angle = 'r270';
						break;
					case 180:
						$angle = 'r180';
						break;
					case 270:
						$angle = 'r90';
						break;
					case 'vrt':
						$angle = 'tb';
						break;
					case 'hor':
						$angle = 'lr';
						break;
					default:
						continue;
				}

				$cmd_inner = 'pnmflip -'.$angle.' ';
			} else { // Resize
				$cmd_inner = 'pnmscale -xysize '.$this->width.' '.$this->height;
			}

			$cmd = $this->libraryPath.$cmd_in.' '.$this->fullSrcPath.' | '.$cmd_inner.' | '.$cmd_out.' > '.$this->destFolder.'netpbm.tmp';

			$retval = 1;
			// exec() might be disabled
			if (function_usable ('exec')) {
				@exec($cmd, $output, $retval);
			}

			// Did it work?
			if ($retval > 0) {
				$this->setError ('imglib_image_process_failed');
				return false;
			}

			// With NetPBM we have to create a temporary image.
			// If you try manipulating the original it fails so
			// we have to rename the temp file.
			copy ($this->destFolder . 'netpbm.tmp', $this->fullDstPath);
			unlink ($this->destFolder.'netpbm.tmp');
			chmod ($this->fullDstPath, $this->filePermissions);
			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Rotate Using GD
		 *
		 * @return	boolean
		 */
		public function imageRotateGD () {
			// Create the image handle
			$srcImg = $this->imageCreateGD();
			if (!$srcImg) {
				return false;
			}

			// Set the background color
			// This won't work with transparent PNG files so we are
			// going to have to figure out how to determine the color
			// of the alpha channel in a future release.

			$white = imagecolorallocate ($srcImg, 255, 255, 255);

			// Rotate it!
			$dstImg = imagerotate ($srcImg, $this->rotationAngle, $white);

			// Show the image
			if ($this->dynamicOutput === true) {
				$this->imageDisplayGD ($dstImg);
			} else if ( ! $this->imageSaveGD($dstImg)) { // ... or save it
				return false;
			}

			// Kill the file handles
			imagedestroy ($dstImg);
			imagedestroy ($srcImg);

			chmod ($this->fullDstPath, $this->filePermissions);

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Create Mirror Image using GD
		 *
		 * This function will flip horizontal or vertical
		 *
		 * @return	boolean
		 */
		public function imageMirrorGD () {
			$srcImg = $this->imageCreateGD ();
			if (!$srcImg) {
				return false;
			}

			$width  = $this->origWidth;
			$height = $this->origHeight;

			if ($this->rotationAngle === 'hor') {
				for ($i = 0; $i < $height; $i++) {
					$left  = 0;
					$right = ($width - 1);

					while ($left < $right) {
						$cl = imagecolorat ($srcImg, $left, $i);
						$cr = imagecolorat ($srcImg, $right, $i);

						imagesetpixel ($srcImg, $left, $i, $cr);
						imagesetpixel ($srcImg, $right, $i, $cl);

						$left++;
						$right--;
					}
				}
			} else {
				for ($i = 0; $i < $width; $i++) {
					$top    = 0;
					$bottom = ($height - 1);

					while ($top < $bottom) {
						$ct = imagecolorat ($srcImg, $i, $top);
						$cb = imagecolorat ($srcImg, $i, $bottom);

						imagesetpixel ($srcImg, $i, $top, $cb);
						imagesetpixel ($srcImg, $i, $bottom, $ct);

						$top++;
						$bottom--;
					}
				}
			}

			// Show the image
			if ($this->dynamicOutput === true) {
				$this->imageDisplayGD ($srcImg);
			} else if ( ! $this->imageSaveGD($srcImg)) { // ... or save it
				return false;
			}

			// Kill the file handles
			imagedestroy ($srcImg);

			chmod ($this->fullDstPath, $this->filePermissions);

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Image Watermark
		 *
		 * This is a wrapper function that chooses the type
		 * of watermarking based on the specified preference.
		 *
		 * @return	boolean
		 */
		public function watermark() {
			return ($this->wmType === 'overlay') ? $this->overlayWatermark () : $this->textWatermark ();
		}

		// --------------------------------------------------------------------

		/**
		 * Watermark - Graphic Version
		 *
		 * @return	boolean
		 */
		public function overlayWatermark () {
			if (!function_exists('imagecolortransparent')) {
				$this->setError ('imglib_gd_required');
				return false;
			}

			// Fetch source image properties
			$this->getImageProperties ();

			// Fetch watermark image properties
			$props	   = $this->getImageProperties ($this->wmOverlayPath, true);
			$wmImgType = $props ['imageType'];
			$wmWidth   = $props ['width'];
			$wmHeight  = $props ['height'];

			// Create two image resources
			$wmImg = $this->imageCreateGD ($this->wmOverlayPath, $wmImgType);
			$srcImg = $this->imageCreateGD ($this->fullSrcPath);

			// Reverse the offset if necessary
			// When the image is positioned at the bottom
			// we don't want the vertical offset to push it
			// further down. We want the reverse, so we'll
			// invert the offset. Same with the horizontal
			// offset when the image is at the right

			$this->wmVrtAlignment = strtoupper ($this->wmVrtAlignment[0]);
			$this->wmHorAlignment = strtoupper ($this->wmHorAlignment[0]);

			if ($this->wmVrtAlignment === 'B') {
				$this->wmVrtOffset = ($this->wmVrtOffset * -1);
			}

			if ($this->wmHorAlignment === 'R') {
				$this->wmHorOffset = ($this->wmHorOffset * -1);
			}

			// Set the base x and y axis values
			$xAxis = ($this->wmHorOffset + $this->wmPadding);
			$yAxis = ($this->wmVrtOffset + $this->wmPadding);

			// Set the vertical position
			if ($this->wmVrtAlignment === 'M') {
				$yAxis += (($this->origHeight / 2) - ($wmHeight / 2));
			} else if ($this->wmVrtAlignment === 'B') {
				$yAxis += ($this->origHeight - $wmHeight);
			}

			// Set the horizontal position
			if ($this->wmHorAlignment === 'C') {
				$xAxis += ((($this->origWidth) / 2) - ($wmWidth / 2));
			} else if ($this->wmHorAlignment === 'R') {
				$xAxis += ($this->origWidth - $wmWidth);
			}

			// Build the finalized image
			if ($wmImgType === 3 && function_exists('imagealphablending')) {
				@imagealphablending ($srcImg, true);
			}

			// Set RGB values for text and shadow
			$rgba  = imagecolorat ($wmImg, $this->wmXTransp, $this->wmYTransp);
			$alpha = (($rgba & 0x7F000000) >> 24);

			// make a best guess as to whether we're dealing with an image with alpha transparency or no/binary transparency
			if ($alpha > 0) {
				// copy the image directly, the image's alpha transparency being the sole determinant of blending
				imagecopy ($srcImg, $wmImg, $xAxis, $yAxis, 0, 0, $wmWidth, $wmHeight);
			} else {
				// set our RGB value from above to be transparent and merge the images with the specified opacity
				imagecolortransparent ($wmImg, imagecolorat ($wmImg, $this->wmXTransp, $this->wmYTransp));
				imagecopymerge ($srcImg, $wmImg, $xAxis, $yAxis, 0, 0, $wmWidth, $wmHeight, $this->wmPpacity);
			}

			// We can preserve transparency for PNG images
			if ($this->imageType === 3) {
				imagealphablending($srcImg, false);
				imagesavealpha($srcImg, true);
			}

			// Output the image
			if ($this->dynamicOutput === true) {
				$this->imageDisplayGD ($srcImg);
			} else if ( ! $this->imageSaveGD($srcImg)) {  // ... or save it
				return false;
			}

			imagedestroy ($srcImg);
			imagedestroy ($wmImg);

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Watermark - Text Version
		 *
		 * @return	boolean
		 */
		public function textWatermark () {
			$srcImg = $this->imageCreateGD ();
			if (!$srcImg) {
				return false;
			}

			if ($this->wmUseTruetype === true && ! file_exists ($this->wmFontPath)) {
				$this->setError ('imglib_missing_font');
				return false;
			}

			// Fetch source image properties
			$this->getImageProperties ();

			// Reverse the vertical offset
			// When the image is positioned at the bottom
			// we don't want the vertical offset to push it
			// further down. We want the reverse, so we'll
			// invert the offset. Note: The horizontal
			// offset flips itself automatically

			if ($this->wmVrtAlignment === 'B') {
				$this->wmVrtOffset = ($this->wmVrtOffset * -1);
			}

			if ($this->wmHorAlignment === 'R') {
				$this->wmHorOffset = ($this->wmHorOffset * -1);
			}

			// Set font width and height
			// These are calculated differently depending on
			// whether we are using the true type font or not
			if ($this->wmUseTruetype === true) {
				if (empty($this->wmFontSize)) {
					$this->wmFontSize = 17;
				}

				if (function_exists ('imagettfbbox')) {
					$temp = imagettfbbox($this->wmFontSize, 0, $this->wmFontPath, $this->wmText);
					$temp = ($temp[2] - $temp[0]);

					$fontwidth = ($temp / strlen($this->wmText));
				} else {
					$fontwidth = ($this->wmFontSize - ($this->wmFontSize / 4));
				}

				$fontheight         = $this->wmFontSize;
				$this->wmVrtOffset += $this->wmFontSize;
			} else {
				$fontwidth  = imagefontwidth ($this->wmFontSize);
				$fontheight = imagefontheight ($this->wmFontSize);
			}

			// Set base X and Y axis values
			$xAxis = ($this->wmHorOffset + $this->wmPadding);
			$yAxis = ($this->wmVrtOffset + $this->wmPadding);

			if ($this->wmUseDropShadow === false) {
				$this->wmShadowDistance = 0;
			}

			$this->wmVrtAlignment = strtoupper($this->wmVrtAlignment[0]);
			$this->wmHorAlignment = strtoupper($this->wmHorAlignment[0]);

			// Set vertical alignment
			if ($this->wmVrtAlignment === 'M') {
				$yAxis += (($this->origHeight / 2) + ($fontheight / 2));
			} else if ($this->wmVrtAlignment === 'B') {
				$yAxis += ($this->origHeight - $fontheight - $this->wmShadowDistance - ($fontheight / 2));
			}

			// Set horizontal alignment
			if ($this->wmHorAlignment === 'R') {
				$xAxis += ($this->origWidth - ($fontwidth * strlen($this->wmText)) - $this->wmShadowDistance);
			} else if ($this->wmHorAlignment === 'C') {
				$xAxis += floor (($this->origWidth - ($fontwidth * strlen($this->wmText))) / 2);
			}

			if ($this->wmUseDropShadow) {
				// Offset from text
				$x_shad = ($xAxis + $this->wmShadowDistance);
				$y_shad = ($yAxis + $this->wmShadowDistance);

				/* Set RGB values for shadow
				 *
				 * First character is #, so we don't really need it.
				 * Get the rest of the string and split it into 2-length
				 * hex values:
				 */
				$drp_color = str_split(substr ($this->wmShadowColor, 1, 6), 2);
				$drp_color = imagecolorclosest ($srcImg, hexdec ($drp_color[0]), hexdec ($drp_color[1]), hexdec ($drp_color[2]));

				// Add the shadow to the source image
				if ($this->wmUseTruetype) {
					imagettftext ($srcImg, $this->wmFontSize, 0, $x_shad, $y_shad, $drp_color, $this->wmFontPath, $this->wmText);
				} else {
					imagestring ($srcImg, $this->wmFontSize, $x_shad, $y_shad, $this->wmText, $drp_color);
				}
			}

			/* Set RGB values for text
			 *
			 * First character is #, so we don't really need it.
			 * Get the rest of the string and split it into 2-length
			 * hex values:
			 */
			$txt_color = str_split (substr ($this->wmFontColor, 1, 6), 2);
			$txt_color = imagecolorclosest ($srcImg, hexdec($txt_color[0]), hexdec($txt_color[1]), hexdec($txt_color[2]));

			// Add the text to the source image
			if ($this->wmUseTruetype) {
				imagettftext ($srcImg, $this->wmFontSize, 0, $xAxis, $yAxis, $txt_color, $this->wmFontPath, $this->wmText);
			} else {
				imagestring ($srcImg, $this->wmFontSize, $xAxis, $yAxis, $this->wmText, $txt_color);
			}

			// We can preserve transparency for PNG images
			if ($this->imageType === 3) {
				imagealphablending ($srcImg, false);
				imagesavealpha ($srcImg, true);
			}

			// Output the final image
			if ($this->dynamicOutput === true) {
				$this->imageDisplayGD ($srcImg);
			} else {
				$this->imageSaveGD($srcImg);
			}

			imagedestroy ($srcImg);

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Create Image - GD
		 *
		 * This simply creates an image resource handle
		 * based on the type of image being processed
		 *
		 * @param	string
		 * @param	string
		 *
		 * @return	resource
		 */
		public function imageCreateGD ($path = '', $imageType = '') {
			if (empty ($path)) {
				$path = $this->fullSrcPath;
			}

			if (empty ($imageType)) {
				$imageType = $this->imageType;
			}

			switch ($imageType) {
				case 1:
					if (!function_exists('imagecreatefromgif')) {
						$this->setError(array('imglib_unsupported_imagecreate', 'imglib_gif_not_supported'));
						return false;
					}
					return imagecreatefromgif ($path);
				case 2:
					if (!function_exists('imagecreatefromjpeg')) {
						$this->setError(array('imglib_unsupported_imagecreate', 'imglib_jpg_not_supported'));
						return false;
					}
					return imagecreatefromjpeg($path);
				case 3:
					if (!function_exists ('imagecreatefrompng')) {
						$this->setError (array ('imglib_unsupported_imagecreate', 'imglib_png_not_supported'));
						return false;
					}
					return imagecreatefrompng ($path);
				default:
					$this->setError (array ('imglib_unsupported_imagecreate'));
					return false;
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Write image file to disk - GD
		 *
		 * Takes an image resource as input and writes the file
		 * to the specified destination
		 *
		 * @param	resource
		 *
		 * @return	boolean
		 */
		public function imageSaveGD ($resource) {
			switch ($this->imageType) {
				case 1:
					if (!function_exists ('imagegif')) {
						$this->setError (array('imglib_unsupported_imagecreate', 'imglib_gif_not_supported'));
						return false;
					}

					if (!@imagegif($resource, $this->fullDstPath)) {
						$this->setError ('imglib_save_failed');
						return false;
					}
					break;
				case 2:
					if (!function_exists ('imagejpeg')) {
						$this->setError(array('imglib_unsupported_imagecreate', 'imglib_jpg_not_supported'));
						return false;
					}

					if (!@imagejpeg($resource, $this->fullDstPath, $this->quality)) {
						$this->setError('imglib_save_failed');
						return false;
					}
					break;
				case 3:
					if (!function_exists ('imagepng')) {
						$this->setError(array('imglib_unsupported_imagecreate', 'imglib_png_not_supported'));
						return false;
					}

					if (!@imagepng ($resource, $this->fullDstPath)) {
						$this->setError('imglib_save_failed');
						return false;
					}
					break;
				default:
					$this->setError (array('imglib_unsupported_imagecreate'));
					return false;
			}

			return true;
		}

		/**
		 * Dynamically outputs an image
		 *
		 * @param	resource
		 *
		 * @return	void
		 */
		public function imageDisplayGD ($resource) {
			header ('Content-Disposition: filename='.$this->sourceImage.';');
			header ('Content-Type: '.$this->mimeType);
			header ('Content-Transfer-Encoding: binary');
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');

			switch ($this->imageType) {
				case 1:
					imagegif ($resource);
					break;
				case 2:
					imagejpeg ($resource, NULL, $this->quality);
					break;
				case 3:
					imagepng ($resource);
					break;
				default:
					echo 'Unable to display the image';
					break;
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Re-proportion Image Width/Height
		 *
		 * When creating thumbs, the desired width/height
		 * can end up warping the image due to an incorrect
		 * ratio between the full-sized image and the thumb.
		 *
		 * This function lets us re-proportion the width/height
		 * if users choose to maintain the aspect ratio when resizing.
		 *
		 * @return	void
		 */
		public function imageReproportion () {
			if (
				($this->width === 0 && $this->height === 0)
				|| $this->origWidth === 0 || $this->origHeight === 0
				|| (!ctype_digit ($this->width) && !ctype_digit ($this->height))
				|| !ctype_digit ($this->origWidth) || !ctype_digit ($this->origHeight)
			) {
				return;
			}

			// Sanitize
			$this->width  = intval ($this->width);
			$this->height = intval ($this->height);

			if ($this->masterDim !== 'width' && $this->masterDim !== 'height') {
				if ($this->width > 0 && $this->height > 0) {
					$this->masterDim = ((($this->origHeight/$this->origWidth) - ($this->height/$this->width)) < 0) ? 'width' : 'height';
				} else {
					$this->masterDim = ($this->height === 0) ? 'width' : 'height';
				}
			} else if (
				($this->masterDim === 'width' && $this->width === 0)
				|| ($this->masterDim === 'height' && $this->height === 0)
			) {
				return;
			}

			if ($this->masterDim === 'width') {
				$this->height = (int) ceil($this->width*$this->origHeight/$this->origWidth);
			} else {
				$this->width = (int) ceil($this->origWidth*$this->height/$this->origHeight);
			}
		}

		// --------------------------------------------------------------------

		/**
		 * Get image properties
		 *
		 * A helper function that gets info about the file
		 *
		 * @param	string
		 * @param	boolean
		 *
		 * @return	mixed
		 */
		public function getImageProperties ($path = '', $return = false) {
			// For now we require GD but we should
			// find a way to determine this using IM or NetPBM

			if (empty ($path)) {
				$path = $this->fullSrcPath;
			}

			if (!file_exists ($path)) {
				$this->setError ('imglib_invalid_path');
				return false;
			}

			$vals  = getimagesize ($path);
			$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');
			$mime  = (isset ($types[$vals[2]])) ? 'image/'.$types[$vals[2]] : 'image/jpg';

			if ($return === true) {
				return array(
					'width'     => $vals[0],
					'height'    => $vals[1],
					'imageType' => $vals[2],
					'sizeStr'   => $vals[3],
					'mimeType'  => $mime,
				);
			}

			$this->origWidth  = $vals[0];
			$this->origHeight = $vals[1];
			$this->imageType  = $vals[2];
			$this->sizeStr	  = $vals[3];
			$this->mimeType	  = $mime;

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Size calculator
		 *
		 * This function takes a known width x height and
		 * recalculates it to a new size. Only one
		 * new variable needs to be known
		 *
		 *	$props = array(
		 *			'width'		=> $width,
		 *			'height'	=> $height,
		 *			'new_width'	=> 40,
		 *			'new_height'	=> ''
		 *		);
		 *
		 * @param	array
		 *
		 * @return	void|array
		 */
		public function sizeCalculator ($vals) {
			if (!is_array ($vals)) {
				return;
			}

			$allowed = array('new_width', 'new_height', 'width', 'height');

			foreach ($allowed as $item) {
				if (empty ($vals[$item])) {
					$vals[$item] = 0;
				}
			}

			if ($vals['width'] === 0 || $vals['height'] === 0) {
				return $vals;
			}

			if ($vals['new_width'] === 0) {
				$vals['new_width'] = ceil ((($vals['width'] * $vals['new_height']) / $vals['height']));
			} else if ($vals['new_height'] === 0) {
				$vals['new_height'] = ceil ((($vals['new_width'] * $vals['height']) / $vals['width']));
			}

			return $vals;
		}

		// --------------------------------------------------------------------

		/**
		 * Explode sourceImage
		 *
		 * This is a helper function that extracts the extension
		 * from the sourceImage.  This function lets us deal with
		 * source_images with multiple periods, like: my.cool.jpg
		 * It returns an associative array with two elements:
		 * $array['ext']  = '.jpg';
		 * $array['name'] = 'my.cool';
		 *
		 * @param	array
		 *
		 * @return	array
		 */
		public function explodeName ($sourceImage) {
			$ext  = strrchr($sourceImage, '.');
			$name = ($ext === false) ? $sourceImage : substr ($sourceImage, 0, -strlen ($ext));

			return array('ext' => $ext, 'name' => $name);
		}

		// --------------------------------------------------------------------

		/**
		 * Is GD Installed?
		 *
		 * @return	boolean
		 */
		public function gdLoaded () {
			if (!extension_loaded ('gd')) {
				/* As it is stated in the PHP manual, dl() is not always available
				 * and even if so - it could generate an E_WARNING message on failure
				 */
				return (function_exists ('dl') && @dl('gd.so'));
			}

			return true;
		}

		// --------------------------------------------------------------------

		/**
		 * Get GD version
		 *
		 * @return	mixed
		 */
		public function gdVersion () {
			if (function_exists('gd_info')) {
				$gd_version = @gd_info ();
				return preg_replace ('/\D/', '', $gd_version['GD Version']);
			}

			return false;
		}

		// --------------------------------------------------------------------

		/**
		 * Set error message
		 *
		 * @param	string
		 *
		 * @return	void
		 */
		public function setError ($msg) {
			var_dump($msg);
		}

		// --------------------------------------------------------------------

		/**
		 * Show error messages
		 *
		 * @param	string
		 * @param	string
		 *
		 * @return	string
		 */
		public function displayErrors ($open = '<p>', $close = '</p>') {
			return (count($this->errorMsg) > 0) ? $open.implode($close.$open, $this->errorMsg).$close : '';
		}

	}
//@codingStandardsIgnoreEnd