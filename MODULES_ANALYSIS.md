# Comprehensive Modules Analysis - SKRIPSIS8 System

**Project Type**: Restaurant/Food Management System  
**Architecture**: MVC-like with Module-based Structure  
**Database**: MySQL with mysqli (prepared statements)  
**Framework**: Vanilla PHP with HTML/CSS/JavaScript

---

## Overview

The `modules/` folder contains 14 core business logic modules organized by entity. Each module follows a standard CRUD pattern with the following file types:
- `entity_tambah.php` - Create form + list display
- `entity_edit.php` - Fetch and display edit form
- `entity_update.php` - Process POST update
- `entity_delete.php` - Process DELETE via GET
- `entity_list.php` - Dedicated list view (mostly template-only)
- `entity_save.php` - Alternative save (minimal usage)
- `entity_proses.php` - Process/handler files

---

## Module Details

### 1. **BAHAN Module** (Ingredients/Raw Materials)
**Purpose**: Manage food ingredients/raw materials  
**Files**: 4 files (delete, edit, tambah, update)

**Database Table**: `bahan`
- `BahanNo` (PK, auto-increment)
- `nama_bahan` (VARCHAR, unique ingredient name)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `bahan_tambah.php` | POST | `nama_bahan` | INSERT INTO bahan (nama_bahan) VALUES (?) |
| READ | `bahan_tambah.php` | GET | - | SELECT BahanNo, nama_bahan FROM bahan ORDER BY BahanNo DESC |
| READ (Single) | `bahan_edit.php` | GET | `BahanNo` | SELECT BahanNo, nama_bahan FROM bahan WHERE BahanNo = ? LIMIT 1 |
| UPDATE | `bahan_update.php` | POST | `BahanNo`, `nama_bahan` | UPDATE bahan SET nama_bahan = ? WHERE BahanNo = ? |
| DELETE | `bahan_delete.php` | GET | `BahanNo` | DELETE FROM bahan WHERE BahanNo = ? |

**Data Flow**:
```
bahan_tambah.php (form + list) 
  ↓ INSERT/GET
  ↓ bahan_edit.php (fetch single)
  ↓ bahan_update.php (process update)
  ↓ bahan_delete.php (process delete)
  ↓ Redirect back to bahan_tambah.php
```

**Relationships**: 
- ← Referenced by: `stok` (BahanNo FK), `detail_resep` (via stok)

---

### 2. **SATUAN Module** (Units of Measurement)
**Purpose**: Define measurement units (gram, kg, ml, liter, pcs, etc.)  
**Files**: 4 files (delete, edit, tambah, update)

**Database Table**: `satuan`
- `SatuanNo` (PK, auto-increment)
- `nama_satuan` (VARCHAR, UNIQUE)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `satuan_tambah.php` | POST | `nama_satuan` | INSERT INTO satuan (nama_satuan) VALUES (?) |
| READ | `satuan_tambah.php` | GET | - | SELECT SatuanNo, nama_satuan FROM satuan ORDER BY SatuanNo ASC |
| READ (Single) | `satuan_edit.php` | GET | `SatuanNo` | SELECT SatuanNo, nama_satuan FROM satuan WHERE SatuanNo = ? LIMIT 1 |
| UPDATE | `satuan_update.php` | POST | `SatuanNo`, `nama_satuan` | UPDATE satuan SET nama_satuan = ? WHERE SatuanNo = ? |
| DELETE | `satuan_delete.php` | GET | `SatuanNo` | DELETE FROM satuan WHERE SatuanNo = ? |

**Relationships**:
- ← Referenced by: `stok` (SatuanNo FK), `detail_resep` (SatuanNo FK)

---

### 3. **MENU Module** (Menu Items)
**Purpose**: Manage menu items/dishes  
**Files**: 5 files (delete, edit, list [empty], tambah, update)

**Database Table**: `menu`
- `MenuNo` (PK, auto-increment)
- `nama_menu` (VARCHAR, menu item name)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `menu_tambah.php` | POST | `nama_menu` | INSERT INTO menu (nama_menu) VALUES (?) |
| READ | `menu_tambah.php` | GET | - | SELECT MenuNo, nama_menu FROM menu ORDER BY MenuNo DESC |
| READ (Single) | `menu_edit.php` | GET | `MenuNo` | SELECT MenuNo, nama_menu FROM menu WHERE MenuNo = ? LIMIT 1 |
| UPDATE | `menu_update.php` | POST | `MenuNo`, `nama_menu` | UPDATE menu SET nama_menu = ? WHERE MenuNo = ? |
| DELETE | `menu_delete.php` | GET | `MenuNo` | DELETE FROM menu WHERE MenuNo = ? |

**Relationships**:
- ← Referenced by: `resep` (MenuNo FK), `varian_menu` (MenuNo FK)

---

