# Receptdatabasen
A simple CRUD web app for storing recipes. Useful to maintain your favourite recipes digitally.

**Warning: old and messy code base!**

**Warning: no authentication is provided in the app - use HTTP Basic Auth in your web server config**

## Docker-compose setup
`export DB_PASSWORD=password`

`docker-compose up`

This is the easisest way, it sets up the app, db and vhost containers allowing access on port `8080`.
It sets up an empty database by default, from [db/schema.sql](db/schema.sql). The database is saved
in a named volume so it persists the data unless you specifically remove the `db-volume`. The images
are uploaded to another named volume `db-data`.

## Manual setup
* You need a web server serving the "app", I use nginx with the configuration
provided in [app/conf/nginx.conf](app/conf/nginx.conf) (edit it to work with your domain and root).
* You need to change some environment variables, see [app/.env](app/.env).
* To set up the db, use the schema dump in [app/conf/schema.sql](app/conf/schema.sql). It sets up the following tables:
  ```
  +---------------------------+
  | Gallery                   |
  | Ingredients               |
  | Recipes                   |
  | Sets                      |
  | Tags                      |
  +---------------------------+
  ```
  and the following schemas:

  ```
  Schema for table "Gallery":
  +----------+---------+------+-----+---------+-------+
  | Field    | Type    | Null | Key | Default | Extra |
  +----------+---------+------+-----+---------+-------+
  | Caption  | text    | YES  |     | NULL    |       |
  | FilePath | text    | NO   |     | NULL    |       |
  | F_id     | int(11) | YES  | MUL | NULL    |       |
  +----------+---------+------+-----+---------+-------+
  ```

  ```
  Schema for table "Ingredients":
  +------------+---------+------+-----+---------+-------+
  | Field      | Type    | Null | Key | Default | Extra |
  +------------+---------+------+-----+---------+-------+
  | Ingredient | text    | NO   |     | NULL    |       |
  | F_id       | int(11) | YES  | MUL | NULL    |       |
  +------------+---------+------+-----+---------+-------+
  ```

  ```
  Schema for table "Recipes":
  +--------------+--------------+------+-----+-------------------+-----------------------------+
  | Field        | Type         | Null | Key | Default           | Extra                       |
  +--------------+--------------+------+-----+-------------------+-----------------------------+
  | P_id         | int(11)      | NO   | PRI | NULL              | auto_increment              |
  | Title        | varchar(255) | NO   | UNI | NULL              |                             |
  | Intro        | text         | YES  |     | NULL              |                             |
  | Instructions | text         | NO   |     | NULL              |                             |
  | NbrOfPersons | int(11)      | NO   |     | NULL              |                             |
  | DateCreated  | datetime     | NO   |     | NULL              |                             |
  | DateUpdated  | timestamp    | NO   |     | CURRENT_TIMESTAMP | on update CURRENT_TIMESTAMP |
  +--------------+--------------+------+-----+-------------------+-----------------------------+
  ```

  ```
  Schema for table "Sets":
  +---------+--------------+------+-----+---------+----------------+
  | Field   | Type         | Null | Key | Default | Extra          |
  +---------+--------------+------+-----+---------+----------------+
  | P_id    | int(11)      | NO   | PRI | NULL    | auto_increment |
  | SetName | varchar(255) | NO   |     | NULL    |                |
  | F_id    | int(11)      | YES  | MUL | NULL    |                |
  +---------+--------------+------+-----+---------+----------------+
  ```

  ```
  Schema for table "Tags":
  +-------+----------+------+-----+---------+-------+
  | Field | Type     | Null | Key | Default | Extra |
  +-------+----------+------+-----+---------+-------+
  | Tag   | tinytext | NO   |     | NULL    |       |
  | F_id  | int(11)  | YES  | MUL | NULL    |       |
  +-------+----------+------+-----+---------+-------+
  ```

