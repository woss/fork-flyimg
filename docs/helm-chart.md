## Installing the Flyimg Helm Chart

This section provides the steps to install the Flyimg Helm chart using Helm.

### Prerequisites

- Helm 3.x installed on your machine. [Get Helm](https://helm.sh/docs/intro/install/)
- Kubernetes cluster running (local or cloud-based)

### Add Flyimg Helm Repository

First, add the Flyimg Helm chart repository to Helm:

```sh
helm repo add flyimg https://charts.flyimg.io
helm repo update
```

### Install Flyimg

Once the repository is added, you can install the Flyimg chart with the following command:

```sh
helm install <release_name> flyimg/flyimg
```

Replace `<release_name>` with a name you choose for your deployment.

### Customizing Installation

You can customize your Flyimg installation by providing a custom `values.yaml` file. For example:

```sh
helm install <release_name> flyimg/flyimg -f values.yaml
```

Alternatively, you can pass in parameters directly via the command line:

```sh
helm install <release_name> flyimg/flyimg \
  --set key1=value1,key2=value2
```

To override the default configuration for Flyimg, you can do that by adding the needed changes in the `parameters:` section in your values.yaml file as the following example:

#### Traditional AWS Credentials
```yaml
parameters:
    storage_system: s3
    aws_s3:
        access_id: 'xxxxxxx'
        secret_key: 'xxxxxx'
        region: 'eu-central-1'
        bucket_name: 'xxxxx'
    # Security configuration
    restricted_domains: true
    whitelist_domains:
        - "example.com"
        - "*.example.org"
        - "api.myservice.com"
        - "*.cdn.myservice.com"
    security_key: 'your-security-key'
    security_iv: 'your-security-iv'
    ....
```

#### IRSA (IAM Roles for Service Accounts) Configuration
For EKS environments using IRSA, you can omit the access credentials:

```yaml
parameters:
    storage_system: s3
    aws_s3:
        # access_id and secret_key are not needed with IRSA
        region: 'eu-central-1'
        bucket_name: 'xxxxx'
    # Security configuration
    restricted_domains: true
    whitelist_domains:
        - "example.com"
        - "*.example.org"
        - "api.myservice.com"
        - "*.cdn.myservice.com"
    security_key: 'your-security-key'
    security_iv: 'your-security-iv'
    ....
```

**IRSA Setup Requirements:**
- Configure a Kubernetes ServiceAccount with an IAM role annotation
- Ensure the IAM role has the necessary S3 permissions
- The AWS SDK will automatically use the role credentials injected by IRSA

**Note**: When using wildcard patterns in YAML, make sure to quote them (e.g., `"*.example.com"`) to avoid YAML parsing errors.

### Upgrade Flyimg

To upgrade the Flyimg chart after making changes to your configuration:

```sh
helm upgrade <release_name> flyimg/flyimg
```

### Uninstalling Flyimg

To uninstall and remove all associated resources:

```sh
helm uninstall <release_name>
```

### Helm Chart Repository Info

- **Repository URL:** https://charts.flyimg.io
- **Chart Name:** flyimg
- **Available Versions:** Run `helm search repo flyimg` to see the available chart versions.
- **Github repo:** https://github.com/flyimg/helm-charts
