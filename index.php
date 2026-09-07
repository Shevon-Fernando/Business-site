<?php
/**
 * noontech - Homepage
 * Fulfills OpenAI light-theme styling layout + PHP/MySQL submission backend readiness.
 */
session_start();

// Database connection logic stub
// Fully compatible with InfinityFree host environments or local XAMPP setups.
/*
$servername = "sql302.infinityfree.com"; // Set your InfinityFree MySQL server
$username = "if0_38210344";             // Set your database user
$password = "yourprivatepassword";       // Set your database password
$dbname = "if0_38210344_noontech";       // Set your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
*/

// Handholder fallback file storage (saves contact requests locally if database is not configured)
$inquiry_logged = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'contact') {
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $details = isset($_POST['details']) ? htmlspecialchars(trim($_POST['details'])) : '';

    if (empty($name) || empty($email) || empty($details)) {
        $_SESSION['contact_error'] = "Missing inputs. Please complete all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Please provide a valid email address.";
    } else {
        // Database integration fallback block:
        // You can uncomment below block once you configure database on InfinityFree Control Panel.
        /*
        $sql = "INSERT INTO inquiries (name, email, details, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $details);
        $stmt->execute();
        $stmt->close();
        */

        // For immediate verification, store submission in a file if folder directory permits
        $submission_data = "[" . date('Y-m-d H:i:s') . "] Name: $name | Email: $email | Details: " . str_replace("\n", " ", $details) . "\n";
        @file_put_contents(__DIR__ . '/inquiries_log.txt', $submission_data, FILE_APPEND);

        $_SESSION['contact_success'] = "Thanks, $name! Project details submitted successfully.";

        // Redirect to avoid form resubmission behavior
        header("Location: index.php#contact");
        exit;
    }
}

$page_title = "Design & development for the modern web";
include 'includes/header.php';
?>

