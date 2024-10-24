<?php

function updater(){
    $passedRegistries = OPTIONS['up'];

    logMessage(print_r($passedRegistries, true));

    if(strpos($passedRegistries, '//') !== false){
        $passedRegistries = explode('//',$passedRegistries);
    } else {
        logMessage('missing parameters, zones must be divided by "//"');
        exit(1);
    }

    logMessage(print_r($passedRegistries, true));
    

    $registryToBeUpdated = zoneFormatter($passedRegistries[0], ['type']);

    $UpdaterRegistry = zoneFormatter($passedRegistries[1],['type', 'data']);

    $zones = getAllZones();
    $zonesFound = getAllZones();

    
    if (isset(OPTIONS['d'])) {
        unset($zonesFound);
        $zonesFound = [];
        $zonesFound = searchDomain($zones, OPTIONS['d']);
        unset($zones);
        $zones = $zonesFound;
    }

    if (isset(OPTIONS['s'])) {
        $seachOptions = zoneFormatter(OPTIONS['s'], ['type']);
        unset($zonesFound);
        $zonesFound = [];
        $zonesData = readRegistry($zones);
        $zonesFound = searchRegistries($zonesData,$seachOptions);
        unset($zones);
        $zones = $zonesFound;
    }
    
    unset($zonesFound);
    $zonesFound = [];
    $zonesData = readRegistry($zones);
    $zonesFound = getLine($zonesData, $registryToBeUpdated);
    unset($zones);

    $zones = $zonesFound;

    foreach ($zones as $domain => $entriesLines) {
       
        // utilização de valores padrões caso não são passados
        $dname = (empty($UpdaterRegistry['dname'])) ? $domain.'.' : $UpdaterRegistry['dname'];
        $ttl = (empty($UpdaterRegistry['ttl'])) ? 14400 : $UpdaterRegistry['ttl'];

        // Obter o serial e o domínio atual da zona
        $soa = getSOARecord($domain); // Função para obter o serial do arquivo da zona
        
        foreach ($entriesLines as $line_index) {
            // correção da linha
            $line_index = $line_index - 1;
            $soaError = true;
            $retries = 0;
            while($soaError) {
                // Montar o comando whmapi1 para adicionar os registros DNS
                $command = sprintf(
                    "whmapi1 mass_edit_dns_zone zone='%s' serial='%s' edit='{\"line_index\":%s, \"dname\":\"%s\", \"ttl\":%d, \"record_type\":\"%s\", \"data\":[\"%s\"]}'",
                    $domain,
                    $soa,
                    $line_index,
                    $dname,
                    $ttl,
                    $UpdaterRegistry['type'],
                    $UpdaterRegistry['data']
                );

                $output = commandExecutor($command, $domain);

                if ($output && getResult($output) === 1) {
                    $soaError = false;
                    logMessage('Command executed successfully for domain: ' . $domain);
                    echo('Command executed successfully for domain: ' . $domain . "\n");
                } elseif ($output == 'cancelled' ) {
                    continue 2;
                } else {
                    if (hasSerialNumberInReason($output) && $retries < 3) {
                        echo("SOA doesen't match retring... \n");
                        logMessage("SOA doesen't match, retring...");
                        $retries++;
                        $soa++;
                    } else {
                        $soaError = false;
                        logMessage('Failed to execute command for domain: ' . $domain, 'error');
                        echo('Failed to execute command for domain: ' . $domain . ' more details in log.' . "\n");
                    }
                }
            }
            $soa++;
        }
    }
}

