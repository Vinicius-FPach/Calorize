<?php

namespace App\Models;

use App\Services\ProfileAvatar;
use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $encrypted_password
 * @property string $avatar_name
 * @property int $is_admin
 */
class User extends Model
{
    protected static string $table = 'users';
    protected static array $columns = ['name', 'email', 'encrypted_password', 'avatar_name', 'is_admin'];

    protected ?string $password = null;
    protected ?string $password_confirmation = null;

    public function __construct($params = [])
    {
        parent::__construct($params);

        if ($this->is_admin === null) {
            $this->is_admin = 0;
        }
    }

    public function profile(): ?Profile
    {
        return Profile::findBy(['user_id' => $this->id]);
    }

    public function diets(): HasMany
    {
        return $this->hasMany(Diet::class, 'user_id');
    }

    public function foods(): HasMany
    {
        return $this->hasMany(Food::class, 'user_id');
    }

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::notEmpty('email', $this);

        Validations::uniqueness('email', $this);

        if ($this->newRecord()) {
            Validations::passwordConfirmation($this);
        }
    }

    public function authenticate(string $password): bool
    {
        if ($this->encrypted_password == null) {
            return false;
        }

        return password_verify($password, $this->encrypted_password);
    }

    public static function findByEmail(string $email): User | null
    {
        return User::findBy(['email' => $email]);
    }

    public function __set(string $property, mixed $value): void
    {
        parent::__set($property, $value);

        if (
            $property === 'password' &&
            $this->newRecord() &&
            $value !== null && $value !== ''
        ) {
            $this->encrypted_password = password_hash($value, PASSWORD_DEFAULT);
        }
    }

    public function avatar(): ProfileAvatar
    {
        return new ProfileAvatar($this, [
            'extension' => ['png', 'jpg', 'jpeg'],
            'size' => 5 * 1024 * 1024
        ]);
    }
}
