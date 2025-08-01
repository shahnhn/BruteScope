# 🛡 BruteScope
Login Brute-Force Visualizer and real-time dashboard (PHP, MySQL, Tailwind, Chart.js)
<img width="1920" height="893" alt="image" src="https://github.com/user-attachments/assets/d366fd66-dcad-46f0-983b-8527d4713d2b" />


<img width="1897" height="820" alt="image" src="https://github.com/user-attachments/assets/602041e0-6ed5-4857-99bc-7b573bae2f55" /><img width="1904" height="720" alt="image" src="https://github.com/user-attachments/assets/9f89b360-75a6-4447-a1e4-e5d397591864" />


<img width="1897" height="853" alt="image" src="https://github.com/user-attachments/assets/198278c0-4dee-487a-86df-0d9c1e08bc18" />



# Features
- **Real‑time dashboard**: Visualizes login attempts live with Ajax polling.
- **Charts**:
  - Line chart: login attempts over time (per hour)
  - Pie chart: success vs fail
  - Bar chart: top 5 most targeted usernames
- **Filters-ready API**: supports filtering by username, status, and time range.
- **Clean, hacker‑themed UI** with Tailwind CSS & custom styling.

# Tech stack
- **PHP (vanilla, no frameworks)** – server-side logic & API
- **MySQL** – stores login attempts
- **Chart.js** – data visualizations
- **Tailwind CSS** – modern, responsive design
- **JavaScript (ES6)** – real‑time data updates with `fetch()`

# How it works
- Fake or real login attempts are recorded into the database.
- The dashboard (`dashboard.php`) fetches live data every 10 seconds.
- Charts update smoothly without page reload.
- API (`api/dashboard_data.php`) supports dynamic filters.

# Project structure
- **BruteScope**:
    - index.php
    - dashboard.php
    - log_view.php
    - .gitignore
    - **api**
        - dashboard_data.php
    - **assets**
        - index.css
        - dashboard.css
        - logview.css
