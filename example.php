<?php
use Namdevel\AnimeMaker;
require('src/animeMaker.php');

$source = './tes.jpg';
$animeMaker = new AnimeMaker($source);
$animeMaker->createAnime();
?>
