<?php

namespace Tests\Unit\Services;

use App\Models\Food;
use App\Models\User;
use App\Services\FoodImage;
use Tests\TestCase;

class FoodImageTest extends TestCase
{
    private User $user;
    private Food $food;
    private FoodImage $foodImage;

    /** @var array<string,mixed> */
    private array $image;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user->save();

        $this->food = new Food([
            'user_id' => $this->user->id,
            'name' => 'Banana',
            'kcal' => 89,
            'carbs' => 22,
            'fats' => 0.3,
            'protein' => 1.1,
            'unit' => 'g',
            'category' => 'Fruta'
        ]);
        $this->food->save();

        $tmpFile = tempnam(sys_get_temp_dir(), 'food');

        $this->image = [
            'name' => 'banana.jpg',
            'full_path' => 'banana.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => $tmpFile,
            'error' => 0,
            'size' => filesize($tmpFile),
        ];

        $this->foodImage = new FoodImage($this->food);
    }

    public function test_upload(): void
    {
        $foodImage = $this->getMockBuilder(FoodImage::class)
            ->setConstructorArgs([$this->food])
            ->onlyMethods(['uploadFile'])
            ->getMock();

        $foodImage->expects($this->once())
            ->method('uploadFile')
            ->willReturn(true);

        $resp = $foodImage->upload($this->image);

        $this->assertTrue($resp);

        $foodReloaded = Food::findById($this->food->id);

        $this->assertMatchesRegularExpression(
            '/^[a-f0-9]{16}\.jpg$/',
            $foodReloaded->photo_url
        );
    }

    public function test_should_fail_with_invalid_image_extension(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img_');
        file_put_contents($tempFile, '<?php echo "Hacked"; ?>');

        $this->food->imageFile = [
            'name' => 'ataque.png',
            'type' => 'image/png',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 1000
        ];

        try {
            $this->assertFalse($this->food->isValid());
            $this->assertEquals(
                'Apenas imagens JPG, JPEG e PNG são permitidas!',
                $this->food->errors('imageFile')
            );
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function test_should_fail_with_large_image(): void
    {
        $this->food->imageFile = [
            'name' => 'foto_pesada.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '/tmp/qualquer_coisa',
            'error' => 1,
            'size' => 6 * 1024 * 1024
        ];

        $this->assertFalse($this->food->isValid());

        $this->assertEquals(
            'A imagem não pode ser maior que 5MB!',
            $this->food->errors('imageFile')
        );
    }

    public function test_default_image_path(): void
    {
        $this->assertEquals(
            '/assets/images/defaults/food.svg',
            $this->foodImage->path()
        );
    }

    public function test_food_image_instance(): void
    {
        $this->assertInstanceOf(
            FoodImage::class,
            $this->food->image()
        );
    }

    public function test_should_generate_unique_uuid(): void
    {
        $food1 = new Food();
        $food2 = new Food();

        $this->assertNotEquals(
            $food1->uuid,
            $food2->uuid
        );
    }
}
