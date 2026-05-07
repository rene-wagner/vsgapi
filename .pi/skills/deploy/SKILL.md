---
name: deploy
description: Deploys application data to a remote server over SSH using rsync, then restarts the Docker Compose stack on the remote host. Trigger this skill when the user asks to deploy the app, upload deployment files, sync migration_data, transfer the initial SQL dump or media directory, or restart the remote Docker Compose application after uploading files.
---

# Deploy Skill

## Description

Deploys application data to a remote server over SSH using `rsync`, then restarts the Docker Compose stack on the remote host. Trigger this skill when the user asks to deploy the app, upload deployment files, sync `migration_data`, transfer the initial SQL dump or media directory, or restart the remote Docker Compose application after uploading files.

## Purpose

Use this skill to deploy the required application files to a remote server and restart the Docker Compose services in the remote application directory.

The deployment consists of four steps:

1. Upload the local `migration_data` directory to the remote server.
2. Upload the local SQL file `001-initial-dump.sql` to the remote MySQL init directory.
3. Upload the local `./var/media` directory to the remote application directory.
4. Restart the remote Docker Compose stack by running `docker compose down` followed by `docker compose up -d` in `~/rwgnr/vsgapi`.

## When to Use This Skill

Use this skill when the user asks to:

* Deploy the app to a remote server
* Push or upload application deployment data
* Sync `migration_data` to a server
* Transfer `001-initial-dump.sql` to the remote MySQL init directory
* Transfer `./var/media` to the remote app directory
* Restart the remote Docker Compose stack after uploading deployment files

## Required Inputs

Before generating the final deployment commands, identify the following values:

* `remote_user`: SSH username for the remote server
* `remote_host`: Hostname or IP address of the remote server
* `ssh_port`: SSH port, defaulting to `22`
* `ssh_key`: Optional SSH private key path, if required
* `local_migration_data_dir`: Local path to the `migration_data` directory, defaulting to `migration_data`
* `local_sql_file`: Local path to `001-initial-dump.sql`, defaulting to `001-initial-dump.sql`
* `local_media_dir`: Local path to the media directory, defaulting to `./var/media`
* `remote_app_dir`: Remote app directory, defaulting to `~/rwgnr/vsgapi`
* `remote_mysql_init_dir`: Remote MySQL init directory, defaulting to `~/rwgnr/vsgapi/mysql/init`

If `remote_user` or `remote_host` is missing, ask the user for it. Use defaults for all other values unless the user specifies otherwise.

## SSH Authentication

Authentication must happen via SSH.

If the user provides an SSH key, use:

```bash
-e "ssh -i <ssh_key> -p <ssh_port>"
```

with `rsync`, and:

```bash
ssh -i <ssh_key> -p <ssh_port> <remote_user>@<remote_host> '<command>'
```

for remote commands.

If no SSH key is provided, use the default SSH configuration:

```bash
rsync -avz --progress <source> <remote_user>@<remote_host>:<target>
ssh <remote_user>@<remote_host> '<command>'
```

## Recommended Deployment Commands

Use these commands when all required values are known and the default SSH port is used.

### 1. Ensure Remote Directories Exist

```bash
ssh <remote_user>@<remote_host> 'mkdir -p ~/rwgnr/vsgapi ~/rwgnr/vsgapi/mysql/init'
```

### 2. Upload `migration_data`

```bash
rsync -avz --progress migration_data/ <remote_user>@<remote_host>:~/rwgnr/vsgapi/migration_data/
```

The trailing slash on `migration_data/` syncs the contents of the local directory into the remote `migration_data` directory.

### 3. Upload `001-initial-dump.sql`

```bash
rsync -avz --progress 001-initial-dump.sql <remote_user>@<remote_host>:~/rwgnr/vsgapi/mysql/init/001-initial-dump.sql
```

### 4. Upload `./var/media`

```bash
rsync -avz --progress ./var/media/ <remote_user>@<remote_host>:~/rwgnr/vsgapi/media/
```

The trailing slash on `./var/media/` syncs the contents of the local media directory into the remote `~/rwgnr/vsgapi/media/` directory.

### 5. Restart the Remote Docker Compose Stack

```bash
ssh <remote_user>@<remote_host> 'cd ~/rwgnr/vsgapi && docker compose down && docker compose up -d'
```

