import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const root = new URL('../', import.meta.url);

test('CI checks tests and a production build without deployment credentials', () => {
    const workflow = readFileSync(new URL('.github/workflows/ci.yml', root), 'utf8');
    for (const command of ['node --test tests/ci-workflow.test.mjs', 'cp .env.example .env', 'php artisan test', 'npm ci', 'npm run build']) {
        assert.ok(workflow.includes(command), `Missing CI check: ${command}`);
    }
    assert.match(workflow, /contents: read/);
    assert.match(workflow, /pull_request:/);
    assert.match(workflow, /push:/);
    assert.match(workflow, /DB_DATABASE: ':memory:'/);
    assert.doesNotMatch(workflow, /secrets\.|pull_request_target|continue-on-error|--if-present|\b(?:ssh|rsync|scp)\b/);
});

test('publishing remains a manual, main-only action', () => {
    const workflow = readFileSync(new URL('.github/workflows/deploy.yml', root), 'utf8');
    assert.match(workflow, /workflow_dispatch:/);
    assert.doesNotMatch(workflow, /^\s+(?:push|pull_request|workflow_run):/m);
    assert.ok(workflow.includes("github.ref == 'refs/heads/main'"));
});
