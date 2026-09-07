<?php
/**
 * noontech - Web Development Pricing Page
 * Displays the 3-column pricing card interface dynamically fetching data from web_dev_plans.
 */
session_start();
include 'includes/db.php';

// Fetch plans from database
$plans = [];
$faqs = [];
if (isset($conn) && !$db_connection_error) {
    $result = $conn->query("SELECT * FROM `web_dev_plans` ORDER BY id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }

    // Fetch FAQs for web development
    try {
        $faq_res = $conn->query("SELECT * FROM `faqs` WHERE category='web_development' ORDER BY sort_order ASC, id ASC");
        if ($faq_res) {
            while ($r = $faq_res->fetch_assoc()) {
                $faqs[] = $r;
            }
        }
    } catch (mysqli_sql_exception $e) {
    }
}

// Fallback plans if database is not seeded
if (empty($plans)) {
    $plans = [
        [
            'id' => 1,
            'title' => 'Starter',
            'summary' => 'Essential features for small sites.',
            'price_amount' => '$19',
            'billing_freq' => 'Per project',
            'features' => json_encode(["1 Landing Page", "Contact Form", "Mobile Responsive"])
        ],
        [
            'id' => 2,
            'title' => 'Pro Plan',
            'summary' => 'Advanced functionality for businesses.',
            'price_amount' => '$99',
            'billing_freq' => 'Billed annually',
            'features' => json_encode(["5 Pages Content", "CMS Integration", "Custom Analytics Dashboard"])
        ],
        [
            'id' => 3,
            'title' => 'Business',
            'summary' => 'Comprehensive solutions for enterprise.',
            'price_amount' => '$199',
            'billing_freq' => 'Billed annually',
            'features' => json_encode(["Unlimited Pages", "E-Commerce Ready", "Dedicated Support 24/7"])
        ]
    ];
}

$page_title = "Web Development Packages";
include 'includes/header.php';
?>

<style>
    /* CSS for Web Development Pricing Cards */
    .pricing-section {
        padding: 80px 20px;
        background-color: var(--bg-primary);
        max-width: 1200px;
        margin: 0 auto;
    }

    .pricing-header-wrapper {
        text-align: center;
        margin-bottom: 60px;
    }

    .pricing-title {
        font-size: 36px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 16px;
        letter-spacing: -0.02em;
    }

    .pricing-subtitle {
        font-size: 18px;
        color: var(--text-secondary);
        max-width: 600px;
        margin: 0 auto;
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        align-items: flex-start;
    }

    /* Base Card Styles */
    .pricing-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 32px;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    .pricing-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    /* Header Section */
    .card-header-plan {
        margin-bottom: 24px;
    }

    .plan-title-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .plan-name {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .popular-badge {
        background-color: var(--accent-light);
        color: var(--accent-color);
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 9999px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .plan-summary {
        font-size: 14.5px;
        color: var(--text-secondary);
        line-height: 1.5;
        margin: 0;
    }

    /* Pricing Block */
    .pricing-block {
        margin-bottom: 32px;
    }

    .price-display {
        font-size: 48px;
        font-weight: 800;
        color: var(--text-primary);
        letter-spacing: -0.03em;
        line-height: 1;
    }

    .billing-freq {
        font-size: 14px;
        color: var(--text-tertiary);
        margin-top: 8px;
        display: block;
    }

    /* CTA and Action Links */
    .card-action {
        margin-bottom: 32px;
        text-align: center;
    }

    .trial-link {
        display: block;
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 12px;
        text-decoration: none;
        transition: color 0.2s;
    }

    .trial-link:hover {
        color: var(--accent-color);
        text-decoration: underline;
    }

    /* Features List Section */
    .features-section {
        border-top: 1px solid var(--border-color);
        padding-top: 32px;
        flex-grow: 1;
    }

    .features-subheader {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 4px 0;
    }

    .features-subtitle {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 20px;
    }

    .features-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .feature-item {
        display: flex;
        align-items: flex-start;
        font-size: 14.5px;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    .feature-icon {
        width: 20px;
        height: 20px;
        background-color: #f3e8ff;
        /* Light purple circle */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .feature-icon svg {
        color: #9333ea;
        /* Purple checkmark */
    }

    /* Responsive Stack */
    @media (max-width: 992px) {
        .pricing-grid {
            grid-template-columns: 1fr;
            max-width: 500px;
            margin: 0 auto;
        }
    }
</style>

<main>
    <section class="pricing-section">
        <div class="pricing-header-wrapper">
            <h1 class="pricing-title">Web Development Plans</h1>
            <p class="pricing-subtitle">Select the right tier to meet your project goals, from simple landing pages to
                full-stack e-commerce platforms.</p>
        </div>

        <div class="pricing-grid">
            <?php foreach ($plans as $index => $plan):
                $feats = json_decode($plan['features'], true) ?: [];
                ?>
                <article class="pricing-card" <?php if ($index === 1)
                    echo 'style="border-color: var(--accent-color); padding: 31px;"'; ?>>

                    <!-- Header Section -->
                    <div class="card-header-plan">
                        <div class="plan-title-wrapper">
                            <h2 class="plan-name">
                                <?php echo htmlspecialchars($plan['title']); ?>
                            </h2>
                            <?php if ($index === 1): ?>
                                <span class="popular-badge">Popular</span>
                            <?php endif; ?>
                        </div>
                        <p class="plan-summary">
                            <?php echo htmlspecialchars($plan['summary']); ?>
                        </p>
                    </div>

                    <!-- Pricing Block -->
                    <div class="pricing-block">
                        <div class="price-display">
                            <?php echo htmlspecialchars($plan['price_amount']); ?>
                        </div>
                        <span class="billing-freq">
                            <?php echo htmlspecialchars($plan['billing_freq']); ?>
                        </span>
                    </div>

                    <!-- Action Button Block -->
                    <div class="card-action">
                        <?php if ($index === 1): ?>
                            <a href="http://wa.me/+94767865330" target="_blank" rel="noopener noreferrer"
                                class="btn btn-primary" style="width: 100%; justify-content: center;">Contact Me</a>
                        <?php else: ?>
                            <a href="http://wa.me/+94767865330" target="_blank" rel="noopener noreferrer"
                                class="btn btn-secondary"
                                style="width: 100%; justify-content: center; border: 1px solid var(--border-color);">Contact
                                Me</a>
                        <?php endif; ?>

                        <a href="#" class="trial-link">Start Free 7-Days Trial</a>
                    </div>

                    <!-- Features List Section -->
                    <div class="features-section">
                        <h3 class="features-subheader">Features</h3>
                        <div class="features-subtitle">Everything in our free plan includes</div>

                        <ul class="features-list">
                            <?php foreach ($feats as $f): ?>
                                <li class="feature-item">
                                    <div class="feature-icon">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                    </div>
                                    <span>
                                        <?php echo htmlspecialchars($f); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($faqs)): ?>
        <section class="faq-section-frontend" style="margin-top: 0;">
            <div class="faq-header">
                <h2 class="faq-title">Frequently Asked Questions</h2>
            </div>
            <div class="faq-accordion">
                <?php foreach ($faqs as $faq): ?>
                    <div class="faq-accordion-item">
                        <button class="faq-accordion-header" onclick="toggleFaq(this)">
                            <?php echo htmlspecialchars($faq['question']); ?>
                            <span class="faq-accordion-icon">+</span>
                        </button>
                        <div class="faq-accordion-body">
                            <div class="faq-accordion-content">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <script>
            function toggleFaq(btn) {
                const item = btn.closest('.faq-accordion-item');
                const body = item.querySelector('.faq-accordion-body');
                const icon = item.querySelector('.faq-accordion-icon');

                if (body.classList.contains('open')) {
                    body.style.maxHeight = null;
                    body.classList.remove('open');
                    icon.textContent = '+';
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    body.classList.add('open');
                    body.style.maxHeight = body.scrollHeight + 40 + 'px';
                    icon.textContent = '×';
                    icon.style.transform = 'rotate(90deg)';
                }
            }
        </script>
    <?php endif; ?>
</main>

<?php
include 'includes/footer.php';
?>