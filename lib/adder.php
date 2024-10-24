<?php



function adder() {
    $parsedOptions = zoneFormatter(OPTIONS['add'],['type', 'data']);
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


    foreach ($zones as $domain) {    
        // utilização de valores padrões caso não são passados
        $dname = (empty($parsedOptions['dname'])) ? $domain.'.' : $parsedOptions['dname'];
        $ttl = (empty($parsedOptions['ttl'])) ? 14400 : $parsedOptions['ttl'];

        // Obter o serial e o domínio atual da zona
        $soa = getSOARecord($domain); // Função para obter o serial do arquivo da zona
        $retries = 0;
        $soaError = true;
        while($soaError) {
            // Montar o comando whmapi1 para adicionar os registros DNS
            $command = sprintf(
                "whmapi1 mass_edit_dns_zone zone='%s' serial='%s' add='{\"dname\":\"%s\", \"ttl\":%d, \"record_type\":\"%s\", \"data\":[\"%s\"]}'",
                $domain,
                $soa,
                $dname,
                $ttl,
                $parsedOptions['type'],
                $parsedOptions['data']
            );

            $output = commandExecutor($command, $domain);

            if ($output && getResult($output) === 1) {
                $soaError = false;
                logMessage('Command executed successfully for domain: ' . $domain);
                echo('Command executed successfully for domain: ' . $domain . "\n");
            } elseif ($output == 'cancelled' ) {
                continue 2;
            } else {
                if (hasSerialNumberInReason($output) && $retries < 10) {
                    echo("SOA doesen't match for domain: $domain, retring... \n");
                    logMessage("SOA doesen't match for domain: $domain, retring...");
                    $retries++;
                    $soa++;
                } else {
                    $soaError = false;
                    logMessage('Failed to execute command for domain: ' . $domain, 'error');
                    echo('Failed to execute command for domain: ' . $domain . ' more details in log.');
                }
            }
        }
    }
}
