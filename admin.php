<?php
/**
 * noontech - Administrative Panel CRUD Category Manager
 * Admin route to dynamically add, edit, and delete Graphic Design Categories.
 */
session_start();
include 'includes/db.php';

// Route Guard: only permit authenticated users matching the standard admin address
$admin_email = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
$is_admin = ($admin_email === 'admin@gmail.com' || $admin_email === 'admin@noontech.com');
if (!$is_admin) {
    header("Location: login.php");
    exit;
}

// Handle Category Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    if (isset($conn) && !$db_connection_error) {
        // Fetch existing record to potentially clean up images if wanted
        $stmt_get = $conn->prepare("SELECT image_path FROM `graphic_design_categories` WHERE id = ?");
        $stmt_get->bind_param("i", $delete_id);
        $stmt_get->execute();
        $res_get = $stmt_get->get_result();
        if ($res_get && $row_get = $res_get->fetch_assoc()) {
            if (!empty($row_get['image_path']) && file_exists(__DIR__ . '/' . $row_get['image_path'])) {
                @unlink(__DIR__ . '/' . $row_get['image_path']);
            }
        }
        $stmt_get->close();

        // Perform deletion
        $stmt = $conn->prepare("DELETE FROM `graphic_design_categories` WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Category deleted successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to remove category: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['admin_error'] = "Authentication active, but database connection is offline.";
    }

    header("Location: admin.php");
    exit;
}

// Handle CRUD Saving Actions (Create & Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['create', 'update'])) {
    $action = $_POST['action'];
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $icon_svg = trim($_POST['icon_svg']); // Intentionally allow raw SVG string tags safely
    $price = htmlspecialchars(trim($_POST['price'] ?? ''));
    $discount_percent = max(0, min(100, intval($_POST['discount_percent'] ?? 0)));
    $delivery_time = htmlspecialchars(trim($_POST['delivery_time'] ?? ''));
    $revisions = htmlspecialchars(trim($_POST['revisions'] ?? ''));

    $info_items = $_POST['info_items'] ?? [];
    $filtered_info_items = [];
    if (is_array($info_items)) {
        foreach ($info_items as $item) {
            if (isset($item['message']) && trim($item['message']) !== '') {
                $filtered_info_items[] = [
                    'message' => htmlspecialchars(trim($item['message'])),
                    'link' => isset($item['link']) ? htmlspecialchars(trim($item['link'])) : ''
                ];
            }
        }
    }
    $info_items_json = json_encode($filtered_info_items);

    if (empty($title)) {
        $_SESSION['admin_error'] = "Category title cannot be blank.";
        header("Location: admin.php");
        exit;
    }

    $image_path = isset($_POST['existing_image']) ? $_POST['existing_image'] : '';

    if (isset($conn) && !$db_connection_error) {
        if ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO `graphic_design_categories` (title, description, image_path, icon_svg, price, discount_percent, delivery_time, revisions, info_items) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssisss", $title, $description, $image_path, $icon_svg, $price, $discount_percent, $delivery_time, $revisions, $info_items_json);
            if ($stmt->execute()) {
                $_SESSION['admin_success'] = "Category '$title' successfully created.";
            } else {
                $_SESSION['admin_error'] = "Failed to insert record: " . $conn->error;
            }
            $stmt->close();
        } elseif ($action === 'update' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE `graphic_design_categories` SET title = ?, description = ?, image_path = ?, icon_svg = ?, price = ?, discount_percent = ?, delivery_time = ?, revisions = ?, info_items = ? WHERE id = ?");
            $stmt->bind_param("sssssisssi", $title, $description, $image_path, $icon_svg, $price, $discount_percent, $delivery_time, $revisions, $info_items_json, $id);
            if ($stmt->execute()) {
                $_SESSION['admin_success'] = "Modified category details successfully saved.";
            } else {
                $_SESSION['admin_error'] = "Failed to save category edits: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $_SESSION['admin_error'] = "Could not connect to database. CRUD operations unavailable.";
    }

    header("Location: admin.php");
    exit;
}

// Handle Web Dev Plan Saving Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_web_plan') {
    $plan_id = intval($_POST['plan_id']);
    $title = htmlspecialchars(trim($_POST['web_title']));
    $summary = htmlspecialchars(trim($_POST['web_summary']));
    $price = htmlspecialchars(trim($_POST['web_price']));
    $billing = htmlspecialchars(trim($_POST['web_billing']));

    $features_raw = $_POST['web_features'] ?? [];
    $filtered_features = [];
    if (is_array($features_raw)) {
        foreach ($features_raw as $item) {
            if (trim($item) !== '') {
                $filtered_features[] = htmlspecialchars(trim($item));
            }
        }
    }
    $features_json = json_encode($filtered_features);

    if (isset($conn) && !$db_connection_error) {
        $stmt = $conn->prepare("UPDATE `web_dev_plans` SET title = ?, summary = ?, price_amount = ?, billing_freq = ?, features = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $summary, $price, $billing, $features_json, $plan_id);
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = "Web Development Plan '$title' successfully updated.";
        } else {
            $_SESSION['admin_error'] = "Failed to update web plan: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['admin_error'] = "Could not connect to database. CRUD operations unavailable.";
    }

    header("Location: admin.php?panel=web");
    exit;
}

