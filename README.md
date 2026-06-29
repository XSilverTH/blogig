## BlogIg

a really simple blog prototype maade with php for a project

uses a really small PostgreSQL database with this scheme
```
User:
Id PK
Username
PasswordHash
Email
Description
CreatedAt
Pfp

Post:
Id PK
Title
Content
Creator FK User.Id
CreatedAt
CoverImage
```

Uses [FrankenUI](https://franken-ui.dev/) to easily achieve an acceptable ui look
