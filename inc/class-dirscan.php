<?php
/**
 * DirScan: A nice directory scanner
 *
 *  @package DirScan
 *  @author Thapelo Moeti
 *  @version 0.1
 */

/**
 *  Main DirScan class
 */
class DirScan {
    /**
     *  Save the path info for the current instance
     *
     *  @var string $path
     */
    private $path = '';

    /**
     *  Lists all the files in the current directory
     *
     *  @var array $files
     */
    private $files = array();

    /**
     *  List all the directories in the current directory
     *
     *  @var array $directories
     */
    private $directories = array();

    /**
     *  class constructor
     *
     *  @see DirScan::setPath
     *
     *  @param string   $path   The path to scan
     */
    function __construct( $path ) {
        $this->setPath( $path );
    }

    /**
     *  method to set working path
     *
     *  @param string   $path   Path to scan
     */
    public function setPath( $path ) {
        if( ! realpath( $path ) && ! is_dir( $path ) ) {
            throw new Exception( 'INVALID PATH: ' . $path );
        }

        $this->path = realpath( $path );

        $this->scanDir();
    }

    /**
     *  The scanning and sorting of files in working directory
     *
     */
    private function scanDir() {
        $files = scandir( $this->path );

        foreach( $files as $file ) {
            if( $file == '.' || $file == '..' )
                continue;

            $path = $this->path . DIRECTORY_SEPARATOR . $file;

            $stat = stat( $path );

            $file_info = array( 'name' => $file, 'path' => $path, 'size' => $stat[ 'size' ], 'modified' => $stat[ 'mtime' ] );

            if( is_dir( $path ) ) {
                $this->directories[] = $file_info;
            } elseif( is_file( $path ) ) {
                $this->files[] = $file_info;
            }
        }
    }

    /**
     *  Get only the files
     *
     *  @return array   all the files
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     *  Get only the directories
     *
     *  @return array   all the directories
     */
    public function getDirectories() {
        return $this->directories;
    }

    /**
     *  Get all files and directories
     *
     *  @return array   all the files and directories
     */
    public function getAll() {
        return array_merge( $this->files, $this->directories );
    }
}