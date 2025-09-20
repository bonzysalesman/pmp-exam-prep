# PMP Dashboard WordPress Theme

## Quick Start

1. **Start WordPress Environment:**
   ```bash
   ./start-wordpress.sh
   ```

2. **Access WordPress:**
   - WordPress: http://localhost:8080
   - phpMyAdmin: http://localhost:8081

3. **WordPress Setup:**
   - Database: `wordpress`
   - Username: `wordpress` 
   - Password: `wordpress`

4. **Activate Theme:**
   - Go to Appearance > Themes
   - Activate "PMP Dashboard Theme"

## Development

- **Theme Files:** `wp-content/themes/pmp-dashboard/`
- **Static Files:** `static/` (reference templates)
- **Assets:** Copy from `static/` to theme `assets/` folder

## Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers  
docker-compose down

# View logs
docker-compose logs wordpress

# Reset everything
docker-compose down -v
```

## Theme Structure

```
wp-content/themes/pmp-dashboard/
├── style.css          # Theme info
├── index.php          # Main template
├── header.php         # HTML head
├── footer.php         # Scripts & closing tags
├── template-parts/    # Reusable components
└── assets/           # Images, CSS, JS
```