### 4. **VARIAN_MENU Module** (Menu Variants - Sizes & Prices)
**Purpose**: Define variations of menu items (Small/Medium/Large with different prices)  
**Files**: 5 files (delete, edit, list [empty], tambah, update)

**Database Table**: `varian_menu`
- `VarianMenuNo` (PK, auto-increment)
- `MenuNo` (FK → menu.MenuNo)
- `nama_ukuran` (VARCHAR, size name: Small, Medium, Large)
- `Harga` (DECIMAL, price)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `varian_tambah.php` | POST | `MenuNo`, `nama_ukuran`, `Harga` | INSERT INTO varian_menu (MenuNo, nama_ukuran, Harga) VALUES (?, ?, ?) |
| READ | `varian_tambah.php` | GET | - | SELECT vm.VarianMenuNo, vm.MenuNo, m.nama_menu, vm.nama_ukuran, vm.Harga FROM varian_menu vm LEFT JOIN menu m ON vm.MenuNo = m.MenuNo ORDER BY vm.VarianMenuNo DESC |
| READ (Single) | `varian_edit.php` | GET | `VarianMenuNo` | SELECT VarianMenuNo, MenuNo, nama_ukuran, Harga FROM varian_menu WHERE VarianMenuNo = ? LIMIT 1 |
| UPDATE | `varian_update.php` | POST | `VarianMenuNo`, `MenuNo`, `nama_ukuran`, `Harga` | UPDATE varian_menu SET MenuNo = ?, nama_ukuran = ?, Harga = ? WHERE VarianMenuNo = ? |
| DELETE | `varian_delete.php` | GET | `VarianMenuNo` | DELETE FROM varian_menu WHERE VarianMenuNo = ? |

**Relationships**:
- → Depends on: `menu` (MenuNo FK)
- ← Referenced by: `detail_transaksi` (VarianMenuNo FK)

---

### 5. **RESEP Module** (Recipes)
**Purpose**: Create recipes linking menu items to ingredients  
**Files**: 5 files (delete, edit, save [empty], tambah, update)

**Database Table**: `resep`
- `ResepNo` (PK, auto-increment)
- `MenuNo` (FK → menu.MenuNo)
- `Keterangan` (TEXT, recipe description/instructions)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `resep_tambah.php` | POST | `MenuNo`, `keterangan` | INSERT INTO resep (MenuNo, Keterangan) VALUES (?, ?) |
| READ | `resep_tambah.php` | GET | - | SELECT r.ResepNo, m.nama_menu, r.MenuNo, r.Keterangan FROM resep r LEFT JOIN menu m ON r.MenuNo = m.MenuNo ORDER BY r.ResepNo DESC |
| READ (Single) | `resep_edit.php` | GET | `ResepNo` | SELECT ResepNo, MenuNo, Keterangan FROM resep WHERE ResepNo = ? LIMIT 1 |
| UPDATE | `resep_update.php` | POST | `ResepNo`, `MenuNo`, `keterangan` | UPDATE resep SET MenuNo = ?, Keterangan = ? WHERE ResepNo = ? |
| DELETE | `resep_delete.php` | GET | `ResepNo` | DELETE FROM resep WHERE ResepNo = ? |

**Relationships**:
- → Depends on: `menu` (MenuNo FK)
- ← Referenced by: `detail_resep` (ResepNo FK)

---

### 6. **DETAIL_RESEP Module** (Recipe Details - Ingredients per Recipe)
**Purpose**: Specify which ingredients and quantities go into each recipe  
**Files**: 5 files (delete, edit, list [empty], tambah, update)

**Database Table**: `detail_resep`
- `DetailResepNo` (PK, auto-increment)
- `ResepNo` (FK → resep.ResepNo)
- `StokNo` (FK → stok.StokNo)
- `SatuanNo` (FK → satuan.SatuanNo)
- `jumlah` (FLOAT, quantity)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE (Bulk) | `detail_resep_tambah.php` | POST | Array: `ResepNo`, `StokNo[]`, `SatuanNo[]`, `jumlah[]` | INSERT INTO detail_resep (ResepNo, StokNo, SatuanNo, jumlah) VALUES (?, ?, ?, ?) × N items |
| READ | `detail_resep_tambah.php` | GET | `ResepNo` | SELECT dr.DetailResepNo, dr.jumlah, b.nama_bahan, sat.nama_satuan FROM detail_resep dr LEFT JOIN stok s ON dr.StokNo = s.StokNo LEFT JOIN bahan b ON s.BahanNo = b.BahanNo LEFT JOIN satuan sat ON dr.SatuanNo = sat.SatuanNo WHERE dr.ResepNo = ? |
| READ (Single) | `detail_resep_edit.php` | GET | `DetailResepNo` | SELECT DetailResepNo, ResepNo, StokNo, SatuanNo, jumlah FROM detail_resep WHERE DetailResepNo = ? LIMIT 1 |
| UPDATE | `detail_resep_update.php` | POST | `DetailResepNo`, `ResepNo`, `StokNo`, `SatuanNo`, `jumlah` | UPDATE detail_resep SET ResepNo = ?, StokNo = ?, SatuanNo = ?, jumlah = ? WHERE DetailResepNo = ? |
| DELETE | `detail_resep_delete.php` | GET | `DetailResepNo` | DELETE FROM detail_resep WHERE DetailResepNo = ? LIMIT 1 |

