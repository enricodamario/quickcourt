<?php

return [
    'accepted' => ':Attribute harus disetujui.',
    'after_or_equal' => ':Attribute harus tanggal yang sama atau setelah :date.',
    'array' => ':Attribute tidak valid.',
    'before_or_equal' => ':Attribute harus tanggal yang sama atau sebelum :date.',
    'between' => [
        'array' => ':Attribute harus berisi :min sampai :max item.',
        'file' => 'Ukuran :attribute harus :min sampai :max kilobyte.',
        'numeric' => ':Attribute harus di antara :min dan :max.',
        'string' => ':Attribute harus :min sampai :max karakter.',
    ],
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => 'Format :attribute tidak valid.',
    'distinct' => ':Attribute tidak boleh ada yang sama.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'file' => ':Attribute harus berupa file.',
    'image' => ':Attribute harus berupa gambar.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'lowercase' => ':Attribute harus huruf kecil.',
    'max' => [
        'array' => ':Attribute maksimal berisi :max item.',
        'file' => 'Ukuran :attribute maksimal :max kilobyte.',
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa file: :values.',
    'min' => [
        'array' => ':Attribute minimal berisi :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'password' => [
        'letters' => ':Attribute harus mengandung minimal satu huruf.',
        'mixed' => ':Attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus mengandung minimal satu angka.',
        'symbols' => ':Attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':Attribute ini pernah bocor di internet. Gunakan kata sandi lain.',
    ],
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah dipakai.',
    'uploaded' => ':Attribute gagal diunggah.',

    // Pesan khusus untuk kolom tertentu
    'custom' => [
        'foto' => [
            'max' => 'Ukuran foto maksimal 2 MB.',
        ],
    ],

    // Nama kolom yang ditampilkan di pesan error
    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'kata sandi',
        'current_password' => 'kata sandi saat ini',
        'foto' => 'foto',
        'nama' => 'nama',
        'alamat' => 'alamat',
        'deskripsi' => 'deskripsi',
        'status' => 'status',
        'jenis_lapangan_id' => 'jenis olahraga',
        'jumlah_court' => 'jumlah lapangan',
        'harga_per_jam' => 'harga per jam',
        'tanggal' => 'tanggal',
        'metode_bayar' => 'metode pembayaran',
    ],
];