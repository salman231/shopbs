<?php

$root_path = $_SERVER['DOCUMENT_ROOT'];
$ary = array('page_cache','cache','report');
foreach($ary as $val){
     $cachpagepath  = $root_path.'/var/'.$val.'/*';
    $files = glob($cachpagepath); // get all file names
    foreach($files as $file){ // iterate files
    	
    		if (!is_writable($file)) {
    			chmod($file, 0777);
    		}
    		deleteDirectory($file);
    	
    }
}
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}
?>