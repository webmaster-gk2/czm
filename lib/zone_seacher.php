<?php


function searchDomain($zones, $wantedDomain) {
    // Explode a string em um array com base nas vírgulas
    $wantedDomain = explode(',', $wantedDomain);

    // Define os caracteres válidos (alfa-numéricos, hífens e ponto)
    $validCharsPattern = '/^[a-zA-Z0-9.-]+$/';

    // Itera sobre os domínios desejados
    foreach ($wantedDomain as $index => $domain) {
        // Remove espaços em branco ao redor do domínio
        $domain = trim($domain);

        // Verifica se o domínio contém apenas caracteres válidos
        if (!preg_match($validCharsPattern, $domain)) {
            // Se encontrar caracteres inválidos, retorna um erro
            logMessage("invalid characters found in domain: $domain");
        }

        // Atualiza o valor do domínio no array limpo
        $wantedDomain[$index] = $domain;
    }

    $i = 0;
    foreach($wantedDomain  as $domain){
        $key = false;
        $key = array_search($domain, $zones);

        if($key){
            $zonesFound[$i] = $zones[$key];
            $i++;
        } else {
            logMessage("None zone found for domain: $domain", 'warning');
        }
    }
    
    return $zonesFound;
}

function searchRegistries($arrayZones, $parsedOptions) {
    $zonesFound = [];

    foreach ($arrayZones as $domain => $zoneData) {
        if (searchInZone($zoneData, $parsedOptions)) {
            $zonesFound[] = $domain;  // Adiciona ao array diretamente
        }
    }

    return $zonesFound;
}

function getLine($arrayZones, $parsedOptions){
    $zonesFound = [];

    foreach ($arrayZones as $domain => $zoneData) {
        $registry = searchInZone($zoneData, $parsedOptions);
        if($registry){
            $zonesFound[$domain] = $registry;
        }
        unset($registryAndId);
    }

    return $zonesFound;
}


function searchInZone($zoneData, $parsedOptions) {
    $registryFound = [];

    $i = 0;
    foreach ($zoneData as $registry) {

        if(!isset($registry['type'])){
            continue;
        }

        // Verifica se o tipo de registro e outros critérios básicos são válidos
        if ($parsedOptions['type'] == $registry['type'] && 
            (!isset($parsedOptions['dname']) || $parsedOptions['dname'] == $registry['name']) &&
            (!isset($parsedOptions['ttl']) || $parsedOptions['ttl'] == $registry['ttl'])) {
            
            // Se o campo 'data' está presente, executa comparações adicionais
            if (isset($parsedOptions['data'])) {
                switch ($parsedOptions['type']) {
                    case 'NS':
                        if ($parsedOptions['data'] == $registry['nsdname']) {
                            //logMessage(print_r($registry, true));
                            $registryFound[$i] = $registry['Line'];
                            $i++;
                        }
                        break;
                    case 'A':
                        if ($parsedOptions['data'] == $registry['address']) {
                            //logMessage(print_r($registry, true));
                            $registryFound[$i] = $registry['Line'];
                            $i++;
                        }
                        break;
                    case 'CNAME':
                        if ($parsedOptions['data'] == $registry['cname']) {
                            //logMessage(print_r($registry, true));
                            $registryFound[$i] = $registry['Line'];
                            $i++;
                        }
                        break;
                    case 'TXT':
                        if ($parsedOptions['data'] == $registry['txtdata']) {
                            //logMessage(print_r($registry, true));
                            $registryFound[$i] = $registry['Line'];
                            $i++;
                        }
                        break;
                    case 'TXT':
                        if ($parsedOptions['data'] == $registry['exchange']) {
                            //logMessage(print_r($registry, true));
                            $registryFound[$i] = $registry['Line'];
                            $i++;
                        }
                        break;
                    default:
                        throw new Exception('This type of registry is not supported.');
                        exit(1);
                        break;
                }
            } else {
                //logMessage(print_r($registry, true));
                $registryFound[$i] = $registry['Line'];
                $i++;
            }
        }
    }
    if (empty($registryFound[0])){
        return false;
    }
    return $registryFound;
}
