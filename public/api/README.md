# Assets API — `public/api`

Base URL: `https://assets.evos7.com/api/v1`

---

## Authentication

All `/assets/data` endpoints require an API key passed as a query string parameter.

```
?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc
```

The key is the **SHA256 hash** of the configured passphrase. Requests without a valid key return `401`.

---

## Endpoints

### Health Check

```
GET /api/v1/health
```

No auth required.

**Response**
```json
{ "status": "ok", "time": "2026-03-09T10:00:00+00:00" }
```

---

## Assets CRUD

### 1. List Assets

```
GET /api/v1/assets/data?api_key=...
```

**Query Parameters**

| Parameter | Default | Description |
|---|---|---|
| `api_key` | — | Required. SHA256 API key |
| `page` | `1` | Page number |
| `size` | `100` | Rows per page (max `1000`) |
| `sort` | `id` | Column to sort by |
| `dir` | `ASC` | Sort direction: `ASC` or `DESC` |
| `q` | — | Search across `asset`, `asset_description`, `tagID`, `location`, `bar_kar`, `sn`, `po` |
| `asset` | — | Filter by exact asset number |
| `tagID` | — | Filter by exact tag ID |
| `location` | — | Filter by exact location |
| `category` | — | Filter by exact category |
| `bar_kar` | — | Filter by exact barcode |
| `sn` | — | Filter by exact serial number |
| `po` | — | Filter by exact PO number |
| `asset_class` | — | Filter by asset class |
| `uom` | — | Filter by unit of measure |

**Examples**
```powershell
curl "https://assets.evos7.com/api/v1/assets/data?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc"

curl "https://assets.evos7.com/api/v1/assets/data?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc&page=2&size=50&sort=created_at&dir=DESC"

curl "https://assets.evos7.com/api/v1/assets/data?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc&q=laptop"

curl "https://assets.evos7.com/api/v1/assets/data?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc&location=ICT&category=Electronic+Machines"
```

**Response**
```json
{
  "status": "ok",
  "total": 500,
  "page": 1,
  "size": 100,
  "pages": 5,
  "data": [
    {
      "id": "1",
      "asset": "2290005699",
      "tagID": "331B0E11C00002152EF9E9B9",
      "subnumber": "0",
      "joint_assets_number": "22900056990",
      "capitalized_on": "2024-02-29",
      "asset_class": "B2503ID1",
      "asset_class_desc": "Electronic Machines (HW&SW)for GA",
      "category": "Electronic Machines (HW&SW)for GA",
      "asset_description": "Laptop For Finance HP Pavilion 14 DV2000",
      "quantity": "1",
      "perpcs_id": "1",
      "sn": "",
      "uom": "Ea",
      "po": "2811960355",
      "location": "ICT / Rena D Agustina - FAT",
      "bar_kar": "0557",
      "created_at": "2025-06-16 14:04:56",
      "updated_at": null,
      "last_scan": null
    }
  ]
}
```

---

### 2. Get Single Asset

```
GET /api/v1/assets/data/{id}?api_key=...
```

**Example**
```powershell
curl "https://assets.evos7.com/api/v1/assets/data/1?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc"
```

**Response**
```json
{
  "status": "ok",
  "data": {
    "id": "1",
    "asset": "2290005699",
    "tagID": "331B0E11C00002152EF9E9B9",
    "subnumber": "0",
    "joint_assets_number": "22900056990",
    "capitalized_on": "2024-02-29",
    "asset_class": "B2503ID1",
    "asset_class_desc": "Electronic Machines (HW&SW)for GA",
    "category": "Electronic Machines (HW&SW)for GA",
    "asset_description": "Laptop For Finance HP Pavilion 14 DV2000",
    "quantity": "1",
    "perpcs_id": "1",
    "sn": "",
    "uom": "Ea",
    "po": "2811960355",
    "location": "ICT / Rena D Agustina - FAT",
    "bar_kar": "0557",
    "created_at": "2025-06-16 14:04:56",
    "updated_at": null,
    "last_scan": null
  }
}
```

---

### 3. Create Asset

```
POST /api/v1/assets/data?api_key=...
Content-Type: application/json
```

**Writable Fields**

| Field | Type | Description |
|---|---|---|
| `asset` | string | Asset number |
| `tagID` | string | RFID tag ID |
| `subnumber` | string | Sub number |
| `joint_assets_number` | string | Joint asset number |
| `capitalized_on` | date | Capitalization date `YYYY-MM-DD` |
| `asset_class` | string | Asset class code |
| `asset_class_desc` | string | Asset class description |
| `category` | string | Category |
| `asset_description` | string | Asset description |
| `quantity` | string | Quantity |
| `perpcs_id` | string | Per piece ID |
| `sn` | string | Serial number |
| `uom` | string | Unit of measure |
| `po` | string | Purchase order number |
| `location` | string | Location |
| `bar_kar` | string | Barcode |
| `last_scan` | datetime | Last scan timestamp `YYYY-MM-DD HH:MM:SS` |