// ── FAQ CRUD Handlers ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $faq_action = $_POST['action'];

    if ($faq_action === 'faq_create') {
        $faq_cat = in_array($_POST['faq_category'] ?? '', ['graphic_design', 'web_development']) ? $_POST['faq_category'] : 'graphic_design';
        $faq_q = htmlspecialchars(trim($_POST['faq_question'] ?? ''));
        $faq_a = htmlspecialchars(trim($_POST['faq_answer'] ?? ''));
        if ($faq_q !== '' && $faq_a !== '' && isset($conn) && !$db_connection_error) {
            $stmt = $conn->prepare("INSERT INTO `faqs` (category, question, answer) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $faq_cat, $faq_q, $faq_a);
            $stmt->execute() ? $_SESSION['admin_success'] = 'FAQ added successfully.' : $_SESSION['admin_error'] = 'Failed to add FAQ: ' . $conn->error;
            $stmt->close();
        } else {
            $_SESSION['admin_error'] = 'Question and answer cannot be empty.';
        }
        header('Location: admin.php?panel=faq&faq_tab=' . ($faq_cat === 'web_development' ? 'web' : 'gd'));
        exit;
    }

    if ($faq_action === 'faq_update') {
        $faq_id = intval($_POST['faq_id'] ?? 0);
        $faq_cat = in_array($_POST['faq_category'] ?? '', ['graphic_design', 'web_development']) ? $_POST['faq_category'] : 'graphic_design';
        $faq_q = htmlspecialchars(trim($_POST['faq_question'] ?? ''));
        $faq_a = htmlspecialchars(trim($_POST['faq_answer'] ?? ''));
        if ($faq_id > 0 && $faq_q !== '' && $faq_a !== '' && isset($conn) && !$db_connection_error) {
            $stmt = $conn->prepare("UPDATE `faqs` SET category = ?, question = ?, answer = ? WHERE id = ?");
            $stmt->bind_param('sssi', $faq_cat, $faq_q, $faq_a, $faq_id);
            $stmt->execute() ? $_SESSION['admin_success'] = 'FAQ updated successfully.' : $_SESSION['admin_error'] = 'Failed to update FAQ: ' . $conn->error;
            $stmt->close();
        }
        header('Location: admin.php?panel=faq&faq_tab=' . ($faq_cat === 'web_development' ? 'web' : 'gd'));
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'faq_delete' && isset($_GET['id'])) {
    $faq_id = intval($_GET['id']);
    $faq_tab_redirect = $_GET['faq_tab'] ?? 'gd';
    if (isset($conn) && !$db_connection_error) {
        $stmt = $conn->prepare("DELETE FROM `faqs` WHERE id = ?");
        $stmt->bind_param('i', $faq_id);
        $stmt->execute() ? $_SESSION['admin_success'] = 'FAQ deleted.' : $_SESSION['admin_error'] = 'Could not delete FAQ.';
        $stmt->close();
    }
    header('Location: admin.php?panel=faq&faq_tab=' . $faq_tab_redirect);
    exit;
}

// Check for edit initialization parameters
$edit_mode = false;
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    if (isset($conn) && !$db_connection_error) {
        $stmt = $conn->prepare("SELECT * FROM `graphic_design_categories` WHERE id = ?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $edit_mode = true;
            $edit_category = $row;
        }
        $stmt->close();

        $existing_gd_links = [];
        if ($edit_mode) {
            $stmt2 = $conn->prepare("SELECT link_url FROM image_links WHERE category_id = ?");
            $stmt2->bind_param("i", $edit_id);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            while ($r2 = $res2->fetch_assoc()) {
                $existing_gd_links[] = $r2['link_url'];
            }
            $stmt2->close();
        }
    }
}

