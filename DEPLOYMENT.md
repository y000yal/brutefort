# BruteFort Plugin Deployment Guide

This guide will help you prepare and deploy your BruteFort plugin for production use.

## 🚀 Pre-Deployment Checklist

### 1. Code Quality
- [ ] Run `npm run lint` to check for code style issues
- [ ] Run `npm run type-check` to verify TypeScript compilation
- [ ] Ensure all tests pass with `npm test`
- [ ] Review and update version numbers in:
  - `brutefort.php` (Plugin header)
  - `package.json`
  - `readme.txt`

### 2. Build Process
- [ ] Clean previous builds: `npm run clean`
- [ ] Build production assets: `npm run build`
- [ ] Verify build output in `assets/build/` directory
- [ ] Check that CSS and JS files are minified and optimized

### 3. File Verification
- [ ] Ensure `License.txt` is present and correct
- [ ] Verify `readme.txt` is up to date
- [ ] Check that `uninstall.php` is properly configured
- [ ] Confirm all required PHP files are present

### 4. Dependencies
- [ ] Run `composer install --no-dev --optimize-autoloader` for production
- [ ] Verify `vendor/` directory contains only production dependencies
- [ ] Check that `node_modules/` is not included in deployment

## 📦 Deployment Commands

### Quick Deployment
```bash
# Full deployment preparation
npm run deploy:prepare

# Create deployment zip
npm run deploy:zip
```

### Manual Deployment Steps
```bash
# 1. Clean and build
npm run build:clean

# 2. Run quality checks
npm run lint
npm run type-check

# 3. Create deployment package
npm run deploy:zip
```

## 🎯 Deployment Options

### Option 1: WordPress.org Repository
1. Create a clean zip file using `npm run deploy:zip`
2. Upload to WordPress.org plugin repository
3. Follow WordPress.org guidelines for plugin submission

### Option 2: Direct Distribution
1. Use the generated zip file from `dist/` directory
2. Distribute via your website or other channels
3. Ensure users can download and install manually

### Option 3: Git Repository Release
1. Tag your release: `git tag v1.0.0`
2. Push tags: `git push --tags`
3. Create GitHub/GitLab release with deployment zip

## 🔧 Production Configuration

### Environment Variables
```bash
# Set production environment
NODE_ENV=production
WP_DEBUG=false
SCRIPT_DEBUG=false
```

### Web Server Configuration
- Enable gzip compression
- Set proper cache headers for static assets
- Configure CDN if applicable

### WordPress Configuration
```php
// Add to wp-config.php for production
define('WP_DEBUG', false);
define('SCRIPT_DEBUG', false);
define('WP_CACHE', true);
```

## 📋 Post-Deployment Verification

### 1. Installation Test
- [ ] Install plugin on fresh WordPress installation
- [ ] Verify activation works without errors
- [ ] Check that all features function correctly
- [ ] Test uninstallation process

### 2. Performance Check
- [ ] Verify assets are properly minified
- [ ] Check page load times
- [ ] Ensure no console errors
- [ ] Validate CSS and JS optimization

### 3. Compatibility Test
- [ ] Test with different WordPress versions
- [ ] Verify compatibility with popular themes
- [ ] Check for conflicts with other plugins
- [ ] Test on different PHP versions

## 🚨 Common Issues & Solutions

### Build Failures
```bash
# Clear all caches and rebuild
npm run clean
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Missing Dependencies
```bash
# Install missing production dependencies
npm install --production
composer install --no-dev
```

### File Size Issues
- Check for unnecessary files in build output
- Verify image optimization
- Review bundle splitting configuration

## 📊 Performance Metrics

### Target Benchmarks
- **JavaScript Bundle**: < 500KB gzipped
- **CSS Bundle**: < 100KB gzipped
- **Plugin Size**: < 2MB total
- **Load Time**: < 100ms additional

### Monitoring Tools
- Chrome DevTools Performance tab
- WebPageTest.org
- GTmetrix
- Lighthouse

## 🔒 Security Considerations

### Before Deployment
- [ ] Remove any debug code
- [ ] Verify no sensitive data in code
- [ ] Check file permissions
- [ ] Validate input sanitization

### Production Security
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Monitor error logs
- [ ] Regular security updates

## 📞 Support & Maintenance

### User Support
- Provide clear installation instructions
- Document common issues and solutions
- Offer support channels (email, forum, etc.)

### Maintenance Schedule
- Regular compatibility updates
- Security patches as needed
- Feature updates and improvements
- Performance optimizations

## 🎉 Success Checklist

- [ ] Plugin builds successfully
- [ ] All tests pass
- [ ] No linting errors
- [ ] Production assets optimized
- [ ] Deployment package created
- [ ] Installation tested
- [ ] Documentation updated
- [ ] Support channels ready

---

**Need Help?** Check the plugin documentation or contact support at [https://brutefort.com/support/](https://brutefort.com/support/)
