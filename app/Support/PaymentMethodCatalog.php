<?php

namespace App\Support;

final class PaymentMethodCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'midtrans_snap' => [
                'label' => 'Midtrans Snap',
                'description' => 'Bayar online melalui Midtrans (VA, e-wallet, QRIS, kartu) dengan status sinkron otomatis.',
                'icon' => 'MT',
                'type' => 'gateway',
                'instant' => false,
                'requires_proof' => false,
                'destination' => [
                    'account_name' => 'Midtrans Gateway',
                    'account_number' => 'Snap Redirect',
                ],
            ],
            'qris' => [
                'label' => 'QRIS',
                'description' => 'Scan QRIS dalam 5 menit untuk mengunci booking dan menerbitkan tiket setelah pembayaran sukses.',
                'icon' => 'QR',
                'type' => 'qris',
                'instant' => true,
                'requires_proof' => false,
                'destination' => [
                    'account_name' => 'PT Cakrawala Airways',
                    'account_number' => 'QRIS Dynamic',
                ],
            ],
            'virtual_account_bca' => [
                'label' => 'BCA Virtual Account',
                'description' => 'Transfer ke virtual account BCA lalu kirim data rekening pengirim dan bukti bayar.',
                'icon' => 'BCA',
                'type' => 'virtual_account',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'bank_name' => 'BCA',
                    'account_name' => 'PT Cakrawala Airways',
                    'account_number' => '8800 9988 7766',
                ],
            ],
            'virtual_account_mandiri' => [
                'label' => 'Mandiri Virtual Account',
                'description' => 'Transfer ke virtual account Mandiri lalu kirim data rekening pengirim dan bukti bayar.',
                'icon' => 'MDR',
                'type' => 'virtual_account',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'bank_name' => 'Mandiri',
                    'account_name' => 'PT Cakrawala Airways',
                    'account_number' => '8855 0011 2233',
                ],
            ],
            'virtual_account_bni' => [
                'label' => 'BNI Virtual Account',
                'description' => 'Transfer ke virtual account BNI lalu kirim data rekening pengirim dan bukti bayar.',
                'icon' => 'BNI',
                'type' => 'virtual_account',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'bank_name' => 'BNI',
                    'account_name' => 'PT Cakrawala Airways',
                    'account_number' => '9888 1122 3344',
                ],
            ],
            'bank_transfer_bri' => [
                'label' => 'BRI Transfer',
                'description' => 'Transfer manual ke rekening BRI perusahaan, isi data rekening pengirim, dan upload bukti.',
                'icon' => 'BRI',
                'type' => 'bank_transfer',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'bank_name' => 'BRI',
                    'account_name' => 'PT Cakrawala Airways',
                    'account_number' => '0123 0101 8899 00',
                ],
            ],
            'e_wallet_gopay' => [
                'label' => 'GoPay',
                'description' => 'Transfer dari akun GoPay Anda lalu kirim nomor ponsel e-wallet dan bukti pembayaran.',
                'icon' => 'GO',
                'type' => 'e_wallet',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'account_name' => 'Cakrawala Payment',
                    'account_number' => '0812-0000-8899',
                ],
            ],
            'e_wallet_ovo' => [
                'label' => 'OVO',
                'description' => 'Transfer dari akun OVO Anda lalu kirim nomor ponsel e-wallet dan bukti pembayaran.',
                'icon' => 'OVO',
                'type' => 'e_wallet',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'account_name' => 'Cakrawala Payment',
                    'account_number' => '0812-0000-8801',
                ],
            ],
            'e_wallet_dana' => [
                'label' => 'DANA',
                'description' => 'Transfer dari akun DANA Anda lalu kirim nomor ponsel e-wallet dan bukti pembayaran.',
                'icon' => 'DNA',
                'type' => 'e_wallet',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'account_name' => 'Cakrawala Payment',
                    'account_number' => '0812-0000-8802',
                ],
            ],
            'e_wallet_shopeepay' => [
                'label' => 'ShopeePay',
                'description' => 'Transfer dari akun ShopeePay Anda lalu kirim nomor ponsel e-wallet dan bukti pembayaran.',
                'icon' => 'SP',
                'type' => 'e_wallet',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'account_name' => 'Cakrawala Payment',
                    'account_number' => '0812-0000-8803',
                ],
            ],
            'credit_card' => [
                'label' => 'Credit Card',
                'description' => 'Simulasi kartu kredit. Masukkan nama pemilik kartu, nomor kontak, dan catatan referensi transaksi.',
                'icon' => 'CC',
                'type' => 'card',
                'instant' => false,
                'requires_proof' => false,
            ],
            'bank_transfer' => [
                'label' => 'Manual Bank Transfer',
                'description' => 'Transfer manual ke rekening perusahaan, isi data rekening pengirim, dan upload bukti pembayaran.',
                'icon' => 'TRF',
                'type' => 'bank_transfer',
                'instant' => false,
                'requires_proof' => true,
                'destination' => [
                    'bank_name' => 'BCA',
                    'account_name' => 'PT Cakrawala Airways',
                    'account_number' => '7777 1100 2200',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function checkoutOptions(): array
    {
        return [
            'midtrans_snap' => self::all()['midtrans_snap'],
        ];
    }

    public static function isValid(?string $method): bool
    {
        return is_string($method) && array_key_exists($method, self::all());
    }

    public static function label(?string $method): string
    {
        if (! self::isValid($method)) {
            return ucfirst(str_replace('_', ' ', (string) $method));
        }

        return self::all()[$method]['label'];
    }

    public static function requiresProof(?string $method): bool
    {
        return self::isValid($method) && (bool) self::all()[$method]['requires_proof'];
    }

    public static function isInstant(?string $method): bool
    {
        return self::isValid($method) && (bool) self::all()[$method]['instant'];
    }

    public static function type(?string $method): ?string
    {
        if (! self::isValid($method)) {
            return null;
        }

        return self::all()[$method]['type'] ?? null;
    }

    public static function destination(?string $method): array
    {
        if (! self::isValid($method)) {
            return [];
        }

        return self::all()[$method]['destination'] ?? [];
    }
}
