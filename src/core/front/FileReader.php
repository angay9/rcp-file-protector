<?php

namespace RcpFileProtector\Core\Front;

use RcpFileProtector\Core\Front\Exceptions\FileNotFoundException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FileReader 
{
    /**
     * Read file
     *
     * @param string $file
     * @return void
     */
    public function read($file)
    {
        list($basedir) = array_values(
			array_intersect_key(
				wp_upload_dir(), array('basedir' => 1)
			)
		) + array(NULL);
		
		$file =  rtrim($basedir,'/').'/'.str_replace('..', '', !is_null($file) ? $file : '');
		$file = realpath($file);
		
		if (!$basedir || !is_file($file)) {
			throw new FileNotFoundException(
                '404. File Not Found',
                404
			);
		}

		$mime = wp_check_filetype($file);
		
		if ( false === $mime[ 'type' ] && function_exists( 'mime_content_type' ) ) {
			$mime[ 'type' ] = mime_content_type( $file );
		}

		if ( $mime[ 'type' ] ) {
			$mimetype = $mime[ 'type' ];
		}
		else {
			$mimetype = 'image/' . substr( $file, strrpos( $file, '.' ) + 1 );
		}

		if ($this->isVideo($mime['type']) && isset($_SERVER['HTTP_RANGE'])) {

			return $this->rangeDownload($file);
		}

		header( 'Content-Type: ' . $mimetype ); // always send this
		if ( false === strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) )
			header( 'Content-Length: ' . filesize( $file ) );
		$lastModified = gmdate( 'D, d M Y H:i:s', filemtime( $file ) );
		$etag = '"' . md5( $lastModified ) . '"';
		header( "Last-Modified: $lastModified GMT" );
		header( 'ETag: ' . $etag );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );
		// Support for Conditional GET
		$clientEtag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;
		if( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
			$_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;
		$clientLastModified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
		// If string is empty, return 0. If not, attempt to parse into a timestamp
		$clientModifiedTimestamp = $clientLastModified ? strtotime( $clientLastModified ) : 0;
		// Make a timestamp for our most recent modification...
		$modifiedTimestamp = strtotime($lastModified);
		if ( ( $clientLastModified && $clientEtag )
			? ( ( $clientModifiedTimestamp >= $modifiedTimestamp) && ( $clientEtag == $etag ) )
			: ( ( $clientModifiedTimestamp >= $modifiedTimestamp) || ( $clientEtag == $etag ) )
			) {
			status_header( 304 );
			exit;
		}

		// If we made it this far, just serve the file
		readfile( $file );

	}
    
    /**
     * Check if file is video
     *
     * @param string $mime_type
     * @return boolean
     */
	protected function isVideo($mime_type) 
	{
		return strstr($mime_type, "video/");
	}

	/**
	 * Range Download File
	 * Taken from: https://stackoverflow.com/a/16419103
	 * 
	 * @param string $file
	 * @return void
	 */
	protected function rangeDownload($file)
	{
        $fp = @fopen($file, 'rb');

        $size   = filesize($file); // File size
        $length = $size;           // Content length
        $start  = 0;               // Start byte
        $end    = $size - 1;       // End byte
        // Now that we've gotten so far without errors we send the accept range header
        /* At the moment we only support single ranges.
         * Multiple ranges requires some more work to ensure it works correctly
         * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
         *
         * Multirange support annouces itself with:
         * header('Accept-Ranges: bytes');
         *
         * Multirange content must be sent with multipart/byteranges mediatype,
         * (mediatype = mimetype)
         * as well as a boundry header to indicate the various chunks of data.
         */
        header("Accept-Ranges: 0-$length");
        // header('Accept-Ranges: bytes');
        // multipart/byteranges
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end   = $end;

            // Extract the range string
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            // Make sure the client hasn't sent us a multibyte range
            if (strpos($range, ',') !== false){
                // (?) Shoud this be issued here, or should the first
                // range be used? Or should the header be ignored and
                // we output the whole content?
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            } // fim do if
            // If the range starts with an '-' we start from the beginning
            // If not, we forward the file pointer
            // And make sure to get the end byte if spesified
            if ($range{0} == '-'){
                // The n-number of the last bytes is requested
                $c_start = $size - substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            } // fim do if
            /* Check the range and make sure it's treated according to the specs.
             * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
             */
            // End bytes can not be larger than $end.
            $c_end = ($c_end > $end) ? $end : $c_end;
            // Validate the requested range and return an error if it's not correct.
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size){
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            } // fim do if

            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1; // Calculate new content length
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        } // fim do if

        // Notify the client the byte range we'll be outputting
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");

        // Start buffered download
        $buffer = 1024 * 8;
        while (!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end){
                // In case we're only outputtin a chunk, make sure we don't
                // read past the length
                $buffer = $end - $p + 1;
            } // fim do if

            set_time_limit(0); // Reset time limit for big files
            echo fread($fp, $buffer);
            flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        } // fim do while

        fclose($fp);
    } // fim do function
	
}