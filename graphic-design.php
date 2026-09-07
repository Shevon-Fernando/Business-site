<?php
/**
 * noontech - Graphic Design Categories Portfolio Page
 * Fetches design categories dynamically from the MySQL database with a graceful local fallback.
 */
session_start();
include 'includes/db.php';

// Prepare dynamic categories buffer
$categories = [];
$image_links = [];
$faqs = [];

if (isset($conn) && !$db_connection_error) {
    // Retrieve only visible categories from database
    $sql = "SELECT id, title, description, image_path, icon_svg, price, discount_percent, delivery_time, revisions, info_items FROM `graphic_design_categories` WHERE `is_visible` = 1 ORDER BY id ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    // Retrieve associated image links gracefully with try-catch
    try {
        $img_result = $conn->query("SELECT category_id, link_url FROM `image_links`");
        if ($img_result) {
            while ($img_row = $img_result->fetch_assoc()) {
                $image_links[$img_row['category_id']][] = $img_row['link_url'];
            }
        }
    } catch (mysqli_sql_exception $e) {
        // Table doesn't exist yet, ignore silently rather than crashing
    }

    // Fetch FAQs for graphic design
    try {
        $faq_res = $conn->query("SELECT * FROM `faqs` WHERE category='graphic_design' ORDER BY sort_order ASC, id ASC");
        if ($faq_res) {
            while ($r = $faq_res->fetch_assoc()) {
                $faqs[] = $r;
            }
        }
    } catch (mysqli_sql_exception $e) {
    }
}

// Graceful fallback to default mock categories if connection fails or tables are uninitialized
if (empty($categories)) {
    $categories = [
        [
            'id' => 1,
            'title' => 'Logo Design',
            'description' => 'Bespoke corporate identity, corporate wordmarks, and iconic vector logos designed for premium scale.',
            'image_path' => '',
            'icon_svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
            'price' => 'Rs 2,500',
            'discount_percent' => 20,
            'delivery_time' => '2-3 Days',
            'revisions' => 'Unlimited',
            'info_items' => '[{"message":"Source files (AI/EPS)","icon":"🔥"},{"message":"Commercial usage rights","icon":"💼"}]'
        ],
        [
            'id' => 2,
            'title' => 'Invitations & Cards',
            'description' => 'Premium high-fidelity invitation cards, corporate newsletters, and layout designs printed flawlessly.',
            'image_path' => '',
            'icon_svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
            'price' => 'Rs 1,800',
            'discount_percent' => 0,
            'delivery_time' => '1-2 Days',
            'revisions' => '2 Iterations',
            'info_items' => '[{"message":"Print ready files","icon":"🖨️"},{"message":"Custom dimensions","icon":"📏"}]'
        ],
        [
            'id' => 3,
            'title' => 'UI/UX Prototypes',
            'description' => 'High-fidelity wireframes, interactive web layout blueprints, and mobile presentation cards.',
            'image_path' => '',
            'icon_svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
            'price' => 'Rs 4,500',
            'discount_percent' => 10,
            'delivery_time' => '5-7 Days',
            'revisions' => '3 Iterations',
            'info_items' => '[{"message":"Figma source file","icon":"🎨"},{"message":"Clickable prototype","icon":"✨"}]'
        ],
        [
            'id' => 4,
            'title' => 'Social Media Kits',
            'description' => 'Optimized platform graphics, header templates, advertising banners, and promotional asset styling.',
            'image_path' => '',
            'icon_svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
            'price' => 'Rs 3,200',
            'discount_percent' => 0,
            'delivery_time' => '2-3 Days',
            'revisions' => '1 Iteration',
            'info_items' => '[{"message":"5 Platform templates","icon":"📱"},{"message":"Custom branding","icon":"🌟"}]'
        ]
    ];
}

$page_title = "Graphic Design Portfolio";
include 'includes/header.php';
?>

