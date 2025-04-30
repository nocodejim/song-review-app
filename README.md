# Song Review App (V1 - Dockerized)

A simple PHP web application allowing users to listen to two versions of a song, vote for their preferred version, and submit optional feedback. Feedback is stored in a MySQL database. This project uses Docker and Docker Compose for a consistent development environment.

## Features (V1)

* Displays two hardcoded song versions (`help_me_v3.mp3`, `help_me_v4.mp3`).
* Responsive design using Tailwind CSS (via CDN).
* Full-screen video background (`bg-video.mp4`).
* Form for selecting preferred version (v3/v4) and submitting optional text feedback.
* Stores votes and feedback in a MySQL database.
* Uses Docker Compose for local development (Apache, PHP 8.2, MySQL 8.0).
* Basic status messages on form submission (success/error/invalid).

## Project Structure

song-review-app/├── public/                 # Web root served by Apache│   ├── index.php           # Main page│   ├── submit_feedback.php # Form submission handler│   └── assets/             # Static files│       ├── audio/          # MP3 files (e.g., help_me_v3.mp3)│       └── video/          # Video files (e.g., bg-video.mp4)│       ├── css/            # (Optional CSS)│       └── js/             # (Optional JS)├── config/                 # Application configuration│   └── database.php        # Database connection logic (reads env vars)├── .dockerignore           # Specifies files to ignore during Docker image build├── .env                    # Local environment variables (DB passwords, etc.) - DO NOT COMMIT├── .env.example            # Example environment variables file - COMMIT THIS├── .gitignore              # Specifies intentionally untracked files for Git├── Dockerfile              # Instructions to build the PHP/Apache Docker image├── docker-compose.yml      # Defines services (web, db) for Docker Compose└── README.md               # This file
## Local Development Setup (Docker)

**Prerequisites:**

* Docker Engine
* Docker Compose (v1 or v2 `docker compose`)

**Steps:**

1.  **Clone the Repository:**
    ```bash
    git clone <your-repository-url> song-review-app
    cd song-review-app
    ```
2.  **Place Media Files:**
    * Put your song versions (e.g., `help_me_v3.mp3`, `help_me_v4.mp3`) into the `public/assets/audio/` directory.
    * Put your background video (e.g., `bg-video.mp4`) into the `public/assets/video/` directory.
    * (Optional) Add a `fallback-image.jpg` to `public/assets/video/` if desired.
3.  **Configure Environment:**
    * Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    * **Edit `.env`:** Open the `.env` file and set strong, unique passwords for `MYSQL_PASSWORD` and `MYSQL_ROOT_PASSWORD`. Update other variables like `MYSQL_DATABASE` or `MYSQL_USER` if you changed them from the defaults.
4.  **Build and Start Containers:**
    ```bash
    docker compose up -d --build
    # Or: docker-compose up -d --build
    ```
    * This will build the `web` image, download the `mysql` image, create containers, network, and volumes, and start the services in the background.
5.  **Create Database Table:**
    * The `db` service automatically creates the database and user specified in `.env`. However, you need to manually create the `feedback` table *once*.
    * Connect to the running database container:
        ```bash
        # Find container name (e.g., song-review-db)
        docker ps
        # Connect using root user
        docker exec -it song-review-db mysql -u root -p
        ```
    * Enter the `MYSQL_ROOT_PASSWORD` from your `.env` file.
    * Inside the `mysql>` prompt, select the database and run the `CREATE TABLE` command:
        ```sql
        USE your_database_name; -- Replace with your MYSQL_DATABASE value from .env
        -- Paste the CREATE TABLE statement from 'Database Schema (MySQL - Final)' artifact here
        CREATE TABLE feedback ( id INT AUTO_INCREMENT PRIMARY KEY, song_id VARCHAR(100) NOT NULL, chosen_version VARCHAR(50) NOT NULL, feedback_text TEXT NULL, submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        exit;
        ```
6.  **Access Application:** Open your web browser and navigate to `http://localhost:8080` (or the host port mapped in `docker-compose.yml`).

**Useful Docker Commands:**

* **Stop Containers:** `docker compose down` (preserves database volume)
* **View Logs:** `docker compose logs -f web` or `docker compose logs -f db`
* **Restart:** `docker compose restart web`
* **Rebuild Image:** `docker compose build web` (if `Dockerfile` changes)

## Deployment to Hostinger (or similar Shared Hosting)

Docker is used here for *development consistency*. Deploying this directly to standard shared hosting (like Hostinger Premium) requires deploying *without* Docker:

1.  **Upload Files:** Using FTP/SFTP or Hostinger's File Manager:
    * Upload the *contents* of your local `public/` directory into Hostinger's web root (e.g., `public_html`).
    * Create a `config/` directory *outside* the web root on the server (e.g., at the same level as `public_html`).
2.  **Configure Database Connection (Server-Side):**
    * Create a `database.php` file inside the server's `config/` directory.
    * **IMPORTANT:** This server version **must not** use `getenv()`. Use PHP `define()` constants with your *actual Hostinger database credentials*.
        ```php
        <?php // config/database.php (FOR HOSTINGER SERVER)
        define('DB_HOST', 'your_hostinger_mysql_hostname'); // Find in Hostinger panel
        define('DB_NAME', 'your_hostinger_database_name');
        define('DB_USER', 'your_hostinger_database_user');
        define('DB_PASS', 'your_hostinger_database_password');
        define('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [ /* PDO options */ ];

        function connect_db() {
            global $dsn, $options;
            try {
                // Use defined constants here
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return $pdo;
            } catch (\PDOException $e) {
                error_log("Hostinger DB Connection Error: " . $e->getMessage());
                die("Database connection failed on server."); // Generic error
            }
        }
        ?>
        ```
3.  **Set up Hostinger Database:**
    * Use Hostinger's control panel to create the MySQL database and user if you haven't already.
    * Use Hostinger's phpMyAdmin (or similar tool) to run the `CREATE TABLE` SQL script (from the `Database Schema (MySQL - Final)` artifact) in your Hostinger database.
4.  **Ensure Permissions:** Standard permissions are usually `755` for directories and `644` for files. Ensure the server's `config` directory is not web-accessible.
5.  **Test:** Access your live domain.

## Security Considerations

* **SQL Injection:** Prevented via PDO prepared statements.
* **XSS:** Mitigated by using `htmlspecialchars()` on output and `FILTER_SANITIZE_FULL_SPECIAL_CHARS` on text input before storage.
* **Credentials:** Handled via `.env` file locally (excluded from Git) and separate configuration on the server.
* **Input Validation:** Server-side checks in `submit_feedback.php` ensure only expected values are processed.
* **Error Handling:** Detailed errors are logged server-side; generic messages shown to users.

## Future Enhancements

* User authentication/authorization.
* Dynamic song loading from the database.
* Admin interface for managing songs.
* Displaying feedback results/analytics.
* More robust client-side validation and user feedback.
* Implementing unit/integration tests.
* Using a PHP framework (e.g., Laravel, Symfony) for larger features.
