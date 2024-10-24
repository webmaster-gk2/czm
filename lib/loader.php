<?php

// loader.php
function libLoader() {
    $libFiles = scandir(__DIR__);
    $libFilesPhp = array_filter($libFiles, function($file) {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php';
    });
    foreach ($libFilesPhp as $file) {
        require_once __DIR__."/".$file;
    }
}

//Carrega as configurações
function configLoader() {
    $configPath = __DIR__ . '/../czm.conf';
    
    if(!file_exists($configPath)){
      echo("Config file not found in " . realpath(dirname($configPath)) . PHP_EOL);
      exit(1);
    }
      return parse_ini_file($configPath, true);
  }