<?php

namespace Lib;

use Core\Database\Database;

class Validations
{
    public static function notEmpty($attribute, $obj, string $msg = 'Não pode ser vazio!')
    {
        if ($obj->$attribute === null || $obj->$attribute === '') {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function greaterThan($attribute, $obj, $value, string $msg = 'Não pode ser vazio!')
    {
        if ($obj->$attribute <= $value) {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function maxLength($attribute, $obj, $value, string $msg = 'Excede o limite de caracteres!')
    {
        if (strlen($obj->$attribute) > $value) {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function maxValue($attribute, $obj, $value, string $msg = 'Excede o limite!')
    {
        if ($obj->$attribute > $value) {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function validateBirthday(
        $attribute,
        $obj,
        int $value,
        string $invalidMsg = 'Informe uma data de nascimento válida!',
        string $futureMsg = 'A data de nascimento não pode ser futura!',
        string $msg = 'Idade mínima não atingida!'
    ): bool {
        if (!$obj->$attribute) {
            return false;
        }

        $date = \DateTime::createFromFormat('Y-m-d', $obj->$attribute);

        if (!$date || $date->format('Y') < 1920) {
            $obj->addError($attribute, $invalidMsg);
            return false;
        }

        if ($date > new \DateTime('now', new \DateTimeZone('America/Sao_Paulo'))) {
            $obj->addError($attribute, $futureMsg);
            return false;
        }

        if ($obj->age() < $value) {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function inList($attribute, $obj, array $list, string $msg = 'Valor inválido!'): bool
    {
        if ($obj->errors($attribute)) {
            return false;
        }

        if (!in_array($obj->$attribute, $list)) {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function fileExtension($attribute, $obj, array $extensions, string $msg = 'Extensão de arquivo inválida!'): bool
    {
        if (empty($obj->$attribute['tmp_name']) || $obj->$attribute['error'] !== UPLOAD_ERR_OK) {
            return true;
        }

        $parts = explode('.', $obj->$attribute['name']);
        $nominalExtension = strtolower(end($parts));

        $hasValidMime = $obj->image()->hasValidMimeType($obj->$attribute);

        if (!$hasValidMime || !in_array($nominalExtension, $extensions)) {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function fileSize($attribute, $obj, int $maxSize, string $msg = 'Tamanho excede o limite permitido!'): bool
    {
        if (empty($obj->$attribute['name'])) {
            return true;
        }

        if ($obj->$attribute['size'] > $maxSize) {
            $obj->addError($attribute, $msg);
            return false;
        }

        return true;
    }

    public static function passwordConfirmation($obj)
    {
        if ($obj->password !== $obj->password_confirmation) {
            $obj->addError('password', 'as senhas devem ser idênticas!');
            return false;
        }

        return true;
    }

    public static function uniqueness($fields, $object)
    {
        $dbFieldsValues = [];
        $objFieldValues = [];

        if (!is_array($fields)) {
            $fields = [$fields];
        }

        if (!$object->newRecord()) {
            $dbObject = $object::findById($object->id);

            foreach ($fields as $field) {
                $dbFieldsValues[] = $dbObject->$field;
                $objFieldValues[] = $object->$field;
            }

            if (
                !empty($dbFieldsValues) &&
                !empty($objFieldValues) &&
                $dbFieldsValues === $objFieldValues
            ) {
                return true;
            }
        }

        $table = $object::table();
        $conditions = implode(' AND ', array_map(fn($field) => "{$field} = :{$field}", $fields));

        $sql = <<<SQL
            SELECT id FROM {$table} WHERE {$conditions};
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);

        foreach ($fields as $field) {
            $stmt->bindValue($field, $object->$field);
        }

        $stmt->execute();

        if ($stmt->rowCount() !== 0) {
            foreach ($fields as $field) {
                $object->addError($field, 'já existe um registro com esse dado');
            }
            return false;
        }

        return true;
    }
}
