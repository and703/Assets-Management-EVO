/* SQL schema for SmartScan backend */
CREATE TABLE users (
  UserID       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  UserName     VARCHAR(100) NOT NULL,
  PasswordHash VARCHAR(255),
  Role         ENUM('admin','user') DEFAULT 'user',
  CreatedAt    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
  CategoryID   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  CategoryDesc VARCHAR(100) NOT NULL
);

CREATE TABLE locations (
  LocationID        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  LocationDesc      VARCHAR(100) NOT NULL,
  FullLocationDesc  VARCHAR(255)
);

CREATE TABLE status_list (
  StatusID   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  StatusDesc VARCHAR(100) NOT NULL
);

CREATE TABLE items (
  ItemID      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ItemBarcode VARCHAR(100) UNIQUE,
  ItemDesc    VARCHAR(255),
  CategoryID  INT UNSIGNED,
  FOREIGN KEY (CategoryID) REFERENCES categories(CategoryID)
);

CREATE TABLE inventory_header (
  InventoryID    INT UNSIGNED PRIMARY KEY,
  InventoryDate  DATETIME NOT NULL,
  UserID         INT UNSIGNED,
  FOREIGN KEY (UserID) REFERENCES users(UserID)
);

CREATE TABLE inventory_items (
  InventoryID         INT UNSIGNED,
  ItemID              INT UNSIGNED,
  ItemBarcode         VARCHAR(100),
  LocationID          INT UNSIGNED,
  StatusID            INT UNSIGNED,
  TagID               VARCHAR(128),
  Scanned             TINYINT(1) DEFAULT 0,
  StatusUpdated       TINYINT(1) DEFAULT 0,
  ReallocatedApplied  TINYINT(1) DEFAULT 0,
  CreatedAt           DATETIME NOT NULL,
  PRIMARY KEY (InventoryID, ItemID),
  FOREIGN KEY (InventoryID) REFERENCES inventory_header(InventoryID),
  FOREIGN KEY (ItemID)      REFERENCES items(ItemID),
  FOREIGN KEY (LocationID)  REFERENCES locations(LocationID),
  FOREIGN KEY (StatusID)    REFERENCES status_list(StatusID)
);

CREATE TABLE assigned_assets (
  ItemID      INT UNSIGNED PRIMARY KEY,
  ItemBarcode VARCHAR(100),
  TagID       VARCHAR(128),
  AssignedAt  DATETIME NOT NULL,
  FOREIGN KEY (ItemID) REFERENCES items(ItemID)
);
