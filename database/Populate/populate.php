<?php

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;
use Database\Populate\UsersPopulate;
use Database\Populate\ProfilesAndDietsPopulate;
use Database\Populate\FoodsPopulate;

Database::migrate();

UsersPopulate::populate();
ProfilesAndDietsPopulate::populate();
FoodsPopulate::populate();
