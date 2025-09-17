<?php
session_start();

// Part 1: Initialize products array with at least two sample products
$products = [
    [
        'id' => 1,
        'name' => 'Laptop Pro 15',
        'description' => 'High performance laptop for work and gaming',
        'price' => 1499.99,
        'category' => 'Electronics'
    ],
    [
        'id' => 2,
        'name' => 'Classic Notebook',
        'description' => '200 pages ruled notebook',
        'price' => 4.5,
        'category' => 'Books'
    ]
];

$categories = ['Electronics', 'Clothing', 'Books', 'Home'];

// Part 2: Form handling
$errors = [];
$submittedData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        
        $submittedData = $_POST;

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priceRaw = trim($_POST['price'] ?? '');
        $category = trim($_POST['category'] ?? '');

        // Validate name
        if ($name === '') {
            $errors['name'] = 'اسم المنتج مطلوب.';
        } elseif (mb_strlen($name) < 2) {
            $errors['name'] = 'اسم المنتج يجب أن يكون 2 أحرف على الأقل.';
        }

        // Validate description
        if ($description === '') {
            $errors['description'] = 'الوصف مطلوب.';
        } elseif (mb_strlen($description) < 5) {
            $errors['description'] = 'الوصف قصير جداً.';
        }

        // Validate price
        if ($priceRaw === '') {
            $errors['price'] = 'السعر مطلوب.';
        } else {
            // allow decimals
            $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
            if ($price === false || $price < 0) {
                $errors['price'] = 'السعر يجب أن يكون رقم موجب.';
            }
        }

        // Validate category
        if ($category === '') {
            $errors['category'] = 'التصنيف مطلوب.';
        } elseif (!in_array($category, $categories)) {
            $errors['category'] = 'التصنيف غير صالح.';
        }

        if (empty($errors)) {

            $ids = array_column($products, 'id');
            $newId = empty($ids) ? 1 : (max($ids) + 1);

            $newProduct = [
                'id' => $newId,
                'name' => $name,
                'description' => $description,
                'price' => (float) $price,
                'category' => $category
            ];

            $products[] = $newProduct;

            $_SESSION['success'] = 'تمت إضافة المنتج بنجاح.';

            // Clear submitted data so the form resets
            $submittedData = [];
        }
    }
}

// Helper for escaping
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

?>

<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إدارة المنتجات - PHP Inventory</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="h3">نظام إدارة المنتجات</h1>
            <p class="text-muted">أضِف، وعرض المنتجات مع التحقق من صحة المدخلات وعرض تنبيهات Bootstrap.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Messages -->
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">هناك أخطاء في النموذج. الرجاء تصحيحها ثم المحاولة مرة أخرى.</div>
            <?php endif; ?>

            <!-- Add Product Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">إضافة منتج جديد</h5>
                    <form method="post" novalidate>
                        <input type="hidden" name="action" value="add">

                        <div class="mb-3">
                            <label class="form-label">اسم المنتج</label>
                            <input type="text" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo e($submittedData['name'] ?? ''); ?>">
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">الوصف</label>
                            <textarea name="description" rows="3" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo e($submittedData['description'] ?? ''); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="invalid-feedback"><?php echo e($errors['description']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">السعر (USD)</label>
                            <input type="number" step="0.01" name="price" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo e($submittedData['price'] ?? ''); ?>">
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?php echo e($errors['price']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">التصنيف</label>
                            <select name="category" class="form-select <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>">
                                <option value="">-- اختر تصنيف --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo e($cat); ?>" <?php echo (isset($submittedData['category']) && $submittedData['category'] === $cat) ? 'selected' : ''; ?>><?php echo e($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category'])): ?>
                                <div class="invalid-feedback"><?php echo e($errors['category']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary">أضف المنتج</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <div class="col-lg-6">
            <!-- Product Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">قائمة المنتجات</h5>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>الوصف</th>
                                <th>التصنيف</th>
                                <th class="text-end">السعر (USD)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><?php echo e($p['id']); ?></td>
                                    <td><?php echo e($p['name']); ?></td>
                                    <td><?php echo e($p['description']); ?></td>
                                    <td><?php echo e($p['category']); ?></td>
                                    <td class="text-end"><?php echo number_format((float)$p['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS CDN (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
