<?php

function zoneFormatter($zoneOptions, $requiredKeys)
{
    $zoneOptions = dataFomater($zoneOptions);

    // Definir as chaves esperadas
    $parsedOptions = [];

    // Definindo chaves validas
    $validKeys = ['dname', 'ttl', 'type', 'data'];

    // Verificar cada par chave=valor
    foreach ($zoneOptions as $option) {
        // Usar regex para verificar o formato chave=valor (somente letras minúsculas e números)
        if (!preg_match('/^([a-z]+)=([\w\.]+)$/', trim($option), $matches)) {
            logMessage('Invalid option format: ' . $option, 'error');
            exit(1);
        }

        // Separar chave e valor
        $key = $matches[1];
        $value = $matches[2];

        // Verificar se a chave é válida e se não foi repetida
        if (!in_array($key, $validKeys)) {
            logMessage('Invalid option key: ' . $key, 'error');
            exit(1);
        }
        if (isset($parsedOptions[$key])) {
            logMessage('Duplicate key found: ' . $key, 'error');
            exit(1);
        }

        // Armazenar o par chave=valor, com validações específicas
        if ($key == 'ttl') {
            // Verificar se o TTL contém apenas números
            if (!ctype_digit($value)) {
                logMessage('TTL must be numeric: ' . $value, 'error');
                exit(1);
            }
            // Converter o TTL para inteiro
            $value = (int) $value;
        }

        if ($key == 'type') {
            // converter type para maiúsculo
            $value = strtoupper($value);
        }

        // Armazenar o valor validado (com TTL como inteiro)
        $parsedOptions[$key] = $value;
    }

    // Verificar se todos os parâmetros obrigatórios estão presentes
    foreach ($requiredKeys as $key) {
        if (!isset($parsedOptions[$key])) {
            logMessage('Missing required key: ' . $key, 'error');
            exit(1);
        }
    }

    return $parsedOptions;
}

function dataFomater($zoneOptions)
{
    // Remover espaços antes e depois das vírgulas, se existirem
    $zoneOptions = preg_replace('/\s*,\s*/', ',', trim($zoneOptions));

    // Separar as opções por vírgula
    $zoneOptions = explode(',', $zoneOptions);

    return $zoneOptions;
}

function soaFormatter($zoneOptions)
{
    logMessage("zone options antes da formatação: " . print_r($zoneOptions, true));
    $zoneOptions = dataFomater($zoneOptions);
    logMessage("zone options depois da formatação: " . print_r($zoneOptions, true));

    
    // Definir as chaves esperadas
    $parsedOptions = [];

    // Definindo chaves validas
    $validKeys = ['ns', 'email', 'retry', 'ttl', 'refresh', 'expire'];

    // Verificar cada par chave=valor
    foreach ($zoneOptions as $option) {
        // Usar regex para verificar o formato chave=valor (chave com apenas letras e valor aceitando @, ponto, letras, números e underscore)
        if (!preg_match('/^([a-zA-Z]+)=([\w\.@]+)$/', trim($option), $matches)) {
            logMessage('Invalid option format: ' . $option, 'error');
            exit(1);
        }

        logMessage("esse é o matches: " . print_r($matches, true));

        // Separar chave e valor
        $key = $matches[1];
        $value = $matches[2];

        // Verificar se a chave é válida e se não foi repetida
        if (!in_array($key, $validKeys)) {
            logMessage('Invalid option key: ' . $key, 'error');
            exit(1);
        }
        if (isset($parsedOptions[$key])) {
            logMessage('Duplicate key found: ' . $key, 'error');
            exit(1);
        }

        // Armazenar o par chave=valor, com validações específicas
        if ($key == 'ttl' || $key == 'retry' || $key == 'refresh' || $key == 'expire') {
            logMessage("passando pela validação, $key - $value");
            // Verificar se o TTL contém apenas números
            if (!ctype_digit($value)) {
                logMessage('TTL, retry, refresh and expire must be numeric: ' . $value, 'error');
                exit(1);
            }
            // Converter para inteiro
            $value = (int) $value;
        }

        if ($key == 'email') {
            $value = str_replace('@', '.', $value);
        }

        // Armazenar o valor validado (com TTL como inteiro)
        $parsedOptions[$key] = $value;
    }

    return $parsedOptions;
}


function getSOARecord($domain)
{
    // Verifica se o domínio foi fornecido
    if (empty($domain)) {
        logMessage("None domain found. Exiting...", 'error');
        exit(1);
    }

    // Comando dig SOA para o domínio na máquina local (127.0.0.1)
    $command = "dig @127.0.0.1 $domain soa";

    // Executa o comando e captura a saída
    $digResult = shell_exec($command);

    // Verifica se houve erro ao executar o comando
    if (is_null($digResult)) {
        logMessage("Error to execute dig command. Exiting...", 'error');
        exit(1);
    }

    // Variável para armazenar o resultado SOA
    $soaRecord = null;
    $insideAnswerSection = false;

    // Separando o resultado linha a linha
    $digResult =  explode(PHP_EOL, $digResult);

    // Percorre a saída em busca da "ANSWER SECTION" e do registro SOA
    foreach ($digResult as $line) {
        // Detecta a linha de início da "ANSWER SECTION"
        if (strpos($line, "ANSWER SECTION") !== false) {
            $insideAnswerSection = true;
            continue;
        }

        // Se estiver dentro da "ANSWER SECTION", busca a linha do SOA
        if ($insideAnswerSection && strpos($line, "SOA") !== false) {
            $columns = preg_split('/[^a-zA-Z0-9.]+/', $line);
            $columns = array_filter($columns);
            $soaRecord = $columns[6];
            break; // Já achamos o SOA, podemos parar
        }
    }

    // Caso encontre o registro SOA, retorna-o, senão retorna mensagem de erro
    if ($soaRecord) {
        if (strlen($soaRecord) === 10) {
            return $soaRecord;
        } else {
            logMessage("SOA for domain: $domain not in valid form SOA: $soaRecord", 'error');
            exit(1);
        }
    } else {
        logMessage("SOA for domain: $domain not found! Exiting...", 'error');
        exit(1);
    }
}

function commandExecutor($command, $domain)
{
    $noResponse = true;

    if (isset(OPTIONS["skip"])) {
        $noResponse = false;
    }


    unset($response);
    while ($noResponse) {
        if (!isset($response)) {
            echo ('command to be executed: ' . $command . "\n");
        }
        $response = readline("Do you really want to execute de command for zone: " . $domain . "? [Y/n]: ");

        switch ($response) {
            case 'Y':
                $noResponse = false;
                break;
            case 'n':
                return 'cancelled';
                break;
            default:
                echo ('invalid option' . "\n");
                break;
        }
    }

    // Execução do comando
    logMessage('Executing command: ' . $command);
    $output = shell_exec($command);

    // Verificar o resultado da execução e logar o retorno
    if ($output === null) {
        logMessage('Failed to execute command for domain: ' . $domain, 'error');
        return 0;
    } else {
        return $output;
    }
}

// Função para extrair o valor de result
function getResult($yamlString)
{
    if (preg_match('/result:\s*(\d+)/', $yamlString, $matches)) {
        return (int) $matches[1];
    }
    return null;
}

// Função para verificar se a string "serial number" está presente na reason
function hasSerialNumberInReason($yamlString)
{
    // Primeiro, extraímos o conteúdo do campo 'reason'
    if (preg_match('/reason:\s*(.*)/', $yamlString, $matches)) {
        $reason = trim($matches[1]);
        // Verificamos se a substring "serial number" está presente
        return strpos($reason, 'serial number') !== false;
    }
    return false;
}
