<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function getAllProducts($filters = []) {
    $db = Database::getInstance();
    $where = ["p.status = 1"];
    $params = [];

    if (!empty($filters['search'])) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['category_id'])) {
        $cat_ids = [(int)$filters['category_id']];
        $subs = $db->fetchAll("SELECT id FROM categories WHERE parent_id = ? AND status = 1", [$filters['category_id']]);
        foreach ($subs as $s) {
            $cat_ids[] = (int)$s['id'];
        }
        $where[] = "p.category_id IN (" . implode(',', array_fill(0, count($cat_ids), '?')) . ")";
        array_push($params, ...$cat_ids);
    }

    if (!empty($filters['brand_id'])) {
        $where[] = "p.brand_id = ?";
        $params[] = $filters['brand_id'];
    }

    if (!empty($filters['min_price'])) {
        $where[] = "COALESCE(p.discount_price, p.price) >= ?";
        $params[] = $filters['min_price'];
    }

    if (!empty($filters['max_price'])) {
        $where[] = "COALESCE(p.discount_price, p.price) <= ?";
        $params[] = $filters['max_price'];
    }

    if (!empty($filters['min_rating'])) {
        $where[] = "p.rating >= ?";
        $params[] = $filters['min_rating'];
    }

    $where_sql = implode(' AND ', $where);

    $order_by = match ($filters['sort'] ?? 'newest') {
        'price_low'  => 'COALESCE(p.discount_price, p.price) ASC',
        'price_high' => 'COALESCE(p.discount_price, p.price) DESC',
        'rating'     => 'p.rating DESC',
        'popular'    => 'p.reviews_count DESC',
        'name_az'    => 'p.name ASC',
        'name_za'    => 'p.name DESC',
        default      => 'p.created_at DESC',
    };

    $count_sql = "SELECT COUNT(*) AS total FROM products p WHERE $where_sql";
    $count_row = $db->fetch($count_sql, $params);
    $total = $count_row ? (int)$count_row['total'] : 0;

    $per_page = $filters['per_page'] ?? PRODUCTS_PER_PAGE;
    $current_page = $filters['page'] ?? 1;
    $pagination = paginate($total, $per_page, $current_page);

    $sql = "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
            c.name AS category_name, b.name AS brand_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE $where_sql
            ORDER BY $order_by
            LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $pagination['offset'];

    $products = $db->fetchAll($sql, $params);

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return ['products' => $products, 'pagination' => $pagination];
}

function getProductById($id) {
    $db = Database::getInstance();
    $product = $db->fetch(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, c.slug AS category_slug,
                b.name AS brand_name, b.slug AS brand_slug
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.id = ? AND p.status = 1",
        [$id]
    );

    if (!$product) return false;

    $product['images'] = getProductImages($product['id']);
    $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
    if (!empty($image)) {
        $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
    } elseif (empty($product['image_url'])) {
        $product['image_url'] = 'https://placehold.co/400x400?text=Product';
    }

    $specs = $db->fetchAll(
        "SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC",
        [$product['id']]
    );
    $product['specifications'] = $specs;

    return $product;
}

function getProductBySlug($slug) {
    $db = Database::getInstance();
    $product = $db->fetch(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, c.slug AS category_slug,
                b.name AS brand_name, b.slug AS brand_slug
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.slug = ? AND p.status = 1",
        [$slug]
    );

    if (!$product) return false;

    $product['images'] = getProductImages($product['id']);
    $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
    if (!empty($image)) {
        $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
    } elseif (empty($product['image_url'])) {
        $product['image_url'] = 'https://placehold.co/400x400?text=Product';
    }

    $specs = $db->fetchAll(
        "SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC",
        [$product['id']]
    );
    $product['specifications'] = $specs;

    return $product;
}

