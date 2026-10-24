import fs from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';

export default class EvidenceReporter {
    constructor(options = {}) { this.suite = options.suite || 'publico'; this.items = []; }
    onTestEnd(test, result) {
        const root = path.resolve(`reports/browser/${this.suite}`);
        fs.mkdirSync(path.join(root, 'assets'), { recursive: true });
        const files = [];
        for (const [index, attachment] of result.attachments.entries()) {
            const extension = attachment.contentType === 'image/png' ? '.png' : attachment.name === 'trace' ? '.zip' : null;
            if (!extension || (!attachment.path && !attachment.body)) continue;
            const hash = createHash('sha256').update(`${test.id}:${result.retry}:${index}:${attachment.name}`).digest('hex').slice(0, 24);
            const name = `assets/${hash}${extension}`;
            try {
                if (attachment.path) fs.copyFileSync(attachment.path, path.join(root, name));
                else fs.writeFileSync(path.join(root, name), attachment.body);
                files.push({ name: attachment.name, path: name, type: extension === '.png' ? 'captura' : 'traza' });
            } catch { files.push({ name: attachment.name, missing: true }); }
        }
        this.items.push({ test: test.title, status: result.status, retry: result.retry, files });
    }
    onEnd() {
        const root = `reports/browser/${this.suite}`;
        fs.mkdirSync(root, { recursive: true });
        fs.writeFileSync(`${root}/evidence.json`, JSON.stringify({ suite: this.suite, cases: this.items }, null, 2));
    }
}
