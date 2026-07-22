const fs = require('fs');
const path = 'resources/views/sales/pos.blade.php';

let content = fs.readFileSync(path, 'utf8');

// Find the script block
const pushStart = content.indexOf("@push('scripts')");
const pushEnd = content.indexOf("@endpush", pushStart) + "@endpush".length;

if (pushStart === -1) {
    console.log("Could not find @push('scripts')");
    process.exit(1);
}

const scriptBlock = content.substring(pushStart, pushEnd);

// Remove the push block from the bottom
content = content.slice(0, pushStart) + content.slice(pushEnd);

// Remove the @push('scripts') and @endpush directives from the extracted block
let cleanScript = scriptBlock
    .replace("@push('scripts')\n", "")
    .replace("@push('scripts')", "")
    .replace("@endpush\n", "")
    .replace("@endpush", "");

// Find @section('content')
const sectionStart = content.indexOf("@section('content')");
if (sectionStart === -1) {
    console.log("Could not find @section('content')");
    process.exit(1);
}

const insertPos = sectionStart + "@section('content')".length;

// Insert the script right after @section('content')
content = content.slice(0, insertPos) + "\n" + cleanScript + "\n" + content.slice(insertPos);

fs.writeFileSync(path, content);
console.log("Successfully moved posManager script to the top of the content section.");
