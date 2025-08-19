# AI SEO Optimizer - REST API Documentation

## Overview

The AI SEO Optimizer provides a comprehensive REST API for integrating with WordPress and Shopify platforms. All endpoints follow RESTful conventions and return JSON responses.

## Base URLs

- **WordPress Plugin**: `https://your-site.com/wp-json/ai-seo/v1`
- **Shopify Integration**: `https://your-shopify-store.myshopify.com/admin/api/2024-01`

## Authentication

### WordPress API
All WordPress endpoints require API key authentication:
```
X-API-Key: your_api_key_here
X-Customer-ID: your_customer_id_here
```

### Shopify API
Shopify endpoints use access token authentication:
```
X-Shopify-Access-Token: your_access_token_here
```

---

## WordPress Plugin API Endpoints

### 1. Dashboard Data
**GET** `/dashboard-data`

Returns comprehensive dashboard data including SEO scores, recent changes, and system status.

**Response:**
```json
{
  "seo_score": 87,
  "total_pages": 25,
  "recent_changes": [
    {
      "id": 1,
      "type": "meta_title",
      "page": "Homepage",
      "timestamp": "2024-01-15 10:30:00",
      "status": "applied"
    }
  ],
  "system_status": {
    "wordpress_version": "6.4.2",
    "plugin_version": "1.0.0",
    "api_status": "active"
  }
}
```

### 2. Site Scan
**GET** `/scan`

Initiates a comprehensive SEO scan of the website.

**Parameters:**
- `depth` (optional): Scan depth (1-5, default: 3)
- `include_images` (optional): Include image analysis (true/false, default: true)

**Response:**
```json
{
  "scan_id": "scan_12345",
  "status": "completed",
  "pages_scanned": 25,
  "issues_found": 12,
  "recommendations": [
    {
      "id": "rec_001",
      "type": "meta_description",
      "priority": "high",
      "title": "Optimize Meta Description",
      "description": "Meta description is too short",
      "impact_score": 8,
      "confidence": 0.95
    }
  ]
}
```

### 3. Apply Changes
**POST** `/apply-changes`

Applies approved SEO changes to the website.

**Request Body:**
```json
{
  "changes": [
    {
      "id": "rec_001",
      "type": "meta_description",
      "page_id": 123,
      "new_value": "Optimized meta description"
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "applied_changes": 1,
  "failed_changes": 0,
  "backup_id": "backup_67890"
}
```

### 4. Get Logs
**GET** `/logs`

Retrieves audit logs and system activity.

**Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)
- `type` (optional): Filter by log type

**Response:**
```json
{
  "logs": [
    {
      "id": 1,
      "timestamp": "2024-01-15 10:30:00",
      "action": "seo_scan",
      "details": "Completed SEO scan",
      "user": "admin"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_items": 100
  }
}
```

### 5. Health Check
**GET** `/health`

Returns system health status and configuration.

**Response:**
```json
{
  "status": "healthy",
  "version": "1.0.0",
  "wordpress_version": "6.4.2",
  "php_version": "8.1.0",
  "database_status": "connected",
  "api_status": "active",
  "last_scan": "2024-01-15 10:30:00"
}
```

---

## Shopify Integration API Endpoints

### 1. Store Connection Test
**GET** `/admin/api/2024-01/shop.json`

Tests connection to Shopify store and returns store information.

**Response:**
```json
{
  "shop": {
    "id": 123456789,
    "name": "My Store",
    "domain": "my-store.myshopify.com",
    "email": "admin@mystore.com",
    "plan_name": "Basic Shopify",
    "currency": "USD"
  }
}
```

### 2. Get Products
**GET** `/admin/api/2024-01/products.json`

Retrieves products for SEO analysis.

**Parameters:**
- `limit` (optional): Number of products (default: 250)
- `fields` (optional): Specific fields to return

**Response:**
```json
{
  "products": [
    {
      "id": 123456789,
      "title": "Product Name",
      "body_html": "Product description",
      "handle": "product-name",
      "seo_title": "SEO Title",
      "seo_description": "SEO Description",
      "tags": "tag1, tag2",
      "vendor": "Vendor Name",
      "product_type": "Electronics",
      "images": [
        {
          "id": 987654321,
          "src": "https://cdn.shopify.com/image.jpg",
          "alt": "Product image",
          "width": 800,
          "height": 600
        }
      ]
    }
  ]
}
```

