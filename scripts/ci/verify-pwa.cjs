const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const publicPath = path.join(root, 'public');
const buildPath = path.join(publicPath, 'build');

const fail = (message) => {
    throw new Error(`PWA verification failed: ${message}`);
};

const readRequiredFile = (filePath) => {
    if (!fs.existsSync(filePath)) {
        fail(`missing ${path.relative(root, filePath)}`);
    }

    return fs.readFileSync(filePath);
};

const manifestPath = path.join(buildPath, 'manifest.webmanifest');
const manifest = JSON.parse(readRequiredFile(manifestPath).toString('utf8'));

const expectedManifest = {
    id: '/dashboard',
    start_url: '/dashboard',
    scope: '/',
    display: 'standalone',
    prefer_related_applications: false,
};

for (const [key, expected] of Object.entries(expectedManifest)) {
    if (manifest[key] !== expected) {
        fail(`manifest ${key} must be ${JSON.stringify(expected)}`);
    }
}

const requiredIcons = [
    { src: '/pwa-192x192.png', width: 192, height: 192, purpose: undefined },
    { src: '/pwa-512x512.png', width: 512, height: 512, purpose: 'any' },
    { src: '/maskable-icon-512x512.png', width: 512, height: 512, purpose: 'maskable' },
];

for (const requiredIcon of requiredIcons) {
    const manifestIcon = manifest.icons?.find(
        (icon) =>
            icon.src === requiredIcon.src
            && icon.purpose === requiredIcon.purpose,
    );

    if (!manifestIcon) {
        fail(`manifest is missing ${requiredIcon.src} (${requiredIcon.purpose || 'default'})`);
    }

    const iconPath = path.join(publicPath, requiredIcon.src.slice(1));
    const icon = readRequiredFile(iconPath);
    const pngSignature = icon.subarray(0, 8).toString('hex');

    if (pngSignature !== '89504e470d0a1a0a') {
        fail(`${requiredIcon.src} is not a PNG file`);
    }

    const width = icon.readUInt32BE(16);
    const height = icon.readUInt32BE(20);

    if (width !== requiredIcon.width || height !== requiredIcon.height) {
        fail(`${requiredIcon.src} must be ${requiredIcon.width}x${requiredIcon.height}`);
    }
}

const worker = readRequiredFile(path.join(buildPath, 'sw.js')).toString('utf8');

if (!worker.includes('precacheAndRoute')) {
    fail('generated worker does not precache versioned frontend assets');
}

if (worker.includes('registerRoute')) {
    fail('generated worker must not runtime-cache authenticated requests');
}

for (const forbiddenPath of ['/dashboard', '/login', '/api/']) {
    if (worker.includes(forbiddenPath)) {
        fail(`generated worker unexpectedly caches ${forbiddenPath}`);
    }
}

const appBundles = fs
    .readdirSync(path.join(buildPath, 'assets'))
    .filter((file) => /^app-.*\.js$/.test(file))
    .map((file) => fs.readFileSync(path.join(buildPath, 'assets', file), 'utf8'));
const appBundle = appBundles.join('\n');

for (const requiredRuntimeSignal of [
    '/build/sw.js',
    'beforeinstallprompt',
    'appinstalled',
    'Install MIRS',
    'A MIRS update is ready',
]) {
    if (!appBundle.includes(requiredRuntimeSignal)) {
        fail(`compiled app is missing ${requiredRuntimeSignal}`);
    }
}

const nginx = readRequiredFile(
    path.join(root, 'docker', 'nginx', 'default.conf'),
).toString('utf8');
const apache = readRequiredFile(
    path.join(publicPath, '.htaccess'),
).toString('utf8');
const dockerfile = readRequiredFile(
    path.join(root, 'Dockerfile'),
).toString('utf8');

if (
    !nginx.includes('location = /build/sw.js')
    || !nginx.includes('Service-Worker-Allowed "/" always')
) {
    fail('Nginx does not authorize the worker root scope');
}

if (
    !apache.includes('<FilesMatch "^sw\\.js$">')
    || !apache.includes('Service-Worker-Allowed "/"')
) {
    fail('Apache does not authorize the worker root scope');
}

if (!dockerfile.includes('rm -f public/hot')) {
    fail('production image does not remove the Vite hot marker');
}

console.log(
    `PWA verification passed: standalone manifest, ${requiredIcons.length} Android icons, `
    + 'online-only worker, install/update UI, and production server safeguards.',
);
