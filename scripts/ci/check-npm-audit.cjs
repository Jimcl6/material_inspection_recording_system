const fs = require('node:fs');

const allowedAdvisorySources = new Set([
    1102341,
    1107323,
    1107327,
    1109132,
    1116229,
    1120061,
    1120784,
    1123525
]);

const reportPath = process.argv[2];

if (!reportPath || !fs.existsSync(reportPath)) {
    console.error('npm audit report is missing.');
    process.exit(2);
}

const report = JSON.parse(fs.readFileSync(reportPath, 'utf8').replace(/^\uFEFF/, ''));
const discoveredSources = new Set();

for (const vulnerability of Object.values(report.vulnerabilities || {})) {
    for (const via of vulnerability.via || []) {
        if (typeof via === 'object' && Number.isInteger(via.source)) {
            discoveredSources.add(via.source);
        }
    }
}

const unexpected = [...discoveredSources].filter((source) => !allowedAdvisorySources.has(source));

for (const source of discoveredSources) {
    const disposition = allowedAdvisorySources.has(source) ? 'informational' : 'blocking';
    console.log(`${disposition} npm advisory source: ${source}`);
}

if (unexpected.length > 0) {
    console.error('npm audit found advisories outside the reviewed Vite toolchain set.');
    process.exit(1);
}

console.log('npm audit gate passed; no unexpected advisories were found.');
