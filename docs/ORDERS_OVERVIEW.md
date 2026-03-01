# Orders Module — Complete Overview

> **TMS (Transportation Management System) — Orders Module**  
> Updated: March 2026

---

## Table of Contents

1. [Non-Technical Overview](#1-non-technical-overview)
2. [Order Types & Lifecycle](#2-order-types--lifecycle)
3. [Database Schema](#3-database-schema)
4. [Backend Architecture](#4-backend-architecture)
5. [Frontend Architecture](#5-frontend-architecture)
6. [Field Reference](#6-field-reference)
7. [Validation Rules](#7-validation-rules)
8. [Permissions & Access Control](#8-permissions--access-control)
9. [QuickBooks Integration](#9-quickbooks-integration)
10. [Key Relationships Map](#10-key-relationships-map)

---

## 1. Non-Technical Overview

An **Order** in this TMS is the foundational document that represents a transport job — from the moment a customer requests a shipment to its final delivery and invoicing.

### What is an Order?

Think of an order as a transport contract between the **company** (the logistics broker/carrier) and a **customer**. It records:

- **Who** needs freight moved (the customer)
- **What** is being moved (commodities/cargo)
- **Where** it starts and ends (shipper to consignee locations)
- **When** it needs to be picked up and delivered
- **How much** it will cost (quoting: revenue from customer vs. cost from carrier)
- **Which truck/driver** will handle it (manifest assignment)

### Order Workflow

```
Customer Request
      │
      ▼
  [DRAFT] ──→ Fill in stops, commodities, billing details
      │
      ▼
 [PENDING] ──→ Submit & Process: assign manifests + set financial quote
      │
      ▼
  [ACTIVE] ──→ Driver picks up cargo
      │
      ▼
[DISPATCHED] ──→ En-route to delivery
      │
      ▼
[COMPLETED] ──→ Delivery confirmed
      │
      ▼
[INVOICED] ──→ Invoice sent to customer (optionally via QuickBooks)
```

Cancelled orders can exit the workflow at any stage.

### Key Concepts for Dispatchers

| Concept | Meaning |
|---|---|
| **Leg / Stop** | A single pickup→delivery pair within an order. An order can have multiple legs. |
| **Shipper** | The location where cargo is collected (Pickup). |
| **Consignee** | The location where cargo is delivered (Delivery). |
| **Manifest** | The operational dispatch document assigning a driver + carrier + truck to one or more legs. |
| **Container Number** | Unique identifier for the shipping container transported in a leg. **Required per leg.** |
| **Accessorials** | Additional services (e.g., liftgate, inside delivery, fuel surcharge). |
| **Quote** | The financial breakdown: how much the customer pays (revenue) vs. how much the carrier costs (expense). |

---

## 2. Order Types & Lifecycle

### Order Types

| Type | Description | Legs |
|---|---|---|
| **Point-to-Point** | One pickup → one delivery. The simplest type. | 1 |
| **Single Shipper** | One pickup source delivering to multiple destinations. | 1 shipper, N consignees |
| **Single Consignee** | Multiple pickup sources delivering to one destination. | N shippers, 1 consignee |
| **Sequence** | A chain of stops where each leg's consignee becomes the next leg's shipper. | N |

### Order Status Flow

```
draft → pending → active → dispatched → completed → invoiced
                                                ↘ cancelled (any stage)
```

| Status | Trigger |
|---|---|
| `draft` | Order created or "Save as Draft" clicked |
| `pending` | "Submit & Process" clicked |
| `active` | Manifest assigned and dispatched begins |
| `dispatched` | Driver confirmed pickup |
| `completed` | Final delivery confirmed |
| `invoiced` | Invoice generated (manual or QuickBooks) |
| `cancelled` | Manually cancelled |

---

## 3. Database Schema

### `orders` Table

| Column | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Auto-increment primary key |
| `order_number` | varchar | Auto-generated: `ORD-000001` |
| `status` | varchar | `draft`, `pending`, `active`, `dispatched`, `completed`, `invoiced`, `cancelled` |
| `order_type` | varchar | `point_to_point`, `single_shipper`, `single_consignee`, `sequence` |
| `customer_id` | FK → customers | The customer who placed the order |
| `company_id` | FK → companies | The company managing the order |
| `container_number` | varchar, nullable | Overall container number for the order |
| `ref_number` | varchar, nullable | Internal or customer-provided reference number |
| `customer_po_number` | varchar, nullable | Customer purchase order number |
| `customs_broker` | varchar, nullable | Name of the customs broker (legacy field) |
| `port_of_entry` | varchar, nullable | Port used for customs clearance (legacy field) |
| `declared_value` | decimal, nullable | Declared customs value (legacy field) |
| `manifest_id` | FK → manifests, nullable | Legacy top-level manifest link |
| `quickbooks_invoice_id` | varchar, nullable | ID from QuickBooks if invoice was synced |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Last update time |

### `order_stops` Table

Each order has one or more stops. Each stop represents a full leg (shipper + consignee pairing).

| Column | Type | Description |
|---|---|---|
| `id` | bigint (PK) | Auto-increment primary key |
| `order_id` | FK → orders | Parent order |
| `stop_type` | varchar | `mixed`, `pickup`, `delivery` |
| `sequence_number` | integer | Order of this stop within the order (1-based) |
| `service_type` | varchar | `truckload`, `ltl`, `cube` |
| `measurement_type` | varchar | `in_lbs` (imperial) or `cm_kg` (metric) |
| `manifest_id` | FK → manifests, nullable | Which manifest handles this leg |
| `company_name` | varchar | Shipper company name |
| `address_1` | varchar | Shipper address line 1 |
| `address_2` | varchar, nullable | Shipper address line 2 |
| `city` | varchar | Shipper city |
| `state` | varchar | Shipper state/province |
| `postal_code` | varchar | Shipper zip/postal code |
| `country` | varchar | Shipper country (US/CA/MX) |
| `contact_name` | varchar, nullable | Shipper contact person name |
| `contact_phone` | varchar, nullable | Shipper contact phone |
| `contact_email` | varchar, nullable | Shipper contact email |
| `opening_time` | time | Shipper facility opening time (24hr) |
| `closing_time` | time | Shipper facility closing time (24hr) |
| `start_time` | datetime, nullable | Pickup ready date + time |
| `end_time` | datetime, nullable | Delivery requested date + time |
| `is_appointment` | boolean | Whether this stop requires a scheduled appointment |
| `notes` | text, nullable | Shipper-specific notes |
| `consignee_data` | JSON | Full consignee info: address, contact, hours, dates, notes |
| `billing_data` | JSON | Per-leg billing: container_number, customs_broker, port_of_entry, declared_value, currency, ref_number, customer_po_number |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

#### `billing_data` JSON Structure (per stop)

```json
{
  "customs_broker": "string",
  "port_of_entry": "string",
  "container_number": "string (REQUIRED)",
  "declared_value": 0.00,
  "currency": "USD|CAD|EUR",
  "ref_number": "string",
  "customer_po_number": "string"
}
```

#### `consignee_data` JSON Structure (per stop)

```json
{
  "company_name": "string",
  "address_1": "string",
  "address_2": "string",
  "city": "string",
  "state": "string",
  "zip": "string",
  "country": "US|CA|MX",
  "contact_name": "string",
  "phone": "string",
  "email": "string",
  "opening_time": "HH:MM",
  "closing_time": "HH:MM",
  "ready_date": "YYYY-MM-DD",
  "ready_time": "HH:MM",
  "appointment": false,
  "notes": "string"
}
```

### `order_stop_commodities` Table

| Column | Type | Description |
|---|---|---|
| `id` | bigint (PK) | — |
| `order_stop_id` | FK → order_stops | Parent stop |
| `description` | varchar | Commodity description |
| `type` | varchar | `skid`, `pallet`, `container`, `crate`, `drum`, `box`, `bag`, `bundle`, `roll`, `loose` |
| `quantity` | integer | Number of units |
| `pieces` | integer | Number of pieces |
| `weight` | decimal | Weight (lbs or kg depending on measurement_type) |
| `length` | decimal, nullable | Length (in or cm) |
| `width` | decimal, nullable | Width |
| `height` | decimal, nullable | Height |
| `linear_feet` | decimal, nullable | Linear feet (LTL shipping) |
| `cube` | decimal, nullable | Cubic measurement |
| `freight_class` | varchar, nullable | Freight class (50–500) |
| `measurement_type` | varchar | `imperial` or `cm_kg` |

### `order_quotes` Table

| Column | Type | Description |
|---|---|---|
| `id` | bigint (PK) | — |
| `order_id` | FK → orders | Parent order (1:1) |
| `service_id` | FK → services, nullable | Service type |
| `delivery_start_date` | date, nullable | Estimated delivery window start |
| `delivery_end_date` | date, nullable | Estimated delivery window end |

### `cost_estimates` Table (via Order Quote)

| Column | Type | Description |
|---|---|---|
| `id` | bigint (PK) | — |
| `order_quote_id` | FK → order_quotes | Parent quote |
| `category` | varchar | `customer` (revenue) or `carrier` (cost) |
| `type` | varchar | `Freight`, `Fuel`, `Accessorial`, `Other` |
| `description` | varchar | Line item description |
| `cost` | decimal | Dollar amount |

### `order_stop_accessorials` (Pivot Table)

Links stops to their applicable accessorial services.

| Column | Type | Description |
|---|---|---|
| `order_stop_id` | FK → order_stops | — |
| `accessorial_id` | FK → accessorials | — |

---

## 4. Backend Architecture

### Routes (`routes/v2.php`)

All routes are prefixed with `/v2/{company}/` and use company-scoped URL slugs.

| Method | Route | Controller Action | Permission |
|---|---|---|---|
| `GET` | `/v2/{company}/orders` | `OrderController@index` | `orders.view` |
| `POST` | `/v2/{company}/orders` | `OrderController@store` | `orders.create` |
| `GET` | `/v2/{company}/orders/{order}/edit` | `OrderController@edit` | `orders.view` |
| `PATCH` | `/v2/{company}/orders/{order}` | `OrderController@update` | `orders.update` |
| `DELETE` | `/v2/{company}/orders/{order}` | `OrderController@destroy` | `orders.delete` |
| `POST` | `/v2/{company}/orders/bulk-delete` | `OrderController@bulkDestroy` | `orders.delete` |
| `GET` | `/v2/{company}/orders/search-customers` | `OrderController@searchCustomers` | `orders.view` |

### Controller: `App\Http\Controllers\V2\OrderController`

**Key methods:**

| Method | Description |
|---|---|
| `index()` | Lists orders with search (order number, ref, PO, customer name) and status filter. Paginated at 15/page. |
| `store()` | Creates a new DRAFT order. Validates `customer_id` and `order_type`. Auto-generates `ORD-XXXXXX` number. Redirects to `edit`. |
| `edit()` | Loads the order with all relationships (stops, commodities, accessorials, quote, manifest). Serializes data to Alpine.js JSON. |
| `update()` | Updates order fields, processes stops (create/update/delete), processes quote + cost estimates. Logs all steps to `orders` log channel. |
| `destroy()` | Soft-deletes a single order. |
| `bulkDestroy()` | Deletes multiple orders by ID array. Company-scoped for safety. |
| `searchCustomers()` | AJAX endpoint for the order creation modal customer search. Returns JSON. |
| `processStops()` | Internal: handles stop CRUD - compares incoming stop IDs vs existing, creates/updates/deletes as needed. |
| `processCommodities()` | Internal: deletes all existing commodities for a stop and recreates them from submitted data. |
| `processQuote()` | Internal: upserts `OrderQuote` and recreates all `CostEstimate` rows. |

### Service: `App\Services\OrderService`

A legacy service class used in earlier versions for type-specific order updates (`updatePointToPoint`, `updateSingleShipper`, etc.). The newer controller approach handles all types generically via the `processStops` method.

### Models

| Model | File | Key Features |
|---|---|---|
| `Order` | `app/Models/Order.php` | `$fillable` includes order fields. Belongs to `Company` and `Customer`. Has many `OrderStop`. Has one `OrderQuote`. Belongs to `Manifest`. |
| `OrderStop` | `app/Models/OrderStop.php` | `$guarded = []`. Casts: `start_time`, `end_time` as datetime; `consignee_data`, `billing_data` as JSON. Has many `OrderStopCommodity`. BelongsToMany `Accessorial`. Belongs to `Manifest`. |
| `OrderStopCommodity` | `app/Models/OrderStopCommodity.php` | Physical cargo line items. Belongs to `OrderStop`. |
| `OrderQuote` | `app/Models/OrderQuote.php` | Financial quote. Has many `CostEstimate`. Belongs to `Order`. |

### Logging

The update flow logs to a dedicated `orders` channel defined in `config/logging.php`. Each step (validation, stop processing, commodity, accessorial, quote) is logged with structured context data.

---

## 5. Frontend Architecture

### View Files

| File | Purpose |
|---|---|
| `resources/views/v2/company/orders/index.blade.php` | Order listing page with search, filter, bulk delete, and create modal |
| `resources/views/v2/company/orders/form.blade.php` | Full order edit/creation form (932 lines) — the main UI |
| `resources/views/v2/company/orders/partials/location-fields.blade.php` | Reusable Blade partial for shipper/consignee location fields |

### Alpine.js Component: `orderForm()`

The form page is driven by a single large Alpine.js component defined in a `@push('scripts')` block at the bottom of `form.blade.php`.

**State:**

| Property | Type | Description |
|---|---|---|
| `stops` | Array | Array of stop objects (legs). Initialized from PHP via `@json($stopsData)`. |
| `quote` | Object | Quote data (service, dates, customer rows, carrier rows). |
| `manifests` | Array | All company manifests for assignment dropdowns. |
| `manifestsMap` | Object | `{ id: code }` lookup for displaying manifest codes. |
| `accessorialsList` | Object | `{ id: name }` lookup for accessorial badge display. |
| `saving` | Boolean | True while "Save as Draft" is submitting. |
| `submitting` | Boolean | True while "Process & Finalize" is submitting. |
| `showProcessModal` | Boolean | Controls the modal that shows manifest assignment + quoting. |
| `massManifestId` | String/Int | Selected manifest ID for the "apply to all legs" feature. |
| `creatingManifest` | Boolean | True while creating a new manifest via AJAX. |

**Key Methods:**

| Method | Description |
|---|---|
| `init()` | Auto-adds first stop if none exist. Ensures all stops have defaults. |
| `addStop()` | Adds a new leg. Autofills shipper from previous consignee (sequence-style). |
| `removeStop(idx)` | Removes a leg (minimum 1 required). |
| `addCommodity(stopIdx)` | Adds blank commodity to a stop. |
| `removeCommodity(stopIdx, cIdx)` | Removes a commodity (minimum 1 required). |
| `getAccessorialName(id)` | Looks up accessorial name by ID. |
| `applyMassManifest()` | Assigns the selected manifest to ALL stops. |
| `calculateTotal(rows)` | Sums cost values for a quote rows array. |
| `calculateProfit()` | Revenue minus carrier cost. |
| `calculateMargin()` | Profit as a percentage of revenue. |
| `saveDraft()` | Sets `save_as_draft=1` and submits the form. |
| `openConfirmModal()` | Opens the processing modal. |
| `submitForm()` | Optionally syncs carrier costs to the manifest's cost estimates via AJAX, then submits the form. |
| `createPendingManifest()` | Creates a new manifest via POST AJAX and auto-assigns it. |

### Stop Data Structure (Alpine.js Object)

```javascript
{
  uid: "unique string",        // Client-side identifier
  expanded: false,              // Whether the stop card is expanded
  manifest_id: "",              // Assigned manifest ID
  service_type: "truckload",   // "truckload" | "ltl" | "cube"
  measurements: "in_lbs",      // "in_lbs" | "cm_kg"
  shipper: {
    company_name: "",
    address_1: "", address_2: "",
    city: "", state: "", zip: "", country: "US",
    contact_name: "", phone: "", email: "",
    opening_time: "08:00",      // 24hr format
    closing_time: "17:00",      // 24hr format
    ready_date: "YYYY-MM-DD",
    ready_time: "HH:MM",        // 24hr format
    appointment: false,
    notes: ""
  },
  consignee: { /* same structure as shipper */ },
  billing: {
    customs_broker: "",
    port_of_entry: "",
    container_number: "",       // REQUIRED per leg
    declared_value: 0,
    currency: "USD",
    ref_number: "",
    customer_po_number: ""
  },
  commodities: [ /* array of commodity objects */ ],
  accessorials: [ /* array of accessorial IDs */ ]
}
```

### Form Submission Flow

```
User clicks "Submit & Process"
         │
         ▼
    openConfirmModal() → shows processing modal
         │
         ▼
    User assigns manifests per leg (or mass-apply)
    User fills in financial quote rows
         │
         ▼
    submitForm()
      ├─ (optional) POST carrier costs to manifest via AJAX
      └─ Submits hidden form #orderForm (PATCH)
              │
              ▼
         OrderController@update()
           ├─ Validates basic fields
           ├─ Updates order record
           ├─ processStops() — CRUD stops
           │      ├─ Saves shipper fields to DB columns
           │      ├─ Saves consignee as JSON (consignee_data)
           │      ├─ Saves billing as JSON (billing_data)
           │      ├─ processCommodities() — recreate all commodities
           │      └─ Sync accessorials pivot
           └─ processQuote() — upsert quote + recreate cost lines
```

### Data Serialization

All stop data travels from browser → server as a **JSON string** in a hidden form field:
- `stops` → `<input type="hidden" name="stops" x-bind:value="JSON.stringify(stops)">`
- `quote_data` → `<input type="hidden" name="quote_data" x-bind:value="JSON.stringify(quote)">`

The controller parses these with `json_decode()`.

---

## 6. Field Reference

### Order-Level Fields

| Field Name (HTML) | DB Column | Required | Description |
|---|---|---|---|
| `ref_number` | `orders.ref_number` | No | Internal/customer reference |
| `customer_po_number` | `orders.customer_po_number` | No | Customer purchase order |
| `container_number` | `orders.container_number` | No | Order-level container number |

### Per-Leg Fields (via `billing_data` JSON)

| Alpine.js Path | JSON Key | Required | Description |
|---|---|---|---|
| `stop.billing.container_number` | `container_number` | **YES** | Container ID for this leg |
| `stop.billing.customs_broker` | `customs_broker` | No | Broker name |
| `stop.billing.port_of_entry` | `port_of_entry` | No | Port name |
| `stop.billing.declared_value` | `declared_value` | No | Value for customs |
| `stop.billing.currency` | `currency` | No | `USD`, `CAD`, `EUR` |
| `stop.billing.ref_number` | `ref_number` | No | Leg-level reference |
| `stop.billing.customer_po_number` | `customer_po_number` | No | Leg-level PO |

### Time Fields (All 24-Hour Format)

| Location | Alpine.js Path | DB Column |
|---|---|---|
| Shipper opening time | `stop.shipper.opening_time` | `order_stops.opening_time` |
| Shipper closing time | `stop.shipper.closing_time` | `order_stops.closing_time` |
| Shipper ready time | `stop.shipper.ready_time` | Extracted from `order_stops.start_time` |
| Consignee opening time | `stop.consignee.opening_time` | `consignee_data.opening_time` (JSON) |
| Consignee closing time | `stop.consignee.closing_time` | `consignee_data.closing_time` (JSON) |
| Consignee delivery time | `stop.consignee.ready_time` | Extracted from `order_stops.end_time` |

---

## 7. Validation Rules

### Controller-Level (PHP)

**On Store (create draft):**
```php
'customer_id' => 'required|exists:customers,id',
'order_type'  => 'required|in:point_to_point,single_shipper,single_consignee,sequence',
```

**On Update:**
```php
'order_type'          => 'required|string',
'ref_number'          => 'nullable|string|max:255',
'customer_po_number'  => 'nullable|string|max:255',
'container_number'    => 'nullable|string|max:255',
```

### Client-Level (Alpine.js)

Before form submission, the `submitForm()` method validates:
- Each stop must have `billing.container_number` filled in (non-empty)
- If validation fails, the stop is expanded and an error is highlighted

---

## 8. Permissions & Access Control

Orders use the `permission` middleware for role-based access:

| Permission Key | Actions Protected |
|---|---|
| `orders.view` | Index listing + Edit form (read) |
| `orders.create` | Create order (store) |
| `orders.update` | Save draft, submit/process, QuickBooks sync |
| `orders.delete` | Delete single + bulk delete |

The `hasPermission()` check on User model also controls visibility of UI elements (e.g., the Create Order button and Delete Selected buttons).

---

## 9. QuickBooks Integration

Orders have a `syncToQuickBooks` action that:
1. Checks if the QuickBooks plugin is active and configured for the company
2. Creates an invoice in QuickBooks using `QuickBooksService::createInvoice($order)`
3. Saves the returned invoice ID to `orders.quickbooks_invoice_id`
4. Once synced, replaces the sync button with an "Invoiced (#ID)" badge

> **Note:** The "Sync to QuickBooks" button is NOT displayed in the Order form. QuickBooks sync should be done from the QuickBooks plugin dashboard or directly via the plugin workflow.

---

## 10. Key Relationships Map

```
Company
  └── Orders (1:N, scoped by company_id)
        ├── Customer (N:1)
        ├── Manifest (N:1, top-level legacy)
        ├── OrderQuote (1:1)
        │       └── CostEstimates (1:N)
        │             ├── category: 'customer' (Revenue lines)
        │             └── category: 'carrier'  (Cost lines)
        └── OrderStops (1:N, ordered by sequence_number)
              ├── Manifest (N:1, per-leg assignment)
              ├── OrderStopCommodities (1:N)
              └── Accessorials (N:M via order_stop_accessorials pivot)
```

---

## Appendix: File Locations

| Type | Path |
|---|---|
| Controller | `app/Http/Controllers/V2/OrderController.php` |
| Service (legacy) | `app/Services/OrderService.php` |
| Order Model | `app/Models/Order.php` |
| Stop Model | `app/Models/OrderStop.php` |
| Commodity Model | `app/Models/OrderStopCommodity.php` |
| Quote Model | `app/Models/OrderQuote.php` |
| Routes | `routes/v2.php` (lines 63–79) |
| Index View | `resources/views/v2/company/orders/index.blade.php` |
| Form View | `resources/views/v2/company/orders/form.blade.php` |
| Location Partial | `resources/views/v2/company/orders/partials/location-fields.blade.php` |
