# noontech - Freelance Creative Agency Template

**noontech** is a modern, light-theme web frontend designed specifically for freelance graphic designers and web developers to receive project inquiries and register users.

The user interface and design tokens strictly emulate the official **OpenAI ChatGPT Light Theme** aesthetics: 
- Flat layout cards with pure white (#FFFFFF) and light-gray (#F9F9F9 / #F4F4F4) surfaces.
- 1px thin borders instead of heavy drop shadows.
- Geometric sans-serif typography (`Inter` stack).
- An interactive prompt box form modeled closely after the ChatGPT message text inputs.

---

## 📁 Codebase Directory Structure

```text
noontech/
│
├── assets/
│   ├── css/
│   │   └── style.css            # Custom layout, styling variables, and response media queries
│   └── js/
│       └── main.js             # Form animation, side-drawers, custom alerts, and suggester chips
│
├── includes/
│   ├── header.php              # Shared header template (links, mobile hamburger drawer, auth statuses)
│   └── footer.php              # Shared footer layout containing scripts and legal copyrights
│
├── index.php                   # Agency Landing page (Hero layout, services cards, About, order form)
├── login.php                   # Clean ChatGPT-styled "Welcome back" credentials portal
├── signup.php                  # ChatGPT-styled "Create your account" registration portal
├── logout.php                  # Destroys sessions and resets site authentication states
├── privacy.php                 # Sleek reader-friendly compliance guidelines
├── rules.php                   # Terms of Service / Rules page
│
├── database_setup.sql          # DB initialization schemas for users & order queues
└── README.md                   # Setup manual documentation
```

---

## ⚡ Interactive Prompt Injection Features
A key user experience highlight is the **quick-suggest chip system**:
1. When a user clicks suggestion chips like `"Order Web Development"` or `"UI/UX Branding"`, JavaScript inserts targeted multi-line templates into the project details area.
2. The browser automatically scrolls and focalizes on the form.
3. The prompt details text area automatically grows vertically matching user typing height.

---

## 🌐 InfinityFree & XAMPP DB Setup Guide

This site is completely standard PHP/HTML/CSS without external node packaging compilers. To link files to a live database:

1. **Import Database Schema**:
   Import `database_setup.sql` in local phpMyAdmin (http://localhost/phpmyadmin) or free control panels on InfinityFree hosting.
   
2. **Replace Connection Stubs**:
   In `index.php`, `login.php`, and `signup.php`, uncomment the connection blocks at the top of the file:
   ```php
   $servername = "sql302.infinityfree.com"; // Your InfinityFree SQL Host
   $username = "if0_xxxxxxx";             // Your Database Username
   $password = "yourdbpassword";          // Your database Password
   $dbname = "if0_xxxxxxx_noontech";       // Your Database Name
   ```
   
3. **Toggle Code Processors**:
   After database configurations are confirmed, remove the mock bypass sections in `login.php` and `signup.php`, and uncomment the database queries code blocks.
   
4. **File Log Backup**:
   For security, inquiries are automatically written in a backup file named `inquiries_log.txt` on submission, so you don't lose any client prompts.
