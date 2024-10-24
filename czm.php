#!/usr/bin/php -q
<?php

require_once(__DIR__.'/lib/loader.php');

libLoader();

// Configurações iniciais
set_error_handler('logError');
set_exception_handler('logException');

// Definir as opções válidas
define("OPTIONS", getopt("h", ["add:", "up:", "s:", "d:", "se:", "skip"]));

// Comandos principais permitidos
$mainCommands = ['add', 'up', 'h', 'se'];

// Filtrar comandos principais que foram passados
$activeMainCommands = array_filter($mainCommands, fn($cmd) => isset(OPTIONS[$cmd]));

// Verificar se mais de um comando principal foi passado
if (count($activeMainCommands) > 1) {
    echo "Erro: Mais de um comando principal foi passado. Use apenas um entre 'add', 'up', 'h' ou 'se'.\n";
    exit(1);
}

// Verificar se pelo menos um comando principal foi passado
if (count($activeMainCommands) === 0) {
    echo "Erro: Nenhum comando principal foi passado. Use 'add', 'up', 'h' ou 'se'.\n";
    help(); // Chamar função de ajuda para mostrar uso correto
    exit(1);
}

// Verificar se foram passados argumentos desconhecidos
$validOptions = array_merge($mainCommands, ['s', 'd', 'skip']);
foreach (array_keys(OPTIONS) as $option) {
    if (!in_array($option, $validOptions)) {
        echo "Erro: Argumento inválido ou desconhecido: '$option'.\n";
        help(); // Chamar função de ajuda para mostrar uso correto
        exit(1);
    }
}

// Continuar com o fluxo normal do programa
if (isset(OPTIONS['add'])) {
    adder();
} elseif (isset(OPTIONS['up'])) {
    updater();
} elseif (isset(OPTIONS['h'])) {
    help();
} elseif (isset(OPTIONS['se'])) {
    soaEditor();
} else {
    help();
}

?>

