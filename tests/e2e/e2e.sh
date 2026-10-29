#!/usr/bin/env bash
# End-to-end test against a running Nextcloud with this app enabled.
#
# usage: tests/e2e/e2e.sh <base_url> docker <container>
#        E2E_PW=<password> tests/e2e/e2e.sh <base_url> ssh <user@host> <nextcloud_path>
#
# Creates the user hltestalice, and a temporary admin hltestadmin that is always deleted
# again. Sections whose title starts with "hltest" are deleted before and after the run, and
# the support settings are put back to what they were. While the test runs, its sections are
# visible on the Help page of the server.
# Checks access, the Help page and its navigation entry, the admin settings page, and
# creating, changing, reordering and deleting sections and settings through both the API the
# frontend uses and the OCS API.
# In ssh mode E2E_PW is required, because the test users would otherwise get a password that
# is public in this repository. Set E2E_CLEANUP=1 to also delete hltestalice.
# Needs curl and python3.
set -uo pipefail
BASE=$1; MODE=$2; shift 2
if [ "$MODE" = docker ]; then CONTAINER=$1; else HOST=$1; NCPATH=$2; fi

if [ "$MODE" = ssh ] && [ -z "${E2E_PW:-}" ]; then
	echo "Set E2E_PW to a password of your own when testing a server over ssh." >&2
	exit 2
fi
PW=${E2E_PW:-'Hl-e2e-Pw-2026-Qz7!'}
A=hltestalice; ADM=hltestadmin
pass=0; fail=0

occ() {
	if [ "$MODE" = docker ]; then
		docker exec -e OC_PASS="$PW" -u www-data "$CONTAINER" php occ "$@"
	else
		ssh -o BatchMode=yes "$HOST" "sudo -n -u www-data env OC_PASS=$(printf '%q' "$PW") php $NCPATH/occ $(printf '%q ' "$@")"
	fi
}
check() { if [ "$2" = "$3" ]; then echo "PASS  $1"; pass=$((pass+1)); else echo "FAIL  $1 (expected '$3', got '$2')"; fail=$((fail+1)); fi; }
skip() { echo "SKIP  $1"; }

# Calls <url> as <user> ("-" for anonymous), prints "<http status> <data as json>".
# The data is the ocs data of an OCS response, or else the whole JSON body.
# The OCS-APIRequest header lets requests without a CSRF token pass, like the
# requesttoken header of the frontend does.
call() { # <user> <method> <url> [json body]
	local user=$1 method=$2 url=$3 body=${4:-}
	local auth=(); [ "$user" != - ] && auth=(-u "$user:$PW")
	curl -s ${auth[@]+"${auth[@]}"} -H 'OCS-APIRequest: true' -H 'Accept: application/json' -H 'Content-Type: application/json' \
		-X "$method" ${body:+-d "$body"} -w '\n%{http_code}' "$url" |
		python3 -c 'import json,sys
body, status = sys.stdin.read().rsplit("\n", 1)
try:
    data = json.loads(body)
    if isinstance(data, dict) and "ocs" in data:
        data = data["ocs"]["data"]
except Exception:
    data = None
print(status + " " + json.dumps(data))'
}
# The OCS API, and the API the frontend uses
api() { call "$1" "$2" "$BASE/ocs/v2.php/apps/helplinks/api/v1$3" "${4:-}"; }
web() { call "$1" "$2" "$BASE/index.php/apps/helplinks$3" "${4:-}"; }
status() { echo "${1%% *}"; }
# Evaluates a Python expression on the data of a response (as d). Lists are printed
# comma separated, booleans as yes/no, a failing expression as "-".
get() { # <response> <python expression>
	python3 -c 'import json,sys
d = json.loads(sys.argv[1].split(" ", 1)[1])
try:
    r = eval(sys.argv[2])
except Exception:
    r = "-"
if isinstance(r, bool):
    r = "yes" if r else "no"
elif isinstance(r, list):
    r = ",".join(str(x) for x in r)
print(r)' "$1" "$2"
}
# Titles of the test sections in the order the server returns them
titles() { get "$(api $ADM GET /sections)" "[s['section']['title'] for s in d['sections'] if s['section']['title'].startswith('hltest')]"; }
section_of() { api $ADM GET "/sections/$1"; }
delete_test_sections() {
	local id
	for id in $(get "$(api $ADM GET /sections)" "[s['section']['id'] for s in d['sections'] if s['section']['title'].startswith('hltest')]" | tr ',' ' '); do
		api $ADM DELETE "/sections/$id" >/dev/null
	done
}
page() { curl -s -u "$1:$PW" "$BASE$2"; }
page_status() { curl -s -o /dev/null -w '%{http_code}' -u "$1:$PW" "$BASE$2"; }
# yes when <text> is in the page at <path> for <user>
page_has() { if page "$1" "$2" | grep -qF -- "$3"; then echo yes; else echo no; fi; }
app_enabled() { if occ app:list --enabled --output=json | grep -q "\"$1\""; then echo yes; else echo no; fi; }

