# WebP Converter & Optimizer

A powerful WordPress plugin to automatically convert and optimize images to WebP format, reducing file sizes and improving page load speeds.

## Features

### 🖼️ Image Converter
- Upload and convert individual images to WebP
- Multiple file upload support
- Image preview with crop functionality
- Individual image management (crop/remove)
- Drag-and-drop interface

### 📦 Bulk Converter
- Convert entire media library to WebP
- Batch processing system (configurable batch size)
- Real-time progress tracking
- Option to delete original images
- Adjustable quality settings

### ⚙️ Settings
- Default quality control (1-100)
- Auto-convert on upload
- Keep/delete original images
- Supported formats (JPEG, PNG, GIF)
- Batch size configuration

### 🎨 Image Cropping
- Built-in image cropper
- Zoom and pan controls
- 4:3 aspect ratio
- Preview before conversion
- Per-image crop support

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'WebP Optimizer' in the admin menu

## Development

### Prerequisites
- Node.js (v14+)
- npm
- PHP 8.0+
- WordPress 5.0+
- GD library with WebP support

### Setup
```bash
npm install
npm run build
```

### Build for Production
```bash
npm run build
```

### Tech Stack
- **Frontend**: React 18, TypeScript
- **Build**: Webpack 5
- **Image Processing**: PHP GD Library, react-easy-crop
- **WordPress**: AJAX API, Options API

## Usage

### Image Converter
1. Go to WebP Optimizer → Image Converter
2. Select one or multiple images
3. Optionally crop individual images
4. Click "Convert to WebP"

### Bulk Converter
1. Go to WebP Optimizer → Bulk Converter
2. Set quality and options
3. Click "Start Bulk Conversion"
4. Wait for batch processing to complete

### Settings
1. Go to WebP Optimizer → Settings
2. Configure default quality
3. Enable/disable auto-convert on upload
4. Choose supported formats
5. Set batch size for bulk operations

## Requirements

- WordPress 5.0 or higher
- PHP 8.0 or higher
- GD library with WebP support
- Modern browser with JavaScript enabled

## File Structure

```
webp-converter-optimizer/
├── admin/                          # Admin-specific functionality
│   ├── css/                        # Admin styles
│   ├── js/                         # Admin scripts
│   ├── class-admin-menu-handler.php
│   ├── class-admin-image-converter-ajax.php
│   ├── class-admin-bulk-converter-ajax.php
│   ├── class-admin-settings-ajax.php
│   └── class-webp-converter-optimizer-admin.php
├── includes/                       # Core plugin classes
│   ├── class-webp-converter-optimizer.php
│   ├── class-webp-converter-optimizer-loader.php
│   └── class-webp-converter-optimizer-i18n.php
├── src/                           # React source files
│   ├── components/
│   │   ├── ImageConverter.tsx
│   │   ├── BulkConverter.tsx
│   │   ├── Settings.tsx
│   │   └── ImageCropper.tsx
│   └── AdminMenu.tsx
├── build/                         # Compiled assets
├── languages/                     # Translation files
├── public/                        # Public-facing functionality
├── node_modules/                  # Node dependencies (gitignored)
├── package.json
├── webpack.config.js
├── tsconfig.json
└── webp-converter-optimizer.php   # Main plugin file
```

## Hooks & Filters

### Actions
- `add_attachment` - Auto-convert on upload (if enabled)

### Options
- `webp_optimizer_settings` - Plugin settings storage

## Security

- Nonce verification on all AJAX requests
- Capability checks (`upload_files`, `manage_options`)
- Input sanitization
- File type validation

## Performance

- Batch processing prevents timeouts
- Configurable batch sizes
- Efficient memory management
- Optimized image processing

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Opera (latest)

## License

GPL-2.0+

## Author

Shaktisinh Jadeja

## Support

For issues and feature requests, please use the GitHub repository.

## Changelog

### 1.0.0
- Initial release
- Image converter with crop functionality
- Bulk converter with batch processing
- Settings management
- Auto-convert on upload
- Multiple file support
