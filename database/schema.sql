SET foreign_key_checks = 0;

DROP TABLE IF EXISTS problem_user_reinforce;
DROP TABLE IF EXISTS problems;
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
    age INT NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    biotype ENUM('ECTOMORFO', 'MESOMORFO', 'ENDOMORFO') NOT NULL,
    gender ENUM('M', 'F', 'NI') NOT NULL,
    activity_factor DECIMAL(4,3) NOT NULL,
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
    name VARCHAR(255) NOT NULL,
    basal_calc DECIMAL(8,2),
    get_calc DECIMAL(8,2),
    kcal_objt DECIMAL(8,2),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_diets_user_id (user_id),
    CONSTRAINT fk_diets_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE problems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    INDEX idx_problems_user_id (user_id),
    CONSTRAINT fk_problems_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE problem_user_reinforce (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    problem_id INT NOT NULL,
    INDEX idx_reinforce_user_id (user_id),
    INDEX idx_reinforce_problem_id (problem_id),
    UNIQUE KEY uq_user_problem (user_id, problem_id),
    CONSTRAINT fk_reinforce_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_reinforce_problem
        FOREIGN KEY (problem_id)
        REFERENCES problems(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

SET foreign_key_checks = 1;