**Special Features**:
- Multi-item insertion (array handling for bulk add)
- Cascading dropdowns (ResepNo → shows available bahan via stok)
- Dynamic satuan selection from stok

**Relationships**:
- → Depends on: `resep` (ResepNo FK), `stok` (StokNo FK), `satuan` (SatuanNo FK)

---

### 7. **PRODUSEN Module** (Suppliers/Producers)
**Purpose**: Manage ingredient suppliers  
**Files**: 5 files (delete, edit, list, save, update)

**Database Table**: `produsen`
- `ProdusenNo` (PK, auto-increment)
- `Nama_Produsen` (VARCHAR, supplier name)
- `Alamat` (TEXT, address)
- `Kontak` (VARCHAR, phone/contact)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `produsen_save.php` | POST | `nama`, `alamat`, `kontak` | INSERT INTO produsen (Nama_Produsen, Alamat, Kontak) VALUES (?, ?, ?) |
| READ | `produsen_list.php` | GET | - | SELECT ProdusenNo, Nama_Produsen, Alamat, Kontak FROM produsen ORDER BY ProdusenNo DESC |
| READ (Single) | `produsen_edit.php` | GET | `produsenNo` | SELECT ProdusenNo, Nama_Produsen, Alamat, Kontak FROM produsen WHERE ProdusenNo = ? LIMIT 1 |
| UPDATE | `produsen_update.php` | POST | `ProdusenNo`, `Nama_Produsen`, `Alamat`, `Kontak` | UPDATE produsen SET Nama_Produsen = ?, Alamat = ?, Kontak = ? WHERE ProdusenNo = ? |
| DELETE | `produsen_delete.php` | GET | `produsenNo` | DELETE FROM produsen WHERE ProdusenNo = ? |

**Relationships**:
- ← Referenced by: `stok` (ProdusenNo FK, optional)

---

### 8. **STOK Module** (Inventory)
**Purpose**: Track ingredient stock levels  
**Files**: 7 files (delete, edit, list [empty], log [empty], save [empty], tambah, update)

**Database Table**: `stok`
- `StokNo` (PK, auto-increment)
- `ProdusenNo` (FK → produsen.ProdusenNo, nullable)
- `BahanNo` (FK → bahan.BahanNo)
- `SatuanNo` (FK → satuan.SatuanNo)
- `jumlah_stok` (INT, current stock level)
- `batas_minimum` (FLOAT, minimum threshold)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `stok_tambah.php` | POST | `ProdusenNo` (opt), `BahanNo`, `SatuanNo`, `jumlah_stok`, `batas_minimum` | INSERT INTO stok (ProdusenNo, BahanNo, SatuanNo, jumlah_stok, batas_minimum) VALUES (?, ?, ?, ?, ?) |
| READ | `stok_tambah.php` | GET | - | SELECT s.StokNo, b.nama_bahan, p.Nama_Produsen, sat.nama_satuan, s.jumlah_stok, s.batas_minimum FROM stok s LEFT JOIN bahan b ON s.BahanNo = b.BahanNo LEFT JOIN produsen p ON s.ProdusenNo = p.ProdusenNo LEFT JOIN satuan sat ON s.SatuanNo = sat.SatuanNo ORDER BY b.nama_bahan ASC |
| READ (Single) | `stok_edit.php` | GET | `StokNo` | SELECT StokNo, ProdusenNo, BahanNo, SatuanNo, jumlah_stok, batas_minimum FROM stok WHERE StokNo = ? LIMIT 1 |
| UPDATE | `stok_update.php` | POST | `StokNo`, `ProdusenNo` (opt), `BahanNo`, `SatuanNo`, `jumlah_stok`, `batas_minimum` | UPDATE stok SET ProdusenNo = ?, BahanNo = ?, SatuanNo = ?, jumlah_stok = ?, batas_minimum = ? WHERE StokNo = ? |
| DELETE | `stok_delete.php` | GET | `StokNo` | DELETE FROM stok WHERE StokNo = ? |

**Features**:
- Optional supplier (ProdusenNo can be NULL)
- Minimum stock threshold tracking
- Complex multi-table joins for display

**Relationships**:
- → Depends on: `bahan` (BahanNo FK), `satuan` (SatuanNo FK), `produsen` (ProdusenNo FK, optional)
- ← Referenced by: `detail_resep` (StokNo FK)

---

### 9. **CUSTOMER Module** (Customers/Clients)
**Purpose**: Manage customer records  
**Files**: 4 files (delete, edit, list, tambah)

