# 📦 Products & Inventory Management

*A practical guide for managing electronics and accessories products on your ecommerce platform.*

---

## 1️⃣ What is a **Product**?

A **Product** is the core entity users can buy in your catalog.  
Every product is connected to several foundational elements:

<div style="background: #F3F4F6; padding: 1em; border-radius: 6px;">
<strong>Key Product Relationships:</strong>
<ul>
  <li><strong>Category</strong></li>
  <li><strong>Brand</strong></li>
  <li><strong>Attribute Family</strong></li>
  <li><strong>Attribute Values</strong> <span style="color: #6B7280;">(Specifications)</span></li>
  <li><strong>Images</strong> <span style="color: #6B7280;">(managed by Spatie Media Library)</span></li>
  <li><strong>Stock &amp; Pricing</strong></li>
</ul>
</div>

> **Examples:**  
> iPhone 14 256GB, Samsung 55" UHD TV, Logitech MX Master 3S Mouse, JBL Charge 5 Speaker

---

### 🛠 Product Components

- Name, SKU, brief and detailed description
- Pricing and inventory management
- Specifications via attribute values
- Image/gallery support
- <span style="color: #6B7280;">(Optional)</span> Variants for multiple SKUs

---

## 2️⃣ Product Table Structure

All essential product data lives in the `products` table.

<div style="background: #F3F4F6; padding: 1em; border-radius: 6px;">
<strong>Main Fields:</strong>
<ul>
  <li><code>name</code> – Product name</li>
  <li><code>slug</code> – URL-friendly identifier</li>
  <li><code>sku</code> – Stock Keeping Unit</li>
  <li><code>brand_id</code> – Brand reference</li>
  <li><code>category_id</code> – Category reference</li>
  <li><code>attribute_family_id</code> – Defines relevant attributes</li>
  <li><code>short_description</code> – Concise summary</li>
  <li><code>description</code> – Full overview</li>
  <li><code>price</code> – Sale price</li>
  <li><code>compare_price</code> – List/prev price</li>
  <li><code>cost</code> – <span style="color: #6B7280;">(Optional)</span> Internal cost</li>
  <li><code>quantity</code> – Inventory (not for variants)</li>
  <li><code>low_stock_threshold</code> – <span style="color: #6B7280;">(Optional)</span> Stock alert level</li>
  <li><code>status</code> – Published or draft</li>
  <li><code>is_featured</code> – <span style="color: #6B7280;">(Optional)</span> Featured flag</li>
</ul>
</div>

---

## 3️⃣ Product Images & Media Library

Product images are handled via **Spatie Media Library**.  
No need for custom image tables—simply use assigned media collections.

<div style="background: #F9FAFB; padding: 1em; border-radius: 6px;">
<strong>Benefits:</strong>
<ul>
  <li>Organized collections (like <code>images</code>)</li>
  <li>File storage & optimized thumbnails</li>
  <li>Media sorting and association</li>
  <li>Variants may have separate galleries</li>
</ul>

<strong>Tip:</strong> Use Filament’s <code>SpatieMediaLibraryFileUpload</code> for easy multi-file uploads.
</div>

---

## 4️⃣ Product Attribute Values (Specifications)

Attributes (see: <strong>Attribute Family</strong>) give products detailed specs shown in listings and filters.

<div style="background: #F3F4F6; padding: 1em; border-radius: 6px;">
<strong>Attribute Value Structure:</strong>
<ul>
  <li><code>product_id</code> – Product link</li>
  <li><code>attribute_id</code> – Attribute link</li>
  <li><code>value</code> – Specific specification</li>
</ul>
</div>

**Sample (Smartphone):**
<ul>
  <li>Color: Blue</li>
  <li>Storage: 256GB</li>
  <li>RAM: 8GB</li>
  <li>Battery: 5000mAh</li>
  <li>Screen Size: 6.1"</li>
  <li>Operating System: Android</li>
</ul>

> **Uses:**  
> • Specs tab  
> • Filtering & comparison  
> • Product SEO (structured data)

---

## 5️⃣ Attributes vs. Product Variants

