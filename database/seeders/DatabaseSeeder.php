<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        $roles = [
            'pengusul',
            'wadir1',
            'wadir2',
            'wadir3',
            'wadir4',
            'sekdir',
            'direktur',
            'pelaksana',
            'bku',
            'admin',
        ];

        foreach ($roles as $role) {
            User::create([
                'name' => ucfirst($role),
                'email' => "{$role}@polban.ac.id",
                'password' => Hash::make('password'),
                'role' => $role,
            ]);
        }
    }
}
