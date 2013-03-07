Dumpster is a web-based holding tank for log/exception/debugging/analysis data dumps.

Generating Keys
======

```openssl genrsa -out application/configs/dumpster.key 1024
openssl rsa -in application/configs/dumpster.key -pubout -out application/configs/dumpster.pub```
