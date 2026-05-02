import { spawn } from 'node:child_process';
import { mkdir, rm, writeFile } from 'node:fs/promises';
import { join, resolve } from 'node:path';

const chromePath = 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe';
const port = 9224;
const baseUrl = 'https://demo.tocoluca.com';
const outDir = resolve('public/lp-screenshots');
const profileDir = resolve('storage/app/lp-screenshot-chrome-profile');

const shots = [
  {
    name: 'company-dashboard',
    url: `${baseUrl}/company/dashboard`,
    viewport: { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false },
    wait: 2500,
  },
  {
    name: 'reservation-calendar',
    url: `${baseUrl}/company/reserve`,
    viewport: { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false },
    wait: 2500,
  },
  {
    name: 'reservation-list',
    url: `${baseUrl}/company/reservations`,
    viewport: { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false },
    wait: 2500,
  },
  {
    name: 'customers',
    url: `${baseUrl}/company/customers`,
    viewport: { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false },
    wait: 2500,
  },
  {
    name: 'public-reservation-mobile',
    url: `${baseUrl}/r/DEMO`,
    viewport: { width: 430, height: 1100, deviceScaleFactor: 1, mobile: true },
    wait: 3000,
  },
];

const delay = (ms) => new Promise((resolveDelay) => setTimeout(resolveDelay, ms));

async function requestJson(path, options = {}) {
  const response = await fetch(`http://127.0.0.1:${port}${path}`, options);
  if (!response.ok) {
    throw new Error(`${path} failed: ${response.status} ${response.statusText}`);
  }
  return response.json();
}

async function waitForChrome() {
  const startedAt = Date.now();
  while (Date.now() - startedAt < 15000) {
    try {
      return await requestJson('/json/version');
    } catch {
      await delay(250);
    }
  }
  throw new Error('Chrome remote debugging endpoint did not become ready.');
}

function connect(wsUrl) {
  const socket = new WebSocket(wsUrl);
  let nextId = 1;
  const pending = new Map();

  socket.addEventListener('message', (event) => {
    const message = JSON.parse(event.data);
    if (!message.id) return;
    const entry = pending.get(message.id);
    if (!entry) return;
    pending.delete(message.id);
    if (message.error) {
      entry.reject(new Error(`${entry.method}: ${message.error.message}`));
      return;
    }
    entry.resolve(message.result ?? {});
  });

  const ready = new Promise((resolveReady, rejectReady) => {
    socket.addEventListener('open', resolveReady, { once: true });
    socket.addEventListener('error', rejectReady, { once: true });
  });

  return {
    ready,
    send(method, params = {}) {
      const id = nextId++;
      socket.send(JSON.stringify({ id, method, params }));
      return new Promise((resolveSend, rejectSend) => {
        pending.set(id, { resolve: resolveSend, reject: rejectSend, method });
      });
    },
    close() {
      socket.close();
    },
  };
}

async function createPage(url) {
  const target = await requestJson(`/json/new?${encodeURIComponent(url)}`, { method: 'PUT' });
  const client = connect(target.webSocketDebuggerUrl);
  await client.ready;
  await client.send('Page.enable');
  await client.send('Runtime.enable');
  await client.send('Network.enable');
  return client;
}

async function setViewport(client, viewport) {
  await client.send('Emulation.setDeviceMetricsOverride', {
    width: viewport.width,
    height: viewport.height,
    deviceScaleFactor: viewport.deviceScaleFactor,
    mobile: viewport.mobile,
  });
}

async function navigate(client, url, wait = 1800) {
  await client.send('Page.navigate', { url });
  await delay(wait);
}

async function evaluate(client, expression) {
  return client.send('Runtime.evaluate', {
    expression,
    awaitPromise: true,
    returnByValue: true,
  });
}

async function capture(client, path) {
  const result = await client.send('Page.captureScreenshot', {
    format: 'png',
    fromSurface: true,
  });
  await writeFile(path, Buffer.from(result.data, 'base64'));
}

async function login(client) {
  await navigate(client, `${baseUrl}/company/login`, 2500);
  await evaluate(client, `
    (() => {
      document.querySelector('#company_code').value = 'DEMO';
      document.querySelector('#staff_code').value = 'MASTER01';
      document.querySelector('#password').value = '12345678';
      document.querySelector('form').submit();
      return true;
    })()
  `);
  await delay(3500);

  const result = await evaluate(client, 'location.href');
  const currentUrl = result.result?.value ?? '';
  if (!currentUrl.includes('/company/dashboard')) {
    throw new Error(`Login did not reach dashboard. Current URL: ${currentUrl}`);
  }
}

await mkdir(outDir, { recursive: true });
await rm(profileDir, { recursive: true, force: true });
await mkdir(profileDir, { recursive: true });

const chrome = spawn(chromePath, [
  '--headless=new',
  `--remote-debugging-port=${port}`,
  `--user-data-dir=${profileDir}`,
  '--no-first-run',
  '--disable-gpu',
  '--hide-scrollbars',
  '--window-size=1440,1000',
  'about:blank',
], {
  stdio: 'ignore',
});

try {
  await waitForChrome();
  const client = await createPage(`${baseUrl}/company/login`);
  await setViewport(client, { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
  await login(client);

  for (const shot of shots) {
    await setViewport(client, shot.viewport);
    await navigate(client, shot.url, shot.wait);
    await capture(client, join(outDir, `${shot.name}.png`));
    console.log(`saved public/lp-screenshots/${shot.name}.png`);
  }

  client.close();
} finally {
  chrome.kill();
}
