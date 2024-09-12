## Installing the Flyimg Helm Chart

This section provides the steps to install the Flyimg Helm chart using Helm.

### Prerequisites

- Helm 3.x installed on your machine. [Get Helm](https://helm.sh/docs/intro/install/)
- Kubernetes cluster running (local or cloud-based)

### Add Flyimg Helm Repository

First, add the Flyimg Helm chart repository to Helm:

```
helm repo add flyimg https://charts.flyimg.io
helm repo update
```

### Install Flyimg

Once the repository is added, you can install the Flyimg chart with the following command:

```
helm install <release_name> flyimg/flyimg
```

Replace `<release_name>` with a name you choose for your deployment.

### Customizing Installation

You can customize your Flyimg installation by providing a custom `values.yaml` file. For example:

```
helm install <release_name> flyimg/flyimg -f values.yaml
```

Alternatively, you can pass in parameters directly via the command line:

```
helm install <release_name> flyimg/flyimg \
  --set key1=value1,key2=value2
```

### Upgrade Flyimg

To upgrade the Flyimg chart after making changes to your configuration:

helm upgrade <release_name> flyimg/flyimg

### Uninstalling Flyimg

To uninstall and remove all associated resources:

```
helm uninstall <release_name>
```

### Helm Chart Repository Info

- **Repository URL:** https://charts.flyimg.io
- **Chart Name:** flyimg
- **Available Versions:** Run `helm search repo flyimg` to see the available chart versions.
- **Github repo:** https://github.com/flyimg/helm-charts
