## Storage Options

### storage_system
_Available options:_ `local`, `s3`
_Defaults to:_ `local`
_Description:_ You can store the transformed images in many different ways taking advantage of the [Flysystem](http://flysystem.thephpleague.com/) file system, like AWS S3, Azure, FTP, Dropbox, or whatever, although currently the only two easy options are `local` (the default) and `s3` to use an AWS S3 bucket.

Read more below at [Abstract storage with Flysystem](#abstract-storage-with-flysystem).

### Using AWS S3 as Storage Provider

in parameters.yml change the `storage_system` option from local to s3, and fill in the aws_s3 options :

#### Traditional AWS Credentials
```yml
storage_system: s3

aws_s3:
  access_id: "s3-access-id"
  secret_key: "s3-secret-id"
  region: "s3-region"
  bucket_name: "s3-bucket-name"
  path_prefix: "s3-path-prefix" # optional
  endpoint: "https://%s.s3.%s.amazonaws.com/" # optional for third party S3 compatible services, the format is https://<bucket-name>.s3.<region>.amazonaws.com/ 
  visibility: "PRIVATE" # options: PUBLIC or PRIVATE
```

#### IRSA (IAM Roles for Service Accounts) - EKS/Container Environments
For containerized environments like Amazon EKS, you can use IRSA to avoid managing access keys and secrets. With IRSA, the container uses a ServiceAccount connected to an IAM role that automatically provides the necessary credentials to the AWS SDK.

```yml
storage_system: s3

aws_s3:
  # access_id and secret_key are not needed with IRSA
  region: "s3-region"
  bucket_name: "s3-bucket-name"
  path_prefix: "s3-path-prefix" # optional
  endpoint: "https://%s.s3.%s.amazonaws.com/" # optional for third party S3 compatible services
  visibility: "PRIVATE" # options: PUBLIC or PRIVATE
```

**IRSA Setup Requirements:**
- Configure a Kubernetes ServiceAccount with an IAM role annotation
- Ensure the IAM role has the necessary S3 permissions
- The AWS SDK will automatically use the role credentials injected by IRSA

## Abstract storage with Flysystem

Storage files based on [Flysystem](http://flysystem.thephpleague.com/) which is `a filesystem abstraction allows you to easily swap out a local filesystem for a remote one. Technical debt is reduced as is the chance of vendor lock-in.`

Default storage is Local, but you can use other Adapters like AWS S3, Azure, FTP, Dropbox, ...

Currently, only the local and S3 are implemented as Storage Provider in Flyimg application, but you can add your specific one easily in `src/Core/Provider/StorageProvider.php`