**Database Table**: `customer`
- `customerNo` (PK, auto-increment)
- `nama_customer` (VARCHAR, customer name)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `customer_tambah.php` | POST | `nama_customer` | INSERT INTO customer (nama_customer) VALUES (?) |
| READ | `customer_tambah.php` + `customer_list.php` | GET | - | SELECT customerNo, nama_customer FROM customer ORDER BY customerNo DESC |
| READ (Single) | `customer_edit.php` | GET | `customerNo` | SELECT customerNo, nama_customer FROM customer WHERE customerNo = ? LIMIT 1 |
| UPDATE | `customer_edit.php` | POST | `customerNo`, `nama_customer` | UPDATE customer SET nama_customer = ? WHERE customerNo = ? |
| DELETE | `customer_delete.php` | GET | `customerNo` | DELETE FROM customer WHERE customerNo = ? (+ cascade deletes transaksi & detail_transaksi) |

**Cascade Delete**:
```
customer_delete.php:
  1. DELETE FROM detail_transaksi WHERE transaksiNo IN (SELECT ... FROM transaksi WHERE customerNo = ?)
  2. DELETE FROM transaksi WHERE customerNo = ?
  3. DELETE FROM customer WHERE customerNo = ?
```

**Relationships**:
- ← Referenced by: `transaksi` (customerNo FK, nullable)

---

### 10. **KARYAWAN Module** (Employees)
**Purpose**: Manage employee records  
**Files**: 5 files (delete, edit, list [empty], tambah, update)

**Database Table**: `karyawan`
- `karyawanNo` (PK, auto-increment)
- `nama` (VARCHAR, employee name)
- `kontak` (VARCHAR, phone/contact)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `karyawan_tambah.php` | POST | `nama`, `kontak` | INSERT INTO karyawan (nama, kontak) VALUES (?, ?) |
| READ | `karyawan_tambah.php` | GET | - | SELECT karyawanNo, nama, kontak FROM karyawan ORDER BY karyawanNo DESC |
| READ (Single) | `karyawan_edit.php` | GET | `karyawanNo` | SELECT karyawanNo, nama, kontak FROM karyawan WHERE karyawanNo = ? |
| UPDATE | `karyawan_update.php` | POST | `karyawanNo`, `nama`, `kontak` | UPDATE karyawan SET nama = ?, kontak = ? WHERE karyawanNo = ? |
| DELETE | `karyawan_delete.php` | GET | `karyawanNo` | DELETE FROM karyawan WHERE karyawanNo = ? |

**Relationships**:
- ← Referenced by: `data_user` (karyawanNo FK), `transaksi` (karyawanNo FK)

---

### 11. **DATA_USER Module** (User Accounts)
**Purpose**: Manage system user accounts and authentication  
**Files**: 5 files (delete, edit, list [empty], tambah, update)

**Database Table**: `data_user`
- `usernameNo` (PK, auto-increment)
- `karyawanNo` (FK → karyawan.karyawanNo)
- `username` (VARCHAR, login username)
- `password` (VARCHAR, bcrypt hashed)
- `jabatan` (VARCHAR, job title/role)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `user_tambah.php` | POST | `karyawanNo`, `username`, `password`, `jabatan` | INSERT INTO data_user (karyawanNo, username, password, jabatan) VALUES (?, ?, ?, ?) |
| READ | `user_tambah.php` | GET | - | SELECT du.usernameNo, du.karyawanNo, du.username, du.jabatan, k.nama FROM data_user du LEFT JOIN karyawan k ON du.karyawanNo = k.karyawanNo ORDER BY du.usernameNo DESC |
| READ (Single) | `user_edit.php` | GET | `usernameNo` | SELECT usernameNo, karyawanNo, username, jabatan FROM data_user WHERE usernameNo = ? |
| UPDATE | `user_update.php` | POST | `usernameNo`, `karyawanNo`, `username`, `jabatan` | UPDATE data_user SET karyawanNo = ?, username = ?, jabatan = ? WHERE usernameNo = ? |
| DELETE | `user_delete.php` | GET | `usernameNo` | DELETE FROM data_user WHERE usernameNo = ? |

**Security**: 
- Password hashed with PASSWORD_BCRYPT
- No password update in edit (only in create)

**Relationships**:
- → Depends on: `karyawan` (karyawanNo FK)

---

### 12. **METODE_PEMBAYARAN Module** (Payment Methods)
**Purpose**: Define payment methods  
**Files**: 5 files (delete, edit, list [empty], tambah, update)