**Example**
```powershell
curl -X POST "https://assets.evos7.com/api/v1/assets/data?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc" `
  -H "Content-Type: application/json" `
  -d '{
    "asset": "2290005699",
    "tagID": "331B0E11C00002152EF9E9B9",
    "subnumber": "0",
    "joint_assets_number": "22900056990",
    "capitalized_on": "2024-02-29",
    "asset_class": "B2503ID1",
    "asset_class_desc": "Electronic Machines (HW&SW)for GA",
    "category": "Electronic Machines (HW&SW)for GA",
    "asset_description": "Laptop For Finance HP Pavilion 14 DV2000",
    "quantity": "1",
    "perpcs_id": "1",
    "sn": "",
    "uom": "Ea",
    "po": "2811960355",
    "location": "ICT / Rena D Agustina - FAT",
    "bar_kar": "0557"
  }'
```

**Response** `201 Created`
```json
{
  "status": "ok",
  "id": 2,
  "data": { "id": "2", "asset": "2290005699", "created_at": "2026-03-09 10:00:00", ... }
}
```

---

### 4. Partial Update (PATCH)

Only send the fields you want to change.

```
PATCH /api/v1/assets/data/{id}?api_key=...
Content-Type: application/json
```

**Example**
```powershell
curl -X PATCH "https://assets.evos7.com/api/v1/assets/data/1?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc" `
  -H "Content-Type: application/json" `
  -d '{
    "location": "Finance / Jakarta",
    "last_scan": "2026-03-09 10:00:00"
  }'
```

**Response**
```json
{
  "status": "ok",
  "updated": 1,
  "data": { "id": "1", "location": "Finance / Jakarta", "last_scan": "2026-03-09 10:00:00", ... }
}
```

---

### 5. Full Replace (PUT)

Replaces all provided fields. Same body structure as POST.

```
PUT /api/v1/assets/data/{id}?api_key=...
Content-Type: application/json
```

**Example**
```powershell
curl -X PUT "https://assets.evos7.com/api/v1/assets/data/1?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc" `
  -H "Content-Type: application/json" `
  -d '{
    "asset": "2290005699",
    "tagID": "331B0E11C00002152EF9E9B9",
    "subnumber": "0",
    "joint_assets_number": "22900056990",
    "capitalized_on": "2024-02-29",
    "asset_class": "B2503ID1",
    "asset_class_desc": "Electronic Machines (HW&SW)for GA",
    "category": "Electronic Machines (HW&SW)for GA",
    "asset_description": "Laptop For Finance HP Pavilion 14 DV2000",
    "quantity": "1",
    "perpcs_id": "1",
    "sn": "SN-NEW-001",
    "uom": "Ea",
    "po": "2811960355",
    "location": "ICT / Jakarta HQ",
    "bar_kar": "0557"
  }'
```

**Response**
```json
{
  "status": "ok",
  "updated": 1,
  "data": { "id": "1", "asset": "2290005699", "location": "ICT / Jakarta HQ", ... }
}
```

---

### 6. Delete Asset

```
DELETE /api/v1/assets/data/{id}?api_key=...
```

**Example**
```powershell
curl -X DELETE "https://assets.evos7.com/api/v1/assets/data/1?api_key=fc9b9a510d85adb2db58b8e328fa8d5baf206a1078d31baa8d4ce816d30fa6fc"
```

**Response**
```json
{
  "status": "ok",
  "deleted": 1,
  "id": 1
}
```

---

## Error Responses

| HTTP | Message | Cause |
|---|---|---|
| `400` | `No valid fields provided` | POST/PUT/PATCH body has no recognized fields |
| `401` | `Unauthorized: missing api_key` | `api_key` not in query string |
| `401` | `Unauthorized: invalid api_key` | `api_key` value is wrong |
| `404` | `Asset not found` | No asset with given `id` |
| `404` | `Not Found` | Route does not exist |

---

## Route Summary

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/health` | No | Health check |
| `GET` | `/api/v1/assets/data` | Yes | List assets (paginated, filterable) |
| `GET` | `/api/v1/assets/data/{id}` | Yes | Get single asset |
| `POST` | `/api/v1/assets/data` | Yes | Create new asset |
| `PATCH` | `/api/v1/assets/data/{id}` | Yes | Partial update |
| `PUT` | `/api/v1/assets/data/{id}` | Yes | Full replace |
| `DELETE` | `/api/v1/assets/data/{id}` | Yes | Delete asset |
