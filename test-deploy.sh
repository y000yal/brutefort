#!/bin/bash

# BruteFort Plugin Deployment Test Script
# This script tests the deployment setup

set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🧪 Testing BruteFort Plugin Deployment Setup${NC}"
echo -e "${BLUE}============================================${NC}"

# Test 1: Check if required files exist
echo -e "\n${BLUE}Test 1: Required Files${NC}"
required_files=(
    "brutefort.php"
    "License.txt"
    "readme.txt"
    "uninstall.php"
    "package.json"
    "webpack.config.js"
    "deploy.sh"
    "deploy.config.js"
    "DEPLOYMENT.md"
)

missing_files=()
for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "  ✅ $file"
    else
        echo -e "  ❌ $file (missing)"
        missing_files+=("$file")
    fi
done

if [ ${#missing_files[@]} -eq 0 ]; then
    echo -e "  ${GREEN}All required files present${NC}"
else
    echo -e "  ${RED}Missing files: ${missing_files[*]}${NC}"
fi

# Test 2: Check package.json scripts
echo -e "\n${BLUE}Test 2: Package Scripts${NC}"
if [ -f "package.json" ]; then
    scripts=("build" "build:clean" "deploy:prepare" "deploy:zip")
    for script in "${scripts[@]}"; do
        if grep -q "\"$script\"" package.json; then
            echo -e "  ✅ $script script found"
        else
            echo -e "  ❌ $script script missing"
        fi
    done
else
    echo -e "  ${RED}package.json not found${NC}"
fi

# Test 3: Check webpack configuration
echo -e "\n${BLUE}Test 3: Webpack Configuration${NC}"
if [ -f "webpack.config.js" ]; then
    if grep -q "production" webpack.config.js; then
        echo -e "  ✅ Production mode configured"
    else
        echo -e "  ❌ Production mode not configured"
    fi
    
    if grep -q "TerserPlugin" webpack.config.js; then
        echo -e "  ✅ Code minification configured"
    else
        echo -e "  ❌ Code minification not configured"
    fi
else
    echo -e "  ${RED}webpack.config.js not found${NC}"
fi

# Test 4: Check deployment script permissions
echo -e "\n${BLUE}Test 4: Script Permissions${NC}"
if [ -x "deploy.sh" ]; then
    echo -e "  ✅ deploy.sh is executable"
else
    echo -e "  ❌ deploy.sh is not executable"
    chmod +x deploy.sh
    echo -e "  🔧 Made deploy.sh executable"
fi

# Test 5: Check directory structure
echo -e "\n${BLUE}Test 5: Directory Structure${NC}"
directories=("includes" "src" "assets" "scripts")
for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        echo -e "  ✅ $dir/ directory exists"
    else
        echo -e "  ❌ $dir/ directory missing"
    fi
done

# Test 6: Check for development files that should be excluded
echo -e "\n${BLUE}Test 6: Development Files (should be excluded)${NC}"
dev_files=("node_modules" ".git" "src" "webpack.config.js")
for file in "${dev_files[@]}"; do
    if [ -e "$file" ]; then
        echo -e "  ⚠️  $file exists (will be excluded from deployment)"
    else
        echo -e "  ✅ $file not found"
    fi
done

# Test 7: Check license file
echo -e "\n${BLUE}Test 7: License File${NC}"
if [ -f "License.txt" ]; then
    if grep -q "GNU General Public License v3" License.txt; then
        echo -e "  ✅ GPL v3 license found"
    else
        echo -e "  ❌ GPL v3 license not found"
    fi
else
    echo -e "  ${RED}License.txt not found${NC}"
fi

# Summary
echo -e "\n${BLUE}📊 Test Summary${NC}"
echo -e "=================="

if [ ${#missing_files[@]} -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed! Your plugin is ready for deployment.${NC}"
    echo -e "\n${BLUE}Next steps:${NC}"
    echo -e "1. Run: ${GREEN}npm install${NC}"
    echo -e "2. Run: ${GREEN}npm run build${NC}"
    echo -e "3. Run: ${GREEN}./deploy.sh${NC}"
    echo -e "4. Check the generated files in ${GREEN}assets/build/${NC}"
else
    echo -e "${RED}❌ Some tests failed. Please fix the missing files before deployment.${NC}"
fi

echo -e "\n${BLUE}For detailed deployment instructions, see: DEPLOYMENT.md${NC}"