# --- setup ---------------------------------------------------------------
settings_before=
cleanup() {
	delete_test_sections
	[ -n "$settings_before" ] && api $ADM PUT /settings "$settings_before" >/dev/null
	occ user:delete $ADM >/dev/null 2>&1
}
trap cleanup EXIT

occ user:add --password-from-env --display-name="$A" $A >/dev/null 2>&1 || occ user:resetpassword --password-from-env $A >/dev/null
occ user:add --password-from-env --display-name="$ADM" --group=admin $ADM >/dev/null 2>&1 || occ user:resetpassword --password-from-env $ADM >/dev/null
delete_test_sections
settings_before=$(get "$(api $ADM GET /settings)" "json.dumps({k: v or '' for k, v in d.items()})")

version=$(occ status --output=json | python3 -c 'import json,sys; print(json.load(sys.stdin)["versionstring"])')
echo "== Nextcloud $version"

# --- access --------------------------------------------------------------
check "anonymous OCS requests are rejected"                 "$(status "$(api - GET /sections)")" 401
check "anonymous frontend requests are rejected"            "$(status "$(web - GET /api/sections)")" 401
check "anonymous visitors of the Help page go to the login" \
	"$(curl -s -o /dev/null -w '%{http_code}' -H 'Accept: text/html' "$BASE/index.php/apps/helplinks/")" 303
check "users who are not admin can list sections"           "$(status "$(api $A GET /sections)")" 200
check "users who are not admin can read the settings"       "$(status "$(api $A GET /settings)")" 200
check "users who are not admin cannot create sections"      "$(status "$(api $A POST /sections '{"title": "hltest by alice"}')")" 403
check "users who are not admin cannot change settings"      "$(status "$(api $A PUT /settings '{"supportEmail": "x@example.org"}')")" 403
check "users who are not admin cannot create sections in the frontend API" \
	"$(status "$(web $A POST /api/sections '{"title": "hltest by alice", "description": "", "mainLinkText": "", "mainLinkUrl": "", "subLinks": [], "sortOrder": 0}')")" 403
check "users who are not admin cannot change settings in the frontend API" \
	"$(status "$(web $A POST /api/settings '{"supportEmail": "x@example.org", "supportUrl": "", "environmentName": ""}')")" 403
check "frontend changes without CSRF token are refused" \
	"$(curl -s -o /dev/null -w '%{http_code}' -u "$ADM:$PW" -H 'Content-Type: application/json' -X POST \
		-d '{"supportEmail": "", "supportUrl": "", "environmentName": ""}' "$BASE/index.php/apps/helplinks/api/settings")" 412