// Fetch all dynamic categories to list in tabular dashboard view
$categories = [];
$web_plans = [];
$faqs_gd = [];
$faqs_web = [];
if (isset($conn) && !$db_connection_error) {
    // 1. Fetch Graphic Design categories
    $result = $conn->query("SELECT * FROM `graphic_design_categories` ORDER BY id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    // 2. Fetch Web Dev Plans
    $web_res = $conn->query("SELECT * FROM `web_dev_plans` ORDER BY id ASC");
    if ($web_res) {
        while ($r = $web_res->fetch_assoc()) {
            $web_plans[] = $r;
        }
    }

    // 3. Fetch FAQs by category
    $faq_res = $conn->query("SELECT * FROM `faqs` WHERE category='graphic_design' ORDER BY sort_order ASC, id ASC");
    if ($faq_res) {
        while ($r = $faq_res->fetch_assoc()) {
            $faqs_gd[] = $r;
        }
    }
    $faq_res2 = $conn->query("SELECT * FROM `faqs` WHERE category='web_development' ORDER BY sort_order ASC, id ASC");
    if ($faq_res2) {
        while ($r = $faq_res2->fetch_assoc()) {
            $faqs_web[] = $r;
        }
    }
}

$active_panel = 'graphic';
if (isset($_GET['panel'])) {
    if ($_GET['panel'] === 'web')
        $active_panel = 'web';
    elseif ($_GET['panel'] === 'faq')
        $active_panel = 'faq';
}
$active_faq_tab = (isset($_GET['faq_tab']) && $_GET['faq_tab'] === 'web') ? 'web' : 'gd';

$page_title = "Admin Panel";
include 'includes/header.php';
?>

<!-- ── Fixed Left Admin Sidebar ────────────────────── -->
<aside class="admin-sidebar" id="adminSidebar">
    <p class="admin-sidebar-label">Sections</p>
    <ul class="admin-sidebar-nav">
        <li>
            <a href="#" id="sidebarGraphicLink" class="<?php echo $active_panel === 'graphic' ? 'active' : ''; ?>"
                onclick="switchPanel('graphic'); return false;">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M18.37 2.63 14 7l-1.59-1.59a2 2 0 0 0-2.82 0L8 7l9 9 1.59-1.59a2 2 0 0 0 0-2.82L17 10l4.37-4.37a2.12 2.12 0 1 0-3-3Z" />
                    <path d="M9 8c-2 3-4 3.5-7 4l8 8c1-.5 3.5-2 4-7" />
                    <path d="M14.5 17.5 4.5 15" />
                </svg>
                Graphic Design
            </a>
        </li>
        <li>
            <a href="#" id="sidebarFaqLink" class="<?php echo $active_panel === 'faq' ? 'active' : ''; ?>"
                onclick="switchPanel('faq'); return false;">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                FAQ
            </a>
        </li>
        <li>
            <a href="#" id="sidebarWebLink" class="<?php echo $active_panel === 'web' ? 'active' : ''; ?>"
                onclick="switchPanel('web'); return false;">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                    <line x1="3" y1="9" x2="21" y2="9" />
                    <line x1="9" y1="21" x2="9" y2="9" />
                </svg>
                Web Development
            </a>
        </li>
    </ul>
    <p class="admin-sidebar-label" style="margin-top: auto;"></p>
    <ul class="admin-sidebar-nav">
        <li><a href="graphic-design.php" target="_blank">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                    <polyline points="15 3 21 3 21 9" />
                    <line x1="10" y1="14" x2="21" y2="3" />
                </svg>
                GD Page
            </a></li>
        <li><a href="web-development.php" target="_blank">
                <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                    <polyline points="15 3 21 3 21 9" />
                    <line x1="10" y1="14" x2="21" y2="3" />
                </svg>
                Web Dev Page
            </a></li>
    </ul>
</aside>

<main class="admin-wrapper" id="adminMainPage">
    <div class="admin-container" id="adminDashboardContainer">

        <!-- Database Setup Warning Notice -->
        <?php if ($db_connection_error): ?>
            <div style="background-color: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 16px; border-radius: var(--radius-md); font-size: 14px; line-height: 1.5; margin-bottom: 8px;"
                id="dbSetupAlert">
                <strong>Database Connection Error:</strong>
                <?php echo htmlspecialchars($db_connection_error); ?><br>
                Pleae import database parameters or verify local MySQL connection to enable CRUD capability. All portfolio
                displays are currently utilizing static offline fallbacks.
            </div>
        <?php endif; ?>

        <div class="admin-card" id="adminDashboardCard">
            <!-- Header Grid Title -->
            <div class="admin-header" id="dashboardHeader">
                <div class="admin-title-group" id="titleGroup">
                    <h1 id="adminPanelHeading">
                        <?php echo $active_panel === 'graphic' ? 'Graphic Design' : 'Web Development'; ?>
                    </h1>
                    <p id="adminPanelSubheading">
                        <?php echo $active_panel === 'graphic' ? 'Manage Graphic Design categories and items.' : 'Manage Web Development plans and features.'; ?>
                    </p>
                </div>
            </div>

            <!-- Hidden legacy switcher buttons kept for JS compatibility -->
            <div style="display:none;">
                <button type="button" id="switchGraphicBtn"></button>
                <button type="button" id="switchWebBtn"></button>
            </div>

            <!-- Panel: Graphic Design Admin -->
            <div id="graphic-design-panel"
                style="<?php echo $active_panel === 'graphic' ? 'display:block;' : 'display:none;'; ?>">
                <!-- Double Column layout: Listing & Action Panel -->
                <div class="admin-panel-grid" id="crudPanelsGrid">

                    <!-- Left Column: Categories List -->
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 16px; min-width: 0;"
                        id="listColumn">
                        <h2 class="admin-form-title" id="listTitle">Active Categories (
                            <?php echo count($categories); ?>)
                        </h2>

                        <div class="admin-table-wrapper" id="tableWrapper" style="overflow-x: auto;">
                            <?php if (empty($categories)): ?>
                                <p style="padding: 32px; color: var(--text-secondary); text-align: center; border: 1px dashed var(--border-color); border-radius: var(--radius-md);"
                                    id="emptyListMsg">
                                    Zero categories configured. Build your first Graphic Design category using the template
                                    console tool on the right.
                                </p>
                            <?php else: ?>
                                <table class="admin-table" id="categoriesCrudTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Cover</th>
                                            <th>Title & Details</th>
                                            <th style="width: 110px;">Price</th>
                                            <th style="width: 80px;">Discount</th>
                                            <th style="width: 90px; text-align:center;">Visible</th>
                                            <th style="width: 140px; text-align: right;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr id="category-row-<?php echo $cat['id']; ?>">
                                                <td>
                                                    <?php if (!empty($cat['image_path'])): ?>
                                                        <img src="<?php echo htmlspecialchars($cat['image_path']); ?>" alt="Cover"
                                                            class="admin-cell-image" id="admin-cell-img-<?php echo $cat['id']; ?>">
                                                    <?php else: ?>
                                                        <div class="admin-cell-placeholder"
                                                            id="admin-cell-placeholder-<?php echo $cat['id']; ?>">
                                                            <?php if (!empty($cat['icon_svg'])): ?>
                                                                <div style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;"
                                                                    id="admin-cell-svg-<?php echo $cat['id']; ?>">
                                                                    <?php echo $cat['icon_svg']; ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2"
                                                                    id="admin-cell-icon-<?php echo $cat['id']; ?>">
                                                                    <polygon points="12 2 2 7 12 12 22 7 12 2" />
                                                                    <polyline points="2 17 12 22 22 17" />
                                                                    <polyline points="2 12 12 17 22 12" />
                                                                </svg>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div style="font-weight: 600; color: var(--text-primary); font-size: 15px;"
                                                        id="cell-title-<?php echo $cat['id']; ?>">
                                                        <?php echo htmlspecialchars($cat['title']); ?>
                                                    </div>
                                                    <div style="font-size: 13.5px; color: var(--text-secondary); margin-top: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                                        id="cell-desc-<?php echo $cat['id']; ?>">
                                                        <?php echo htmlspecialchars($cat['description']); ?>
                                                    </div>
                                                </td>
                                                <!-- Price cell -->
                                                <td style="font-size: 14px; font-weight: 600; color: var(--text-primary);"
                                                    id="cell-price-<?php echo $cat['id']; ?>">
                                                    <?php echo !empty($cat['price']) ? htmlspecialchars($cat['price']) : '<span style="color:var(--text-tertiary)">—</span>'; ?>
                                                </td>
                                                <!-- Discount cell -->
                                                <td id="cell-discount-<?php echo $cat['id']; ?>">
                                                    <?php if (intval($cat['discount_percent'] ?? 0) > 0): ?>
                                                        <span
                                                            style="display:inline-block; background:#ef4444; color:#fff; font-size:11px; font-weight:700; padding:3px 7px; border-radius:4px; letter-spacing:0.03em;">
                                                            <?php echo intval($cat['discount_percent']); ?>% OFF
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="color:var(--text-tertiary); font-size:13px;">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center;">
                                                    <label class="vis-toggle" title="Toggle visibility">
                                                        <input type="checkbox" class="vis-checkbox"
                                                            data-id="<?php echo $cat['id']; ?>" <?php echo intval($cat['is_visible'] ?? 1) ? 'checked' : ''; ?>>
                                                        <span class="vis-slider"></span>
                                                    </label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <div class="admin-actions" id="cell-actions-<?php echo $cat['id']; ?>">
                                                        <a href="admin.php?action=edit&id=<?php echo $cat['id']; ?>"
                                                            class="btn btn-outline btn-small"
                                                            id="edit-btn-<?php echo $cat['id']; ?>">Edit</a>
                                                        <a href="admin.php?action=delete&id=<?php echo $cat['id']; ?>"
                                                            class="btn btn-danger btn-small"
                                                            id="delete-btn-<?php echo $cat['id']; ?>"
                                                            onclick="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars(addslashes($cat['title'])); ?>\'?');">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column: Editing/Adding Console Form -->
                    <div style="flex: 1; max-width: 540px; border-left: 1px solid var(--border-color); padding-left: 28px; min-width: 0;"
                        id="formColumn">
                        <div class="admin-form-card" id="formCard"
                            style="height: max-content; position: sticky; top: 24px;">
                            <h2 class="admin-form-title" id="formTitle">
                                <?php echo $edit_mode ? 'Edit Category Details' : 'Add New Category'; ?>
                            </h2>

                            <form action="admin.php" method="POST" enctype="multipart/form-data" class="contact-form"
                                id="crudCategoryForm">
                                <!-- Action Hooks -->
                                <input type="hidden" name="action"
                                    value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
                                <?php if ($edit_mode): ?>
                                    <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                                    <input type="hidden" name="existing_image"
                                        value="<?php echo htmlspecialchars($edit_category['image_path']); ?>">
                                <?php endif; ?>

                                <div class="form-group" id="titleGroupInput">
                                    <label for="catTitle" class="form-label">Category Title</label>
                                    <input type="text" id="catTitle" name="title" class="form-input"
                                        placeholder="e.g., Brand Guidelines" required
                                        value="<?php echo $edit_mode ? htmlspecialchars($edit_category['title']) : ''; ?>">
                                </div>

                                <div class="form-group" id="descriptionGroupInput">
                                    <label for="catDesc" class="form-label">Short Description</label>
                                    <textarea id="catDesc" name="description" class="form-input"
                                        style="height: 100px; resize: vertical;"
                                        placeholder="Provide features or services outline..."><?php echo $edit_mode ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                                </div>

                                <div class="form-group" id="imageGroupInput">
                                    <label for="gdLinks" class="form-label">Google Drive Images (One link per
                                        line)</label>
                                    <textarea id="gdLinks" name="drive_links" class="form-input"
                                        style="height: 100px; resize: vertical; margin-bottom: 8px;"
                                        placeholder="Paste Google Drive file links here..."><?php
                                        if ($edit_mode) {
                                            if (!empty($existing_gd_links)) {
                                                echo htmlspecialchars(implode("\n", $existing_gd_links));
                                            } elseif (!empty($edit_category['image_path'])) {
                                                echo htmlspecialchars($edit_category['image_path']);
                                            }
                                        }
                                        ?></textarea>

                                    <div id="gdLinksPreviewContainer"
                                        style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 8px;">
                                    </div>

                                    <?php if ($edit_mode): ?>
                                        <button type="button" class="btn btn-outline btn-small" id="saveDriveLinksBtn"
                                            style="margin-top: 8px;">Save Google Drive Links</button>
                                        <div id="gdLinksStatus"
                                            style="font-size: 13px; margin-top: 4px; color: var(--accent-color);"></div>
                                    <?php else: ?>
                                        <div style="font-size: 12px; color: var(--text-tertiary); margin-top: 4px;">Save
                                            links via edit mode once category is created.</div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group" id="iconGroupInput">
                                    <label for="catIcon" class="form-label">Icon in Thumbnail</label>
                                    <textarea id="catIcon" name="icon_svg" class="form-input"
                                        style="height: 80px; font-family: monospace; font-size: 11.5px; resize: vertical;"
                                        placeholder='<svg>...</svg>'><?php echo $edit_mode ? htmlspecialchars($edit_category['icon_svg']) : ''; ?></textarea>

                                </div>

                                <!-- Price & Discount row -->
                                <div class="form-row" id="priceDiscountRow"
                                    style="grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div class="form-group" id="priceGroupInput">
                                        <label for="catPrice" class="form-label">Price</label>
                                        <input type="text" id="catPrice" name="price" class="form-input"
                                            placeholder="Rs 2,500 or $49"
                                            value="<?php echo $edit_mode ? htmlspecialchars($edit_category['price'] ?? '') : ''; ?>">
                                    </div>
                                    <div class="form-group" id="discountGroupInput">
                                        <label for="catDiscount" class="form-label">Discount</label>
                                        <input type="number" id="catDiscount" name="discount_percent" class="form-input"
                                            placeholder="20" min="0" max="100"
                                            value="<?php echo $edit_mode ? intval($edit_category['discount_percent'] ?? 0) : 0; ?>">
                                    </div>
                                </div>

                                <!-- Delivery & Revisions row -->
                                <div class="form-row" id="deliveryRevisionsRow"
                                    style="grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div class="form-group" id="deliveryGroupInput">
                                        <label for="catDelivery" class="form-label">Delivery Time</label>
                                        <input type="text" id="catDelivery" name="delivery_time" class="form-input"
                                            placeholder="2-3 Days"
                                            value="<?php echo $edit_mode ? htmlspecialchars($edit_category['delivery_time'] ?? '') : ''; ?>">
                                    </div>
                                    <div class="form-group" id="revisionsGroupInput">
                                        <label for="catRevisions" class="form-label">Revisions</label>
                                        <input type="text" id="catRevisions" name="revisions" class="form-input"
                                            placeholder="Unlimited, 2 Iterations"
                                            value="<?php echo $edit_mode ? htmlspecialchars($edit_category['revisions'] ?? '') : ''; ?>">
                                    </div>
                                </div>

                                <!-- Unlimited Info Repeater -->
                                <div class="form-group" id="infoItemsGroup"
                                    style="border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius-md); background: #fafafa;">
                                    <label class="form-label"
                                        style="font-weight: 600; font-size:14px; margin-bottom: 12px; display:block;">Custom
                                        Information Items</label>
                                    <div id="infoItemsRepeater" style="display:flex; flex-direction:column; gap:8px;">
                                    </div>
                                    <button type="button" class="btn btn-outline btn-small" id="addInfoItemBtn"
                                        style="margin-top:12px; width:100%; border-style:dashed;">+ Add Info
                                        Item</button>
                                </div>


                                <!-- Form Actions Control Group -->
                                <div class="form-actions" id="formActionsControl">
                                    <?php if ($edit_mode): ?>
                                        <a href="admin.php" class="btn btn-outline" id="cancelEditBtn">Cancel</a>
                                        <button type="submit" class="btn btn-primary" id="saveEditBtn">Save Changes</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-primary" style="width: 100%;"
                                            id="createCategoryBtn">Create Category</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End Graphic Design Panel -->

        <!-- Panel: Web Development Admin -->
        <div id="web-development-panel"
            style="<?php echo $active_panel === 'web' ? 'display:block;' : 'display:none;'; ?>">
            <h2 class="admin-form-title" style="margin-bottom: 24px;">Manage Web Development Plans</h2>
            <div class="plans-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <?php if (!empty($web_plans)): ?>
                    <?php foreach ($web_plans as $plan):
                        $feats = json_decode($plan['features'], true) ?: [];
                        ?>
                        <div class="admin-form-card" style="margin-bottom: 16px;">
                            <form action="admin.php" method="POST" class="contact-form">
                                <input type="hidden" name="action" value="update_web_plan">
                                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">

                                <h3 style="margin-bottom:16px; font-weight:600; color:var(--text-primary);">Plan
                                    <?php echo $plan['id']; ?> Config
                                </h3>

                                <div class="form-group">
                                    <label class="form-label">Plan Title</label>
                                    <input type="text" name="web_title" class="form-input"
                                        value="<?php echo htmlspecialchars($plan['title']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Plan Summary</label>
                                    <input type="text" name="web_summary" class="form-input"
                                        value="<?php echo htmlspecialchars($plan['summary']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Price Amount</label>
                                    <input type="text" name="web_price" class="form-input"
                                        value="<?php echo htmlspecialchars($plan['price_amount']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Billing Freq</label>
                                    <input type="text" name="web_billing" class="form-input"
                                        value="<?php echo htmlspecialchars($plan['billing_freq']); ?>" required>
                                </div>

                                <div class="form-group"
                                    style="border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius-md); background: #fafafa;">
                                    <label class="form-label"
                                        style="font-weight: 600; margin-bottom: 12px; display:block;">Features List</label>
                                    <div class="web-plan-features-repeater-<?php echo $plan['id']; ?>"
                                        style="display:flex; flex-direction:column; gap:8px;">
                                        <?php foreach ($feats as $idx => $f): ?>
                                            <div style="display:flex; gap:8px; align-items:center;">
                                                <input type="text" name="web_features[]" class="form-input" style="flex:1;"
                                                    value="<?php echo htmlspecialchars($f); ?>">
                                                <button type="button" class="btn btn-danger btn-small"
                                                    onclick="this.parentElement.remove()" style="padding: 6px 10px;">🗑️</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn btn-outline btn-small add-web-feature-btn"
                                        data-target=".web-plan-features-repeater-<?php echo $plan['id']; ?>"
                                        style="margin-top:12px; width:100%; border-style:dashed;">+ Add Feature</button>
                                </div>

                                <button type="submit" class="btn btn-primary" style="width:100%;">Save Plan
                                    <?php echo $plan['id']; ?> Changes</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No web development plans configured.</p>
                <?php endif; ?>
            </div>
        </div> <!-- End Web Development Panel -->

        <!-- ══ Panel: FAQ Management ══════════════════════════════════ -->
        <div id="faq-panel" style="<?php echo $active_panel === 'faq' ? 'display:block;' : 'display:none;'; ?>">

            <!-- Panel header -->
            <div class="admin-header" id="faqPanelHeader">
                <div class="admin-title-group">
                    <h1 style="font-size:28px;font-weight:700;letter-spacing:-0.03em;">FAQ Management</h1>
                    <p style="font-size:14.5px;color:var(--text-secondary);">Manage frequently asked questions for each
                        service category.</p>
                </div>
                <button type="button" class="btn btn-primary" id="openFaqFormBtn" onclick="openFaqForm()">+ Add
                    FAQ</button>
            </div>

            <!-- Category Tabs -->
            <div class="faq-tabs" id="faqTabs">
                <button type="button"
                    class="faq-tab-btn <?php echo $active_faq_tab === 'gd' ? 'faq-tab-active' : ''; ?>" id="tabBtnGd"
                    onclick="switchFaqTab('gd')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
                        <path
                            d="M18.37 2.63 14 7l-1.59-1.59a2 2 0 0 0-2.82 0L8 7l9 9 1.59-1.59a2 2 0 0 0 0-2.82L17 10l4.37-4.37a2.12 2.12 0 1 0-3-3Z" />
                        <path d="M9 8c-2 3-4 3.5-7 4l8 8c1-.5 3.5-2 4-7" />
                        <path d="M14.5 17.5 4.5 15" />
                    </svg>
                    Graphic Design
                    <span class="faq-tab-count"><?php echo count($faqs_gd); ?></span>
                </button>
                <button type="button"
                    class="faq-tab-btn <?php echo $active_faq_tab === 'web' ? 'faq-tab-active' : ''; ?>" id="tabBtnWeb"
                    onclick="switchFaqTab('web')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px;">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <line x1="3" y1="9" x2="21" y2="9" />
                        <line x1="9" y1="21" x2="9" y2="9" />
                    </svg>
                    Web Development
                    <span class="faq-tab-count"><?php echo count($faqs_web); ?></span>
                </button>
            </div>

            <!-- Inline Add / Edit Form (hidden by default) -->
            <div id="faqFormWrap" style="display:none; margin-bottom:24px;">
                <div
                    style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:24px; background:var(--bg-primary);">
                    <h3 id="faqFormHeading"
                        style="font-size:17px; font-weight:600; margin-bottom:20px; letter-spacing:-0.02em;">Add New FAQ
                    </h3>
                    <form action="admin.php" method="POST" class="contact-form" id="faqCrudForm">
                        <input type="hidden" name="action" id="faqActionInput" value="faq_create">
                        <input type="hidden" name="faq_id" id="faqIdInput" value="">
                        <input type="hidden" name="faq_category" id="faqCategoryInput" value="graphic_design">
                        <div class="form-group">
                            <label for="faqQuestion" class="form-label">Question</label>
                            <input type="text" id="faqQuestion" name="faq_question" class="form-input"
                                placeholder="e.g., How long does delivery take?" required>
                        </div>
                        <div class="form-group">
                            <label for="faqAnswer" class="form-label">Answer</label>
                            <textarea id="faqAnswer" name="faq_answer" class="form-input"
                                style="height:100px; resize:vertical;" placeholder="Write a clear, concise answer..."
                                required></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" onclick="closeFaqForm()">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="faqSubmitBtn">Create FAQ</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Graphic Design FAQs List -->
            <div id="faqContentGd" style="<?php echo $active_faq_tab === 'gd' ? 'display:block' : 'display:none'; ?>">
                <?php if (empty($faqs_gd)): ?>
                    <p
                        style="padding:32px; text-align:center; color:var(--text-secondary); border:1px dashed var(--border-color); border-radius:var(--radius-md);">
                        No FAQs yet for Graphic Design. Click "+ Add FAQ" to create the first one.
                    </p>
                <?php else: ?>
                    <div class="faq-list" id="faqListGd">
                        <?php foreach ($faqs_gd as $faq): ?>
                            <div class="faq-item" id="faq-item-<?php echo $faq['id']; ?>">
                                <div class="faq-item-body">
                                    <p class="faq-item-q"><?php echo htmlspecialchars($faq['question']); ?></p>
                                    <p class="faq-item-a"><?php echo htmlspecialchars($faq['answer']); ?></p>
                                </div>
                                <div class="admin-actions faq-item-actions">
                                    <button type="button" class="btn btn-outline btn-small"
                                        onclick="editFaq(<?php echo $faq['id']; ?>, 'graphic_design', <?php echo htmlspecialchars(json_encode($faq['question']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($faq['answer']), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
                                    <a href="admin.php?action=faq_delete&id=<?php echo $faq['id']; ?>&faq_tab=gd"
                                        class="btn btn-danger btn-small"
                                        onclick="return confirm('Delete this FAQ? This cannot be undone.')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Web Development FAQs List -->
            <div id="faqContentWeb" style="<?php echo $active_faq_tab === 'web' ? 'display:block' : 'display:none'; ?>">
                <?php if (empty($faqs_web)): ?>
                    <p
                        style="padding:32px; text-align:center; color:var(--text-secondary); border:1px dashed var(--border-color); border-radius:var(--radius-md);">
                        No FAQs yet for Web Development. Click "+ Add FAQ" to create the first one.
                    </p>
                <?php else: ?>
                    <div class="faq-list" id="faqListWeb">
                        <?php foreach ($faqs_web as $faq): ?>
                            <div class="faq-item" id="faq-item-<?php echo $faq['id']; ?>">
                                <div class="faq-item-body">
                                    <p class="faq-item-q"><?php echo htmlspecialchars($faq['question']); ?></p>
                                    <p class="faq-item-a"><?php echo htmlspecialchars($faq['answer']); ?></p>
                                </div>
                                <div class="admin-actions faq-item-actions">
                                    <button type="button" class="btn btn-outline btn-small"
                                        onclick="editFaq(<?php echo $faq['id']; ?>, 'web_development', <?php echo htmlspecialchars(json_encode($faq['question']), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($faq['answer']), ENT_QUOTES, 'UTF-8'); ?>)">Edit</button>
                                    <a href="admin.php?action=faq_delete&id=<?php echo $faq['id']; ?>&faq_tab=web"
                                        class="btn btn-danger btn-small"
                                        onclick="return confirm('Delete this FAQ? This cannot be undone.')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div> <!-- End FAQ Panel -->

    </div>
</main>

<style>
    /* ── Visibility Toggle Switch ─────────────────────────── */
    .vis-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        cursor: pointer;
    }

    .vis-toggle .vis-checkbox {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }

    .vis-slider {
        position: absolute;
        inset: 0;
        background-color: #e5e7eb;
        border-radius: 9999px;
        transition: background-color 0.25s ease;
    }

    .vis-slider::before {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        background-color: #fff;
        border-radius: 50%;
        transition: transform 0.25s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .vis-checkbox:checked+.vis-slider {
        background-color: #10a37f;
    }

    .vis-checkbox:checked+.vis-slider::before {
        transform: translateX(20px);
    }

    .vis-toggle:hover .vis-slider {
        box-shadow: 0 0 0 3px rgba(16, 163, 127, 0.15);
    }

    /* Row dim when hidden */
    tr.cat-hidden td {
        opacity: 0.45;
    }
</style>

<script>
    document.querySelectorAll('.vis-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', async function () {
            const id = parseInt(this.dataset.id);
            const is_visible = this.checked ? 1 : 0;
            const row = this.closest('tr');

            // Optimistic UI update
            row.classList.toggle('cat-hidden', !this.checked);

            try {
                const res = await fetch('toggle_visibility.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, is_visible })
                });
                const data = await res.json();

                if (!data.success) {
                    // Revert on failure
                    this.checked = !this.checked;
                    row.classList.toggle('cat-hidden', !this.checked);
                    if (window.showToast) window.showToast('Failed to update visibility: ' + (data.message || 'Unknown error'), 'error');
                } else {
                    if (window.showToast) window.showToast(
                        is_visible ? 'Category is now visible to the public.' : 'Category hidden from public.',
                        is_visible ? 'success' : 'error'
                    );
                }
            } catch (err) {
                // Revert on network error
                this.checked = !this.checked;
                row.classList.toggle('cat-hidden', !this.checked);
                if (window.showToast) window.showToast('Network error. Please try again.', 'error');
            }
        });

        // Apply dim class on initial load for hidden items
        if (!checkbox.checked) {
            checkbox.closest('tr').classList.add('cat-hidden');
        }
    });

    // Unified Panel Switcher
    const panelGraphic = document.getElementById('graphic-design-panel');
    const panelWeb = document.getElementById('web-development-panel');
    const panelFaq = document.getElementById('faq-panel');
    const sidebarGraphicLink = document.getElementById('sidebarGraphicLink');
    const sidebarWebLink = document.getElementById('sidebarWebLink');
    const sidebarFaqLink = document.getElementById('sidebarFaqLink');
    const adminPanelHeading = document.getElementById('adminPanelHeading');
    const adminPanelSubheading = document.getElementById('adminPanelSubheading');

    function switchPanel(panel) {
        const allPanels = [panelGraphic, panelWeb, panelFaq];
        const allLinks = [sidebarGraphicLink, sidebarWebLink, sidebarFaqLink];
        allPanels.forEach(p => { if (p) p.style.display = 'none'; });
        allLinks.forEach(l => { if (l) l.classList.remove('active'); });

        if (panel === 'graphic') {
            if (panelGraphic) panelGraphic.style.display = 'block';
            if (sidebarGraphicLink) sidebarGraphicLink.classList.add('active');
            if (adminPanelHeading) adminPanelHeading.textContent = 'Graphic Design';
            if (adminPanelSubheading) adminPanelSubheading.textContent = 'Manage Graphic Design categories and items.';
        } else if (panel === 'faq') {
            if (panelFaq) panelFaq.style.display = 'block';
            if (sidebarFaqLink) sidebarFaqLink.classList.add('active');
            if (adminPanelHeading) adminPanelHeading.textContent = 'FAQ Management';
            if (adminPanelSubheading) adminPanelSubheading.textContent = 'Create and manage FAQs for each service.';
        } else {
            if (panelWeb) panelWeb.style.display = 'block';
            if (sidebarWebLink) sidebarWebLink.classList.add('active');
            if (adminPanelHeading) adminPanelHeading.textContent = 'Web Development';
            if (adminPanelSubheading) adminPanelSubheading.textContent = 'Manage Web Development plans and features.';
        }
    }

    // ── FAQ Tab Switching ───────────────────────────────────────────
    let _currentFaqTab = '<?php echo $active_faq_tab; ?>';

    function switchFaqTab(tab) {
        _currentFaqTab = tab;
        const gdContent = document.getElementById('faqContentGd');
        const webContent = document.getElementById('faqContentWeb');
        const btnGd = document.getElementById('tabBtnGd');
        const btnWeb = document.getElementById('tabBtnWeb');
        const catInput = document.getElementById('faqCategoryInput');

        if (tab === 'gd') {
            if (gdContent) gdContent.style.display = 'block';
            if (webContent) webContent.style.display = 'none';
            if (btnGd) btnGd.classList.add('faq-tab-active');
            if (btnWeb) btnWeb.classList.remove('faq-tab-active');
            if (catInput) catInput.value = 'graphic_design';
        } else {
            if (gdContent) gdContent.style.display = 'none';
            if (webContent) webContent.style.display = 'block';
            if (btnGd) btnGd.classList.remove('faq-tab-active');
            if (btnWeb) btnWeb.classList.add('faq-tab-active');
            if (catInput) catInput.value = 'web_development';
        }
        // close form when switching tabs
        closeFaqForm();
    }

    // ── FAQ Form Helpers ────────────────────────────────────────────
    function openFaqForm() {
        const wrap = document.getElementById('faqFormWrap');
        const heading = document.getElementById('faqFormHeading');
        const submitBtn = document.getElementById('faqSubmitBtn');
        const actionInput = document.getElementById('faqActionInput');
        const idInput = document.getElementById('faqIdInput');
        const qInput = document.getElementById('faqQuestion');
        const aInput = document.getElementById('faqAnswer');
        // Reset to create mode
        if (heading) heading.textContent = 'Add New FAQ';
        if (submitBtn) submitBtn.textContent = 'Create FAQ';
        if (actionInput) actionInput.value = 'faq_create';
        if (idInput) idInput.value = '';
        if (qInput) qInput.value = '';
        if (aInput) aInput.value = '';
        // Set category from current active tab
        const catInput = document.getElementById('faqCategoryInput');
        if (catInput) catInput.value = _currentFaqTab === 'web' ? 'web_development' : 'graphic_design';
        if (wrap) { wrap.style.display = 'block'; wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
    }

    function closeFaqForm() {
        const wrap = document.getElementById('faqFormWrap');
        if (wrap) wrap.style.display = 'none';
    }

    function editFaq(id, category, question, answer) {
        // Switch to matching tab if needed
        const targetTab = category === 'web_development' ? 'web' : 'gd';
        switchFaqTab(targetTab);
        const wrap = document.getElementById('faqFormWrap');
        const heading = document.getElementById('faqFormHeading');
        const submitBtn = document.getElementById('faqSubmitBtn');
        const actionInput = document.getElementById('faqActionInput');
        const idInput = document.getElementById('faqIdInput');
        const catInput = document.getElementById('faqCategoryInput');
        const qInput = document.getElementById('faqQuestion');
        const aInput = document.getElementById('faqAnswer');
        if (heading) heading.textContent = 'Edit FAQ';
        if (submitBtn) submitBtn.textContent = 'Save Changes';
        if (actionInput) actionInput.value = 'faq_update';
        if (idInput) idInput.value = id;
        if (catInput) catInput.value = category;
        if (qInput) qInput.value = question;
        if (aInput) aInput.value = answer;
        if (wrap) { wrap.style.display = 'block'; wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
    }

    // Legacy hidden buttons (kept for any external calls)
    const switchGraphicBtn = document.getElementById('switchGraphicBtn');
    const switchWebBtn = document.getElementById('switchWebBtn');
    if (switchGraphicBtn) switchGraphicBtn.addEventListener('click', () => switchPanel('graphic'));
    if (switchWebBtn) switchWebBtn.addEventListener('click', () => switchPanel('web'));

    // Repeater script for Web Plans Arrays
    document.querySelectorAll('.add-web-feature-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetClass = btn.getAttribute('data-target');
            const container = document.querySelector(targetClass);
            if (container) {
                const div = document.createElement('div');
                div.style.cssText = 'display:flex; gap:8px; align-items:center;';
                div.innerHTML = `
                <input type="text" name="web_features[]" class="form-input" style="flex:1;" value="" placeholder="Feature name...">
                <button type="button" class="btn btn-danger btn-small" onclick="this.parentElement.remove()" style="padding: 6px 10px;">🗑️</button>
            `;
                container.appendChild(div);
            }
        });
    });