<main id="graphicDesignMain">
    <!-- Hero Block -->
    <section class="graphic-design-hero" id="designHero">
        <div class="hero-container">
            <h1 class="hero-title" id="mainDesignTitle">Graphic Design</h1>
            <p class="hero-subtitle">
                Explore our dynamic design solutions. Custom branding packages, corporate stationery layouts, and
                interactive UI/UX blueprints, built dynamically to fit your business requirements.
            </p>
        </div>
    </section>

    <!-- Categories Grid Block -->
    <section class="graphic-design-categories" id="designCategories">
        <div class="categories-container">
            <div class="categories-grid" id="designGrid">
                <?php foreach ($categories as $cat):
                    $discount = intval($cat['discount_percent'] ?? 0);
                    $price = trim($cat['price'] ?? '');
                    ?>
                    <article class="category-box" id="cat-box-<?php echo $cat['id']; ?>">

                        <!-- ========================================================
                             Discount Badge — absolute top-right of the card wrapper.
                             Rendered only when discount_percent > 0.
                             ======================================================== -->
                        <?php if ($discount > 0): ?>
                            <div class="discount-badge" id="cat-badge-<?php echo $cat['id']; ?>"
                                aria-label="<?php echo $discount; ?> percent off">
                                <?php echo $discount; ?>% OFF
                            </div>
                        <?php endif; ?>

                        <!-- Media / Icon Area -->
                        <div class="category-media slideshow-container" data-catid="<?php echo $cat['id']; ?>"
                            style="position: relative; overflow: hidden; min-height: 200px;">
                            <?php if (isset($image_links[$cat['id']]) && count($image_links[$cat['id']]) > 0): ?>
                                <?php foreach ($image_links[$cat['id']] as $idx => $link): ?>
                                    <img src="<?php echo htmlspecialchars($link); ?>" alt="Slide" class="category-image slide-img"
                                        data-index="<?php echo $idx; ?>"
                                        style="<?php echo $idx === 0 ? 'display: block;' : 'display: none;'; ?> width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0;">
                                <?php endforeach; ?>
                                <?php if (count($image_links[$cat['id']]) > 1): ?>
                                    <button class="slide-nav prev-slide"
                                        style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; z-index: 10;"
                                        onclick="changeSlide(event, this, -1)">&#10094;</button>
                                    <button class="slide-nav next-slide"
                                        style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; z-index: 10;"
                                        onclick="changeSlide(event, this, 1)">&#10095;</button>
                                <?php endif; ?>
                            <?php elseif (!empty($cat['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($cat['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($cat['title']); ?>" class="category-image"
                                    id="cat-img-<?php echo $cat['id']; ?>">
                            <?php else: ?>
                                <div class="category-placeholder" id="cat-placeholder-<?php echo $cat['id']; ?>">
                                    <?php if (!empty($cat['icon_svg'])): ?>
                                        <?php echo $cat['icon_svg']; ?>
                                    <?php else: ?>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                            <polyline points="2 17 12 22 22 17"></polyline>
                                            <polyline points="2 12 12 17 22 12"></polyline>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Text + Price Details -->
                        <div class="category-details">
                            <h2 class="category-title" id="cat-title-<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['title']); ?>
                            </h2>
                            <p class="category-desc" id="cat-desc-<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['description']); ?>
                            </p>

                            <!-- Price Footer Row: shown only when a price value is stored -->
                            <?php if ($price !== ''): ?>
                                <div class="category-price-row" id="cat-price-row-<?php echo $cat['id']; ?>">
                                    <span class="category-price" id="cat-price-<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($price); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <!-- Actions / Order Now Button -->
                            <div class="category-actions"
                                style="margin-top: 16px; display: flex; justify-content: flex-end;">
                                <?php
                                $modal_cat = $cat;
                                $modal_images = [];
                                if (isset($image_links[$cat['id']]) && count($image_links[$cat['id']]) > 0) {
                                    $modal_images = $image_links[$cat['id']];
                                    $modal_cat['image_path'] = $modal_images[0];
                                }
                                ?>
                                <a href="http://wa.me/+94767865330" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-primary btn-small" style="border-radius: var(--radius-pill);">Contact
                                    Me</a>
                            </div>
                        </div>

                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($faqs)): ?>
        <section class="faq-section-frontend">
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
    <?php endif; ?>
</main>

<!-- Order Now Modal -->
<div id="orderModal" class="modal-overlay">
    <div class="modal-content">
        <!-- Close Button -->
        <button id="closeOrderModal" aria-label="Close modal">&times;</button>
        <!-- Discount Badge -->
        <div id="modalDiscountBadge" class="modal-discount-badge"></div>

        <!-- Left Side: Slideshow/Media -->
        <div class="modal-left">
            <div id="modalHeaderImage"
                style="position:relative; width:100%; height:100%; overflow:hidden; border-radius: var(--radius-md);">
                <!-- Slides injected by JS -->
                <button id="modalSlidePrev" onclick="modalChangeSlide(-1)"
                    style="display:none; position:absolute; left:8px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.45); color:#fff; border:none; border-radius:50%; width:34px; height:34px; cursor:pointer; z-index:10; font-size:16px; align-items:center; justify-content:center;">&#10094;</button>
                <button id="modalSlideNext" onclick="modalChangeSlide(1)"
                    style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.45); color:#fff; border:none; border-radius:50%; width:34px; height:34px; cursor:pointer; z-index:10; font-size:16px; align-items:center; justify-content:center;">&#10095;</button>
                <div id="modalSlideDots"
                    style="position:absolute; bottom:10px; left:0; right:0; display:flex; justify-content:center; gap:6px; z-index:10;">
                </div>
            </div>
        </div>

        <!-- Right Side: Details -->
        <div class="modal-right">
            <div class="modal-title-price-group">
                <h2 id="modalTitle"></h2>
                <div id="modalPrice"></div>
            </div>

            <p id="modalDesc"></p>

            <div class="modal-meta-grid">
                <div class="modal-meta-box">
                    <strong>Delivery Time</strong>
                    <span id="modalDelivery"></span>
                </div>
                <div class="modal-meta-box">
                    <strong>Revisions</strong>
                    <span id="modalRevisions"></span>
                </div>
            </div>

            <div id="modalInfoItems">
                <!-- dynamic items injected here -->
            </div>

            <div class="modal-action">
                <a href="http://wa.me/+94767865330" target="_blank" rel="noopener noreferrer"
                    class="btn btn-primary btn-full">Contact Me</a>
            </div>
        </div>
    </div>
</div>

<script>
    function changeSlide(e, btn, direction) {
        e.preventDefault();
        e.stopPropagation(); // prevent modal opening if user clicks arrows
        const container = btn.closest('.slideshow-container');
        const slides = container.querySelectorAll('.slide-img');
        if (slides.length === 0) return;

        let currentIndex = -1;
        slides.forEach((slide, idx) => {
            if (slide.style.display === 'block') {
                currentIndex = idx;
            }
        });

        if (currentIndex !== -1) {
            slides[currentIndex].style.display = 'none';
        }

        let nextIndex = currentIndex + direction;
        if (nextIndex >= slides.length) nextIndex = 0;
        if (nextIndex < 0) nextIndex = slides.length - 1;

        slides[nextIndex].style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('orderModal');
        const modalContent = modal.querySelector('.modal-content');
        const closeBtn = document.getElementById('closeOrderModal');

        document.addEventListener('click', (e) => {
            if (e.target.closest('.order-now-btn')) {
                const btn = e.target.closest('.order-now-btn');
                try {
                    const data = JSON.parse(btn.getAttribute('data-category'));

                    document.getElementById('modalTitle').textContent = data.title;
                    document.getElementById('modalDesc').textContent = data.description;

                    const priceEl = document.getElementById('modalPrice');
                    if (data.price) {
                        priceEl.style.display = 'block';
                        priceEl.textContent = data.price;
                    } else {
                        priceEl.style.display = 'none';
                    }

                    const discountBadge = document.getElementById('modalDiscountBadge');
                    const discount = parseInt(data.discount_percent || 0, 10);
                    if (discount > 0) {
                        discountBadge.style.display = 'block';
                        discountBadge.textContent = discount + '% OFF';
                    } else {
                        discountBadge.style.display = 'none';
                    }

                    document.getElementById('modalDelivery').textContent = data.delivery_time || 'Standard';
                    document.getElementById('modalRevisions').textContent = data.revisions || 'Standard';

                    // Image / Slideshow
                    const headerImg = document.getElementById('modalHeaderImage');
                    const prevBtn = document.getElementById('modalSlidePrev');
                    const nextBtn = document.getElementById('modalSlideNext');
                    const dotsContainer = document.getElementById('modalSlideDots');

                    // Clear previous slides/dots but keep buttons & dots container
                    headerImg.querySelectorAll('.modal-slide-img, .modal-icon-wrap').forEach(el => el.remove());
                    dotsContainer.innerHTML = '';
                    prevBtn.style.display = 'none';
                    nextBtn.style.display = 'none';

                    let modalImages = [];
                    try { modalImages = JSON.parse(btn.getAttribute('data-images') || '[]'); } catch (e) { }

                    if (modalImages.length > 0) {
                        let modalSlideIndex = 0;

                        modalImages.forEach((src, idx) => {
                            const img = document.createElement('img');
                            img.src = src;
                            img.className = 'modal-slide-img';
                            img.style.cssText = `position:absolute; inset:0; width:100%; height:100%; object-fit:cover; display:${idx === 0 ? 'block' : 'none'}; transition: opacity 0.3s ease;`;
                            headerImg.insertBefore(img, prevBtn);

                            // Dot
                            const dot = document.createElement('div');
                            dot.style.cssText = `width:8px; height:8px; border-radius:50%; background:${idx === 0 ? '#fff' : 'rgba(255,255,255,0.5)'}; cursor:pointer; transition:background 0.2s;`;
                            dot.onclick = () => goToModalSlide(idx);
                            dotsContainer.appendChild(dot);
                        });

                        function updateModalDots(idx) {
                            dotsContainer.querySelectorAll('div').forEach((d, i) => {
                                d.style.background = i === idx ? '#fff' : 'rgba(255,255,255,0.5)';
                            });
                        }

                        function goToModalSlide(newIdx) {
                            headerImg.querySelectorAll('.modal-slide-img').forEach((s, i) => {
                                s.style.display = i === newIdx ? 'block' : 'none';
                            });
                            modalSlideIndex = newIdx;
                            updateModalDots(newIdx);
                        }

                        window.modalChangeSlide = function (dir) {
                            const slides = headerImg.querySelectorAll('.modal-slide-img');
                            let next = modalSlideIndex + dir;
                            if (next >= slides.length) next = 0;
                            if (next < 0) next = slides.length - 1;
                            goToModalSlide(next);
                        };

                        if (modalImages.length > 1) {
                            prevBtn.style.display = 'flex';
                            nextBtn.style.display = 'flex';
                        }
                    } else if (data.image_path) {
                        const img = document.createElement('img');
                        img.src = data.image_path;
                        img.className = 'modal-slide-img';
                        img.style.cssText = 'position:absolute; inset:0; width:100%; height:100%; object-fit:cover;';
                        headerImg.insertBefore(img, prevBtn);
                    } else if (data.icon_svg) {
                        const svgWrap = document.createElement('div');
                        svgWrap.className = 'modal-icon-wrap';
                        svgWrap.style.cssText = 'width:100%; height:100%; display:flex; align-items:center; justify-content:center;';
                        svgWrap.innerHTML = data.icon_svg;
                        const svg = svgWrap.querySelector('svg');
                        if (svg) { svg.setAttribute('width', '64'); svg.setAttribute('height', '64'); svg.style.color = 'var(--text-tertiary)'; }
                        headerImg.insertBefore(svgWrap, prevBtn);
                    }

                    // Info Items
                    const itemsContainer = document.getElementById('modalInfoItems');
                    itemsContainer.innerHTML = '';
                    if (data.info_items) {
                        const items = typeof data.info_items === 'string' ? JSON.parse(data.info_items) : data.info_items;
                        if (Array.isArray(items)) {
                            items.forEach(item => {
                                const row = document.createElement('div');
                                row.style.display = 'flex';
                                row.style.alignItems = 'center'; // center aligns the button and text properly
                                row.style.justifyContent = 'flex-start';
                                row.style.background = 'transparent';
                                row.style.padding = '4px 0';
                                row.style.border = 'none';

                                const textSpan = document.createElement('span');
                                textSpan.textContent = item.message;
                                textSpan.style.color = 'var(--text-primary)';
                                textSpan.style.fontWeight = '500';
                                textSpan.style.fontSize = '14.5px';
                                textSpan.style.flex = '0 1 auto'; // Allows the link button to sit near the sentence end
                                textSpan.style.lineHeight = '1.5';

                                const iconWrap = document.createElement('div');
                                iconWrap.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-color)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                                iconWrap.style.flexShrink = '0';
                                iconWrap.style.display = 'flex';
                                iconWrap.style.alignItems = 'center';
                                iconWrap.style.justifyContent = 'center';
                                iconWrap.style.marginRight = '12px';
                                iconWrap.style.marginTop = '1px';

                                row.appendChild(iconWrap);
                                row.appendChild(textSpan);

                                if (item.link && item.link.trim() !== '') {
                                    const linkBtn = document.createElement('a');
                                    linkBtn.href = item.link.trim();
                                    linkBtn.target = "_blank";
                                    linkBtn.rel = "noopener noreferrer";
                                    // Use standard UI button classes
                                    linkBtn.className = "btn btn-outline btn-small";
                                    linkBtn.style.marginLeft = "12px";
                                    linkBtn.style.display = "inline-flex";
                                    linkBtn.style.alignItems = "center";
                                    linkBtn.style.gap = "6px";
                                    linkBtn.style.padding = "4px 10px";
                                    linkBtn.style.fontSize = "12.5px";
                                    linkBtn.style.textDecoration = "none";
                                    linkBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg> Link';
                                    linkBtn.style.flexShrink = "0";
                                    row.appendChild(linkBtn);
                                }

                                itemsContainer.appendChild(row);
                            });
                        }
                    }

                    // Show modal
                    modal.style.display = 'flex';
                    // Trigger reflow
                    void modal.offsetWidth;
                    modal.style.opacity = '1';
                    modalContent.style.transform = 'translateY(0)';
                } catch (err) {
                    console.error("Error parsing category data", err);
                }
            }
        });

        function closeModal() {
            modal.style.opacity = '0';
            modalContent.style.transform = 'translateY(20px)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300); // Wait for transition
        }

        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    });

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

<?php include 'includes/footer.php'; ?>