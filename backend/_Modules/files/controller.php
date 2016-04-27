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
        preg_match("#^data:(.*);base64,#", $data, $matches);

        $fileendings = [
            'image/jpg' => 'jpg',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'text/csv' => 'csv'
        ];

        if(count($matches) < 2 || !isset($fileendings[$matches[1]])) {
            \Helpers\Response::error(\Helpers\Response::$E_SAVE_FAILED);
            return;
        }

        // Sanitize data
        $data = preg_replace('#^data:\w+/\w+;base64,#i', '', $data);
        $data = base64_decode($data);

        // Save file
        $fileending = $fileendings[$matches[1]];
        $filename = uniqid('upload-'.date('Ymd').'-') . '.' . $fileending;

        $file = fopen(join(DIRECTORY_SEPARATOR, [dirname(getcwd()), "uploads", $filename]), "w");
        fwrite($file, $data);
        fclose($file);

        \Helpers\Response::success([
            'url' => "uploads/$filename"
        ]);
    }
}

?>