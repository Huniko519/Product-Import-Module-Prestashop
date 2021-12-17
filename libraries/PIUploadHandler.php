<?php
/**
 * Overrides carrier shipping with Table Rate Shipping
 *
 * Table Rate Shipping by Kahanit(https://www.kahanit.com/) is licensed under a
 * Creative Creative Commons Attribution-NoDerivatives 4.0 International License.
 * Based on a work at https://www.kahanit.com/.
 * Permissions beyond the scope of this license may be available at https://www.kahanit.com/.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 * @version   1.0.4.0
 */

require_once(dirname(__FILE__) . '/magento/framework/File/Uploader.php');
use Magento\Framework\File\Uploader;

/**
 * Class PIUploadHandler
 *
 * @package Kahanit\TableRateShipping\Helper
 */
class PIUploadHandler
{
    protected $options;

    protected $response;

    public function __construct($options = null)
    {
        $this->options = [
            'upload_dir'                       => '',
            'script_url'                       => '',
            'upload_url'                       => '',
            'param_name'                       => 'files',
            'access_control_allow_origin'      => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods'     => ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'access_control_allow_headers'     => ['Content-Type', 'Content-Range', 'Content-Disposition']
        ];
        if ($options) {
            $this->options = $options + $this->options;
        }
        $this->response = [];
        $this->initialize();
    }

    protected function initialize()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->get();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post();
                break;
            case 'DELETE':
                $this->delete();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    public function head()
    {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->sendAccessControlHeaders();
        }
        $this->sendContentTypeHeader();
    }

    public function get()
    {
        $file_name = $this->getFileNameParam();

        if (Tools::getValue('download', 0)) {
            $this->download($file_name);

            return '';
        }

        if ($file_name) {
            $response = [$this->getSingularParamName() => $this->getFileObject($file_name)];
        } else {
            $response = [$this->options['param_name'] => $this->getFileObjects()];
        }

        return $this->generateResponse($response);
    }

    public function post()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $this->delete();

            return '';
        }

        $files = [];

        try {
            $uploader = new Uploader($this->options['param_name'] . '[0]');
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowedExtensions(['txt']);
            $save = $uploader->save($this->options['upload_dir']);
            $files[] = $this->getFileObject($save['file']);
        } catch (\Exception $e) {
            $files[] = (object)['error' => $e->getMessage()];
        }

        $response = [$this->options['param_name'] => $files];

        return $this->generateResponse($response);
    }

    public function delete()
    {
        $file_name = $this->getFileNameParam();
        $file_path = $this->getUploadPath($file_name);
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        $response = [$file_name => $success];

        return $this->generateResponse($response);
    }

    public function download($file_name)
    {
        if (!$this->isValidFileObject($file_name)) {
            $this->header('HTTP/1.1 404 Not Found');

            return;
        }

        $file_path = $this->getUploadPath($file_name);

        if (file_exists($file_path) && is_readable($file_path)) {
            $this->header('Content-Description: File Transfer');
            $this->header('Content-Type: application/octet-stream');
            $this->header('Content-Disposition: attachment; filename=' . basename($file_path));
            $this->header('Expires: 0');
            $this->header('Cache-Control: must-revalidate');
            $this->header('Pragma: public');
            $this->header('Content-Length: ' . filesize($file_path));

            readfile($file_path);
        } else {
            echo 'file not found';
        }

        exit;
    }

    public function generateResponse($content)
    {
        $this->response = $content;

        return $content;
    }

    public function getResponse()
    {
        return $this->response;
    }

    protected function getFileObject($file_name)
    {
        if ($this->isValidFileObject($file_name)) {
            $file = new \stdClass();
            $file->name = $file_name;
            $file->size = $this->getFileSize($this->getUploadPath($file_name));
            $file->url = $this->getDownloadUrl($file->name);
            $file->deleteUrl = $this->options['script_url']
                . $this->getQuerySeparator($this->options['script_url'])
                . $this->getSingularParamName()
                . '=' . rawurlencode($file->name);
            $file->deleteType = 'DELETE';

            return $file;
        }

        return null;
    }

    protected function getFileObjects($iteration_method = 'getFileObject')
    {
        $upload_dir = $this->getUploadPath();

        if (!is_dir($upload_dir)) {
            return [];
        }

        return array_values(array_filter(array_map([$this, $iteration_method], scandir($upload_dir))));
    }

    protected function isValidFileObject($file_name)
    {
        $file_path = $this->getUploadPath($file_name);

        if (is_file($file_path)
            && $file_name[0] !== '.'
            && strtolower(pathinfo($file_path, PATHINFO_EXTENSION) == 'txt')
        ) {
            return true;
        }

        return false;
    }

    protected function getFileNameParam()
    {
        $name = $this->getSingularParamName();

        return $this->basename(stripslashes(Tools::getValue($name)));
    }

    protected function getSingularParamName()
    {
        return substr($this->options['param_name'], 0, -1);
    }

    protected function getFileSize($file_path, $clear_stat_cache = false)
    {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }

        return $this->fixIntegerOverflow(filesize($file_path));
    }

    protected function getQuerySeparator($url)
    {
        return strpos($url, '?') === false ? '?' : '&';
    }

    protected function getUploadPath($file_name = null)
    {
        return $this->options['upload_dir'] . $file_name;
    }

    protected function getDownloadUrl($file_name)
    {
        return $this->options['upload_url'] . rawurlencode($file_name);
    }

    protected function basename($filepath, $suffix = null)
    {
        $splited = preg_split('/\//', rtrim($filepath, '/ '));

        return substr(basename('X' . $splited[count($splited) - 1], $suffix), 1);
    }

    protected function fixIntegerOverflow($size)
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }

        return $size;
    }

    protected function header($str)
    {
        header($str);
    }

    protected function sendAccessControlHeaders()
    {
        $this->header('Access-Control-Allow-Origin: '
            . $this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            . ($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            . implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            . implode(', ', $this->options['access_control_allow_headers']));
    }

    protected function sendContentTypeHeader()
    {
        $this->header('Vary: Accept');
        if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }
}
