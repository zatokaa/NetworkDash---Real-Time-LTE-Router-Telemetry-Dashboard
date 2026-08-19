# 📡 NetworkDash - 4G LTE Router Signal Monitoring Dashboard

NetworkDash is a modern, lightweight **4G LTE Router Signal & Telemetry Monitoring Dashboard** built with **Laravel 12**, **Livewire 3**, **Alpine.js**, **Tailwind CSS**, and **Chart.js**.

It delivers plain-English, real-time cellular diagnostics in a **Dark Bento Box** interface designed to transform complex RF parameters into clear, actionable network intelligence.

---

## ✨ Features Overview

### 1. 🎛️ Dark Bento Box Dashboard
- **4 Primary Radio Metrics:** RSRP (Signal Strength), RSSI (Received Power), RSRQ (Signal Quality), and SINR (Signal-to-Noise Ratio) with delta trends against previous readings.
- **Connection Health Score (0–100):** Weighted multi-variable connection score with plain-English link health analysis.
- **RF Interference & Noise Detection:** Automatic detection of strong signal with high interference (e.g. good RSRP with low SINR).
- **Horizontal Signal Gauges:** Normalized visual power meters for RSRP, RSRQ, and SINR.
- **Carrier & Radio Parameters:** LTE Band, Downlink Bandwidth, EARFCN, Transmission Mode (e.g., TM8), TX Power, RRC State, DL MCS, and CQI.
- **Cell Tower Diagnostics with 1-Click Copy:** eNodeB ID, Sector Cell ID, Global Cell ID (ECI), and Physical Cell ID (PCI).
- **Historical Statistical Aggregates:** Best, Worst, and Average computations across `15M`, `30M`, `1H`, and `24H` windows.

### 2. 🔌 Real Hardware Driver Integration (ZLT / Tozed / S10 / P11X / P21)
- **Direct Live Hardware Polling:** Connects to router web gateways (e.g. `http://192.168.0.1/cgi-bin/http.cgi`) via MD5 authentication.
- **Automated Parameter Extraction:** Real-time decoding of radio parameters and automatic calculation of eNodeB base station and local cell sector ID.
- **Session Token Management:** Intelligent session caching with automatic relogin handling.

### 3. 📈 Interactive Signal Telemetry Chart
- **Lightweight Chart.js Splines:** Smooth vertical gradients and dark grid lines.
- **Dynamic Metric Switcher:** Switch between `RSRP` (Gold), `SINR` (Emerald), `RSRQ` (Cyan), and `RSSI` (Violet) on the fly.
- **Timeframe Selector:** `15M`, `30M`, `1H`, `6H`, `24H`, and `7D`.

### 4. 🔔 Connection Event Tracker
- **Automated Handover Detection:** Tracks cell tower handovers and carrier band switches.
- **Signal Quality Alerts:** Logs degradation to Poor and transitions to Excellent.
- **Visual Delta Badges:** Shows previous vs. new parameter states (e.g. `B40 → B3`).

### 5. 📜 Historical Telemetry Log & CSV Export
- **Multi-Filter Telemetry Grid:** Filter by Target Router, Timeframe, Quality Rating, and live keyword search.
- **Column Sorting:** Sort by Timestamp, RSRP, RSSI, RSRQ, and SINR.
- **Memory-Safe CSV Stream Export:** Chunked streamed CSV export suitable for large datasets on cPanel/shared hosting.

### 6. ⚙️ Configurable Thresholds & Settings
- **Signal Boundary GUI:** Custom cutoffs for RSRP, RSSI, RSRQ, and SINR with boundary validation.
- **Scoring Weights:** Interactive percentage sliders for RSRP, SINR, and RSRQ with $100\%$ sum validation.
- **Privacy & Masking:** Sensitive hardware identifier masking (IMEI, MAC, IMSI, ICCID) with unmask toggle.
- **Reset to Defaults:** One-click factory reset.

### 7. ⏱️ Auto-Refresh & Background Polling
- **Cadence Options:** `OFF (Manual)`, `10 Seconds`, `30 Seconds`, `1 Minute`, `5 Minutes`.
- **Session Persistence:** Remembers user refresh preference across visits.
- **Client-Side Live Clock:** Real-time ticking relative time counter without network overhead.

### 8. 📱 Mobile Optimization
- **Responsive Breakpoints:** Optimized for `360px`, `390px`, `430px`, `768px`, `1024px`, and `1440px+`.
- **Single-Column Mobile Cards:** Mobile-optimized card layout replacing horizontal scrolling tables.
- **Mobile Bottom Navigation Bar:** Quick navigation docked to the screen bottom for touch ergonomics.

---

## 🚀 Installation & Local Setup

### Prerequisites
- PHP 8.2 or 8.3+ with `pdo_mysql`, `curl`, `mbstring`, `openssl`
- MySQL 8.0+ / MariaDB
- Composer & Node.js (v18+)

### Step-by-Step Setup

1. **Clone repository & enter directory:**
   ```bash
   cd c:\xampp\htdocs\NetworkDash
   ```

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment:**
   Update `.env` database and application settings:
   ```ini
   APP_NAME=NetworkDash
   APP_ENV=local
   APP_DEBUG=false
   APP_URL=http://127.0.0.1:8001

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3307
   DB_DATABASE=networkdash
   DB_USERNAME=root
   DB_PASSWORD=1234
   ```

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Build Frontend Assets:**
   ```bash
   npm run build
   ```

6. **Start Application:**
   ```bash
   php artisan serve --port=8001
   ```
   Open `http://127.0.0.1:8001/` in your browser.

---

## 🔑 Default Administrator Credentials
- **Email:** `admin@networkdash.local`
- **Password:** `admin1234`

---

## 📦 cPanel & Shared Hosting Deployment Guide

1. Upload files to your cPanel `public_html` (or subfolder).
2. Point your cPanel Document Root to the `public/` directory.
3. Import the MySQL database and update `.env` with your database credentials.
4. Set permissions on `storage/` and `bootstrap/cache/` (`chmod -R 775`).
5. Run `php artisan optimize` for production caching.

---

## 🛡️ License
Built for personal and enterprise 4G LTE signal telemetry monitoring.