**Database Table**: `metode_pembayaran`
- `metode_pembayaranNo` (PK, auto-increment)
- `nama_metode` (VARCHAR, method name: Cash, Bank Transfer, Credit Card, etc.)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `metode_tambah.php` | POST | `nama_metode` | INSERT INTO metode_pembayaran (nama_metode) VALUES (?) |
| READ | `metode_tambah.php` | GET | - | SELECT metode_pembayaranNo, nama_metode FROM metode_pembayaran ORDER BY metode_pembayaranNo DESC |
| READ (Single) | `metode_edit.php` | GET | `metode_pembayaranNo` | SELECT metode_pembayaranNo, nama_metode FROM metode_pembayaran WHERE metode_pembayaranNo = ? |
| UPDATE | `metode_update.php` | POST | `metode_pembayaranNo`, `nama_metode` | UPDATE metode_pembayaran SET nama_metode = ? WHERE metode_pembayaranNo = ? |
| DELETE | `metode_delete.php` | GET | `metode_pembayaranNo` | DELETE FROM metode_pembayaran WHERE metode_pembayaranNo = ? |

**Relationships**:
- ← Referenced by: `transaksi` (metode_pembayaranNo FK)

---

### 13. **VOUCHER Module** (Discount Vouchers)
**Purpose**: Manage discount vouchers with minimum purchase requirements  
**Files**: 5 files (delete, edit, list [empty], tambah, update)

**Database Table**: `voucher`
- `voucherNo` (PK, auto-increment)
- `nama_voucher` (VARCHAR, voucher name/description)
- `syarat_minimum` (DECIMAL, minimum purchase amount in Rp)

**CRUD Operations**:
| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `voucher_tambah.php` | POST | `nama_voucher`, `syarat_minimum` | INSERT INTO voucher (nama_voucher, syarat_minimum) VALUES (?, ?) |
| READ | `voucher_tambah.php` | GET | - | SELECT voucherNo, nama_voucher, syarat_minimum FROM voucher ORDER BY voucherNo DESC |
| READ (Single) | `voucher_edit.php` | GET | `voucherNo` | SELECT voucherNo, nama_voucher, syarat_minimum FROM voucher WHERE voucherNo = ? |
| UPDATE | `voucher_update.php` | POST | `voucherNo`, `nama_voucher`, `syarat_minimum` | UPDATE voucher SET nama_voucher = ?, syarat_minimum = ? WHERE voucherNo = ? |
| DELETE | `voucher_delete.php` | GET | `voucherNo` | DELETE FROM voucher WHERE voucherNo = ? |

**Relationships**:
- ← Referenced by: `transaksi` (voucherNo FK, nullable)

---

### 14. **TRANSAKSI Module** (Transactions/Sales)
**Purpose**: Record and manage sales transactions  
**Files**: 4 files (detail, list, proses [empty], tambah)

**Database Tables**: 
- `transaksi`
  - `transaksiNo` (PK, auto-increment)
  - `customerNo` (FK → customer.customerNo, nullable)
  - `karyawanNo` (FK → karyawan.karyawanNo)
  - `metode_pembayaranNo` (FK → metode_pembayaran.metode_pembayaranNo)
  - `voucherNo` (FK → voucher.voucherNo, nullable)
  - `tanggal` (DATETIME, transaction date/time)
  - `total_harga` (DECIMAL, total amount)

- `detail_transaksi`
  - `detailNo` (PK, auto-increment)
  - `transaksiNo` (FK → transaksi.transaksiNo)
  - `VarianMenuNo` (FK → varian_menu.VarianMenuNo)
  - `jumlah` (INT, quantity)
  - `harga_satuan` (DECIMAL, unit price)

**CRUD Operations**:

| Operation | File | Method | Parameters | Query |
|-----------|------|--------|------------|-------|
| CREATE | `transaksi_tambah.php` | POST | `customerNo` (opt), `karyawanNo`, `metode_pembayaranNo`, `voucherNo` (opt), `tanggal`, `items[]` (JSON) | START TRANSACTION; INSERT INTO transaksi (...); INSERT INTO detail_transaksi (...) × N items; COMMIT |
| READ (List) | `transaksi_list.php` | GET | - | SELECT t.transaksiNo, t.tanggal, t.total_harga, c.nama_customer, k.nama, m.nama_metode, v.nama_voucher FROM transaksi t LEFT JOIN customer c ... LEFT JOIN karyawan k ... LEFT JOIN metode_pembayaran m ... LEFT JOIN voucher v ... ORDER BY t.transaksiNo DESC |
| READ (Detail) | `transaksi_detail.php` | GET | `transaksiNo` | Multi-query: header + item details with varian/menu info |

**Transaction Management**:
```php
// transaksi_tambah.php
mysqli_begin_transaction($conn);
try {
    // Insert header
    INSERT INTO transaksi (customerNo, karyawanNo, metode_pembayaranNo, voucherNo, tanggal, total_harga)
    
    // Insert items loop
    foreach ($items as $item) {
        INSERT INTO detail_transaksi (transaksiNo, VarianMenuNo, jumlah, harga_satuan)
    }
    // Commit on success
} catch (Exception $e) {
    mysqli_rollback($conn);
}
```

