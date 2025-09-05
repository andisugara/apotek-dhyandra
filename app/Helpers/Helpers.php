<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

if (!function_exists('getMenu')) {
    /**
     * Get navigation menu based on user role.
     *
     * @return array
     */
    function getMenu()
    {
        $user = Auth::user();

        // Common menu items for all users
        $menu = [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'icon'  => 'ki-element-11',
            ],
        ];

        if ($user) {
            // Menus for Superadmin
            if ($user->role && $user->role->name === 'Superadmin') {
                $menu[] = [
                    'title' => 'Master',
                    'icon'  => 'ki-abstract-26',
                    'children' => [
                        ['title' => 'Supplier', 'route' => 'supplier.index'],
                        ['title' => 'User', 'route' => 'user.index'],
                        ['title' => 'Pasien', 'route' => 'pasien.index'],
                        ['title' => 'Golongan Obat', 'route' => 'golongan_obat.index'],
                        ['title' => 'Kategori Obat', 'route' => 'kategori_obat.index'],
                        ['title' => 'Lokasi Obat', 'route' => 'lokasi_obat.index'],
                        ['title' => 'Satuan Obat', 'route' => 'satuan_obat.index'],
                        ['title' => 'Pabrik', 'route' => 'pabrik.index'],
                        ['title' => 'Akun', 'route' => 'akun.index'],
                        ['title' => 'Data Obat', 'route' => 'obat.index'],
                    ],
                ];

                $menu[] = [
                    'title' => 'Pembelian',
                    'icon'  => 'ki-tag',
                    'children' => [
                        ['title' => 'Pembelian Obat', 'route' => 'pembelian.index'],
                        ['title' => 'Retur Obat', 'route' => 'retur_pembelian.index'],
                    ],
                ];

                $menu[] = [
                    'title' => 'Penjualan',
                    'route' => 'penjualan.index',
                    'icon'  => 'ki-purchase',
                ];

                $menu[] = [
                    'title' => 'Stock Opname',
                    'route' => 'stock_opname.index',
                    'icon'  => 'ki-clipboard',
                ];

                $menu[] = [
                    'title' => 'Pengeluaran',
                    'route' => 'pengeluaran.index',
                    'icon'  => 'ki-wallet',
                ];

                $menu[] = [
                    'title' => 'Laporan',
                    'icon'  => 'ki-file',
                    'children' => [
                        // ['title' => 'Laporan Penjualan', 'route' => 'laporan.penjualan.index'],
                        ['title' => 'Laporan Laba Rugi', 'route' => 'laporan.laba-rugi.index'],
                    ],
                ];

                $menu[] = [
                    'title' => 'Pengaturan',
                    'route' => 'settings.index',
                    'icon'  => 'ki-gear',
                ];
            }

            // Menus for Apoteker
            if ($user->role && $user->role->name === 'Apoteker') {
                $menu[] = [
                    'title' => 'Data',
                    'icon'  => 'ki-abstract-26',
                    'children' => [
                        ['title' => 'Supplier', 'route' => 'supplier.index'],
                        ['title' => 'Pasien', 'route' => 'pasien.index'],
                        ['title' => 'Data Obat', 'route' => 'obat.index'],
                    ],
                ];

                $menu[] = [
                    'title' => 'Penjualan',
                    'route' => 'penjualan.index',
                    'icon'  => 'ki-purchase',
                ];

                $menu[] = [
                    'title' => 'Stock Opname',
                    'route' => 'stock_opname.index',
                    'icon'  => 'ki-clipboard',
                ];
            }
        }

        return $menu;
    }
}

if (!function_exists('getSetting')) {
    /**
     * Get application settings.
     *
     * @return array
     */
    function getSetting()
    {
        // Example: fetch settings from config or database
        // Replace with actual logic as needed
        return Setting::first();
    }
}
