-- should set the server time (because publish)

INSERT INTO Slug (slug, reserved) VALUES
    ("login", true),
    ("logout", true),
    ("lost", true),
    ("admin", true),
    ("settings", true),
    ("users", true),
    ("me", true),
    ("story", true),
    ("basicpage", true),
    ("storypage", true),
    ("edit", true),
    ("new", true),
    ("orderpages", true),
    ("news", true);

INSERT INTO Setting (name, value) VALUES
    ("site_title", null),
    ("news_rss_url", null),
    ("home_format", null),
    ("home_text", null),
    ("home_update_format", null),
    ("home_show_news", "true"),
    ("timezone", "America/New_York");

INSERT INTO Role (roleid, role) VALUES
    (1, "admin"),
    (0, "banned");

INSERT INTO Permission (permissionid, permission) VALUES
    (1, "edit_own_account"),
    (2, "view_admin_area"),
    (3, "edit_settings"),
    (4, "view_drafts"),
    (5, "upload_media"),
    (6, "edit_basicpage"),
    (7, "order_storypage"),
    (8, "edit_storypage"),
    (9, "edit_story"),
    (10, "manage_users");

INSERT INTO Grant_ (roleid, permissionid) VALUES
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "edit_own_account" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "view_admin_area" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "edit_settings" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "view_drafts" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "upload_media" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "edit_basicpage" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "order_storypage" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "edit_storypage" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "edit_story" GROUP BY permission)),
    ((SELECT roleid FROM Role WHERE role = "admin" GROUP BY role),
        (SELECT permissionid FROM Permission WHERE permission = "manage_users" GROUP BY permission));

INSERT INTO User (userid, username, password, email, roleid) VALUES
    (1, "admin", "password", "email@example.com",
        (SELECT roleid FROM Role WHERE role = "admin" GROUP BY role));


-- starting pages

INSERT INTO Slug (slug, reserved) VALUES ("about", false);

INSERT INTO Content (userid, title, publish, body) VALUES
    (1, "About", NOW(), '<p>Welcome to your new Sequence website. To get started, <a href = "login">login</a> to your administrator account. (You should also bookmark the login page, or write down its URL.)</p><p>After logging in, you can edit this page to fill it with information about your story, or delete it.</p>');

INSERT INTO Basic_Page (id, slug, short_title) VALUE
    ((SELECT id FROM Content WHERE title = "about" GROUP BY title), "about", "About");
