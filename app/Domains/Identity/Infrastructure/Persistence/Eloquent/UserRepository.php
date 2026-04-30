<?php

namespace App\Domains\Identity\Infrastructure\Persistence\Eloquent;

use App\Domains\Identity\Domain\Entities\User as UserEntity;
use App\Models\User as UserModel;

final class UserRepository
{
    public function findOrCreateFromFirebase(array $firebaseUser): UserEntity
    {
        $model = UserModel::query()->firstOrCreate(
            ['firebase_uid' => $firebaseUser['uid']],
            [
                'email' => $firebaseUser['email'] ?? null,
                'name' => $firebaseUser['name'] ?? null,
                'avatar' => $firebaseUser['picture'] ?? null,
                'is_email_verified' => (bool) ($firebaseUser['email_verified'] ?? false),
            ]
        );

        $model->forceFill([
            'email' => $firebaseUser['email'] ?? $model->email,
            'name' => $firebaseUser['name'] ?? $model->name,
            'avatar' => $firebaseUser['picture'] ?? $model->avatar,
            'is_email_verified' => (bool) ($firebaseUser['email_verified'] ?? false),
        ])->save();

        return $this->toEntity($model);
    }

    public function findModelByFirebaseUid(string $firebaseUid): ?UserModel
    {
        return UserModel::query()
            ->where('firebase_uid', $firebaseUid)
            ->first();
    }

    private function toEntity(UserModel $model): UserEntity
    {
        return new UserEntity(
            id: $model->id,
            firebaseUid: $model->firebase_uid,
            email: $model->email,
            name: $model->name,
            avatar: $model->avatar,
            isEmailVerified: (bool) $model->is_email_verified,
        );
    }
}