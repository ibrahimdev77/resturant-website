XIAJUN 夏君 – Chinese restaurant website (PHP + MySQL on XAMPP)

SETUP (Windows / macOS / Linux with XAMPP)
1. Open the XAMPP Control Panel and Start both "Apache" and "MySQL".
2. Copy the whole "xiajun" folder into XAMPP's htdocs folder:
     Windows: C:\xampp\htdocs\xiajun
     macOS:   /Applications/XAMPP/htdocs/xiajun
     Linux:   /opt/lampp/htdocs/xiajun
3. In your browser open:   http://localhost/xiajun/install.php
   This creates the database "xiajun_db", all tables, 24 sample dishes
   and the admin account. Run it once, then DELETE install.php.
4. Open the website:       http://localhost/xiajun/
5. Open the hidden admin:  http://localhost/xiajun/admin
   (type it in the address bar – there is no link to it anywhere on the site)
   Username: ibrahim
   Password: 12345678

NOTES
- The site detects its own folder path automatically. The admin address is http://localhost/<folder-name>/admin.
- MySQL defaults: user root, empty password. Change them in includes/config.php if yours differ.
- Requires PHP 8.0+ (current XAMPP versions are fine).
- Change the admin password before putting the site online.
