<?php

function help() {
    echo '
    CZM (Zone Management) Tool - Usage Guide:

    Main Commands:
    --add  - Add a new DNS zone.
    --up   - Update an existing DNS zone.
    -h     - Display this help message.

    ### Primary Commands ###

    --add:
        Syntax: czm --add dname=value,ttl=value,type=value,data=value
        Description: Adds a new DNS record to the zone list. Use the format key=value for the parameters.
        Parameters:
            - dname: (optional) Domain name. Defaults to "domain" if not specified.
            - ttl:   (optional) Time to Live (TTL). Defaults to 14400 seconds.
            - type:  (required) Record type (e.g., A, CNAME, MX, etc.).
            - data:  (required) Record data (e.g., IP address, hostname).

        Example:
            czm --add dname=example.com,ttl=3600,type=A,data=192.168.0.1

    --up:
        Syntax: czm --up type=value,data=value//dname=value,ttl=value,type=value,data=value
        Description: Replaces an existing DNS record with a new one. The command uses two sets of key=value pairs separated by `//`. The first set defines the record to be replaced, and the second defines the replacement.
        Parameters (for both original and replacement records):
            - dname: (optional) Domain name.
            - ttl:   (optional) Time to Live (TTL).
            - type:  (required) Record type.
            - data:  (required) Record data.

        Example:
            czm --up type=A,data=192.168.0.1//dname=example.com,ttl=3600,type=A,data=10.0.0.1

    --se:
        Syntax: czm --se ttl=14400,ns=ns.server.com,email=email@example.com,retry=1800,refresh=3600,expire=3600
        Description: Updates the SOA (Start of Authority) record for the zone with the specified key=value pairs.
        Parameters:
            - ttl:    (optional) Time to live.
            - ns:     (optional) Nameserver.
            - email:  (optional) Contact email for the domain.
            - retry:  (optional) Retry interval for zone transfers.
            - refresh: (optional) Refresh interval for zone updates.
            - expire: (optional) Expiration time for zone data.

    ### Additional Options ###

    --d:
        Syntax: czm [--add|--up] --d domain1,domain2
        Description: Specifies one or more domains for the command (either --add or --up). The command will be applied to all listed domains.
        
        Example:
            czm --add dname=example.com,type=A,data=10.0.0.1 --d gk27.com,gk50.com

    --s:
        Syntax: czm --add dname=value,ttl=value,type=value,data=value -s dname=value,ttl=value,type=value,data=value
        Description: This option is used with the --add command. It adds a new record only to zones where a specific record (defined after --s) already exists.
        Parameters (for the filter record):
            - dname: (optional) Domain name.
            - ttl:   (optional) Time to Live.
            - type:  (required) Record type.
            - data:  (optional) Record content.

        Example:
            czm --add dname=example.com,ttl=3600,type=A,data=192.168.0.1 -s dname=example.com,ttl=14400,type=MX,data=mail.example.com

    ### Command Examples ###

    1. Add a new DNS record:
        czm --add dname=example.com,ttl=3600,type=A,data=192.168.0.1

    2. Update an existing DNS record:
        czm --up type=A,data=192.168.0.1//dname=example.com,ttl=3600,type=A,data=10.0.0.1

    3. Add a record to multiple domains:
        czm --add dname=example.com,ttl=3600,type=A,data=192.168.0.1 --d gk27.com,gk50.com

    4. Add a record filtered by zone type:
        czm --add dname=example.com,type=A,data=10.0.0.1 -s type=MX

    5. Add a record while ignoring warnings:
        czm --add dname=example.com,ttl=14400,type=A,data=10.0.0.1
    ';
}
