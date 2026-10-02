// INFRA-002 / TEST-024: the QA tooling (Playwright, Lighthouse) never runs against the live site by accident — no load
// testing or aggressive crawling on Production. A production address is refused unless SHELTER_ALLOW_PRODUCTION=1 is
// set for that one run (a read-only post-deploy check the Owner approved). Staging and local addresses pass.
// Self-check: node scripts/base-url-guard.mjs --selftest   (npm run guard:selftest)
import process from 'node:process';
import { fileURLToPath } from 'node:url';

export const PRODUCTION_HOSTS = ['shelterjo.com', 'www.shelterjo.com'];

/** The host of a URL, also when the scheme was left out ("www.shelterjo.com/ar/"). */
function hostOf(url) {
  for (const candidate of [url, `http://${url}`]) {
    try {
      return new URL(candidate).hostname.toLowerCase().replace(/\.$/, '');
    } catch {
      // try the next form
    }
  }
  return '';
}

export function isProductionUrl(url) {
  return Boolean(url) && PRODUCTION_HOSTS.includes(hostOf(String(url).trim()));
}

/** Throws for a production address unless SHELTER_ALLOW_PRODUCTION=1; returns the URL otherwise. */
export function assertNotProduction(url, env = process.env, what = 'SHELTER_BASE_URL') {
  if (!isProductionUrl(url)) return url;
  if (env.SHELTER_ALLOW_PRODUCTION === '1') {
    console.warn(`⚠ ${what} is the live site (${url}): allowed by SHELTER_ALLOW_PRODUCTION=1 — read-only checks only.`);
    return url;
  }
  throw new Error(`Refusing to run against the live site (${what}=${url}). Use local or staging; ` +
    'a read-only check on production needs the Owner\'s approval and SHELTER_ALLOW_PRODUCTION=1 (INFRA-002, TEST-024).');
}

function selftest() {
  const refused = ['https://www.shelterjo.com', 'http://shelterjo.com/ar/', 'HTTPS://WWW.SHELTERJO.COM./', 'https://www.shelterjo.com:443/en/jo/menu/', 'www.shelterjo.com', 'shelterjo.com/ar/'];
  const allowed = ['http://127.0.0.1:8000', 'http://localhost:4173/', 'https://staging.shelterjo.com', 'https://shelterjo.com.example.test', 'https://notshelterjo.com', '', undefined];
  const failures = [];
  for (const url of refused) {
    try {
      assertNotProduction(url, {});
      failures.push(`not refused: ${url}`);
    } catch {
      // expected
    }
    const warn = console.warn;
    console.warn = () => {};
    try {
      assertNotProduction(url, { SHELTER_ALLOW_PRODUCTION: '1' });
    } catch {
      failures.push(`refused despite SHELTER_ALLOW_PRODUCTION=1: ${url}`);
    } finally {
      console.warn = warn;
    }
    for (const value of ['true', 'yes', '0', '']) {
      try {
        assertNotProduction(url, { SHELTER_ALLOW_PRODUCTION: value });
        failures.push(`allowed with SHELTER_ALLOW_PRODUCTION=${value}: ${url}`);
      } catch {
        // only "1" opens the gate
      }
    }
  }
  for (const url of allowed) {
    try {
      assertNotProduction(url, {});
    } catch {
      failures.push(`refused a non-production address: ${url}`);
    }
  }
  if (failures.length) {
    console.error(`base-url-guard selftest: FAIL\n  ${failures.join('\n  ')}`);
    process.exit(1);
  }
  console.log(`base-url-guard selftest: PASS (${refused.length} production forms refused, ${allowed.length} others allowed)`);
}

if (process.argv[1] === fileURLToPath(import.meta.url) && process.argv.includes('--selftest')) selftest();
