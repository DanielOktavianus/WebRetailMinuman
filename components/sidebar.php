<?php
// Sidebar component - use from modules pages
// session_start() should be called in the main page BEFORE including sidebar

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../helpers/auth_helper.php';

// Use absolute paths so links work regardless of include depth.
$base = defined('APP_BASE') ? APP_BASE : '/WebRetailMinuman';

// Define all links with module mapping for access control
$allLinks = [
	[
		'label' => 'Dashboard',
		'icon'  => '🏠',
		'href' => $base . '/dashboard/dashboard.php',
		'type' => 'link',
		'module' => 'dashboard'
	],
	[
		'label' => 'Menu',
		'icon'  => '🍽️',
		'type' => 'group',
		'module' => 'menu',
		'items' => [
			'Menu' => $base . '/modules/menu/menu_tambah.php',
			'Varian Menu' => $base . '/modules/varian_menu/varian_tambah.php',
		]
	],
	[
		'label' => 'Resep',
		'icon'  => '📋',
		'type' => 'group',
		'module' => 'resep',
		'items' => [
			'Resep' => $base . '/modules/resep/resep_tambah.php',
			'Detail Resep' => $base . '/modules/detail_resep/detail_resep_tambah.php',
			'Satuan' => $base . '/modules/satuan/satuan_tambah.php',
		]
	],
	[
		'label' => 'Stok',
		'icon'  => '📦',
		'type' => 'group',
		'module' => 'stok',
		'items' => [
			'Stok' => $base . '/modules/stok/stok_tambah.php',
			'Bahan' => $base . '/modules/bahan/bahan_tambah.php',
			'Produsen' => $base . '/modules/produsen/produsen_list.php',
		]
	],
	[
		'label' => 'Transaksi',
		'icon'  => '🧾',
		'type' => 'group',
		'module' => 'transaksi',
		'items' => [
			'Transaksi' => $base . '/modules/transaksi/transaksi_tambah.php',
			'Detail Transaksi' => $base . '/modules/transaksi/transaksi_list.php',
		]
	],
	[
		'label' => 'Voucher',
		'icon'  => '🎫',
		'href' => $base . '/modules/voucher/voucher_tambah.php',
		'type' => 'link',
		'module' => 'voucher'
	],
	[
		'label' => 'Metode Pembayaran',
		'icon'  => '💳',
		'href' => $base . '/modules/metode_pembayaran/metode_tambah.php',
		'type' => 'link',
		'module' => 'metode_pembayaran'
	],
	[
		'label' => 'User',
		'icon'  => '👥',
		'type' => 'group',
		'module' => 'users',
		'items' => [
			'Users' => $base . '/modules/users/user_tambah.php',
		]
	],
	[
		'label' => 'Karyawan',
		'icon'  => '👔',
		'href' => $base . '/modules/karyawan/karyawan_tambah.php',
		'type' => 'link',
		'module' => 'karyawan'
	],
];

// Filter links by user role
$allowedModules = getAllowedModules();
$links = array_filter($allLinks, function($item) use ($allowedModules) {
	return isset($item['module']) && in_array($item['module'], $allowedModules);
});

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$userRole = getUserRole();
$username = getUsername();
?>