**Special Features**:
- Transaction handling with COMMIT/ROLLBACK
- DateTime-local format conversion (JS datetime → MySQL datetime)
- NULL customer support (walk-in customers)
- Bulk item insertion from JSON array
- Datetime auto-fill with current time if not provided

**Relationships**:
- → Depends on: `customer` (customerNo FK, opt), `karyawan` (karyawanNo FK), `metode_pembayaran` (metode_pembayaranNo FK), `voucher` (voucherNo FK, opt), `detail_transaksi` (child table)
- Has: `detail_transaksi` (1:N relationship)

---

## Data Flow & Relationships Diagram

```
                        ┌─────────────────┐
                        │     MENU        │ (Menu items)
                        │   MenuNo [PK]   │
                        └────────┬────────┘
                               / │ \
                              /  │  \
                    ┌─────────┘   │   └───────────┐
                    │             │               │
          ┌─────────▼────────┐ ┌─▼────────────────▼────┐
          │  RESEP           │ │  VARIAN_MENU          │
          │  ResepNo [PK]    │ │  VarianMenuNo [PK]    │
          │  MenuNo [FK]─────┼─┤  MenuNo [FK]──────────┤
          └────────┬─────────┘ │  nama_ukuran          │
                   │           │  Harga                │
         ┌─────────▼─────────┐ └──────────┬────────────┘
         │  DETAIL_RESEP     │            │
         │  DetailResepNo[PK]│     ┌──────▼──────────────┐
         │  ResepNo [FK]─────┤     │  DETAIL_TRANSAKSI   │
         │  StokNo [FK]      │     │  detailNo [PK]      │
         │  SatuanNo [FK]    │     │  VarianMenuNo [FK]──┤
         │  jumlah           │     │  transaksiNo [FK]   │
         └────────┬──────────┘     │  jumlah             │
                  │                │  harga_satuan       │
         ┌────────▼────────────┐   └──────────┬──────────┘
         │  STOK               │              │
         │  StokNo [PK]        │     ┌────────▼──────────────┐
         │  BahanNo [FK]       │     │  TRANSAKSI            │
         │  ProdusenNo [FK-opt]│     │  transaksiNo [PK]     │
         │  SatuanNo [FK]      │     │  customerNo [FK-opt]  │
         │  jumlah_stok        │     │  karyawanNo [FK]      │
         │  batas_minimum      │     │  metode_pembayaranNo[FK]
         └────────┬────────────┘     │  voucherNo [FK-opt]   │
                  │                  │  tanggal              │
         ┌────────┴────────────┐     │  total_harga          │
         │                     │     └─────────────┬──────────┘
      ┌──▼──┐          ┌───────▼───────┐         │
      │BAHAN│          │  SATUAN       │         │
      │Bahan└──┐       │SatuanNo [PK] │         │
      └────────┘       └──────────────┘         │
                                                 │
                  ┌──────────────┬───────────────┤
                  │              │               │
            ┌─────▼───┐    ┌────▼────┐   ┌─────▼─────┐
            │CUSTOMER │    │KARYAWAN │   │ METODE_   │
            │customer ├────│karyawan ├──┐│PEMBAYARAN │
            │No [PK]  │    │No [PK]  │  ││metode_   │
            └─────────┘    └────┬────┘  ││pembayaran│
                                │       ││No [PK]   │
                           ┌────▼─────┐││ └─────────┘
                           │DATA_USER │││
                           │username  ││
                           │No [PK]   │││
                           │karyawan  ││
                           │No [FK]───┤│
                           └──────────┘│
                                       │
                                   ┌───▼─────┐
                                   │VOUCHER  │
                                   │voucher  │
                                   │No [PK]  │
                                   └─────────┘
```

**Key Entities**:
- **Master Data**: BAHAN, SATUAN, MENU, KARYAWAN, CUSTOMER, PRODUSEN
- **Product Configuration**: VARIAN_MENU, RESEP, DETAIL_RESEP
- **Inventory**: STOK
- **Settings**: METODE_PEMBAYARAN, VOUCHER, DATA_USER
- **Transactions**: TRANSAKSI, DETAIL_TRANSAKSI

---

## CRUD Pattern Summary

### Standard Pattern (Simple Entities)
**Files**: delete, edit, tambah, update

```
tambah.php: 
  - Form to CREATE new entity
  - POST processing for insert
  - GET to display list

edit.php:
  - GET parameter: [EntityNo]
  - Fetch single record
  - Display form pre-filled

update.php:
  - POST-only handler
  - Update record
  - Redirect to tambah.php

delete.php:
  - GET parameter: [EntityNo]
  - Delete record
  - Redirect to tambah.php
```

**Applies to**: BAHAN, SATUAN, MENU, KARYAWAN, METODE_PEMBAYARAN, VOUCHER

### Complex Pattern (Multiple FKs)
**Files**: delete, edit, list, tambah, update (+ special processing)

