# ESS Portal PWA Icons

The PWA requires icon files in the following sizes. You need to create PNG versions of the ESS Portal icon.

## Required Icon Sizes

Place the following PNG files in this directory (`public/assets/images/ess-icons/`):

- `icon-72x72.png` - 72x72 pixels
- `icon-96x96.png` - 96x96 pixels  
- `icon-128x128.png` - 128x128 pixels
- `icon-144x144.png` - 144x144 pixels
- `icon-152x152.png` - 152x152 pixels
- `icon-192x192.png` - 192x192 pixels
- `icon-384x384.png` - 384x384 pixels
- `icon-512x512.png` - 512x512 pixels

## Design Guidelines

1. **Shape**: Square icons with rounded corners (safe area for maskable icons)
2. **Background**: Gradient from `#6366f1` to `#4f46e5`
3. **Icon**: White user/employee silhouette
4. **Padding**: Keep at least 20% padding from edges for maskable icon support

## Quick Generation

You can use the included SVG file (`icon-512x512.svg`) as a base and use tools like:

1. **Online Tools**:
   - [RealFaviconGenerator](https://realfavicongenerator.net/)
   - [PWA Asset Generator](https://progressier.com/pwa-icons-and-splash-screen-generator)

2. **Command Line** (requires ImageMagick):
   ```bash
   convert icon-512x512.svg -resize 72x72 icon-72x72.png
   convert icon-512x512.svg -resize 96x96 icon-96x96.png
   convert icon-512x512.svg -resize 128x128 icon-128x128.png
   convert icon-512x512.svg -resize 144x144 icon-144x144.png
   convert icon-512x512.svg -resize 152x152 icon-152x152.png
   convert icon-512x512.svg -resize 192x192 icon-192x192.png
   convert icon-512x512.svg -resize 384x384 icon-384x384.png
   convert icon-512x512.svg -resize 512x512 icon-512x512.png
   ```

3. **PHP** (if GD extension is available):
   Run the `generate-icons.php` script in this directory.

## Testing

After adding the icons, test your PWA:
1. Open Chrome DevTools → Application → Manifest
2. Check that all icons are loading correctly
3. Test the "Add to Home Screen" functionality
