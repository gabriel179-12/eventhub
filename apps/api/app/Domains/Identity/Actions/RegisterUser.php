<?php

namespace App\Domains\Identity\Actions;


use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class RegisterUser
{
    /**
     * @param array{name: string, email: string, password: string} $attributes
     */
    public function handle(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $participantRole = Role::query()
                ->where('name', 'participant')
                ->firstOrFail();
            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
            ]);
            $user->roles()->attach($participantRole->id);

            return $user;
        });
    }
}