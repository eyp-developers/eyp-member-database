<?php

namespace Modules;

/**
 * The Files module
 */
class Files extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Set supported actions
        $this->_actions = [
            'POST' => [
                '/upload' => 'upload'
            ]
        ];
    }

    /**
     * Uploads a file to the server
     *
     * @return void
     */
    public function upload() {

        // Get the transmitted data
        if (!isset($_POST['imageData'])) {
            \Helpers\Response::error(\Helpers\Response::$E_SAVE_FAILED);
            return;
        }
        $data = $_POST['imageData'];

        // Check data type        
        preg_match("#^data:image\/(.*);base64,#", $data, $matches);

        $fileendings = [
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png'
        ];

        if(count($matches) < 2 || !isset($fileendings[$matches[1]])) {
            \Helpers\Response::error(\Helpers\Response::$E_SAVE_FAILED);
            return;
        }

        // Sanitize image data
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data));

        // Save image
        $fileending = $fileendings[$matches[1]];
        $filename = uniqid('img-'.date('Ymd').'-') . '.' . $fileending;

        $file = fopen(join(DIRECTORY_SEPARATOR, [dirname(getcwd()), "uploads", $filename]), "w");
        fwrite($file, $data);
        fclose($file);

        \Helpers\Response::success([
            'url' => "uploads/$filename"
        ]);
    }
}

?>