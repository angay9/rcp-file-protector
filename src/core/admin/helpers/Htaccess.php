<?php

namespace RcpFileProtector\Core\Admin\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Htaccess
{
    /**
     * Add htaccess rules
     *
     * @param string $pluginPath
     * @return void
     */
    public function addRules($pluginPath)
    {
        $htaccessFile = $this->getHtaccessFilePath();

        if ($this->isHtaccessWritable($htaccessFile) && !$this->rulesAreadyExist($htaccessFile)) {
            $rules = $this->getRules($pluginPath);

            file_put_contents($htaccessFile, $rules, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Remove rules from htaccess
     *
     * @return void
     */
    public function removeRules()
    {
        $htaccessFile = $this->getHtaccessFilePath();

        if ($this->isHtaccessWritable($htaccessFile) && $this->rulesAreadyExist($htaccessFile)) {
            $content = file_get_contents($htaccessFile);

            $content = preg_replace('/(?s)# RCP File Protector Rules.*?# End RCP File Protector Rules/', "", $content);

            file_put_contents($htaccessFile, $content);
        }
    }

    /**
     * Get htaccess file path
     *
     * @return string
     */
    public function getHtaccessFilePath()
    {
        $homePath = get_home_path();
        $htaccessFile = $homePath . '.htaccess';

        return $htaccessFile;
    }

    /**
     * Check if htaccess file is writable
     *
     * @param string $htaccessFile
     * @return boolean
     */
    public function isHtaccessWritable($htaccessFile) 
    {
        if (!file_exists($htaccessFile)) {
    		error_log( '.htaccess file doesn\'t exist');
    		return '.htaccess file doesn\'t exist';
    	}

    	error_log( '.htaccess is writeable: ' . is_writable($htaccessFile));
    	if (is_writable($htaccessFile)) {
    		return true;
    	}

    	@chmod($htaccessFile, 0666);

    	if (!is_writable($htaccessFile)) {
    		error_log( 'Please ask host manager to grant write permission for .htaccess file.');
    		return 'Please ask host manager to grant write permission for .htaccess file.';
    	}

    	return true;
    }

    /**
     * Get htacces rules
     *
     * @param string $pluginPath
     * @return string
     */
    public function getRules($pluginPath)
    {
        $rules = " # RCP File Protector Rules
<IfModule mod_rewrite.c>
    RewriteCond %{REQUEST_FILENAME} -s
    # RewriteRule ^wp-content/uploads/(.*)$ dl-file.php?file=/$1 [QSA,L]
    RewriteRule ^wp-content/uploads/(.*)$ {$pluginPath}/file-guard.php?file=/$1 [QSA,L]
</IfModule>
 # End RCP File Protector Rules";
        
        return $rules;
    }

    /**
     * Check if rules already exist
     *
     * @param string $htaccessFile
     * @return boolean
     */
    public function rulesAreadyExist($htaccessFile)
    {
        return strpos(file_get_contents($htaccessFile), "# RCP File Protector Rules") !== false;
    }
}