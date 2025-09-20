# Project Overview: PMP Exam Prep Platform

This repository contains the source code for a WordPress-based e-learning platform focused on PMP (Project Management Professional) exam preparation.

## Key Technologies

*   **Backend:** WordPress
*   **Frontend:** Custom WordPress Theme (`pmp-dashboard`)
*   **Local Development:** Docker

## Project Structure

*   `wp-content/themes/pmp-dashboard/`: The source code for the custom WordPress theme. This is the primary development directory for the theme.
*   `static/`: Contains the original static HTML mockups for the website. These were used as a basis for the WordPress theme conversion.
*   `docker-compose.yml`: Defines the Docker services for the local development environment (WordPress, MySQL, phpMyAdmin).
*   `start-wordpress.sh`: A utility script to start the Docker-based environment.

## Development Environments

### 1. Docker (Primary)

*   **Purpose:** Main development environment.
*   **Setup:** Run the `start-wordpress.sh` script.
*   **URL:** `http://localhost:8080`
*   **Theme Path:** `./wp-content/themes/pmp-dashboard/`

### 2. MAMP (Testing)

*   **Purpose:** A secondary environment for testing purposes.
*   **Setup:** The theme from this repository is manually copied to a MAMP installation.
*   **MAMP Theme Path:** `/Applications/MAMP/htdocs/mi/wp-content/themes/pmp-dashboard`
*   **Note:** This is a testing location. The authoritative source for the theme remains within this project's `wp-content/themes/pmp-dashboard/` directory.
