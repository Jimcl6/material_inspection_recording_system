const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');

const root = path.resolve(__dirname, '../..');
const baselinePath = path.join(__dirname, 'eslint-baseline.json');
const eslintPath = path.join(path.dirname(require.resolve('eslint/package.json')), 'bin/eslint.js');

const result = spawnSync(
    process.execPath,
    [eslintPath, 'resources/js', '--ext', '.js,.ts,.vue', '--format', 'json'],
    {
        cwd: root,
        encoding: 'utf8',
        maxBuffer: 64 * 1024 * 1024
    }
);

if (result.error) {
    throw result.error;
}

if (![0, 1].includes(result.status)) {
    process.stderr.write(result.stderr || 'ESLint could not complete.\n');
    process.exit(result.status || 2);
}

const report = JSON.parse(result.stdout || '[]');
const current = {};
let warnings = 0;

for (const file of report) {
    const relativePath = path.relative(root, file.filePath).replaceAll('\\', '/');

    for (const message of file.messages) {
        if (message.severity === 1) {
            warnings += 1;
            continue;
        }

        if (message.severity !== 2) {
            continue;
        }

        const rule = message.ruleId || 'parse-error';
        current[relativePath] ||= {};
        current[relativePath][rule] = (current[relativePath][rule] || 0) + 1;
    }
}

const sortBaseline = (value) => Object.fromEntries(
    Object.entries(value)
        .sort(([left], [right]) => left.localeCompare(right))
        .map(([file, rules]) => [
            file,
            Object.fromEntries(Object.entries(rules).sort(([left], [right]) => left.localeCompare(right)))
        ])
);

if (process.argv.includes('--write-baseline')) {
    fs.writeFileSync(baselinePath, `${JSON.stringify(sortBaseline(current), null, 2)}\n`);
    console.log(`ESLint baseline written with ${Object.keys(current).length} files.`);
    process.exit(0);
}

if (!fs.existsSync(baselinePath)) {
    console.error('ESLint baseline is missing.');
    process.exit(2);
}

const baseline = JSON.parse(fs.readFileSync(baselinePath, 'utf8'));
const regressions = [];

for (const [file, rules] of Object.entries(current)) {
    for (const [rule, count] of Object.entries(rules)) {
        const allowed = baseline[file]?.[rule] || 0;

        if (count > allowed) {
            regressions.push(`${file}: ${rule} increased from ${allowed} to ${count}`);
        }
    }
}

const errorCount = Object.values(current).reduce(
    (total, rules) => total + Object.values(rules).reduce((sum, count) => sum + count, 0),
    0
);

console.log(`ESLint completed: ${errorCount} baselined errors, ${warnings} warnings.`);

if (regressions.length > 0) {
    for (const regression of regressions) {
        console.error(`New ESLint regression: ${regression}`);
    }

    process.exit(1);
}

console.log('ESLint gate passed; no new errors were introduced.');
