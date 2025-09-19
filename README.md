# Spectrum

A WordPress plugin that fetches and manages block patterns from a remote API, providing a centralised pattern library for consistent design across multiple websites.

## Overview

Spectrum solves the problem of manually updating block patterns across multiple client websites. Instead of updating patterns individually on each site, you can manage them centrally and have them automatically distributed to all connected websites.

## Features

- **Remote Pattern Fetching**: Securely fetches block patterns from a remote API endpoint
- **Persistent Storage**: Stores patterns in the WordPress options table (never expires)
- **Automatic Sync**: Weekly automatic synchronisation to keep patterns up-to-date
- **Admin Interface**: Clean, WordPress-native admin interface for pattern management
- **Pattern Management**: View all cached patterns with detailed information including:
  - Pattern name, title, and description
  - Categories and keywords
  - Block types, post types, and template types
  - Viewport width and inserter settings
  - Last updated timestamp
- **Screen Options**: Customisable table display with column visibility and pagination
- **Secure API Integration**: Uses basic authentication with username and password defined in wp-config.php

## Requirements

- WordPress 6.8 or higher
- PHP 8.3 or higher
- Valid username and password for your pattern service

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the `spectrum` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your credentials (see Configuration section)

### Via WP-CLI

```bash
wp plugin install /path/to/spectrum.zip --activate
```

## Configuration

### Credentials Setup

Add your username and password to your `wp-config.php` file:

```php
define( 'SPECTRUM_API_USERNAME', 'your-username' );
define( 'SPECTRUM_API_PASSWORD', 'your-password' );
define( 'SPECTRUM_API_URI', 'https://your-api-endpoint.com/patterns' );
```

**Important**: Never store credentials in the database or plugin files. Always use wp-config.php for security.

### API Endpoint

The plugin connects to any compatible pattern service API. The endpoint URL is configurable via the `SPECTRUM_API_URI` constant. Contact your administrator for the correct endpoint URL and credentials.

## Usage

### Initial Setup

1. **Configure API Settings**: Add your username, password, and API URI to wp-config.php
2. **Fetch Patterns**: Go to Settings → Spectrum and click "Update Patterns"
3. **Verify Patterns**: Check that patterns appear in the table and are available in the block editor

### Admin Interface

Navigate to **Settings → Spectrum** to access the plugin's admin interface:

- **Update Patterns**: Manually fetch the latest patterns from the API
- **Storage Status**: View current storage status and last sync time
- **Pattern List**: Browse all cached patterns with detailed information
- **Screen Options**: Customize which columns to display and items per page

### Block Editor Integration

Once patterns are fetched, they automatically appear in the WordPress block editor:

1. Open any post or page in the block editor
2. Click the "+" button to add a block
3. Navigate to the "Patterns" tab
4. Find your patterns organised by category

## Automatic Synchronisation

The plugin automatically syncs patterns weekly using WordPress cron:

- **Frequency**: Every 7 days
- **Automatic**: No manual intervention required
- **Background**: Runs via WordPress cron system
- **Fallback**: Manual sync available if needed

## Pattern Information

Each pattern includes comprehensive metadata:

| Field | Description |
|-------|-------------|
| **Name** | Unique pattern identifier |
| **Title** | Display name in the editor |
| **Categories** | Pattern categories for organisation |
| **Description** | Pattern description and usage notes |
| **Last Updated** | When the pattern was last modified |
| **Block Types** | Compatible block types |
| **Post Types** | Compatible post types |
| **Template Types** | Compatible template types |
| **Viewport Width** | Recommended viewport width |
| **Inserter** | Whether pattern appears in inserter |

## Security

- Credentials are stored securely in wp-config.php
- All API communications use HTTPS
- Pattern content is sanitised before storage
- No sensitive data is stored in the database

## Development

### File Structure

```
spectrum/
├── spectrum.php              # Main plugin file
├── autoload.php              # Class autoloader
├── composer.json             # Composer dependencies
├── includes/
│   └── classes/
│       ├── Admin.php         # Admin interface logic
│       ├── AdminPage.php     # Admin page rendering
│       ├── ApiClient.php     # API communication
│       ├── Cache.php         # Pattern storage management
│       ├── PatternListTable.php # Admin table display
│       ├── Registration.php  # Pattern registration
│       └── Singleton.php     # Singleton trait
└── README.md                 # This file
```

### Hooks and Filters

The plugin provides several WordPress hooks for customization:

- `spectrum_weekly_sync`: Fired during weekly pattern sync
- Admin action hooks for pattern management

## License

GPL-2.0-or-later
