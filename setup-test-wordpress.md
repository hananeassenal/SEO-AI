# Setting Up a New WordPress Test Site

## Option A: Using Local by Flywheel (Recommended)

1. **Download Local by Flywheel**: https://localwp.com/
2. **Create New Site**:
   - Click "Create a new site"
   - Choose "Custom" setup
   - Site name: `ai-seo-test`
   - Admin username: `admin`
   - Admin password: `password123`
   - Choose PHP 8.0+ and latest WordPress

3. **Install the Plugin**:
   - Copy the `ai-seo-optimizer` folder to:
     ```
     C:\Users\[YourUsername]\Local Sites\ai-seo-test\app\public\wp-content\plugins\
     ```
   - Activate the plugin in WordPress admin

## Option B: Using XAMPP/WAMP

1. **Install XAMPP**: https://www.apachefriends.org/
2. **Create Database**:
   - Open phpMyAdmin
   - Create database: `ai_seo_test`
   - Import WordPress database

3. **Download WordPress**:
   - Download from wordpress.org
   - Extract to `htdocs/ai-seo-test/`
   - Run WordPress installation

4. **Install Plugin**:
   - Copy plugin folder to `wp-content/plugins/`
   - Activate in WordPress admin

## Option C: Using Docker

```bash
# Create docker-compose.yml
version: '3'
services:
  wordpress:
    image: wordpress:latest
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - ./wordpress:/var/www/html
      - ./ai-seo-optimizer:/var/www/html/wp-content/plugins/ai-seo-optimizer
  db:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: somewordpress
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data: {}
```

## Quick Test URLs

Once set up, you can access:

- **WordPress Admin**: `http://localhost:8080/wp-admin/`
- **Plugin Dashboard**: `http://localhost:8080/wp-admin/admin.php?page=ai-seo-optimizer`
- **Shopping Site Demo**: `http://localhost:8080/wp-content/plugins/ai-seo-optimizer/test-shopping-site/index.html`
- **SEO Test Interface**: `http://localhost:8080/wp-content/plugins/ai-seo-optimizer/test-shopping-seo.php`

## Testing Checklist

- [ ] WordPress site is running
- [ ] Plugin is activated
- [ ] Dashboard is accessible
- [ ] Site analysis works
- [ ] AI recommendations appear
- [ ] Shopping site demo loads
- [ ] API endpoints respond