### 3. Update Product SEO
**PUT** `/admin/api/2024-01/products/{product_id}.json`

Updates product SEO data.

**Request Body:**
```json
{
  "product": {
    "id": 123456789,
    "seo_title": "Optimized SEO Title",
    "seo_description": "Optimized SEO description"
  }
}
```

**Response:**
```json
{
  "product": {
    "id": 123456789,
    "seo_title": "Optimized SEO Title",
    "seo_description": "Optimized SEO description",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

### 4. Get Pages
**GET** `/admin/api/2024-01/pages.json`

Retrieves pages for SEO analysis.

**Response:**
```json
{
  "pages": [
    {
      "id": 123456789,
      "title": "Page Title",
      "body_html": "Page content",
      "handle": "page-title",
      "seo_title": "SEO Title",
      "seo_description": "SEO Description"
    }
  ]
}
```

### 5. Update Page SEO
**PUT** `/admin/api/2024-01/pages/{page_id}.json`

Updates page SEO data.

**Request Body:**
```json
{
  "page": {
    "id": 123456789,
    "seo_title": "Optimized SEO Title",
    "seo_description": "Optimized SEO description"
  }
}
```

### 6. Get Collections
**GET** `/admin/api/2024-01/collections.json`

Retrieves collections for SEO analysis.

**Response:**
```json
{
  "collections": [
    {
      "id": 123456789,
      "title": "Collection Title",
      "body_html": "Collection description",
      "handle": "collection-title",
      "seo_title": "SEO Title",
      "seo_description": "SEO Description"
    }
  ]
}
```

---

## AJAX Endpoints (WordPress Admin)

### 1. Connect Shopify Store
**POST** `wp-admin/admin-ajax.php`

**Action:** `ai_seo_shopify_connect`

**Request:**
```json
{
  "action": "ai_seo_shopify_connect",
  "nonce": "security_nonce",
  "store_url": "my-store.myshopify.com",
  "access_token": "shpat_xxxxxxxxxxxxx"
}
```

### 2. Analyze Shopify Store
**POST** `wp-admin/admin-ajax.php`

**Action:** `ai_seo_shopify_analyze`

**Request:**
```json
{
  "action": "ai_seo_shopify_analyze",
  "nonce": "security_nonce"
}
```

### 3. Update Shopify SEO
**POST** `wp-admin/admin-ajax.php`

**Action:** `ai_seo_shopify_update`

**Request:**
```json
{
  "action": "ai_seo_shopify_update",
  "nonce": "security_nonce",
  "target_type": "product",
  "target_id": 123456789,
  "seo_title": "New SEO Title",
  "seo_description": "New SEO Description"
}
```

---

## Error Responses

All endpoints return consistent error responses:

```json
{
  "error": true,
  "message": "Error description",
  "code": "ERROR_CODE",
  "details": {
    "field": "Additional error details"
  }
}
```

Common error codes:
- `401`: Unauthorized (invalid API key)
- `403`: Forbidden (insufficient permissions)
- `404`: Not found
- `422`: Validation error
- `500`: Internal server error

---

## Rate Limiting

- **WordPress API**: 100 requests per minute
- **Shopify API**: 2 requests per second (Shopify's standard limit)

---

## SDK Examples

### JavaScript (Dashboard)
```javascript
// Get dashboard data
const response = await fetch('/wp-json/ai-seo/v1/dashboard-data', {
  headers: {
    'X-API-Key': 'your_api_key',
    'X-Customer-ID': 'your_customer_id'
  }
});
const data = await response.json();
```

### PHP (WordPress)
```php
// Apply SEO changes
$response = wp_remote_post('/wp-json/ai-seo/v1/apply-changes', [
  'headers' => [
    'X-API-Key' => 'your_api_key',
    'Content-Type' => 'application/json'
  ],
  'body' => json_encode([
    'changes' => [
      [
        'id' => 'rec_001',
        'type' => 'meta_description',
        'page_id' => 123,
        'new_value' => 'Optimized description'
      ]
    ]
  ])
]);
```

### cURL (Shopify)
```bash
# Get products
curl -H "X-Shopify-Access-Token: your_token" \
     "https://your-store.myshopify.com/admin/api/2024-01/products.json"
```

---

## Support

For API support or questions:
- Check the WordPress admin dashboard for connection status
- Review error logs in the plugin settings
- Ensure proper authentication credentials are configured
