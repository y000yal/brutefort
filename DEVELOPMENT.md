# Development Guide - BruteFort Plugin

## Code Quality Workflow

### Pre-commit Checks
Before committing code, ensure it passes all quality checks:

```bash
# Run code quality checks
npm run quality

# Or use Grunt directly
grunt quality

# Run pre-commit checks
npm run precommit
```

### Automatic Code Fixing
Many coding standard issues can be automatically fixed:

```bash
# Auto-fix PHPCS issues
npm run phpcs:fix

# Then run checks again to ensure all issues are resolved
npm run phpcs
```

### Release Process
The release process now includes automatic code quality checks:

```bash
# Full release with quality checks
npm run release

# Or use Grunt directly
grunt release
```

This will:
1. ✅ Run PHPCS code quality checks
2. ✅ Build production assets
3. ✅ Generate language files
4. ✅ Create distribution package

### Available Commands

| Command | Description |
|---------|-------------|
| `npm run quality` | Check code quality with PHPCS |
| `npm run precommit` | Run pre-commit quality checks |
| `npm run phpcs` | Run PHPCS manually |
| `npm run phpcs:fix` | Auto-fix PHPCS issues |
| `npm run release` | Full release with quality checks |

### Pre-commit Hook Setup

#### For Git Hooks (Optional)
You can set up automatic pre-commit checks by copying the pre-commit script:

**Linux/Mac:**
```bash
cp scripts/pre-commit.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

**Windows:**
```cmd
copy scripts\pre-commit.bat .git\hooks\pre-commit
```

This will automatically run code quality checks before every commit.

### Code Quality Standards
- **WordPress Coding Standards**: All PHP code follows WordPress standards
- **Security**: All input sanitized, output escaped, SQL prepared
- **Documentation**: Complete PHPDoc comments for all classes and methods
- **Naming**: Consistent snake_case for functions, PascalCase for classes

### Troubleshooting

#### PHPCS Errors
If you encounter PHPCS errors:

1. **Run auto-fix first:**
   ```bash
   npm run phpcs:fix
   ```

2. **Check remaining issues:**
   ```bash
   npm run phpcs
   ```

3. **Fix manually** any remaining issues that can't be auto-fixed

#### Common Issues
- **File naming**: WordPress standards require `class-` prefix for class files
- **Yoda conditions**: Use `'value' === $variable` instead of `$variable === 'value'`
- **Inline comments**: Must end with proper punctuation (., !, ?)
- **Documentation**: All public methods must have complete PHPDoc blocks

### Continuous Integration
The release process ensures:
- ✅ Code quality standards are met
- ✅ Security best practices are followed
- ✅ Documentation is complete
- ✅ No critical issues remain

This guarantees that every release meets WordPress.org standards and is production-ready.
