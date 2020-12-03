# Simple PHP Git deploy script
_Automatically deploy the code using PHP and Git._

Because I prefer to setup deploy one time and let it work, I dom't need seperate config file.
Also, for base deploy script, I don't need most of features from original repository so I cleaned it up and left only what I need. For original version, check `deploy-advanced.php` file.

## Requirements

* For base use you only need `git` to be installed.
* The system user running PHP (e.g. `www-data`) needs to have the necessary
  access permissions for the `TARGET_DIR` locations on
  the _server machine_.
* If the Git repository you wish to deploy is private, the system user running PHP
  also needs to have the right SSH keys to access the remote repository.

## Usage

 * Configure the script and put it somewhere that's accessible from the
   Internet. Update configuration in the beggiging of the file.
 * Configure your git repository to call this script when the code is updated.
   The instructions for GitHub and Bitbucket are below.

### GitHub

 1. _(This step is only needed for private repositories)_ Go to
    `https://github.com/USERNAME/REPOSITORY/settings/keys` and add your server
    SSH key.
 1. Go to `https://github.com/USERNAME/REPOSITORY/settings/hooks`.
 1. Click **Add webhook** in the **Webhooks** panel.
 1. Enter the **Payload URL** for your deployment script e.g. `http://example.com/deploy.php?sat=YourSecretAccessTokenFromDeployFile`.
 1. _Optional_ Choose which events should trigger the deployment.
 1. Make sure that the **Active** checkbox is checked.
 1. Click **Add webhook**.

### Bitbucket

 1. _(This step is only needed for private repositories)_ Go to
    `https://bitbucket.org/USERNAME/REPOSITORY/admin/deploy-keys` and add your
    server SSH key.
 1. Go to `https://bitbucket.org/USERNAME/REPOSITORY/admin/services`.
 1. Add **POST** service.
 1. Enter the URL to your deployment script e.g. `http://example.com/deploy.php?sat=YourSecretAccessTokenFromDeployFile`.
 1. Click **Save**.

### Generic Git

 1. Configure the SSH keys.
 1. Add a executable `.git/hooks/post_receive` script that calls the script e.g.

```sh
#!/bin/sh
echo "Triggering the code deployment ..."
wget -q -O /dev/null http://example.com/deploy.php?sat=YourSecretAccessTokenFromDeployFile
```

## Done!

Next time you push the code to the repository that has a hook enabled, it's
going to trigger the `deploy.php` script which is going to pull the changes and
update the code on the _server machine_.

For more info, read the source of `deploy.php`.
