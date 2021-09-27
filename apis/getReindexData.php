<?php
$output ='';
$path = getcwd().'/../';
exec("cd $path");
exec("$path php bin/magento indexer:reindex",$output);
echo $data = json_encode($output, TRUE);
?>

