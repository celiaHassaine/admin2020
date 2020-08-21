$TTL    604800

$ORIGIN wt7.ephec-ti.be.
@      IN      SOA     ns1.wt7.ephec-ti.be. root.wt7.ephec-ti.be. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
; serveur web  
                       
@         IN      NS      ns1.wt7.ephec-ti.be.
ns1       IN      A       51.178.40.97

www       IN      A       51.178.40.97
b2b       IN      A       51.178.40.97
intranet  IN      A       51.178.40.97 
