<?php
$srcRoot = dirname(__FILE__)."/GLExchange";
$buildRoot = dirname(__FILE__);
 
$phar = new Phar($buildRoot . "/glexchange.phar", 
	FilesystemIterator::CURRENT_AS_FILEINFO |     	FilesystemIterator::KEY_AS_FILENAME, "glexchange.phar");
$phar->buildFromDirectory($srcRoot);

?>