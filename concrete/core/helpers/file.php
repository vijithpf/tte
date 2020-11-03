<?php

/**
 * File helper.
 *
 * Functions useful for working with files and directories.
 *
 * Used as follows:
 * <code>
 * $file = Loader::helper('file');
 * $path = 'http://www.concrete5.org/tools/get_latest_version_number';
 * $contents = $file->getContents($path);
 * echo $contents;
 * </code>
 *
 * @category Concrete
 *
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Helper_File
{

    const ERR_NO_FILE      = 1;
    const ERR_SIZE_LIMIT   = 2;
    const ERR_COUNT_LIMIT  = 3;
    const ERR_INVALID_TYPE = 4;
    const ERR_UNKNOWN      = 5;

    public function getDirectoryContents($dir, $ignoreFilesArray = array(), $recursive = false)
    {
        $env = Environment::get();

        return $env->getDirectoryContents($dir, $ignoreFilesArray, $recursive);
    }

    /**
     * Removes the extension of a filename, uncamelcases it.
     *
     * @param string $filename
     *
     * @return string
     */
    public function unfilename($filename)
    {
        // removes the extension and makes it look nice
        $txt = Loader::helper('text');

        return substr($txt->unhandle($filename), 0, strrpos($filename, '.'));
    }

    /**
     * Recursively copies all items in the source directory or file to the target directory.
     *
     * @param string $source Source to copy
     * @param string $target Place to copy the source
     * @param int    $mode   What to chmod the file to
     */
    public function copyAll($source, $target, $mode = null)
    {
        if (is_dir($source)) {
            if ($mode == null) {
                @mkdir($target, DIRECTORY_PERMISSIONS_MODE);
                @chmod($target, DIRECTORY_PERMISSIONS_MODE);
            } else {
                @mkdir($target, $mode);
                @chmod($target, $mode);
            }

            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if (substr($entry, 0, 1) === '.') {
                    continue;
                }

                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    $this->copyAll($Entry, $target . '/' . $entry, $mode);
                    continue;
                }

                copy($Entry, $target . '/' . $entry);
                if ($mode == null) {
                    @chmod($target . '/' . $entry, $this->getCreateFilePermissions($target)->file);
                } else {
                    @chmod($target . '/' . $entry, $mode);
                }
            }

            $d->close();
        } else {
            if ($mode == null) {
                $mode = $this->getCreateFilePermissions(dirname($target))->file;
            }
            copy($source, $target);
            chmod($target, $mode);
        }
    }

    /**
     * Removes all files from within a specified directory.
     *
     * @param string $source Directory
     */
    public function removeAll($source)
    {
        $r = @glob($source);
        if (is_array($r)) {
            foreach ($r as $file) {
                if (is_dir($file)) {
                    $this->removeAll("$file/*");
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Takes a path to a file and sends it to the browser, streaming it, and closing the HTTP connection afterwards. Basically a force download method.
     *
     * @param string $file
     *
     * @return bool
     */
    public function forceDownload($file)
    {
        session_write_close();
        ob_clean();
        header('Content-type: application/octet-stream');
        $filename = basename($file);
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Content-Length: ' . filesize($file));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Transfer-Encoding: binary');
        header('Content-Encoding: plainbinary');

        // This code isn't ready yet. It will allow us to no longer force download

        /*
        $h = Loader::helper('mime');
        $mimeType = $h->mimeFromExtension($this->getExtension($file));
        header('Content-type: ' . $mimeType);
        */

        $chunk  = 1024 * 1024;
        $handle = fopen($file, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunk);
            print $buffer;
        }

        fclose($handle);
        exit;
    }

    /**
     * Returns the full path to the temporary directory.
     *
     * @return string
     */
    public function getTemporaryDirectory()
    {
        if (defined('DIR_TMP')) {
            return DIR_TMP;
        }

        if (!is_dir(DIR_BASE . '/files/tmp')) {
            @mkdir(DIR_BASE . '/files/tmp', DIRECTORY_PERMISSIONS_MODE);
            @chmod(DIR_BASE . '/files/tmp', DIRECTORY_PERMISSIONS_MODE);
            @touch(DIR_BASE . '/files/tmp/index.html');
        }

        if (is_dir(DIR_BASE . '/files/tmp') && is_writable(DIR_BASE . '/files/tmp')) {
            return DIR_BASE . '/files/tmp';
        }

        if ($temp = getenv('TMP')) {
            return $temp;
        }
        if ($temp = getenv('TEMP')) {
            return $temp;
        }
        if ($temp = getenv('TMPDIR')) {
            return $temp;
        }

        $temp = tempnam(__FILE__, '');
        if (file_exists($temp)) {
            unlink($temp);

            return dirname($temp);
        }

        return false;
    }

    /**
     * Adds content to a new line in a file. If a file is not there it will be created.
     *
     * @param string $filename
     * @param string $content
     *
     * @return int
     */
    public function append($filename, $content)
    {
        return file_put_contents($filename, $content, FILE_APPEND);
    }

    /**
     * Just a consistency wrapper for file_get_contents
     * Should use curl if it exists and fopen isn't allowed (thanks Remo).
     *
     * @param $file
     * @param int|string $timeout
     *
     * @return string $contents
     */
    public function getContents($file, $timeout = 5)
    {
        $url = @parse_url($file);
        if (isset($url['scheme']) && isset($url['host'])) {
            if (ini_get('allow_url_fopen')) {
                $ctx = stream_context_create(array(
                                                 'http' => array('timeout' => $timeout),
                                             ));
                if ($contents = @file_get_contents($file, 0, $ctx)) {
                    return $contents;
                }
            }

            if (function_exists('curl_init')) {
                $curl_handle = curl_init();

                // Check to see if there are proxy settings
                if (Config::get('HTTP_PROXY_HOST') != null) {
                    @curl_setopt($curl_handle, CURLOPT_PROXY, Config::get('HTTP_PROXY_HOST'));
                    @curl_setopt($curl_handle, CURLOPT_PROXYPORT, Config::get('HTTP_PROXY_PORT'));

                    // Check if there is a username/password to access the proxy
                    if (Config::get('HTTP_PROXY_USER') != null) {
                        @curl_setopt($curl_handle, CURLOPT_PROXYUSERPWD, Config::get('HTTP_PROXY_USER') . ':' . Config::get('HTTP_PROXY_PWD'));
                    }
                }

                curl_setopt($curl_handle, CURLOPT_URL, $file);
                curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
                $contents  = curl_exec($curl_handle);
                $http_code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
                curl_close($curl_handle);
                if ($http_code == 404) {
                    return false;
                }

                return $contents;
            }
        } else {
            if ($contents = @file_get_contents($file)) {
                return $contents;
            }
        }

        return false;
    }

    /**
     * Removes contents of the file.
     *
     * @param $file
     *
     * @return int
     */
    public function clear($file)
    {
        return file_put_contents($file, '');
    }

    /**
     * Cleans up a filename and returns the cleaned up version.
     *
     * @param string $file
     *
     * @return string @file
     */
    public function sanitize($file)
    {
        // Let's build an ASCII-only version of name, to avoid filesystem-specific encoding issues.
        $asciiName = Loader::helper('text')->asciify($file);
        // Let's keep only letters, numbers, underscore and dots.
        $asciiName = trim(preg_replace(array('/[\\s]/', '/[^0-9A-Z_a-z-.]/'), array('_', ''), $asciiName));
        // Trim underscores at start and end
        $asciiName = trim($asciiName, '_');
        if (!strlen(str_replace('.', '', $asciiName))) {
            // If the resulting name is empty (or we have only dots in it)
            $asciiName = md5($file);
        } elseif (preg_match('/^\.\w+$/', $asciiName)) {
            // If the resulting name is only composed by the file extension
            $asciiName = md5($file) . $asciiName;
        }

        return $asciiName;
    }

    /**
     * Returns the extension for a file name.
     *
     * @param string $filename
     *
     * @return string $extension
     */
    public function getExtension($filename)
    {
        $extension = end(explode('.', $filename));

        return $extension;
    }

    /**
     * Takes a path and replaces the files extension in that path with the specified extension.
     *
     * @param string $filename
     * @param string $extension
     *
     * @return string $newFileName
     */
    public function replaceExtension($filename, $extension)
    {
        if (($dot = strrpos($filename, '.')) !== false) {
            return substr($filename, 0, $dot + 1) . $extension;
        }

        return $filename;
    }

    /**
     * returns an object with two permissions modes (octal):
     * one for files: $res->file
     * and another for directories: $res->dir.
     *
     * @param string $path (optional)
     *
     * @return StdClass
     */
    public function getCreateFilePermissions($path = null)
    {
        try {
            if (!isset($path)) {
                $path = DIR_BASE . '/files';
            }

            if (!is_dir($path)) {
                $path = @dirname($path);
            }
            $perms = @fileperms($path);

            if (!$perms) {
                throw new Exception(t('An error occured while attempting to determine file permissions.'));
            }
            clearstatcache();
            $dir_perms = substr(decoct($perms), 1);

            $file_perms = '0';
            $parts[]    = substr($dir_perms, 1, 1);
            $parts[]    = substr($dir_perms, 2, 1);
            $parts[]    = substr($dir_perms, 3, 1);
            foreach ($parts as $p) {
                if (intval($p) % 2 == 0) {
                    $file_perms .= $p;
                    continue;
                }
                $file_perms .= intval($p) - 1;
            }
        } catch (Exception $e) {
            return false;
        }
        $res       = new stdClass();
        $res->file = intval($file_perms, 8);
        $res->dir  = intval($dir_perms, 8);

        return $res;
    }

    /**
     * A convenience method to import a file from HTML file input
     * to concrete5 File Manager. Acts as a wrapper for
     * uploading a single file for concrete5.
     *
     * @param string $inputName        The name of the input in the HTML form
     * @param array  $allowedMimeTypes An array of allowed file types. For e.g., to allow only images, pass ['image/']
     * @param int    $sizeLimit        The maximum size allowed to be uploaded in bytes. To avoid any size limit, pass 0.
     *
     * @return int|FileVersion Returns a number in case of error (One of the error constants in FileHelper). Otherwise, returns an instance of FileVersion.
     */
    public function importFile($inputName, $allowedMimeTypes = array(), $sizeLimit = 0)
    {
        if (!isset($_FILES[$inputName])) {
            return self::ERR_NO_FILE;
        }

        $file = $_FILES[$inputName];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return self::ERR_NO_FILE;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return self::ERR_SIZE_LIMIT;

                case UPLOAD_ERR_EXTENSION:
                    return self::ERR_INVALID_TYPE;

                default:
                    return self::ERR_UNKNOWN;
            }
        }

        if (count($allowedMimeTypes) > 0) {
            $mimeType = @mime_content_type($file['tmp_name']);
            $valid    = false;
            foreach ($allowedMimeTypes as $allowedMimeType) {
                if (strpos($mimeType, $allowedMimeType) !== false) {
                    $valid = true;
                }
            }
            if (!$valid) {
                return self::ERR_INVALID_TYPE;
            }
        }

        if ($sizeLimit > 0) {
            if ($file['size'] > $sizeLimit) {
                return self::ERR_SIZE_LIMIT;
            }
        }

        Loader::library('file/importer');
        $fi = new FileImporter();
        $fv = $fi->import($file['tmp_name'], $file['name']);

        if (!($fv instanceof FileVersion)) {
            switch ($fv) {
                case FileImporter::E_PHP_NO_FILE:
                    return self::ERR_NO_FILE;

                case FileImporter::E_FILE_INVALID_EXTENSION:
                    return self::ERR_INVALID_TYPE;

                case FileImporter::E_PHP_FILE_EXCEEDS_HTML_MAX_FILE_SIZE:
                case FileImporter::E_PHP_FILE_EXCEEDS_UPLOAD_MAX_FILESIZE:
                    return self::ERR_SIZE_LIMIT;

                default:
                    return self::ERR_UNKNOWN;
            }
        }

        return $fv;
    }

    /**
     * A convenience method to import multiple files from HTML file input
     * array to concrete5 File Manager. Acts as a wrapper for
     * uploading multiple files for concrete5.
     *
     * @param string $inputName        The name of the input array in the HTML form.
     * @param array  $allowedMimeTypes An array of allowed file types. For e.g., to allow only images, pass ['image/']. Keep it empty to allow any file.
     * @param int    $countLimit       The maximum number of files allowed to be uploaded. 0 for unlimited files.
     * @param int    $sizeLimit        The maximum size allowed to be uploaded in bytes. To avoid any size limit, pass 0.
     *
     * @return array|int Returns a number in case of error (one of the error constants in FileHelper). Otherwise, returns an array of FileVersion.
     */
    public function importMultipleFiles($inputName, $allowedMimeTypes = array(), $countLimit = 0, $sizeLimit = 0)
    {
        if (!isset($_FILES[$inputName])) {
            return array();
        }

        $files = $_FILES[$inputName];

        if (!$files) {
            return array();
        }

        foreach ($files['error'] as $errorCode) {
            if ($errorCode !== UPLOAD_ERR_OK && $errorCode !== UPLOAD_ERR_NO_FILE) {
                switch ($errorCode) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        return self::ERR_SIZE_LIMIT;

                    case UPLOAD_ERR_EXTENSION:
                        return self::ERR_INVALID_TYPE;

                    default:
                        return self::ERR_UNKNOWN;
                }
            }
        }

        if (count($allowedMimeTypes) > 0) {
            foreach ($files['tmp_name'] as $pointer) {
                $mimeType = @mime_content_type($pointer);
                $valid    = false;
                foreach ($allowedMimeTypes as $allowedMimeType) {
                    if (strpos($mimeType, $allowedMimeType) !== false) {
                        $valid = true;
                    }
                }
                if (!$valid) {
                    return self::ERR_INVALID_TYPE;
                }
            }
        }

        if ($countLimit > 0 && count($files['tmp_name']) > $countLimit) {
            return self::ERR_COUNT_LIMIT;
        }

        if ($sizeLimit > 0) {
            $totalSize = 0;
            foreach ($files['size'] as $size) {
                $totalSize += $size;
            }
            if ($totalSize > $sizeLimit) {
                return self::ERR_SIZE_LIMIT;
            }
        }

        Loader::library('file/importer');
        $fi  = new FileImporter();
        $fvs = array();
        foreach ($files['tmp_name'] as $i => $pointer) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fv = $fi->import($pointer, $files['name'][$i]);

                if (!($fv instanceof FileVersion)) {
                    switch ($fv) {
                        case FileImporter::E_PHP_NO_FILE:
                            return self::ERR_NO_FILE;

                        case FileImporter::E_FILE_INVALID_EXTENSION:
                            return self::ERR_INVALID_TYPE;

                        case FileImporter::E_PHP_FILE_EXCEEDS_HTML_MAX_FILE_SIZE:
                        case FileImporter::E_PHP_FILE_EXCEEDS_UPLOAD_MAX_FILESIZE:
                            return self::ERR_SIZE_LIMIT;

                        default:
                            return self::ERR_UNKNOWN;
                    }
                }

                $fvs[] = $fv;
            }
        }

        return $fvs;
    }

    /**
     * @param int $errCode One of the error constants in FileHelper
     *
     * @return string Returns a generic error message explaining the $errCode.
     */
    public function getErrorMessage($errCode)
    {
        switch ($errCode) {
            case self::ERR_NO_FILE:
                return 'No file uploaded.';

            case self::ERR_SIZE_LIMIT:
                return 'Uploaded file is too large.';

            case self::ERR_COUNT_LIMIT:
                return 'Too many files uploaded.';

            case self::ERR_INVALID_TYPE:
                return 'Invalid file uploaded.';

            case self::ERR_UNKNOWN:
            default:
                return 'An error occurred during file upload.';
        }
    }
}
