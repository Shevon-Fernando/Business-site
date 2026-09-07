<?php
/**
 * noontech - Terms & Rules
 * Clean layout for Terms of Service.
 */
session_start();

$page_title = "Terms & Rules";
include 'includes/header.php';
?>

<main>
    <section class="section-doc">
        <div class="doc-container">
            <div class="doc-header">
                <h1 class="doc-title">Terms & Rules</h1>
                <div class="doc-meta">Last updated: August 25, 2026</div>
            </div>

            <div class="doc-content">
                <p>Welcome to noontech! By browsing our catalog, register-testing our mock auth, or ordering creative
                    design tasks, you agree to comply with the rules outlined below.</p>

                <h2>1. Scope of Digital Deliverables</h2>
                <p>noontech operates as a freelance contract network providing two specialized tracks:</p>
                <ul>
                    <li><strong>Graphic Design:</strong> Vector assets, brand identity booklets, and interactive user
                        interface layouts.</li>
                    <li><strong>Website Development:</strong> Lightweight frontend HTML/CSS layouts, modular JavaScript
                        files, and relational database configuration ready for InfinityFree compatibility.</li>
                </ul>

                <h2>2. Standard Client Conduct</h2>
                <p>You agree not to use our code infrastructure or contact channels to submit malicious commands, SQL
                    injections, or scripts designed to destabilize host networks. All inquiries must describe legitimate
                    application or design briefs.</p>

                <h2>3. InfinityFree & MySQL Implementations</h2>
                <p>While noontech ensures our frontend codebase forms (Contact, Sign Up, Login) are prepared with
                    precise name controls for database mapping, we are not responsible for configuring your database
                    tables, host firewalls, or mod-security rules on free hosting clusters. Developers must review MySQL
                    connection settings before production deployment.</p>

                <h2>4. Open Source Foundations</h2>
                <p>Unless specified otherwise, our website layout, prompt-suggestion styling, and interaction animations
                    are free to use, customize, and refactor for personal or commercial projects. No active copyright
                    notices on layouts are enforced.</p>

                <h2>5. Termination of Services</h2>
                <p>We reserve the right to decline project inquiries that promote illegal, hateful, or abusive digital
                    content. Decisions are made at the sole discretion of our creator network.</p>
            </div>
        </div>
    </section>
</main>

<?php
include 'includes/footer.php';
?>