<!-- Hamburger Button (Mobile) -->
<button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">☰</button>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
	<div class="brand">
		Monitoring Penjualan
		<div style="font-size: 11px; color: #9ca3af; margin-top: 4px;">
			<?php
			$roleLabel = $userRole === 'admin' ? 'PEMILIK' : strtoupper($userRole);
			echo htmlspecialchars($username) . ' (' . $roleLabel . ')';
		?>
		</div>
	</div>
	<nav>
		<?php foreach ($links as $item):
			if ($item['type'] === 'link'):
				$isActive = ($item['href'] === $currentPath || basename($item['href']) === basename($currentPath)) ? ' active' : '';
		?>
				<a class="sidebar-link<?php echo $isActive; ?>" href="<?php echo $item['href']; ?>">
					<?php if (!empty($item['icon'])): ?>
						<span class="nav-icon"><?php echo $item['icon']; ?></span>
					<?php endif; ?>
					<?php echo $item['label']; ?>
				</a>
			<?php else: // group dropdown
				// Check if any item in group is active
				$groupActive = false;
				foreach ($item['items'] as $subHref):
					if ($subHref === $currentPath || basename($subHref) === basename($currentPath)):
						$groupActive = true;
						break;
					endif;
				endforeach;
				$groupActiveClass = $groupActive ? ' active' : '';
		?>
				<div class="sidebar-group<?php echo $groupActiveClass; ?>">
					<button class="sidebar-group-toggle<?php echo $groupActiveClass; ?>" type="button" data-target="group-<?php echo strtolower(str_replace(' ', '-', $item['label'])); ?>">
						<span class="group-label">
							<?php if (!empty($item['icon'])): ?>
								<span class="nav-icon"><?php echo $item['icon']; ?></span>
							<?php endif; ?>
							<?php echo $item['label']; ?>
						</span>
						<span class="group-arrow">▼</span>
					</button>
					<div class="sidebar-group-items" id="group-<?php echo strtolower(str_replace(' ', '-', $item['label'])); ?>"<?php echo $groupActive ? ' style="display: block;"' : ''; ?>>
						<?php foreach ($item['items'] as $label => $href):
							$isSubActive = ($href === $currentPath || basename($href) === basename($currentPath)) ? ' active' : '';
						?>
							<a class="sidebar-link sidebar-sublink<?php echo $isSubActive; ?>" href="<?php echo $href; ?>">
								<?php echo $label; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		
		<!-- Logout Button -->
		<div style="margin-top: auto; padding: 12px 0; border-top: 1px solid #333;">
			<a href="<?php echo $base; ?>/auth/logout.php" class="sidebar-link" style="background: #ef4444; color: white; text-align: center; border-radius: 4px; margin: 8px 0;">
				Logout
			</a>
		</div>
	</nav>
</aside>

<style>
	.sidebar {
		width: 260px;
		background: #0f1724;
		color: #e6eef8;
		/* height dihandle oleh position:sticky + height:100vh di components.css */
		overflow-y: auto;
		padding: 0;
		border-right: 1px solid #1e2d3d;
	}

	.sidebar .brand {
		padding: 20px;
		font-size: 18px;
		font-weight: bold;
		border-bottom: 1px solid #333;
		background: #0f0f0f;
	}

	.sidebar nav {
		flex: 1;
		padding: 0;
		display: flex;
		flex-direction: column;
	}

	.nav-icon {
		display: inline-block;
		width: 22px;
		text-align: center;
		margin-right: 8px;
		font-size: 15px;
		flex-shrink: 0;
	}

	.sidebar-link {
		display: flex;
		align-items: center;
		padding: 12px 20px;
		color: #ccc;
		text-decoration: none;
		transition: all 0.2s ease;
		border-left: 3px solid transparent;
		font-size: 14px;
	}

	.sidebar-link:hover {
		background: #2a2a2a;
		color: #fff;
	}

	.sidebar-link.active {
		background: #3b82f6;
		color: #fff;
		border-left-color: #fff;
	}

	.sidebar-sublink {
		padding-left: 40px;
		font-size: 13px;
		color: #93c5fd;
		border-left-color: rgba(147, 197, 253, 0.2);
	}

	.sidebar-sublink:hover {
		background: rgba(147, 197, 253, 0.08);
		color: #bfdbfe;
	}

	.sidebar-sublink.active {
		background: #2563eb;
		color: #fff;
		border-left-color: #93c5fd;
	}

	.sidebar-group {
		display: flex;
		flex-direction: column;
	}

	.sidebar-group-toggle {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 12px 20px;
		background: transparent;
		border: none;
		color: #ccc;
		cursor: pointer;
		transition: all 0.2s ease;
		border-left: 3px solid transparent;
		font-size: 14px;
		width: 100%;
		text-align: left;
	}

	.sidebar-group-toggle .group-label {
		display: flex;
		align-items: center;
		gap: 0;
	}

	.sidebar-group-toggle:hover {
		background: #2a2a2a;
		color: #fff;
	}

	.sidebar-group.active .sidebar-group-toggle {
		background: #3b82f6;
		color: #fff;
		border-left-color: #fff;
	}

	.group-arrow {
		display: inline-block;
		transition: transform 0.2s ease;
		font-size: 12px;
	}

	.sidebar-group-toggle:not(.active) .group-arrow {
		transform: rotate(-90deg);
	}

	.sidebar-group-items {
		display: none;
		flex-direction: column;
		background: #0f0f0f;
	}

	.sidebar-group-items.show {
		display: flex;
	}

	/* Responsive untuk mobile */
	@media (max-width: 768px) {
		.sidebar {
			position: fixed;
			left: 0;
			top: 0;
			z-index: 1000;
			transform: translateX(-100%);
			transition: transform 0.3s ease;
		}
		
		.sidebar.active {
			transform: translateX(0);
		}
	}
