# Assets-Management-EVO — REST API (v1)

A modular, framework-lite REST API that lives under `public/api/`. It provides:

- CRUD for **any allow‑listed table**
- Bulk create/update/delete
- **Bulk PATCH by query** (search + filters)
- **GET / PATCH / PUT by barcode** for `assets` (`bar_kar` column)
- Table metadata
- CSV / JSONL export
- Optional Bearer auth, CORS, simple rate limiting
- Clean separation: **controllers / models / middleware / core**

> Works on Windows (Laragon), Linux, macOS. No Composer or external libs required. Uses PDO (MySQL).

---

## 1) Directory Layout

```
public/api/
├─ .htaccess
├─ index.php
├─ bootstrap/
│  ├─ autoload.php
│  ├─ config.php        # DB, CORS, TOKEN, tables allow-list, JSON columns
│  ├─ db.php
│  └─ routes.php        # Route definitions
├─ core/
│  ├─ MiddlewareInterface.php
│  ├─ Request.php
│  ├─ Response.php
│  └─ Router.php
├─ middleware/
│  ├─ AuthMiddleware.php
│  ├─ CorsMiddleware.php
│  └─ RateLimitMiddleware.php
├─ models/
│  ├─ BaseModel.php
│  └─ GenericModel.php
└─ controllers/
   ├─ BaseController.php
   ├─ GenericController.php
   ├─ BulkController.php
   ├─ UtilityController.php
   └─ AssetsController.php
```

**Routing** is handled by `index.php` + `.htaccess` (pretty URLs).

---

## 2) Configuration

Edit `public/api/bootstrap/config.php` (or set environment variables):

| Key | Description | Default |
|---|---|---|
| `DB_HOST` | MySQL host | `127.0.0.1` |
| `DB_PORT` | MySQL port | `3306` |
| `DB_NAME` | Database name | `assets_db` |
| `DB_USER` | Database user | `root` |
| `DB_PASS` | Database password | `` (empty) |
| `CORS_ORIG` | CORS allow origin | `*` |
| `API_TOKEN` | Optional Bearer token (if empty, auth disabled) | `` (empty) |
| `RATE_PER_MIN` | Requests per minute per IP | `120` |
| `ALLOWED_TABLES` | `'*'` (all tables) or an array of table names | `'*'` |
| `JSON_COLUMNS` | Map of table ⇒ JSON columns | `['asset_audits'=>['changes']]` |

> The bootstrap reads your CI4 `.env` (if present) as a fallback for `database.default.*` values.

---

## 3) Auth, CORS, Rate‑Limit

- **Auth**: If `API_TOKEN` is set, every request must send the header:  
  `Authorization: Bearer <API_TOKEN>`
- **CORS**: Controlled via `CORS_ORIG` (e.g., `*` or `https://your-site`)
- **Rate limit**: Simple per‑IP storage in temp files (default `120`/min).

---

## 4) Base URL

When served from the repo root on Laragon (Windows), the API will be accessible at:

```
http://localhost/Assets-Management-EVO/public/api
```

All endpoints below are **relative to** that base and start with `/api/v1/...`

**Health check**
```
GET /api/v1/health
```

---

## 5) Generic CRUD (All Tables)

> Applies to any table listed in `ALLOWED_TABLES` (or all tables when `'*'`).  
> The primary key is assumed to be integer `id`.

### List with search, filters, pagination, sort
```
GET /api/v1/{table}?q=<text>&page=<n>&size=<n>&sort=<col>&dir=ASC|DESC&<col>=<value>...
```
- `q` — free text across all text columns.
- Any `?column=value` becomes an **equality filter**.
- `page` default 1; `size` default 50 (max 200).
- `sort` defaults to `id`; `dir` defaults to `DESC`.

**Response**
```json
{
  "status": "ok",
  "table": "assets",
  "page": 1,
  "size": 50,
  "total": 231,
  "sort": "updated_at",
  "dir": "ASC",
  "filters": [":eq_location", ":eq_category"],
  "data": [ { "...": "..." } ]
}
```

### Get by id
```
GET /api/v1/{table}/{id}
```

### Create
```
POST /api/v1/{table}
Content-Type: application/json
Body: { ...fields... }
```
- On success: **201** with the created row.

### Update (partial/full)
```
PATCH /api/v1/{table}/{id}
PUT    /api/v1/{table}/{id}
Content-Type: application/json
Body: { ...fields... }
```

### Delete
```
DELETE /api/v1/{table}/{id}
```

---

## 6) Bulk Operations

### Bulk create
```
POST /api/v1/{table}/bulk
Body: [ { ... }, { ... } ]
```

### Bulk update by ids
```
PATCH /api/v1/{table}/bulk
Body: [ { "id": 1, ...fields }, { "id": 2, ... } ]
```

### Bulk delete by ids
```
DELETE /api/v1/{table}/bulk
Body: { "ids": [1,2,3] }
```

### **Bulk PATCH by query**  ✅
```
PATCH /api/v1/{table}/bulk-query?q=<text>&<col>=<value>&limit=<n>&dry_run=1
Body: { ...fields to update... }
```
- **Filters** via querystring become equality conditions.
- **`q`** searches all text columns (optional).
- **`limit`** caps rows to update (max 10,000).
- **`dry_run=1`**: report matched IDs but **do not update**.
- Updates are executed via `WHERE id IN (...)` to avoid moving targets.

