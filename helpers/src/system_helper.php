<?php

if ( !function_exists('silent_require')){
	/**
	 * Use a specific private scope for each require so variable are not availables in global scope
	 * 
	 * @param string $file Path to required file
	 * @param array $params Parameters array
	 *
	 */
	function silent_require($file,$params = array()){
		
		if(!is_file($file))
			throw new InvalidArgumentException(sprintf('File do not exists at %s'),$file);

		extract($params);
		return require($file);
	}
}


if ( !function_exists('glob_recursive'))
{
    
    /**
     * Same as glob function but recursively
     *
     * @param string $pattern 
     *
     * @param int|const $flag
     * Does not support flag GLOB_BRACE
     *
     * @return array 
     */
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
       
        foreach (glob(dirname($pattern).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, glob_recursive($dir.DIRECTORY_SEPARATOR.basename($pattern), $flags));
        }
       
        return $files;
    }
}

if ( ! function_exists('global_require')){
	/**
	 * Load files dynamically
	 *
	 * @param string $path File path or dir path
	 * @param array $filename If $path is a dir, filenames or regex of required files
	 * @param int $maxdepth Maximum depth to search in
	 * @param array $params Parameters to include in the included files
	 */
	function global_require($path, $filename = array(), $maxdepth = null, $params = array()){
		static $depth = 0;
		$depth++;
		$nargs = count(func_get_args());
		if(!is_array($path))
			$path = array($path);
		
		foreach($path as $filepath){
			$glob = glob($filepath);
			if(empty($glob))
				trigger_error(sprintf('Path(s) %s do not exists',implode($path)),E_USER_NOTICE);

			foreach ($glob as $file){
				
				$file = realpath($file);
				
				if(is_file($file)){
					if(!is_array($params))
						$params = array($params);
					
					if(!is_included($file))
						silent_require($file,$params);
				}
				else if(
					($maxdepth == null
					|| $depth <= $maxdepth)
					&& is_dir($file) 
					&& $file != '.' 
					&& $file != '..'
					){
					if(empty($filename)){
						$path = realpath($file).DIRECTORY_SEPARATOR.'*';
						magic_require($path , $filename ,$maxdepth);
					}
					else{
						if(!is_array($filename))
							$filename = array($filename);
						foreach($filename as $name){
							$path = realpath($file).DIRECTORY_SEPARATOR.$name;
							magic_require($path , $filename ,$maxdepth, $params);
						}
					}
					
				}	
			}
			$depth = 0;
		}
	}
}

if( !function_exists('is_included') ){

	/**
	 * Check if a file is included
	 *
	 * @param string $file File path to check
	 *
	 * @return bool
	 */
	function is_included($file){
		$files = get_included_files();
		return (bool) in_array(realpath($file),$files);
	}
}

if( !function_exists('clean')){
	/**
	 * Clean all output
	 */
	function clean(){
		ob_flush();
		flush();
	}
}


if( !function_exists('check')){
	/**
	 * Check that a variable exists and check its value too if $value is filled
	 *
	 * @param &$var Trick which give  a value even if the variable do not exists
	 * @param $value Value to check on the variable
	 *
	 * @return bool
	 */
	function check( &$var, $value = null){
		if(1 == func_num_args())
			return ($var !== null);
		else
			return ($var === $value);
	}
}