</style>

<script>
	// Handle dropdown toggle
	document.querySelectorAll('.sidebar-group-toggle').forEach(btn => {
		btn.addEventListener('click', function() {
			const targetId = this.getAttribute('data-target');
			const groupItems = document.getElementById(targetId);
			const groupContainer = this.closest('.sidebar-group');
			
			// Toggle display
			if (groupItems.style.display === 'none' || groupItems.style.display === '') {
				groupItems.style.display = 'block';
				this.classList.add('active');
				groupContainer.classList.add('active');
			} else {
				groupItems.style.display = 'none';
				this.classList.remove('active');
				groupContainer.classList.remove('active');
			}
		});
	});

	// Handle hamburger button and sidebar toggle for mobile
	document.addEventListener('DOMContentLoaded', function() {
		const hamburgerBtn = document.getElementById('hamburgerBtn');
		const sidebar = document.getElementById('sidebar');
		const sidebarOverlay = document.getElementById('sidebarOverlay');

		if (hamburgerBtn && sidebar && sidebarOverlay) {
			// Toggle sidebar when hamburger button is clicked
			hamburgerBtn.addEventListener('click', function(e) {
				e.stopPropagation();
				sidebar.classList.toggle('active');
				sidebarOverlay.classList.toggle('active');
			});

			// Close sidebar when overlay is clicked
			sidebarOverlay.addEventListener('click', function() {
				sidebar.classList.remove('active');
				sidebarOverlay.classList.remove('active');
			});

			// Close sidebar when a link is clicked
			sidebar.querySelectorAll('a').forEach(link => {
				link.addEventListener('click', function() {
					// Only close if screen is mobile size
					if (window.innerWidth <= 768) {
						sidebar.classList.remove('active');
						sidebarOverlay.classList.remove('active');
					}
				});
			});

			// Close sidebar if window is resized to desktop
			window.addEventListener('resize', function() {
				if (window.innerWidth > 768) {
					sidebar.classList.remove('active');
					sidebarOverlay.classList.remove('active');
				}
			});
		}
	});
</script>

<!-- PWA Install Banner (muncul otomatis kalau browser support install) -->
<div id="pwa-install-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;
     background:linear-gradient(90deg,#667eea,#764ba2);color:white;
     padding:14px 20px;z-index:9999;
     align-items:center;justify-content:space-between;gap:12px;
     font-size:14px;box-shadow:0 -2px 12px rgba(0,0,0,0.25)">
    <div>
        <strong>📲 Install Aplikasi</strong>
        <span style="font-size:12px;opacity:0.85;margin-left:8px">Tambahkan ke layar utama HP kamu</span>
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0">
        <button id="pwa-install-btn"
            style="background:white;color:#667eea;border:none;padding:8px 16px;
                   border-radius:6px;cursor:pointer;font-weight:700;font-size:13px">
            Install
        </button>
        <button id="pwa-close-banner"
            style="background:rgba(255,255,255,0.2);color:white;border:none;
                   padding:8px 11px;border-radius:6px;cursor:pointer;font-size:18px;line-height:1">
            ✕
        </button>
    </div>
</div>
<script>window.APP_BASE = "<?php echo defined('APP_BASE') ? APP_BASE : '/WebRetailMinuman'; ?>";</script>
<script>
(function(){
    const params = new URLSearchParams(window.location.search);
    if (params.get('deleted') === '1') {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#10b981;color:white;padding:14px 20px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.18);font-size:14px;font-weight:600;z-index:99999;display:flex;align-items:center;gap:8px;transition:opacity .4s';
        toast.innerHTML = '<span style="font-size:18px">✓</span> Data berhasil dihapus';
        document.body.appendChild(toast);
        params.delete('deleted');
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState({}, '', newUrl);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 3000);
    }
})();
</script>
<script src="<?php echo (defined('APP_BASE') ? APP_BASE : '/WebRetailMinuman'); ?>/assets/js/pwa.js"></script>

