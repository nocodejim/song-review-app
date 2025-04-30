# Song Review App

A simple application for reviewing song versions.

## Setup (Docker)

1.  Ensure Docker and Docker Compose are installed.
2.  Place your audio files (`help_me_v3.mp3`, `help_me_v4.mp3`) in `public/assets/audio/`.
3.  Place your background video (`bg-video.mp4`) in `public/assets/video/`.
4.  Copy or rename `.env.example` to `.env` and fill in your database credentials.
5.  Run the application: `docker-compose up -d --build`
6.  Access the application at [http://localhost:8080](http://localhost:8080) (or the port specified in `docker-compose.yml`).

*(More details in the full documentation)*
