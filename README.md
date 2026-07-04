# South Forsyth.org

SouthForsyth.org is a community-focused WordPress site for South Forsyth, Georgia. The goal is to become a trusted local resource for residents, visitors, businesses, and families.

## Purpose
- Provide a polished, mobile-first hub for local community information
- Support future growth into news, events, guides, directories, and business listings
- Prioritize performance, accessibility, SEO, and AI-friendly structure

## Local development
1. Start a local WordPress environment with the project root mounted as the web root.
2. Place this repository in the WordPress themes directory at: wordpress/wp-content/themes/southforsyth
3. Activate the South Forsyth theme from the WordPress admin.
4. Create menus and widgets in Appearance > Menus and Appearance > Widgets to populate the header, footer, and sidebars.

## Theme structure
- inc/setup.php — core theme supports and setup
- inc/enqueue.php — CSS and JS asset loading
- inc/menus.php — menu registration
- inc/widgets.php — widget area registration
- inc/schema.php — SEO and schema helpers
- inc/helpers.php — reusable rendering helpers
- template-parts/header/site-header.php — header partial
- template-parts/footer/site-footer.php — footer partial
- template-parts/components/hero.php — homepage hero
- template-parts/components/card-grid.php — reusable card-grid section
- template-parts/components/newsletter.php — newsletter signup block
- assets/css/main.css — stylesheet
- assets/js/main.js — small interactive enhancements

## DreamHost deployment workflow

### 1. Configure your environment
Copy the example environment file and fill in your DreamHost details:

```bash
cp deploy.example.env .env
```

Then edit .env and set:
- DREAMHOST_USER
- DREAMHOST_SERVER
- DREAMHOST_REMOTE_PATH
- LOCAL_THEME_PATH

The file .env is ignored by Git so credentials remain local.

### 2. Pull the live theme
Use this when you want to sync the currently live DreamHost theme down to your local workspace:

```bash
./pull-live.sh
```

### 3. Deploy the local theme
After editing locally and committing to GitHub, deploy the theme to DreamHost with:

```bash
./deploy.sh
```

The deployment script uses rsync over SSH and excludes common local-only files such as .DS_Store, node_modules, logs, cache files, and environment files.

### 4. Connect VS Code to DreamHost
Recommended workflow:
- Use VS Code with the project folder open.
- Keep local development in this repository.
- Use GitHub as the source of truth.
- Use SFTP/SSH remote access or a DreamHost-compatible remote connection if you want to browse the server directly from VS Code.
- Keep the deployment process scripted so it remains repeatable and consistent.

### 5. Recommended development loop
```bash
git status
./pull-live.sh
# edit locally
git add .
git commit
# push to GitHub
git push
./deploy.sh
```

This keeps local development first, uses GitHub as the source of truth, and only deploys after a commit is ready.

## Deployment notes
- Keep the theme lightweight and plugin-free where possible.
- Use caching, image optimization, and a CDN when moving to production.
- Review SEO metadata and schema output regularly as content expands.

## Current status
- Theme foundation and homepage are implemented with placeholder content.
- Navigation, widgets, footer, and reusable components are wired up.
- SEO-ready metadata and schema placeholders are included.

## Next steps
- Replace placeholder content with WordPress-driven dynamic queries.
- Add custom post types for events, guides, businesses, and restaurants.
- Expand the homepage into richer local content sections and search experiences.