**Applies to**: VARIAN_MENU, RESEP, STOK, DETAIL_RESEP, TRANSAKSI

**Features**:
- Dropdown cascading based on FK relationships
- Multiple dropdown options (menu, bahan, satuan, etc.)
- Complex SELECT with LEFT JOINs for display

---

## Security Practices Observed

✅ **Good**:
- Prepared statements with parameterized queries (mysqli_prepare)
- htmlspecialchars() for output escaping
- Password hashing with PASSWORD_BCRYPT
- Type casting (int, float, string)
- Numeric validation is_numeric()

⚠️ **Areas for Improvement**:
- No CSRF token validation
- No rate limiting
- No input length validation
- Some error messages might leak DB info
- No SQL error logging to separate file

---

## Module Interconnections & Data Flow

### User Creation Flow
```
karyawan_tambah.php (create employee)
    ↓
    ↓ (after karyawan created)
    ↓
user_tambah.php (create user account) 
    ↓ SELECT karyawanNo FROM karyawan (dropdown)
    ↓ INSERT INTO data_user (karyawanNo FK)
```

### Recipe Creation Flow
```
menu_tambah.php (create menu item)
    ↓
resep_tambah.php (create recipe for menu)
    ↓ SELECT MenuNo FROM menu (dropdown)
    ↓ INSERT INTO resep (MenuNo FK)
    ↓
detail_resep_tambah.php (add ingredients to recipe)
    ↓ Bulk array insertion
    ↓ INSERT INTO detail_resep (ResepNo, StokNo, SatuanNo FK)
```

### Stock Management Flow
```
bahan_tambah.php (create ingredient)
    ↓
satuan_tambah.php (define measurement unit)
    ↓
produsen_list.php (link supplier, optional)
    ↓
stok_tambah.php (create stock record)
    ↓ SELECT BahanNo, SatuanNo, ProdusenNo (dropdowns)
    ↓ INSERT INTO stok (BahanNo, SatuanNo, ProdusenNo FK)
    ↓
detail_resep_tambah.php (use in recipes)
    ↓ StokNo linked to detail_resep
```

### Transaction Flow
```
customer_tambah.php (register customer, optional)
    ↓
varian_menu_tambah.php (create menu sizes/prices)
    ↓
transaksi_tambah.php (create transaction)
    ↓ SELECT customerNo (optional), karyawanNo, metode_pembayaranNo, voucherNo, VarianMenuNo
    ↓ BEGIN TRANSACTION
    ↓ INSERT INTO transaksi
    ↓ INSERT INTO detail_transaksi (loop through items)
    ↓ COMMIT
    ↓
transaksi_list.php (view all transactions with JOINs)
    ↓
transaksi_detail.php (view single transaction details)
```

---

## Database Operations Summary

### SELECT Patterns
- **Simple**: `SELECT * FROM entity ORDER BY [id] DESC`
- **With Joins**: Multi-table LEFT JOINs (e.g., transaksi_list with customer, karyawan, metode_pembayaran, voucher)
- **Cascading**: Dependent selects (e.g., stok with bahan + satuan)
- **Filtered**: By primary key (single record retrieval)

### INSERT Patterns
- **Single**: `INSERT INTO entity (...) VALUES (?)`
- **Bulk**: Loop through arrays (detail_resep, detail_transaksi)
- **Transaction**: Multiple inserts wrapped in BEGIN/COMMIT/ROLLBACK

### UPDATE Patterns
- **Standard**: `UPDATE entity SET col1=?, col2=? WHERE [id]=?`
- **All modules**: Update by primary key only

### DELETE Patterns
- **Simple**: `DELETE FROM entity WHERE [id]=?`
- **Cascading**: customer_delete cascades to transaksi → detail_transaksi

---

## URL Parameter Summary

| Module | Operation | Parameter | Type |
|--------|-----------|-----------|------|
| BAHAN | Edit | `BahanNo` | GET |
| BAHAN | Delete | `BahanNo` | GET |
| SATUAN | Edit | `SatuanNo` | GET |
| SATUAN | Delete | `SatuanNo` | GET |
| MENU | Edit | `MenuNo` | GET |
| MENU | Delete | `MenuNo` | GET |
| VARIAN | Edit | `VarianMenuNo` | GET |
| VARIAN | Delete | `VarianMenuNo` | GET |
| RESEP | Edit | `ResepNo` | GET |
| RESEP | Delete | `ResepNo` | GET |
| DETAIL_RESEP | Edit | `DetailResepNo` | GET |
| DETAIL_RESEP | Delete | `DetailResepNo` | GET |
| DETAIL_RESEP | List | `ResepNo` | GET (optional, for filtering) |
| STOK | Edit | `StokNo` | GET |
| STOK | Delete | `StokNo` | GET |
| PRODUSEN | Edit | `produsenNo` | GET |
| PRODUSEN | Delete | `produsenNo` | GET |
| CUSTOMER | Edit | `customerNo` | GET |
| CUSTOMER | Delete | `customerNo` | GET |
| KARYAWAN | Edit | `karyawanNo` | GET |
| KARYAWAN | Delete | `karyawanNo` | GET |
| USER | Edit | `usernameNo` | GET |
| USER | Delete | `usernameNo` | GET |
| METODE | Edit | `metode_pembayaranNo` | GET |
| METODE | Delete | `metode_pembayaranNo` | GET |
| VOUCHER | Edit | `voucherNo` | GET |
| VOUCHER | Delete | `voucherNo` | GET |
| TRANSAKSI | Detail | `transaksiNo` | GET |

