# API smoke tests

Run against a running instance (for example with Docker):

```bash
docker compose up -d --build
bash tests/api_smoke.sh
```

Optional custom base URL:

```bash
bash tests/api_smoke.sh http://127.0.0.1:8091
```

If Docker is not available, you can run a local PHP server:

```bash
php -S 127.0.0.1:8091 -t .
bash tests/api_smoke.sh http://127.0.0.1:8091
```
