SET foreign_key_checks = 0;

DROP TABLE IF EXISTS food_meal;
DROP TABLE IF EXISTS meals;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS diets;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS profiles;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(50) NOT NULL,
    encrypted_password VARCHAR(255) NOT NULL,
    avatar_name VARCHAR(65),
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    reset_token VARCHAR(255) NULL,
    reset_token_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    height INT NOT NULL,
    birthday DATE NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    biotype ENUM('ECTOMORFO', 'MESOMORFO', 'ENDOMORFO') NOT NULL,
    gender ENUM('M', 'F', 'NI') NOT NULL,
    activity_factor ENUM('1.200', '1.375', '1.550', '1.725', '1.900') NOT NULL,
    objective ENUM('EMAGRECER', 'MANTER', 'GANHAR') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE diets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(32) NOT NULL,
    basal_calc DECIMAL(8,2),
    get_calc DECIMAL(8,2),
    kcal_objt DECIMAL(8,2),
    protein DECIMAL(8,2),
    fat DECIMAL(8,2),
    carbs DECIMAL(8,2),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_diets_user_id (user_id),
    CONSTRAINT fk_diets_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(16) NOT NULL UNIQUE,
    user_id INT NULL,
    name VARCHAR(32) NOT NULL,
    favorite BOOLEAN NOT NULL DEFAULT FALSE,
    kcal DECIMAL(8,2) NOT NULL,
    carbs DECIMAL(8,2) NOT NULL,
    fats DECIMAL(8,2) NOT NULL,
    protein DECIMAL(8,2) NOT NULL,
    unit ENUM('g', 'ml') NOT NULL,
    category VARCHAR(32) NOT NULL,
    is_global BOOLEAN NOT NULL DEFAULT FALSE,
    photo_url VARCHAR(255) NULL,
    moderation_status ENUM('PENDENTE', 'APROVADO', 'REJEITADO') NOT NULL DEFAULT 'PENDENTE',
    moderated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_foods_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    diet_id INT NOT NULL,
    name VARCHAR(32) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_meals_diet_id (diet_id),
    CONSTRAINT fk_meals_diet
        FOREIGN KEY (diet_id)
        REFERENCES diets(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
 
CREATE TABLE food_meal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meal_id INT NOT NULL,
    food_id INT NOT NULL,
    quantity DECIMAL(8,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_food_meal_meal_id (meal_id),
    INDEX idx_food_meal_food_id (food_id),
    CONSTRAINT fk_food_meal_meal
        FOREIGN KEY (meal_id)
        REFERENCES meals(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_food_meal_food
        FOREIGN KEY (food_id)
        REFERENCES foods(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

SET foreign_key_checks = 1;