</script>

<?php
// Output Toast script if redirect trigger is set in session
if (isset($_SESSION['admin_success'])) {
    $msg = $_SESSION['admin_success'];
    echo "<script>
      document.addEventListener('DOMContentLoaded', () => {
        if(window.showToast) {
          window.showToast(" . json_encode($msg) . ", 'success');
        }
      });
    </script>";
    unset($_SESSION['admin_success']);
}

if (isset($_SESSION['admin_error'])) {
    $msg = $_SESSION['admin_error'];
    echo "<script>
      document.addEventListener('DOMContentLoaded', () => {
        if(window.showToast) {
          window.showToast(" . json_encode($msg) . ", 'error');
        }
      });
    </script>";
    unset($_SESSION['admin_error']);
}

$existing_info_items = [];
if ($edit_mode && !empty($edit_category['info_items'])) {
    $existing_info_items = json_decode($edit_category['info_items'], true) ?: [];
}
?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const repeater = document.getElementById('infoItemsRepeater');
        const addBtn = document.getElementById('addInfoItemBtn');
        let itemCount = 0;

        const existingItems = <?php echo json_encode($existing_info_items); ?>;

        function createRepeaterItem(data = { message: '', link: '' }) {
            const index = itemCount++;
            const mainRow = document.createElement('div');
            mainRow.style.cssText = "display: flex; flex-direction: column; gap: 8px; background: #fff; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--radius-sm);";
            mainRow.dataset.draggable = "main";

            const topRow = document.createElement('div');
            topRow.style.cssText = "display: flex; gap: 8px; align-items: center; width: 100%;";

            const dragHandle = document.createElement('div');
            dragHandle.innerHTML = '⋮⋮';
            dragHandle.style.cssText = "cursor: grab; color: #9ca3af; font-size: 18px; padding: 0 4px;";

            const messageInput = document.createElement('input');
            messageInput.type = 'text';
            messageInput.name = `info_items[${index}][message]`;
            messageInput.className = 'form-input';
            messageInput.placeholder = 'Information text...';
            messageInput.value = data.message || '';
            messageInput.style.flex = "1";

            const toggleLinkBtn = document.createElement('button');
            toggleLinkBtn.type = 'button';
            toggleLinkBtn.className = 'btn btn-outline btn-small';
            toggleLinkBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>';
            toggleLinkBtn.title = 'Link a Google Drive URL';
            toggleLinkBtn.style.padding = "6px 10px";

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'btn btn-danger btn-small';
            deleteBtn.innerHTML = '🗑️';
            deleteBtn.style.padding = "6px 10px";
            deleteBtn.onclick = () => mainRow.remove();

            topRow.appendChild(dragHandle);
            topRow.appendChild(messageInput);
            topRow.appendChild(toggleLinkBtn);
            topRow.appendChild(deleteBtn);

            const linkInputWrap = document.createElement('div');
            linkInputWrap.style.cssText = "display: none; width: 100%; box-sizing: border-box; padding-left: 32px;"; // Align with message input

            const linkInput = document.createElement('input');
            linkInput.type = 'url';
            linkInput.name = `info_items[${index}][link]`;
            linkInput.className = 'form-input';
            linkInput.placeholder = 'Paste Google Drive URL here...';
            linkInput.value = data.link || '';
            linkInput.style.width = "100%";
            linkInput.style.boxSizing = "border-box";
            linkInput.style.fontSize = "13px";

            linkInputWrap.appendChild(linkInput);

            if (data.link && data.link.trim() !== '') {
                linkInputWrap.style.display = 'block';
                toggleLinkBtn.style.background = 'var(--border-color)';
            }

            toggleLinkBtn.onclick = () => {
                if (linkInputWrap.style.display === 'none') {
                    linkInputWrap.style.display = 'block';
                    linkInput.focus();
                    toggleLinkBtn.style.background = 'var(--border-color)';
                } else {
                    linkInputWrap.style.display = 'none';
                    toggleLinkBtn.style.background = '';
                }
            };

            mainRow.appendChild(topRow);
            mainRow.appendChild(linkInputWrap);

            repeater.appendChild(mainRow);
        }

        if (existingItems.length > 0) {
            existingItems.forEach(item => createRepeaterItem(item));
        } else {
            createRepeaterItem(); // Add one default blank row
        }

        addBtn.addEventListener('click', () => createRepeaterItem());

        // Very simple drag logic
        let draggedItem = null;
        repeater.addEventListener('dragstart', (e) => {
            const itemToDrag = e.target.closest('[data-draggable="main"]') || e.target;
            if (itemToDrag.parentNode === repeater) {
                draggedItem = itemToDrag;
                setTimeout(() => draggedItem.style.display = 'none', 0);
            }
        });
        repeater.addEventListener('dragend', () => {
            if (draggedItem) {
                draggedItem.style.display = 'flex';
                draggedItem = null;
            }
        });
        repeater.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterElement = getDragAfterElement(repeater, e.clientY);
            if (afterElement == null) {
                if (draggedItem) repeater.appendChild(draggedItem);
            } else {
                if (draggedItem) repeater.insertBefore(draggedItem, afterElement);
            }
        });
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('div[draggable="true"]:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
        // Make rows draggable
        repeater.addEventListener('mousedown', (e) => {
            if (e.target.innerHTML === '⋮⋮') {
                const mainRow = e.target.closest('[data-draggable="main"]');
                if (mainRow) mainRow.draggable = true;
            }
        });
        repeater.addEventListener('mouseup', (e) => {
            if (e.target.innerHTML === '⋮⋮') {
                const mainRow = e.target.closest('[data-draggable="main"]');
                if (mainRow) mainRow.draggable = false;
            }
        });

        // Google Drive Image Links Logic
        const gdLinksTextarea = document.getElementById('gdLinks');
        const saveDriveLinksBtn = document.getElementById('saveDriveLinksBtn');
        const gdLinksStatus = document.getElementById('gdLinksStatus');
        const previewContainer = document.getElementById('gdLinksPreviewContainer');

        function renderGdPreviews() {
            if (!previewContainer || !gdLinksTextarea) return;
            previewContainer.innerHTML = '';
            const lines = gdLinksTextarea.value.split('\n');
            lines.forEach((line, index) => {
                const originalUrl = line.trim();
                if (originalUrl === '') return;

                let directUrl = originalUrl;
                const match = originalUrl.match(/(?:file\/d\/|id=)([a-zA-Z0-9_-]{25,})/);
                if (match && match[1]) {
                    directUrl = `https://drive.google.com/thumbnail?id=${match[1]}&sz=w1000`;
                }

                const card = document.createElement('div');
                card.style.cssText = 'display: flex; gap: 12px; align-items: center; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 8px; background: #fff;';

                const img = document.createElement('img');
                img.src = directUrl;
                img.style.cssText = 'width: 48px; height: 48px; object-fit: cover; border-radius: 4px; background: var(--bg-secondary); border: 1px solid var(--border-color);';
                // Fallback icon if image fails to load
                img.onerror = () => { img.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%239ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>'; img.style.objectFit = 'none'; };

                const meta = document.createElement('div');
                meta.style.cssText = 'flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center;';

                const linkDisplay = document.createElement('div');
                linkDisplay.textContent = originalUrl;
                linkDisplay.title = originalUrl;
                linkDisplay.style.cssText = 'font-size: 11px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-family: monospace;';

                meta.appendChild(linkDisplay);

                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.style.cssText = 'background: none; border: none; cursor: pointer; color: var(--toast-error); padding: 4px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: background 0.2s;';
                delBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
                delBtn.onmouseover = () => delBtn.style.background = '#fee2e2';
                delBtn.onmouseout = () => delBtn.style.background = 'none';
                delBtn.onclick = () => {
                    const arr = gdLinksTextarea.value.split('\n');
                    arr.splice(index, 1);
                    gdLinksTextarea.value = arr.join('\n');
                    renderGdPreviews(); // Re-render
                };

                card.appendChild(img);
                card.appendChild(meta);
                card.appendChild(delBtn);
                previewContainer.appendChild(card);
            });
        }

        if (gdLinksTextarea) {
            gdLinksTextarea.addEventListener('input', renderGdPreviews);
            gdLinksTextarea.addEventListener('change', renderGdPreviews);
            // Render immediately on edit page load
            renderGdPreviews();
        }

        <?php if ($edit_mode): ?>
            const categoryId = <?php echo $edit_category['id']; ?>;

            // Save links
            if (saveDriveLinksBtn) {
                saveDriveLinksBtn.addEventListener('click', () => {
                    const lines = gdLinksTextarea.value.split('\n').filter(l => l.trim() !== '');
                    const convertedLinks = lines.map(line => {
                        let url = line.trim();
                        const match = url.match(/(?:file\/d\/|id=)([a-zA-Z0-9_-]{25,})/);
                        if (match && match[1]) {
                            return `https://drive.google.com/thumbnail?id=${match[1]}&sz=w1000`;
                        }
                        return url;
                    });

                    gdLinksStatus.textContent = "Saving...";

                    fetch('save_links.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ category_id: categoryId, links: convertedLinks })
                    })
                        .then(res => res.json())
                        .then(data => {
                            gdLinksStatus.textContent = data.message;
                            if (data.success) {
                                gdLinksTextarea.value = convertedLinks.join('\n');
                                if (window.showToast) window.showToast('Links saved successfully', 'success');
                            } else {
                                if (window.showToast) window.showToast(data.message, 'error');
                            }
                        })
                        .catch(err => {
                            gdLinksStatus.textContent = "Error saving links.";
                            console.error(err);
                        });
                });
            }
        <?php endif; ?>
    });
</script>

<?php
include 'includes/footer.php';
?>