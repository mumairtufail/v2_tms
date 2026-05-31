# TMS Platform — Feature Documentation

**Transportation Management System (TMS)**
Last Updated: May 2026 | Non-Technical Reference Guide

---

## Table of Contents

1. [Platform Overview](#1-platform-overview)
2. [Super Admin Panel](#2-super-admin-panel)
3. [Company Admin Panel](#3-company-admin-panel)
4. [Orders — Types & Details](#4-orders--types--details)
5. [Manifests — Full Breakdown](#5-manifests--full-breakdown)
6. [Summary Counts](#6-summary-counts)

---

## 1. Platform Overview

The TMS is a multi-company transportation management platform. It is divided into two main access levels:

| Level | Who Uses It | Purpose |
|---|---|---|
| **Super Admin** | Platform owner / IT team | Manage all companies, users, and system-wide settings |
| **Company Admin** | Each logistics company | Manage their own operations — orders, manifests, staff, etc. |

Each company operates in its own isolated space. Companies cannot see each other's data.

---

## 2. Super Admin Panel

The Super Admin panel is accessible only to platform administrators. It provides complete oversight and control over the entire platform.

### 2.1 Dashboard

- Overview of all activity across the platform
- System-wide statistics

**Total features in Dashboard: 1**

---

### 2.2 Companies Management

Manage all companies registered on the platform.

| Action | Description |
|---|---|
| View all companies | See a list of every company on the platform |
| Add a company | Create a new company with name, contact, and branding |
| Edit a company | Update company information |
| Delete a company | Remove a company from the platform |
| Generate shortcode | Assign a unique 3-letter code to a company (used in order numbering) |

**Total actions: 5**

---

### 2.3 All Users Management

View and manage every user across all companies on the platform.

| Action | Description |
|---|---|
| View all users | See every user from every company in one list |
| Add a user | Create a new user (assign to a specific company) |
| Edit a user | Update user details |
| Delete a user | Remove a user from the platform |
| View user profile | See detailed information about a specific user |

**Total actions: 5**

---

### 2.4 Activity Logs

- View a global log of all user activity across all companies
- Track who did what, when, and from which IP address
- Useful for auditing and security reviews

**Total features: 1**

---

### 2.5 System Settings (Branding)

Control the global look and feel of the platform.

| Setting | Description |
|---|---|
| App Name | The name displayed in the browser tab and throughout the system |
| Light Logo | Logo shown on light-colored backgrounds / navigation bars |
| Dark Logo | Logo shown on dark-colored backgrounds |
| Icon Logo | Small icon version of the logo (favicon / mobile) |

**Total settings: 4**

---

### Super Admin — Total Feature Count

| Section | Items |
|---|---|
| Dashboard | 1 |
| Companies Management | 5 actions |
| All Users Management | 5 actions |
| Activity Logs | 1 |
| System Settings | 4 settings |
| **TOTAL** | **16** |

---

## 3. Company Admin Panel

Each company has its own dedicated admin panel. All data inside a company panel is private to that company. The sections below apply to every company on the platform.

### 3.1 Dashboard

Company-specific overview showing key business metrics and recent activity.

**Total: 1 section**

---

### 3.2 Orders

Manage all freight/shipment orders for the company. Orders are the core work items of the system.

| Action | Description |
|---|---|
| View all orders | See a list of all company orders with filters |
| Create order | Add a new shipment order (multiple types available — see Section 4) |
| Edit order | Modify an existing order |
| Delete order | Remove an order |
| Bulk delete | Delete multiple orders at once |
| Search customers | Find customers while creating or editing an order |
| Sync to QuickBooks | Push order data to QuickBooks for invoicing |

**Total actions: 7**

---

### 3.3 Manifests

Group orders together into manifests for dispatch and delivery management. A manifest is a trip/run that includes drivers, vehicles, and multiple stops.

*(Full manifest breakdown in Section 5)*

**Total actions: 14+ (see Section 5)**

---

### 3.4 Customers

Manage the company's customer accounts.

| Action | Description |
|---|---|
| View all customers | List of all company customers |
| Add customer | Create a new customer profile |
| Edit customer | Update customer information |
| Delete customer | Remove a customer |
| View customer profile | See full customer details and history |
| Sync to QuickBooks | Push customer data to QuickBooks |

**Customer Profile Fields:**

| Field | Description |
|---|---|
| Name | Customer company name |
| Short Code | Unique identifier used in order numbering |
| Address | Full mailing address (city, state, postal, country) |
| Email | Customer contact email |
| Currency | Default billing currency |
| Customer Type | Classification of the customer |
| Default Billing Option | How this customer is billed by default |
| Portal Access | Whether the customer has portal login access |
| Location Sharing | Whether location tracking is shared with this customer |
| Network Customer | Whether this is a network/partner customer |
| Quote Required | Whether a quote must be approved before orders proceed |

**Total actions: 6 | Total fields: 11**

---

### 3.5 Carriers

Manage transportation carrier companies used for shipments.

| Action | Description |
|---|---|
| View all carriers | List of all carriers the company works with |
| Add carrier | Register a new carrier |
| Edit carrier | Update carrier information |
| Delete carrier | Remove a carrier |
| View carrier profile | See full carrier details |

**Carrier Profile Fields:**

| Field | Description |
|---|---|
| Carrier Name | Name of the carrier company |
| DOT ID | Department of Transportation ID number |
| Docket Number | Carrier's docket/registration number |
| Address | Physical address (city, state, postal, country) |
| Currency | Billing currency for this carrier |
| Status | Whether the carrier is active |

**Total actions: 5 | Total fields: 6**

---

### 3.6 Equipment

Manage all vehicles and equipment used for deliveries.

| Action | Description |
|---|---|
| View all equipment | List of all company equipment |
| Add equipment | Register a new vehicle/trailer/asset |
| Edit equipment | Update equipment details |
| Delete equipment | Remove equipment from the system |
| View equipment profile | See full equipment details and current status |

**Equipment Profile Fields:**

| Field | Description |
|---|---|
| Name | Equipment name or unit number |
| Type | Primary classification (e.g., Truck, Trailer) |
| Sub-Type | More specific classification |
| Description | Additional notes |
| Status | Current state — Available / In Use / Maintenance |
| Current Manifest | Which manifest/trip this equipment is currently on |
| Last Seen | Timestamp of last system activity |
| Last Location | Most recent tracked location |

**Total actions: 5 | Total fields: 8**

---

### 3.7 Users

Manage all staff members within the company.

| Action | Description |
|---|---|
| View all users | List of all company staff members |
| Add user | Create a new staff account |
| Edit user | Update user information |
| Delete user | Remove a user |
| View user profile | See full user details |
| Enable / Disable user | Toggle a user's access without deleting them |

**User Profile Fields:**

| Field | Description |
|---|---|
| First Name / Last Name | Full name |
| Email | Login email address |
| Phone | Contact phone number |
| Address | Staff member's address |
| Profile Image | Profile photo |
| Status | Active or inactive |
| Email Notifications | Whether this user receives email alerts |
| Two-Factor Authentication | Whether 2FA is enabled for security |

**Total actions: 6 | Total fields: 8**

---

### 3.8 Roles & Permissions

Control what each staff member can do within the system. Roles are custom groups that define access levels.

| Action | Description |
|---|---|
| View all roles | List of all roles defined for the company |
| Create role | Define a new role (e.g., "Dispatcher", "Driver Manager") |
| Edit role | Rename or update a role |
| Update permissions | Choose exactly what each role can do |
| Delete role | Remove a role |
| View role details | See who has this role and what it allows |

**Permission Areas (what can be controlled per role):**

| Module | Controllable Actions |
|---|---|
| Orders | View, Create, Edit, Delete, View Logs, Other Actions |
| Manifests | View, Create, Edit, Delete, View Logs, Other Actions |
| Customers | View, Create, Edit, Delete, View Logs, Other Actions |
| Carriers | View, Create, Edit, Delete, View Logs, Other Actions |
| Equipment | View, Create, Edit, Delete, View Logs, Other Actions |
| Users | View, Create, Edit, Delete, View Logs, Other Actions |
| Roles | View, Create, Edit, Delete, View Logs, Other Actions |

**Total actions: 6 | Permission modules: 7 | Actions per module: 6**

---

### 3.9 Plugins

Extend the platform's capabilities with optional integrations.

| Action | Description |
|---|---|
| View all plugins | See available integrations |
| Enable / Disable plugin | Turn a plugin on or off for the company |
| Install plugin | Install a new plugin |
| Uninstall plugin | Remove a plugin |
| Configure plugin | Set plugin-specific settings |

**Available Plugins:**

| Plugin | Description |
|---|---|
| QuickBooks | Sync orders and customers directly to QuickBooks for accounting |

**Total actions: 5 | Available plugins: 1**

---

### 3.10 Company Settings (Branding)

Customize the look and feel for this specific company.

| Setting | Description |
|---|---|
| Light Logo | Company logo on light backgrounds |
| Dark Logo | Company logo on dark backgrounds |
| Icon Logo | Small icon version of the company logo |

**Total settings: 3**

---

### 3.11 User Profile (Personal Settings)

Each logged-in user can manage their own account.

| Action | Description |
|---|---|
| Edit profile | Update personal info (name, phone, address, photo) |
| Change password | Update login password |
| Delete account | Request account removal |

**Total actions: 3**

---

### Company Admin — Total Feature Count

| Section | Items |
|---|---|
| Dashboard | 1 |
| Orders | 7 actions |
| Manifests | 14+ actions |
| Customers | 6 actions + 11 fields |
| Carriers | 5 actions + 6 fields |
| Equipment | 5 actions + 8 fields |
| Users | 6 actions + 8 fields |
| Roles & Permissions | 6 actions, 7 modules × 6 permission types |
| Plugins | 5 actions, 1 plugin |
| Company Settings | 3 settings |
| User Profile | 3 actions |
| **TOTAL SECTIONS** | **11** |

---

## 4. Orders — Types & Details

Orders represent individual shipment requests. Each order can have one of five types, depending on the routing and pickup/delivery structure.

---

### 4.1 Order Types

#### Type 1: Point to Point
**What it is:** A simple, direct shipment from one location to one destination.

- One pickup location
- One delivery location
- Straightforward A → B routing
- Best for: Single truckload deliveries between two fixed locations

---

#### Type 2: Single Shipper
**What it is:** One pickup location, multiple delivery destinations.

- One origin/shipper location
- Multiple consignee (delivery) stops
- The truck picks up everything in one place and delivers to several locations
- Best for: Distribution runs from a warehouse to multiple customers

---

#### Type 3: Single Consignee
**What it is:** Multiple pickup locations, one delivery destination.

- Multiple shipper/pickup stops
- One final destination
- The truck collects freight from several locations and delivers it all to one place
- Best for: Consolidation runs, collecting from multiple suppliers to one warehouse

---

#### Type 4: Sequence
**What it is:** A defined sequence of stops in a specific, ordered route.

- Multiple stops in a fixed order
- Each stop is visited in sequence
- The route does not vary
- Best for: Milk runs, scheduled routes with a strict stop order

---

#### Type 5: Multi-Stop
**What it is:** Multiple stops with flexible routing.

- Multiple pickup and delivery stops
- Stops can be organized dynamically
- Best for: Complex routes where pickup and delivery stops are mixed throughout the trip

---

### 4.2 What Is Inside an Order

Every order, regardless of type, contains the following information:

**Core Information:**

| Field | Description |
|---|---|
| Order Number | Auto-generated unique ID (format: `COMPANY-CUSTOMER-ID`) |
| Order Type | One of the 5 types above |
| Status | Current stage of the order (see statuses below) |
| Customer | Which customer this order belongs to |
| Reference Number | External reference ID provided by the customer |
| Customer PO Number | Customer's purchase order number |
| Special Instructions | Custom notes or requirements for this shipment |

**International / Customs Fields:**

| Field | Description |
|---|---|
| Customs Broker | Name/company handling customs clearance |
| Port of Entry | Where the shipment enters the country |
| Declared Value | Value of goods for customs purposes |
| Container Number | Shipping container ID (for ocean freight) |

**Stops (per stop within the order):**

| Field | Description |
|---|---|
| Location Name | Name of the stop location |
| Full Address | Street, city, state, country, postal code |
| Service Type | Type of service at this stop (e.g., Truckload) |
| Measurement Type | Units used (e.g., inches/pounds) |
| Appointment Window | Start and end time for the appointment |
| Is Appointment | Whether a scheduled appointment is required |
| Shipper / Consignee Details | Contact info and details at this stop |
| Commodities | What goods are being shipped (items, weight, dimensions) |
| Accessorials | Additional services at this stop (e.g., Liftgate, Inside Delivery) |
| Billing Information | Billing details specific to this stop |

**Links to Other Records:**

| Link | Description |
|---|---|
| Manifest | Which manifest/trip this order is assigned to (optional) |
| Quote | Pricing quote associated with this order |
| QuickBooks Invoice | Linked invoice in QuickBooks (if synced) |

---

### 4.3 Order Statuses

| Status | Meaning |
|---|---|
| Draft | Order has been started but not finalized — still being filled in |
| *(Additional statuses managed by the system)* | |

> **Note:** Additional statuses beyond "Draft" are managed within the system. Full status flow (e.g., Pending, In Progress, Delivered, Invoiced) should be confirmed with the development team for the current state.

---

### 4.4 What is Still Remaining / To Be Confirmed for Orders

| Item | Status |
|---|---|
| Full order status workflow (beyond "Draft") | To be confirmed — statuses exist in system but full list not yet documented |
| Order reporting / export | Not yet identified in current system |
| Order search and filtering options | Available in list view but detailed filter options not yet documented |
| Customer portal order visibility | Referenced in customer settings but full behavior not documented |
| Quote workflow (quote → order approval) | Quote model exists, full approval workflow not documented |
| Email notifications on order events | User setting exists, trigger events not fully documented |

---

## 5. Manifests — Full Breakdown

A **Manifest** is a trip or dispatch run. It groups multiple orders together and assigns drivers, vehicles, and carriers to complete those deliveries. Think of it as the "dispatch sheet" for a specific run.

---

### 5.1 What a Manifest Contains

**Core Manifest Information:**

| Field | Description |
|---|---|
| Manifest Code | Unique identifier for this manifest/trip |
| Status | Current state of the manifest (see statuses below) |
| Start Date | The date this manifest/trip begins |
| Draft | Whether this manifest is still in draft (not dispatched) |
| Freight Information | General freight notes for the entire run |
| Previous Stop | Reference to the previous stop in the route |
| Next Stop | Reference to the next stop coming up |
| Manifest Document | Attached document for this manifest |

---

### 5.2 What Can Be Assigned to a Manifest

**Drivers:**

| Action | Description |
|---|---|
| Assign driver | Add a driver to this manifest/trip |
| Remove driver | Take a driver off this manifest |
| View available drivers | See which drivers are free to be assigned |
| Sync drivers | Update the driver assignment list |

**Carriers:**

| Action | Description |
|---|---|
| Assign carrier | Add a carrier company to this manifest |
| Remove carrier | Remove a carrier from this manifest |
| View available carriers | See which carriers are available |
| Sync carriers | Update the carrier assignment |

**Equipment (Vehicles/Trailers):**

| Action | Description |
|---|---|
| Assign equipment | Add a truck, trailer, or other equipment |
| Remove equipment | Remove equipment from this manifest |
| View available equipment | See which units are free |
| Sync equipment | Update the equipment assignment |

**Orders & Stops:**

| Action | Description |
|---|---|
| Add stops | Add delivery/pickup stops to this manifest |
| Remove stops | Remove a stop from this manifest |
| View all orders on manifest | See all orders grouped under this manifest |

---

### 5.3 Manifest Actions

| Action | Description |
|---|---|
| Create manifest | Start a new manifest/trip |
| Quick create | Create a manifest instantly with today's date and default settings |
| Edit manifest | Modify manifest details |
| Delete manifest | Remove a manifest |
| Bulk delete | Delete multiple manifests at once |

---

### 5.4 Cost Management on a Manifest

Each manifest can have detailed cost estimates attached to it.

**Cost Estimate Fields:**

| Field | Description |
|---|---|
| Type | Category of cost (e.g., Fuel, Driver Pay, Tolls) |
| Description | Notes about this cost item |
| Quantity | How many units of this cost |
| Rate | Price per unit |
| Estimated Cost | Calculated total (Quantity × Rate) |

Multiple cost lines can be added to build up the full estimated cost of a manifest/trip.

---

### 5.5 Rate Confirmation

- A **Rate Confirmation PDF** can be generated and downloaded for each manifest
- This document confirms the agreed rate between the company and the carrier/customer
- Used as the official contract/agreement for the trip

---

### 5.6 Manifest Statuses

| Status | Meaning |
|---|---|
| Pending | Manifest has been created but not yet dispatched |
| *(Additional statuses)* | Active, In Transit, Completed, etc. — to be confirmed |

---

### 5.7 What is Still Remaining / To Be Confirmed for Manifests

| Item | Status |
|---|---|
| Full status workflow (Pending → Dispatched → In Transit → Completed) | Partial — "Pending" confirmed, full flow not yet documented |
| Driver mobile/app tracking integration | Equipment has last_seen/last_location fields but full tracking not documented |
| Manifest reporting or summary export | Not yet identified |
| Proof of delivery (POD) capture | Not yet identified in current system |
| Customer notifications on manifest events | Not yet documented |
| Manifest timeline / history view | Not yet identified |
| Stop-level status updates | Stop model exists, status tracking at stop level not documented |

---

## 6. Summary Counts

### Super Admin Panel — At a Glance

| Feature Area | Count |
|---|---|
| Dashboard sections | 1 |
| Companies management actions | 5 |
| Users management actions | 5 |
| Activity log views | 1 |
| System branding settings | 4 |
| **Total Super Admin Features** | **16** |

---

### Company Admin Panel — At a Glance

| Feature Area | Actions | Fields/Details |
|---|---|---|
| Dashboard | 1 | — |
| Orders | 7 | 5 order types, 13+ fields per order |
| Manifests | 14 | 8 fields, drivers/carriers/equipment/stops |
| Customers | 6 | 11 fields |
| Carriers | 5 | 6 fields |
| Equipment | 5 | 8 fields |
| Users | 6 | 8 fields |
| Roles & Permissions | 6 | 7 modules × 6 permission actions = 42 permission options |
| Plugins | 5 | 1 active plugin (QuickBooks) |
| Company Branding | 3 | — |
| User Profile | 3 | — |
| **Totals** | **61 actions** | **11 sections** |

---

### Orders — At a Glance

| Order Type | Description |
|---|---|
| Point to Point | 1 pickup → 1 delivery |
| Single Shipper | 1 pickup → many deliveries |
| Single Consignee | Many pickups → 1 delivery |
| Sequence | Many stops in fixed order |
| Multi-Stop | Many stops, flexible |
| **Total Types** | **5** |

---

### Manifests — At a Glance

| Component | Count |
|---|---|
| Core manifest fields | 8 |
| Driver management actions | 4 |
| Carrier management actions | 4 |
| Equipment management actions | 4 |
| Stop management actions | 2 |
| Manifest CRUD actions | 5 |
| Cost estimate fields | 5 |
| Documents/downloads | 1 (Rate Confirmation PDF) |
| **Total Manifest Actions** | **28** |

---

### Items Still To Be Confirmed / Remaining

| Area | Remaining Items |
|---|---|
| Orders | Full status workflow, quote approval flow, reporting/export, notifications |
| Manifests | Full status workflow, proof of delivery, stop-level status, tracking integration, reporting |
| Plugins | Additional plugins beyond QuickBooks not yet built |
| Customers | Customer portal behavior not fully documented |
| General | Email notification triggers not documented |

---

*This document covers the current state of the TMS platform as of May 2026. For technical specifications, database schemas, or API documentation, refer to the technical documentation or contact the development team.*
