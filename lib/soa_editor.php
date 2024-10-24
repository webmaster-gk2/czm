<?php



function soaEditor() {
    $parsedOptions = soaFormatter(OPTIONS['se']);
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
        // Montar o comando whmapi1 para adicionar os registros DNS
        if (empty($parsedOptions['ttl'])){
            $ttl = '';
        } else {
            $ttlValue = $parsedOptions['ttl'];
            $ttl = "ttl=$ttlValue";
        }

        $command = sprintf(
            "whmapi1 editzonerecord domain=%s line=4 name=%s. $ttl type=SOA ",
            $domain,
            $domain,
        );

        foreach($parsedOptions as $key => $value ){
            switch ($key) {
                case 'ns':
                    $command .= "mname='$value' ";
                    break;
                case 'email':
                    $command .= "rname='$value' ";
                default:
                    $command .= "$key=$value ";
            }
        }
        $output = commandExecutor($command, $domain);

        if ($output && getResult($output) === 1) {
            logMessage('Command executed successfully for domain: ' . $domain);
            echo('Command executed successfully for domain: ' . $domain . "\n");
        } elseif ($output == 'cancelled' ) {
            continue;
        } else {
            logMessage('Failed to execute command for domain: ' . $domain, 'error');
            echo('Failed to execute command for domain: ' . $domain . ' more details in log.' . "\n");
        }
    }
}