## Variant with Custom SSH Port

If the SSH port is not `22`, use `-e` with `rsync` and `-p` with `ssh`:

```bash
ssh -p <ssh_port> <remote_user>@<remote_host> 'mkdir -p ~/rwgnr/vsgapi ~/rwgnr/vsgapi/mysql/init'

rsync -avz --progress -e "ssh -p <ssh_port>" migration_data/ <remote_user>@<remote_host>:~/rwgnr/vsgapi/migration_data/

rsync -avz --progress -e "ssh -p <ssh_port>" 001-initial-dump.sql <remote_user>@<remote_host>:~/rwgnr/vsgapi/mysql/init/001-initial-dump.sql

rsync -avz --progress -e "ssh -p <ssh_port>" ./var/media/ <remote_user>@<remote_host>:~/rwgnr/vsgapi/media/

ssh -p <ssh_port> <remote_user>@<remote_host> 'cd ~/rwgnr/vsgapi && docker compose down && docker compose up -d'
```

## Variant with SSH Key

If the user provides an SSH key, use:

```bash
ssh -i <ssh_key> -p <ssh_port> <remote_user>@<remote_host> 'mkdir -p ~/rwgnr/vsgapi ~/rwgnr/vsgapi/mysql/init'

rsync -avz --progress -e "ssh -i <ssh_key> -p <ssh_port>" migration_data/ <remote_user>@<remote_host>:~/rwgnr/vsgapi/migration_data/

rsync -avz --progress -e "ssh -i <ssh_key> -p <ssh_port>" 001-initial-dump.sql <remote_user>@<remote_host>:~/rwgnr/vsgapi/mysql/init/001-initial-dump.sql

rsync -avz --progress -e "ssh -i <ssh_key> -p <ssh_port>" ./var/media/ <remote_user>@<remote_host>:~/rwgnr/vsgapi/media/

ssh -i <ssh_key> -p <ssh_port> <remote_user>@<remote_host> 'cd ~/rwgnr/vsgapi && docker compose down && docker compose up -d'
```

## Important rsync Path Rules

Use trailing slashes intentionally:

* `migration_data/` means sync the contents of the local directory into the remote target directory.
* `./var/media/` means sync the contents of the local media directory into the remote `media` directory.
* Without a trailing slash, `rsync` would copy the directory itself into the target path.

## Safety Checks

Before running the deployment, suggest checking that the required local paths exist:

```bash
test -d migration_data && echo "migration_data exists"
test -f 001-initial-dump.sql && echo "001-initial-dump.sql exists"
test -d ./var/media && echo "./var/media exists"
```

Optionally verify remote access:

```bash
ssh <remote_user>@<remote_host> 'pwd && docker --version && docker compose version'
```

## Post-Deployment Verification

After the deployment, suggest checking the running containers:

```bash
ssh <remote_user>@<remote_host> 'cd ~/rwgnr/vsgapi && docker compose ps'
```

Optionally check recent logs:

```bash
ssh <remote_user>@<remote_host> 'cd ~/rwgnr/vsgapi && docker compose logs --tail=100'
```

## Response Style

When responding to the user:

1. Confirm that deployment will use SSH-based `rsync` uploads.
2. State the remote target paths clearly.
3. Ask for `remote_user` and `remote_host` if they are missing.
4. Provide copyable Bash commands.
5. Explain the use of trailing slashes for directory syncs only when helpful.
6. Include the Docker Compose restart command.
7. Do not include compression steps.

## Example Response

Use the following commands to deploy the app to the remote server:

```bash
ssh deploy@example.com 'mkdir -p ~/rwgnr/vsgapi ~/rwgnr/vsgapi/mysql/init'

rsync -avz --progress migration_data/ deploy@example.com:~/rwgnr/vsgapi/migration_data/

rsync -avz --progress 001-initial-dump.sql deploy@example.com:~/rwgnr/vsgapi/mysql/init/001-initial-dump.sql

rsync -avz --progress ./var/media/ deploy@example.com:~/rwgnr/vsgapi/media/

ssh deploy@example.com 'cd ~/rwgnr/vsgapi && docker compose down && docker compose up -d'
```

The directory uploads use trailing slashes so that the contents of the local directories are synced into the corresponding remote directories.
