# 🧭 Localhost Dashboard

A modern PHP-based dashboard for managing your local web development projects. Designed to auto-detect local folders, support metadata editing via GUI, tagging, search, dark mode toggle, and more — all with a clean [Tabler UI](https://tabler.io/).

---

## ✨ Features

* Auto-detects projects from the parent directory
* GUI-based project metadata editing (title, description, tags, group, entry point)
* Project grouping and filtering
* Full-text search with tag support
* Sorting (A–Z, Z–A, last modified)
* Light/Dark theme switch with localStorage persistence
* Sticky header with Tabler icons and responsive design
* Last modification time and project size display (optional)

---

## 🚀 Installation

1. Clone or download this repository into your development environment.

```bash
cd your-dev-root
git clone https://github.com/Anthony1701/localhost-dashboard dashboard
```

2. Make sure your development root (e.g., XAMPP, MAMP, Laragon, Docker, etc.) is set up and projects are inside the parent folder.

3. Open `http://localhost/dashboard/` in your browser.

4. To automatically redirect to a default project:

   * Edit `config.json` and set the `"default"` key to the folder name of your preferred project.

---

## 🛠 Configuration

All configuration is stored in `config.json`. You can manually edit it or use the GUI via the edit modal.

```json
{
  "default": "my-project",
  "projects": {
    "my-project": {
      "title": "My Project",
      "description": "Main site",
      "tags": ["main", "php"],
      "group": "Websites",
      "entry": "www"
    }
  }
}
```

---

## 🖼 Design & UI

Built with [Tabler UI](https://tabler.io/) (CDN only). You can customize styles or extend functionality via `assets/scripts.js` and `assets/icon.svg`.

---

## 📦 Folder Structure

```
/your-dev-root/
├── project1/
├── project2/
├── dashboard/
│   ├── index.php
│   ├── save.php
│   ├── config.json
│   ├── assets/
│   │   ├── scripts.js
│   │   └── icon.svg
```

---

## 🧩 Compatibility

* PHP 7.4+
* Works with any localhost stack (XAMPP, MAMP, Laragon, Valet, Docker, WAMP...)

---

## 👤 Author

Made with ❤️ by [Antonín Šimkanin](https://simkanin.cz)

---

## 📃 License

MIT License. Free to use and modify.