# --- pages ---------------------------------------------------------------
check "the Help page loads"                                 "$(page_status $A /index.php/apps/helplinks/)" 200
check "the Help page loads its script"                      "$(page_has $A /index.php/apps/helplinks/ 'helplinks/js/helplinks-main.mjs')" yes
# The script path is absolute, from the root of the host
script=$(page $A /index.php/apps/helplinks/ | grep -o '/[^"]*helplinks/js/helplinks-main\.mjs' | head -1)
check "the script of the Help page is served" \
	"$(curl -s -o /dev/null -w '%{http_code}' "$(echo "$BASE" | grep -oE '^https?://[^/]+')$script")" 200
check "the Help page is in the navigation" \
	"$(get "$(call $A GET "$BASE/ocs/v2.php/core/navigation/apps")" "'helplinks' in [e['id'] for e in d]")" yes
check "the admin settings load"                             "$(page_status $ADM /index.php/settings/admin/additional)" 200
check "the admin settings show the Help Links Manager"      "$(page_has $ADM /index.php/settings/admin/additional 'helplinks-admin-app')" yes
check "the admin settings load their script"                "$(page_has $ADM /index.php/settings/admin/additional 'helplinks/js/helplinks-admin-settings.mjs')" yes
check "the admin settings are not shown to other users"     "$(page_has $A /index.php/settings/admin/additional 'helplinks-admin-app')" no

# --- sections in the OCS API ---------------------------------------------
r=$(api $ADM POST /sections '{"title": "hltest first", "description": "about files", "mainLinkText": "Docs", "mainLinkUrl": "https://example.org/docs", "subLinks": [{"text": "Sharing", "url": "https://example.org/share"}, {"text": "Sync", "url": "https://example.org/sync"}], "sortOrder": 1}')
check "create a section"                                    "$(status "$r")" 201
S1=$(get "$r" "d['section']['id']")
check "the section has an id"                               "$(get "$r" "isinstance(d['section']['id'], int)")" yes
check "the section has its title"                           "$(get "$r" "d['section']['title']")" 'hltest first'
check "the section has its main link"                       "$(get "$r" "d['section']['mainLinkUrl']")" https://example.org/docs
check "the sub-links are created in order"                  "$(get "$r" "[s['text'] for s in d['subLinks']]")" Sharing,Sync
check "the sub-links belong to the section"                 "$(get "$r" "all(s['sectionId'] == $S1 for s in d['subLinks'])")" yes
r=$(section_of "$S1")
check "show the section"                                    "$(status "$r")" 200
check "the shown section has its description"               "$(get "$r" "d['section']['description']")" 'about files'
check "the shown section has its sub-links"                 "$(get "$r" "[s['url'] for s in d['subLinks']]")" https://example.org/share,https://example.org/sync
check "show an unknown section"                             "$(status "$(section_of 999999999)")" 404
check "the section is listed for other users"               "$(get "$(api $A GET /sections)" "$S1 in [s['section']['id'] for s in d['sections']]")" yes

r=$(api $ADM PUT "/sections/$S1" '{"title": "hltest renamed", "description": "about sync", "mainLinkText": "Manual", "mainLinkUrl": "https://example.org/manual", "subLinks": [{"text": "Desktop", "url": "https://example.org/desktop"}]}')
check "update the section"                                  "$(status "$r")" 200
r=$(section_of "$S1")
check "the title is changed"                                "$(get "$r" "d['section']['title']")" 'hltest renamed'
check "the main link is changed"                            "$(get "$r" "d['section']['mainLinkText']")" Manual
check "the sub-links are replaced"                          "$(get "$r" "[s['text'] for s in d['subLinks']]")" Desktop
check "update an unknown section"                           "$(status "$(api $ADM PUT /sections/999999999 '{"title": "hltest nothing"}')")" 404

