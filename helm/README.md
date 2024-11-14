<div align="center">
<img src="https://leantime.io/logos/leantime-logo-transparentBg-landscape-1500.png" alt="Leantime Logo" width="300"/><img src="https://cncf-branding.netlify.app/img/projects/helm/icon/color/helm-icon-color.svg" width=6% />

Leantime is a lean open source project management system for startups and innovators. <br />It's an alternative to ClickUp, Notion, and Asana.<br />[https://leantime.io](https://leantime.io)<br /></div>

# Introduction
This Helm Chart bootstraps a production-ready instance of Leantime in a Kubernetes cluster. To know more about Leantime and/or contribute to the development of the software, please refer to the root project [Leantime on GitHub](https://github.com/Leantime/leantime).

# Prerequisites
1. [x] Helm > v2 [installed](https://helm.sh/docs/using_helm/#installing-helm): `helm version`
2. [x] Kubernetes > 1.16.x
3. [x] Leantime Helm chart repository: `git clone https://github.com/Leantime/leantime`

# Deploying Leantime
To deploy Leantime clone this repository:
```bash
git clone https://github.com/Leantime/leantime
```
Prepare chart dependencies:
```bash
helm dependency build ./leantime/helm
```
Create a `values.yaml` file to override the default configurations. For convenience you can copy the default file in the `leantime/helm` directory into your current directory and modify it according to your needs (see configuring section). Then, deploy the application:
```bash
helm install leantime -f values.yaml ./leantime/helm
```

# Configuring

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| app.defaultTimezone | string | `"America/Los_Angeles"` | Sets the default Timezone |
| app.email.enabled | bool | `false` | Set to true if you want to use SMTP. If set to false, the default php mail() function will be used |
| app.email.return | string | `"leantime@cluster.local"` | Sets the email address to use for notifications and registrations |
| app.email.smtp.autoTLS | bool | `true` | Set autoTLS? |
| app.email.smtp.hosts | string | `""` | SMTP host |
| app.email.smtp.password | string | `""` | SMTP password |
| app.email.smtp.port | int | `587` | SMTP port |
| app.email.smtp.secure | string | `"STARTLS"` | Sets the SMTP security protocol (usually one of: TLS, SSL, STARTTLS) |
| app.email.smtp.username | string | `""` | SMTP username |
| app.language | string | `"en-US"` | Sets application language |
| app.ldap.DN | string | `""` | Location of users, example: CN=users,DC=example,DC=com |
| app.ldap.baseDN | string | `""` | Base DN, example: DC=example,DC=com |
| app.ldap.defaultRoleKey | string | `""` | Sets the default role for users when they are first created |
| app.ldap.enabled | bool | `false` | Set to true if you want to use LDAP |
| app.ldap.groupAssignment | string | `""` | Default role assignments upon first login |
| app.ldap.host | string | `""` | FQDN |
| app.ldap.keys | string | `""` | Default ldap keys in your directory |
| app.ldap.port | int | `389` | Sets LDAP port |
| app.ldap.type | string | `""` | Select the correct directory type. Currently Supported: OL - OpenLdap, AD - Active Directory |
| app.ldap.userDomain | string | `""` | Domain after ldap, example @example.com |
| app.s3.bucket | string | `""` | S3 bucket |
| app.s3.enabled | bool | `false` | Set to true if you want to use S3 instead of local files |
| app.s3.enpoint | string | `""` | S3 endpoint |
| app.s3.folderName | string | `""` | Sets the foldername within S3 (can be empty) |
| app.s3.key | string | `""` | S3 key |
| app.s3.region | string | `""` | S3 region |
| app.s3.secret | string | `""` | S3 secret |
| app.s3.usePathStyleEndpoint | string | `"false"` | Sets wether or not use path-style endpoint |
| app.session.expiration | int | `28800` | Session expiration |
| app.session.password | string | `"changeme"` | Salting sessions. Replace with a strong password |
| app.sitename | string | `"Leantime"` | Sets the name for the instance |
| autoscaling.enabled | bool | `false` |  |
| autoscaling.maxReplicas | int | `100` |  |
| autoscaling.minReplicas | int | `1` |  |
| autoscaling.targetCPUUtilizationPercentage | int | `80` |  |
| fullnameOverride | string | `""` | Ovverrides the fullname of the kubernetes object names for this release |
| image.pullPolicy | string | `"IfNotPresent"` | The pull policy of the Leantime OCI image applied to the deployment |
| image.repository | string | `"leantime/leantime"` | OCI image repository of the Leantime application |
| image.tag | string | `"latest"` | Overrides the image tag whose default is the chart appVersion |
| imagePullSecrets | list | `[]` | The pull secrets to be used if you want to use a Leantime application image hosted in a private registry |
| ingress.annotations | object | `{}` |  |
| ingress.className | string | `""` |  |
| ingress.enabled | bool | `false` |  |
| ingress.hosts[0].host | string | `"chart-example.local"` |  |
| ingress.hosts[0].paths[0].path | string | `"/"` |  |
| ingress.hosts[0].paths[0].pathType | string | `"ImplementationSpecific"` |  |
| ingress.tls | list | `[]` |  |
| mariadb.auth.database | string | `"leantime"` | Database name |
| mariadb.auth.password | string | `"changeme"` | Database password |
| mariadb.auth.rootPassword | string | `"changeme"` | Database root password |
| mariadb.auth.username | string | `"leantime"` | Database username |
| mariadb.enabled | bool | `true` |  |
| nameOverride | string | `""` | Overrides the name of the chart |
| nodeSelector | object | `{}` |  |
| persistence.enabled | bool | `true` | Enables or disables the persistence |
| persistence.size | string | `"5Gi"` | Sets the size of the PVs for Leantime's public and private userfiles |
| persistence.storageClass | string | `"standard"` | Sets the storage class for Leantime's volumes |
| podAnnotations | object | `{}` |  |
| podSecurityContext | object | `{}` |  |
| replicaCount | int | `1` |  |
| resources | object | `{}` |  |
| securityContext | object | `{}` |  |
| service.port | int | `80` |  |
| service.type | string | `"ClusterIP"` |  |
| serviceAccount.annotations | object | `{}` |  |
| serviceAccount.create | bool | `false` |  |
| serviceAccount.name | string | `""` |  |
| tolerations | list | `[]` |  |