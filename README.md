# Receptdatabasen
A simple CRUD web app for storing recipes. Useful to maintain your favourite recipes digitally.

**Warning: old and messy code base!**

**Warning: no authentication is provided in the app - use HTTP Basic Auth in your web server config**

## Configuration
* You need a web server serving the "app", I use nginx with the configuration
provided in [src/conf/nginx.conf](src/conf/nginx.conf) (edit it to work with your domain and root).
* You need to change some constants as appropriate in the following files:
  * assets/includes/global.inc.ph
  * assets/deleteImage.php
  * assets/classes/Image.class.php
  * assets/includes/global.inc.php
* To set up the db, use the schema dump in [src/conf/schema.sql](src/conf/schema.sql). Alternatively, manually create the following tables:
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

