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

const manifestPath = path.join(publicPath, 'manifest.webmanifest');
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

const worker = readRequiredFile(path.join(publicPath, 'sw.js')).toString('utf8');

if (!worker.includes('const BUILD_ID =')) {
    fail('generated worker does not contain a release build ID');
}

if (!worker.includes('SKIP_WAITING')) {
    fail('generated worker does not support user-approved updates');
}

if (worker.includes("addEventListener('fetch'") || worker.includes('caches.')) {
    fail('generated worker must not cache authenticated requests or application data');
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
    '/sw.js',
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
    !nginx.includes('location = /sw.js')
    || !nginx.includes('Cache-Control "no-cache" always')
) {
    fail('Nginx does not serve the root worker with no-cache headers');
}

if (
    !apache.includes('<FilesMatch "^sw\\.js$">')
    || !apache.includes('Cache-Control "no-cache"')
) {
    fail('Apache does not serve the root worker with no-cache headers');
}

if (!dockerfile.includes('rm -f public/hot')) {
    fail('production image does not remove the Vite hot marker');
}

console.log(
    `PWA verification passed: standalone manifest, ${requiredIcons.length} Android icons, `
    + 'root-scoped online-only worker, install/update UI, and production safeguards.',
);
