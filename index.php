<?php
$time = -microtime(true);
require_once "importer.php";

date_default_timezone_set('UTC');

$blog = new \Poorcode\Blog("./Posts", "./posts.cache");
echo $blog->run();
$time += microtime(true);
?><!-- rendered in <?php echo 1000*$time; ?> ms -->