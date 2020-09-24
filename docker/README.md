
## 1. Create a key

```sh
openssl req                           \
    -config <( cat server.csr.config )  \
    -keyout server.key                  \
    -new                                \
    -newkey rsa:2048                    \
    -nodes                              \
    -out server.csr                     \
    -sha256 
```

## 2. Create a certificate

```sh
openssl x509            \
    -days 365           \
    -extfile v3.ext     \
    -in server.csr      \
    -out server.crt     \
    -req                \
    -sha256 

# Generate key and certificate
openssl req             \
    -keyout server.key  \
    -out server.cert    \
    -subj "/C=/ST=/L=/O=IT/CN=localhost" \
    -days 365           \
    -new                \
    -nodes              \
    -x509 

openssl req                         \
    -key "server.key"               \
    -new                            \
    -out "server.csr"               \
    -passout "pass:${sPassPhrase}"  \
    -subj "/C=NL/ST=Overijssel/L=Enschede/O=PDS Interop/OU=Development/CN=*.${sDomain}/emailAddress=me@example.com"

```

<!--
    -CA rootCA.pem      \
    -CAcreateserial     \
    -CAkey rootCA.key   \
-->

