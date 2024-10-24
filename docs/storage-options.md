## Storage Options

_Defaults to:_ `local`
_Description:_ You can store the transformed images in many different ways taking advantage of the [Flysystem](http://flysystem.thephpleague.com/) file system, like AWS S3, Azure, FTP, Dropbox, or whatever, although currently the only two easy options are `local` (the default) and `s3` to use an AWS S3 bucket.

### aws_s3

_Description:_ In case `storage_system` is set to `s3` you need to pass your AWS S3 Bucket credentials, do it here. Read more below at [Abstract storage with Flysystem](#abstract-storage-with-flysystem).
_Defaults to:_

```yml
  access_id: ""
  secret_key: ""
  region: ""
  bucket_name: ""
```

## Abstract storage with Flysystem

Storage files based on [Flysystem](http://flysystem.thephpleague.com/) which is `a filesystem abstraction allows you to easily swap out a local filesystem for a remote one. Technical debt is reduced as is the chance of vendor lock-in.`

Default storage is Local, but you can use other Adapters like AWS S3, Azure, FTP, Dropbox, ...

Currently, only the local and S3 are implemented as Storage Provider in Flyimg application, but you can add your specific one easily in `src/Core/Provider/StorageProvider.php`

### Using AWS S3 as Storage Provider

in parameters.yml change the `storage_system` option from local to s3, and fill in the aws_s3 options :

```yml
storage_system: s3

aws_s3:
  access_id: "s3-access-id"
  secret_key: "s3-secret-id"
  region: "s3-region"
  bucket_name: "s3-bucket-name"
```