**Dry run response**
```json
{
  "status": "ok",
  "table": "assets",
  "dry_run": true,
  "matched": 231,
  "limit": 500,
  "ids_sample": [1,2,3,...]
}
```

**Write response**
```json
{
  "status": "ok",
  "table": "assets",
  "dry_run": false,
  "matched": 231,
  "updated": 231,
  "ids_sample": [1,2,3,...]
}
```

---

## 7) Assets Special Endpoints (barcode)

> Barcode column is `bar_kar` in `assets` table.

### Get by barcode (all rows)
```
GET /api/v1/assets/barcode/{barcode}
```

### Update by barcode (partial / full) — affects **all** rows with that barcode
```
PATCH /api/v1/assets/barcode/{barcode}
PUT   /api/v1/assets/barcode/{barcode}
Body: { ...fields to set... }
```
- If you include `"bar_kar"` in the body, matching rows move to the new barcode.
- No implicit `last_scan` updates — write only the fields you provide.

**Response**
```json
{
  "status": "ok",
  "method": "PATCH",
  "barcode_in": "ABC123",
  "barcode_out": "ABC123-NEW",
  "updated": 2,
  "count": 2,
  "data": [ { "...": "..." } ]
}
```

---

## 8) Metadata & Export

### Table columns
```
GET /api/v1/{table}/meta
```
- Uses `INFORMATION_SCHEMA.COLUMNS` to return name + data type.

### CSV export (streaming)
```
GET /api/v1/{table}/export.csv?q=<text>&<col>=<value>
```

### JSONL export (streaming)
```
GET /api/v1/{table}/export.jsonl?q=<text>&<col>=<value>
```

---

## 9) Request / Response Examples

**Create an asset**
```bash
curl -X POST "http://localhost/Assets-Management-EVO/public/api/api/v1/assets" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOURTOKEN" \
  -d '{"asset":"Table","location":"WH1","bar_kar":"ABC123"}'
```

**Search + filter + sort**
```bash
curl "http://localhost/Assets-Management-EVO/public/api/api/v1/assets?q=chair&location=WH1&sort=updated_at&dir=ASC&page=1&size=50" \
  -H "Authorization: Bearer YOURTOKEN"
```

**Bulk patch by query (dry run)**
```bash
curl -X PATCH "http://localhost/Assets-Management-EVO/public/api/api/v1/assets/bulk-query?q=chair&location=WH1&limit=500&dry_run=1" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOURTOKEN" \
  -d '{"category":"Furniture","uom":"PCS"}'
```

**Barcode update (move rows to new barcode)**
```bash
curl -X PATCH "http://localhost/Assets-Management-EVO/public/api/api/v1/assets/barcode/ABC123" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOURTOKEN" \
  -d '{"bar_kar":"ABC123-NEW","location":"WH2"}'
```

---

## 10) Status Codes & Errors

Common codes:
- `200 OK` — successful GET/PATCH/PUT/DELETE
- `201 Created` — successful POST
- `400 Bad Request` — validation or malformed input
- `401 Unauthorized` — missing/invalid token when `API_TOKEN` is set
- `404 Not Found` — row or selection not found
- `405 Method Not Allowed` — wrong HTTP verb
- `409 Conflict` — (reserved; e.g., duplicate key)
- `422 Unprocessable Entity` — (reserved; detailed validation)
- `429 Too Many Requests` — rate limit exceeded
- `500 Internal Server Error` — server/database issues

**Error body**
```json
{ "status": "error", "message": "reason" }
```

---

## 11) Security Notes

- Prefer setting a strong `API_TOKEN` (random 32–64 chars).
- Lock down `ALLOWED_TABLES` to only what you need in production.
- Use HTTPS in production.
- Add DB indexes for frequently filtered columns (e.g., `bar_kar`, `location`, `sn`, etc.).
- The included rate limiter is best‑effort; for production, consider Redis or your gateway/LB.

---

## 12) Versioning

- All routes are prefixed with `/api/v1/...`.  
- Backward-incompatible changes should create `/api/v2/...` with separate router entries.

---

## 13) Extending

- Add a new controller in `controllers/` and register routes in `bootstrap/routes.php`.
- For table‑specific logic (e.g., derived fields, business rules), prefer dedicated controllers.
- `JSON_COLUMNS` helps auto‑encode arrays/objects on write for JSON columns.

---

## 14) Troubleshooting

**`Failed to open ... bootstrap/autoload.php`**  
Ensure the folders/files exist exactly as in the tree above and that `index.php` includes:
```php
require __DIR__.'/bootstrap/autoload.php';
```

**404 on `/api/v1/...`**  
Check `.htaccess` is present and Apache `AllowOverride All` is enabled (or configure Nginx rewrite).

**DB errors**  
Verify credentials in `bootstrap/config.php`. Confirm the database schema matches routes (e.g., `assets.bar_kar`).

---

## 15) Change Log (highlights)

- v1.0.0: Base CRUD, bulk by ids, metadata, export, barcode GET
- v1.1.0: Assets `PATCH/PUT` by barcode (multi‑row)
- v1.2.0: **Bulk PATCH by query** (search + filters + limit + dry‑run)

---

## License

MIT (or your project’s license).
