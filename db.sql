-- Table: User
CREATE TABLE IF NOT EXISTS "User" (
  "Id" integer,
  "Username" character varying(64),
  "PasswordHash" character varying(60),
  "Email" character varying(255),
  "Description" character varying(255),
  "CreatedAt" timestamp without time zone,
  "Pfp" character varying(255)
);


-- Table: Post
CREATE TABLE IF NOT EXISTS "Post" (
  "Id" integer,
  "Title" character varying(255),
  "Content" text,
  "Creator" integer,
  "CreatedAt" timestamp without time zone,
  "CoverImage" character varying(255)
);

