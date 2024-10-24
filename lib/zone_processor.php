<?php

function getAllZones() {
    $allZones = shell_exec("whmapi1 --output=jsonpretty \
    listzones");

    $allZones = json_decode($allZones);

    $domainsZones=[];

    $i = 0;
    foreach( $allZones->data->zone as $zones){

        $domainsZones[$i] = $zones->domain;
        $i++;
    }

    return $domainsZones;
}

function readRegistry($zones) {
    $arrayZones = [];
    foreach ($zones as $zone) {
        $command = "whmapi1 dumpzone zone='$zone'";
        //logMessage($command);
    
        $registries = shell_exec($command);
    
        // Limpando o resultado do comando
        $registries = preg_split('/(?=Line:)/', $registries);
    
        $identRegistries = [];
        
        foreach ($registries as &$registry) {
            $registry = explode(PHP_EOL, trim($registry)); // Divide o texto em linhas
    
            // Array associativo para armazenar as chaves e valores
            $registryData = [];
    
            foreach ($registry as &$line) {
                $line = trim($line); // Remove espaços em branco do início e do fim
                if (strpos($line, ':') !== false) { // Verifica se a linha contém ':'
                    list($key, $value) = explode(':', $line, 2); // Divide a linha em chave e valor
                    $registryData[trim($key)] = trim($value); // Adiciona ao array removendo espaços em branco
                }
            }
    
            // Adiciona o array associativo ao array principal
            if (!empty($registryData)) {
                $identRegistries[] = $registryData; // Adiciona apenas se não estiver vazio
            }
        }
    
        //logMessage(print_r($identRegistries, true));
        $arrayZones[$zone] = $identRegistries;
    }
    return $arrayZones;
}