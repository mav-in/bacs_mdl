#Status

Trash

## Generate protobuf

**Linux:**

```sh
$ mkdir ~/bacs_mdl/external/output
$ protoc-gen-php -i ~/git/bacs/external_proto2/include -o ~/git/bacs_mdl/site/mod/bacs/external/ ~/git/bacs/external_proto2/include/bacs/external/external.proto
```