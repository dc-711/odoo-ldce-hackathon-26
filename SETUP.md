# GlobeTrotter database setup

## Run it with XAMPP

1. Copy this project into `C:\xampp\htdocs\heckathon`.
2. Open XAMPP Control Panel and start Apache and MySQL.
3. Open `http://localhost/phpmyadmin`.
4. Select the Import tab, choose `database.sql`, and press Import.
5. Open `http://localhost/heckathon/`.
6. Create a trip from the dashboard. It will be saved in the `trips` table.

If importing from PowerShell, use port `3307`:

```powershell
Get-Content .\database.sql | & "C:\xampp\mysql\bin\mysql.exe" -u root -P 3307
```

## Open the admin side

Open `http://localhost/heckathon/admin/`.

Demo administrator:

```text
Email: admin@globetrotter.local
Password: admin123
```

The admin dashboard shows registered users, total trips, planned stops, tracked expenses, recent users, and recent trips. It reads directly from the same `globetrotter` database as the user dashboard.

Open analytics at `http://localhost/heckathon/admin/analytics.php` after signing in. Analytics shows users with trips, planned items, most selected cities, most added activities, and trips grouped by month.

Use `http://localhost/heckathon/login.php` for the shared login screen. Admin accounts go to the admin dashboard. Regular user accounts go to the user dashboard. Use `http://localhost/heckathon/register.php` to create a regular user account. Always open `http://localhost/heckathon/`, not `index.html` directly. If an old user session is still open, visit `http://localhost/heckathon/logout.php` once and log in again.

The connection uses XAMPP MySQL on port `3307`: host `127.0.0.1`, user `root`, and an empty password. The port was changed from `3306` because another program is using the default MySQL port.

## Watch the saved data

In phpMyAdmin, select the `globetrotter` database and open the `trips` table. Use Browse to see saved trips. You can also run:

```sql
SELECT * FROM trips ORDER BY created_at DESC;
```

## Work with a partner

The database is local to each computer, so do not try to share your local MySQL server over the internet.

Use GitHub for the project files. Both partners should:

1. Clone the same repository.
2. Start Apache and MySQL locally.
3. Import `database.sql` into their own phpMyAdmin once.
4. Work on separate branches and push changes.
5. Share schema changes by editing `database.sql`, then tell the other person to re-import or run the new SQL migration.

For a team database that both people can use at the same time, host MySQL on a shared development server such as Railway, Render, Supabase, or a school server. Give each partner a separate database user, keep credentials out of Git, and put them in a local config file that is listed in `.gitignore`.
