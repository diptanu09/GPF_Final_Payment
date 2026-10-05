import subprocess
import json
import base64
import sys

TARGET_HOST = "10.47.240.169"
CONTAINER_NAME = "gpf_final_payment_app"

def get_running_containers():
    res = subprocess.run(["docker", "ps", "-a", "--filter", f"name=^/{CONTAINER_NAME}$", "--format", "{{.ID}}|{{.Status}}"], capture_output=True, text=True)
    out = res.stdout.strip()
    if not out:
        return None, None
    parts = out.split("|")
    return parts[0], parts[1]

def main():
    cid, status = get_running_containers()
    if cid and "Up" in status:
        print(f"[OK] Container '{CONTAINER_NAME}' ({cid[:12]}) is already running: {status}")
        return 0

    print(f"[INFO] Container '{CONTAINER_NAME}' is not running (Current: {status}). Starting/re-creating...")

    if cid:
        # Try to remove stopped/stale container first
        print(f"[INFO] Removing stale container {cid[:12]}...")
        subprocess.run(["docker", "rm", "-f", cid], capture_output=True)

    payload = {
        "Image": "gpf_final_payment_app:latest",
        "Cmd": ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"],
        "Entrypoint": ["/usr/local/bin/entrypoint.sh"],
        "ExposedPorts": {
            "80/tcp": {},
            "443/tcp": {}
        },
        "Env": [
            "APP_NAME=Laravel",
            "APP_ENV=production",
            "APP_KEY=base64:UuW3rw5jdbttDuINw+5fmw+YHuWd9mnfkqFfrJJyV5U=",
            "APP_DEBUG=false",
            "APP_URL=https://gpffp.local",
            "APP_LOCALE=en",
            "APP_FALLBACK_LOCALE=en",
            "APP_FAKER_LOCALE=en_US",
            "APP_MAINTENANCE_DRIVER=file",
            "PHP_CLI_SERVER_WORKERS=4",
            "BCRYPT_ROUNDS=12",
            "LOG_CHANNEL=stack",
            "LOG_STACK=single",
            "LOG_DEPRECATIONS_CHANNEL=null",
            "LOG_LEVEL=debug",
            "DB_CONNECTION=pgsql",
            "DB_HOST=10.47.240.169",
            "DB_PORT=5432",
            "DB_DATABASE=gpf_final_payment",
            "DB_USERNAME=postgres",
            "DB_PASSWORD=root@123",
            "DB_SCHEMA=gpffp",
            "ORACLE_DB_HOST=192.168.100.247",
            "ORACLE_DB_PORT=1521",
            "ORACLE_DB_SID=db11g",
            "ORACLE_DB_USERNAME=gpffp",
            "ORACLE_DB_PASSWORD=gpffp",
            "ORACLE_DB_SCHEMA=gpffp",
            "PORT=8082",
            "SSL_PORT=8443",
            "SESSION_DRIVER=database",
            "SESSION_LIFETIME=120",
            "SESSION_ENCRYPT=false",
            "SESSION_PATH=/",
            "SESSION_DOMAIN=null",
            "BROADCAST_CONNECTION=log",
            "FILESYSTEM_DISK=local",
            "QUEUE_CONNECTION=database",
            "CACHE_STORE=database",
            "RUN_MIGRATIONS=true",
            "TNS_ADMIN=/usr/lib/oracle/current/network/admin",
            "ORACLE_PROBE_TIMEOUT=3.0"
        ],
        "HostConfig": {
            "PortBindings": {
                "80/tcp": [{"HostIp": "0.0.0.0", "HostPort": "8082"}],
                "443/tcp": [{"HostIp": "0.0.0.0", "HostPort": "8443"}]
            },
            "Binds": [
                "gpf_final_payment_app_storage:/var/www/html/storage",
                "gpf_final_payment_app_logs:/var/www/html/storage/logs"
            ],
            "RestartPolicy": {
                "Name": "unless-stopped"
            },
            "ExtraHosts": [
                "host.docker.internal:host-gateway"
            ]
        },
        "Healthcheck": {
            "Test": ["CMD-SHELL", "curl -k -f https://localhost/login || curl -f http://localhost/login || exit 1"],
            "Interval": 30000000000,
            "Timeout": 10000000000,
            "StartPeriod": 20000000000,
            "Retries": 3
        }
    }

    json_str = json.dumps(payload)
    sh_script = f"""
cat << 'EOF' > /tmp/gpf_create.json
{json_str}
EOF
curl -s -X POST -H "Content-Type: application/json" --data-binary @/tmp/gpf_create.json "http://{TARGET_HOST}:2375/v1.56/containers/create?name={CONTAINER_NAME}"
curl -s -X POST -H "Content-Type: application/json" -d '{{"Container":"{CONTAINER_NAME}"}}' "http://{TARGET_HOST}:2375/v1.56/networks/proxy-network/connect"
curl -s -X POST "http://{TARGET_HOST}:2375/v1.56/containers/{CONTAINER_NAME}/start"
"""
    b64_script = base64.b64encode(sh_script.encode()).decode()
    cmd = f"echo {b64_script} | base64 -d | sh"

    res = subprocess.run(["docker", "exec", "npm-gateway", "sh", "-c", cmd], capture_output=True, text=True)
    if res.returncode != 0:
        print("[ERROR] Failed to start container via relay:", res.stderr)
        return 1

    print("[SUCCESS] Container initialized and started successfully.")
    return 0

if __name__ == "__main__":
    sys.exit(main())