---

## POST Variables Summary

### Master Data Modules
- **BAHAN**: `nama_bahan`
- **SATUAN**: `nama_satuan`
- **MENU**: `nama_menu`
- **KARYAWAN**: `nama`, `kontak`
- **METODE**: `nama_metode`
- **VOUCHER**: `nama_voucher`, `syarat_minimum`
- **CUSTOMER**: `nama_customer`
- **PRODUSEN**: `nama`, `alamat`, `kontak`

### Complex Modules
- **VARIAN**: `MenuNo`, `nama_ukuran`, `Harga`
- **RESEP**: `MenuNo`, `keterangan`
- **DETAIL_RESEP**: Array - `ResepNo`, `StokNo[]`, `SatuanNo[]`, `jumlah[]`
- **STOK**: `ProdusenNo` (opt), `BahanNo`, `SatuanNo`, `jumlah_stok`, `batas_minimum`
- **USER**: `karyawanNo`, `username`, `password`, `jabatan`
- **TRANSAKSI**: `customerNo` (opt), `karyawanNo`, `metode_pembayaranNo`, `voucherNo` (opt), `tanggal`, `items` (JSON)

---

## Files Structure Legend

- ✅ **Implemented**: Full CRUD working
- 🔄 **Partial**: Form or template-only (empty file)
- ❌ **Empty**: Template placeholder with no code

| Module | delete | edit | list | tambah | update | save | proses |
|--------|--------|------|------|--------|--------|------|--------|
| BAHAN | ✅ | ✅ | - | ✅ | ✅ | - | - |
| SATUAN | ✅ | ✅ | - | ✅ | ✅ | - | - |
| MENU | ✅ | ✅ | 🔄 | ✅ | ✅ | - | - |
| VARIAN | ✅ | 🔄 | 🔄 | ✅ | ✅ | - | - |
| RESEP | ✅ | ✅ | - | ✅ | ✅ | 🔄 | - |
| DETAIL_RESEP | ✅ | ✅ | 🔄 | ✅ | ✅ | - | - |
| STOK | ✅ | ✅ | 🔄 | ✅ | ✅ | 🔄 | - |
| PRODUSEN | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | - |
| CUSTOMER | ✅ | ✅ | ✅ | ✅ | - | - | - |
| KARYAWAN | ✅ | ✅ | 🔄 | ✅ | ✅ | - | - |
| USER | ✅ | ✅ | 🔄 | ✅ | ✅ | - | - |
| METODE | ✅ | ✅ | 🔄 | ✅ | ✅ | - | - |
| VOUCHER | ✅ | ✅ | 🔄 | ✅ | ✅ | - | - |
| TRANSAKSI | - | - | ✅ | ✅ | - | - | 🔄 |

---

## Key Observations

1. **Naming Inconsistency**: Some files use underscores (`bahan_tambah`) while some use camelCase in variable names
2. **Incomplete Implementation**: Several list files are template-only
3. **Redirect Pattern**: Most modules redirect to `*_tambah.php` after operations
4. **Form Persistence**: Successfully cleared form data with `$_POST = []` after successful insert
5. **Optional Fields**: Some FK columns are nullable (customerNo in transaksi, ProdusenNo in stok, voucherNo in transaksi)
6. **Format Helper**: Uses `format_helper.php` for functions like `rupiah()` for currency formatting
7. **Array Processing**: detail_resep and transaksi use array handling for bulk operations
8. **Transaction Support**: Only transaksi_tambah implements database transactions (COMMIT/ROLLBACK)

---

## Recommendations for Improvement

1. **Validation**: Add comprehensive input validation (length, type, range)
2. **Error Handling**: Implement proper error logging instead of die() statements
3. **Security**: Add CSRF tokens, rate limiting, input sanitization
4. **Completeness**: Implement all *_list.php files properly (some are empty)
5. **Transactions**: Use transactions for cascade delete (customer_delete)
6. **Consistency**: Standardize naming conventions across all modules
7. **API**: Consider separating business logic from presentation
8. **Pagination**: Implement pagination for large datasets
9. **Soft Deletes**: Consider soft delete for audit trail
10. **Relationships**: Add constraints validation before delete operations

---

**Analysis Complete** - All 14 modules reviewed with comprehensive CRUD, database, and relationship documentation.