<table>
  <thead>
    <tr>
      <th></th>
      <th>Attributes</th>
      <th>Variants</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Purpose</strong></td>
      <td>Describe/specify product</td>
      <td>Purchasable options (multi-SKU)</td>
    </tr>
    <tr>
      <td><strong>SKU/Stock/Price?</strong></td>
      <td>✗</td>
      <td>✓</td>
    </tr>
    <tr>
      <td><strong>Add-to-Cart?</strong></td>
      <td>✗</td>
      <td>✓ (choose at purchase)</td>
    </tr>
    <tr>
      <td><strong>Specs Tab?</strong></td>
      <td>✓</td>
      <td>✗</td>
    </tr>
    <tr>
      <td><strong>Stock Managed?</strong></td>
      <td>✗</td>
      <td>✓</td>
    </tr>
  </tbody>
</table>

<div style="background: #EFF6FF; padding: 1em; border-radius: 6px;">
<strong>Definitions:</strong>
<ul>
  <li><strong>Attributes</strong> – For product detail/filtering. Does <u>not</u> create new purchasable options.</li>
  <li><strong>Variants</strong> – Enable customer choice (color, storage, etc.), each with distinct stock, SKU, and price if needed.</li>
</ul>
</div>

**Example: Samsung Galaxy A55 Variant Matrix**

| Color | Storage | Quantity |
|-------|---------|----------|
| Black | 128GB   | 12       |
| Black | 256GB   | 8        |
| Blue  | 128GB   | 5        |
| Blue  | 256GB   | 6        |

---

## 6️⃣ Inventory Management Approaches

Product type sets how inventory is tracked:

### 🟦 Inventory at Product Level (Simple)

- Stock tracked in `products.quantity`.
- Actions:
  <ul>
    <li>Decrease on sale</li>
    <li>Increase on return/restock</li>
    <li>Warn at <code>low_stock_threshold</code></li>
  </ul>
  <span style="color: #6B7280;">Example: JBL Charge 5 – Quantity: 25</span>

### 🟧 Inventory at Variant Level

Each variant handles its own:
<ul>
  <li><code>sku</code></li>
  <li>Price override <span style="color: #6B7280;">(optional)</span></li>
  <li>Quantity</li>
</ul>

Actions:
<ul>
  <li>Decrease purchased variant's stock</li>
  <li>Restock specific variant</li>
  <li>Show variant-specific images (if set)</li>
</ul>

### 🟩 Attributes & Inventory

<div style="background: #FFE4E6; padding: 0.75em; border-radius: 5px;">
<strong>Note:</strong> <u>You cannot track stock at the attribute level</u>. Inventory always belongs to the product or one of its variants.
</div>

### 🟦 Spatie Media & Inventory

Media collection is managed apart and can visualize stock or variant details.

---

## 7️⃣ Product Creation: Admin Workflow

<ol>
  <li>Select <strong>Category</strong></li>
  <li>Choose <strong>Brand</strong></li>
  <li>Pick <strong>Attribute Family</strong></li>
  <li>Fill in <strong>Specifications</strong> (auto-generated)</li>
  <li>Upload <strong>Images</strong> (Spatie Media Library)</li>
  <li>Configure <strong>Pricing & Inventory</strong> (single/variant)</li>
  <li><strong>Publish</strong> the product listing</li>
</ol>

---

## 💡 Summary

<div style="background: #F3F4F6; padding: 1em; border-radius: 6px;">
<ul>
  <li><strong>Product</strong>: What you sell</li>
  <li><strong>Attributes</strong>: Specification fields (via attribute family)</li>
  <li><strong>Attribute Family</strong>: Decides which specs apply</li>
  <li><strong>Attribute Values</strong>: Actual product details</li>
  <li><strong>Spatie Media Library</strong>: Centralized image management</li>
  <li><strong>Variants</strong>: (Optional) Multi-SKU support</li>
  <li><strong>Inventory</strong>: Managed per product or variant</li>
</ul>
</div>

This structure supports a clear, scalable, and customer-friendly electronics & accessories catalog.
