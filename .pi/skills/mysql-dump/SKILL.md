---
name: mysql-dump
description: Creates an uncompressed SQL dump from a MySQL database running inside a Docker container by using docker exec and mysqldump. Trigger this skill when the user asks to export, back up, dump, or retrieve a MySQL database from a Docker container, especially when they need a host-side .sql dump file.
---

# MySQL Dump Skill

## Purpose

Use this skill when the user needs to create a SQL dump from a MySQL database running inside a Docker container.

The skill should produce or execute a command equivalent to:

```bash
docker exec <container_name> mysqldump -u <user> -p'<password>' <database> > dump.sql
```

This skill is organized into focused sections: **Required Inputs**, **Recommended Command**, **Password Handling**, **Output Location**, and **Validation Steps**.

## When to Use This Skill

Use this skill when the user asks to:

* Export a MySQL database from a Docker container
* Create a MySQL dump from Docker
* Back up a MySQL database running in a container
* Generate a `dump.sql` file from a containerized MySQL instance

## Required Inputs

Before generating the final command, identify the following values. If the container name or ID is invalid or inaccessible, inform the user and suggest running `docker ps` to list available containers.

* `container_name`: Name or ID of the Docker container running MySQL
* `user`: MySQL username
* `password`: MySQL password
* `database`: Name of the database to dump
* `output_file`: Path of the dump file on the host machine, defaulting to `dump.sql`

Always ask the user for missing values first. If the user does not provide input, use placeholders such as `<container_name>`, `<user>`, `<password>`, and `<database>`.

## Recommended Command

Use this command when all required values are known:

```bash
docker exec <container_name> mysqldump -u <user> -p'<password>' <database> > <output_file>
```

Example:

```bash
docker exec mysql-container mysqldump -u root -p'secret' database > dump.sql
```

## Password Handling

Prefer quoting the password with single quotes to reduce issues with shell-special characters:

```bash
docker exec <container_name> mysqldump -u <user> -p'<password>' <database> > dump.sql
```

Important details:

* There must be no space between `-p` and the password.
* If the password contains a single quote, use an alternative approach such as an environment variable or interactive password prompt.
* Do not print or log passwords unnecessarily.
* If the user does not want the password in shell history, recommend an interactive approach.

## Safer Interactive Variant

If the user wants to avoid putting the password directly in the command, use:

```bash
docker exec -i <container_name> mysqldump -u <user> -p <database> > <output_file>
```

The command will prompt for the password interactively.

## Notes on Output Location

The redirection operator `>` runs on the host shell, not inside the container. Therefore:

```bash
docker exec <container_name> mysqldump -u <user> -p'<password>' <database> > dump.sql
```

creates `dump.sql` on the host machine in the current working directory.

If the user wants the dump inside the container, use a shell inside the container:

```bash
docker exec <container_name> sh -c "mysqldump -u <user> -p'<password>' <database> > /path/in/container/dump.sql"
```

## Validation Steps

After creating the dump, suggest validating the output file:

```bash
ls -lh <output_file>
head -n 20 <output_file>
```

## Restore Reference

If the user also asks how to restore the dump, provide:

```bash
docker exec -i <container_name> mysql -u <user> -p'<password>' <database> < <output_file>
```

## Response Style

When responding to the user:

1. Confirm the command structure.
2. Explain that the dump file is written on the host because `>` is handled by the host shell.
3. Mention password handling briefly.
4. Provide the final command in a copyable Bash block.
5. Include validation commands when useful.

## Example Response

Use the following command to create the dump on the host machine:

```bash
docker exec mysql-container mysqldump -u root -p'secret' database > dump.sql
```

The `dump.sql` file will be created in your current host directory because the `>` redirection is handled by your local shell, not by the container.

To check the result:

```bash
ls -lh dump.sql
head -n 20 dump.sql
```
