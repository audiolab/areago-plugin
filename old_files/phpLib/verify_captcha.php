<?php

  require_once dirname(__FILE__) . '/captcha/securimage.php';
  $img = new Securimage;

  if ($img->check($_POST["captcha"]) == false) {
    $errors = "Falso";
  }else{
  	$errors="Correcto";
  }
echo $errors;



?>