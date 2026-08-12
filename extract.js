const fs = require('fs');
const content = fs.readFileSync('register.php', 'utf8');
const scriptRegex = /<script>([\s\S]*?)<\/script>/g;
let match;
let count = 0;
while ((match = scriptRegex.exec(content)) !== null) {
    fs.writeFileSync(`test_script_${count}.js`, match[1]);
    count++;
}
console.log(`Extracted ${count} scripts.`);
