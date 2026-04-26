# CSP Rollout

The initial CSP is report-only because Tabler and legacy auth pages still use inline styles/scripts. The rollout path is:

1. Keep `Content-Security-Policy-Report-Only` active.
2. Move stable inline assets into versioned CSS/JS files.
3. Add nonces or hashes for unavoidable inline scripts.
4. Switch to enforcing `Content-Security-Policy`.
