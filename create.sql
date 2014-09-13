DROP TABLE IF EXISTS Story_Page;
DROP TABLE IF EXISTS Basic_Page;
DROP TABLE IF EXISTS Content;
DROP TABLE IF EXISTS Story;
DROP TABLE IF EXISTS User;
DROP TABLE IF EXISTS Grant_;
DROP TABLE IF EXISTS Role;
DROP TABLE IF EXISTS Permission;
DROP TABLE IF EXISTS Slug;
DROP TABLE IF EXISTS Setting;

CREATE TABLE Setting (
    name            VARCHAR(20),
    value           TEXT,
    CONSTRAINT setting_pk PRIMARY KEY (name)
)ENGINE=InnoDB;

CREATE TABLE Slug (
    slug            VARCHAR(20),
    reserved        BOOL            NOT NULL DEFAULT false,
    CONSTRAINT slug_pk PRIMARY KEY (slug)
)ENGINE=InnoDB;

CREATE TABLE Permission (
    permissionid    INT,
    permission      VARCHAR(50)     NOT NULL UNIQUE,
    CONSTRAINT permission_pk PRIMARY KEY (permissionid)
)ENGINE=InnoDB;

CREATE TABLE Role(
    roleid          SMALLINT,
    role            VARCHAR(20)     NOT NULL UNIQUE,
    CONSTRAINT role_pk PRIMARY KEY (roleid)
)ENGINE=InnoDB;

CREATE TABLE Grant_ (
    roleid          SMALLINT,
    permissionid    INT,
    CONSTRAINT grant_pk PRIMARY KEY (roleid, permissionid),
    CONSTRAINT grant_roleid_fk FOREIGN KEY (roleid) REFERENCES Role(roleid),
    CONSTRAINT grant_permissionid_fk FOREIGN KEY (permissionid) REFERENCES Permission(permissionid)
)ENGINE=InnoDB;

CREATE TABLE User(
    userid          INT             AUTO_INCREMENT,
    username        VARCHAR(20)     NOT NULL UNIQUE,
    password        VARCHAR(20)     NOT NULL,
    email           TINYTEXT        NOT NULL,
    url             TINYTEXT,
    roleid          SMALLINT,
    CONSTRAINT user_pk PRIMARY KEY (userid),
    CONSTRAINT user_roleid_fk FOREIGN KEY (roleid) REFERENCES Role(roleid)
)ENGINE=InnoDB;

CREATE TABLE Story (
    storyid         INT             AUTO_INCREMENT,
    slug            VARCHAR(20)     NOT NULL UNIQUE,
    title           TINYTEXT        NOT NULL,
    short_title     varchar(20)     NOT NULL,
    synopsis        TEXT,
    show_synopsis   BOOL            NOT NULL DEFAULT false,
   show_cover_thumb BOOL            NOT NULL DEFAULT false,
    show_page_thumb BOOL            NOT NULL DEFAULT false,
    show_page_title BOOL            NOT NULL DEFAULT true,
    show_page_num   BOOL            NOT NULL DEFAULT false,
    show_prevnext   BOOL            NOT NULL DEFAULT true,
    show_firstlast  BOOL            NOT NULL DEFAULT false,
    show_updates    BOOL            NOT NULL DEFAULT true,
    CONSTRAINT story_pk PRIMARY KEY (storyid)
)ENGINE=InnoDB;

CREATE TABLE Content (
    id              INT             AUTO_INCREMENT,
    created         TIMESTAMP       NOT NULL, -- DEFAULT '0000-00-00 00:00:00'    (if there are problems)
    edited          TIMESTAMP,
    publish         TIMESTAMP,
    title           TINYTEXT        NOT NULL,
    body            TEXT,
    userid          INT             NOT NULL,
    CONSTRAINT content_pk PRIMARY KEY (id),
    CONSTRAINT content_userid_fk FOREIGN KEY (userid) REFERENCES User(userid)
)ENGINE=InnoDB;

CREATE TABLE Basic_Page (
    id              INT,
    slug            VARCHAR(20)     NOT NULL UNIQUE,
    short_title     varchar(20)     NOT NULL,
    CONSTRAINT basic_page_pk PRIMARY KEY (id),
    CONSTRAINT basic_page_id_fk FOREIGN KEY (id) REFERENCES Content(id),
    CONSTRAINT basic_page_slug_fk FOREIGN KEY (slug) REFERENCES Slug(slug)
)ENGINE=InnoDB;

CREATE TABLE Story_Page (
    id              INT,
    author_comment  TEXT,
    thumbnail       TINYTEXT,
    story           INT             NOT NULL,
    pagenum         SMALLINT        NOT NULL,
    iscover         BOOL            NOT NULL DEFAULT false,
    CONSTRAINT story_page_pk PRIMARY KEY (id),
    CONSTRAINT story_page_id_fk FOREIGN KEY (id) REFERENCES Content(id),
    CONSTRAINT story_page_story_fk FOREIGN KEY (story) REFERENCES Story(storyid)
)ENGINE=InnoDB;
