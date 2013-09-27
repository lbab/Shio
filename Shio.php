<?php
/**
 * Image compression ran tru command line using optipng and jpegoptim
 *
 * @author Pierre Galvez
 */
class Shio {

	private $imageType;
	private $source;
	
	const TMP_FILENAME_APPEND = '_tmp';
	
	/**
	 * optipng command statement
	 * 
	 * options:
	 * -quiet
	 *	Run in quiet mode.
	 * 
	 * @var const
	 */
	const OPTIPNG_CMD = 'optipng -quiet %s';
	
	/**
	 * jpegtran command statement
	 * 
	 * options:
	 * 
	 * @var const
	 */
	const JPEGOPTIM_CMD = 'jpegoptim %s';
	
	/**
	 * Constructor
	 * 
	 * @param string $source
	 * @param string $type Empty string will check for the extension filename from $source or $destination.
	 */
	public function __construct( $source, $type = '' ) {
		$this->source = $source;
		$this->setImageType( $type );
	}

	/**
	 * Executes the legitimate image to optimize
	 */
	public function optimise() {
		self::checkRequirements();
		
		switch( $this->imageType ) {
			case 'png':
				shell_exec( $this->command( self::OPTIPNG_CMD ) );
				break;
			case 'jpg':
				shell_exec( $this->command( self::JPEGOPTIM_CMD ) );
				break;
			case 'jpeg':
				shell_exec( $this->command( self::JPEGOPTIM_CMD ) );
				break;
			default:
				// no image compression for other
				break;
		}
	}
	
	/**
	 * Set the suitable command line statement
	 * 
	 * @param string $cmdStr
	 */
	protected function command( $cmdStr ) {
		return sprintf( $cmdStr, $this->source );
	}
	
	/**
	 * Sets the image type or get the extension filename from source
	 * 
	 * @param string $imageType
	 */
	protected function setImageType( $imageType ) {
		$type = '';
		if( !empty( $imageType ) ) {
			$type = $imageType;
		} else {
			$filename = $this->source;
			$type = strtolower( end( explode( '.' , $filename ) ) );
		}
		$this->imageType = $type;
	}
	
	/**
	 * Checks if optipng and jpegoptim are installed
	 * 
	 * @throws Exception
	 */
	public function checkRequirements() {
		if( $this->imageType == 'png' && !is_string( shell_exec( 'optipng -v' ) ) ) {
			throw new Exception( 'Please install optipng first!' );
		}
		if( $this->imageType == 'jpg' && !is_string( shell_exec( 'jpegoptim -V' ) ) ) {
			echo gettype(shell_exec( 'jpegoptim -V' ));
			throw new Exception( 'Please install jpegoptim first!' );
		}
		if( $this->imageType == 'jpeg' && !is_string( shell_exec( 'jpegoptim -V' ) ) ) {
			throw new Exception( 'Please install jpegoptim first!' );
		}
	}
	
	/**
	 * Boolean requirements check
	 */
	public function isRequirementsInstalled() {
		try {
			self::checkRequirements();
		} catch( Exception $e ) {
			return false;
		} 
		return true;		
	}
	
	/**
	 * Batch images compression
	 * 
	 * @param string $directory
	 * @param boolean $isRecursive
	 */
	public static function batch( $directory, $isRecursive = false ) {
		if( !is_dir( $directory ) ) {
			throw new Exception( 'Directory "' . $directory . '" doesn\'t exist.' );
		}
		self::batchCompress( self::fetchImages( $directory, $isRecursive ) );
	}
	
	/**
	 * Gets all png|jpg|jpeg images from defined directory
	 * 
	 * @param string $directory
	 * @param boolean $isRecursive
	 */
	protected function fetchImages( $directory, $isRecursive ) {
		$images = array();
		if( $isRecursive === false ) {
		  	$iterator = new DirectoryIterator( $directory );
		  	foreach ( $iterator as $fileinfo ) {
		  		if ( $fileinfo->isFile() && preg_match( '/\.(png|jpg|jpeg)$/i', $fileinfo->getFilename() ) ) {
		  			array_push($images, $fileinfo->getPathname() );
		  		}
		  	}
		} else {
			$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory ) );
			while( $iterator->valid() ) {
			    if (!$iterator->isDot() && preg_match( '/\.(png|jpg|jpeg)$/i', $iterator->current()->getFilename() ) ) {
				    array_push($images, $iterator->current()->getPathname() );
			    }
			    $iterator->next();
			}
		}
		return $images;
	}
	
	/**
	 * Does the compression of images
	 * 
	 * @param array $images
	 */
	protected function batchCompress( $images ) {
		foreach( $images as $image ) {
			$optimise = new self( $image );
			$optimise->optimise();
		}
	}
	
	
	
	
	
	
	
	
	
	
}
