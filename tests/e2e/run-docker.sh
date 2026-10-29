#!/usr/bin/env bash
# Starts the official Nextcloud Docker image, installs the app from this checkout
# and runs the end-to-end test against it. The container is removed afterwards.
#
# usage: tests/e2e/run-docker.sh <image tag, e.g. 35, latest or master> [port]
#
# "master" is the daily build of the Nextcloud development branch, installed in the
# Nextcloud 35 image. It tests a version before its own Docker image is released.
# The frontend must be built first: npm ci && npm run build.
# Set FORCE_ENABLE=1 to enable the app on a Nextcloud version newer than max-version in info.xml.
set -euo pipefail
TAG=${1:?usage: $0 <image tag, e.g. 35, latest or master> [port]}
PORT=${2:-8080}
APP_DIR=$(cd "$(dirname "$0")/../.." && pwd)
CONTAINER=helplinks-e2e-$TAG
IMAGE=nextcloud:$TAG-apache
if [ "$TAG" = latest ]; then
	IMAGE=nextcloud:apache
fi

if [ ! -f "$APP_DIR/js/helplinks-main.mjs" ]; then
	echo "The frontend is not built, run: npm ci && npm run build" >&2
	exit 1
fi

if [ "$TAG" = master ]; then
	# Keeps the config files of the image, they set up the data directory and custom_apps.
	# The build argument downloads a new daily build once a day.
	IMAGE=helplinks-e2e:master
	docker build -q -t "$IMAGE" --build-arg DAY="$(date +%F)" - >/dev/null <<-'EOF'
		FROM nextcloud:35-apache
		ARG DAY
		RUN set -e; \
			mv /usr/src/nextcloud /usr/src/nextcloud-image; \
			curl -fsSL https://download.nextcloud.com/server/daily/latest-master.tar.bz2 | tar -xj -C /usr/src; \
			cp /usr/src/nextcloud-image/config/*.config.php /usr/src/nextcloud/config/; \
			mkdir -p /usr/src/nextcloud/data /usr/src/nextcloud/custom_apps; \
			chmod +x /usr/src/nextcloud/occ; \
			chown -R www-data:root /usr/src/nextcloud; \
			rm -rf /usr/src/nextcloud-image
	EOF
fi

docker rm -f -v "$CONTAINER" >/dev/null 2>&1 || true
trap 'docker rm -f -v "$CONTAINER" >/dev/null 2>&1 || true' EXIT

docker run -d --name "$CONTAINER" -p "127.0.0.1:$PORT:80" \
	-e SQLITE_DATABASE=nextcloud \
	-e NEXTCLOUD_ADMIN_USER=admin \
	-e NEXTCLOUD_ADMIN_PASSWORD="$(head -c 24 /dev/urandom | base64)" \
	-e NEXTCLOUD_TRUSTED_DOMAINS=localhost \
	"$IMAGE" >/dev/null

echo "Waiting for Nextcloud ($TAG) to be installed..."
for _ in $(seq 1 120); do
	if curl -s "http://localhost:$PORT/status.php" | grep -q '"installed":true'; then
		break
	fi
	sleep 2
done
curl -s "http://localhost:$PORT/status.php" | grep -q '"installed":true' || { docker logs "$CONTAINER" | tail -20; exit 1; }

# COPYFILE_DISABLE keeps macOS tar from adding ._ files, which Nextcloud would load as classes
docker exec "$CONTAINER" mkdir -p /var/www/html/custom_apps/helplinks
COPYFILE_DISABLE=1 tar -C "$APP_DIR" -cf - appinfo css img js l10n lib templates |
	docker exec -i "$CONTAINER" tar -C /var/www/html/custom_apps/helplinks -xf - 2>/dev/null
docker exec "$CONTAINER" chown -R www-data:www-data /var/www/html/custom_apps/helplinks
docker exec -u www-data "$CONTAINER" php occ app:enable ${FORCE_ENABLE:+--force} helplinks

# The web server can answer 404 for a short while until it knows the routes of the new app
for _ in $(seq 1 30); do
	code=$(curl -s -o /dev/null -w '%{http_code}' -H 'OCS-APIRequest: true' "http://localhost:$PORT/ocs/v2.php/apps/helplinks/api/v1/sections")
	[ "$code" = 401 ] && break
	sleep 1
done

status=0
"$APP_DIR/tests/e2e/e2e.sh" "http://localhost:$PORT" docker "$CONTAINER" || status=$?

echo "== Nextcloud log (warnings and errors)"
log=$(docker exec "$CONTAINER" sh -c 'cat /var/www/html/data/nextcloud.log 2>/dev/null' || true)
echo "$log"

# Any error in the log, such as an uncaught exception, fails the run.
unexpected=$(echo "$log" | python3 -c 'import json,sys
for line in sys.stdin:
    try:
        entry = json.loads(line)
    except ValueError:
        continue
    if entry.get("level", 0) >= 3:
        print(entry["message"][:300])')
if [ -n "$unexpected" ]; then
	echo "== Unexpected errors in the Nextcloud log"
	echo "$unexpected"
	status=1
fi
exit $status
