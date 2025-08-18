#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

// Configuration
const PLUGIN_NAME = 'brutefort';
const VERSION = require('../package.json').version;
const OUTPUT_DIR = path.join(__dirname, '../dist');
const OUTPUT_FILE = `${PLUGIN_NAME}-v${VERSION}.zip`;

// Files and directories to include
const INCLUDE_PATTERNS = [
    'includes/**/*',
    'assets/**/*',
    'languages/**/*',
    '*.php',
    'readme.txt',
    'License.txt',
    'uninstall.php'
];

// Files and directories to exclude
const EXCLUDE_PATTERNS = [
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
    '*.log',
    '*.map'
];

// Development files to exclude
const DEV_FILES = [
    'webpack-dev-server',
    'webpackbar',
    'style-loader',
    'source-map'
];

function shouldIncludeFile(filePath) {
    // Check if file matches any exclude pattern
    for (const pattern of EXCLUDE_PATTERNS) {
        if (filePath.includes(pattern.replace('/**/*', ''))) {
            return false;
        }
    }
    
    // Check if file is a development dependency
    for (const devFile of DEV_FILES) {
        if (filePath.includes(devFile)) {
            return false;
        }
    }
    
    return true;
}

function createDeploymentZip() {
    console.log('🚀 Creating deployment package...');
    console.log(`📦 Plugin: ${PLUGIN_NAME} v${VERSION}`);
    
    // Create output directory if it doesn't exist
    if (!fs.existsSync(OUTPUT_DIR)) {
        fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    }
    
    const outputPath = path.join(OUTPUT_DIR, OUTPUT_FILE);
    const output = fs.createWriteStream(outputPath);
    const archive = archiver('zip', {
        zlib: { level: 9 } // Maximum compression
    });
    
    output.on('close', () => {
        const sizeInMB = (archive.pointer() / 1024 / 1024).toFixed(2);
        console.log(`✅ Deployment package created successfully!`);
        console.log(`📁 Location: ${outputPath}`);
        console.log(`📏 Size: ${sizeInMB} MB`);
        console.log(`🔍 Files included: ${archive.pointer()} bytes`);
    });
    
    archive.on('warning', (err) => {
        if (err.code === 'ENOENT') {
            console.warn('⚠️  Warning:', err.message);
        } else {
            throw err;
        }
    });
    
    archive.on('error', (err) => {
        throw err;
    });
    
    archive.pipe(output);
    
    // Add files to archive
    const pluginDir = path.join(__dirname, '..');
    
    function addDirectory(dirPath, archivePath = '') {
        const items = fs.readdirSync(dirPath);
        
        for (const item of items) {
            const fullPath = path.join(dirPath, item);
            const relativePath = path.join(archivePath, item);
            const stat = fs.statSync(fullPath);
            
            if (stat.isDirectory()) {
                // Only add directories that should be included
                if (shouldIncludeFile(relativePath)) {
                    addDirectory(fullPath, relativePath);
                }
            } else if (stat.isFile()) {
                // Only add files that should be included
                if (shouldIncludeFile(relativePath)) {
                    archive.file(fullPath, { name: relativePath });
                    console.log(`➕ Added: ${relativePath}`);
                }
            }
        }
    }
    
    addDirectory(pluginDir);
    
    // Finalize the archive
    archive.finalize();
}

// Run the script
if (require.main === module) {
    try {
        createDeploymentZip();
    } catch (error) {
        console.error('❌ Error creating deployment package:', error.message);
        process.exit(1);
    }
}

module.exports = { createDeploymentZip };
