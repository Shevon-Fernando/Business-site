<?php
/**
 * noontech - Privacy Policy
 * Clean layout for legal agreements.
 */
session_start();

$page_title = "Privacy Policy";
include 'includes/header.php';
?>

<main>
    <section class="section-doc">
        <div class="doc-container">
            <div class="doc-header">
                <h1 class="doc-title">Privacy Policy</h1>
                <div class="doc-meta">Last updated: August 25, 2026</div>
            </div>

            <div class="doc-content">
                <p>At noontech, we are committed to protecting your privacy. This Privacy Policy describes how we
                    collect, use, and protect your personal information when you use our website or order services.</p>

                <h2>1. Information We Collect</h2>
                <p>We collect information that you voluntarily provide to us when placing an order or initiating
                    contact:</p>
                <ul>
                    <li><strong>Identity Data:</strong> Name, user aliases, login email address, database identifiers.
                    </li>
                    <li><strong>Contact Details:</strong> Email addresses and client contact profiles.</li>
                    <li><strong>Project Specifications:</strong> Input prompts, code snippets, project briefs, and
                        layout mockups.</li>
                </ul>

                <h2>2. How We Use Informational Assets</h2>
                <p>We process collected data to optimize creative outputs and code generation, specifically to:</p>
                <ul>
                    <li>Deliver graphic design vectors and robust PHP backend frameworks.</li>
                    <li>Connect frontend models to relational MySQL databases.</li>
                    <li>Respond to custom development inquiries and client invoices.</li>
                    <li>Track active user session statistics securely.</li>
                </ul>

                <h2>3. Database Security & Infrastructure</h2>
                <p>Our website frontends are designed to deploy directly on InfinityFree or custom environments. When
                    integrating database forms, your query information is transmitted securely to your configured
                    hosting environment. We recommend using standard password hashing mechanisms (such as PHP's
                    <code>password_hash()</code>) for storing account credentials.</p>

                <h2>4. Data Retention</h2>
                <p>We retain client submission logs only as long as necessary to complete design briefs. You can request
                    deletion of active records by contacting our support team directly.</p>

                <h2>5. Updates to This Agreement</h2>
                <p>We may refresh this privacy outline from time to time. We encourage visitors to check this page
                    periodically for updates.</p>
            </div>
        </div>
    </section>
</main>

<?php
include 'includes/footer.php';
?>