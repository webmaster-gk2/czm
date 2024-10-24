<?php


define("LOG_FILE", __DIR__ . '/../czm.log'); 
define("LOG_DIR", realpath(dirname(LOG_FILE)));


if(!is_dir(LOG_DIR)) {
  mkdir(LOG_DIR, 0755, true);
}

// pegando o timestamp para impressão de logs
function getCurrentTimestamp() {
    return date('[Y-m-d H:i:s]');
  }

// Envia erros para os logs
function logError($errno, $errstr, $errfile, $errline) {
	$errorMsg = getCurrentTimestamp() . " Erro [$errno]: $errstr em $errfile na linha $errline" . PHP_EOL;
	file_put_contents(LOG_FILE, $errorMsg, FILE_APPEND);
	exit(1);
}

// Envia as exceções para os logs
function logException($exception) {
	$errorMsg = getCurrentTimestamp() . " Exceção não capturada: " . $exception->getMessage() . " em " . $exception->getFile() . " na linha " . $exception->getLine() . PHP_EOL;
	file_put_contents(LOG_FILE, $errorMsg, FILE_APPEND);
	exit(1);
}

// Envia menssagens de log
function logMessage($message, $level = 'info') {
	file_put_contents(LOG_FILE, getCurrentTimestamp() . " [$level] $message" . PHP_EOL, FILE_APPEND);
}