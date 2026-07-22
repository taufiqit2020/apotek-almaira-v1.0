const fs = require('fs');
const path = require('path');

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(file));
        } else {
            if (file.endsWith('.blade.php')) {
                results.push(file);
            }
        }
    });
    return results;
}

const files = walk('resources/views');
let modifiedCount = 0;

for (const file of files) {
    let content = fs.readFileSync(file, 'utf8');

    // Skip if it doesn't have @push('scripts')
    if (!content.includes("@push('scripts')")) continue;
    
    // Skip app.blade.php or other layouts
    if (file.includes('layouts')) continue;

    // Skip pos.blade.php as we already fixed it
    if (file.includes('pos.blade.php')) continue;

    // Find the push block
    const pushStart = content.indexOf("@push('scripts')");
    let pushEnd = content.indexOf("@endpush", pushStart);
    if (pushEnd === -1) continue;
    
    pushEnd += "@endpush".length;

    let scriptBlock = content.substring(pushStart, pushEnd);
    
    // Remove the push block from its current location
    content = content.slice(0, pushStart) + content.slice(pushEnd);

    // Clean up the script block
    let cleanScript = scriptBlock
        .replace(/@push\('scripts'\)\r?\n?/, "")
        .replace(/@endpush\r?\n?/, "");

    // If it uses alpine:init (like profile/edit.blade.php)
    if (cleanScript.includes('alpine:init') && cleanScript.includes('Alpine.data')) {
        // We will do a generic replacement for standard Alpine.data pattern
        // e.g. document.addEventListener('alpine:init', () => { window.Alpine.data('avatarUpload', () => ({ ... })); });
        cleanScript = cleanScript.replace(/document\.addEventListener\('alpine:init',\s*\(\)\s*=>\s*\{[\s\S]*?window\.Alpine\.data\('([^']+)',\s*\(\)\s*=>\s*\(\{/, "window.$1 = () => ({");
        // Also remove the closing brackets })\); });
        cleanScript = cleanScript.replace(/\}\)\);\s*\}\);/, "});");
    }

    // Convert function foo() to window.foo = function() for total safety
    // Match "function myComp()" or "function myComp() {"
    cleanScript = cleanScript.replace(/function\s+([a-zA-Z0-9_]+)\s*\(/g, "window.$1 = function(");

    // Find @section('content')
    const sectionStart = content.indexOf("@section('content')");
    if (sectionStart !== -1) {
        // Insert right after @section('content')
        // Find the end of the line
        let insertPos = content.indexOf("\n", sectionStart);
        if (insertPos === -1) insertPos = sectionStart + "@section('content')".length;
        else insertPos += 1;

        content = content.slice(0, insertPos) + cleanScript + "\n" + content.slice(insertPos);
    } else {
        // If no content section, put it at the very top
        content = cleanScript + "\n" + content;
    }

    // Also we need to make sure x-data uses parentheses if it was defined as a function
    // Example: x-data="myComp" -> x-data="myComp()"
    // But we only add parentheses if it doesn't have them, and if it's one of the components we moved
    // Let's just blindly add () to x-data="foo" if it matches a word (not an object { ... })
    // Watch out for x-data="{ open: false }"
    content = content.replace(/x-data="([a-zA-Z0-9_]+)"/g, 'x-data="$1()"');

    fs.writeFileSync(file, content);
    console.log('Fixed:', file);
    modifiedCount++;
}

console.log('Total files fixed:', modifiedCount);