function getFeaturedProducts($limit = 8) {
    $db = Database::getInstance();
    $products = $db->fetchAll(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, b.name AS brand_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.status = 1 AND p.is_featured = 1
         ORDER BY p.created_at DESC
         LIMIT ?",
        [$limit]
    );

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function getTrendingProducts($limit = 8) {
    $db = Database::getInstance();
    $products = $db->fetchAll(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, b.name AS brand_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.status = 1
         ORDER BY p.reviews_count DESC, p.rating DESC
         LIMIT ?",
        [$limit]
    );

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function getBestSellerProducts($limit = 8) {
    $db = Database::getInstance();
    $products = $db->fetchAll(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, b.name AS brand_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.status = 1 AND p.reviews_count > 0
         ORDER BY p.reviews_count DESC
         LIMIT ?",
        [$limit]
    );

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function getFlashSaleProducts($limit = 8) {
    $db = Database::getInstance();
    try {
        $products = $db->fetchAll(
            "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                    c.name AS category_name, b.name AS brand_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             LEFT JOIN brands b ON p.brand_id = b.id
             WHERE p.status = 1 AND p.is_flash_sale = 1
               AND p.created_at <= NOW()
             ORDER BY p.updated_at DESC
             LIMIT ?",
            [$limit]
        );
    } catch (Exception $e) {
        $products = [];
    }

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function getRelatedProducts($product_id, $category_id, $limit = 4) {
    $db = Database::getInstance();
    $products = $db->fetchAll(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, b.name AS brand_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.status = 1 AND p.category_id = ? AND p.id != ?
         ORDER BY RAND()
         LIMIT ?",
        [$category_id, $product_id, $limit]
    );

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function getNewArrivals($limit = 8) {
    $db = Database::getInstance();
    $products = $db->fetchAll(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                c.name AS category_name, b.name AS brand_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.status = 1
         ORDER BY p.created_at DESC
         LIMIT ?",
        [$limit]
    );

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function getProductReviews($product_id) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT r.*, u.name AS user_name
         FROM reviews r
         LEFT JOIN users u ON r.user_id = u.id
         WHERE r.product_id = ? AND r.status = 1
         ORDER BY r.created_at DESC",
        [$product_id]
    );
}

function getCategories() {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT c.*,
          (SELECT COUNT(*) FROM products p
           WHERE p.status = 1
           AND (p.category_id = c.id OR p.category_id IN (SELECT id FROM categories WHERE parent_id = c.id AND status = 1))
          ) AS product_count
         FROM categories c
         WHERE c.status = 1
         ORDER BY c.name ASC"
    );
}

function getBrands() {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT b.*, COUNT(p.id) AS product_count
         FROM brands b
         LEFT JOIN products p ON b.id = p.brand_id AND p.status = 1
         WHERE b.status = 1
         GROUP BY b.id
         ORDER BY b.name ASC"
    );
}

function searchProducts($query) {
    $db = Database::getInstance();
    $q = '%' . trim($query) . '%';
    $products = $db->fetchAll(
        "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image,
                c.name AS category_name, b.name AS brand_name
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN brands b ON p.brand_id = b.id
         WHERE p.status = 1
           AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?
                OR c.name LIKE ? OR b.name LIKE ?)
         ORDER BY p.reviews_count DESC, p.rating DESC
         LIMIT 20",
        [$q, $q, $q, $q, $q]
    );

    foreach ($products as &$product) {
        $product['primary_image'] = $product['image'] ?? '';
        if (!empty($product['image'])) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $product['image'];
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function aiSearchProducts($query) {
    $query = trim($query);
    if (empty($query)) return [];

    $keywords = extractSearchKeywords($query);

    $db = Database::getInstance();
    $conditions = [];
    $params = [];

    $conditions[] = "p.status = 1";
    $conditions[] = "(";

    $or_clauses = [];
    foreach ($keywords as $keyword) {
        $like = '%' . $keyword . '%';
        $or_clauses[] = "p.name LIKE ?";
        $params[] = $like;
        $or_clauses[] = "p.description LIKE ?";
        $params[] = $like;
        $or_clauses[] = "p.short_description LIKE ?";
        $params[] = $like;
    }

    $conditions[] = implode(' OR ', $or_clauses) . ")";
    $params[] = $query;
    $params[] = '%' . $query . '%';

    $where = implode(' AND ', $conditions);

    $sql = "SELECT p.*, COALESCE(p.discount_price, p.price) AS sale_price, p.quantity AS stock,
                   REPLACE((SELECT image FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), 'products/', '') AS image,
                   c.name AS category_name, b.name AS brand_name,
                   (CASE
                       WHEN p.name LIKE ? THEN 10
                       WHEN p.name LIKE ? THEN 5
                       WHEN p.description LIKE ? THEN 3
                       ELSE 1
                   END) AS relevance
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE $where
            ORDER BY relevance DESC, p.reviews_count DESC
            LIMIT 20";

    $all_params = array_merge(['%' . $query . '%', '%' . $query . '%', '%' . $query . '%'], $params);
    $products = $db->fetchAll($sql, $all_params);

    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $image = !empty($product['images']) ? ($product['images'][0]['image'] ?? '') : '';
        $image = preg_replace('#^products/#', '', $image);
        $product['primary_image'] = $image;
        $product['image'] = $image;
        if (!empty($image)) {
            $product['image_url'] = BASE_URL . 'assets/uploads/products/' . $image;
        } elseif (empty($product['image_url'])) {
            $product['image_url'] = 'https://placehold.co/400x400?text=Product';
        }
    }

    return $products;
}

function extractSearchKeywords($query) {
    $stop_words = [
        'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'is', 'it', 'this', 'that', 'are', 'was',
        'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does',
        'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can',
        'shall', 'i', 'me', 'my', 'we', 'our', 'you', 'your', 'he', 'she',
        'they', 'them', 'his', 'her', 'its', 'our', 'my', 'your', 'which',
        'what', 'where', 'when', 'who', 'how', 'not', 'no', 'nor', 'so',
        'too', 'very', 'just', 'about', 'above', 'after', 'again', 'all',
        'also', 'any', 'because', 'before', 'between', 'both', 'each',
        'few', 'more', 'most', 'other', 'some', 'such', 'than', 'then',
        'there', 'these', 'those', 'through', 'under', 'until', 'up',
        'during', 'out', 'off', 'over', 'own', 'same', 'into', 'only'
    ];

    $clean = strtolower($query);
    $clean = preg_replace('/[^a-z0-9\s]/', ' ', $clean);
    $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);

    $keywords = [];
    foreach ($words as $word) {
        if (strlen($word) > 1 && !in_array($word, $stop_words)) {
            $keywords[] = $word;
        }
    }

    if (empty($keywords)) {
        $keywords[] = $query;
    }

    return $keywords;
}