<style>
    .hero-gradient-text {
        background: linear-gradient(to right, #10a37f, #20c997, #3b82f6, #06b6d4, #10a37f);
        background-size: 200% auto;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: gradientMove 4s linear infinite;
    }

    @keyframes gradientMove {
        from {
            background-position: 0% center;
        }
        to {
            background-position: 200% center;
        }
    }
</style>

<main id="home">
    <!-- Magnetic Dots Background -->
    <canvas id="magneticDotsCanvas"
        style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; pointer-events: none; z-index: 9999;"></canvas>
    <!-- Hero & Action Suggestions Section -->
    <section class="section-hero">
        <div class="hero-container">
            <h1 class="hero-title"><span class="hero-gradient-text">Design & build</span> the future.</h1>
            <p class="hero-subtitle">
                noontech is a digital studio bridging minimalist interface layouts with robust backend architecture.
                Select a suggestion prompt below to outline your project order instantly.
            </p>

            <!-- Quick Action Suggester Chips -->
            <div class="quick-chips-wrapper">
                <h3 class="quick-chips-title">Suggested Inquiries</h3>
                <div class="quick-chips">
                    <button class="quick-chip" data-type="web">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                            <line x1="8" y1="21" x2="16" y2="21" />
                            <line x1="12" y1="17" x2="12" y2="21" />
                        </svg>
                        <span>Order Web Development</span>
                    </button>

                    <button class="quick-chip" data-type="logo">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" />
                            <path d="M12 8V16" />
                            <path d="M8 12H16" />
                        </svg>
                        <span>Request Logo Design</span>
                    </button>

                    <button class="quick-chip" data-type="branding">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                        </svg>
                        <span>UI/UX Branding</span>
                    </button>

                    <button class="quick-chip" data-type="ecommerce">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        <span>Custom E-Commerce Site</span>
                    </button>

                    <button class="quick-chip" data-type="dashboard">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v18h18" />
                            <path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3" />
                        </svg>
                        <span>Modern Dashboard UI</span>
                    </button>
                </div>
            </div>

            <!-- Services Split Layout -->
            <div class="services-grid">
                <!-- Service Block 1: Graphic Design -->
                <article class="service-card">
                    <div class="card-top">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9" />
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                            </svg>
                        </div>
                        <h2 class="card-title">Graphic Design</h2>
                        <p class="card-desc">Bespoke branding identity layouts created for clarity, scalability, and
                            premium readability.</p>
                        <ul class="card-features">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Logo & Brand Guides
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Advertisements
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Posters & Banners
                            </li>
                        </ul>
                    </div>
                    <div class="card-action">
                        <a href="graphic-design.php" class="btn btn-secondary"
                            style="width: 100%; text-align: center; justify-content: center; display: flex;">View
                            Graphic Design</a>
                    </div>
                </article>

                <!-- Service Block 2: Web Development -->
                <article class="service-card">
                    <div class="card-top">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="16 18 22 12 16 6" />
                                <polyline points="8 6 2 12 8 18" />
                            </svg>
                        </div>
                        <h2 class="card-title">Website Development</h2>
                        <p class="card-desc">Robust frontend code systems integrated smoothly with backend database
                            clusters.</p>
                        <ul class="card-features">
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                PHP / MySQL Core Engines
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Hosting Optimization
                            </li>
                            <li>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                Clean modern layouts
                            </li>
                        </ul>
                    </div>
                    <div class="card-action">
                        <a href="web-development.php" class="btn btn-secondary"
                            style="width: 100%; text-align: center; justify-content: center; display: flex;">View Web
                            Development</a>
                    </div>
                </article>
            </div>

        </div>
    </section>

    <!-- About Section -->
    <section class="section-about" id="about">
        <div class="about-container about-me-layout">
            <div class="about-me-left">
                <!-- Profile picture placeholder -->
                <div class="profile-picture-wrapper" style="margin-bottom: 24px;">
                    <img src="images/placeholder-profile.svg" alt="Shevon Fernando" class="profile-picture"
                        onerror="this.src=''; this.style.backgroundColor='#ececec';">
                </div>
                <div class="section-tag">About Me</div>
                <h2 class="section-title">I'm Shevon Fernando,</h2>
                <p class="about-me-bio">
                    a professional with 4 years of experience specializing in graphic design and web development.
                </p>
            </div>

            <div class="about-me-right">
                <div class="skills-wrapper">
                    <!-- Group 1: Graphic Design -->
                    <div class="skills-group">
                        <h3 class="skills-group-title">Graphic Design</h3>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">Photoshop</span>
                                <span class="skill-percent">90%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 90%;"></div>
                            </div>
                        </div>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">Illustrator</span>
                                <span class="skill-percent">40%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 40%;"></div>
                            </div>
                        </div>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">Canva</span>
                                <span class="skill-percent">50%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 50%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Group 2: Web Development -->
                    <div class="skills-group">
                        <h3 class="skills-group-title">Web Development</h3>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">HTML</span>
                                <span class="skill-percent">90%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 90%;"></div>
                            </div>
                        </div>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">CSS</span>
                                <span class="skill-percent">90%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 90%;"></div>
                            </div>
                        </div>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">JavaScript</span>
                                <span class="skill-percent">60%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 60%;"></div>
                            </div>
                        </div>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">PHP</span>
                                <span class="skill-percent">60%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 60%;"></div>
                            </div>
                        </div>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">MySQL</span>
                                <span class="skill-percent">40%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 40%;"></div>
                            </div>
                        </div>

                        <div class="skill-item">
                            <div class="skill-info">
                                <span class="skill-name">FlutterFlow</span>
                                <span class="skill-percent">75%</span>
                            </div>
                            <div class="skill-track">
                                <div class="skill-fill" style="width: 75%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="section-contact-us" id="contact">
        <div class="contact-us-wrapper">

            <!-- Section Header -->
            <div class="contact-us-header">
                <div class="section-tag">Get In Touch</div>
                <h2 class="contact-us-title">Contact Me</h2>
                <p class="contact-us-subtitle">Reach out through your preferred platform. I'm available during the hours
                    listed below.</p>
            </div>

            <!-- Two-Column Layout -->
            <div class="contact-us-body">

                <!-- LEFT SIDE -->
                <div class="contact-us-left">

                    <!-- Social Media Section -->
                    <div class="cu-group">
                        <h3 class="cu-group-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="18" cy="5" r="3" />
                                <circle cx="6" cy="12" r="3" />
                                <circle cx="18" cy="19" r="3" />
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                            </svg>
                            Social Media
                        </h3>
                        <ul class="cu-social-list">
                            <li class="cu-social-item">
                                <a href="https://wa.me/+94767865330" target="_blank" rel="noopener"
                                    class="cu-social-link">
                                    <span class="cu-social-icon cu-icon-whatsapp">
                                        <!-- WhatsApp icon -->
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                            <path
                                                d="M11.999 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.978-1.413A9.954 9.954 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.182a8.172 8.172 0 0 1-4.162-1.138l-.299-.178-3.094.878.886-3.023-.194-.31A8.182 8.182 0 1 1 12 20.182z" />
                                        </svg>
                                    </span>
                                    <span class="cu-social-name">WhatsApp</span>
                                    <svg class="cu-social-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </a>
                            </li>
                            <li class="cu-social-item">
                                <a href="https://t.me/noontech" target="_blank" rel="noopener" class="cu-social-link">
                                    <span class="cu-social-icon cu-icon-telegram">
                                        <!-- Telegram icon -->
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8l-1.7 8.02c-.12.56-.46.7-.93.44l-2.58-1.9-1.24 1.2c-.14.14-.26.26-.53.26l.19-2.65 4.84-4.37c.21-.19-.05-.29-.32-.1L7.59 14.52l-2.54-.79c-.55-.18-.56-.55.12-.81l9.93-3.83c.46-.17.86.11.54.71z" />
                                        </svg>
                                    </span>
                                    <span class="cu-social-name">Telegram</span>
                                    <svg class="cu-social-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </a>
                            </li>
                            <li class="cu-social-item">
                                <a href="https://instagram.com/noontech" target="_blank" rel="noopener"
                                    class="cu-social-link">
                                    <span class="cu-social-icon cu-icon-instagram">
                                        <!-- Instagram icon -->
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z" />
                                        </svg>
                                    </span>
                                    <span class="cu-social-name">Instagram</span>
                                    <svg class="cu-social-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </a>
                            </li>
                            <li class="cu-social-item">
                                <a href="https://github.com/noontech" target="_blank" rel="noopener"
                                    class="cu-social-link">
                                    <span class="cu-social-icon cu-icon-github">
                                        <!-- GitHub icon -->
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
                                        </svg>
                                    </span>
                                    <span class="cu-social-name">GitHub</span>
                                    <svg class="cu-social-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </a>
                            </li>
                            <li class="cu-social-item">
                                <a href="https://www.w3schools.com" target="_blank" rel="noopener"
                                    class="cu-social-link">
                                    <span class="cu-social-icon cu-social-icon-text">W3</span>
                                    <span class="cu-social-name">W3Schools</span>
                                    <svg class="cu-social-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M7 17L17 7" />
                                        <path d="M7 7h10v10" />
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Call Me Section -->
                    <div class="cu-group">
                        <h3 class="cu-group-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.5 2 2 0 0 1 3.6 1.32h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16.92z" />
                            </svg>
                            Call Me
                        </h3>
                        <div class="cu-phone-block">
                            <a href="tel:+94767865330" class="cu-phone-number">+94 767 865 330</a>
                            <p class="cu-schedule">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display:inline;vertical-align:-2px;margin-right:5px;">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                Monday – Friday: 2:00 pm – 10:00 pm<br>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="display:inline;vertical-align:-2px;margin-right:5px;">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                Saturday &amp; Sunday: 10:00 am – 10:00 pm
                            </p>
                        </div>
                    </div>

                </div>

                <!-- RIGHT SIDE — QR Code Grid -->
                <div class="contact-us-right">
                    <h3 class="cu-group-title cu-qr-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                            <rect x="5" y="5" width="3" height="3" fill="currentColor" />
                            <rect x="16" y="5" width="3" height="3" fill="currentColor" />
                            <rect x="16" y="16" width="3" height="3" fill="currentColor" />
                            <rect x="5" y="16" width="3" height="3" fill="currentColor" />
                        </svg>
                        Scan to Connect
                    </h3>
                    <div class="cu-qr-grid">
                        <div class="cu-qr-card">
                            <div class="cu-qr-frame">
                                <img src="images/WhatsApp.png" alt="WhatsApp QR Code">
                            </div>
                            <span class="cu-qr-label">WhatsApp</span>
                        </div>
                        <div class="cu-qr-card">
                            <div class="cu-qr-frame">
                                <img src="images/Telegram.png" alt="Telegram QR Code">
                            </div>
                            <span class="cu-qr-label">Telegram</span>
                        </div>
                        <div class="cu-qr-card">
                            <div class="cu-qr-frame">
                                <img src="images/Instagram.png" alt="Instagram QR Code">
                            </div>
                            <span class="cu-qr-label">Instagram</span>
                        </div>
                        <div class="cu-qr-card">
                            <div class="cu-qr-frame">
                                <img src="images/GitHub.png" alt="GitHub QR Code">
                            </div>
                            <span class="cu-qr-label">GitHub</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('magneticDotsCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let width, height;
        let dots = [];

        // Configuration
        const dotSpacing = 35; // Space between dots
        const dotRadius = 1.2;
        const attractRadius = 120; // How close mouse needs to be to affect dot
        const maxDisplacement = 25; // Max distance a dot can be pulled

        let mouse = { x: -1000, y: -1000 };

        function initLayout() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;

            dots = [];
            for (let x = 0; x < width; x += dotSpacing) {
                for (let y = 0; y < height; y += dotSpacing) {
                    dots.push({
                        originX: x,
                        originY: y,
                        x: x,
                        y: y
                    });
                }
            }
        }

        function animate() {
            ctx.clearRect(0, 0, width, height);

            const visibleRadius = 180; // Distance at which dots completely fade out

            for (let i = 0; i < dots.length; i++) {
                let dot = dots[i];

                const dx = mouse.x - dot.originX;
                const dy = mouse.y - dot.originY;
                const dist = Math.sqrt(dx * dx + dy * dy);

                // Optimization: if dot is far away and already at origin, skip math/drawing
                if (dist > visibleRadius + 50 && Math.abs(dot.x - dot.originX) < 1 && Math.abs(dot.y - dot.originY) < 1) {
                    dot.x = dot.originX;
                    dot.y = dot.originY;
                    continue;
                }

                let targetX = dot.originX;
                let targetY = dot.originY;

                if (dist < attractRadius) {
                    // How strong the attraction is
                    const pull = (1 - (dist / attractRadius)) * maxDisplacement;

                    // Vector from dot to mouse
                    const angle = Math.atan2(dy, dx);
                    targetX = dot.originX + Math.cos(angle) * pull;
                    targetY = dot.originY + Math.sin(angle) * pull;
                }

                // Smooth interpolation
                dot.x += (targetX - dot.x) * 0.1;
                dot.y += (targetY - dot.y) * 0.1;

                // Calculate distance for opacity (fade out seamlessly)
                const currentDist = Math.sqrt(Math.pow(mouse.x - dot.x, 2) + Math.pow(mouse.y - dot.y, 2));

                if (currentDist < visibleRadius) {
                    const opacity = (1 - (currentDist / visibleRadius)) * 0.4;
                    ctx.fillStyle = `rgba(0, 0, 0, ${opacity})`;
                    ctx.beginPath();
                    ctx.arc(dot.x, dot.y, dotRadius, 0, Math.PI * 2);
                    ctx.fill();
                }
            }

            requestAnimationFrame(animate);
        }

        window.addEventListener('mousemove', (e) => {
            mouse.x = e.clientX;
            mouse.y = e.clientY;
        });
        window.addEventListener('mouseout', () => {
            mouse.x = -1000;
            mouse.y = -1000;
        });
        window.addEventListener('resize', initLayout);

        initLayout();
        animate();
    });
</script>

<?php
// Output Toast script if redirect trigger is set in session
if (isset($_SESSION['contact_success'])) {
    $msg = $_SESSION['contact_success'];
    echo "<script>
      document.addEventListener('DOMContentLoaded', () => {
        if(window.showToast) {
          window.showToast(" . json_encode($msg) . ", 'success');
        }
      });
    </script>";
    unset($_SESSION['contact_success']);
}

if (isset($_SESSION['contact_error'])) {
    $msg = $_SESSION['contact_error'];
    echo "<script>
      document.addEventListener('DOMContentLoaded', () => {
        if(window.showToast) {
          window.showToast(" . json_encode($msg) . ", 'error');
        }
      });
    </script>";
    unset($_SESSION['contact_error']);
}

include 'includes/footer.php';
?>