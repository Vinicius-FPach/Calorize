<?php

namespace App\Models;

use App\Services\FoodImage;
use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\Database;
use Lib\Validations;
use Lib\Paginator;
use Core\Database\ActiveRecord\Model;
use PDO;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $user_id
 * @property string $name
 * @property int $favorite
 * @property float $kcal
 * @property float $carbs
 * @property float $fats
 * @property float $protein
 * @property string $unit
 * @property string $category
 * @property int $is_global
 * @property string|null $photo_url
 * @property string $moderation_status
 * @property string|null $moderated_at
 * @property User $user
 */
class Food extends Model
{
    protected static string $table = 'foods';
    protected static array $columns = [
        'uuid', 'user_id', 'name', 'favorite', 'kcal', 'carbs', 'fats',
        'protein', 'unit', 'category', 'is_global',
        'photo_url', 'moderation_status', 'moderated_at'
    ];

    public function __construct($params = [])
    {
        parent::__construct($params);

        if ($this->uuid === null) {
            $this->uuid = bin2hex(random_bytes(8));
        }

        if ($this->is_global === null) {
            $this->is_global = 0;
        }

        if ($this->moderation_status === null) {
            $this->moderation_status = 'PENDENTE';
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @var array<string, mixed>|null */
    public ?array $imageFile = null;

    public function validates(): void
    {
        Validations::notEmpty('name', $this, msg: 'O nome do alimento não pode ser vazio!');
        Validations::notEmpty('unit', $this, msg: 'Selecione a unidade de medida!');
        Validations::notEmpty('category', $this, msg: 'Informe a categoria do alimento!');

        Validations::maxLength('name', $this, 32, msg: 'O nome do alimento não pode ter mais de 32 caracteres!');
        Validations::maxLength('category', $this, 32, msg: 'O nome da categoria não pode ter mais de 32 caracteres!');

        Validations::greaterThan('kcal', $this, 0, msg: 'O valor calórico deve ser maior que zero!');
        Validations::greaterThan('carbs', $this, 0, msg: 'Os carboidratos devem ser maiores que zero!');
        Validations::greaterThan('fats', $this, 0, msg: 'As gorduras devem ser maiores que zero!');
        Validations::greaterThan('protein', $this, 0, msg: 'As proteínas devem ser maiores que zero!');

        Validations::maxValue('kcal', $this, 900, msg: 'O valor calórico não pode passar de 900 kcal a cada 100g/mL!');
        Validations::maxValue('carbs', $this, 100, msg: 'Os carboidratos não podem passar de 100g a cada 100g/mL!');
        Validations::maxValue('fats', $this, 100, msg: 'As gorduras não podem passar de 100g a cada 100g/mL!');
        Validations::maxValue('protein', $this, 100, msg: 'As proteínas não podem passar de 100g a cada 100g/mL!');

        Validations::fileSize('imageFile', $this, 5 * 1024 * 1024, msg: 'A imagem não pode ser maior que 5MB!');
        Validations::fileExtension(
            'imageFile',
            $this,
            ['jpg', 'jpeg', 'png'],
            msg: 'Apenas imagens JPG, JPEG e PNG são permitidas!'
        );
    }

    public function image(): FoodImage
    {
        return new FoodImage($this);
    }

    public function hasMeals(): bool
    {
        return !empty(FoodMeal::where(['food_id' => $this->id]));
    }

    /**
     * @return array<int, Food>
     */
    public static function searchAvailable(int $userId, string $search = ''): array
    {
        $sql = <<<SQL
            SELECT id, uuid, user_id, name, kcal, carbs, fats, protein,
                   unit, category, is_global, photo_url, moderation_status, moderated_at
            FROM foods
            WHERE (is_global = 1 OR user_id = :user_id)
              AND name LIKE :search
            ORDER BY name ASC
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':search', '%' . $search . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $foods = [];
        foreach ($rows as $row) {
            $foods[] = new static($row);
        }

        return $foods;
    }

    public static function paginateFavorites(
    int $userId,
    int $page,
    int $perPage,
    string $route
): Paginator
{
    return new Paginator(
        class: static::class,
        page: $page,
        per_page: $perPage,
        table: static::table(),
        attributes: static::columns(),
        conditions: [
            'user_id' => $userId,
            'favorite' => 1
        ],
        route: $route
    );
}
}
