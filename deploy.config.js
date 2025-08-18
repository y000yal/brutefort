/**
 * BruteFort Plugin Deployment Configuration
 * 
 * This file contains all deployment-related configuration options
 */

module.exports = {
    // Plugin information
    plugin: {
        name: 'brutefort',
        version: '1.0.0',
        author: 'Y0000el',
        website: 'https://brutefort.com/',
        support: 'https://brutefort.com/support/',
        documentation: 'https://brutefort.com/docs/'
    },

    // Build configuration
    build: {
        // Output directories
        output: {
            assets: 'assets/build',
            dist: 'dist',
            zip: 'dist'
        },

        // File patterns to include in deployment
        include: [
            'includes/**/*',
            'assets/**/*',
            'languages/**/*',
            '*.php',
            'readme.txt',
            'License.txt',
            'uninstall.php'
        ],

        // File patterns to exclude from deployment
        exclude: [
            'node_modules/**/*',
            'src/**/*',
            'dist/**/*',
            'scripts/**/*',
            'tests/**/*',
            '.git/**/*',
            '.github/**/*',
            'webpack.config.js',
            'package.json',
            'package-lock.json',
            'composer.json',
            'composer.lock',
            'tsconfig.json',
            'tailwind.config.js',
            'postcss.config.js',
            '.babelrc',
            '.eslintrc.js',
            '.prettierrc',
            '.gitignore',
            'README.md',
            'CHANGELOG.md',
            'DEPLOYMENT.md',
            'deploy.sh',
            'deploy.config.js',
            '*.log',
            '*.map',
            '.sass-cache/',
            '.DS_Store',
            'Thumbs.db'
        ],

        // Development dependencies to exclude
        devDependencies: [
            'webpack-dev-server',
            'webpackbar',
            'style-loader',
            'source-map',
            'eslint',
            'jest',
            'typescript'
        ]
    },

    // Quality checks
    quality: {
        // Linting configuration
        lint: {
            enabled: true,
            extensions: ['.ts', '.tsx', '.js', '.jsx'],
            config: '.eslintrc.js'
        },

        // Type checking
        typeCheck: {
            enabled: true,
            config: 'tsconfig.json'
        },

        // Testing
        testing: {
            enabled: false, // Enable when tests are added
            framework: 'jest'
        }
    },

    // Performance targets
    performance: {
        // Bundle size limits (in bytes)
        limits: {
            js: 500 * 1024,      // 500KB
            css: 100 * 1024,     // 100KB
            total: 2 * 1024 * 1024 // 2MB
        },

        // Load time targets
        loadTime: {
            additional: 100 // milliseconds
        }
    },

    // WordPress compatibility
    wordpress: {
        minVersion: '5.0',
        maxVersion: '6.5',
        minPhpVersion: '7.4',
        testedUpTo: '6.5'
    },

    // Deployment options
    deployment: {
        // WordPress.org repository
        wordpressOrg: {
            enabled: true,
            guidelines: 'https://developer.wordpress.org/plugins/wordpress-org/',
            requirements: [
                'GPL compatible license',
                'No external dependencies',
                'Proper security practices',
                'Accessibility compliance'
            ]
        },

        // Direct distribution
        directDistribution: {
            enabled: true,
            platforms: [
                'GitHub Releases',
                'Plugin website',
                'Direct download'
            ]
        },

        // Git repository
        gitRepository: {
            enabled: true,
            platforms: [
                'GitHub',
                'GitLab',
                'Bitbucket'
            ]
        }
    },

    // Security settings
    security: {
        // File permissions
        permissions: {
            directories: '755',
            files: '644',
            executable: '755'
        },

        // Security checks
        checks: [
            'No debug code',
            'No sensitive data',
            'Input sanitization',
            'Output escaping',
            'Nonce verification',
            'Capability checks'
        ]
    },

    // Support and maintenance
    support: {
        // Support channels
        channels: {
            email: 'support@brutefort.com',
            forum: 'https://brutefort.com/forum/',
            documentation: 'https://brutefort.com/docs/',
            github: 'https://github.com/y000yal/brutefort/issues'
        },

        // Maintenance schedule
        maintenance: {
            compatibility: 'monthly',
            security: 'as-needed',
            features: 'quarterly',
            performance: 'quarterly'
        }
    }
};