# --- sections in the frontend API ----------------------------------------
r=$(web $ADM POST /api/sections '{"title": "hltest second", "description": "", "mainLinkText": "", "mainLinkUrl": "", "subLinks": [{"text": "Help", "url": "https://example.org/help"}], "sortOrder": 2}')
check "create a section in the frontend API"                "$(status "$r")" 200
S2=$(get "$r" "d['section']['id']")
check "it is visible in the OCS API"                        "$(get "$(section_of "$S2")" "[s['text'] for s in d['subLinks']]")" Help
r=$(web $ADM PUT "/api/sections/$S2" '{"title": "hltest second", "description": "changed", "mainLinkText": "", "mainLinkUrl": "", "subLinks": []}')
check "update a section in the frontend API"                "$(status "$r")" 200
check "the change is saved"                                 "$(get "$(section_of "$S2")" "d['section']['description']")" changed
check "removing all sub-links"                              "$(get "$(section_of "$S2")" "len(d['subLinks'])")" 0

r=$(web $A GET /api/sections)
check "the Help page data loads for users"                  "$(status "$r")" 200
check "the Help page data has the sections"                 "$(get "$r" "[s['section']['title'] for s in d['sections'] if s['section']['title'].startswith('hltest')]")" 'hltest renamed,hltest second'
check "the Help page data has the cloud ID of the user"     "$(get "$r" "d['cloudId'].startswith('$A@')")" yes
check "the Help page data knows whether IntroVox is enabled" "$(get "$r" "d['introvoxEnabled']")" "$(app_enabled introvox)"
check "the Help page data knows whether Talk is enabled"    "$(get "$r" "d['talkEnabled']")" "$(app_enabled spreed)"

# A body with braces and commas in a variable, bash 3 would brace expand it inside "$(...)"
order="{\"order\": [{\"id\": $S1, \"sortOrder\": 9}, {\"id\": $S2, \"sortOrder\": 8}]}"
check "reorder sections"                                    "$(status "$(web $ADM POST /api/sections/reorder "$order")")" 200
check "the sections are in the new order"                   "$(titles)" 'hltest second,hltest renamed'
# The admin page saves the order of the sections as sortOrder of each section
web $ADM PUT "/api/sections/$S1" '{"title": "hltest renamed", "description": "about sync", "mainLinkText": "Manual", "mainLinkUrl": "https://example.org/manual", "subLinks": [{"text": "Desktop", "url": "https://example.org/desktop"}], "sortOrder": 1}' >/dev/null
check "the admin page saves the order of the sections"      "$(titles)" 'hltest renamed,hltest second'

# --- settings ------------------------------------------------------------
r=$(api $ADM PUT /settings '{"supportEmail": "hltest@example.org", "supportUrl": "https://example.org/support", "environmentName": "hltest env"}')
check "change the settings"                                 "$(status "$r")" 200
r=$(api $A GET /settings)
check "the support email is saved"                          "$(get "$r" "d['supportEmail']")" hltest@example.org
check "the support URL is saved"                            "$(get "$r" "d['supportUrl']")" https://example.org/support
check "the environment name is saved"                       "$(get "$r" "d['environmentName']")" 'hltest env'
check "the Help page data has the settings"                 "$(get "$(web $A GET /api/sections)" "d['supportUrl']")" https://example.org/support
r=$(web $ADM POST /api/settings '{"supportEmail": "", "supportUrl": "https://example.org/other", "environmentName": "hltest env"}')
check "change the settings in the frontend API"             "$(status "$r")" 200
r=$(api $A GET /settings)
check "an empty setting is removed"                         "$(get "$r" "d['supportEmail']")" None
check "the other settings are changed"                      "$(get "$r" "d['supportUrl']")" https://example.org/other

# --- deleting ------------------------------------------------------------
check "delete a section in the frontend API"                "$(status "$(web $ADM DELETE "/api/sections/$S2")")" 200
check "the section is gone"                                 "$(status "$(section_of "$S2")")" 404
check "delete a section"                                    "$(status "$(api $ADM DELETE "/sections/$S1")")" 200
check "the section is no longer listed"                     "$(titles)" ''
check "delete an unknown section"                           "$(status "$(api $ADM DELETE /sections/999999999)")" 404

if [ "${E2E_CLEANUP:-0}" = 1 ]; then
	occ user:delete $A >/dev/null
fi

echo "== $pass passed, $fail failed"
[ "$fail" = 0 ]
